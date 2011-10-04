<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.13.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.13.0
 */
class Stagehand_TestRunner_TestRunnerCLIControllerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Stagehand_TestRunner_Autotest
     * @since Property available since Release 2.18.0
     */
    protected $autotest;

    /**
     * @var string
     * @since Property available since Release 2.18.1
     */
    protected $phpConfigDir = false;

    /**
     * @var integer
     * @since Property available since Release 2.20.0
     */
    protected $nestingLevel = 1;

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
     */
    public function buildsACommandLineStringWithAutotest($command, $options, $phpConfigDir, $builtCommand, $builtOptions)
    {
        unset($_SERVER['PHP_COMMAND']);
        $this->phpConfigDir = $phpConfigDir;
        if (!is_null($command)) {
            $_SERVER['_'] = $command;
        } else {
            unset($_SERVER['_']);
        }
        $_SERVER['argv'] = $GLOBALS['argv'] = $options;
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $this->createTestRunnerCLIController()->run();

        $runnerCommand = $this->readAttribute($this->autotest, 'runnerCommand');
        $runnerOptions = $this->readAttribute($this->autotest, 'runnerOptions');
        $this->assertEquals($builtCommand, $runnerCommand);
        for ($i = 0; $i < count($builtOptions); ++$i) {
            $this->assertEquals($builtOptions[$i], $runnerOptions[$i]);
        }
    }

    /**
     * @return array
     * @since Method available since Release 2.18.1
     */
    public function commandLines()
    {
        return array(
            array('/usr/bin/php', array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('/usr/bin/php'), array('-c', escapeshellarg('/etc/php5/cli'), escapeshellarg('phpunitrunner'), '-R', escapeshellarg('test'))),
            array('/usr/bin/php', array('phpunitrunner', '-a', 'test'), false, escapeshellarg('/usr/bin/php'), array(escapeshellarg('phpunitrunner'), '-R', escapeshellarg('test'))),
            array(null, array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('phpunitrunner'), array('-R', escapeshellarg('test'))),
            array('phpunitrunner', array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('phpunitrunner'), array('-R', escapeshellarg('test'))),
            array('phpunitrunner', array('phpunitrunner', '-a', 'test'), false, escapeshellarg('phpunitrunner'), array('-R', escapeshellarg('test'))),
            array(null, array('phpunitrunner', '-a', 'test'), '/etc/php5/cli', escapeshellarg('phpunitrunner'), array('-R', escapeshellarg('test'))),
        );
    }

    /**
     * @param Stagehand_TestRunner_Config $config
     * @return Stagehand_TestRunner_Autotest
     * @since Method available since Release 2.18.0
     */
    public function createAutotest(Stagehand_TestRunner_Config $config)
    {
        $monitor = $this->getMock('Stagehand_AlterationMonitor', array('monitor'), array(null, null));
        $monitor->expects($this->any())
                ->method('monitor')
                ->will($this->returnValue(null));
        $this->autotest = $this->getMock(
            'Stagehand_TestRunner_Autotest',
            array('createAlterationMonitor', 'getMonitoringDirectories', 'executeRunnerCommand', 'getPHPConfigDir'),
            array($config)
        );
        $this->autotest->expects($this->any())
                       ->method('createAlterationMonitor')
                       ->will($this->returnValue($monitor));
        $this->autotest->expects($this->any())
                       ->method('getMonitoringDirectories')
                       ->will($this->returnValue(array()));
        $this->autotest->expects($this->any())
                       ->method('executeRunnerCommand')
                       ->will($this->returnValue(0));
        $this->autotest->expects($this->any())
                       ->method('getPHPConfigDir')
                       ->will($this->returnCallback(array($this, 'getPHPConfigDir')));
        return $this->autotest;
    }

    /**
     * @since Method available since Release 2.18.1
     */
    public function getPHPConfigDir()
    {
        return $this->phpConfigDir;
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/202
     * @since Method available since Release 2.14.0
     */
    public function supportsPhpunitXmlConfigurationFile()
    {
        $phpunitConfigFile = 'phpunit.xml';
        $_SERVER['argv'] = $GLOBALS['argv'] = array(
            'bin/phpunitrunner',
            '-p', 'tests/prepare.php',
            '--phpunit-config=' . $phpunitConfigFile
        );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $controller = $this->createTestRunnerCLIController();
        $controller->run();
        $config = $this->readAttribute($controller, 'config');
        $this->assertNotNull($config->phpunitConfigFile);
        $this->assertEquals($phpunitConfigFile, $config->phpunitConfigFile);
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/230
     * @since Method available since Release 2.16.0
     */
    public function supportsTestFilesWithAnyPattern()
    {
        $testFilePattern = '^test_';
        $_SERVER['argv'] = $GLOBALS['argv'] = array(
            'bin/phpunitrunner',
            '-p', 'tests/prepare.php',
            '--test-file-pattern=' . $testFilePattern
        );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $controller = $this->createTestRunnerCLIController();
        $controller->run();
        $this->assertEquals($testFilePattern, $this->readAttribute($controller, 'config')->testFilePattern);
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/211
     * @since Method available since Release 2.14.0
     */
    public function supportsTestFilesWithAnySuffix()
    {
        $testFileSuffix = '_test_';
        $_SERVER['argv'] = $GLOBALS['argv'] = array(
            'bin/phpunitrunner',
            '-p', 'tests/prepare.php',
            '--test-file-suffix=' . $testFileSuffix
        );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $controller = $this->createTestRunnerCLIController();
        $controller->run();
        $this->assertEquals($testFileSuffix, $this->readAttribute($controller, 'config')->testFileSuffix);
    }

    /**
     * @test
     * @dataProvider notificationOptions
     * @param string $option
     * @link http://redmine.piece-framework.com/issues/311
     * @since Method available since Release 2.18.0
     */
    public function supportsNotifications($option)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', $option);
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $controller = $this->createTestRunnerCLIController();
        $controller->run();
        $this->assertTrue($this->readAttribute($controller, 'config')->usesNotification);
    }

    /**
     * @return array
     * @since Method available since Release 2.18.0
     */
    public function notificationOptions()
    {
        return array(array('-n'), array('-g'),);
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/323
     * @since Method available since Release 2.19.0
     */
    public function clearsThePrecedingOutputHandlers()
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', '-p', 'tests/prepare.php', '-R');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $this->createTestRunnerCLIController()->run();
        $this->assertEquals(-1, $this->nestingLevel);
    }

    /**
     * @return integer
     */
    public function getNestingLevel()
    {
        return $this->nestingLevel--;
    }

    /**
     * @return Stagehand_TestRunner_Util_OutputBuffering
     * @since Method available since Release 2.20.0
     */
    protected function createOutputBuffering()
    {
        $outputBuffering = $this->getMock(
            'Stagehand_TestRunner_Util_OutputBuffering',
            array('getNestingLevel', 'clearOutputHandler')
        );
        $outputBuffering->expects($this->exactly(2))
            ->method('getNestingLevel')
            ->will($this->returnCallback(array($this, 'getNestingLevel')));
        $outputBuffering->expects($this->once())
            ->method('clearOutputHandler')
            ->will($this->returnValue(null));
        return $outputBuffering;
    }

    /**
     * @return Stagehand_TestRunner_TestRunnerCLIController
     * @since Method available since Release 2.20.0
     */
    protected function createTestRunnerCLIController()
    {
        $controller = $this->getMock(
            'Stagehand_TestRunner_TestRunnerCLIController',
            array(
                'createOutputBuffering',
                'runTests',
                'validateDirectory',
                'createAutotest'
            ),
            array(Stagehand_TestRunner_Framework::PHPUNIT)
        );
        $controller->expects($this->once())
            ->method('createOutputBuffering')
            ->will($this->returnValue($this->createOutputBuffering()));
        $controller->expects($this->any())
            ->method('runTests')
            ->will($this->returnValue(null));
        $controller->expects($this->any())
            ->method('validateDirectory')
            ->will($this->returnValue(null));
        $controller->expects($this->any())
            ->method('createAutotest')
            ->will($this->returnCallback(array($this, 'createAutotest')));
        return $controller;
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
