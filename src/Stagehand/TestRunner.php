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
        $allOptions = Console_Getopt::getopt2($argv, 'hVRcp:');
        if (PEAR::isError($allOptions)) {
            echo 'ERROR: ' . preg_replace('/^Console_Getopt: /', '', $allOptions->getMessage()) . "\n";
            Stagehand_TestRunner::_displayUsage();
            return 1;
        }

        $directory = null;
        $isRecursive = false;
        $color = false;
        $isFile = false;
        foreach ($allOptions as $options) {
            if (!count($options)) {
                continue;
            }

            foreach ($options as $option) {
                if (is_array($option)) {
                    switch ($option[0]) {
                    case 'h':
                        Stagehand_TestRunner::_displayUsage();
                        return 1;
                    case 'V':
                        Stagehand_TestRunner::_displayVersion();
                        return 1;
                    case 'R':
                        $isRecursive = true;
                        break;
                    case 'c':
                        if (@include_once 'Console/Color.php') {
                            $color = true;
                        }
                        break;
                    }
                } else {
                    $directory = $option;
                    $isFile = true;
                }
            }
        }

        include_once "Stagehand/TestRunner/$testRunnerName.php";
        $className = "Stagehand_TestRunner_$testRunnerName";
        $testRunner = new $className($color, $isFile);

        if (!$isRecursive) {
            if (is_null($directory)) {
                $directory = getcwd();
            }

            $testRunner->run($directory);
        } else {
            $directory = getcwd();
            $testRunner->runRecursively($directory);
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
        echo "Usage: {$_SERVER['SCRIPT_NAME']} [options] [testcase]

Options:
  -h        display this help and exit
  -V        display version information and exit
  -R        run tests recursively
  -c        color the result of a test runner run
  -p <file> preload <file> as a PHP script

With no [testcase], run all tests in the current directory.
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
