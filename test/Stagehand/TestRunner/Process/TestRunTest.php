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

namespace Stagehand\TestRunner\Process;

use Stagehand\TestRunner\Notification\Notification;
use Stagehand\TestRunner\Test\PHPUnitFactoryAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.18.0
 */
class TestRunTest extends PHPUnitFactoryAwareTestCase
{
    const NOTIFICATION_USE = true;
    const NOTIFICATION_NOTUSE = false;

    /**
     * @test
     * @dataProvider notificationDecisions
     * @param boolean $usesNotification
     */
    public function runsATest($usesNotification)
    {
        $outputBuffering = \Phake::mock('\Stagehand\TestRunner\Util\OutputBuffering');
        \Phake::when($outputBuffering)->clearOutputHandlers()->thenReturn(null);
        $this->applicationContext->setComponent('output_buffering', $outputBuffering);

        $preparer = \Phake::mock('\Stagehand\TestRunner\Preparer\Preparer');
        \Phake::when($preparer)->prepare()->thenReturn(null);
        $this->applicationContext->setComponent('phpunit.preparer', $preparer);

        $collector = \Phake::mock('\Stagehand\TestRunner\Collector\Collector');
        $testSuite = new \stdClass();
        \Phake::when($collector)->collect()->thenReturn($testSuite);
        $this->applicationContext->setComponent('phpunit.collector', $collector);

        $runner = \Phake::mock('\Stagehand\TestRunner\Runner\Runner');
        \Phake::when($runner)->run($this->anything())->thenReturn(null);
        \Phake::when($runner)->usesNotification()->thenReturn($usesNotification);
        if ($usesNotification) {
            $notification = new Notification(Notification::RESULT_PASSED, 'MESSAGE');
            \Phake::when($runner)->getNotification()->thenReturn($notification);
        }
        $this->applicationContext->setComponent('phpunit.runner', $runner);

        $notifier = \Phake::mock('\Stagehand\TestRunner\Notification\Notifier');
        if ($usesNotification) {
            \Phake::when($notifier)->notifyResult($this->anything())->thenReturn(null);
        }
        $this->applicationContext->setComponent('notifier', $notifier);

        $this->applicationContext->createComponent('test_run')->run();

        \Phake::verify($preparer)->prepare();
        \Phake::verify($collector)->collect();
        \Phake::verify($runner)->run($this->identicalTo($testSuite));
        \Phake::verify($runner)->usesNotification();
        \Phake::verify($runner, \Phake::times($usesNotification ? 1 : 0))->getNotification();
        if ($usesNotification) {
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
