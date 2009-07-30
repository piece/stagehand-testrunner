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

require_once 'Console/Getopt.php';
require_once 'Stagehand/AlterationMonitor.php';
require_once 'Stagehand/TestRunner/Exception.php';
require_once 'PEAR.php';
require_once 'Stagehand/TestRunner/Config.php';

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
class Stagehand_TestRunner
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private $testRunnerName;
    private $config;

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
    }

    // }}}
    // {{{ run()

    /**
     * Runs tests automatically.
     *
     * @return integer
     */
    public function run()
    {
        if (!array_key_exists('argv', $_SERVER)) {
            echo "ERROR: either use the CLI php executable, or set register_argc_argv=On in php.ini.\n";;
            return 1;
        }

        try {
            $this->config = $this->loadConfig();
            if (is_null($this->config)) {
                return 1;
            }

            if (!$this->config->enablesAutotest) {
                $this->runTests();
            } else {
                $this->monitorAlteration();
            }
        } catch (Stagehand_TestRunner_Exception $e) {
            echo 'ERROR: ' . $e->getMessage() . "\n";
            $this->displayUsage();
            return 1;
        }

        return 0;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ displayUsage()

    /**
     * Displays the usage.
     */
    private function displayUsage()
    {
        echo "USAGE
  {$_SERVER['SCRIPT_NAME']} [OPTIONS] [DIRECTORY OR FILE]

NOTES
  With no [directory or file], run all tests in the current directory.

OPTIONS:

  -h
     Display this help and exit.

  -V
     Display version information and exit.

  -R
     Run tests recursively.

  -c
     Color the result of a test runner run.

  -p <file>
     Preload <file> as a PHP script.

  -a
     Watch for changes in one or more directories and run tests in the test
     directory recursively when changes are detected.

  -w DIRECTORY1,DIRECTORY2,...
     Specify one or more directories to be watched for changes.

  -g
     Notify test results to Growl

  --growl-password=PASSWORD
     Specify PASSWORD for Growl

  -m METHOD1,METHOD2,... (PHPUnit only)
     Specify one or more methods which you want to test.
     This option is only available on single file mode.

  -v
     Display detailed progress report (PHPUnit only)
";
    }

    // }}}
    // {{{ displayVersion()

    /**
     * Displays the version.
     */
    private function displayVersion()
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
     * Watches for changes in one or more target directories and runs tests in
     * the test directory recursively when changes are detected. And also the test
     * directory is always added to the target directories.
     *
     * @throws Stagehand_TestRunner_Exception
     * @since Method available since Release 2.1.0
     */
    private function monitorAlteration()
    {
        $targetDirectories = array();
        foreach (array_merge($this->config->targetDirectories,
                             (array)$this->config->directory) as $directory
                 ) {
            if (!is_dir($directory)) {
                throw new Stagehand_TestRunner_Exception("A specified path [ $directory ] is not found or not a directory.");
            }

            $directory = realpath($directory);
            if ($directory === false) {
                throw new Stagehand_TestRunner_Exception("Cannnot get the absolute path of a specified directory [ $directory ]. Make sure all elements of the absolute path have valid permissions.");
            }

            if (!in_array($directory, $targetDirectories)) {
                $targetDirectories[] = $directory;
            }
        }

        if (array_key_exists('_', $_SERVER)) {
            $command = $_SERVER['_'];
        } else {
            $command = $_SERVER['argv'][0];
        }

        $options = array();
        if (preg_match('!^/cygdrive/([a-z])/(.+)!', $command, $matches)) {
            $command = "{$matches[1]}:\\" . str_replace('/', '\\', $matches[2]);
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
            $options[] = "-p {$this->config->preloadFile}";
        }

        if ($this->config->color) {
            $options[] = '-c';
        }

        if ($this->config->useGrowl) {
            $options[] = '-g';
        }

        if (!is_null($this->config->growlPassword)) {
            $options[] = "--growl-password={$this->config->growlPassword}";
        }

        $options[] = $this->config->directory;

        $monitor = new Stagehand_AlterationMonitor($targetDirectories,
                                                   create_function('',
                                                       "passthru('" .
                                                       "$command " .
                                                       implode(' ', $options) .
                                                       "');")
                                                   );
        $monitor->monitor();
    }

    // }}}
    // {{{ loadConfig()

    /**
     * Loads the configuration by the default values and command line options.
     *
     * @return stdClass
     * @since Method available since Release 2.1.0
     */
    private function loadConfig()
    {
        $config = new Stagehand_TestRunner_Config();

        foreach ($this->parseOptions() as $options) {
            if (!count($options)) {
                continue;
            }

            foreach ($options as $option) {
                if (is_array($option)) {
                    switch ($option[0]) {
                    case 'h':
                        $this->displayUsage();
                        return;
                    case 'V':
                        $this->displayVersion();
                        return;
                    case 'R':
                        $config->recursivelyScans = true;
                        break;
                    case 'c':
                        if (@include_once 'Console/Color.php') {
                            $config->color = true;
                        }
                        break;
                    case 'p':
                        $config->preloadFile = $option[1];
                        break;
                    case 'a':
                        $config->enablesAutotest = true;
                        break;
                    case 'w':
                        $config->targetDirectories = explode(',', $option[1]);
                        break;
                    case 'g':
                        if (@include_once 'Net/Growl.php') {
                            $useGrowl = true;
                        }
                        break;
                    case '--growl-password':
                        $config->growlPassword = $option[1];
                        break;
                    case 'm':
                        $config->testMethods = explode(',', $option[1]);
                        break;
                    case 'v':
                        $config->isVerbose = true;
                        break;
                    }
                } else {
                    $config->directory = $option;
                }
            }
        }

        return $config;
    }

    // }}}
    // {{{ runTests()

    /**
     * Runs tests.
     *
     * @since Method available since Release 2.1.0
     */
    private function runTests()
    {
        include_once "Stagehand/TestRunner/Collector/{$this->testRunnerName}Collector.php";
        $className = "Stagehand_TestRunner_Collector_{$this->testRunnerName}Collector";
        $collector = new $className($this->config->directory,
                                    $this->config->recursivelyScans,
                                    $this->config->testMethods
                                    );
        $suite = $collector->collect();

        include_once "Stagehand/TestRunner/Runner/{$this->testRunnerName}Runner.php";
        $className = "Stagehand_TestRunner_Runner_{$this->testRunnerName}Runner";
        $runner = new $className();
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

    // }}}
    // {{{ parseOptions()

    /**
     * Parses the command line options.
     *
     * @return array
     * @throws Stagehand_TestRunner_Exception
     * @since Method available since Release 2.6.1
     */
    private function parseOptions()
    {
        $oldErrorReportingLevel = error_reporting(error_reporting() & ~E_STRICT);

        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $argv = Console_Getopt::readPHPArgv();
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($argv)) {
            error_reporting($oldErrorReportingLevel);
            throw new Stagehand_TestRunner_Exception(preg_replace('/^Console_Getopt: /', '', $argv->getMessage()));
        }

        array_shift($argv);
        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $allOptions = Console_Getopt::getopt2($argv,
                                              'hVRcp:aw:gm:v',
                                              array('growl-password=')
                                              );
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($allOptions)) {
            error_reporting($oldErrorReportingLevel);
            throw new Stagehand_TestRunner_Exception(preg_replace('/^Console_Getopt: /', '', $allOptions->getMessage()));
        }

        error_reporting($oldErrorReportingLevel);

        return $allOptions;
    }

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
