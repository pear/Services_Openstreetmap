<?php
/**
 * V6.php
 * 08-Nov-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     V6.php
 */

/**
 * Services_Openstreetmap_API_V06
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     API_V06.php
 */
class Services_Openstreetmap_API_V06
{
    /**
     * Elements supported by the API (v0.6).
     * Used for validation purposes.
     * @var array
     * @internal
     */
    protected $elements = array('changeset', 'node', 'relation', 'way');

    protected $transport = null;

    protected $config = null;

    /**
     * Counter for assigning IDs to [newly] created objects.
     * @var int
     * @internal
     */
    protected $newId = -1;

    /**
     * Set Config object
     *
     * @param Services_Openstreetmap_Config $config Config object.
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
        return $this->config;
    }

    /**
     * Set the Transport instance.
     *
     * @param Services_Openstreetmap_Transport $transport Transport instance.
     *
     * @return Services_Openstreetmap_Config
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Retrieve the current Transport instance.
     *
     * @return Services_Openstreetmap_Transport.
     */
    public function getTransport()
    {
        return $this->transport;
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
        return $this->getTransport()->getObject('relation', $relationID, $version);
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
        return $this->getTransport()->getObjects(
            'relation',
            Services_Openstreetmap::getIDs(func_get_args())
        );
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
        return $this->getTransport()->getObject('changeset', $id, $version);
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
        $changeset->setTransport($this->getTransport());
        $changeset->setConfig($this->getConfig());
        return $changeset;
    }

    /**
     * searchChangesets
     *
     * @param array $criteria Array of Services_Openstreetmap_Criterion objects.
     *
     * @return Services_Openstreetmap_Changesets
     */
    public function searchChangesets(array $criteria)
    {
        $types = array();
        foreach ($criteria as $criterion) {
            $types[] = $criterion->type();
        }

        if (array_search('user', $types) !== false
            && array_search('display_name', $types) !== false
        ) {
            throw new InvalidArgumentException('Can\'t supply both user and display_name criteria');
        }

        return $this->getTransport()->searchObjects('changeset', $criteria);
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
        $config = $this->getConfig();
        $api_version = $config->getValue('api_version');
        $user_agent  = $config->getValue('User-Agent');
        $xml = "<?xml version='1.0' encoding='UTF-8'?>
<osm version='{$api_version}' generator='{$user_agent}'>
<node lat='{$latitude}' lon='{$longitude}' version='1'/></osm>";
        $node->setLat($latitude);
        $node->setLon($longitude);
        $node->setXml(simplexml_load_string($xml));
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
     * May return false if the user could not be found for any reason.
     *
     * @see setConfig
     *
     * @return Services_Openstreetmap_User
     */
    public function getUser()
    {
        $config = $this->getConfig()->asArray();
        $url = $config['server']
             . 'api/'
             . $config['api_version']
             . '/user/details';
        $user = $config['user'];
        $password = $config['password'];
        try {
            $response = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_GET,
                $user,
                $password
            );
        } catch (Services_Openstreetmap_Exception $ex) {
            switch ($ex->getCode()) {
            case Services_Openstreetmap_Transport::NOT_FOUND:
            case Services_Openstreetmap_Transport::UNAUTHORISED:
            case Services_Openstreetmap_Transport::GONE:
                return false;
            default:
                throw $ex;
            }
        }
        $url = $config['server'] . 'api/'
             . $config['api_version']
             . '/user/preferences';
        try {
            $prefs = $this->getTransport()->getResponse(
                $url,
                HTTP_Request2::METHOD_GET,
                $user,
                $password
            );
        } catch (Services_Openstreetmap_Exception $ex) {
            switch ($ex->getCode()) {
            case Services_Openstreetmap_Transport::NOT_FOUND:
            case Services_Openstreetmap_Transport::UNAUTHORISED:
            case Services_Openstreetmap_Transport::GONE:
                return false;
            default:
                throw $ex;
            }
        }
        $obj = new Services_Openstreetmap_User();
        $obj->setXml(simplexml_load_string($response->getBody()));
        $obj->setPreferencesXml($prefs->getBody());
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
        $way = $this->getTransport()->getObject('way', $wayID, $version);
        if ($way !== false) {
            $way->setTransport($this->getTransport());
            $way->setConfig($this->getConfig());
        }
        return $way;
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
        return $this->getTransport()->getObjects(
            'way',
            Services_Openstreetmap::getIDs(func_get_args())
        );
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
        $node = $this->getTransport()->getObject('node', $nodeID, $version);
        if ($node !== false) {
            $node->setTransport($this->getTransport());
            $node->setConfig($this->getConfig());
        }
        return $node;
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
        return $this->getTransport()->getObjects(
            'node',
            Services_Openstreetmap::getIDs(func_get_args())
        );
    }
}

?>
