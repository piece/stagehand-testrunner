<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2014 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.18.0
 */

namespace Stagehand\TestRunner\Process;

use Stagehand\TestRunner\Notification\Notification;
use Stagehand\TestRunner\Test\PHPUnitComponentAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.18.0
 */
class TestRunnerTest extends PHPUnitComponentAwareTestCase
{
    const NOTIFICATION_USE = true;
    const NOTIFICATION_NOTUSE = false;

    /**
     * @test
     * @dataProvider notificationDecisions
     * @param boolean $notify
     */
    public function runsATest($notify)
    {
        $outputBuffering = \Phake::mock('Stagehand\TestRunner\Util\OutputBuffering');
        \Phake::when($outputBuffering)->clearOutputHandlers()->thenReturn(null);
        $this->setComponent('output_buffering', $outputBuffering);

        $preparer = \Phake::mock('Stagehand\TestRunner\Preparer\Preparer');
        \Phake::when($preparer)->prepare()->thenReturn(null);
        $this->setComponent('phpunit.preparer', $preparer);

        $collector = \Phake::mock('Stagehand\TestRunner\Collector\Collector');
        $testSuite = new \stdClass();
        \Phake::when($collector)->collect()->thenReturn($testSuite);
        $this->setComponent('phpunit.collector', $collector);

        $runner = \Phake::mock('Stagehand\TestRunner\Runner\Runner');
        \Phake::when($runner)->run($this->anything())->thenReturn(null);
        \Phake::when($runner)->shouldNotify()->thenReturn($notify);
        if ($notify) {
            $notification = new Notification(Notification::RESULT_PASSED, 'MESSAGE');
            \Phake::when($runner)->getNotification()->thenReturn($notification);
        }
        $this->setComponent('phpunit.runner', $runner);

        $notifier = \Phake::mock('Stagehand\TestRunner\Notification\Notifier');
        if ($notify) {
            \Phake::when($notifier)->notifyResult($this->anything())->thenReturn(null);
        }
        $this->setComponent('notifier', $notifier);

        $this->createComponent('test_runner')->run();

        \Phake::verify($preparer)->prepare();
        \Phake::verify($collector)->collect();
        \Phake::verify($runner)->run($this->identicalTo($testSuite));
        \Phake::verify($runner)->shouldNotify();
        \Phake::verify($runner, \Phake::times($notify ? 1 : 0))->getNotification();
        if ($notify) {
            \Phake::verify($notifier)->notifyResult($this->identicalTo($notification));
        } else {
            \Phake::verify($notifier, \Phake::never())->notifyResult();
        }
    }

    /**
     * @return array
     */
    public function notificationDecisions()
    {
        return array(array(self::NOTIFICATION_USE), array(self::NOTIFICATION_NOTUSE));
    }

    /**
     * @test
     * @since Method available since Release 4.0.0
     */
    public function raisesAnExceptionWhenTheNotificationObjectIsNotSet()
    {
        $this->setComponent('output_buffering', \Phake::mock('Stagehand\TestRunner\Util\OutputBuffering'));
        $this->setComponent('phpunit.preparer', \Phake::mock('Stagehand\TestRunner\Preparer\Preparer'));
        $collector = \Phake::mock('Stagehand\TestRunner\Collector\Collector');
        \Phake::when($collector)->collect()->thenReturn(new \stdClass());
        $this->setComponent('phpunit.collector', $collector);
        $runner = \Phake::mock('Stagehand\TestRunner\Runner\Runner');
        \Phake::when($runner)->shouldNotify()->thenReturn(true);
        $this->setComponent('phpunit.runner', $runner);
        $notifier = \Phake::mock('Stagehand\TestRunner\Notification\Notifier');
        $this->setComponent('notifier', $notifier);

        try {
            $this->createComponent('test_runner')->run();
            $this->fail('An expected exception has not been raised.');
        } catch (\LogicException $e) {
            \Phake::verify($runner)->getNotification();
            \Phake::verify($notifier, \Phake::times(0))->notifyResult($this->anything());
        }
    }

    /**
     * @return array
     * @since Method available since Release 4.0.0
     */
    public function emptyNotificationMessages()
    {
        return array(
            array(null, Notification::RESULT_PASSED),
            array(null, Notification::RESULT_FAILED),
            array('', Notification::RESULT_PASSED),
            array('', Notification::RESULT_FAILED),
        );
    }

    /**
     * @param string $notificationMessage
     * @param string $result
     * @since Method available since Release 4.0.0
     *
     * @test
     * @dataProvider emptyNotificationMessages
     */
    public function notifiesTheSpecialMessageIfTheNotificationMessageIsEmpty($notificationMessage, $result)
    {
        $this->setComponent('output_buffering', \Phake::mock('Stagehand\TestRunner\Util\OutputBuffering'));
        $this->setComponent('phpunit.preparer', \Phake::mock('Stagehand\TestRunner\Preparer\Preparer'));
        $collector = \Phake::mock('Stagehand\TestRunner\Collector\Collector');
        \Phake::when($collector)->collect()->thenReturn(new \stdClass());
        $this->setComponent('phpunit.collector', $collector);

        $notification = \Phake::mock('Stagehand\TestRunner\Notification\Notification');
        \Phake::when($notification)->getMessage()->thenReturn($notificationMessage);
        if ($result == Notification::RESULT_PASSED) {
            \Phake::when($notification)->isPassed()->thenReturn(true);
            \Phake::when($notification)->isFailed()->thenReturn(false);
        } elseif ($result == Notification::RESULT_FAILED) {
            \Phake::when($notification)->isPassed()->thenReturn(false);
            \Phake::when($notification)->isFailed()->thenReturn(true);
        }

        $runner = \Phake::mock('Stagehand\TestRunner\Runner\Runner');
        \Phake::when($runner)->shouldNotify()->thenReturn(true);
        \Phake::when($runner)->getNotification()->thenReturn($notification);
        $this->setComponent('phpunit.runner', $runner);
        $notifier = \Phake::mock('Stagehand\TestRunner\Notification\Notifier');
        $this->setComponent('notifier', $notifier);

        $this->createComponent('test_runner')->run();

        \Phake::verify($notifier)->notifyResult(\Phake::capture($newNotification));

        if ($result == Notification::RESULT_PASSED) {
            \Phake::when($notification)->isPassed();
            \Phake::when($notification, \Phake::times(0))->isFailed();
        } elseif ($result == Notification::RESULT_FAILED) {
            \Phake::when($notification)->isPassed();
            \Phake::when($notification)->isFailed();
        }
    }

    /**
     * @since Method available since Release 4.0.0
     *
     * @test
     * @dataProvider emptyNotificationMessages
     */
    public function raisesAnExceptionWhenAnUnexpectedNotificationResultIsSet()
    {
        $this->setComponent('output_buffering', \Phake::mock('Stagehand\TestRunner\Util\OutputBuffering'));
        $this->setComponent('phpunit.preparer', \Phake::mock('Stagehand\TestRunner\Preparer\Preparer'));
        $collector = \Phake::mock('Stagehand\TestRunner\Collector\Collector');
        \Phake::when($collector)->collect()->thenReturn(new \stdClass());
        $this->setComponent('phpunit.collector', $collector);
        $notification = \Phake::mock('Stagehand\TestRunner\Notification\Notification');
        \Phake::when($notification)->isPassed()->thenReturn(false);
        \Phake::when($notification)->isFailed()->thenReturn(false);
        $runner = \Phake::mock('Stagehand\TestRunner\Runner\Runner');
        \Phake::when($runner)->shouldNotify()->thenReturn(true);
        \Phake::when($runner)->getNotification()->thenReturn($notification);
        $this->setComponent('phpunit.runner', $runner);
        $notifier = \Phake::mock('Stagehand\TestRunner\Notification\Notifier');
        $this->setComponent('notifier', $notifier);

        try {
            $this->createComponent('test_runner')->run();
            $this->fail('An expected exception has not been raised.');
        } catch (\LogicException $e) {
            \Phake::verify($runner)->getNotification();
            \Phake::verify($notification)->isFailed();
            \Phake::verify($notifier, \Phake::times(0))->notifyResult($this->anything());
        }
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
