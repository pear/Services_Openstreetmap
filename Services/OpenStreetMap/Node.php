<?php
/**
 * Node.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       Node.php
*/

/**
 * Services_OpenStreetMap_Node
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Node.php
 */
class Services_OpenStreetMap_Node extends Services_OpenStreetMap_Object
{
    /**
     * What type of object this is.
     *
     * @var string
     */
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
     * Set the Latitude of the node
     *
     * Value must be in range of -90 to 90 degrees.
     *
     * <code>
     * $node->setLat($lat)->setLon($lon);
     * </code>
     *
     * @param float $value Latitude (-90 < y < 90)
     *
     * @return Services_OpenStreetMap_Node
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function setLat($value)
    {
        if (!is_numeric($value)) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Latitude must be numeric'
            );
        }
        if ($value < -90) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Latitude can\'t be less than -90'
            );
        }
        if ($value > 90) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Latitude can\'t be greater than 90'
            );
        }
        return $this;
    }

    /**
     * Set the Longitude of the node
     *
     * Value must be in range of -180 to 180 degrees.
     *
     * <code>
     * $node->setLat($lat)->setLon($lon);
     * </code>
     *
     * @param float $value Longitude (-180 < x < 180)
     *
     * @return Services_OpenStreetMap_Node
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function setLon($value)
    {
        if (!is_numeric($value)) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Longitude must be numeric'
            );
        }
        if ($value < -180) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Longitude can\'t be less than -180'
            );
        }
        if ($value > 180) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Longitude can\'t be greater than 180'
            );
        }
        return $this;
    }

    /**
     * Return address [tags], as an array, if set.
     *
     * @return array
     */
    public function getAddress()
    {
        $ret  = [
            'addr_housename' => null,
            'addr_housenumber' => null,
            'addr_street' => null,
            'addr_city' => null,
            'addr_country' => null
        ];
        $tags = $this->getTags();
        $detailsSet = false;
        foreach ($tags as $key => $value) {
            if (strpos($key, 'addr') === 0) {
                $ret[str_replace(':', '_', $key)] = $value;
                $detailsSet = true;
            }
        }
        if (!$detailsSet) {
            $ret = null;
        }
        return $ret;
    }

    /**
     * Return a collection of Services_OpenStreetMap_Way objects that use the
     * node in question.
     *
     * @return Services_OpenStreetMap_Ways
     */
    public function getWays()
    {
        $config = $this->getConfig();
        $id = $this->getId();
        $url = $config->getValue('server')
            . 'api/'
            . $config->getValue('api_version')
            . "/node/$id/ways";
        $response = $this->getTransport()->getResponse($url);
        $obj = new Services_OpenStreetMap_Ways();
        $sxe = @simplexml_load_string($response->getBody());
        if ($sxe === false) {
            $obj->setVal(trim($response->getBody()));
        } else {
            $obj->setXml($sxe);
        }
        return $obj;
    }
}
// vim:set et ts=4 sw=4:
?>
