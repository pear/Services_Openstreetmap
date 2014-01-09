<?php
/**
 * example1_savetolocalfile.php
 * 22-Nov-2009
 *
 * PHP Version 5
 *
 * @category Services
 * @package  Services_OpenStreetMap
 * @author   Ken Guest <kguest@php.net>
 * @license  BSD http://www.opensource.org/licenses/bsd-license.php
 * @version  Release: @package_version@
 * @link     example12_notes.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

$osm = new Services_OpenStreetMap(array('verbose' => true));

try {
    $osm->getConfig()->setServer('http://api.openstreetmap.org/');
} catch (Exception $ex) {
    var_dump($ex->getMessage());
    // Fall back to default server...so carry on.
}

$notes = $osm->getNotesByBbox(
    -8.24724502663969, 52.8482419135407, -8.17416147865479, 52.8995782553221
);
echo $notes;
foreach ($notes as $note) {
    echo 'ID: ', $note->getId(), "\n";
    echo 'LAT/LON:     ', $note->getLat(), "/", $note->getLon(), "\n";
    echo 'STATUS:      ', $note->getStatus(), "\n";
    echo 'CREATED:     ', strftime("%c %T", $note->getDateCreated()), "\n";
    echo 'URL:         ', $note->getUrl(), "\n";
    echo 'COMMENT URL: ', $note->getCommentUrl(), "\n";
    echo 'CLOSE URL:   ', $note->getCloseUrl(), "\n";

    echo "\n\nComments:\n";
    $comments = $note->getComments();
    foreach($comments as $comment) {
        echo 'Action: ', $comment->getAction(), "\n";
        echo 'Date: ', $comment->getDate(), "\n";
        echo $comment->getText(), "\n";
        echo $comment->getHtml(), "\n";
    }
}

// vim:set et ts=4 sw=4:
?>
