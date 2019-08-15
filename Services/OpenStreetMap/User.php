<?php
/**
 * User.php
 * 07-Sep-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     User.php
 */

/**
 * Services_OpenStreetMap_User
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     User.php
 */
class Services_OpenStreetMap_User
{

    /**
     * User preferences
     *
     * @var array
     */
    protected $preferences = [];

    /**
     * Transport object.
     *
     * @var Services_OpenStreetMap_Transport
     */
    protected $transport = null;

    /**
     * Config object, contains setting on how to interact with API Endpoint
     *
     * @var Services_OpenStreetMap_Config $config
     */
    protected $config = null;

    /**
     * XML represention of the User
     *
     * @var string
     */
    protected $xml = null;

    /**
     * Object based on xml for user
     *
     * @var object|false
     */
    protected $obj;

    /**
     * Preferences XML
     *
     * @var string
     */
    protected $prefXml;

    /**
     * Preferences object
     *
     * @var object|false
     */
    protected $prefObj;

    /**
     * Set the XML representing this user.
     *
     * @param SimpleXMLElement $xml XML describing a user.
     *
     * @return Services_OpenStreetMap_User
     */
    public function setXml(SimpleXMLElement $xml): Services_OpenStreetMap_User
    {
        $this->xml = $xml->saveXML();
        $this->obj = $xml->xpath('//user');
        return $this;
    }

    /**
     * Set the XML for preferences.
     *
     * @param mixed $xml XML describing a user's preferences.
     *
     * @return void
     */
    public function setPreferencesXml($xml): void
    {
        $this->prefXml = $xml;
        $sXml = simplexml_load_string($xml);
        if ($sXml !== false) {
            $this->prefObj = $sXml->xpath('//preferences');
        }
    }

    /**
     * Return the attributes set for this user instance.
     *
     * @return mixed getAttributes()
     */
    public function getAttributes()
    {
        if ($this->obj === false) {
            return false;
        }
        return $this->obj[0]->attributes();
    }

    /**
     * Return the display name of the user.
     *
     * @return string display name of user.
     */
    public function getDisplayName(): string
    {
        return (string) $this->getAttributes()->display_name;
    }

    /**
     * Retrieve date, as a string, representing when the user's account was
     * created.
     *
     * @return string
     */
    public function getAccountCreated(): string
    {
        return (string) ($this->getAttributes()->account_created);
    }

    /**
     * Return the description set for the user.
     *
     * @return string
     */
    public function getDescription(): string
    {
        $sXml = simplexml_load_string($this->xml);
        if ($sXml === false) {
            return '';
        }
        $desc = $sXml->xpath('//user/description');
        return trim($desc[0]);
    }

    /**
     * Retrieve the id of the user.
     *
     * @return string id of the object
     */
    public function getId(): string
    {
        return (string) $this->getAttributes()->id;
    }

    /**
     * Return href to user's profile image, null if not set.
     *
     * @return string|null
     */
    public function getImage(): ?string
    {
        $sXml = simplexml_load_string($this->xml);
        if ($sXml === false) {
            return null;
        }
        $img = $sXml->xpath('//user/img');
        if (empty($img)) {
            return null;
        }
        return (string) $img[0]->attributes()->href;
    }

    /**
     * Return an array of the user's preferred languages.
     *
     * @return array|null
     */
    public function getLanguages(): ?array
    {
        $langers = [];
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $languages = $cxml->xpath('//user/languages');
        if (empty($languages)) {
            return null;
        }
        foreach ($languages[0]->children() as $child) {
            if ($child->getName() == 'lang') {
                $langers[] = (string) $child[0];
            }
        }
        return $langers;
    }

    /**
     * Latitude of 'home' setting for user.
     *
     * @return float|null
     */
    public function getLat(): ?float
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $home = $cxml->xpath('//user/home');
        if ($home === false) {
            return null;
        }
        return (float) $home[0]->attributes()->lat;
    }

    /**
     * Longitude of 'home' setting for user.
     *
     * @return float|null
     */
    public function getLon(): ?float
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $home = $cxml->xpath('//user/home');
        if (empty($home)) {
            return null;
        }
        return (float) $home[0]->attributes()->lon;
    }

    /**
     * Zoom level of 'home' setting for user.
     *
     * @return integer|null
     */
    public function getZoom(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $home = $cxml->xpath('//user/home');
        if (empty($home)) {
            return null;
        }
        return (int) $home[0]->attributes()->zoom;
    }

    /**
     * The number of changesets opened by the user.
     *
     * @return integer|null
     */
    public function getChangesets(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $changesets = $cxml->xpath('//user/changesets');
        if (empty($changesets)) {
            return null;
        }
        return (int) $changesets[0]->attributes()->count;
    }

    /**
     * The number of traces uploaded by the user.
     *
     * @return integer|null
     */
    public function getTraces(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $traces = $cxml->xpath('//user/traces');
        if (empty($traces)) {
            return null;
        }
        return (int) $traces[0]->attributes()->count;
    }

    /**
     * The [total] number of blocks received by the user.
     *
     * @return integer|null
     */
    public function getBlocksReceived(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $changesets = $cxml->xpath('//user/blocks/received');
        if (empty($changesets)) {
            return null;
        }
        return (int) $changesets[0]->attributes()->count;
    }

    /**
     * The number of active blocks received by the user.
     *
     * @return integer|null
     */
    public function getActiveBlocksReceived(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $changesets = $cxml->xpath('//user/blocks/received');
        if (empty($changesets)) {
            return null;
        }
        return (int) $changesets[0]->attributes()->active;
    }

    /**
     * The [total] number of blocks issued by the user.
     *
     * @return integer|null
     */
    public function getBlocksIssued(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $changesets = $cxml->xpath('//user/blocks/issued');
        if (empty($changesets)) {
            return null;
        }
        return (int) $changesets[0]->attributes()->count;
    }

    /**
     * The number of active blocks issued by the user.
     *
     * @return integer|null
     */
    public function getActiveBlocksIssued(): ?int
    {
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return null;
        }
        $changesets = $cxml->xpath('//user/blocks/issued');
        if (empty($changesets)) {
            return null;
        }
        return (int) $changesets[0]->attributes()->active;
    }

    /**
     * Array of names of roles associated with the user.
     *
     * @return array
     */
    public function getRoles(): array
    {
        $ret = [];
        $cxml = simplexml_load_string($this->xml);
        if ($cxml === false) {
            return [];
        }
        $roles = $cxml->xpath('//user/roles');
        if (empty($roles)) {
            return $ret;
        }
        foreach ($roles[0]->children() as $child) {
            $ret[] = $child->getName();
        }
        return $ret;
    }

    /**
     * Return an array of the user's preferences.
     *
     * @return array
     */
    public function getPreferences(): array
    {
        if ($this->preferences == []) {
            $preferences = [];
            if ($this->prefObj !== false) {
                foreach ($this->prefObj[0]->children() as $child) {
                    $key = (string) $child->attributes()->k;
                    if ($key !== '') {
                        $preferences[$key] = (string) $child->attributes()->v;
                    }
                }
            }
            $this->preferences = $preferences;
        }
        return $this->preferences;
    }

    /**
     * Set user preferences, updating the values on the server automatically.
     *
     * To update a single preference, use an array with just one entry.
     *
     * @param array $preferences Key/Value pairs in associative array
     *
     * @return Services_OpenStreetMap_User
     */
    public function setPreferences(array $preferences): Services_OpenStreetMap_User
    {
        $doc = '';
        $this->preferences = $preferences;
        $config = $this->getConfig()->asArray();
        $url = $config['server']
            . 'api/'
            . $config['api_version']
            . '/user/preferences';
        if (count($preferences) > 1) {
            $doc = "<osm version='0.6' generator='Services_OpenStreetMap'>"
                . '<preferences>';
            foreach ($preferences as $key => $value) {
                $doc .= "<preference k='$key' v='$value' />";
            }
            $doc .= '</preferences></osm>';
        } elseif (count($preferences) === 1) {
            foreach ($preferences as $k => $v) {
                $url .= '/' . $k;
                $doc = $v;
            }
        }
        try {
            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_PUT,
                $config['user'],
                $config['password'],
                $doc,
                null,
                [['Content-type', 'text/xml', true]]
            );
        } catch (Exception $ex) {
            $code = $ex->getCode();
        }
        return $this;
    }

    /**
     * Set the Transport instance.
     *
     * @param Services_OpenStreetMap_Transport $transport Transport instance.
     *
     * @return Services_OpenStreetMap_User
     */
    public function setTransport(
        Services_OpenStreetMap_Transport $transport
    ): Services_OpenStreetMap_User {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Retrieve the current Transport instance.
     *
     * @return Services_OpenStreetMap_Transport.
     */
    public function getTransport(): \Services_OpenStreetMap_Transport
    {
        return $this->transport;
    }

    /**
     * Get current Config object
     *
     * @return Services_OpenStreetMap_Config
     */
    public function getConfig(): \Services_OpenStreetMap_Config
    {
        return $this->config;
    }

    /**
     * Set Config object
     *
     * @param Services_OpenStreetMap_Config $config Config object
     *
     * @return Services_OpenStreetMap_User
     */
    public function setConfig(
        Services_OpenStreetMap_Config $config
    ): Services_OpenStreetMap_User {
        $this->config = $config;
        return $this;
    }
}
// vim:set et ts=4 sw=4:
