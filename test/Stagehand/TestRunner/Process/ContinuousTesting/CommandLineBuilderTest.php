<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.6.0
 */

namespace Stagehand\TestRunner\Process\ContinuousTesting;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Test\PHPUnitComponentAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.6.0
 */
class CommandLineBuilderTest extends PHPUnitComponentAwareTestCase
{
    /**
     * @var array
     */
    private static $configurators;

    public static function setUpBeforeClass()
    {
        self::$configurators = array();
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('collector')->setRecursive(true);
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('collector')->setRecursive(false);
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $terminal = $applicationContext->createComponent('terminal'); /* @var $terminal \Stagehand\TestRunner\CLI\Terminal */
            $terminal->setColor(true);
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->getEnvironment()->setPreloadScript('test/prepare.php');
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('continuous_test_runner')->setWatchDirs(array('src'));
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('runner')->setNotify(true);
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $testTargetRepository = $applicationContext->createComponent('test_target_repository'); /* @var $testTargetRepository \Stagehand\TestRunner\Core\TestTargetRepository */
            $testTargetRepository->setMethods(array('METHOD1'));
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $testTargetRepository = $applicationContext->createComponent('test_target_repository'); /* @var $testTargetRepository \Stagehand\TestRunner\Core\TestTargetRepository */
            $testTargetRepository->setClasses(array('CLASS1'));
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('runner')->setJUnitXMLFile('FILE');
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('runner')->setJUnitXMLRealtime(true);
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('runner')->setStopOnFailure(true);
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $testTargetRepository = $applicationContext->createComponent('test_target_repository'); /* @var $testTargetRepository \Stagehand\TestRunner\Core\TestTargetRepository */
            $testTargetRepository->setFilePattern('PATTERN');
        };
        self::$configurators[] = function (ApplicationContext $applicationContext) {
            $applicationContext->createComponent('runner')->setDetailedProgress(true);
        };
    }

    /**
     * @test
     * @dataProvider commandLines
     * @param string $inputCommand
     * @param array $inputOptions
     * @param string $phpConfigDir
     * @param string $expectedBuiltCommand
     * @param array $expectedBuiltOptions
     * @link http://redmine.piece-framework.com/issues/196
     * @link http://redmine.piece-framework.com/issues/319
     * @link http://redmine.piece-framework.com/issues/393
     */
    public function buildsACommandLineString($inputCommand, array $inputOptions, $phpConfigDir, $expectedBuiltCommand, $expectedBuiltOptions)
    {
        unset($_SERVER['PHP_COMMAND']);
        if (!is_null($inputCommand)) {
            $_SERVER['_'] = $inputCommand;
        } else {
            unset($_SERVER['_']);
        }
        $_SERVER['argv'] = $GLOBALS['argv'] = $inputOptions;
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);

        $testTargetRepository = $this->createComponent('test_target_repository'); /* @var $testTargetRepository \Stagehand\TestRunner\Core\TestTargetRepository */
        $testTargetRepository->setResources(array($inputOptions[ count($inputOptions) - 1 ]));

        $legacyProxy = \Phake::mock('Stagehand\TestRunner\Util\LegacyProxy');
        \Phake::when($legacyProxy)->get_cfg_var($this->anything())->thenReturn($phpConfigDir);
        $this->setComponent('legacy_proxy', $legacyProxy);

        $this->createComponent('command_line_builder')->build();

        $builtCommand = $this->readAttribute($this->createComponent('command_line_builder'), 'command');
        $builtOptions = $this->readAttribute($this->createComponent('command_line_builder'), 'options');
        $this->assertThat($builtCommand, $this->equalTo($expectedBuiltCommand));
        for ($i = 0; $i < count($expectedBuiltOptions); ++$i) {
            $this->assertThat($builtOptions[$i], $this->equalTo($expectedBuiltOptions[$i]));
        }
    }

    /**
     * @return array
     */
    public function commandLines()
    {
        return array(
            array('/usr/bin/php', array('testrunner', strtolower($this->getPluginID()), '-a', 'test'), '/etc/php5/cli', escapeshellarg('/usr/bin/php'), array('-c', escapeshellarg('/etc/php5/cli'), escapeshellarg('testrunner'), escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
            array('/usr/bin/php', array('testrunner', strtolower($this->getPluginID()), '-a', 'test'), false, escapeshellarg('/usr/bin/php'), array(escapeshellarg('testrunner'), escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
            array(null, array('testrunner', strtolower($this->getPluginID()), '-a', 'test'), '/etc/php5/cli', escapeshellarg('testrunner'), array( escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
            array('testrunner', array('testrunner', strtolower($this->getPluginID()), '-a', 'test'), '/etc/php5/cli', escapeshellarg('testrunner'), array( escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
            array('testrunner', array('testrunner', strtolower($this->getPluginID()), '-a', 'test'), false, escapeshellarg('testrunner'), array(escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
            array(null, array('testrunner', strtolower($this->getPluginID()), '-a', 'test'), '/etc/php5/cli', escapeshellarg('testrunner'), array(escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
            array(null, array('/path/to/testrunner', strtolower($this->getPluginID()), '-a', 'test'), '/etc/php5/cli', escapeshellarg('/path/to/testrunner'), array(escapeshellarg(strtolower($this->getPluginID())), '-R', escapeshellarg('test'))),
        );
    }

    /**
     * @test
     * @dataProvider commandLineOptions
     * @param integer $configuratorIndex
     * @param array $normalizedOptions
     * @param array $shouldPreserve
     * @link http://redmine.piece-framework.com/issues/314
     */
    public function buildsCommandLineOptions($configuratorIndex, array $normalizedOptions,array $shouldPreserve)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/testrunner', '-a');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);

        call_user_func(self::$configurators[$configuratorIndex], $this->applicationContext);

        $this->createComponent('command_line_builder')->build();

        $builtOptions = $this->readAttribute($this->createComponent('command_line_builder'), 'options');
        for ($i = 0; $i < count($normalizedOptions); ++$i) {
            $preserved = in_array($normalizedOptions[$i], $builtOptions);
            $this->assertThat($preserved, $this->equalTo($shouldPreserve[$i]));
        }
    }

    /**
     * @return array
     * @link http://redmine.piece-framework.com/issues/314
     */
    public function commandLineOptions()
    {
        $preservedConfigurations = array(
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R'), array(true, true)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R'), array(true, true)),
            array(array('--ansi', escapeshellarg(strtolower($this->getPluginID())), '-R'), array(true, true, true)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '-p ' . escapeshellarg('test/prepare.php')), array(true, true, true)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '-w ' . escapeshellarg('src')), array(true, true, false)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '-m'), array(true, true, true)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--test-method=' . escapeshellarg('METHOD1')), array(true, true, false)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--test-class=' . escapeshellarg('CLASS1')), array(true, true, false)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--log-junit=' . escapeshellarg('FILE')), array(true, true, false)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--log-junit-realtime'), array(true, true, false)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--stop-on-failure'), array(true, true, true)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--test-file-pattern=' . escapeshellarg('PATTERN')), array(true, true, true)),
            array(array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--detailed-progress'), array(true, true, true)),
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
