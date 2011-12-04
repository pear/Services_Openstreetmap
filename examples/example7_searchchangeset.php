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
$id = 18197393;
$osm = new Services_Openstreetmap();
try {
    $changesets = $osm->searchChangesets(
        array(
            new Services_Openstreetmap_Criterion('uid', 113243),
            new Services_Openstreetmap_Criterion('display_name', 'kenguest'),
        )
    );
}
catch (Services_Openstreetmap_Exception $e) {
    var_dump($e);
}
foreach($changesets as $changeset) {
    echo $changeset->getCreatedAt(), "\t", $changeset->getTag('comment'), "\n";
}
?>
