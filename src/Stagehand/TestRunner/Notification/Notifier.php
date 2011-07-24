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
class Stagehand_TestRunner_Notification_Notifier
{
    public static $ICON_PASSED;
    public static $ICON_FAILED;
    public static $ICON_STOPPED;

    public function __construct()
    {
        $growlIconDir = dirname(__FILE__);
        self::$ICON_PASSED = $growlIconDir . DIRECTORY_SEPARATOR . 'passed.png';
        self::$ICON_FAILED = $growlIconDir . DIRECTORY_SEPARATOR . 'failed.png';
        self::$ICON_STOPPED = $growlIconDir . DIRECTORY_SEPARATOR . 'stopped.png';
    }

    /**
     * @param Stagehand_TestRunner_Notification_Notification $result
     */
    public function notifyResult(Stagehand_TestRunner_Notification_Notification $result)
    {
        $this->executeNotifyCommand($this->buildNotifyCommand($result));
    }

    /**
     * @return boolean
     */
    public function isWin()
    {
        return strtolower(substr($this->getPHPOS(), 0, strlen('win'))) == 'win';
    }

    /**
     * @return boolean
     */
    public function isDarwin()
    {
        return strtolower(substr($this->getPHPOS(), 0, strlen('darwin'))) == 'darwin';
    }

    /**
     * @return boolean
     */
    public function isLinux()
    {
        return strtolower(substr($this->getPHPOS(), 0, strlen('linux'))) == 'linux';
    }

    /**
     * @param Stagehand_TestRunner_Notification_Notification $result
     * @return string
     */
    protected function buildNotifyCommand(Stagehand_TestRunner_Notification_Notification $result)
    {
        $title = $result->isPassed() ? 'Test Passed' : 'Test Failed';
        if ($this->isWin()) {
            return sprintf(
                'growlnotify /t:"%s" /p:-2 /i:"%s" /a:Stagehand_TestRunner /r:"Test Passed","Test Failed" /n:"%s" /silent:true "%s"',
                $title,
                $result->isPassed() ? self::$ICON_PASSED : self::$ICON_FAILED,
                $title,
                $result->getMessage()
            );
        } elseif ($this->isDarwin()) {
            return sprintf(
                'growlnotify --name "%s" --priority -2 --image "%s" --title "%s" --message "%s"',
                $title,
                $result->isPassed() ? self::$ICON_PASSED : self::$ICON_FAILED,
                $title,
                $result->getMessage()
            );
        } elseif ($this->isLinux()) {
            return sprintf(
                'notify-send --urgency=low --icon="%s" "%s" "%s"',
                $result->isPassed() ? self::$ICON_PASSED : self::$ICON_FAILED,
                $title,
                $result->getMessage()
            );
        }
    }

    /**
     * @param string $command
     */
    protected function executeNotifyCommand($command)
    {
        if (strlen($command) > 0) {
            system($command);
        }
    }

    /**
     * @return string
     */
    protected function getPHPOS()
    {
        return PHP_OS;
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
