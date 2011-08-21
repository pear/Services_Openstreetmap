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
 * @version  SVN: $$
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

    protected $minVersion = null;

    protected $maxVersion = null;

    /**
     * [Retrieved] XML
     * @var string
     */
    protected $xml = null;

    /**
     * Default config settings
     * @var array
     */
    protected $config = array(
        'server'      => 'http://www.openstreetmap.org/',
        'api_version' => '0.6',
        'User-Agent'  => 'Services_Openstreetmap',
        'adapter'     => 'HTTP_Request2_Adapter_Socket',
    );

    /**
     * Supported elements
     * @var array
     */
    protected $elements = array('node', 'way', 'relation');

    /**
     * timeout
     */
    protected $timeout = null;

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
     *  <li> 'server'      - server to connect to (string)</li>
     *  <li> 'api_version' - Version of API to communicate via (string)</li>
     *  <li> 'User-Agent'  - User-Agent (string)</li>
     *  <li> 'adapter'     - adapter to use (string)</li>
     * </ul>
     *
     * @param mixed $config array containing config settings
     *
     * @access public
     * @return void
     */
    function setConfig($config, $value = null)
    {
        $server_set = false;
        if (is_array($config)) {
            foreach ($config as $key=>$value) {
                if (!array_key_exists($key, $this->config)) {
                    throw new Services_Openstreetmap_Exception("Unknown config");
                }
                switch($key){
                case 'server':
                    $this->setServer($value);
                    $server_set = true;
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
                $server_set = true;
            }
        }
        if (!$server_set) {
            $this->setServer($this->server);
        }
    }

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
     * _getObject
     *
     * @param string $type    object type
     * @param mixed  $id      id of object to retrieve
     * @param mixed  $version version of object
     *
     * @return void
     */
    private function _getObject($type, $id, $version)
    {
        $url = $this->config['server']
            . 'api/'
            . $this->config['api_version']
            . '/' . $type . '/'
            . $id;
        if ($version !== null) {
            $url .= "/$version";
        }
        $r = $this->getResponse($url);
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
     */
    function getHistory($type, $id)
    {
        if (!in_array($type, $this->elements)) {
            throw new Services_Openstreetmap_Exception('Invalid Element Type');
        }

        $url = $this->config['server']
            . 'api/'
            . $this->config['api_version']
            . "/$type/$id/history";
        $r = $this->getResponse($url);
        return $r->getBody();
    }

    /**
     * Send request to OSM server and return the response.
     *
     * @param string $url URL
     *
     * @access public
     * @return HTTP_Request2_Response
     */
    function getResponse($url)
    {
        $response = null;
        $eMsg = null;

        $request = new HTTP_Request2(
            $url,
            HTTP_Request2::METHOD_GET,
            array('adapter' => $this->config['adapter'])
        );

        $request->setHeader('User-Agent', $this->config['User-Agent']);
        $status = 0;
        try {
            $response = $request->send();
            $status = $response->getStatus();
            if (200 == $status) {
                return $response;
            } else {
                $eMsg = 'Unexpected HTTP status: '
                    . $status . ' '
                    . $response->getReasonPhrase();
            }
        } catch (HTTP_Request2_Exception $e) {
            throw new Services_Openstreetmap_Exception($e->getMessage(), $status, $e);
        }
        if ($eMsg != null) {
            throw new Services_Openstreetmap_Exception($eMsg);
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
     * Connect to specified server.
     *
     * @param mixed $server base server details
     *
     * @access public
     * @return void
     */
    function setServer($server)
    {
        $this->server = $server;
        $c = $this->getResponse($server . '/api/capabilities');
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
                "Specified API Version {$this->api_version} not supported."
            );
        }
        $v = $xml->xpath('//timeout');
        $this->timeout = (int) $v[0]->attributes()->seconds;
        //changesets
        //waynodes
        //tracepoints
        //max area
    }
}
// vim:set et ts=4 sw=4:
?>
