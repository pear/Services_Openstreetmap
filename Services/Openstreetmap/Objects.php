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
class Services_Openstreetmap_Objects implements Iterator, Countable
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
}

?>
