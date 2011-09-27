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
require_once 'Services/Openstreetmap/Object.php';
require_once 'Services/Openstreetmap/Relation.php';
require_once 'Services/Openstreetmap/Changeset.php';
require_once 'Services/Openstreetmap/Node.php';
require_once 'Services/Openstreetmap/Way.php';
require_once 'Services/Openstreetmap/Exception.php';

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
     */
    protected $server = 'http://www.openstreetmap.org/';

    /**
     * Version of the API which communications will be over
     * @var string
     */
    protected $api_version = '0.6';

    /**
     * Minimum version of the API that is supported.
     * @var float
     */
    protected $minVersion = null;

    /**
     * Maximum version of the API that is supported.
     * @var float
     */
    protected $maxVersion = null;

    /**
     * timeout
     * @var integer
     */
    protected $timeout = null;

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
     */
    protected $xml = null;

    /**
     * Supported elements
     * @var array
     */
    protected $elements = array('node', 'way', 'relation');

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
     * set configuration
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
                    throw new Services_Openstreetmap_Exception("Unknown config");
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
    }

    /**
     * getConfig
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
     * get XML describing area prescribed by the given co-ordinates.
     *
     * @param string $minLon min Longitude (leftmost point)
     * @param string $minLat min Latitude (bottom point)
     * @param string $maxLon max Longitude (rightmost point)
     * @param string $maxLat max Latitude (top point)
     *
     * @access public
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
     * @param string $place name
     *
     * @access public
     * @return array
     */
    function getCoordsOfPlace($place)
    {
        $url = 'http://gazetteer.openstreetmap.org/namefinder/search.xml?find='
             . urlencode($place) . '&max=1';
        $response = $this->getResponse($url);
        $xml = simplexml_load_string($response->getBody());
        $attrs = $xml->named[0]->attributes();
        $lat = (string) $attrs['lat'];
        $lon = (string) $attrs['lon'];
        return compact("lat", "lon");
    }

    /**
     * Get details of specified node
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
     * return an array of specified nodes.
     *
     * @return void
     */
    public function getNodes()
    {
        $nodes = array();

        $IDs = $this->_getIDs(func_get_args());

        foreach ($IDs as $nodeID) {
            if (is_numeric($nodeID)) {
                $nodes[] = $this->_getObject('node', $nodeID);
            }
        }
        return $nodes;
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
     * @throws   Services_Openstreetmap_Exception If the API Version is not
     *                                            supported.
     */
    private function _checkCapabilities($capabilities)
    {
        $xml = simplexml_load_string($capabilities);
        if ($xml === false) {
            return;
        }
        $v = $xml->xpath('//version');
        $this->minVersion = (float) $v[0]->attributes()->minimum;
        $this->maxVersion = (float) $v[0]->attributes()->maximum;
        if (($this->minVersion > $this->api_version
            || $this->api_version > $this->maxVersion)
        ) {
            throw new Services_Openstreetmap_Exception(
                'Specified API Version ' . $this->api_version .' not supported.'
            );
        }
        $v = $xml->xpath('//timeout');
        $this->timeout = (int) $v[0]->attributes()->seconds;
        //changesets
        //waynodes
        //tracepoints
        //max area
    }

    /**
     * Given the results of a call to func_get_args return an array of unique
     * valid IDs specified in those results (either 1 per argument or each
     * argument containing an array of IDs).
     *
     * @param mixed $args results of call to func_get_args
     *
     * @return array
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
     */
    private function _getObject($type, $id, $version = null)
    {
        $url = $this->getConfig('server')
            . 'api/'
            . $this->getConfig('api_version')
            . '/' . $type . '/'
            . $id;
        if ($version !== null) {
            $url .= "/$version";
        }
        try {
            $r = $this->getResponse($url);
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
        $obj->setXml($r->getBody());
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
     * @return array
     */
    public function getWays()
    {
        $ways = array();

        $IDs = $this->_getIDs(func_get_args());

        foreach ($IDs as $wayID) {
            if (is_numeric($wayID)) {
                $ways[] = $this->_getObject('way', $wayID);
            }
        }
        return $ways;
    }

    /**
     * Get details of specified relation
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
     * @return array
     */
    public function getRelations()
    {
        $relations = array();

        $IDs = $this->_getIDs(func_get_args());

        foreach ($IDs as $relationID) {
            if (is_numeric($relationID)) {
                $relations[] = $this->_getObject('relation', $relationID);
            }
        }
        return $relations;
    }

    /**
     * Get details of specified changeset
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
        $r = $this->getResponse($url);
        return $r->getBody();
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
     * Load XML from [cache] file
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
     * return XML
     *
     * @access public
     * @return string
     */
    function getXML()
    {
        return $this->xml;
    }


    /**
     * Parse passwordfile, setting username and password as specified in the
     * file
     *
     * @param string $file file containing credentials
     *
     * @access public
     * @return void
     */
    function setPasswordfile($file)
    {
        if (is_null($file)) {
            return;
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
    }

    /**
     * Connect to specified server.
     *
     * @param mixed $server base server details
     *
     * @access public
     * @return void
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
        $this->_checkCapabilities($capabilities);
    }

    /**
     * search based on given criteria
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
     * Number of seconds
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
     * @return float
     */
    public function getMinVersion()
    {
        return $this->minVersion;
    }

    /**
     * maxVersion - max API version supported by connected server.
     *
     * @return float
     */
    public function getMaxVersion()
    {
        return $this->maxVersion;
    }

    /**
     * createChangeset
     *
     * @param boolean $atomic atomic changeset?
     *
     * @return Services_Openstreetmap_Changeset
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
<node lat='{$latitude}' lon='{$longitude}' version='1'/>
</osm>";
        $node->setXml($xml);
        if (!empty($tags)) {
            foreach ($tags as $key=>$value) {
                $node->setTag($key, $value);
            }
        }
        return $node;
    }
}
// vim:set et ts=4 sw=4:
?>
