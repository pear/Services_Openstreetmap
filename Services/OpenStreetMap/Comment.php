<?php
/**
 * Comment.php
 * 30-Dec-2013
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       Comment.php
 */

/**
 * Services_OpenStreetMap_Comment
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Comment.php
 */
class Services_OpenStreetMap_Comment extends Services_OpenStreetMap_Object
{
    /**
     * What type of object this is.
     *
     * @var string
     */
    protected $type = 'comment';

    /**
     * Retrieve the action of the note ('opened' etc)
     *
     * @return string
     */
    public function getAction()
    {
        return $this->obj[0]->action;
    }

    /**
     * Retrieve time-stamp of when note was created.
     *
     * @return int
     */
    public function getDate()
    {
        return $this->obj[0]->date;
    }

    /**
     * Text entry for this comment.
     *
     * @return string
     */
    public function getText()
    {
        return $this->obj[0]->text;
    }

    /**
     * Html entry for this comment.
     *
     * @return string
     */
    public function getHtml()
    {
        return $this->obj[0]->html;
    }

    /**
     * Set XML
     *
     * @param SimpleXMLElement $xml OSM XML
     *
     * @return Services_OpenStreetMap_Comment
     */
    public function setXml(SimpleXMLElement $xml)
    {
        $this->xml = $xml->saveXml();
        $obj = $xml->xpath('//' . $this->getType());
        $kids = [];
        foreach ($obj[0]->children() as $child) {
            $key = (string) $child->attributes()->k;
            if ($key != '') {
                $this->tags[$key] = (string) $child->attributes()->v;
            }
            $name = (string) $child->getName();
            $kids[$name] = (string) $child;
        }
        $this->tags = $kids;
        if (isset($kids['id'])) {
            $this->setId($kids['id']);
        }
        $this->obj = $obj;
        return $this;
    }
}
// vim:set et ts=4 sw=4:
?>
