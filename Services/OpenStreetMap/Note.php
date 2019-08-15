<?php
/**
 * Note.php
 * 14-May-2011
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       Note.php
 */

/**
 * Services_OpenStreetMap_Note
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage Services_OpenStreetMap_Object
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       Note.php
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
     * Comments object
     *
     * @var Services_OpenStreetMap_Comments
     */
    protected $comments;

    /**
     * Latitude of note
     *
     * @return float
     */
    public function getLat(): float
    {
        return (float) $this->getAttributes()->lat;
    }

    /**
     * Longitude of note
     *
     * @return float
     */
    public function getLon(): float
    {
        return (float) $this->getAttributes()->lon;
    }

    /**
     * Retrieve the status of the note ('open' etc)
     *
     * @return string
     */
    public function getStatus(): string
    {
        return $this->obj[0]->status;
    }

    /**
     * Retrieve time-stamp of when note was created.
     *
     * @return int
     */
    public function getDateCreated(): int
    {
        return strtotime($this->obj[0]->date_created);
    }

    /**
     * URL for this note.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->obj[0]->url;
    }

    /**
     * URL for adding comment to note.
     *
     * @return string
     */
    public function getCommentUrl(): string
    {
        return $this->obj[0]->comment_url;
    }

    /**
     * URL for closing note.
     *
     * @return string
     */
    public function getCloseUrl(): string
    {
        return $this->obj[0]->close_url;
    }

    /**
     * Get Comments against this note.
     *
     * @return Services_OpenStreetMap_Comments
     */
    public function getComments(): \Services_OpenStreetMap_Comments
    {
        return $this->comments;
    }

    /**
     * Set the Latitude of the note
     *
     * Latitude may range from -180 to 180 degrees.
     *
     * <code>
     * $note->setLat($lat)->setLon($lon);
     * </code>
     *
     * @param float $value Latitude (-180 < y < 180)
     *
     * @return Services_OpenStreetMap_Note
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function setLat(float $value): Services_OpenStreetMap_Note
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
     * Set the Longitude of the note
     *
     * Longitude may range from -90 to 90 degrees.
     *
     * <code>
     * $note->setLat($lat)->setLon($lon);
     * </code>
     *
     * @param float $value Longitude (-90 < x < 90)
     *
     * @return Services_OpenStreetMap_Note
     * @throws Services_OpenStreetMap_InvalidArgumentException
     */
    public function setLon(float $value): Services_OpenStreetMap_Note
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

    /**
     * Set XML
     *
     * @param SimpleXMLElement $xml OSM XML
     *
     * @return Services_OpenStreetMap_Note
     */
    public function setXml(SimpleXMLElement $xml): Services_OpenStreetMap_Object
    {
        $comments = [];
        $this->xml = $xml->saveXml();
        $obj = $xml->xpath('//' . $this->getType());
        $kids = [];
        foreach ($obj[0]->children() as $child) {
            $key = (string) $child->attributes()->k;
            if ($key !== '') {
                $this->tags[$key] = (string) $child->attributes()->v;
            }
            $name = (string) $child->getName();
            if ($name === 'comments') {
                $comments = [];
                foreach ($child->children() as $gchild) {
                    $comment = new Services_OpenStreetMap_Comment();
                    $comment->setXml($gchild);
                    $comments[] = $comment;
                }
                $kids[] = $comments;
            } elseif ($name === 'id') {
                $this->setId((string) $child);
            }
        }
        $this->comments = new Services_OpenStreetMap_Comments($comments);
        $this->tags = $kids;
        $this->obj = $obj;
        return $this;
    }
}
// vim:set et ts=4 sw=4:
