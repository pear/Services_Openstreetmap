<?php
/**
 * Objects.php
 * 01-Oct-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Ways.php
 */

/**
 * Services_Openstreetmap_Objects
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Objects.php
 */
class Services_Openstreetmap_Objects implements Iterator, ArrayAccess, Countable
{

    protected $xml = null;

    protected $objects = null;

    protected $position = 0;

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
     * @param mixed $xml OSM XML
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
     * @return Services_Openstreetmap_Object
     */
    public function current()
    {
        $class = 'Services_Openstreetmap_' . ucfirst(strtolower($this->getType()));
        $way = new $class();
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
     * @return Services_Openstreetmap_Object
     */
    public function offsetGet($offset)
    {
        $class = 'Services_Openstreetmap_' . ucfirst(strtolower($this->getType()));
        $way = new $class();
        if (isset($this->objects[$offset])) {
            $way->setXml(simplexml_load_string($this->objects[$offset]));
            return $way;
        }
    }

    /**
     * Does nothing as collection is read-only: required for ArrayAccess.
     *
     * @param int                           $offset N/A
     * @param Services_Openstreetmap_Object $value  N/A
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * Does nothing as collection is read-only: required for ArrayAccess.
     *
     * @param int $offset N/A.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
    }
}

?>
