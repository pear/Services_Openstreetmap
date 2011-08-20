<?php
/**
 * example1_cahir.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  CVS: <cvs_id>
 * @link     osmx.php
 * @todo
*/

require_once 'Services/Openstreetmap.php';

$osm = new Services_Openstreetmap();
#$osm->setConfig(array('server' => 'http://apidev2.openstreetmap.ie/'));
$osm->setConfig(array('server' => 'http://www.openstreetmap.org/'));

try {
    $osm->get(-8.3564758, 52.821022799999994, -7.7330017, 53.0428644);

    file_put_contents("homelands.osm", $osm->getXML());
} catch (Exception $e) {
    echo $e->getMessage(), "\n";
}
// vim:set et ts=4 sw=4:
?>
