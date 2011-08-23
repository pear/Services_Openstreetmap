<?php
/**
 * Object.php
 * 26-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Object.php
 */

/**
 * Services_Openstreetmap_Object
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Object.php
 */
class Services_Openstreetmap_Object
{
    protected $xml = null;

    protected $tags = array();

    protected $id = null;

    protected $type = null;

    protected $obj = null;

    /**
     * setXml
     *
     * @param mixed $xml OSM XML
     *
     * @return void
     */
    public function setXml($xml)
    {
        $this->xml = $xml;
        $cxml = simplexml_load_string($xml);
        $obj = $cxml->xpath('//' . $this->type);
        foreach ($obj[0]->children() as $child) {
            $key = (string) $child->attributes()->k;
            if ($key != '') {
                $this->tags[$key] = (string) $child->attributes()->v;
            }
        }
        $this->obj = $obj;
    }

    /**
     * Retrieve the id of the object in question
     *
     * @return string id of the object
     */
    public function getId()
    {
        return (integer) $this->getAttributes()->id;
    }

    /**
     * Retrieve the uid of the object in question
     *
     * @return string uid of the object
     */
    public function getUid()
    {
        return (integer) $this->getAttributes()->uid;
    }

    /**
     * Retrieve the user (creator/editor) of the object in question
     *
     * @return string user of the object
     */
    public function getUser()
    {
        return (string) $this->getAttributes()->user;
    }

    /**
     * Retrieve the version of the object in question
     *
     * @return string version of the object
     */
    public function getVersion()
    {
        return (integer) $this->getAttributes()->version;
    }

    /**
     * getAttributes()
     *
     * @return string getAttributes()
     */
    public function getAttributes()
    {
        return $this->obj[0]->attributes();
    }

    /**
     * tags
     *
     * @return string tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Display type and id of the object.
     *
     * @return void
     */
    public function getHistory()
    {
        echo "type: ", $this->type, "\n";
        echo "id: ", $this->id(), "\n";
    }

}

// vim:set et ts=4 sw=4:
?>
