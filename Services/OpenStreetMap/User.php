<?php
/**
 * User.php
 * 07-Sep-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     User.php
*/

/**
 * Services_OpenStreetMap_User
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     User.php
 */
class Services_OpenStreetMap_User
{

    protected $preferences = array();

    /**
     * setXml
     *
     * @param SimpleXMLElement $xml XML describing a user.
     *
     * @return Services_OpenStreetMap_User
     */
    public function setXml(SimpleXMLElement $xml)
    {
        $this->xml = $xml->saveXML();
        $this->obj = $xml->xpath('//user');
        return $this;
    }

    /**
     * setPreferencesXml
     *
     * @param mixed $xml XML describing a user's preferences.
     *
     * @return void
     */
    public function setPreferencesXml($xml)
    {
        $this->prefXml = $xml;
        $this->prefObj = simplexml_load_string($xml)->xpath('//preferences');
    }

    /**
     * Return the attributes set for this user instance.
     *
     * @return string getAttributes()
     */
    public function getAttributes()
    {
        return $this->obj[0]->attributes();
    }

    /**
     * Return the display name of the user.
     *
     * @return string display name of user.
     */
    public function getDisplayName()
    {
        return (string) $this->getAttributes()->display_name;
    }

    /**
     * Retrieve date, as a string, representing when the user's account was
     * created.
     *
     * @return string
     */
    public function getAccountCreated()
    {
        return (string) $this->getAttributes()->account_created;
    }

    /**
     * Return the description set for the user.
     *
     * @return string
     */
    public function getDescription()
    {
        $desc = simplexml_load_string($this->xml)->xpath('//user/description');
        return (string) trim($desc[0]);
    }

    /**
     * Retrieve the id of the user.
     *
     * @return integer id of the object
     */
    public function getId()
    {
        return (float) $this->getAttributes()->id;
    }

    /**
     * Return href to user's profile image, null if not set.
     *
     * @return string
     */
    public function getImage()
    {
        $img = simplexml_load_string($this->xml)->xpath('//user/img');
        if (empty($img)) {
            return null;
        }
        return (string) $img[0]->attributes()->href;
    }

    /**
     * Return an array of the user's preferred languages.
     *
     * @return array
     */
    public function getLanguages()
    {
        $langers = array();
        $cxml = simplexml_load_string($this->xml);
        $languages = $cxml->xpath('//user/languages');
        if (empty($languages)) {
            return null;
        }
        foreach ($languages[0]->children() as $child) {
            if ($child->getName() == 'lang') {
                $langers[] = (string) $child[0];
            }
        }
        return $langers;
    }

    /**
     * Latitude of 'home' setting for user.
     *
     * @return float
     */
    public function getLat()
    {
        $home = simplexml_load_string($this->xml)->xpath('//user/home');
        if (empty($home)) {
            return null;
        }
        return (float) $home[0]->attributes()->lat;
    }

    /**
     * Longitude of 'home' setting for user.
     *
     * @return float
     */
    public function getLon()
    {
        $cxml = simplexml_load_string($this->xml);
        $home = $cxml->xpath('//user/home');
        if (empty($home)) {
            return null;
        }
        return (float) $home[0]->attributes()->lon;
    }

    /**
     * Zoom level of 'home' setting for user.
     *
     * @return integer
     */
    public function getZoom()
    {
        $cxml = simplexml_load_string($this->xml);
        $home = $cxml->xpath('//user/home');
        if (empty($home)) {
            return null;
        }
        return (integer) $home[0]->attributes()->zoom;
    }

    /**
     * The number of changesets opened by the user.
     *
     * @return integer
     */
    public function getChangesets()
    {
        $cxml = simplexml_load_string($this->xml);
        $changesets = $cxml->xpath('//user/changesets');
        if (empty($changesets)) {
            return null;
        }
        return (integer) $changesets[0]->attributes()->count;
    }

    /**
     * The number of traces uploaded by the user.
     *
     * @return integer
     */
    public function getTraces()
    {
        $cxml = simplexml_load_string($this->xml);
        $traces = $cxml->xpath('//user/traces');
        if (empty($traces)) {
            return null;
        }
        return (integer) $traces[0]->attributes()->count;
    }

    /**
     * The [total] number of blocks received by the user.
     *
     * @return integer
     */
    public function getBlocksReceived()
    {
        $cxml = simplexml_load_string($this->xml);
        $changesets = $cxml->xpath('//user/blocks/received');
        if (empty($changesets)) {
            return null;
        }
        return (integer) $changesets[0]->attributes()->count;
    }

    /**
     * The number of active blocks received by the user.
     *
     * @return integer
     */
    public function getActiveBlocksReceived()
    {
        $cxml = simplexml_load_string($this->xml);
        $changesets = $cxml->xpath('//user/blocks/received');
        if (empty($changesets)) {
            return null;
        }
        return (integer) $changesets[0]->attributes()->active;
    }

    /**
     * The [total] number of blocks issued by the user.
     *
     * @return integer
     */
    public function getBlocksIssued()
    {
        $cxml = simplexml_load_string($this->xml);
        $changesets = $cxml->xpath('//user/blocks/issued');
        if (empty($changesets)) {
            return null;
        }
        return (integer) $changesets[0]->attributes()->count;
    }

    /**
     * The number of active blocks issued by the user.
     *
     * @return integer
     */
    public function getActiveBlocksIssued()
    {
        $cxml = simplexml_load_string($this->xml);
        $changesets = $cxml->xpath('//user/blocks/issued');
        if (empty($changesets)) {
            return null;
        }
        return (integer) $changesets[0]->attributes()->active;
    }

    /**
     * Array of names of roles associated with the user.
     *
     * @return array
     */
    public function getRoles()
    {
        $ret = array();
        $cxml = simplexml_load_string($this->xml);
        $roles = $cxml->xpath('//user/roles');
        if (empty($roles)) {
            return $ret;
        }
        foreach ($roles[0]->children() as $child) {
            $ret[] = $child->getName();
        }
        return $ret;
    }

    /**
     * return an array of the user's preferences.
     *
     * @return array
     */
    public function getPreferences()
    {
        if ($this->preferences == array()) {

            $preferences = array();
            foreach ($this->prefObj[0]->children() as $child) {
                $key = (string) $child->attributes()->k;
                if ($key != '') {
                    $preferences[$key] = (string) $child->attributes()->v;
                }
            }
            $this->preferences = $preferences;
        }
        return $this->preferences;
    }

}
// vim:set et ts=4 sw=4:
?>
