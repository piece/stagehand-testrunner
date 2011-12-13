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

namespace Stagehand\TestRunner\Process\Autotest;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\TestingFramework;
use Stagehand\TestRunner\Test\FactoryAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.20.0
 */
abstract class TestCase extends FactoryAwareTestCase
{
    /**
     * @var array
     * @since Property available since Release 3.0.0
     */
    protected static $configurators;

    /**
     * @since Method available since Release 3.0.0
     */
    protected static function initializeConfigurators()
    {
        self::$configurators = array();
        self::$configurators[] = function ($testingFramework) {
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setRecursivelyScans(true);
        };
        self::$configurators[] = function ($testingFramework) {
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setRecursivelyScans(false);
        };
        self::$configurators[] = function ($testingFramework) {
            $terminal = ApplicationContext::getInstance()->createComponent('terminal'); /* @var $terminal \Stagehand\TestRunner\CLI\Terminal */
            $terminal->setColors(true);
        };
        self::$configurators[] = function ($testingFramework) {
            $autotest = ApplicationContext::getInstance()->createComponent($testingFramework . '.autotest'); /* @var $autotest \Stagehand\TestRunner\Process\AutoTest */
            $autotest->setPreloadFile('test/prepare.php');
        };
        self::$configurators[] = function ($testingFramework) {
            $autotest = ApplicationContext::getInstance()->createComponent($testingFramework . '.autotest'); /* @var $autotest \Stagehand\TestRunner\Process\AutoTest */
            $autotest->setMonitoringDirectories(array('src'));
        };
        self::$configurators[] = function ($testingFramework) {
            $runner = ApplicationContext::getInstance()->createComponent($testingFramework . '.runner'); /* @var $runner \Stagehand\TestRunner\Runner\Runner */
            $runner->setUsesNotification(true);
        };
        self::$configurators[] = function ($testingFramework) {
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setMethods(array('METHOD1'));
        };
        self::$configurators[] = function ($testingFramework) {
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setClasses(array('CLASS1'));
        };
        self::$configurators[] = function ($testingFramework) {
            $runner = ApplicationContext::getInstance()->createComponent($testingFramework . '.runner'); /* @var $runner \Stagehand\TestRunner\Runner\Runner */
            $runner->setJUnitXMLFile('FILE');
        };
        self::$configurators[] = function ($testingFramework) {
            $runner = ApplicationContext::getInstance()->createComponent($testingFramework . '.runner'); /* @var $runner \Stagehand\TestRunner\Runner\Runner */
            $runner->setLogsResultsInJUnitXMLInRealtime(true);
        };
        self::$configurators[] = function ($testingFramework) {
            $runner = ApplicationContext::getInstance()->createComponent($testingFramework . '.runner'); /* @var $runner \Stagehand\TestRunner\Runner\Runner */
            $runner->setStopsOnFailure(true);
        };
        self::$configurators[] = function ($testingFramework) {
            $testTargets = ApplicationContext::getInstance()->createComponent('test_targets'); /* @var $testTargets \Stagehand\TestRunner\Core\TestTargets */
            $testTargets->setFilePattern('PATTERN');
        };
    }

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

        $autotest = $this->applicationContext->createComponent($this->getTestingFramework() . '.autotest');
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

        call_user_func(self::$configurators[$configuratorIndex], $this->getTestingFramework());

        $autotest = $this->applicationContext->createComponent($this->getTestingFramework() . '.autotest');
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
        $preservedConfigurations = array(
            array(array('-R'), array(true)),
            array(array('-R'), array(true)),
            array(array('-R', '-c'), array(true, true)),
            array(array('-R', '-p ' . escapeshellarg('test/prepare.php')), array(true, true)),
            array(array('-R', '-w ' . escapeshellarg('src')), array(true, false)),
            array(array('-R', '-n'), array(true, true)),
            array(array('-R', '-m ' . escapeshellarg('METHOD1')), array(true, false)),
            array(array('-R', '--classes=' . escapeshellarg('CLASS1')), array(true, false)),
            array(array('-R', '--log-junit=' . escapeshellarg('FILE')), array(true, false)),
            array(array('-R', '--log-junit-realtime'), array(true, false)),
            array(array('-R', '--stop-on-failure'), array(true, true)),
            array(array('-R', '--test-file-pattern=' . escapeshellarg('PATTERN')), array(true, true)),
        );

        return array_map(function (array $preservedConfiguration) {
            static $index = 0;
            array_unshift($preservedConfiguration, $index++);
            return $preservedConfiguration;
        }, $preservedConfigurations);
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
