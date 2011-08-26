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
     * @test
     * @link http://redmine.piece-framework.com/issues/197
     */
    public function treatsTheCurrentDirectoryAsTheTestDirectoryIfNoDirectoriesOrFilesAreSpecified()
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', '-p', 'tests/prepare.php', '-R');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $oldWorkingDirectory = getcwd();
        chdir(dirname(__FILE__));
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('runTests'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('runTests')
               ->will($this->returnValue(null));
        chdir($oldWorkingDirectory);
        $runner->run();
        $config = $this->readAttribute($runner, 'config');
        $this->assertEquals(1, count($config->testingResources));
        $this->assertEquals(dirname(__FILE__), $config->testingResources[0]);
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
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('createAutotest'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('createAutotest')
               ->will($this->returnCallback(array($this, 'createAutotest')));
        $runner->run();

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
                       ->will($this->returnValue(null));
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
        $_SERVER['argv'] = $GLOBALS['argv'] =
            array(
                'bin/phpunitrunner',
                '-p', 'tests/prepare.php',
                '--phpunit-config=' . $phpunitConfigFile
            );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $oldWorkingDirectory = getcwd();
        chdir(dirname(__FILE__));
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('runTests'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('runTests')
               ->will($this->returnValue(null));
        chdir($oldWorkingDirectory);
        $runner->run();
        $config = $this->readAttribute($runner, 'config');
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
        $_SERVER['argv'] = $GLOBALS['argv'] =
            array(
                'bin/phpunitrunner',
                '-p', 'tests/prepare.php',
                '--test-file-pattern=' . $testFilePattern
            );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('runTests'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('runTests')
               ->will($this->returnValue(null));
        $runner->run();
        $this->assertEquals($testFilePattern, $this->readAttribute($runner, 'config')->testFilePattern);
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/211
     * @since Method available since Release 2.14.0
     */
    public function supportsTestFilesWithAnySuffix()
    {
        $testFileSuffix = '_test_';
        $_SERVER['argv'] = $GLOBALS['argv'] =
            array(
                'bin/phpunitrunner',
                '-p', 'tests/prepare.php',
                '--test-file-suffix=' . $testFileSuffix
            );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('runTests'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('runTests')
               ->will($this->returnValue(null));
        $runner->run();
        $this->assertEquals($testFileSuffix, $this->readAttribute($runner, 'config')->testFileSuffix);
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
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('runTests'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('runTests')
               ->will($this->returnValue(null));
        $runner->run();
        $this->assertTrue($this->readAttribute($runner, 'config')->usesNotification);
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
     * @dataProvider preservedOptionsForAutotest
     * @param array $option
     * @param array $normalizedOption
     * @param array $shouldPreserve
     * @link http://redmine.piece-framework.com/issues/314
     * @since Method available since Release 2.18.0
     */
    public function preservesSomeOptionsForAutotest(array $option, array $normalizedOption, array $shouldPreserve)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array_merge(array('bin/phpunitrunner', '-a'), $option);
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $runner = $this->getMock(
                      'Stagehand_TestRunner_TestRunnerCLIController',
                      array('createAutotest', 'validateDirectory'),
                      array(Stagehand_TestRunner_Framework::PHPUNIT)
                  );
        $runner->expects($this->any())
               ->method('createAutotest')
               ->will($this->returnCallback(array($this, 'createAutotest')));
        $runner->expects($this->any())
               ->method('validateDirectory')
               ->will($this->returnValue(null));
        $runner->run();

        for ($i = 0; $i < count($normalizedOption); ++$i) {
            $preserved = in_array($normalizedOption[$i], $this->readAttribute($this->autotest, 'runnerOptions'));
            $this->assertEquals($shouldPreserve[$i], $preserved);
        }
    }

    /**
     * @return array
     * @link http://redmine.piece-framework.com/issues/314
     * @since Method available since Release 2.18.0
     */
    public function preservedOptionsForAutotest()
    {
        return array(
            array(array('-R'), array('-R'), array(true)),
            array(array('-c'), array('-R', '-c'), array(true, true)),
            array(array('-p', 'test/prepare.php'), array('-R', '-p ' . escapeshellarg('test/prepare.php')), array(true, true)),
            array(array('-a'), array('-R', '-a'), array(true, false)),
            array(array('-w', 'src'), array('-R', '-w ' . escapeshellarg('src')), array(true, false)),
            array(array('-n'), array('-R', '-n'), array(true, true)),
            array(array('-g'), array('-R', '-n'), array(true, true)),
            array(array('--growl-password=PASSWORD'), array('-R', '--growl-password=' . escapeshellarg('PASSWORD')), array(true, true)),
            array(array('-m', 'METHOD1'), array('-R', '-m ' . escapeshellarg('METHOD1')), array(true, false)),
            array(array('--classes=CLASS1'), array('-R', '--classes=' . escapeshellarg('CLASS1')), array(true, false)),
            array(array('--log-junit=FILE'), array('-R', '--log-junit=' . escapeshellarg('FILE')), array(true, false)),
            array(array('--log-junit-realtime'), array('-R', '--log-junit-realtime'), array(true, false)),
            array(array('-v'), array('-R', '-v'), array(true, true)),
            array(array('--stop-on-failure'), array('-R', '--stop-on-failure'), array(true, true)),
            array(array('--phpunit-config=FILE'), array('-R', '--phpunit-config=' . escapeshellarg('FILE')), array(true, true)),
            array(array('--cakephp-app-path=DIRECTORY'), array('-R', '--cakephp-app-path=' . escapeshellarg('DIRECTORY')), array(true, true)),
            array(array('--cakephp-core-path=DIRECTORY'), array('-R', '--cakephp-core-path=' . escapeshellarg('DIRECTORY')), array(true, true)),
            array(array('--ciunit-path=DIRECTORY'), array('-R', '--ciunit-path=' . escapeshellarg('DIRECTORY')), array(true, true)),
            array(array('--test-file-pattern=PATTERN'), array('-R', '--test-file-pattern=' . escapeshellarg('PATTERN')), array(true, true)),
            array(array('--test-file-suffix=SUFFIX'), array('-R', '--test-file-suffix=' . escapeshellarg('SUFFIX')), array(true, true)),
        );
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
        Stagehand_TestRunner_Util_OutputBuffering::clearOutputHandlers();
        ob_start(array($this, 'passThrough'), 0, true);
        $controller = new Stagehand_TestRunner_TestRunnerCLIController(Stagehand_TestRunner_Framework::PHPUNIT);
        $this->assertEquals(0, count(ob_get_status()));
    }

    /**
     * @link http://redmine.piece-framework.com/issues/323
     * @since Method available since Release 2.19.0
     */
    public function passThrough($buffer)
    {
        return $buffer;
    }

    /**
     * @test
     * @expectedException Stagehand_TestRunner_CannotRemoveException
     * @link http://redmine.piece-framework.com/issues/323
     * @since Method available since Release 2.19.0
     */
    public function raisesAnExceptionWhenAPrecedingOutputBufferCannotBeRemoved()
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', '-p', 'tests/prepare.php', '-R');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        Stagehand_TestRunner_Util_OutputBuffering::clearOutputHandlers();
        ob_start(array($this, 'passThrough'), 0, false);
        new Stagehand_TestRunner_TestRunnerCLIController(Stagehand_TestRunner_Framework::PHPUNIT);
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
