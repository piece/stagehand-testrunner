<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.20.0
 */

namespace Stagehand\TestRunner\Process;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\TestingFramework;
use Stagehand\TestRunner\Test\PHPUnitFactoryAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.20.0
 */
class AutotestTest extends PHPUnitFactoryAwareTestCase
{
    /**
     * @test
     * @dataProvider commandLines
     * @param string $command
     * @param array $options
     * @param string $phpConfigDir
     * @param string $builtCommand
     * @param array $builtOptions
     * @link http://redmine.piece-framework.com/issues/196
     * @link http://redmine.piece-framework.com/issues/319
     * @since Method available since Release 2.21.0
     */
    public function buildsACommandLineString($command, $options, $phpConfigDir, $builtCommand, $builtOptions)
    {
        unset($_SERVER['PHP_COMMAND']);
        if (!is_null($command)) {
            $_SERVER['_'] = $command;
        } else {
            unset($_SERVER['_']);
        }
        $_SERVER['argv'] = $GLOBALS['argv'] = $options;
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);

        $testTargets = $this->applicationContext->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
        $testTargets->setResources(array($options[ count($options) - 1 ]));

        $legacyProxy = \Phake::mock('\Stagehand\TestRunner\Core\LegacyProxy');
        \Phake::when($legacyProxy)->get_cfg_var($this->anything())->thenReturn($phpConfigDir);
        \Phake::when($legacyProxy)->is_dir($this->anything())->thenReturn(true);
        \Phake::when($legacyProxy)->realpath($this->anything())->thenReturn(true);
        $this->applicationContext->setComponent('legacy_proxy', $legacyProxy);

        $alterationMonitoring = \Phake::mock('\Stagehand\TestRunner\Process\AlterationMonitoring');
        \Phake::when($alterationMonitoring)->monitor($this->anything(), $this->anything())->thenReturn(null);
        $this->applicationContext->setComponent('alteration_monitoring', $alterationMonitoring);

        $autotest = $this->applicationContext->createComponent('autotest');
        $autotest->monitorAlteration();

        $runnerCommand = $this->readAttribute($autotest, 'runnerCommand');
        $runnerOptions = $this->readAttribute($autotest, 'runnerOptions');
        $this->assertEquals($builtCommand, $runnerCommand);
        for ($i = 0; $i < count($builtOptions); ++$i) {
            $this->assertEquals($builtOptions[$i], $runnerOptions[$i]);
        }
    }

    /**
     * @return array
     * @since Method available since Release 2.21.0
     */
    public function commandLines()
    {
        $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
        return array(
            array('/usr/bin/php', array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('/usr/bin/php'), array('-c', escapeshellarg('/etc/php5/cli'), escapeshellarg('phpunitrunner'), '-R', '--test-file-pattern=' . escapeshellarg($testTargets->getFilePattern()), escapeshellarg('test'))),
            array('/usr/bin/php', array('phpunitrunner', '-a', 'test'), false, escapeshellarg('/usr/bin/php'), array(escapeshellarg('phpunitrunner'), '-R', '--test-file-pattern=' . escapeshellarg($testTargets->getFilePattern()), escapeshellarg('test'))),
            array(null, array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('phpunitrunner'), array('-R', '--test-file-pattern=' . escapeshellarg($testTargets->getFilePattern()), escapeshellarg('test'))),
            array('phpunitrunner', array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('phpunitrunner'), array('-R', '--test-file-pattern=' . escapeshellarg($testTargets->getFilePattern()), escapeshellarg('test'))),
            array('phpunitrunner', array('phpunitrunner', '-a', 'test'), false, escapeshellarg('phpunitrunner'), array('-R', '--test-file-pattern=' . escapeshellarg($testTargets->getFilePattern()), escapeshellarg('test'))),
            array(null, array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('phpunitrunner'), array('-R', '--test-file-pattern=' . escapeshellarg($testTargets->getFilePattern()), escapeshellarg('test'))),
        );
    }
}

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class AutotestConfigurationPreservationTest extends PHPUnitFactoryAwareTestCase
{
    public static $configurators = array();

    public static function setUpBeforeClass()
    {
        self::$configurators[] = function () { // 0
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setRecursivelyScans(true);
        };
        self::$configurators[] = function () { // 1
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setRecursivelyScans(false);
        };
        self::$configurators[] = function () { // 2
            $terminal = ApplicationContext::getInstance()->createComponent('terminal'); /* @var $terminal \Stagehand\TestRunner\CLI\Terminal */
            $terminal->setColors(true);
        };
        self::$configurators[] = function () { // 3
            $autotest = ApplicationContext::getInstance()->createComponent('autotest'); /* @var $autotest \Stagehand\TestRunner\Process\AutoTest */
            $autotest->setPreloadFile('test/prepare.php');
        };
        self::$configurators[] = function () { // 4
            $autotest = ApplicationContext::getInstance()->createComponent('autotest'); /* @var $autotest \Stagehand\TestRunner\Process\AutoTest */
            $autotest->setMonitoringDirectories(array('src'));
        };
        self::$configurators[] = function () { // 5
            $runner = ApplicationContext::getInstance()->createComponent('phpunit.runner'); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
            $runner->setUsesNotification(true);
        };
        self::$configurators[] = function () { // 6
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setMethods(array('METHOD1'));
        };
        self::$configurators[] = function () { // 7
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setClasses(array('CLASS1'));
        };
        self::$configurators[] = function () { // 8
            $runner = ApplicationContext::getInstance()->createComponent('phpunit.runner'); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
            $runner->setJUnitXMLFile('FILE');
        };
        self::$configurators[] = function () { // 9
            $runner = ApplicationContext::getInstance()->createComponent('phpunit.runner'); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
            $runner->setLogsResultsInJUnitXMLInRealtime(true);
        };
        self::$configurators[] = function () { // 10
            $runner = ApplicationContext::getInstance()->createComponent('phpunit.runner'); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
            $runner->setPrintsDetailedProgressReport(true);
        };
        self::$configurators[] = function () { // 11
            $runner = ApplicationContext::getInstance()->createComponent('phpunit.runner'); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
            $runner->setStopsOnFailure(true);
        };
        self::$configurators[] = function () { // 12
            $phpunitXMLConfiguration = \Phake::mock('\Stagehand\TestRunner\Core\PHPUnitXMLConfiguration'); /* @var $phpunitXMLConfiguration \Stagehand\TestRunner\Core\PHPUnitXMLConfiguration */
            \Phake::when($phpunitXMLConfiguration)->getFileName()->thenReturn('FILE');
            $autotest = ApplicationContext::getInstance()->createComponent('autotest'); /* @var $autotest \Stagehand\TestRunner\Process\AutoTest */
            $autotest->setPHPUnitXMLConfiguration($phpunitXMLConfiguration);
        };
        self::$configurators[] = function () { // 13
            $testingFramework = \Phake::mock('\Stagehand\TestRunner\Core\TestingFramework'); /* @var $testingFramework \Stagehand\TestRunner\Core\TestingFramework */
            \Phake::when($testingFramework)->getSelected()->thenReturn(TestingFramework::CAKE);
            ApplicationContext::getInstance()->setComponent('testing_framework', $testingFramework);
            $preparer = ApplicationContext::getInstance()->createComponent('cake.preparer'); /* @var $preparer \Stagehand\TestRunner\Preparer\CakePreparer */
            $preparer->setCakePHPAppPath('DIRECTORY');
        };
        self::$configurators[] = function () { // 14
            $testingFramework = \Phake::mock('\Stagehand\TestRunner\Core\TestingFramework'); /* @var $testingFramework \Stagehand\TestRunner\Core\TestingFramework */
            \Phake::when($testingFramework)->getSelected()->thenReturn(TestingFramework::CAKE);
            ApplicationContext::getInstance()->setComponent('testing_framework', $testingFramework);
            $preparer = ApplicationContext::getInstance()->createComponent('cake.preparer'); /* @var $preparer \Stagehand\TestRunner\Preparer\CakePreparer */
            $preparer->setCakePHPCorePath('DIRECTORY');
        };
        self::$configurators[] = function () { // 15
            $testingFramework = \Phake::mock('\Stagehand\TestRunner\Core\TestingFramework'); /* @var $testingFramework \Stagehand\TestRunner\Core\TestingFramework */
            \Phake::when($testingFramework)->getSelected()->thenReturn(TestingFramework::CIUNIT);
            ApplicationContext::getInstance()->setComponent('testing_framework', $testingFramework);
            $preparer = ApplicationContext::getInstance()->createComponent('ciunit.preparer'); /* @var $preparer \Stagehand\TestRunner\Preparer\CIUnitPreparer */
            $preparer->setCIUnitPath('DIRECTORY');
        };
        self::$configurators[] = function () { // 16
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setFilePattern('PATTERN');
        };
    }

    /**
     * @test
     * @dataProvider preservedConfigurations
     * @param integer $configuratorIndex
     * @param array $normalizedOption
     * @param array $shouldPreserve
     * @link http://redmine.piece-framework.com/issues/314
     */
    public function preservesSomeConfigurations(
        $configuratorIndex,
        array $normalizedOption,
        array $shouldPreserve)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', '-a');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);

        $notifier = \Phake::mock('\Stagehand\TestRunner\Notification\Notifier');
        \Phake::when($notifier)->notifyResult($this->anything())->thenReturn(null);
        $this->applicationContext->setComponent('notifier', $notifier);

        $legacyProxy = \Phake::mock('\Stagehand\TestRunner\Core\LegacyProxy');
        \Phake::when($legacyProxy)->passthru($this->anything())->thenReturn(null);
        \Phake::when($legacyProxy)->is_dir($this->anything())->thenReturn(true);
        $this->applicationContext->setComponent('legacy_proxy', $legacyProxy);

        $alterationMonitoring = \Phake::mock('\Stagehand\TestRunner\Process\AlterationMonitoring');
        \Phake::when($alterationMonitoring)->monitor($this->anything(), $this->anything())->thenReturn(null);
        $this->applicationContext->setComponent('alteration_monitoring', $alterationMonitoring);

        call_user_func(self::$configurators[$configuratorIndex]);

        $autotest = $this->applicationContext->createComponent('autotest');
        $autotest->monitorAlteration();

        $runnerOptions = $this->readAttribute($autotest, 'runnerOptions');

        for ($i = 0; $i < count($normalizedOption); ++$i) {
            $preserved = in_array($normalizedOption[$i], $runnerOptions);
            $this->assertEquals($shouldPreserve[$i], $preserved);
        }
    }

    /**
     * @return array
     * @link http://redmine.piece-framework.com/issues/314
     */
    public function preservedConfigurations()
    {
        return array(
            array(0, array('-R'), array(true)),
            array(1, array('-R'), array(true)),
            array(2, array('-R', '-c'), array(true, true)),
            array(3, array('-R', '-p ' . escapeshellarg('test/prepare.php')), array(true, true)),
            array(4, array('-R', '-w ' . escapeshellarg('src')), array(true, false)),
            array(5, array('-R', '-n'), array(true, true)),
            array(6, array('-R', '-m ' . escapeshellarg('METHOD1')), array(true, false)),
            array(7, array('-R', '--classes=' . escapeshellarg('CLASS1')), array(true, false)),
            array(8, array('-R', '--log-junit=' . escapeshellarg('FILE')), array(true, false)),
            array(9, array('-R', '--log-junit-realtime'), array(true, false)),
            array(10, array('-R', '-v'), array(true, true)),
            array(11, array('-R', '--stop-on-failure'), array(true, true)),
            array(12, array('-R', '--phpunit-config=' . escapeshellarg('FILE')), array(true, true)),
            array(13, array('-R', '--cakephp-app-path=' . escapeshellarg('DIRECTORY')), array(true, true)),
            array(14, array('-R', '--cakephp-core-path=' . escapeshellarg('DIRECTORY')), array(true, true)),
            array(15, array('-R', '--ciunit-path=' . escapeshellarg('DIRECTORY')), array(true, true)),
            array(16, array('-R', '--test-file-pattern=' . escapeshellarg('PATTERN')), array(true, true)),
        );
    }
}

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
