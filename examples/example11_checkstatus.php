#!/usr/bin/php
<?php
/**
 * Check API/DB/GPX server status
 * 28 May 2012
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @link     example11_checkstatus.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

$config = array(
    'server' => 'https://api.openstreetmap.org/',
);

$osm = new Services_OpenStreetMap($config);

echo "Querying {$config['server']}\n\n";
echo "API Status: " , $osm->getApiStatus(), "\n";
echo "DB  Status: " , $osm->getDatabaseStatus(), "\n";
echo "GPX Status: " , $osm->getGpxStatus(), "\n";
