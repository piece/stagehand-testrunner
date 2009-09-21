<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.5.0
 */

// {{{ Stagehand_TestRunner

/**
 * A testrunner script to run tests automatically.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.5.0
 */
class Stagehand_TestRunner extends Stagehand_CLIController
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $exceptionClass = 'Stagehand_TestRunner_Exception';
    protected $shortOptions = 'hVRcp:aw:gm:v';
    protected $longOptions = array('growl-password=');
    protected $testRunnerName;
    protected $config;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * @param string $testRunnerName
     */
    public function __construct($testRunnerName)
    {
        $this->testRunnerName = $testRunnerName;
        $this->config = new Stagehand_TestRunner_Config();
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ configureByOption()

    /**
     * @param string $option
     * @param string $value
     * @return boolean
     */
    protected function configureByOption($option, $value)
    {
        switch ($option) {
        case 'h':
            $this->printUsage();
            return false;
        case 'V':
            $this->printVersion();
            return false;
        case 'R':
            $this->config->recursivelyScans = true;
            return true;
        case 'c':
            if (@include_once 'Console/Color.php') {
                $this->config->colors = true;
            }
            return true;
        case 'p':
            $this->config->preloadFile = $value;
            return true;
        case 'a':
            $this->config->enablesAutotest = true;
            return true;
        case 'w':
            $this->config->monitoredDirectories = explode(',', $value);
            return true;
        case 'g':
            if (@include_once 'Net/Growl.php') {
                $useGrowl = true;
            }
            return true;
        case '--growl-password':
            $this->config->growlPassword = $value;
            return true;
        case 'm':
            $this->config->testMethods = explode(',', $value);
            return true;
        case 'v':
            $this->config->printsDetailedProgressReport = true;
            return true;
        }
    }

    // }}}
    // {{{ configureByArg()

    /**
     * @param string $arg
     * @return boolean
     */
    protected function configureByArg($arg)
    {
        $this->config->targetPath = $arg;
        return true;
    }

    // }}}
    // {{{ doRun()

    /**
     */
    protected function doRun()
    {
        if (!$this->config->enablesAutotest) {
            $this->runTests();
        } else {
            $this->monitorAlteration();
        }
    }

    // }}}
    // {{{ printUsage()

    /**
     * Prints the usage.
     */
    protected function printUsage()
    {
        echo "USAGE
  {$_SERVER['SCRIPT_NAME']} [OPTIONS] DIRECTORY_OR_FILE

NOTES
  If no DIRECTORY_OR_FILE is given, run all tests in the current directory.

OPTIONS

  -h
     Print this help and exit.

  -V
     Print version information and exit.

  -R
     Run tests recursively.

  -c
     Color the result of a test runner run.

  -p <file>
     Preload <file> as a PHP script.

  -a
     Monitor for changes in one or more directories and run tests in the test
     directory recursively when changes are detected.

  -w DIRECTORY1,DIRECTORY2,...
     Specify one or more directories to be monitored for changes.

  -g
     Notify test results to Growl

  --growl-password=PASSWORD
     Specify PASSWORD for Growl

  -m METHOD1,METHOD2,... (PHPUnit only)
     Specify one or more methods which you want to test.
     This option is only available on single file mode.

  -v
     Print detailed progress report (PHPUnit only)
";
    }

    // }}}
    // {{{ printVersion()

    /**
     * Prints the version.
     */
    protected function printVersion()
    {
        echo "Stagehand_TestRunner @package_version@ ({$this->testRunnerName})

Copyright (c) 2005-2009 KUBO Atsuhiro <kubo@iteman.jp>,
              2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>,
All rights reserved.
";
    }

    // }}}
    // {{{ monitorAlteration()

    /**
     * Monitors for changes in one or more target directories and runs tests in
     * the test directory recursively when changes are detected. And also the test
     * directory is always added to the directories to be monitored.
     *
     * @throws Stagehand_TestRunner_Exception
     * @since Method available since Release 2.1.0
     */
    protected function monitorAlteration()
    {
        $monitoredDirectories = array();
        foreach (array_merge($this->config->monitoredDirectories,
                             (array)$this->config->targetPath) as $directory
                 ) {
            if (!is_dir($directory)) {
                throw new Stagehand_TestRunner_Exception(
                    'A specified path [ ' .
                    $directory .
                    ' ] is not found or not a directory'
                                                         );
            }

            $directory = realpath($directory);
            if ($directory === false) {
                throw new Stagehand_TestRunner_Exception(
                    'Cannnot get the absolute path of a specified directory [ ' .
                    $directory .
                    ' ]. Make sure all elements of the absolute path have valid permissions.'
                                                         );
            }

            if (!in_array($directory, $monitoredDirectories)) {
                $monitoredDirectories[] = $directory;
            }
        }

        if (array_key_exists('_', $_SERVER)) {
            $command = $_SERVER['_'];
        } else {
            $command = $_SERVER['argv'][0];
        }

        $options = array();
        if (preg_match('!^/cygdrive/([a-z])/(.+)!', $command, $matches)) {
            $command = $matches[1] . ':\\' . str_replace('/', '\\', $matches[2]);
        }

        if (preg_match('/\.bat$/', $command)) {
            $command = str_replace('/', '\\', $command);
        }

        if (!preg_match('/(?:testrunner(?:-st)?|specrunner)(?:\.bat)?$/', $command)) {
            $configFile = get_cfg_var('cfg_file_path');
            if ($configFile !== false) {
                $options[] = '-c';
                $options[] = dirname($configFile);
            }

            $options[] = $_SERVER['argv'][0];
        }

        $options[] = '-R';

        if (!is_null($this->config->preloadFile)) {
            $options[] = '-p ' . $this->config->preloadFile;
        }

        if ($this->config->colors) {
            $options[] = '-c';
        }

        if ($this->config->useGrowl) {
            $options[] = '-g';
        }

        if (!is_null($this->config->growlPassword)) {
            $options[] = '--growl-password=' . $this->config->growlPassword;
        }

        $options[] = $this->config->targetPath;

        $monitor = new Stagehand_AlterationMonitor($monitoredDirectories,
                                                   create_function('',
                                                      "passthru('" .
                                                       $command .
                                                       ' ' .
                                                       implode(' ', $options) .
                                                       "');")
                                                   );
        $monitor->monitor();
    }

    // }}}
    // {{{ runTests()

    /**
     * Runs tests.
     *
     * @since Method available since Release 2.1.0
     */
    protected function runTests()
    {
        $collectorClass =
            'Stagehand_TestRunner_Collector_' . $this->testRunnerName . 'Collector';
        $collector = new $collectorClass($this->config);
        $suite = $collector->collect();

        $runnerClass =
            'Stagehand_TestRunner_Runner_' . $this->testRunnerName . 'Runner';
        $runner = new $runnerClass();
        $runner->run($suite, $this->config);

        if ($this->config->useGrowl) {
            $notification = $runner->getNotification();
            $application = new Net_Growl_Application('Stagehand_TestRunner',
                                                     array('Green', 'Red'),
                                                     $this->config->growlPassword
                                                     );
            $growl = new Net_Growl($application);
            $growl->notify($notification->name,
                           'Test Results by Stagehand_TestRunner',
                           $notification->description
                           );
        }
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

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
