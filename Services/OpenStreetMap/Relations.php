<?php
/**
 * Relations.php
 * 01-Oct-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Relations.php
 */

/**
 * Services_OpenStreetMap_Relations
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Relations.php
 */
class Services_OpenStreetMap_Relations extends Services_OpenStreetMap_Objects
{
    /**
     * type
     *
     * @return string type
     */
    public function getType()
    {
        return 'relation';
    }
}

?>
