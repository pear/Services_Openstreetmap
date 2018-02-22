<?php
/**
 * example14_user_info_extended.php
 * 15-Mar-2017
 *
 * PHP Version 5
 *
 * @category example14_user_info_extended
 * @package  example14_user_info_extended
 * @author   Ken Guest <ken@linux.ie>
 * @license  GPL (see http://www.gnu.org/licenses/gpl.txt)
 * @version  CVS: <cvs_id>
 * @link     example14_user_info_extended.php
 * @todo
*/


$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . PATH_SEPARATOR . get_include_path());
}

require_once 'Services/OpenStreetMap.php';
require_once 'HTTP/Request2.php';
require_once 'HTTP/Request2/Adapter/Mock.php';

$osm = new Services_OpenStreetMap();
$config = array(
        'server'   => 'https://api.openstreetmap.org/',
        'passwordfile' => __DIR__ . '/credentials'
);
$id = 11324;
$user = $osm->getUser();
var_dump ($user);

?>
