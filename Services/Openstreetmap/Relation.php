<?php
/**
 * Relation.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Relation.php
 */

/**
 * Services_Openstreetmap_Relation
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Relation.php
 */
class Services_Openstreetmap_Relation extends Services_Openstreetmap_Object
{
    protected $type = 'relation';

    protected $members = array();

    /**
     * Return all members of the relation.
     *
     * @access public
     * @return void
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * type
     *
     * @access public
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * addMember
     *
     * @todo   add member to relation
     * @access public
     * @return void
     */
    public function addMember()
    {
    }

    /**
     * remove a member from the relation.
     *
     * @todo   remove member from relation
     * @access public
     * @return void
     */
    public function removeMember()
    {
    }

    /**
     * setXml
     *
     * @param SimpleXMLElement $xml OSM XML
     *
     * @return Services_Openstreetmap_Relation
     */
    public function setXml(SimpleXMLElement $xml)
    {
        $this->xml = $xml->saveXML();
        $obj = $xml->xpath('//' . $this->getType());
        foreach ($obj[0]->children() as $child) {
            $childname = $child->getName();
            if ($childname == 'tag') {
                $key = (string) $child->attributes()->k;
                if ($key != '') {
                    $this->tags[$key] = (string) $child->attributes()->v;
                }
            } elseif ($childname == 'member') {
                $this->members[] = array(
                    'type'=> (string) $child->attributes()->type,
                    'ref'=> (string) $child->attributes()->ref,
                    'role'=> (string) $child->attributes()->role,
                );

            }
        }
        $this->obj = $obj;
        return $this;
    }
}
// vim:set et ts=4 sw=4:
?>
