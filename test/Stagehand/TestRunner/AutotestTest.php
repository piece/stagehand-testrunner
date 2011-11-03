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

namespace Stagehand\TestRunner;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.20.0
 */
class AutotestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $errorOutput;

    /**
     * @var string
     * @since Property available since Release 2.21.0
     */
    protected $phpConfigDir = false;

    /**
     * @test
     * @dataProvider messagesOfAFatalOrParseError
     * @param string $errorOutput
     * @param string $errorMessage
     * @link http://redmine.piece-framework.com/issues/333
     */
    public function findsTheMessageOfAFatalOrParseError($errorOutput, $errorMessage)
    {
        $this->errorOutput = $errorOutput;
        $config = new Config();
        $config->usesNotification = true;

        $notifier = \Phake::mock('\Stagehand\TestRunner\Notification\Notifier');
        \Phake::when($notifier)->notifyResult($this->anything())->thenReturn(null);

        $autotest = \Phake::partialMock('\Stagehand\TestRunner\Autotest', $config);
        \Phake::when($autotest)->executeRunnerCommand($this->anything())
            ->thenGetReturnByLambda(array($this, 'executeRunnerCommand'));
        \Phake::when($autotest)->createNotifier()->thenReturn($notifier);

        ob_start();
        $autotest->runTests();
        ob_end_clean();

        \Phake::verify($notifier)->notifyResult(\Phake::capture($notification));
        $this->assertEquals($errorMessage, $notification->getMessage());
    }

    /**
     * @return array
     */
    public function messagesOfAFatalOrParseError()
    {
        $fatalErrorMessage = "Fatal error: Class 'Stagehand\\FSM\\Events' not found in /home/iteman/GITREPOS/stagehand-fsm/test/Stagehand/FSM/EventTest.php on line 52";
        $fatalErrorOutput =
'PHPUnit 3.5.14 by Sebastian Bergmann.' . PHP_EOL .
PHP_EOL .
PHP_EOL .
$fatalErrorMessage . PHP_EOL;
        $parseErrorMessage = "Parse error: syntax error, unexpected T_CONST, expecting '{' in /home/iteman/GITREPOS/stagehand-fsm/src/Stagehand/FSM/Event.php on line 53";
        $parseErrorOutput =
'PHPUnit 3.5.14 by Sebastian Bergmann.' . PHP_EOL .
PHP_EOL .
PHP_EOL .
$parseErrorMessage . PHP_EOL;
        $unknownFatalErrorOutput =
'PHPUnit 3.5.14 by Sebastian Bergmann.' . PHP_EOL .
PHP_EOL .
'..';
        $unknownFatalErrorMessage = str_replace(PHP_EOL, ' ', $unknownFatalErrorOutput);
        return array(
            array($fatalErrorOutput, $fatalErrorMessage),
            array($parseErrorOutput, $parseErrorMessage),
            array($unknownFatalErrorOutput, $unknownFatalErrorMessage),
        );
    }

    /**
     * @param string $runnerCommand
     * @return integer
     */
    public function executeRunnerCommand($runnerCommand)
    {
        echo $this->errorOutput;
        return 1;
    }

    /**
     * @param \Stagehand\TestRunner\Config $config
     * @return \Stagehand\TestRunner\Autotest
     */
    public function createAutotest(Config $config)
    {
        $monitor = \Phake::mock('\Stagehand_AlterationMonitor', null, null);
        \Phake::when($monitor)->monitor()->thenReturn(null);

        $autotest = \Phake::partialMock('\Stagehand\TestRunner\Autotest', $config);
        \Phake::when($autotest)->createAlterationMonitor()->thenReturn($monitor);
        \Phake::when($autotest)->getMonitoringDirectories()->thenReturn(array());
        \Phake::when($autotest)->executeRunnerCommand($this->anything())->thenReturn(0);
        \Phake::when($autotest)->getPHPConfigDir()->thenGetReturnByLambda(array($this, 'getPHPConfigDir'));

        return $autotest;
    }

    /**
     * @test
     * @dataProvider preservedConfigurations
     * @param \Stagehand\TestRunner\Config $config
     * @param array $normalizedOption
     * @param array $shouldPreserve
     * @link http://redmine.piece-framework.com/issues/314
     */
    public function preservesSomeConfigurationsForAutotest(
        Config $config,
        array $normalizedOption,
        array $shouldPreserve)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', '-a');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $autotest = $this->createAutotest($config);
        \Phake::when($autotest)->buildRunnerOptions()->captureReturnTo($runnerOptions);
        $autotest->monitorAlteration();

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
        $data = array();

        $config = new Config();
        $config->recursivelyScans = true;
        $data[] = array($config, array('-R'), array(true));

        $config = new Config();
        $config->setColors(true);
        $data[] = array($config, array('-R', '-c'), array(true, true));

        $config = new Config();
        $config->preloadFile = 'test/prepare.php';
        $data[] = array($config, array('-R', '-p ' . escapeshellarg('test/prepare.php')), array(true, true));

        $config = new Config();
        $config->recursivelyScans = true;
        $config->enablesAutotest = true;
        $data[] = array($config, array('-R', '-a'), array(true, false));

        $config = new Config();
        $config->monitoringDirectories[] = 'src';
        $data[] = array($config, array('-R', '-w ' . escapeshellarg('src')), array(true, false));

        $config = new Config();
        $config->usesNotification = true;
        $data[] = array($config, array('-R', '-n'), array(true, true));

        $config = new Config();
        $config->growlPassword = 'PASSWORD';
        $data[] = array($config, array('-R', '--growl-password=' . escapeshellarg('PASSWORD')), array(true, true));

        $config = new Config();
        $config->addTestingMethod('METHOD1');
        $data[] = array($config, array('-R', '-m ' . escapeshellarg('METHOD1')), array(true, false));

        $config = new Config();
        $config->addTestingClass('CLASS1');
        $data[] = array($config, array('-R', '--classes=' . escapeshellarg('CLASS1')), array(true, false));

        $config = new Config();
        $config->setJUnitXMLFile('FILE');
        $data[] = array($config, array('-R', '--log-junit=' . escapeshellarg('FILE')), array(true, false));

        $config = new Config();
        $config->setLogsResultsInJUnitXMLInRealtime(true);
        $data[] = array($config, array('-R', '--log-junit-realtime'), array(true, false));

        $config = new Config();
        $config->printsDetailedProgressReport = true;
        $data[] = array($config, array('-R', '-v'), array(true, true));

        $config = new Config();
        $config->stopsOnFailure = true;
        $data[] = array($config, array('-R', '--stop-on-failure'), array(true, true));

        $config = new Config();
        $config->phpunitConfigFile = 'FILE';
        $data[] = array($config, array('-R', '--phpunit-config=' . escapeshellarg('FILE')), array(true, true));

        $config = new Config();
        $config->cakephpAppPath = 'DIRECTORY';
        $data[] = array($config, array('-R', '--cakephp-app-path=' . escapeshellarg('DIRECTORY')), array(true, true));

        $config = new Config();
        $config->cakephpCorePath = 'DIRECTORY';
        $data[] = array($config, array('-R', '--cakephp-core-path=' . escapeshellarg('DIRECTORY')), array(true, true));

        $config = new Config();
        $config->ciunitPath = 'DIRECTORY';
        $data[] = array($config, array('-R', '--ciunit-path=' . escapeshellarg('DIRECTORY')), array(true, true));

        $config = new Config();
        $config->testFilePattern = 'PATTERN';
        $data[] = array($config, array('-R', '--test-file-pattern=' . escapeshellarg('PATTERN')), array(true, true));

        $config = new Config();
        $config->testFileSuffix = 'SUFFIX';
        $data[] = array($config, array('-R', '--test-file-suffix=' . escapeshellarg('SUFFIX')), array(true, true));

        return $data;
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
        $this->phpConfigDir = $phpConfigDir;
        if (!is_null($command)) {
            $_SERVER['_'] = $command;
        } else {
            unset($_SERVER['_']);
        }
        $_SERVER['argv'] = $GLOBALS['argv'] = $options;
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $config = new Config();
        $config->addTestingResource($options[ count($options) - 1 ]);
        
        $autotest = $this->createAutotest($config);
        \Phake::when($autotest)->buildRunnerCommand()->captureReturnTo($runnerCommand);
        \Phake::when($autotest)->buildRunnerOptions()->captureReturnTo($runnerOptions);

        $autotest->monitorAlteration();

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
     * @since Method available since Release 2.21.0
     */
    public function getPHPConfigDir()
    {
        return $this->phpConfigDir;
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
