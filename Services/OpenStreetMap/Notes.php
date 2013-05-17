<?php
/**
 * Notes.php
 * 14-May-2013
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Notes.php
 */

/**
 * Services_OpenStreetMap_Notes
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Notes.php
 */
class Services_OpenStreetMap_Notes extends Services_OpenStreetMap_Objects
{
    /**
     * type
     *
     * @return string type
     */
    public function getType()
    {
        return 'note';
    }
}

?>
