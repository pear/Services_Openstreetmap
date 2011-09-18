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
 * @todo
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

    /**
     * members
     *
     * @access public
     * @return void
     */
    function members()
    {
    }

    /**
     * type
     *
     * @access public
     * @return void
     */
    function type()
    {
    }

    /**
     * role
     *
     * @todo return role attribute of relation
     * @access public
     * @return string
     */
    function role()
    {
    }

    /**
     * addRole
     *
     * @todo add role attribute of relation
     * @access public
     * @return string
     */
    function addRole()
    {
    }

    /**
     * addMember
     *
     * @access public
     * @return void
     */
    function addMember()
    {
    }

    /**
     * removeNode
     *
     * @todo   remove node from relation
     * @access public
     * @return void
     */
    function removeNode()
    {
    }
}
// vim:set et ts=4 sw=4:
?>
