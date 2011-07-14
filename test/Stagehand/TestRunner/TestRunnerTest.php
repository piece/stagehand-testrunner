<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2011 KUBO Atsuhiro <kubo@iteman.jp>,
 *               2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>,
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
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.18.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.18.0
 */
class Stagehand_TestRunner_TestRunnerTest extends Stagehand_TestRunner_TestCase
{
    protected $framework = Stagehand_TestRunner_Framework::PHPUNIT;
    protected $result;

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/309
     * @dataProvider providePassAndFailure
     * @param string $testClass
     * @param boolean $result
     * @param string $phpOS
     */
    public function createNotificationWithAIcon($testClass, $result, $phpOS)
    {
        $this->config->usesGrowl = true;
        $this->collector->collectTestCase($testClass);
        $this->growlNotifier->expects($this->any())
                    ->method('executeNotifyCommand')
                    ->will($this->returnCallback(array($this, 'executeGrowlNotifyCommand')));
        $this->phpOS = $phpOS;
        $this->result = $result;
        $this->runTests();
    }

    /**
     * @return array
     */
    public function providePassAndFailure()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitPassTest', true, 'win'),
            array('Stagehand_TestRunner_PHPUnitFailureTest', false, 'win'),
            array('Stagehand_TestRunner_PHPUnitPassTest', true, 'darwin'),
            array('Stagehand_TestRunner_PHPUnitFailureTest', false, 'darwin'),
        );
    }

    /**
     * @param string $command
     */
    public function executeGrowlNotifyCommand($command)
    {
        if ($this->result) {
            $title = 'Test Passed';
            $icon = Stagehand_TestRunner_Notification_GrowlNotifier::$ICON_PASSED;
        } else {
            $title = 'Test Failed';
            $icon = Stagehand_TestRunner_Notification_GrowlNotifier::$ICON_FAILED;
        }

        if ($this->growlNotifier->isWin()) {
            $expected = '!^growlnotify /t:"' . $title .
                '" /p:-2 /i:"' . preg_quote($icon) .
                '" /a:Stagehand_TestRunner /r:"Test Passed","Test Failed" /n:"' . $title .
                '" /silent:true ".+"$!';
        } elseif ($this->growlNotifier->isDarwin()) {
            $expected = '!^growlnotify --name "' . $title .
                '" --priority -2 --image "' . preg_quote($icon) .
                '" --title "' . $title .
                '" --message ".+"$!';
        }

        $this->assertRegExp($expected, $command);
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
