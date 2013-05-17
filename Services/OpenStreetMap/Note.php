<?php
/**
 * Note.php
 * 14-May-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Note.php
*/

/**
 * Services_OpenStreetMap_Note
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Note.php
 */
class Services_OpenStreetMap_Note extends Services_OpenStreetMap_Object
{
    /**
     * What type of object this is.
     *
     * @var string
     */
    protected $type = 'note';

    /**
     * Latitude of note
     *
     * @return float
     */
    public function getLat()
    {
        return (float) $this->getAttributes()->lat;
    }

    /**
     * Longitude of note
     *
     * @return float
     */
    public function getLon()
    {
        return (float) $this->getAttributes()->lon;
    }

    /**
     * set the Latitude of the note
     *
     * <pre>
     * $note->setLat($lat)->setLon($lon);
     * </pre>
     *
     * @param float $value Latitude (-180 < y < 180)
     *
     * @return Services_OpenStreetMap_Note
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function setLat($value)
    {
        if (!is_numeric($value)) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Latitude must be numeric'
            );
        }
        if ($value < -180) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Latitude can\'t be less than -180'
            );
        }
        if ($value > 180) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Latitude can\'t be greater than 180'
            );
        }
        return $this;
    }

    /**
     * set the Longitude of the note
     *
     * <pre>
     * $note->setLat($lat)->setLon($lon);
     * </pre>
     *
     * @param float $value Longitude (-90 < x < 90)
     *
     * @return Services_OpenStreetMap_Note
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function setLon($value)
    {
        if (!is_numeric($value)) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Longitude must be numeric'
            );
        }
        if ($value < -90) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Longitude can\'t be less than -90'
            );
        }
        if ($value > 90) {
            throw new Services_OpenStreetMap_InvalidArgumentException(
                'Longitude can\'t be greater than 90'
            );
        }
        return $this;
    }

    public function setXml(SimpleXMLElement $xml)
    {
        $this->xml = $xml->saveXml();
        $obj = $xml->xpath('//' . $this->getType());
        $kids = array();
        foreach ($obj[0]->children() as $child) {
            $key = (string) $child->attributes()->k;
            if ($key != '') {
                $this->tags[$key] = (string) $child->attributes()->v;
            }
            $name = (string) $child->getName();
            if ($name == 'comments') {
                $comments = array();
                foreach($child->children() as $gchild) {
                    $comment = array();
                    foreach($gchild->children() as $ggchild) {
                        $ggname = (string) $ggchild->getName();
                        $ggvalue = (string) $ggchild;
                        $comment[$ggname] = $ggvalue;
                    }
                    $comments[] = $comment;
                }
                $kids['comments'] = $comments;

            } else {
                $kids[$name] = (string) $child;
            }
        }
        $this->tags = $kids;
        if (isset($kids['id'])) {
            $this->setId($kids['id']);
            var_dump($this->getId());
        }
        $this->obj = $obj;
        return $this;
    }

}
// vim:set et ts=4 sw=4:
?>
