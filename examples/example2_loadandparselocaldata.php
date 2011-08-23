<?php
/**
 * example2_loadandparselocaldata.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_Openstreetmap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     osmx.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

$osm = new Services_Openstreetmap();

$osm->loadXML("./osm.osm");
$results = $osm->search(array("amenity" => "pharmacy"));
echo "List of Pharmacies\n";
echo "==================\n\n";

foreach ($results as $result) {
    $name = null;
    $addr_street = null;
    $addr_city = null;
    $addr_country = null;
    $addr_housename = null;
    $addr_housenumber = null;
    $opening_hours = null;
    $phone = null;

    extract($result);
    $line1 = ($addr_housenumber) ? $addr_housenumber : $addr_housename;
    if ($line1 != null) {
        $line1 .= ', ';
    }
    echo  "$name\n{$line1}{$addr_street}\n$phone\n$opening_hours\n\n";
}
// vim:set et ts=4 sw=4:
?>
