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
     * members
     *
     * @access public
     * @return void
     */
    function getMembers()
    {
        return $this->members;
    }

    /**
     * type
     *
     * @access public
     * @return void
     */
    function getType()
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
    function addMember()
    {
    }

    /**
     * removeNode
     *
     * @todo   remove member from relation
     * @access public
     * @return void
     */
    function removeMember()
    {
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
