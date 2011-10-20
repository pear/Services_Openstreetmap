<?php
/**
 * Provide a method of interfacing with Openstreetmap servers.
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Services_Openstreetmap
 * @link     http://wiki.openstreetmap.org/wiki/Api06
 */

require_once 'HTTP/Request2.php';

spl_autoload_register(array('Services_Openstreetmap', 'autoload'));

/**
 * Services_Openstreetmap - interface with Openstreetmap
 *
 * @category  Services
 * @package   Services_Openstreetmap
 * @author    Ken Guest <kguest@php.net>
 * @copyright 2010 Ken Guest
 * @license   BSD http://www.opensource.org/licenses/bsd-license.php
 * @version   Release: 0.0.1
 * @link      http://pear.php.net/package/Services_Openstreetmap
 */
class Services_Openstreetmap
{
    /**
     * server to connect to
     * @var string
     * @internal
     */
    protected $server = 'http://www.openstreetmap.org/';

    /**
     * Version of the [OSM] API which communications will be over
     * @var string
     * @internal
     */
    protected $api_version = '0.6';

    /**
     * Minimum version of the OSM API that is supported.
     * @var float
     * @internal
     */
    protected $minVersion = null;

    /**
     * Maximum version of the OSM API that is supported.
     * @var float
     * @internal
     */
    protected $maxVersion = null;

    /**
     * timeout
     * @var integer
     * @internal
     */
    protected $timeout = null;

    /**
     * number of elements allowed per changeset
     * @var integer
     * @internal
     */
    protected $changeset_maximum_elements = null;

    /**
     * maximum number of nodes per way
     * @var integer
     * @internal
     */
    protected $waynodes_maximum = null;

    /**
     * Number of tracepoints per way.
     * @var integer
     * @internal
     */
    protected $tracepoints_per_page = null;

    /**
     * Max size of area that can be downloaded in one request.
     * @var float
     * @internal
     */
    protected $area_maximum = null;

    /**
     * Default config settings
     * @var array
     */
    protected $config = array(
        'adapter'      => 'HTTP_Request2_Adapter_Socket',
        'api_version'  => '0.6',
        'password'     => null,
        'passwordfile' => null,
        'server'       => 'http://www.openstreetmap.org/',
        'User-Agent'   => 'Services_Openstreetmap',
        'user'         => null,
        'verbose'      => false,
    );

    /**
     * [Retrieved] XML
     * @var string
     * @internal
     */
    protected $xml = null;

    /**
     * Elements supported by the API (v0.6).
     * Used for validation purposes.
     * @var array
     * @internal
     */
    protected $elements = array('changeset', 'node', 'relation', 'way');

    /**
     * Counter for assigning IDs to [newly] created objects.
     * @var int
     * @internal
     */
    protected $newId = -1;

    /**
     * autoloader
     *
     * @param string $class Name of class
     *
     * @return boolean
     */
    public static function autoload($class)
    {
        $dir  = dirname(dirname(__FILE__));
        $file = $dir . '/' . str_replace('_', '/', $class) . '.php';
        if (file_exists($file)) {
            return include_once $file;
        }
        return false;
    }

    /**
     * constructor; which optionally sets config details.
     *
     * @param array $config Defaults to empty array if none provided
     *
     * @access protected
     * @return void
     */
    function __construct($config = array())
    {
        if ($config == array()) {
            $config = $this->config;
        }
        $this->setConfig($config);
    }

    /**
     * getXMLValue
     *
     * @param SimpleXMLElement $xml       Object
     * @param string           $tag       name of tag
     * @param string           $attribute name of attribute
     * @param mixed            $default   default value
     *
     * @return void
     */
    public function getXMLValue(
        SimpleXMLElement $xml,
        $tag,
        $attribute,
        $default = null
    ) {
        $obj = $xml->xpath('//' . $tag);
        if (empty($obj)) {
            return $default;
        }
        return $obj[0]->attributes()->$attribute;
    }

    /**
     * set at least one configuration variable.
     *
     * <pre>
     * $osm->setConfig('user', 'fred@example.com');
     * $osm->setConfig(array('user' => 'fred@example.com', 'password' => 'Simples'));
     * $osm->setConfig('user' => 'f@example.com')->setConfig('password' => 'Sis');
     * </pre>
     *
     * The following parameters are available:
     * <ul>
     *  <li> 'adapter'      - adapter to use (string)</li>
     *  <li> 'api_version'  - Version of API to communicate via (string)</li>
     *  <li> 'password'     - password (string, optional)</li>
     *  <li> 'passwordfile' - passwordfile (string, optional)</li>
     *  <li> 'server'       - server to connect to (string)</li>
     *  <li> 'User-Agent'   - User-Agent (string)</li>
     *  <li> 'user'         - user (string, optional)</li>
     *  <li> 'verbose'      - verbose (boolean, optional)</li>
     * </ul>
     *
     * @param mixed $config array containing config settings
     * @param mixed $value  config value if $config is not an array
     *
     * @throws Services_Openstreetmap_Exception If the parameter is unknown
     *
     * @access public
     * @return void
     */
    function setConfig($config, $value = null)
    {
        if (is_array($config)) {
            if (isset($config['adapter'])) {
                $this->config['adapter'] = $config['adapter'];
            }
            foreach ($config as $key=>$value) {
                if (!array_key_exists($key, $this->config)) {
                    throw new Services_Openstreetmap_Exception(
                        "Unknown config parameter '$key'"
                    );
                }
                switch($key) {
                case 'server':
                    $this->setServer($value);
                    break;
                case 'passwordfile':
                    $this->setPasswordfile($value);
                    break;
                default:
                    $this->config[$key] = $value;
                }
            }
        } else {
            if (!array_key_exists($config, $this->config)) {
                throw new Services_Openstreetmap_Exception(
                    "Unknown config parameter '$config'"
                );
            }
            $this->config[$config] = $value;
            if ($config == 'server') {
                $this->setServer($this->server);
            } elseif ($config == 'passwordfile') {
                $this->setPasswordfile($value);
            }
        }
        return $this;
    }

    /**
     * Get the value of a configuration setting - if none is set all are
     * returned.
     *
     * <code>
     * $config = $osm->getConfig();
     * </code>
     *
     * @param string $name name. optional.
     *
     * @return mixed  value of $name parameter, array of all configuration
     *                parameters if $name is not given
     * @throws Services_Openstreetmap_Exception If the parameter is unknown
     */
    public function getConfig($name = null)
    {
        if ($name === null) {
            return $this->config;
        } elseif (!array_key_exists($name, $this->config)) {
            throw new Services_Openstreetmap_Exception(
                "Unknown config parameter '$name'"
            );
        }
        return $this->config[$name];
    }


    /**
     * Convert a 'bbox' ordered set of coordinates to ordering required for get
     * method.
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * $osm->get($osm->bboxToMinMax($minLat, $minLon, $maxLat, $maxLon));
     * file_put_contents("area_covered.osm", $osm->getXML());
     * </code>
     *
     * @param mixed $minLat min Latitude
     * @param mixed $minLon min Longitude
     * @param mixed $maxLat max Latitude
     * @param mixed $maxLon max Longitude
     *
     * @access public
     * @return array
     */
    function bboxToMinMax($minLat, $minLon, $maxLat, $maxLon)
    {
        return array($minLon, $minLat, $maxLon, $maxLat);
    }

    /**
     * Get XML describing area prescribed by the given co-ordinates.
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * $osm->get(-8.3564758, 52.821022799999994, -7.7330017, 53.0428644);
     * file_put_contents("area_covered.osm", $osm->getXML());
     * </code>
     *
     * @param string $minLon min Longitude (leftmost point)
     * @param string $minLat min Latitude (bottom point)
     * @param string $maxLon max Longitude (rightmost point)
     * @param string $maxLat max Latitude (top point)
     *
     * @return void
     */

    function get($minLon, $minLat, $maxLon, $maxLat)
    {
        $url = $this->config['server'] .
            "api/" .
            $this->config['api_version'] .
        "/map?bbox=$minLat,$minLon,$maxLat,$maxLon";
        $response = $this->getResponse($url);
        $this->xml = $response->getBody();
    }

    /**
     * Get co-ordinates of some named place
     *
     * <code>
     * $coords = $osm->getCoordsOfPlace('Limerick, Ireland');
     * </code>
     *
     * @param string $place name
     *
     * @access public
     * @return array
     */
    function getCoordsOfPlace($place)
    {
        $url = 'http://nominatim.openstreetmap.org/search?q='
             . urlencode($place) . '&limit=1&format=xml';
        $response = $this->getResponse($url);
        $xml = simplexml_load_string($response->getBody());
        $obj = $xml->xpath('//place');
        $attrs = $xml->named[0];
        $attrs = $obj[0]->attributes();
        $lat = (string) $attrs['lat'];
        $lon = (string) $attrs['lon'];
        return compact("lat", "lon");
    }

    /**
     * Get details of specified node
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * var_dump($osm->getNode(52245107));
     * </code>
     *
     * @param string $nodeID  nodeID
     * @param mixed  $version [optional] version of node
     *
     * @access public
     * @return string
     */
    function getNode($nodeID, $version = null)
    {
        return $this->_getObject('node', $nodeID, $version);
    }

    /**
     * Return an array of specified nodes.
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * var_dump($osm->getNodes(52245107, 52245108));
     * </code>
     * Or
     * <code>
     * $osm = new Services_Openstreetmap();
     * var_dump($osm->getNodes(array(52245107, 52245108)));
     * </code>
     *
     * @return Services_Openstreetmap_Nodes
     */
    public function getNodes()
    {
        return $this->_getObjects('node', $this->_getIDs(func_get_args()));
    }

    /**
     * Set various properties to describe the capabilities that the connected
     * server supports.
     *
     * @param mixed $capabilities XML describing the capabilities of the server
     *
     * @see maxVersion
     * @see minVersion
     * @see timeout
     *
     * @return void
     *
     * @internal
     * @throws   Services_Openstreetmap_Exception If the API Version is not
     *                                            supported.
     */
    private function _checkCapabilities($capabilities)
    {
        $xml = simplexml_load_string($capabilities);
        if ($xml === false) {
            return false;
        }

        $this->minVersion = (float) $this->getXMLValue($xml, 'version', 'minimum');
        $this->maxVersion = (float) $this->getXMLValue($xml, 'version', 'maximum');
        if (($this->minVersion > $this->api_version
            || $this->api_version > $this->maxVersion)
        ) {
            throw new Services_Openstreetmap_Exception(
                'Specified API Version ' . $this->api_version .' not supported.'
            );
        }
        $this->timeout = (int) $this->getXMLValue($xml, 'timeout', 'seconds');
        //changesets
        $this->changeset_maximum_elements = (int) $this->getXMLValue(
            $xml,
            'changesets',
            'maximum_elements'
        );

        // Maximum number of nodes per way.
        $this->waynodes_maximum = (int) $this->getXMLValue(
            $xml,
            'waynodes',
            'maximum'
        );

        // Number of tracepoints per way.
        $this->tracepoints_per_page = (int) $this->getXMLValue(
            $xml,
            'tracepoints',
            'per_page'
        );

        // Max size of area that can be downloaded in one request.
        $this->area_maximum = (float) $this->getXMLValue(
            $xml,
            'area',
            'maximum'
        );
        return true;
    }

    /**
     * Given the results of a call to func_get_args return an array of unique
     * valid IDs specified in those results (either 1 per argument or each
     * argument containing an array of IDs).
     *
     * @param mixed $args results of call to func_get_args
     *
     * @return array
     *
     * @internal
     */
    private function _getIDs($args)
    {
        $IDs = array();
        foreach ($args as $arg) {
            if (is_array($arg)) {
                $IDs = array_merge($arg, $IDs);
            } elseif (is_numeric($arg)) {
                $IDs[] = $arg;
            }
        }
        return array_unique($IDs);
    }

    /**
     * _getObject
     *
     * Returns false if the object is not found
     *
     * @param string $type    object type
     * @param mixed  $id      id of object to retrieve
     * @param mixed  $version version of object
     *
     * @return object
     * @throws Services_Openstreetmap_Exception
     *
     * @internal
     */
    private function _getObject($type, $id, $version = null)
    {
        if (!in_array($type, $this->elements)) {
            throw new Services_Openstreetmap_Exception(
                sprintf("Invalid Element Type '%s'", $type)
            );
        }

        $url = $this->getConfig('server')
            . 'api/'
            . $this->getConfig('api_version')
            . '/' . $type . '/'
            . $id;
        if ($version !== null) {
            $url .= "/$version";
        }
        try {
            $response = $this->getResponse($url);
        } catch (Services_Openstreetmap_Exception $ex) {
            $code = $ex->getCode();
            if (404 == $code) {
                return false;
            } elseif (410 == $code) {
                return false;
            } else {
                throw $ex;
            }
        }
        $class =  "Services_Openstreetmap_" . ucfirst(strtolower($type));
        $obj = new $class();
        $obj->setXml($response->getBody());
        return $obj;
    }

    /**
     * _getObjects
     *
     * @param string $type object type
     * @param array  $ids  ids of objects to retrieve
     *
     * @return void
     *
     * @internal
     */
    private function _getObjects($type, array $ids)
    {
        if (!in_array($type, $this->elements)) {
            throw new Services_Openstreetmap_Exception('Invalid Element Type');
        }
        $url = $this->getConfig('server')
            . 'api/'
            . $this->getConfig('api_version')
            . '/' . $type . 's?' . $type . 's='
            . implode(',', $ids);
        try {
            $response = $this->getResponse($url);
        } catch (Services_Openstreetmap_Exception $ex) {
            $code = $ex->getCode();
            if (404 == $code || 401 == $code) {
                return false;
            } elseif (410 == $code) {
                return false;
            } else {
                throw $ex;
            }
        }
        $class = 'Services_Openstreetmap_' . ucfirst(strtolower($type)) . 's';
        $obj = new $class();
        $obj->setXml($response->getBody());
        return $obj;
    }

    /**
     * Get details of specified way
     *
     * @param mixed $wayID   wayID
     * @param mixed $version [optional] version of way
     *
     * @access public
     * @return string
     */
    public function getWay($wayID, $version = null)
    {
        return $this->_getObject('way', $wayID, $version);
    }

    /**
     * Return an array of specified ways.
     *
     * <pre>
     * $ways = $osm->getWays($wayId, $way2Id);
     * </pre>
     *
     * @return array
     */
    public function getWays()
    {
        return $this->_getObjects('way', $this->_getIDs(func_get_args()));
    }

    /**
     * Get details of specified relation, optionally specify which version of
     * the relation to be retrieved.
     *
     * <pre>
     * $r = $osm->getRelation(1234567);
     * $r = $osm->getRelation(1234567, 2);
     * </pre>
     *
     * @param mixed $relationID ID of relation
     * @param mixed $version    [optional] version of relation
     *
     * @access public
     * @return string
     */
    function getRelation($relationID, $version = null)
    {
        return $this->_getObject('relation', $relationID, $version);
    }

    /**
     * Return an array of specified relations
     *
     * <pre>
     * $relations = $osm->getRelations($relationId, $relation2Id);
     * </pre>
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->_getObjects('relation', $this->_getIDs(func_get_args()));
    }

    /**
     * Get details of specified changeset
     *
     * <code>
     * $changeset = $osm->getChangeset(123456);
     * </code>
     *
     * @param string $id      numeric ID of changeset
     * @param string $version optional
     *
     * @access public
     * @return string
     */
    function getChangeset($id, $version = null)
    {
        return $this->_getObject('changeset', $id, $version);
    }

    /**
     * Retrieve all versions of a specified element
     *
     * @param string $type Any one of the supported element types
     * @param string $id   numeric Id of element
     *
     * @access public
     * @return string
     * @throws Services_Openstreetmap_Exception If the element type is unknown
     */
    function getHistory($type, $id)
    {
        if (!in_array($type, $this->elements)) {
            throw new Services_Openstreetmap_Exception('Invalid Element Type');
        }

        $url = $this->getConfig('server')
            . 'api/'
            . $this->getConfig('api_version')
            . "/$type/$id/history";
        $response = $this->getResponse($url);
        return $response->getBody();
    }

    /**
     * Send request to OSM server and return the response.
     *
     * @param string $url       URL
     * @param string $method    GET (default)/POST/PUT
     * @param string $user      user (optional for read-only actions)
     * @param string $password  password (optional for read-only actions)
     * @param string $body      body (optional)
     * @param array  $post_data (optional)
     * @param array  $headers   (optional)
     *
     * @access public
     * @return HTTP_Request2_Response
     * @throws  Services_Openstreetmap_Exception If something unexpected has
     *                                           happened while conversing with
     *                                           the server.
     */
    function getResponse(
        $url,
        $method = HTTP_Request2::METHOD_GET,
        $user = null,
        $password = null,
        $body = null,
        array $post_data = null,
        array $headers = null
    ) {
        $response = null;
        $eMsg = null;

        if ($this->getConfig('verbose')) {
            echo $url, "\n";
        }
        $request = new HTTP_Request2(
            $url,
            $method,
            array('adapter' => $this->getConfig('adapter'))
        );

        $request->setHeader('User-Agent', $this->getConfig('User-Agent'));
        if ($user !== null && $password !== null) {
            $request->setAuth($user, $password);
        }
        if ($post_data != array()) {
            $request->setMethod(HTTP_Request2::METHOD_POST);
            foreach ($post_data as $key => $value) {
                $request->addPostParameter($key, $value);
            }
        }
        if ($headers != array()) {
            foreach ($headers as $header) {
                $request->setHeader($header[0], $header[1], $header[2]);
            }
        }
        if ($body !== null) {
            $request->setBody($body);
        }
        $code = 0;
        try {
            $response = $request->send();
            $code = $response->getStatus();

            if ($this->getConfig('verbose')) {
                var_dump($response->getHeader());
                var_dump($response->getBody());
            }

            if (200 == $code) {
                return $response;
            } else {
                $eMsg = 'Unexpected HTTP status: '
                    . $code . ' '
                    . $response->getReasonPhrase();
                $error = $response->getHeader('error');
                if (!is_null($error)) {
                    $eMsg .= " ($error)";
                }

            }
        } catch (HTTP_Request2_Exception $e) {
            throw new Services_Openstreetmap_Exception(
                $e->getMessage(),
                $code,
                $e
            );
        }
        if ($eMsg != null) {
            throw new Services_Openstreetmap_Exception($eMsg, $code);
        }
    }

    /**
     * Load XML from [cache] file.
     *
     * @param string $file filename
     *
     * @access public
     * @return void
     */
    function loadXML($file)
    {
        $this->xml = file_get_contents($file);
    }

    /**
     * return XML.
     *
     * @access public
     * @return string
     */
    function getXML()
    {
        return $this->xml;
    }


    /**
     * Set and parse a password file, setting username and password as specified
     * in the file.
     *
     * A password file is a ASCII text file, with username and passwords pairs
     * on each line, seperated [delimited] by a semicolon.
     * Lines starting with a hash [#] are comments.
     * If only one non-commented line is present in the file, that username and
     * password will be used for authentication.
     * If more than one set of usernames and passwords are present, the
     * username must be specified, and the matching password from the file will
     * be used.
     *
     * <pre>
     * # Example password file.
     * fredfs@example.com:Wilma4evah
     * barney@example.net:B3ttyRawks
     * </pre>
     *
     * @param string $file file containing credentials
     *
     * @access public
     * @return Services_Openstreetmap
     */
    function setPasswordfile($file)
    {
        if (is_null($file)) {
            return $this;
        }
        $lines = @file($file);
        if ($lines === false) {
            throw new Services_Openstreetmap_Exception(
                'Could not read password file'
            );
        }
        $this->config['passwordfile'] =  $file;
        array_walk($lines, create_function('&$val', '$val = trim($val);'));
        if (sizeof($lines) == 1) {
            if (strpos($lines[0], '#') !== 0) {
                list($this->config['user'], $this->config['password'])
                    = explode(':', $lines[0]);
            }
        } elseif (sizeof($lines) == 2) {
            if (strpos($lines[0], '#') === 0) {
                if (strpos($lines[1], '#') !== 0) {
                    list($this->config['user'], $this->config['password'])
                        = explode(':', $lines[1]);
                }
            }
        } else {
            foreach ($lines as $line) {
                if (strpos($line, '#') === 0) {
                    continue;
                }
                list($user, $pwd) = explode(':', $line);
                if ($user == $this->config['user']) {
                    $this->config['password'] = $pwd;
                }
            }
        }
        return $this;
    }

    /**
     * Connect to specified server.
     *
     * @param string $server base server details, e.g. http://www.openstreetmap.org
     *
     * @access public
     * @return Services_Openstreetmap
     */
    function setServer($server)
    {
        try {
            $c = $this->getResponse($server . 'api/capabilities');
        } catch (Exception $ex) {
            throw new Services_Openstreetmap_Exception(
                'Could not get a valid response from server',
                $ex->getCode(),
                $ex
            );
        }
        $this->server = $server;
        $capabilities = $c->getBody();
        if (!$this->_checkCapabilities($capabilities)) {
            throw new Services_Openstreetmap_Exception(
                'Problem checking server capabilities'
            );
        }

        return $this;
    }

    /**
     * search based on given criteria
     *
     * <code>
     *  $osm = new Services_Openstreetmap();
     *
     *  $osm->loadXML("./osm.osm");
     *  $results = $osm->search(array("amenity" => "pharmacy"));
     *  echo "List of Pharmacies\n";
     *  echo "==================\n\n";
     *
     *  foreach ($results as $result) {
     *      $name = null;
     *      $addr_street = null;
     *      $addr_city = null;
     *      $addr_country = null;
     *      $addr_housename = null;
     *      $addr_housenumber = null;
     *      $opening_hours = null;
     *      $phone = null;
     *
     *      extract($result);
     *      $line1 = ($addr_housenumber) ? $addr_housenumber : $addr_housename;
     *      if ($line1 != null) {
     *          $line1 .= ', ';
     *      }
     *      echo  "$name\n{$line1}{$addr_street}\n$phone\n$opening_hours\n\n";
     *  }
     * </code>
     *
     * @param mixed $criteria what to search for
     *
     * @access public
     * @return array
     */
    function search($criteria)
    {
        $results = array();

        $xml = simplexml_load_string($this->xml);
        if ($xml === false) {
            return array();

        }
        foreach ($criteria as $key => $value) {
            foreach ($xml->xpath('//way') as $node) {
                foreach ($node->tag as $tag) {
                    if (($tag['k'] == $key) && ($tag['v'] == $value)) {
                        $results[] = $node;
                    }
                }
            }
            foreach ($xml->xpath('//node') as $node) {
                foreach ($node->tag as $tag) {
                    if (($tag['k'] == $key) && ($tag['v'] == $value)) {
                        $results[] = $node;
                    }
                }
            }
        }
        $ares = array();
        foreach ($results as $resultnode) {
            $ar = array();
            foreach ($resultnode->tag as $tag) {
                $ar[str_replace(':', '_', $tag['k'])] = (string) $tag['v'];
            }
            $ares[] = $ar;
            unset($ar); //ensure $ar is wiped clean for each iteration
        }
        return $ares;
    }


    /**
     * Return the number of seconds that must elapse before a connection is
     * considered to have timed-out.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * minVersion - min API version supported by connected server.
     *
     * <code>
     * $config = array('user' => 'fred@example.net', 'password' => 'wilma4eva');
     * $osm = new Services_Openstreetmap($config);
     * $min = $osm->getMinVersion();
     * </code>
     *
     * @return float
     */
    public function getMinVersion()
    {
        return $this->minVersion;
    }

    /**
     * maxVersion - max API version supported by connected server.
     *
     * <code>
     * $config = array('user' => 'fred@example.net', 'password' => 'wilma4eva');
     * $osm = new Services_Openstreetmap($config);
     * $max = $osm->getMaxVersion();
     * </code>
     *
     * @return float
     */
    public function getMaxVersion()
    {
        return $this->maxVersion;
    }

    /**
     * Max size of area that can be downloaded in one request.
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * $area_allowed = $osm->getMaxArea();
     * </code>
     *
     * @return float
     */
    public function getMaxArea()
    {
        return $this->area_maximum;
    }

    /**
     * Maximum number of tracepoints per page.
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * $tracepoints = $osm->getTracepointsPerPage();
     * </code>
     *
     * @return float
     */
    public function getTracepointsPerPage()
    {
        return $this->tracepoints_per_page;
    }

    /**
     * Maximum number of nodes per way.
     *
     * Anymore than that and the way must be split.
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * $max = $osm->getMaxNodes();
     * </code>
     *
     * @return float
     */
    public function getMaxNodes()
    {
        return $this->waynodes_maximum;
    }

    /**
     * Number of elements allowed per changeset
     *
     * <code>
     * $osm = new Services_Openstreetmap();
     * $max = $osm->getMaxElements();
     * </code>
     *
     * @return float
     */
    public function getMaxElements()
    {
        return $this->changeset_maximum_elements;
    }

    /**
     * Create a changeset, used to transmit changes (creation, updates, deletion)
     * to the server. Username and password must be set.
     *
     * <code>
     * $config = array('user' => 'fred@example.net', 'password' => 'wilma4eva');
     * $osm = new Services_Openstreetmap($config);
     * $changeset = $osm->createChangeset();
     * </code>
     *
     * @param boolean $atomic atomic changeset?
     *
     * @return Services_Openstreetmap_Changeset
     * @see setConfig
     */
    public function createChangeset($atomic = true)
    {
        $changeset = new Services_Openstreetmap_Changeset($atomic);
        $changeset->_osm = $this;
        return $changeset;
    }

    /**
     * Create and return a Services_Openstreetmap_Node
     *
     * <code>
     * $node = $osm->createNode($lat, $lon, array('building' => 'yes'));
     * </code>
     *
     * @param float $latitude  Latitude of node
     * @param float $longitude Longitude of node
     * @param array $tags      Array of key->value tag pairs.
     *
     * @return Services_Openstreetmap_Node
     */
    public function createNode($latitude, $longitude, array $tags = array())
    {
        $node = new Services_Openstreetmap_Node();
        $api_version = $this->getConfig('api_version');
        $user_agent =  $this->getConfig('User-Agent');
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
<osm version='{$api_version}' generator='{$user_agent}'>
<node lat='{$latitude}' lon='{$longitude}' version='1'/></osm>";
        $node->setLat($latitude);
        $node->setLon($longitude);
        $node->setXml($xml);
        $node->setId($this->newId--);
        $node->setTag('created_by', $user_agent);
        if (!empty($tags)) {
            foreach ($tags as $key=>$value) {
                $node->setTag($key, $value);
            }
        }
        return $node;
    }

    /**
     * Get a Services_Openstreetmap_User object for the [current] user.
     *
     * @see setConfig
     *
     * @return Services_Openstreetmap_User
     */
    public function getUser()
    {
        $url = $this->config['server'] .
            "api/" .
            $this->config['api_version'] .
            "/user/details";
        $user = $this->getConfig('user');
        $password = $this->getConfig('password');
        try {
            $response = $this->getResponse(
                $url,
                HTTP_Request2::METHOD_GET,
                $user,
                $password
            );
        } catch (Services_Openstreetmap_Exception $ex) {
            $code = $ex->getCode();
            if (404 == $code || 401 == $code) {
                return false;
            } elseif (410 == $code) {
                return false;
            } else {
                throw $ex;
            }
        }
        $url = $this->config['server'] .
            "api/" .
            $this->config['api_version'] .
            "/user/preferences";
        $user = $this->getConfig('user');
        $password = $this->getConfig('password');
        try {
            $prefs = $this->getResponse(
                $url,
                HTTP_Request2::METHOD_GET,
                $user,
                $password
            );
        } catch (Services_Openstreetmap_Exception $ex) {
            $code = $ex->getCode();
            if (404 == $code || 401 == $code) {
                return false;
            } elseif (410 == $code) {
                return false;
            } else {
                throw $ex;
            }
        }
        $obj = new Services_Openstreetmap_User();
        $obj->setXml($response->getBody());
        $obj->setPreferencesXml($prefs->getBody());
        return $obj;
    }
}
// vim:set et ts=4 sw=4:
?>
