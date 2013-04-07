<?php
/**
 * Objects.php
 * 01-Oct-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Ways.php
 */

/**
 * Services_OpenStreetMap_Objects
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Objects.php
 */
class Services_OpenStreetMap_Objects implements Iterator, ArrayAccess, Countable
{

    /**
     * xml representation of the objects
     *
     * @var string
     */
    protected $xml = null;

    /**
     * Array of Services_OpenStreetMap_Object instances
     *
     * @var array
     */
    protected $objects = null;

    /**
     * position/pointer for navigating objects array
     *
     * @var int
     */
    protected $position = 0;

    /**
     * transport
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
     * getXml
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    /**
     * setXml
     *
     * @param SimpleXMLElement $xml OSM XML
     *
     * @return void
     */
    public function setXml(SimpleXMLElement $xml)
    {
        $this->xml = $xml->saveXML();
        $objs = $xml->xpath('//' . $this->getType());
        foreach ($objs as $obj) {
            $this->objects[] = $obj->saveXML();
        }
        return $this;
    }

    /**
     * Store a specified value.
     *
     * @param string $value Most likely an id value, returned from the server.
     *
     * @return void
     */
    public function setVal($value)
    {
        $this->xml = $value;
        return $this;
    }


    /**
     * Return the number of objects
     *
     * @return void
     */
    public function count()
    {
        return sizeof($this->objects);
    }

    /**
     * Resets the internal iterator pointer
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Return the current object
     *
     * @return Services_OpenStreetMap_Object
     */
    public function current()
    {
        $class = 'Services_OpenStreetMap_' . ucfirst(strtolower($this->getType()));
        $way = new $class();
        $config = $this->getConfig();
        if (!is_null($config)) {
            $way->setConfig($config);
        }
        $way->setTransport($this->getTransport());
        $way->setXml(simplexml_load_string($this->objects[$this->position]));
        return $way;
    }

    /**
     * Advance the internal iterator pointer
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Return the key of the current internal iterator pointer
     *
     * @return void
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Returns whether the current internal iterator pointer is pointing to an
     * existing/valid value.
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->objects[$this->position]);
    }

    /**
     * Check if the specified offset exists.
     *
     * @param int $offset N/A.
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->objects[$offset]);
    }

    /**
     * Get object from the specified offset.
     *
     * @param int $offset N/A.
     *
     * @return Services_OpenStreetMap_Object
     */
    public function offsetGet($offset)
    {
        $class = 'Services_OpenStreetMap_' . ucfirst(strtolower($this->getType()));
        $way = new $class();
        $config = $this->getConfig();
        if (!is_null($config)) {
            $way->setConfig($config);
        }
        $way->setTransport($this->getTransport());
        if (isset($this->objects[$offset])) {
            $way->setXml(simplexml_load_string($this->objects[$offset]));
            return $way;
        }
    }

    /**
     * Does nothing as collection is read-only: required for ArrayAccess.
     *
     * @param int                           $offset N/A
     * @param Services_OpenStreetMap_Object $value  N/A
     *
     * @return void
     * @throws LogicException
     */
    public function offsetSet($offset, $value)
    {
        throw new LogicException('Changing properties not implemented');
    }

    /**
     * Does nothing as collection is read-only: required for ArrayAccess.
     *
     * @param int $offset N/A.
     *
     * @return void
     * @throws LogicException
     */
    public function offsetUnset($offset)
    {
        throw new LogicException('Changing properties not implemented');
    }

    /**
     * Set Config object
     *
     * @param Services_OpenStreetMap_Config $config Config object
     *
     * @return Services_OpenStreetMap_Changeset
     */
    public function setConfig(Services_OpenStreetMap_Config $config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * Get current Config object
     *
     * @return Services_OpenStreetMap_Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set the Transport instance.
     *
     * @param Services_OpenStreetMap_Transport $transport Transport instance.
     *
     * @return Services_OpenStreetMap_Config
     */
    public function setTransport($transport)
    {
        $this->transport = $transport;
        return $this;
    }

    /**
     * Retrieve the current Transport instance.
     *
     * @return Services_OpenStreetMap_Transport.
     */
    public function getTransport()
    {
        return $this->transport;
    }
}

?>
