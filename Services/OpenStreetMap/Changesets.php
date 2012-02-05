<?php
/**
 * Nodes.php
 * 01-Oct-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Nodes.php
 */

/**
 * Services_OpenStreetMap_Nodes
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Nodes.php
 */
class Services_OpenStreetMap_Changesets extends Services_OpenStreetMap_Objects
{
    /**
     * type
     *
     * @return string type
     */
    public function getType()
    {
        return 'changeset';
    }
}

?>
