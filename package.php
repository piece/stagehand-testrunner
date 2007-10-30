<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$releaseVersion = '1.2.0';
$releaseStability = 'stable';
$apiVersion = '1.1.0';
$apiStability = 'stable';
$notes = 'A new release of Stagehand_TestRunner is now available.

What\'s New in Stagehand_TestRunner 1.2.0

 * Coloring the result of a test runner run: The result of a test runner run can be colored by "-c" option.
 * PHP script preloading: An arbitrary PHP script can be loaded before tests run for testing in a closed environment.

See the following release notes for details.

Enhancements
============

- Added support for coloring the result of a test runner run with ANSI console colors.
- Added support for preloading an arbitrary PHP script for testing in a closed environment. (Ticket #4)';

$package = new PEAR_PackageFileManager2();
$result = $package->setOptions(array('filelistgenerator' => 'svn',
                                     'changelogoldtonew' => false,
                                     'simpleoutput'      => true,
                                     'baseinstalldir'    => '/',
                                     'packagefile'       => 'package.xml',
                                     'packagedirectory'  => '.',
                                     'dir_roles'         => array('tests' => 'test',
                                                                  'docs' => 'doc',
                                                                  'scripts' => 'script'),
                                     'ignore'            => array('package.php', 'package.xml'))
                               );

$package->setPackage('Stagehand_TestRunner');
$package->setPackageType('php');
$package->setSummary('Automated test runners for PHPUnit and SimpleTest');
$package->setDescription('Stagehand_TestRunner is automated test runners for PHPUnit and SimpleTest.

Stagehand_TestRunner provides command line scripts to run tests automatically. These scripts automatically detect and run all tests that are suffixed with "TestCase.php" under an arbitrary directory. Stagehand_TestRunner now supports PHPUnit 3, PHPUnit 2, PHPUnit 1, and SimpleTest.');
$package->setChannel('pear.piece-framework.com');
$package->setLicense('BSD License (revised)', 'http://www.opensource.org/licenses/bsd-license.php');
$package->setAPIVersion($apiVersion);
$package->setAPIStability($apiStability);
$package->setReleaseVersion($releaseVersion);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.4.3');
$package->addPackageDepWithChannel('required', 'PEAR', 'pear.php.net', '1.4.3');
$package->addPackageDepWithChannel('required', 'Console_Getopt', 'pear.php.net', '1.2');
$package->addPackageDepWithChannel('required', 'PHP_Compat', 'pear.php.net', '1.5.0');
$package->addPackageDepWithChannel('optional', 'Console_Color', 'pear.php.net', '1.0.2');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->addInstallAs('scripts/testrunner1', 'testrunner1');
$package->addInstallAs('scripts/testrunner1.bat', 'testrunner1.bat');
$package->addInstallAs('scripts/testrunner2', 'testrunner2');
$package->addInstallAs('scripts/testrunner2.bat', 'testrunner2.bat');
$package->addInstallAs('scripts/testrunner3', 'testrunner3');
$package->addInstallAs('scripts/testrunner3.bat', 'testrunner3.bat');
$package->addInstallAs('scripts/testrunner-st', 'testrunner-st');
$package->addInstallAs('scripts/testrunner-st.bat', 'testrunner-st.bat');
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
?>
