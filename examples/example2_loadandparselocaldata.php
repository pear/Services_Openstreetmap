<?php
/**
 * example2_loadandparselocaldata.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     osmx.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

$osm = new Services_OpenStreetMap();

$osm->loadXML("./osm.osm");
$results = $osm->search(array("amenity" => "pharmacy"));
echo "List of Pharmacies\n";
echo "==================\n\n";

foreach ($results as $result) {
    $name = $result->getTag('name');
    $addrStreet = $result->getTag('addr:street');
    $addrCity = $result->getTag('addr:city');
    $addrCountry = $result->getTag('addr:country');
    $addrHouseName = $result->getTag('addr:housename');
    $addrHouseNumber = $result->getTag('addr:housenumber');
    $openingHours = $result->getTag('opening_hours');
    $phone = $result->getTag('phone');

    $line1 = ($addrHouseNumber) ? $addrHouseNumber : $addrHouseName;
    if ($line1 != null) {
        $line1 .= ', ';
    }
    echo  "$name\n{$line1}{$addrStreet}\n$phone\n$openingHours\n\n";
}
// vim:set et ts=4 sw=4:
?>
