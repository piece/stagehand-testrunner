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
