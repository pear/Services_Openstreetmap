<?php
/**
 * Way.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     Way.php
 */

/**
 * Services_Openstreetmap_Way
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     Way.php
 */
class Services_Openstreetmap_Way extends Services_Openstreetmap_Object
{
    protected $type = 'way';
    protected $nodes = array();
    private $_pointer = 0;

    /**
     * Return true if the way can be considered 'closed'.
     *
     * @access public
     * @return boolean
     */
    function isClosed()
    {
        // Not closed if there's just one node.
        // Otherwise a way is considered closed if the first node has
        // the same id as the last.
        $nodes = $this->getNodes();
        if (sizeof($nodes) == 1) {
            $closed = false;
        } else {
            $closed = ($nodes[0]) == ($nodes[count($nodes) - 1]);
        }
        return $closed;
    }

    /**
     * Return an array containing the IDs of all nodes in the way.
     *
     * @return array
     */
    public function getNodes()
    {
        $x = simplexml_load_string($this->xml);
        $o = $x->xpath('//nd');
        $nodes = array();
        foreach ($o as $n) {
            $nodes[] = (string) $n->attributes()->ref;
        }
        return $nodes;
    }

    /**
     * addNode
     *
     * @todo   Add node to way.
     * @access public
     * @return void
     */
    function addNode()
    {
    }

    /**
     * removeNode
     *
     * @todo   Remove node from way.
     * @access public
     * @return void
     */
    function removeNode()
    {
    }
}
// vim:set et ts=4 sw=4:
?>
