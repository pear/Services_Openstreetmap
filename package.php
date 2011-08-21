<?php
require_once 'PEAR/PackageFileManager2.php';
// recommended - makes PEAR_Errors act like exceptions (kind of)
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$packagexml = new PEAR_PackageFileManager2();
$packagexml->setOptions(
    array(
    'filelistgenerator' => 'file',
    'packagedirectory' => dirname(__FILE__),
    'baseinstalldir' => '/',
    'dir_roles' => array(
        'examples' => 'doc',
        'docs'     => 'doc',
        'tests'    => 'test'
        ),
    'ignore' => array(
        'package.php',
        'package.xml',
        'Services_Capsule.tmproj',
        'package.xml.old'
    ),
    'simpleoutput' => true)
);
$packagexml->setPackageType('php');
$packagexml->addRelease();
$packagexml->setPackage('Services_Openstreetmap');
$packagexml->setChannel('pear.php.net');
$packagexml->setReleaseVersion('0.0.1');
$packagexml->setAPIVersion('0.0.1');
$packagexml->setReleaseStability('alpha');
$packagexml->setAPIStability('alpha');
$packagexml->setSummary('Openstreetmap Services');
$packagexml->setDescription('Provide an interface to the Openstreetmap services.');
$packagexml->setNotes('Initial release');
$packagexml->setPhpDep('5.2.0');
$packagexml->setPearinstallerDep('1.7.0');
$packagexml->addPackageDepWithChannel(
    'required',
    'HTTP_Request2',
    'pear.php.net',
    '0.5.1'
);
$packagexml->addMaintainer('lead', 'kguest', 'Ken Guest', 'kguest@php.net');
$packagexml->setLicense(
    'BSD License',
    'http://www.opensource.org/licenses/bsd-license.html'
);
$packagexml->addGlobalReplacement('package-info', '@PEAR-VER@', 'version');
$packagexml->generateContents();

if (isset($_GET['make'])
    || (isset($_SERVER['argv'])
    && @$_SERVER['argv'][1] == 'make')
) {
    $packagexml->writePackageFile();
} else {
    $packagexml->debugPackageFile();
}
// vim:set et ts=4 sw=4:
?>
