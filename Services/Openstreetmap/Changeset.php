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

    /**
     * getClosedAt
     *
     * @return string
     */
    public function getClosedAt()
    {
        return (string) $this->getAttributes()->closed_at;
    }

    /**
     * isOpen
     *
     * @return boolean
     */
    public function isOpen()
    {
        return $this->getAttributes()->open == 'true';
    }

    /**
     * getMinLon
     *
     * @return float
     */
    public function getMinLon()
    {
        return (float) $this->getAttributes()->min_lon;
    }

    /**
     * getMinLat
     *
     * @return float
     */
    public function getMinLat()
    {
        return (float) $this->getAttributes()->min_lat;
    }


    /**
     * getMaxLon
     *
     * @return float
     */
    public function getMaxLon()
    {
        return (float) $this->getAttributes()->max_lon;
    }

    /**
     * getMaxLat
     *
     * @return float
     */
    public function getMaxLat()
    {
        return (float) $this->getAttributes()->max_lat;
    }

}
// vim:set et ts=4 sw=4:
?>
