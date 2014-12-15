<?php
/**
 * Ways.php
 * 01-Oct-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Ways.php
 */

/**
 * Services_OpenStreetMap_Ways
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Ways.php
 */
class Services_OpenStreetMap_Ways extends Services_OpenStreetMap_Objects
{
    /**
     * Type
     *
     * @return string type
     */
    public function getType()
    {
        return 'way';
    }
}
// vim:set et ts=4 sw=4:
?>
