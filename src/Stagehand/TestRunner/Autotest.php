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
 * @since      File available since Release 2.18.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.18.0
 */
class Stagehand_TestRunner_Autotest
{
    /**
     * @var Stagehand_TestRunner_Config $config
     */
    protected $config;

    /**
     * @var array
     */
    protected $monitoringDirectories;

    /**
     * @var string
     */
    protected $runnerCommand;

    /**
     * @var array
     */
    protected $runnerOptions;

    /**
     * @var string
     */
    protected $output;

    /**
     * @param Stagehand_TestRunner_Config $config
     */
    public function __construct(Stagehand_TestRunner_Config $config)
    {
        $this->config = $config;
        $this->monitoringDirectories = $this->getMonitoringDirectories();
        $this->runnerCommand = $this->buildRunnerCommand();
        $this->runnerOptions = $this->buildRunnerOptions();
    }

    /**
     * Monitors for changes in one or more target directories and runs tests in
     * the test directory recursively when changes are detected. And also the test
     * directory is always added to the directories to be monitored.
     */
    public function monitorAlteration()
    {
        $this->createAlterationMonitor()->monitor();
    }

    /**
     * @since Method available since Release 2.18.0
     */
    public function executeRunnerCommand()
    {
        $this->output = '';
        ob_start(array($this, 'filterOutput'), 2);
        passthru($this->runnerCommand . ' ' . implode(' ', $this->runnerOptions), $exitStatus);
        ob_end_flush();
        if ($exitStatus != 0 && $this->config->usesNotification) {
            $message = ltrim(Stagehand_TestRunner_Util_String::normalizeNewline($this->output));
            $firstNewlinePosition = strpos($message, PHP_EOL);
            if ($firstNewlinePosition !== false) {
                $message = substr($message, 0, $firstNewlinePosition);
            }
            $notifier = new Stagehand_TestRunner_Notification_Notifier();
            $notifier->notifyResult(
                new Stagehand_TestRunner_Notification_Notification(
                    Stagehand_TestRunner_Notification_Notification::RESULT_STOPPED,
                    $message
            ));
        }
    }

    /**
     * @return string
     */
    public function filterOutput($buffer)
    {
        $this->output .= $buffer;
        return $buffer;
    }

    /**
     * @return array
     * @throws Stagehand_TestRunner_Exception
     */
    protected function getMonitoringDirectories()
    {
        $monitoringDirectories = array();
        foreach (
            array_merge(
                $this->config->monitoringDirectories,
                $this->config->testingResources
            ) as $directory) {
            if (!is_dir($directory)) {
                throw new Stagehand_TestRunner_Exception('A specified path [ ' . $directory . ' ] is not found or not a directory.');
            }

            $directory = realpath($directory);
            if ($directory === false) {
                throw new Stagehand_TestRunner_Exception('Cannnot get the absolute path of a specified directory [ ' . $directory . ' ]. Make sure all elements of the absolute path have valid permissions.');
            }

            if (!in_array($directory, $monitoringDirectories)) {
                $monitoringDirectories[] = $directory;
            }
        }

        return $monitoringDirectories;
    }

    /**
     * @return array
     * @throws Stagehand_TestRunner_Exception
     */
    protected function buildRunnerCommand()
    {
        if (array_key_exists('_', $_SERVER)) {
            $command = $_SERVER['_'];
        } elseif (array_key_exists('PHP_COMMAND', $_SERVER)) {
            $command = $_SERVER['PHP_COMMAND'];
        } else {
            $command = $_SERVER['argv'][0];
        }

        if (preg_match('!^/cygdrive/([a-z])/(.+)!', $command, $matches)) {
            $command = $matches[1] . ':\\' . str_replace('/', '\\', $matches[2]);
        }

        return escapeshellarg($command);
    }

    /**
     * @return array
     */
    protected function buildRunnerOptions()
    {
        $options = array();

        if (!preg_match('/(?:phpspec|phpt|phpunit|simpletest)runner$/', $this->runnerCommand)) {
            $configFile = get_cfg_var('cfg_file_path');
            if ($configFile !== false) {
                $options[] = '-c';
                $options[] = escapeshellarg(dirname($configFile));
            }

            $options[] = escapeshellarg($_SERVER['argv'][0]);
        }

        $options[] = '-R';

        if (!is_null($this->config->preloadFile)) {
            $options[] = '-p ' . escapeshellarg($this->config->preloadFile);
        }

        if ($this->config->colors) {
            $options[] = '-c';
        }

        if ($this->config->usesNotification) {
            $options[] = '-n';
        }

        if (!is_null($this->config->growlPassword)) {
            $options[] = '--growl-password=' . escapeshellarg($this->config->growlPassword);
        }

        foreach ($this->config->testingResources as $testingResource) {
            $options[] = escapeshellarg($testingResource);
        }

        return $options;
    }

    /**
     * @return Stagehand_AlterationMonitor
     */
    protected function createAlterationMonitor()
    {
        return new Stagehand_AlterationMonitor($this->monitoringDirectories, array($this, 'executeRunnerCommand'));
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
