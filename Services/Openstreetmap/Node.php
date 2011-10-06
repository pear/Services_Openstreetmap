<?php
/**
 * Node.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Node.php
*/

/**
 * Services_Openstreetmap_Node
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Node.php
 */
class Services_Openstreetmap_Node extends Services_Openstreetmap_Object
{
    protected $type = 'node';

    /**
     * Latitude of node
     *
     * @return float
     */
    public function getLat()
    {
        return (float) $this->getAttributes()->lat;
    }

    /**
     * Longitude of node
     *
     * @return float
     */
    public function getLon()
    {
        return (float) $this->getAttributes()->lon;
    }

    /**
     * set the Latitude of the node
     *
     * <pre>
     * $node->setLat($lat)->setLon($lon);
     * </pre>
     *
     * @param float $value Latitude (-90 < y < 90)
     *
     * @return Services_Openstreetmap_Node
     * @throws InvalidArgumentException
     */
    public function setLat($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Latitude must be numeric");
        }
        if ($value < -90 ) {
            throw new InvalidArgumentException("Latitude can't be less than -90");
        }
        if ($value > 90 ) {
            throw new InvalidArgumentException("Latitude can't be greater than 90");
        }
        return $this;
    }

    /**
     * set the Longitude of the node
     *
     * <pre>
     * $node->setLat($lat)->setLon($lon);
     * </pre>
     *
     * @param float $value Longitude (-90 < x < 90)
     *
     * @return Services_Openstreetmap_Node
     * @throws InvalidArgumentException
     */
    public function setLon($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidArgumentException("Longitude must be numeric");
        }
        if ($value < -90 ) {
            throw new InvalidArgumentException("Longitude can't be less than -90");
        }
        if ($value > 90 ) {
            throw new InvalidArgumentException("Longitude can't be greater than 90");
        }
        return $this;
    }
}
// vim:set et ts=4 sw=4:
?>
