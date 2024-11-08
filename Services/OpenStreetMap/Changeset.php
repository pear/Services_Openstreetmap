<?php
/**
 * Changeset.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       Changeset.php
 */

/**
 * Services_OpenStreetMap_Changeset
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @author     Valery Khvalov <khvalov@tut.by>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Changeset.php
 */
class Services_OpenStreetMap_Changeset extends Services_OpenStreetMap_Object
{
    /**
     * Array of tags in key/value format
     *
     * @var array
     */
    protected $tags = [];

    /**
     * What type object this is.
     *
     * @var string
     */
    protected $type = 'changeset';

    /**
     * Array containing members of what this changeset represents.
     *
     * @var array
     */
    protected $members = [];

    /**
     * Array of the Ids of the members of what this changeset represents.
     *
     * @var array
     */
    protected $membersIds = [];

    /**
     * Whether the changeset is open.
     *
     * @var bool
     */
    protected $open = false;

    /**
     * The Id of this changeset.
     *
     * @var string|null
     */
    protected $id = null;

    /**
     * The OsmChange XML for this changeset.
     *
     * @var string|null
     */
    protected $osmChangeXml = null;

    /**
     * Used to keep track of Id updates.
     *
     * @var array
     */
    protected $updateMap = [];

    /**
     * Atomic?
     *
     * @var bool
     */
    protected $atomic;

    /**
     * The message set for the changeset
     *
     * @var string
     */
    protected $message = '';

    /**
     * Set if requesting for someone to review uploaded changeset.
     *
     * @var mixed
     */
    protected $reviewRequested = false;

    const ATOMIC = 'atomic';
    const MESSAGE = 'message';
    const REVIEW_REQUESTED = 'reviewRequested';

    /**
     * Constructor
     *
     * @param array $settings Associative array of settings for the changeset.
     *
     *  bool   $atomic          Whether changeset is atomic or not.
     *  string $message         The changeset log message.
     *  bool   $reviewRequested Set if a review of the changes is required.
     *
     * @return Services_OpenStreetMap_Changeset
     */
    public function __construct($settings = [])
    {
        if (array_key_exists(self::ATOMIC, $settings)) {
            $this->atomic = $settings[self::ATOMIC];
        }
        if (array_key_exists(self::MESSAGE, $settings)) {
            $this->message = $settings[self::MESSAGE];
        }
        if (array_key_exists(self::REVIEW_REQUESTED, $settings)) {
            $this->reviewRequested = $settings[self::REVIEW_REQUESTED];
        }
    }

    /**
     * Set tag to [new] key/value pair.
     *
     * The object is returned, supporting Fluent coding style.
     *
     * <code>
     * $changeset->setTag('key', 'value')->setTag(...);
     * </code>
     *
     * @param mixed $key   key
     * @param mixed $value value
     *
     * @return Services_OpenStreetMap_Changeset
     */
    public function setTag($key, $value): Services_OpenStreetMap_Changeset
    {
        $this->tags[$key] = $value;
        return $this;
    }
    /**
     * Return the tags set for this changeset in question.
     *
     * @return array tags
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Return value of specified tag as set against this changeset.
     * If tag isn't set, return null.
     *
     * @param string $key Key value, For example, 'created_by', 'automatic' etc
     *
     * @return string|null
     */
    public function getTag(string $key): ?string
    {
        if (isset($this->tags[$key])) {
            return $this->tags[$key];
        } else {
            return null;
        }
    }
    
    /**
     * Begin changeset transaction.
     *
     * @param string $message The changeset log message. Overrides same as set in constructor.
     *
     * @return void
     * @throws Services_OpenStreetMap_RuntimeException If either user or
     *                                                 password are not set.
     */
    public function begin(string $message): void
    {
        $response = null;
        $code = null;
        $this->members = [];
        $this->open = true;
        $configObj = $this->getConfig();
        if ($message == '') {
            $message = $this->message;
        }
        $userAgent = $configObj->getValue('User-Agent');
        $doc = "<?xml version='1.0' encoding=\"UTF-8\"?>\n" .
        '<osm version="0.6" generator="' . $userAgent . '">'
            . "<changeset id='0' open='false'>";
        $this->setTag('comment' , $message);
        $this->setTag('created_by' , $userAgent . '/0.1');
        $this->setTag('review_requested' , ($this->reviewRequested ? 'yes' : 'no'));
        $tags = $this->getTags();
        foreach ($tags as $key => $value) {
            $doc .=  '<tag k="' . $key . '" v="' . $value . '"/>';
        }      
        $doc .= '</changeset></osm>';
        $url = $configObj->getValue('server')
            . 'api/'
            . $configObj->getValue('api_version')
            . '/changeset/create';
        $user = $configObj->getValue('user');
        $password = $configObj->getValue('password');
        /* Oauth2 Field */
        $oauth2_token = $configObj->getValue('oauth2_token');        
        /* Oauth1 Fields */
        $oauth_consumer_key = $configObj->getValue('oauth_consumer_key');
        $oauth_token = $configObj->getValue('oauth_token');
        $consumer_secret = $configObj->getValue('consumer_secret');
        $oauth_token_secret = $configObj->getValue('oauth_token_secret');


        if ($user !== null && $password !== null) {
            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_PUT,
                $user,
                $password,
                $doc,
                null,
                [['Content-type', 'text/xml', true]]
            );
        } elseif (!empty($oauth2_token)) {
            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_PUT,
                null,
                null,
                $doc,
                null,
                [
                    ['Content-type', 'text/xml', true],
                    ['Authorization', 'Bearer ' . $oauth2_token, true]
                ]
            );
        } elseif (!empty($oauth_consumer_key)
            && !empty($oauth_token)
            && !empty($consumer_secret)
            && !empty($oauth_token_secret)
        ) {
            include_once 'Services/OpenStreetMap/Helper/OAuth.php';
            $timest = Services_OpenStreetMap_Helper_OAuth::getOauthTimestamp();
            $nonce = Services_OpenStreetMap_Helper_OAuth::getOauthNonce();

            $oAuthArray = [
                'oauth_consumer_key'     => $oauth_consumer_key,
                'oauth_nonce'            => $nonce,
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_timestamp'        => $timest,
                'oauth_token'            => $oauth_token,
                'oauth_version'          => '1.0'
            ];

            $authString = Services_OpenStreetMap_Helper_OAuth::assocArrayToString($oAuthArray);
            $encoded = '';
            if (is_string($authString)) {
                $encoded = rawurlencode($authString);
            }
            $hashString = HTTP_Request2::METHOD_PUT . '&' . rawurlencode($url) . '&' . $encoded;

            $oAuthArray['oauth_signature'] = Services_OpenStreetMap_Helper_OAuth::getOauthSignature(
                $consumer_secret . '&' . $oauth_token_secret,
                $hashString
            );

            $authStr = 'OAuth ' . Services_OpenStreetMap_Helper_OAuth::assocArrayToString(
                $oAuthArray,
                '=',
                ', ',
                '"'
            );

            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_PUT,
                null,
                null,
                $doc,
                null,
                [
                    ['Content-type', 'text/xml', true],
                    ['Authorization', $authStr, true]
                ]
            );
        } else {
            try {
                $this->_validateCredentials($user, $password);
            } catch (Exception $ex) {
                $message = $ex->getMessage();
                throw new Services_OpenStreetMap_RuntimeException($message);
            }
        }

        $code = $response->getStatus();
        if (Services_OpenStreetMap_Transport::OK == $code) {
            $trimmed = trim($response->getBody());
            if (is_numeric($trimmed)) {
                $this->id = $trimmed;
            }
        }
    }

    /**
     * Add object to the changeset so changes can be transmitted to the server.
     *
     * @param Services_OpenStreetMap_Object $object OSM object
     *
     * @return void
     * @throws Services_OpenStreetMap_RuntimeException If an object has already
     *                                                 been added to the changeset
     *                                                 or has been added to a
     *                                                 closed changeset.
     */
    public function add(Services_OpenStreetMap_Object $object): void
    {
        if (!$this->open) {
            throw new Services_OpenStreetMap_RuntimeException(
                'Object added to closed changeset'
            );
        }
        $object->setChangesetId($this->getId());
        $objectId = $object->getType() . $object->getId();
        if (!in_array($objectId, $this->membersIds)) {
            $this->members[] = $object;
            $this->membersIds[] = $objectId;
        } else {
            throw new Services_OpenStreetMap_RuntimeException(
                'Object added to changeset already'
            );
        }
    }

    /**
     * Commit changeset, posting to server.
     *
     * Generate osmChange document and post it to the server, when successful
     * close the changeset. Return true on success, false otherwise.
     *
     * https://wiki.openstreetmap.org/wiki/API_v0.6#Close:_PUT_/api/0.6/changeset/#id/close
     *
     * @return bool Success
     * @link   http://wiki.openstreetmap.org/wiki/OsmChange
     * @throws Services_OpenStreetMap_RuntimeException If changeset Id is not
     *                                                 numeric.
     * @throws Services_OpenStreetMap_Exception        If changeset is already
     *                                                 closed.
     */
    public function commit(): bool
    {
        if (!$this->open) {
            throw new Services_OpenStreetMap_Exception(
                'Attempt to commit a closed changeset'
            );
        }

        $code = null;
        // Generate URL that the osmChange document will be posted to
        $cId = $this->getId();
        $changesetValidator = new Services_OpenStreetMap_Validator_Changeset();
        $changesetValidator->validate($cId);

        $configObj = $this->getConfig();
        $url = $configObj->getValue('server')
            . 'api/'
            . $configObj->getValue('api_version') .
            "/changeset/{$cId}/upload";

        $user = $configObj->getValue('user');
        $password = $configObj->getValue('password');
        /* Oauth2 Field */
        $oauth2_token = $configObj->getValue('oauth2_token');
        /* Oauth1 Fields */
        $oauth_consumer_key = $configObj->getValue('oauth_consumer_key');
        $oauth_token = $configObj->getValue('oauth_token');
        $consumer_secret = $configObj->getValue('consumer_secret');
        $oauth_token_secret = $configObj->getValue('oauth_token_secret');

        // Post the osmChange document to the server
        $response = null;
        try {
            $response = $this->_uploadWithUsernameAndPassword($url, $user, $password);
            if ($response === null) {
                $response = $this->_uploadWithOauth2($url, $oauth2_token);
            }
            if ($response === null) {
                $response = $this->_uploadWithOauth($url, $oauth_consumer_key, $oauth_token, $consumer_secret, $oauth_token_secret);
            }
            if ($response === null) {
                throw new Services_OpenStreetMap_RuntimeException(
                    "User & Password for user based auth " .
                    "OR oauth2_token " .
                    "OR oauth_consumer_key,  oauth_token, consumer_secret, oauth_token_secret " .
                    "have to be defined to interact with OSM API"
                );
            }
            $this->updateObjectIds($response->getBody());
        } catch (Exception $ex) {
            $code = $ex->getCode();
        }

        if (is_object($response)) {
            $code = $response->getStatus();
        }
        $changesetValidator->validateChangesetPostedOk($code);
        // Explicitly close the changeset
        $url = $configObj->getValue('server')
            . 'api/'
            . $configObj->getValue('api_version')
            . "/changeset/{$cId}/close";

        $code = null;
        $response = null;
        try {
            $response = $this->_closeWithUsernameAndPassword($url, $user, $password);
            if ($response === null) {
                $response = $this->_closeWithOauth2($url, $oauth2_token);
            }
            if ($response === null) {
                $response = $this->_closeWithOauth(
                    $url,
                    $oauth_consumer_key,
                    $oauth_token,
                    $consumer_secret,
                    $oauth_token_secret
                );
            }
            if ($response === null) {
                throw new Services_OpenStreetMap_RuntimeException(
                    "User & Password for user based auth " .
                    "OR oauth2_token " .
                    "OR oauth_consumer_key,  oauth_token, consumer_secret, oauth_token_secret " .
                    "have to be defined to interact with OSM API"
                );
            }
        } catch (Exception $ex) {
            $code = $ex->getCode();
        }
        if (is_object($response)) {
            $code = $response->getStatus();
        }
        $changesetValidator->validateChangesetClosedOk($code);
        $this->open = false;
        return $code === 200;
    }

    /**
     * Commit using username and password, if set.
     *
     * If username and password are both null, return null.
     *
     * @param string $url      URL for uploading changeset
     * @param string $user     username
     * @param string $password User's password
     *
     * @return void
     */
    private function _uploadWithUsernameAndPassword($url, $user, $password)
    {
        if ($user === null && $password === null) {
            return null;
        }
        return $this->getTransport()->getResponse(
            $url,
            HTTP_Request2::METHOD_POST,
            $user,
            $password,
            $this->getOsmChangeXml(),
            null,
            [['Content-type', 'text/xml', true]]
        );
    }

    /**
     * Upload changeset via Oauth2 details
     *
     * @param string $url                URL for uploading changeset
     * @param string $oauth2_token       Oauth2 token
     *
     * @return void
     */
    private function _uploadWithOauth2($url, $oauth2_token)
    {
        if (!empty($oauth2_token)) {
            
            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_POST,
                null,
                null,
                $this->getOsmChangeXml(),
                null,
                [
                    ['Content-type', 'text/xml', true],
                    ['Authorization', 'Bearer ' . $oauth2_token, true]
                ]
            );

            return $response;
        }
        return null;
    }
            
    /**
     * Upload changeset via Oauth details
     *
     * @param string $url                URL for uploading changeset
     * @param string $oauth_consumer_key Consumer key
     * @param string $oauth_token        Oauth token
     * @param string $consumer_secret    Consumer secret
     * @param string $oauth_token_secret Oauth token secret
     *
     * @return void
     */
    private function _uploadWithOauth($url, $oauth_consumer_key, $oauth_token, $consumer_secret, $oauth_token_secret)
    {
        if (!empty($oauth_consumer_key)
            && !empty($oauth_token)
            && !empty($consumer_secret)
            && !empty($oauth_token_secret)
        ) {
            include_once 'Services/OpenStreetMap/Helper/OAuth.php';
            $timest = Services_OpenStreetMap_Helper_OAuth::getOauthTimestamp();
            $nonce = Services_OpenStreetMap_Helper_OAuth::getOauthNonce();

            $oAuthArray = [
                'oauth_consumer_key'     => $oauth_consumer_key,
                'oauth_nonce'            => $nonce,
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_timestamp'        => $timest,
                'oauth_token'            => $oauth_token,
                'oauth_version'          => '1.0'
            ];

            $oAuthString = Services_OpenStreetMap_Helper_OAuth::assocArrayToString($oAuthArray);
            $reUrl = rawurlencode($url);
            $reAuthString = '';
            if (is_string($oAuthString)) {
                $reAuthString = rawurlencode($oAuthString);
            }
            $hashString = HTTP_Request2::METHOD_POST . '&' . $reUrl . '&' . $reAuthString;
            $oAuthArray['oauth_signature'] = Services_OpenStreetMap_Helper_OAuth::getOauthSignature(
                $consumer_secret . '&' . $oauth_token_secret,
                $hashString
            );

            $authStr = 'OAuth ' . Services_OpenStreetMap_Helper_OAuth::assocArrayToString(
                $oAuthArray,
                '=',
                ', ',
                '"'
            );

            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_POST,
                null,
                null,
                $this->getOsmChangeXml(),
                null,
                [
                    ['Content-type', 'text/xml', true],
                    ['Authorization', $authStr, true]
                ]
            );

            return $response;
        }
        return null;
    }

    /**
     * Close uploaded changeset with username and password
     *
     * @param string $url      URL for closing uploaded changeset
     * @param string $user     Username
     * @param string $password User's password
     *
     * @return void
     */
    private function _closeWithUsernameAndPassword($url, $user, $password)
    {
        if ($user === null && $password === null) {
            return null;
        }
        return $this->getTransport()->getResponse(
            $url,
            HTTP_Request2::METHOD_PUT,
            $user,
            $password,
            null,
            null,
            [['Content-type', 'text/xml', true]]
        );
    }

    /**
     * Close uploaded changeset with Oauth2 details
     *
     * @param string $url                URL for closing uploaded changeset
     * @param string $oauth2_token       Oauth2 token
     *
     * @return $request
     */
    private function _closeWithOauth2($url, $oauth2_token)
    {
        if (!empty($oauth2_token)) {
     
            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_PUT,
                null,
                null,
                null,
                null,
                [
                    ['Content-type', 'text/xml', true],
                    ['Authorization', 'Bearer ' . $oauth2_token, true]
                ]
            );
            return $response;
        }
        return null;
    }
    
    /**
     * Close uploaded changeset with Oauth details
     *
     * @param string $url                URL for closing uploaded changeset
     * @param string $oauth_consumer_key Consumer key
     * @param string $oauth_token        Oauth token
     * @param string $consumer_secret    Consumer secret
     * @param string $oauth_token_secret Oauth token secret
     *
     * @return $request
     */
    private function _closeWithOauth($url, $oauth_consumer_key, $oauth_token, $consumer_secret, $oauth_token_secret)
    {
        if (!empty($oauth_consumer_key)
            && !empty($oauth_token)
            && !empty($consumer_secret)
            && !empty($oauth_token_secret)
        ) {
            include_once 'Services_OpenStreetMap_Helper_OAuth.php';
            $timest = Services_OpenStreetMap_Helper_OAuth::getOauthTimestamp();
            $nonce  = Services_OpenStreetMap_Helper_OAuth::getOauthNonce();

            $oAuthArray = [
                'oauth_consumer_key'     => $oauth_consumer_key,
                'oauth_nonce'            => $nonce,
                'oauth_signature_method' => 'HMAC-SHA1',
                'oauth_timestamp'        => $timest,
                'oauth_token'            => $oauth_token,
                'oauth_version'          => '1.0'
            ];

            $oauthString = Services_OpenStreetMap_Helper_OAuth::assocArrayToString($oAuthArray);
            $reUrl = rawurlencode($url);
            $reAuthString = '';
            if (is_string($oauthString)) {
                $reAuthString = rawurlencode($oauthString);
            }
            $hashString = '';
            if (($reUrl !== '') && ($reAuthString !== '')) {
                $hashString = HTTP_Request2::METHOD_PUT . '&' . $reUrl . '&' . $reAuthString;
            }

            $oAuthArray['oauth_signature'] = Services_OpenStreetMap_Helper_OAuth::getOauthSignature(
                $consumer_secret . '&' . $oauth_token_secret,
                $hashString
            );

            $authStr = 'OAuth ' . Services_OpenStreetMap_Helper_OAuth::assocArrayToString(
                $oAuthArray,
                '=',
                ', ',
                '"'
            );

            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_PUT,
                null,
                null,
                null,
                null,
                [
                    ['Content-type', 'text/xml', true],
                    ['Authorization', $authStr, true]
                ]
            );
            return $response;
        }
        return null;
    }

    /**
     * Generate and return the OsmChange XML required to record the changes
     * made to the object in question.
     *
     * @return string
     * @link   http://wiki.openstreetmap.org/wiki/OsmChange
     */
    public function getOsmChangeXml(): string
    {
        if ($this->osmChangeXml === null) {
            // Generate the osmChange document
            $blocks = null;
            foreach ($this->members as $member) {
                $blocks .= $member->getOsmChangeXml() . "\n";
            }
            $this->setOsmChangeXml(
                "<osmChange version='0.6' generator='Services_OpenStreetMap'>"
                . $blocks . '</osmChange>'
            );
        }
        return $this->osmChangeXml;
    }

    /**
     * Set Change XML.
     *
     * @param string $xml OsmChange XML
     *
     * @return Services_OpenStreetMap_Changeset
     */
    public function setOsmChangeXml(string $xml): Services_OpenStreetMap_Changeset
    {
        $this->osmChangeXml = $xml;
        return $this;
    }

    /**
     * Get CreatedAt time.
     *
     * @return string
     */
    public function getCreatedAt(): string
    {
        return (string) $this->getAttributes()->created_at;
    }

    /**
     * Get ClosedAt time.
     *
     * @return string
     */
    public function getClosedAt(): string
    {
        return (string) $this->getAttributes()->closed_at;
    }

    /**
     * Is the changeset open?
     *
     * @return boolean
     */
    public function isOpen(): bool
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return $attribs->open == 'true';
        }
        return $this->open;
    }

    /**
     * Return min longitude.
     *
     * @return float
     */
    public function getMinLon(): float
    {
        return (float) $this->getAttributes()->min_lon;
    }

    /**
     * Return min latitude value.
     *
     * @return float
     */
    public function getMinLat(): float
    {
        return (float) $this->getAttributes()->min_lat;
    }


    /**
     * Return max longitude value.
     *
     * @return float
     */
    public function getMaxLon(): float
    {
        return (float) $this->getAttributes()->max_lon;
    }

    /**
     * Return max latitude value.
     *
     * @return float
     */
    public function getMaxLat(): float
    {
        return (float) $this->getAttributes()->max_lat;
    }

    /**
     * Get changeset Id.
     *
     * @return null|string value or null if none set
     */
    public function getId(): ?string
    {
        $p_id = parent::getId();
        if ($p_id === null) {
            return $this->id;
        }
        return $p_id;
    }

    /**
     * Given diffResult xml, update Ids of objects that are members of the
     * current changeset.
     *
     * @param string $body diffResult xml
     *
     * @return void
     * @throws Services_OpenStreetMap_Exception If diffResult xml is invalid.
     */
    public function updateObjectIds(string $body): void
    {
        $body = trim($body);
            // should check here that body has expected form.
        if (stripos($body, 'diffResult') === false) {
            throw new Services_OpenStreetMap_Exception('Invalid diffResult XML');
        }
        $cxml = simplexml_load_string($body);
        if ($cxml !== false) {
            $obj = $cxml->xpath('//diffResult');
            foreach ($obj[0]->children() as $child) {
                $old_id = null;
                $new_id = null;
                $old_id = (string) $child->attributes()->old_id;
                $new_id = (string) $child->attributes()->new_id;
                $this->updateObjectId($child->getName(), $old_id, $new_id);
            }
        }
    }

    /**
     * Update id of some type of object.
     *
     * @param string $type  Object type
     * @param string $oldId Old id
     * @param string $newId New id
     *
     * @return void
     */
    public function updateObjectId(string $type, string $oldId, string $newId): void
    {
        if ($oldId === $newId) {
            return;
        }
        foreach ($this->members as $member) {
            if ($member->getType() == $type && $member->getId() == $oldId) {
                $member->setId($newId);
                $this->updateMap[$oldId] = $newId;
            }
        }
    }

    /**
     * Get update map.
     *
     * @return array
     */
    public function getUpdateMap(): array
    {
        return $this->updateMap;
    }

    /**
     * Validate credentials for using the API backend.
     *
     * @param string|null $user     Username
     * @param string|null $password Password
     *
     * @throws Services_OpenStreetMap_RuntimeException Thrown if user/password or password file not set
     *
     * @return void
     */
    private function _validateCredentials($user, $password)
    {
        if ($user !== null && $password === null) {
            throw new Services_OpenStreetMap_RuntimeException(
                "Password must be set"
            );
        } elseif ($user === null && $password !== null) {
            throw new Services_OpenStreetMap_RuntimeException(
                "User must be set"
            );
        } elseif ($this->getConfig()->getValue('passwordfile') === null) {
            throw new Services_OpenStreetMap_RuntimeException(
                "User & Password for user based auth " .
                "OR oauth2_token " .
                "OR oauth_consumer_key,  oauth_token, consumer_secret, oauth_token_secret " .
                "have to be defined to interact with OSM API"
            );
        }
    }
}
