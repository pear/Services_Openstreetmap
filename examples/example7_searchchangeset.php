<?php
/**
 * example6_closedway.php
 * 21-Aug-2011
 *
 * PHP Version 5
 *
 * @category example6_closedway
 * @package  example6_closedway
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     example6_closedway.php
*/

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/Openstreetmap.php';

$osm = new Services_Openstreetmap();
try {
    $changesets = $osm->searchChangesets(
        array(
            new Services_Openstreetmap_Criterion('user', 11324),
            new Services_Openstreetmap_Criterion(
                'bbox',
                -8.0590275,
                52.9347449,
                -7.9966939,
                52.9611999
            ),
            new Services_Openstreetmap_Criterion('closed'),
        )
    );
}
catch (Services_Openstreetmap_Exception $e) {
    die($e->getMessage());
}
foreach ($changesets as $changeset) {
    echo $changeset->getCreatedAt(), "\t";
    try {
        echo $changeset->getTag('comment'), "\n";
    } catch(Exception $e) {
        echo "\n";
    }
}
?>
