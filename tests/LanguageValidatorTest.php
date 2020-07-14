<?php
/**
 * Unit testing for Services_OpenStreetMap_LanguageValidator class.
 *
 * PHP Version 5
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @version    Release: @package_version@
 * @link       LanguageValidatorTest.php
 */

$version = '@package_version@';
if (strstr($version, 'package_version')) {
    set_include_path(dirname(dirname(__FILE__)) . ':' . get_include_path());
}

require_once 'Services/OpenStreetMap.php';

// don't pull in file if using phpunit installed as a PHAR
if (stream_resolve_include_path('PHPUnit/Framework/TestCase.php')) {
    include_once 'PHPUnit/Framework/TestCase.php';
}

/**
 * Test Services_OpenStreetMap_Config functionality and how it's used
 * throughout the Services_OpenStreetMap package.
 *
 * @category   Services
 * @package    Services_OpenStreetMap
 * @subpackage UnitTesting
 * @author     Ken Guest <kguest@php.net>
 * @license    BSD http://www.opensource.org/licenses/bsd-license.php
 * @link       ConfigTest.php
 */
class LanguageValidatorTest extends PHPUnit_Framework_TestCase
{
    public function testValidatorLanguageConstructor()
    {
        $v = new Services_OpenStreetMap_Validator_Language('en');
        $v = new Services_OpenStreetMap_Validator_Language('en-ie');
    }
    /**
     * testValidatorLanguageConstructorInvalid
     *
     * @expectedException        Services_OpenStreetMap_InvalidLanguageException
     * @expectedExceptionMessage Language Invalid: 1
     *
     * @return void
     */
    public function testValidatorLanguageConstructorInvalid()
    {
        $v = new Services_OpenStreetMap_Validator_Language('1');
        $v = new Services_OpenStreetMap_Validator_Language('1-2');
    }
}
