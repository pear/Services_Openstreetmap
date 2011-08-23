<?php
/**
 * Changeset.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Changeset.php
*/

/**
 * Services_Openstreetmap_Changeset
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Changeset.php
 */
class Services_Openstreetmap_Changeset extends Services_Openstreetmap_Object
{
    protected $type = 'changeset';

    public function getCreatedAt()
    {
        return (string) $this->getAttributes()->created_at;
    }

    public function getClosedAt()
    {
        return (string) $this->getAttributes()->closed_at;
    }

    public function isOpen()
    {
        return $this->getAttributes()->open == 'true';
    }

    public function getMinLon()
    {
        return (float) $this->getAttributes()->min_lon;
    }

    public function getMinLat()
    {
        return (float) $this->getAttributes()->min_lat;
    }


    public function getMaxLon()
    {
        return (float) $this->getAttributes()->max_lon;
    }

    public function getMaxLat()
    {
        return (float) $this->getAttributes()->max_lat;
    }

}
// vim:set et ts=4 sw=4:
?>
