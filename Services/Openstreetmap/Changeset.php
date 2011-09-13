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
    protected $atomic = true;
    protected $members = array();
    protected $members_ids = array();
    protected $open = false;
    protected $id = null;

    /**
     * __construct
     *
     * @param boolean $atomic true if changeset should be treated as atomic
     *
     * @return void
     */
    public function __construct($atomic = true)
    {
        $this->atomic = $atomic;
    }

    /**
     * begin
     *
     * @param string $message The changeset log message.
     *
     * @return void
     */
    public function begin($message)
    {
        $this->members = array();
        $this->open = true;
        $user_agent = $this->_osm->getConfig('User-Agent');
        $doc = "<?xml version='1.0' encoding=\"UTF-8\"?>\n" .
        '<osm version="0.6" generator="' . $user_agent . '">'
            . "<changeset id='0' open='false'>"
            . '<tag k="comment" v="' . $message . '"/>'
            . '<tag k="created_by" v="' . $user_agent . '/0.1"/>'
            . '</changeset></osm>';
        $url = $this->_osm->getConfig('server')
            . 'api/'
            . $this->_osm->getConfig('api_version')
            . "/changeset/create";
        $user = $this->_osm->getConfig('user');
        $password = $this->_osm->getConfig('password');
        if ($user == null) {
            throw new Services_Openstreetmap_Exception('User must be set');
        }
        if ($password == null) {
            throw new Services_Openstreetmap_Exception('Password must be set');
        }
        $response = $this->_osm->getResponse(
            $url,
            HTTP_Request2::METHOD_PUT,
            $user,
            $password,
            $doc,
            null,
            array(array('Content-type', 'text/xml', true))
        );
        $code = $response->getStatus();
        if (200 == $code) {
            $trimmed = trim($response->getBody());
            if (is_numeric($trimmed)) {
                $this->id = $trimmed;
            }
        }
    }

    /**
     * add object to the changeset so changes can be transmitted to the server
     *
     * @param Services_Openstreetmap_Object $object OSM object
     *
     * @return void
     */
    public function add(Services_Openstreetmap_Object $object)
    {
        if ($this->open === false) {
            throw new Services_Openstreetmap_Exception(
                "Object added to closed changeset"
            );
        }
        $object->setChangesetId($this->getId());
        $object_id = $object->getType() . $object->getId();
        if (!in_array($object_id, $this->members_ids)) {
            $this->members[] = $object;
            $this->members_ids[] = $object_id;
        } else {
            throw new Services_Openstreetmap_Exception(
                "Object added to changeset already"
            );
        }
    }

    /**
     * commit
     *
     * @return void
     */
    public function commit()
    {
        if (!$this->open) {
            throw new Services_Openstreetmap_Exception(
                "Attempt to commit a closed changeset"
            );
        }

        $cId = $this->getId();
        $url = $this->_osm->getConfig('server')
            . 'api/'
            . $this->_osm->getConfig('api_version') .
            "/changeset/{$cId}/upload";

        $blocks = null;
        foreach ($this->members as $member) {
            $blocks .= $member->getOsmChangeXML() . "\n";
        }

        $doc = '<osmChange version="0.6" generator="Services_Openstreetmap">'
            . "\n"
             . $blocks
             . '</osmChange>';

        $response = $this->_osm->getResponse(
            $url,
            HTTP_Request2::METHOD_POST,
            $this->_osm->getConfig('user'),
            $this->_osm->getConfig('password'),
            $doc,
            null,
            array(array('Content-type', 'text/xml', true))
        );

        $code = $response->getStatus();
        if (200 != $code) {
            throw new Services_Openstreetmap_Exception(
                "Error posting changeset",
                $code
            );

        }
        // Explicitly close the changeset
        $url = $this->_osm->getConfig('server')
            . 'api/'
            . $this->_osm->getConfig('api_version') .
            "/changeset/{$cId}/close";

        $response = $this->_osm->getResponse(
            $url,
            HTTP_Request2::METHOD_PUT,
            $this->_osm->getConfig('user'),
            $this->_osm->getConfig('password'),
            null,
            null,
            array(array('Content-type', 'text/xml', true))
        );
        $code = $response->getStatus();
        if (200 != $code) {
            throw new Services_Openstreetmap_Exception(
                "Error posting changeset",
                $code
            );

        }

        $this->open = false;
    }

    /**
     * getCreatedAt
     *
     * @return string
     */
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
        $attribs = $this->getAttributes();
        if ($attribs !== null) {
            return $attribs->open == 'true';
        } else {
            return $this->open;
        }
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


    /**
     * getId
     *
     * @return numeric value or null if none set
     */
    public function getId()
    {
        $p_id = parent::getId();
        if (is_null($p_id)) {
            return $this->id;
        } else {
            return $p_id;
        }
    }
}
// vim:set et ts=4 sw=4:
?>
