<?php
/**
 * Relation.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       Relation.php
 */

/**
 * Services_OpenStreetMap_Relation
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Relation.php
 */
class Services_OpenStreetMap_Relation extends Services_OpenStreetMap_Object
{
    /**
     * What type of object this is.
     *
     * @var string
     */
    protected $type = 'relation';


    /**
     * Array containing members of this relation.
     *
     * @var array
     */
    protected $members = [];

    /**
     * Return all members of the relation.
     *
     * @return void
     */
    public function getMembers()
    {
        return $this->members;
    }

    /**
     * Type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Add Member to the relation.
     *
     * @todo   add member to relation
     * @return void
     */
    public function addMember()
    {
    }

    /**
     * Remove a member from the relation.
     *
     * @todo   remove member from relation
     * @return void
     */
    public function removeMember()
    {
    }

    /**
     * Set the Xml
     *
     * @param SimpleXMLElement $xml OSM XML
     *
     * @return Services_OpenStreetMap_Relation
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
                $this->members[] = [
                    'type'=> (string) $child->attributes()->type,
                    'ref'=> (string) $child->attributes()->ref,
                    'role'=> (string) $child->attributes()->role,
                ];

            }
        }
        $this->obj = $obj;
        return $this;
    }
}
// vim:set et ts=4 sw=4:
?>
