<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2005-2010 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2005-2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

require_once 'PEAR/PackageFileManager2.php';
require_once 'PEAR.php';

PEAR::staticPushErrorHandling(PEAR_ERROR_CALLBACK, create_function('$error', 'var_dump($error); exit();'));

$packageName = 'Stagehand_TestRunner';
$releaseVersion = '2.10.0';
$releaseStability = 'stable';
$apiVersion = '1.1.0';
$apiStability = 'stable';
$notes = "What's New in $packageName $releaseVersion

 Logs test results in real-time into the specified file in the JUnit XML format. (PHPUnit, PHPT, and SimpleTest):

  phpunitrunner --log-junit=FILE --log-junit-realtime DIRECTORY_OR_FILE

  By specifying the --log-junit-realtime option, the test results get to be written in real-time by element-by-element.

 Enhanced SimpleTest support:

  The following features have been supported in SimpleTest:

   * Runs only the specified tests in the specified file.
   * Runs only the tests in the specified classes.
   * Logs test results into the specified file in the JUnit XML format.";

$package = new PEAR_PackageFileManager2();
$package->setOptions(array('filelistgenerator' => 'file',
                           'changelogoldtonew' => false,
                           'simpleoutput'      => true,
                           'baseinstalldir'    => '/',
                           'packagefile'       => 'package.xml',
                           'packagedirectory'  => '.',
                           'dir_roles'         => array('bin' => 'script',
                                                        'data' => 'php',
                                                        'doc' => 'doc',
                                                        'examples' => 'doc',
                                                        'src' => 'php',
                                                        'tests' => 'test'),
                           'ignore'            => array('package.php'))
                     );

$package->setPackage($packageName);
$package->setPackageType('php');
$package->setSummary('A test runner for Test Driven Development');
$package->setDescription('Stagehand_TestRunner provides a test runner to run unit tests. Stagehand_TestRunner strongly supports Test Driven Development by various features.');
$package->setChannel('pear.piece-framework.com');
$package->setLicense('New BSD License', 'http://www.opensource.org/licenses/bsd-license.php');
$package->setAPIVersion($apiVersion);
$package->setAPIStability($apiStability);
$package->setReleaseVersion($releaseVersion);
$package->setReleaseStability($releaseStability);
$package->setNotes($notes);
$package->setPhpDep('5.2.0');
$package->setPearinstallerDep('1.4.3');
$package->addPackageDepWithChannel('required', 'Stagehand_AccessControl', 'pear.piece-framework.com', '0.1.0');
$package->addPackageDepWithChannel('required', 'Stagehand_AlterationMonitor', 'pear.piece-framework.com', '1.0.0');
$package->addPackageDepWithChannel('required', 'Stagehand_Autoload', 'pear.piece-framework.com', '0.4.0');
$package->addPackageDepWithChannel('required', 'Stagehand_CLIController', 'pear.piece-framework.com', '0.1.0');
$package->addPackageDepWithChannel('required', 'Stagehand_DirectoryScanner', 'pear.piece-framework.com', '1.0.0');
$package->addPackageDepWithChannel('optional', 'Console_Color', 'pear.php.net', '1.0.2');
$package->addPackageDepWithChannel('optional', 'Net_Growl', 'pear.php.net', '0.7.0');
$package->addPackageDepWithChannel('optional', 'PHPSpec', 'pear.phpspec.org', '0.2.3');
$package->addPackageDepWithChannel('optional', 'PHPUnit', 'pear.phpunit.de', '3.4.1');
$package->addExtensionDep('required', 'pcre');
$package->addExtensionDep('required', 'SPL');
$package->addExtensionDep('optional', 'dom');
$package->addExtensionDep('optional', 'mbstring');
$package->addExtensionDep('optional', 'xmlwriter');
$package->addMaintainer('lead', 'iteman', 'KUBO Atsuhiro', 'kubo@iteman.jp');
$package->addMaintainer('developer', 'kumatch', 'KUMAKURA Yousuke', 'kumatch@gmail.com');
$package->addGlobalReplacement('package-info', '@package_version@', 'version');
$package->addInstallAs('bin/phpspecrunner', 'phpspecrunner');
$package->addInstallAs('bin/phpspecrunner.bat', 'phpspecrunner.bat');
$package->addInstallAs('bin/phptrunner', 'phptrunner');
$package->addInstallAs('bin/phptrunner.bat', 'phptrunner.bat');
$package->addInstallAs('bin/phpunitrunner', 'phpunitrunner');
$package->addInstallAs('bin/phpunitrunner.bat', 'phpunitrunner.bat');
$package->addInstallAs('bin/simpletestrunner', 'simpletestrunner');
$package->addInstallAs('bin/simpletestrunner.bat', 'simpletestrunner.bat');

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
