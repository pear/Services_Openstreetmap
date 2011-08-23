<?php
$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'ConfigTest.php';
require_once 'OSMTest.php';

class AllTests
{
	public static function main()
	{
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite()
	{
		$suite = new PHPUnit_Framework_TestSuite('Services_Openstreetmap Tests');
		$suite->addTestSuite('ConfigTest');
		$suite->addTestSuite('OSMTest');

		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}
// vim:set noet ts=4 sw=4:
