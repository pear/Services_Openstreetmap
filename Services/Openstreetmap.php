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
     * Default config settings
     *
     * @var Services_Openstreetmap_Config
     * @see Services_Openstreetmap::getConfig
     * @see Services_Openstreetmap::setConfig
     */
    protected $config = null;

    /**
     * [Retrieved] XML
     * @var string
     * @internal
     */
    protected $xml = null;

    protected $transport = null;

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
        $this->getConfig()->setTransport($this->getTransport());
        if (!is_null($config)) {
            $this->getConfig()->setValue($config);
        }
        $version = $this->getConfig()->getValue('api_version');
        $api = "Services_Openstreetmap_API_V" . str_replace('.', '', $version);
        $this->api = new $api;
        $this->api->setTransport($this->getTransport());
        $this->api->setConfig($this->getConfig());
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
        $config = $this->getConfig();
        $url = $config->getValue('server')
            . 'api/'
            . $config->getValue('api_version')
             . "/map?bbox=$minLat,$minLon,$maxLat,$maxLon";
        $response = $this->getTransport()->getResponse($url);
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
        $response = $this->getTransport()->getResponse($url);
        $xml = simplexml_load_string($response->getBody());
        $obj = $xml->xpath('//place');
        $attrs = $xml->named[0];
        $attrs = $obj[0]->attributes();
        $lat = (string) $attrs['lat'];
        $lon = (string) $attrs['lon'];
        return compact('lat', 'lon');
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
     */
    public static function getIDs($args)
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
     * search based on given criteria.
     *
     * returns an array of objects such as Services_Openstreetmap_Node etc.
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
     *      $name = $result->getTag('name');
     *      $addr_street = $result->getTag('addr:street');
     *      $addr_city = $result->getTag('addr:city');
     *      $addr_country = $result->getTag('addr:country');
     *      $addr_housename = $result->getTag('addr:housename');
     *      $addr_housenumber = $result->getTag('addr:housenumber');
     *      $opening_hours = $result->getTag('opening_hours');
     *      $phone = $result->getTag('phone');
     *
     *      $line1 = ($addr_housenumber) ? $addr_housenumber : $addr_housename;
     *      if ($line1 != null) {
     *          $line1 .= ', ';
     *      }
     *      echo  "$name\n{$line1}{$addr_street}\n$phone\n$opening_hours\n\n";
     *  }
     * </code>
     *
     * @param array $criteria what to search for
     *
     * @access public
     * @return array
     */
    public function search(array $criteria)
    {
        $results = array();

        $xml = simplexml_load_string($this->xml);
        if ($xml === false) {
            return array();
        }
        foreach ($criteria as $key => $value) {
            foreach ($xml->xpath('//way') as $node) {
                $results = array_merge(
                    $results,
                    $this->_searchNode($node, $key, $value, 'way')
                );
            }
            foreach ($xml->xpath('//node') as $node) {
                $results = array_merge(
                    $results,
                    $this->_searchNode($node, $key, $value, 'node')
                );
            }
        }
        return $results;
    }

    /**
     * Search node for a specific key/value pair, allowing for value to be
     * included in a semicolon delimited list.
     *
     * @param SimpleXMLElement $node  Node to search
     * @param string           $key   Key to search for (Eg 'amenity')
     * @param string           $value Value to search for (Eg 'pharmacy')
     * @param string           $type  Type of object to return.
     *
     * @return array
     */
    private function _searchNode(SimpleXMLElement $node, $key, $value, $type)
    {
        $class =  'Services_Openstreetmap_' . ucfirst(strtolower($type));
        $results = array();
        foreach ($node->tag as $tag) {
            if ($tag['k'] == $key) {
                if ($tag['v'] == $value) {
                    $obj = new $class();
                    $obj->setTransport($this->getTransport());
                    $obj->setConfig($this->getConfig());
                    $obj->setXml(simplexml_load_string($node->saveXML()));
                    $results[] = $obj;
                } elseif (strpos($tag['v'], ';')) {
                    $array = explode(';', $tag['v']);
                    if (in_array($value, $array)) {
                        $obj = new $class();
                        $obj->setTransport($this->getTransport());
                        $obj->setConfig($this->getConfig());
                        $obj->setXml(simplexml_load_string($node->saveXML()));
                        $results[] = $obj;
                    }
                }
            }
        }
        return $results;
    }

    /**
     * Return the number of seconds that must elapse before a connection is
     * considered to have timed-out.
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->getConfig()->getTimeout();
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
        return $this->getConfig()->getMinVersion();
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
        return $this->getConfig()->getMaxVersion();
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
        return $this->getConfig()->getMaxArea();
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
        return $this->getConfig()->getTracepointsPerPage();
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
        return $this->getConfig()->getMaxNodes();
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
        return $this->getConfig()->getMaxElements();
    }

    /**
     * Set Config object
     *
     * @param Services_Openstreetmap_Config $config Config settings.
     *
     * @return Services_Openstreetmap_API_V06
     */
    public function setConfig(Services_Openstreetmap_Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get current Config object
     *
     * @return Services_Openstreetmap_Config
     */
    public function getConfig()
    {
        if (is_null($this->config)) {
            $config = new Services_Openstreetmap_Config();
            $this->config = $config;
        }
        return $this->config;
    }

    /**
     * Get current Transport object.
     *
     * If one is not defined, create it.
     *
     * @return Services_Openstreetmap_Transport
     */
    public function getTransport()
    {
        if (is_null($this->transport)) {
            $transport = new Services_Openstreetmap_Transport();
            $transport->setConfig($this->getConfig());
            $this->transport = $transport;

        }
        return $this->transport;
    }

    /**
     * __call
     *
     * If possible, call the appropriate method of the API instance.
     *
     * @param string $name      Name of missing method to call.
     * @param array  $arguments Arguments to be used when calling method.
     *
     * @return void
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this->api, $name)) {
            return call_user_func_array(array($this->api, $name), $arguments);
        } else {
            throw new Services_Openstreetmap_Exception(
                sprintf(
                    'Method %s does not exist.',
                    $name
                )
            );
        }
    }
}
// vim:set et ts=4 sw=4:
?>
