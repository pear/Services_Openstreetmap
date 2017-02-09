<?php
/**
 * example10.php
 * 02-Oct-2012
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     example10_user_info.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

$id = 1;

$osm = new Services_OpenStreetMap();
$user = $osm->getUserById($id);

if ($user === false) {
    die("User #$id not found\n");
}

echo "Display Name: ", $user->getDisplayName(), " (", $user->getId(),")\n";
echo "Roles "; var_dump($user->getRoles()); echo "\n";
echo "#Changesets ", $user->getChangesets(), "\n";
echo "#Traces ", $user->getTraces(), "\n";
echo "#BlocksReceived ", $user->getBlocksReceived(), "\n";
echo "#ActiveBlocksReceived ", $user->getActiveBlocksReceived(), "\n";
echo "#BlocksIssued ", $user->getBlocksIssued(), "\n";
echo "#Languages "; var_dump($user->getLanguages()); echo "\n";
?>
