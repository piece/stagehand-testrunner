<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
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

namespace Stagehand\TestRunner;

use Stagehand\TestRunner\Notification\Notification;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.18.0
 */
class TestRunnerTest extends \PHPUnit_Framework_TestCase
{
    const NOTIFICATION_CONFIG_DEFAULT = 1;
    const NOTIFICATION_CONFIG_TRUE = 2;
    const NOTIFICATION_CONFIG_FALSE = 3;
    const NOTIFICATION_USE = true;
    const NOTIFICATION_NOTUSE = false;

    /**
     * @test
     * @dataProvider decisionTable
     * @param integer $notificationConfigParameter
     * @param boolean $usesNotification
     */
    public function runsATest($notificationConfigParameter, $usesNotification)
    {
        $config = new Config();
        if ($notificationConfigParameter == self::NOTIFICATION_CONFIG_FALSE) {
            $config->usesNotification = false;
        } elseif ($notificationConfigParameter == self::NOTIFICATION_CONFIG_TRUE) {
            $config->usesNotification = true;
        }
        $testSuite = new \stdClass();
        $notification = new Notification(Notification::RESULT_PASSED, 'MESSAGE');

        $preparer = \Phake::mock('\Stagehand\TestRunner\Preparer\Preparer');
        \Phake::when($preparer)->prepare()->thenReturn(null);

        $collector = \Phake::mock('\Stagehand\TestRunner\Collector\Collector');
        \Phake::when($collector)->collect()->thenReturn($testSuite);

        $runner = \Phake::mock('\Stagehand\TestRunner\Runner');
        \Phake::when($runner)->run($this->anything())->thenReturn(null);
        \Phake::when($runner)->getNotification()->thenReturn($notification);

        $notifier = \Phake::mock('\Stagehand\TestRunner\Notification\Notifier');
        \Phake::when($notifier)->notifyResult($this->anything())->thenReturn(null);

        $testRunner = \Phake::partialMock('\Stagehand\TestRunner\TestRunner', $config);
        \Phake::when($testRunner)->createPreparer()->thenReturn($preparer);
        \Phake::when($testRunner)->createCollector()->thenReturn($collector);
        \Phake::when($testRunner)->createRunner()->thenReturn($runner);
        \Phake::when($testRunner)->createNotifier()->thenReturn($notifier);

        $testRunner->run();

        \Phake::verify($preparer)->prepare();
        \Phake::verify($collector)->collect();
        \Phake::verify($runner)->run($this->equalTo($testSuite));
        \Phake::verify($notifier, \Phake::times($usesNotification ? 1 : 0))->notifyResult($this->equalTo($notification));
        \Phake::verify($testRunner, \Phake::times($usesNotification ? 1 : 0))->createNotifier();
    }

    /**
     * @return array
     */
    public function decisionTable()
    {
        return array(
            array(self::NOTIFICATION_CONFIG_DEFAULT, self::NOTIFICATION_NOTUSE),
            array(self::NOTIFICATION_CONFIG_FALSE, self::NOTIFICATION_NOTUSE),
            array(self::NOTIFICATION_CONFIG_TRUE, self::NOTIFICATION_USE),
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
