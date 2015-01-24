<?php
/**
 * Main test suite for Services_OpenStreetMap
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       AllTests.php
 */
$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

if (stream_resolve_include_path('PHPUnit/TextUI/TestRunner.php')) {
    include_once 'PHPUnit/TextUI/TestRunner.php';
}

require_once 'ChangesetTest.php';
require_once 'ConfigTest.php';
require_once 'CriterionTest.php';
require_once 'NodeTest.php';
require_once 'NotesTest.php';
require_once 'NominatimTest.php';
require_once 'OpeningHoursTest.php';
require_once 'OSMTest.php';
require_once 'PearBug20205Test.php';
require_once 'PermissionsTest.php';
require_once 'RelationTest.php';
require_once 'UserTest.php';
require_once 'WayFullTest.php';
require_once 'WayTest.php';

/**
 * Main test suite for Services_OpenStreetMap.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       AllTests.php
 */
class AllTests
{
    /**
     * Launches the TextUI test runner
     *
     * @return void
     * @uses PHPUnit_TextUI_TestRunner
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Adds all class test suites into the master suite
     *
     * @return PHPUnit_Framework_TestSuite a master test suite
     *                                     containing all class test suites
     * @uses PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Services_OpenStreetMap Tests');

        $suite->addTestSuite('ChangesetTest');
        $suite->addTestSuite('ConfigTest');
        $suite->addTestSuite('CriterionTest');
        $suite->addTestSuite('NodeTest');
        $suite->addTestSuite('NotesTest');
        $suite->addTestSuite('NominatimTest');
        $suite->addTestSuite('OpeningHoursTest');
        $suite->addTestSuite('OSMTest');
        $suite->addTestSuite('PearBug20205Test');
        $suite->addTestSuite('PermissionsTest');
        $suite->addTestSuite('RelationTest');
        $suite->addTestSuite('UserTest');
        $suite->addTestSuite('WayFullTest');
        $suite->addTestSuite('WayTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
