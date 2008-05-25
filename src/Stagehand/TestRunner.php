<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 0.5.0
 */

require_once 'Console/Getopt.php';
require_once 'Stagehand/TestRunner/AlterationMonitor.php';
require_once 'Stagehand/TestRunner/Exception.php';

// {{{ Stagehand_TestRunner

/**
 * A testrunner script to run tests automatically.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
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

    private $_testRunnerName;
    private $_config;

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
        $this->_testRunnerName = $testRunnerName;
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
            $this->_config = $this->_parseOptions();
            if (is_null($this->_config)) {
                return 1;
            }

            if (!$this->_config->enableAutotest) {
                $this->_runTests();
            } else {
                $this->_monitorAlteration();
            }
        } catch (Stagehand_TestRunner_Exception $e) {
            echo 'ERROR: ' . $e->getMessage() . "\n";
            $this->_displayUsage();
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
    // {{{ _displayUsage()

    /**
     * Displays the usage.
     */
    private function _displayUsage()
    {
        echo "Usage: {$_SERVER['SCRIPT_NAME']} [options] [directory or file]

Options:
  -h        display this help and exit
  -V        display version information and exit
  -R        run tests recursively
  -c        color the result of a test runner run
  -p <file> preload <file> as a PHP script
  -a        watch for changes in one or more directories and run tests in the test directory recursively when changes are detected (autotest)
  -w <directory1,directory2,...> specify one or more directories to be watched for changes
  -g        notify test results to Growl

With no [directory or file], run all tests in the current directory.
";
    }

    // }}}
    // {{{ _displayVersion()

    /**
     * Displays the version.
     */
    private function _displayVersion()
    {
        echo "Stagehand_TestRunner @package_version@ ({$this->_testRunnerName})

Copyright (c) 2005-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
              2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>,
All rights reserved.
";
    }

    // }}}
    // {{{ _monitorAlteration()

    /**
     * Watches for changes in one or more target directories and runs tests in
     * the test directory recursively when changes are detected. And also the test
     * directory is always added to the target directories.
     *
     * @throws Stagehand_TestRunner_Exception
     * @since Method available since Release 2.1.0
     */
    private function _monitorAlteration()
    {
        $targetDirectories = array();
        foreach (array_merge($this->_config->targetDirectories, (array)$this->_config->directory)
                 as $directory
                 ) {
            if (!is_dir($directory)) {
                throw new Stagehand_TestRunner_Exception("ERROR: A specified path [ $directory ] is not found or not a directory.");
            }

            $directory = realpath($directory);
            if ($directory === false) {
                throw new Stagehand_TestRunner_Exception("ERROR: Cannnot get the absolute path of a specified directory [ $directory ]. Make sure all elements of the absolute path have valid permissions.");
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

        if (!is_null($this->_config->preloadFile)) {
            $options[] = "-p {$this->_config->preloadFile}";
        }

        if ($this->_config->color) {
            $options[] = '-c';
        }

        if ($this->_config->useGrowl) {
            $options[] = '-g';
        }

        $options[] = $this->_config->directory;

        $monitor = new Stagehand_TestRunner_AlterationMonitor($targetDirectories,
                                                              "$command " . implode(' ', $options)
                                                              );
        $monitor->monitor();
    }

    // }}}
    // {{{ _parseOptions()

    /**
     * Parses the command line options and creates a configuration object.
     *
     * @return stdClass
     * @throws Stagehand_TestRunner_Exception
     * @since Method available since Release 2.1.0
     */
    private function _parseOptions()
    {
        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $argv = Console_Getopt::readPHPArgv();
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($argv)) {
            throw new Stagehand_TestRunner_Exception('ERROR: ' . preg_replace('/^Console_Getopt: /', '', $argv->getMessage()));
        }

        array_shift($argv);
        PEAR::staticPushErrorHandling(PEAR_ERROR_RETURN);
        $allOptions = Console_Getopt::getopt2($argv, 'hVRcp:aw:g');
        PEAR::staticPopErrorHandling();
        if (PEAR::isError($allOptions)) {
            throw new Stagehand_TestRunner_Exception('ERROR: ' . preg_replace('/^Console_Getopt: /', '', $allOptions->getMessage()));
        }

        $directory = getcwd();
        $isRecursive = false;
        $color = false;
        $enableAutotest = false;
        $preloadFile = null;
        $targetDirectories = array();
        $useGrowl = false;
        foreach ($allOptions as $options) {
            if (!count($options)) {
                continue;
            }

            foreach ($options as $option) {
                if (is_array($option)) {
                    switch ($option[0]) {
                    case 'h':
                        $this->_displayUsage();
                        return;
                    case 'V':
                        $this->_displayVersion();
                        return;
                    case 'R':
                        $isRecursive = true;
                        break;
                    case 'c':
                        if (@include_once 'Console/Color.php') {
                            $color = true;
                        }
                        break;
                    case 'p':
                        $preloadFile = $option[1];
                        break;
                    case 'a':
                        $enableAutotest = true;
                        break;
                    case 'w':
                        $targetDirectories = explode(',', $option[1]);
                        break;
                    case 'g':
                        if (@include_once 'Net/Growl.php') {
                            $useGrowl = true;
                        }
                        break;
                    }
                } else {
                    $directory = $option;
                }
            }
        }

        return (object)array('directory' => $directory,
                             'isRecursive' => $isRecursive,
                             'color' => $color,
                             'enableAutotest' => $enableAutotest,
                             'preloadFile' => $preloadFile,
                             'targetDirectories' => $targetDirectories,
                             'useGrowl' => $useGrowl
                             );
    }

    // }}}
    // {{{ _runTests()

    /**
     * Runs tests.
     *
     * @since Method available since Release 2.1.0
     */
    private function _runTests()
    {
        include_once "Stagehand/TestRunner/Collector/{$this->_testRunnerName}.php";
        $className = "Stagehand_TestRunner_Collector_{$this->_testRunnerName}";
        $collector = new $className($this->_config->directory,
                                    $this->_config->isRecursive
                                    );
        $suite = $collector->collect();

        include_once "Stagehand/TestRunner/Runner/{$this->_testRunnerName}.php";
        $className = "Stagehand_TestRunner_Runner_{$this->_testRunnerName}";
        $runner = new $className();
        $runner->run($suite, $this->_config);

        if ($this->_config->useGrowl) {
            $notification = $runner->getNotification();
            $application = new Net_Growl_Application('Stagehand_TestRunner',
                                                     array('Green', 'Red')
                                                     );
            $growl = new Net_Growl($application);
            $growl->notify($notification->name,
                           'Test Results by Stagehand_TestRunner',
                           $notification->description
                           );
        }
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
