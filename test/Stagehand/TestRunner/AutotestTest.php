<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
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

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.20.0
 */
class Stagehand_TestRunner_AutotestTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $errorOutput;

    /**
     * @var Stagehand_TestRunner_Notification_Notification
     */
    protected $notification;

    /**
     * @var string
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
        $config = new Stagehand_TestRunner_Config();
        $config->usesNotification = true;

        $notifier = $this->getMock('Stagehand_TestRunner_Notification_Notifier', array('notifyResult'));
        $notifier->expects($this->once())
                 ->method('notifyResult')
                 ->will($this->returnCallback(array($this, 'notifyResult')));

        $autotest = $this->getMock(
            'Stagehand_TestRunner_Autotest',
            array('executeRunnerCommand', 'createNotifier'),
            array($config)
        );
        $autotest->expects($this->once())
                 ->method('executeRunnerCommand')
                 ->will($this->returnCallback(array($this, 'executeRunnerCommand')));
        $autotest->expects($this->once())
                 ->method('createNotifier')
                 ->will($this->returnValue($notifier));

        ob_start();
        $autotest->runTests();
        ob_end_clean();

        $this->assertEquals($errorMessage, $this->notification->getMessage());
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
     * @param Stagehand_TestRunner_Notification_Notification $notification
     */
    public function notifyResult(Stagehand_TestRunner_Notification_Notification $notification)
    {
        $this->notification = $notification;
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
     * @param Stagehand_TestRunner_Config $config
     * @return Stagehand_TestRunner_Autotest
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
     * @return string
     */
    public function getPHPConfigDir()
    {
        return $this->phpConfigDir;
    }

    /**
     * @test
     * @dataProvider preservedConfigurations
     * @param Stagehand_TestRunner_Config $config
     * @param array $normalizedOption
     * @param array $shouldPreserve
     * @link http://redmine.piece-framework.com/issues/314
     */
    public function preservesSomeConfigurationsForAutotest(
        Stagehand_TestRunner_Config $config,
        array $normalizedOption,
        array $shouldPreserve)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', '-a');
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $autotest = $this->createAutotest($config);
        $autotest->monitorAlteration();

        for ($i = 0; $i < count($normalizedOption); ++$i) {
            $preserved = in_array($normalizedOption[$i], $this->readAttribute($autotest, 'runnerOptions'));
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

        $config = new Stagehand_TestRunner_Config();
        $config->recursivelyScans = true;
        $data[] = array($config, array('-R'), array(true));

        $config = new Stagehand_TestRunner_Config();
        $config->setColors(true);
        $data[] = array($config, array('-R', '-c'), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->preloadFile = 'test/prepare.php';
        $data[] = array($config, array('-R', '-p ' . escapeshellarg('test/prepare.php')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->recursivelyScans = true;
        $config->enablesAutotest = true;
        $data[] = array($config, array('-R', '-a'), array(true, false));

        $config = new Stagehand_TestRunner_Config();
        $config->monitoringDirectories[] = 'src';
        $data[] = array($config, array('-R', '-w ' . escapeshellarg('src')), array(true, false));

        $config = new Stagehand_TestRunner_Config();
        $config->usesNotification = true;
        $data[] = array($config, array('-R', '-n'), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->growlPassword = 'PASSWORD';
        $data[] = array($config, array('-R', '--growl-password=' . escapeshellarg('PASSWORD')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->addTestingMethod('METHOD1');
        $data[] = array($config, array('-R', '-m ' . escapeshellarg('METHOD1')), array(true, false));

        $config = new Stagehand_TestRunner_Config();
        $config->addTestingClass('CLASS1');
        $data[] = array($config, array('-R', '--classes=' . escapeshellarg('CLASS1')), array(true, false));

        $config = new Stagehand_TestRunner_Config();
        $config->logsResultsInJUnitXML = true;
        $config->setJUnitXMLFile('FILE');
        $data[] = array($config, array('-R', '--log-junit=' . escapeshellarg('FILE')), array(true, false));

        $config = new Stagehand_TestRunner_Config();
        $config->setLogsResultsInJUnitXMLInRealtime(true);
        $data[] = array($config, array('-R', '--log-junit-realtime'), array(true, false));

        $config = new Stagehand_TestRunner_Config();
        $config->printsDetailedProgressReport = true;
        $data[] = array($config, array('-R', '-v'), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->stopsOnFailure = true;
        $data[] = array($config, array('-R', '--stop-on-failure'), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->phpunitConfigFile = 'FILE';
        $data[] = array($config, array('-R', '--phpunit-config=' . escapeshellarg('FILE')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->cakephpAppPath = 'DIRECTORY';
        $data[] = array($config, array('-R', '--cakephp-app-path=' . escapeshellarg('DIRECTORY')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->cakephpCorePath = 'DIRECTORY';
        $data[] = array($config, array('-R', '--cakephp-core-path=' . escapeshellarg('DIRECTORY')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->ciunitPath = 'DIRECTORY';
        $data[] = array($config, array('-R', '--ciunit-path=' . escapeshellarg('DIRECTORY')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->testFilePattern = 'PATTERN';
        $data[] = array($config, array('-R', '--test-file-pattern=' . escapeshellarg('PATTERN')), array(true, true));

        $config = new Stagehand_TestRunner_Config();
        $config->testFileSuffix = 'SUFFIX';
        $data[] = array($config, array('-R', '--test-file-suffix=' . escapeshellarg('SUFFIX')), array(true, true));

        return $data;
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
