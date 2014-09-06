<?php
$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}
require_once 'Services/OpenStreetMap.php';

class Issue20205Test extends PHPUnit_Framework_TestCase
{

    public function test20205()
    {
        // @todo: need mockups here
        $osm = new Services_OpenStreetMap(array('verbose' => true));
        $osm->getConfig()->setAcceptLanguage('ru,en-AU');
        $test = $osm->getPlace('Moskau');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $this->assertEquals(
            $display,
            "Москва, Центральный федеральный округ, Российская Федерация"
        );
        $osm->getConfig()->setAcceptLanguage('en');
        $test = $osm->getPlace('Russia');
        $attribs = $test[0]->attributes();
        $display = (string) $attribs['display_name'];
        $this->assertEquals($display, "Russian Federation");
    }
}
