<?php
/**
 * Node.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Node.php
*/

/**
 * Services_Openstreetmap_Node
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Node.php
 */
class Services_Openstreetmap_Node extends Services_Openstreetmap_Object
{
    protected $type = 'node';

    /**
     * Latitude of note
     *
     * @return string
     */
    public function lat()
    {
        return (string) $this->attributes()->lat;
    }

    /**
     * Longitude of node
     *
     * @return string
     */
    public function lon()
    {
        return (string) $this->attributes()->lon;
    }
}
// vim:set et ts=4 sw=4:
?>
