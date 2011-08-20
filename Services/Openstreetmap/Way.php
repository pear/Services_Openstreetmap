<?php
/**
 * Way.php
 * 25-Apr-2011
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreemap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  CVS: <cvs_id>
 * @link     Way.php
 * @todo
*/

/**
 * Services_Openstreetmap_Way
 *
 * @category Services
 * @package  Services_Openstreemap
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
     * isClosed
     *
     * @access public
     * @return void
     */
    function isClosed()
    {
    }

    /**
     * nodes
     *
     * @return void
     */
    public function nodes()
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
     * @access public
     * @return void
     */
    function addNode()
    {
    }

    /**
     * removeNode
     *
     * @access public
     * @return void
     */
    function removeNode()
    {
    }
}
// vim:set et ts=4 sw=4:
?>
