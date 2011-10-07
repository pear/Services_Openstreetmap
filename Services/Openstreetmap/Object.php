<?php
/**
 * Object.php
 * 26-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Object.php
 */

/**
 * Services_Openstreetmap_Object
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Object.php
 */
class Services_Openstreetmap_Object
{
    protected $xml = null;

    /**
     * Array of tags in key/value format
     *
     * @var array
     */
    protected $tags = array();

    protected $id = null;

    /**
     * type of object
     *
     * @var string
     */
    protected $type = null;

    protected $obj = null;

    protected $dirty = false;

    protected $action = null;

    protected $changeset_id = null;

    /**
     * getXml
     *
     * @return string
     */
    public function getXml()
    {
        return $this->xml;
    }

    public function __tostring()
    {
        $changeXML = $this->getOsmChangeXML();
        if (is_null($changeXML)) {
            return $this->getXml();
        } else {
            return $changeXML;
        }
    }

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
        $obj = $cxml->xpath('//' . $this->getType());
        foreach ($obj[0]->children() as $child) {
            $key = (string) $child->attributes()->k;
            if ($key != '') {
                $this->tags[$key] = (string) $child->attributes()->v;
            }
        }
        $this->obj = $obj;
        return $this;
    }

    /**
     * Set the Changeset Id for this object.
     *
     * @param integer $id Changeset Id (numeric)
     *
     * @return Services_Openstreetmap_Object
     */
    public function setChangesetId($id)
    {
        $this->changeset_id = $id;
        return $this;
    }

    /**
     * Generate and return the OSM Change XML required to record the changes
     * made to the object in question.
     *
     * @return string
     */
    public function getOsmChangeXML()
    {
        $type = $this->getType();
        if ($this->dirty) {
            $version = $this->getVersion();
            $version++;
            $domd = new DomDocument();
            $domd->loadXML($this->getXML());
            $xpath = new DomXPath($domd);
            $nodelist = $xpath->query("//{$type}");
            $nodelist->item(0)->setAttribute("action", $this->action);
            $nodelist->item(0)->setAttribute("id", $this->getId());

            if ($this->changeset_id !== null) {
                $nodelist->item(0)->setAttribute("changeset", $this->changeset_id);
            }
            $tags = $xpath->query("//{$type}/tag");

            $set = array();
            for ($i = 0; $i < $tags->length; $i++) {
                $key = $tags->item($i)->getAttribute("k");
                $val = $tags->item($i)->getAttribute("v");
                $set[$key] = $val;
            }

            $diff = array_diff($this->getTags(), $set);

            // Remove existing tags
            for ($i = 0; $i < $tags->length; $i++) {
                $rkey = $tags->item($i)->getAttribute("k");
                if (isset($diff[$rkey])) {
                    $nodelist->item(0)->removeChild($tags->item($i));
                }
            }

            foreach ($diff as $key=>$value) {
                $new = $domd->createElement("tag");
                $new->setAttribute("k", $key);
                $new->setAttribute("v", $value);
                $nodelist->item(0)->appendChild($new);
            }

            $xml = $domd->saveXML($nodelist->item(0));
            return "<{$this->action}>{$xml}</{$this->action}>";

        } elseif ($this->action == 'delete') {
            $xml = null;
            $domd = new DomDocument();
            $domd->loadXML($this->getXML());
            $xpath = new DomXPath($domd);
            $n = $xpath->query("//{$type}");
            $version = $this->getVersion();
            $version++;
            if ($this->changeset_id !== null) {
                $n->item(0)->setAttribute("changeset", $this->changeset_id);
            }
            $n->item(0)->setAttribute('action', 'delete');
            $xml = $domd->saveXML($n->item(0));
            return "<delete>{$xml}</delete>";
        }
    }

    /**
     * Retrieve the id of the object in question.
     *
     * @return integer id of the object
     */
    public function getId()
    {
        if (!is_null($this->id)) {
            return $this->id;
        }

        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (integer) $attribs->id;
        }
    }

    /**
     * Set the id value of the object in question.
     *
     * <pre>
     * $obj->setId($id)->...
     * </pre>
     *
     * @param integer $value new id of the object
     *
     * @return Services_Openstreetmap_Object
     */
    public function setId($value)
    {
        $this->id = $value;
        return $this;
    }

    /**
     * Retrieve the uid of the object in question.
     *
     * @return integer uid of the object
     */
    public function getUid()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (integer) $attribs->uid;
        }
    }

    /**
     * Retrieve the user (creator/editor) of the object in question.
     *
     * @return string user of the object
     */
    public function getUser()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (string) $attribs->user;
        }
    }

    /**
     * Retrieve the version of the object in question
     *
     * @return string version of the object
     */
    public function getVersion()
    {
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return (integer) $attribs->version;
        }
    }

    /**
     * Return the attributes set for this object in question.
     *
     * @return string getAttributes()
     */
    public function getAttributes()
    {

        if ($this->obj[0] === null) {
            return null;
        }
        return $this->obj[0]->attributes();
    }

    /**
     * Return the tags set for this object in question.
     *
     * @return array tags
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Return which type of object this is.
     *
     * @return string type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * setTag
     *
     * <pre>
     * $obj->setTag('key', 'value')->setTag(...);
     * </pre>
     *
     * @param mixed $key   key
     * @param mixed $value value
     *
     * @return void
     */
    public function setTag($key, $value)
    {
        if ($this->action == null) {
            if ($this->getId() < 0) {
                $this->action = 'create';
            } else {
                $this->action = 'modify';
            }
        }
        $this->dirty = true;
        $this->tags[$key] = $value;
        return $this;
    }

    /**
     * Mark the object as deleted.
     *
     * <pre>
     * $obj->delete();
     * </pre>
     *
     * @return void
     */
    public function delete()
    {
        $this->action = 'delete';
        return $this;
    }
}

// vim:set et ts=4 sw=4:
?>
