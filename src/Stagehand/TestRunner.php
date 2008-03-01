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

    /**#@-*/

    /**#@+
     * @access public
     * @static
     */

    // }}}
    // {{{ run()

    /**
     * Runs tests automatically.
     *
     * @param string $testRunnerName
     */
    public static function run($testRunnerName)
    {
        if (!array_key_exists('argv', $_SERVER)) {
            echo "ERROR: either use the CLI php executable, or set register_argc_argv=On in php.ini.\n";;
            return 1;
        }

        $argv = Console_Getopt::readPHPArgv();
        array_shift($argv);
        $allOptions = Console_Getopt::getopt2($argv, 'hVRcp:a');
        if (PEAR::isError($allOptions)) {
            echo 'ERROR: ' . preg_replace('/^Console_Getopt: /', '', $allOptions->getMessage()) . "\n";
            self::_displayUsage();
            return 1;
        }

        $directory = null;
        $isRecursive = false;
        $color = false;
        $enableAutotest = false;
        $preload = false;
        $preloadFile = null;
        foreach ($allOptions as $options) {
            if (!count($options)) {
                continue;
            }

            foreach ($options as $option) {
                if (is_array($option)) {
                    switch ($option[0]) {
                    case 'h':
                        self::_displayUsage();
                        return 1;
                    case 'V':
                        self::_displayVersion();
                        return 1;
                    case 'R':
                        $isRecursive = true;
                        break;
                    case 'c':
                        if (@include_once 'Console/Color.php') {
                            $color = true;
                        }
                        break;
                    case 'p':
                        $preload = true;
                        $preloadFile = $option[1];
                        break;
                    case 'a':
                        $enableAutotest = true;
                        break;
                    }
                } else {
                    $directory = $option;
                }
            }
        }

        if (!$enableAutotest) {
            include_once "Stagehand/TestRunner/Collector/$testRunnerName.php";
            $className = "Stagehand_TestRunner_Collector_$testRunnerName";
            $collector = new $className($directory, $isRecursive);

            try {
                $suite = $collector->collect();
            } catch (Stagehand_TestRunner_Exception $e) {
                echo 'ERROR: ' . $e->getMessage() . "\n";
                self::_displayUsage();
                return 1;
            }

            include_once "Stagehand/TestRunner/Runner/$testRunnerName.php";
            $className = "Stagehand_TestRunner_Runner_$testRunnerName";
            $runner = new $className();
            $runner->run($suite, $color);
        } else {
            if (php_uname('s') !== 'Windows NT') {
                echo "ERROR: -a option is not supported for your platform.\n";
                self::_displayUsage();
                return 1;
            }

            $directory = realpath($directory);
            if ($directory === false || !is_dir($directory)) {
                echo "ERROR: The specified path [ $directory ] is not found or not a directory.\n";
                self::_displayUsage();
                return 1;
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

            if (!preg_match('/(?:test|spec)runner(?:\.bat)?$/', $command)) {
                $configFile = get_cfg_var('cfg_file_path');
                if ($configFile !== false) {
                    $options[] = '-c';
                    $options[] = dirname($configFile);
                }

                $options[] = $_SERVER['argv'][0];
            }

            $options[] = '-R';

            if ($preload) {
                $options[] = "-p $preloadFile";
            }

            if ($color) {
                $options[] = '-c';
            }

            $options[] = $directory;

            define('FILE_NOTIFY_CHANGE_FILE_NAME',    0x00000001);
            define('FILE_NOTIFY_CHANGE_DIR_NAME',     0x00000002);
            define('FILE_NOTIFY_CHANGE_ATTRIBUTES',   0x00000004);
            define('FILE_NOTIFY_CHANGE_SIZE',         0x00000008);
            define('FILE_NOTIFY_CHANGE_LAST_WRITE',   0x00000010);
            define('FILE_NOTIFY_CHANGE_LAST_ACCESS',  0x00000020);
            define('FILE_NOTIFY_CHANGE_CREATION',     0x00000040);
            define('FILE_NOTIFY_CHANGE_EA',           0x00000080);
            define('FILE_NOTIFY_CHANGE_SECURITY',     0x00000100);
            define('FILE_NOTIFY_CHANGE_STREAM_NAME',  0x00000200);
            define('FILE_NOTIFY_CHANGE_STREAM_SIZE',  0x00000400);
            define('FILE_NOTIFY_CHANGE_STREAM_WRITE', 0x00000800);
            define('INFINITE', 0xffffffff);

            $kernel32 = wb_load_library('kernel32.dll');
            while (true) {
                $h1 = wb_call_function(wb_get_function_address('FindFirstChangeNotification', $kernel32),
                                       array($directory,
                                             1,
                                             FILE_NOTIFY_CHANGE_FILE_NAME | FILE_NOTIFY_CHANGE_DIR_NAME | FILE_NOTIFY_CHANGE_ATTRIBUTES | FILE_NOTIFY_CHANGE_LAST_WRITE | FILE_NOTIFY_CHANGE_EA | FILE_NOTIFY_CHANGE_SECURITY)
                                       );

                print "Waiting for changes in the directory [ $directory ] ...\n";
                $h2 = wb_call_function(wb_get_function_address('WaitForSingleObject', $kernel32),
                                       array($h1, INFINITE)
                                       );
                passthru("$command " . implode(' ', $options), $result);

                $h3 = wb_call_function(wb_get_function_address('FindCloseChangeNotification', $kernel32),
                                       array($h1)
                                       );

                if ($result !== 0) {
                    return 1;
                }
            }
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
     * @static
     */

    // }}}
    // {{{ _displayUsage()

    /**
     * Displays the usage.
     */
    private static function _displayUsage()
    {
        echo "Usage: {$_SERVER['SCRIPT_NAME']} [options] [directory or file]

Options:
  -h        display this help and exit
  -V        display version information and exit
  -R        run tests recursively
  -c        color the result of a test runner run
  -p <file> preload <file> as a PHP script
  -a        watch for changes in a specified directory and run all tests in
            the directory recursively when changes are detected (autotest)

With no [directory or file], run all tests in the current directory.
";
    }

    // }}}
    // {{{ _displayVersion()

    /**
     * Displays the version.
     */
    private static function _displayVersion()
    {
        echo "Stagehand_TestRunner @package_version@

Copyright (c) 2005-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
              2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>,
All rights reserved.
";
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
