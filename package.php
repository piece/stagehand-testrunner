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
 * @see        PEAR_PackageFileManager2
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';

$releaseVersion = '0.5.0';
$releaseStability = 'beta';
$apiVersion = '0.3.0';
$apiStability = 'beta';
$notes = 'A new release of Stagehand_TestRunner is now available.

What\'s New in Stagehand_TestRunner 0.5.0

 * Command Line Interface Support: testrunner1/testrunner2 can be used to run tests automatically without TestRunner.php and AllTestRunner.php. Also these commands can be used to run only the specified test case.

See the following release notes for details.

Enhancements
============ 

- Added testrunner1/testrunner2 scripts to run tests automatically without TestRunner.php and AllTestRunner.php.';

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

if (PEAR::isError($result)) {
    print $result->getMessage();
    exit();
}

$package->setPackage('Stagehand_TestRunner');
$package->setPackageType('php');
$package->setSummary('Automated test runners for PHPUnit2 and PHPUnit');
$package->setDescription('Stagehand_TestRunner is automated test runners for PHPUnit2 and PHPUnit.

Stagehand_TestRunner provides command line scripts to run tests automatically. These scripts automatically detect and run all tests that are suffixed with "TestCase.php" under an arbitrary directory. Stagehand_TestRunner now supports PHPUnit2 and PHPUnit.');
$package->setChannel('pear.piece-framework.com');
$package->setLicense('BSD License (revised)', 'http://www.opensource.org/licenses/bsd-license.php');
$package->setAPIVersion($apiVersion);
$package->setAPIStability($apiStability);
$package->setReleaseVersion($releaseVersion);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('4.3.0');
$package->setPearinstallerDep('1.4.3');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'iteman@users.sourceforge.net');
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->addInstallAs('scripts/testrunner1', 'testrunner1');
$package->addInstallAs('scripts/testrunner1.bat', 'testrunner1.bat');
$package->addInstallAs('scripts/testrunner2', 'testrunner2');
$package->addInstallAs('scripts/testrunner2.bat', 'testrunner2.bat');
$package->addPackageDepWithChannel('required', 'PHPUnit', 'pear.phpunit.de', '1.3.2');
$package->generateContents();

if (array_key_exists(1, $_SERVER['argv'])
    && $_SERVER['argv'][1] == 'make'
    ) {
    $result = $package->writePackageFile();
} else {
    $result = $package->debugPackageFile();
}

if (PEAR::isError($result)) {
    print $result->getMessage();
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
