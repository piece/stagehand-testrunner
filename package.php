<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2005-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2005-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$releaseVersion = '2.0.0';
$releaseStability = 'stable';
$apiVersion = '1.1.0';
$apiStability = 'stable';
$notes = 'A new release of Stagehand_TestRunner is now available.

What\'s New in Stagehand_TestRunner 2.0.0

 * PHPSpec Support: Stagehand_TestRunner can now be used with PHPSpec (http://code.google.com/p/phpspec/). If you use PHPSpec 0.2.0devel or greater, use the specrunner script for running tests.
 * Migration to PHP 5: Stagehand_TestRunner now works with PHP 5.0.3 or greater.
 * End of support for PHPUnit 1 and 2: Starting with this release, PHPUnit 1 and 2 are no longer supported.
 * New directory structure: The source code of Stagehand_TestRunner is now located in the php_dir/src directory. Please add php_dir/src into your include_path before starting to use this package.';

$package = new PEAR_PackageFileManager2();
$result = $package->setOptions(array('filelistgenerator' => 'file',
                                     'changelogoldtonew' => false,
                                     'simpleoutput'      => true,
                                     'baseinstalldir'    => '/',
                                     'packagefile'       => 'package.xml',
                                     'packagedirectory'  => '.',
                                     'dir_roles'         => array('tests' => 'test',
                                                                  'doc' => 'doc',
                                                                  'bin' => 'script',
                                                                  'src' => 'php'),
                                     'ignore'            => array('package.php', 'package.xml'))
                               );

$package->setPackage('Stagehand_TestRunner');
$package->setPackageType('php');
$package->setSummary('Automated test runners for PHPSpec, PHPUnit, and SimpleTest');
$package->setDescription('Stagehand_TestRunner is automated test runners for PHPSpec, PHPUnit, and SimpleTest.

Stagehand_TestRunner provides command line scripts to run tests automatically. These scripts automatically detect and run all tests ending with "Spec.php" (PHPSpec) or "TestCase.php" (PHPUnit/SimpleTest) under an arbitrary directory. Stagehand_TestRunner now supports PHPSpec, PHPUnit, and SimpleTest.');
$package->setChannel('pear.piece-framework.com');
$package->setLicense('BSD License (revised)', 'http://www.opensource.org/licenses/bsd-license.php');
$package->setAPIVersion($apiVersion);
$package->setAPIStability($apiStability);
$package->setReleaseVersion($releaseVersion);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('5.0.3');
$package->setPearinstallerDep('1.4.3');
$package->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.3');
$package->addPackageDepWithChannel('required', 'Console_Getopt', 'pear.php.net', '1.2');
$package->addPackageDepWithChannel('optional', 'Console_Color', 'pear.php.net', '1.0.2');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->addInstallAs('bin/specrunner', 'specrunner');
$package->addInstallAs('bin/specrunner.bat', 'specrunner.bat');
$package->addInstallAs('bin/testrunner', 'testrunner');
$package->addInstallAs('bin/testrunner.bat', 'testrunner.bat');
$package->addInstallAs('bin/testrunner-st', 'testrunner-st');
$package->addInstallAs('bin/testrunner-st.bat', 'testrunner-st.bat');
$package->generateContents();

if (array_key_exists(1, $_SERVER['argv']) && $_SERVER['argv'][1] == 'make') {
    $package->writePackageFile();
} else {
    $package->debugPackageFile();
}

exit();

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
