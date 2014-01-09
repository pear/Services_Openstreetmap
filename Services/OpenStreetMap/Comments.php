<?php
/**
 * Comments.php
 * 30-Dec-2013
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Comments.php
 */

/**
 * Services_OpenStreetMap_Comments
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Comments.php
 */
class Services_OpenStreetMap_Comments extends Services_OpenStreetMap_Objects
{
    /**
     * type
     *
     * @return string type
     */
    public function getType()
    {
        return 'comment';
    }

    public function __construct(array $array = array())
    {
        $this->objects = $array;
    }
}

?>
