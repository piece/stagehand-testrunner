<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
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
 * @copyright  2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
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
     * Runs tests automatically
     *
     * @param string $testRunnerName
     */
    function run($testRunnerName)
    {
        if (!array_key_exists('argv', $_SERVER)) {
            echo "ERROR: either use the CLI php executable, or set register_argc_argv=On in php.ini.\n";;
            return 1;
        }

        $argv = Console_Getopt::readPHPArgv();
        array_shift($argv);
        $options = Console_Getopt::getopt2($argv, "hVR");
        if (PEAR::isError($options)) {
            echo 'ERROR: ' . preg_replace('/^Console_Getopt: /', '', $options->getMessage()) . "\n";
            Stagehand_TestRunner::_displayUsage();
            return 1;
        }

        $directory = null;
        $isRecursive = false;
        foreach ($options as $option) {
            if (!count($option)) {
                continue;
            }

            if (count($option) == 1) {
                if (is_array($option[0])) {
                    switch ($option[0][0]) {
                    case 'h':
                        Stagehand_TestRunner::_displayUsage();
                        return 1;
                    case 'V':
                        Stagehand_TestRunner::_displayVersion();
                        return 1;
                    case 'R':
                        $isRecursive = true;
                        break;
                    }
                } else {
                    if (preg_match('/TestCase\.php$/', $option[0])) {
                        $directory = $option[0];
                    } else {
                        $directory = "{$option[0]}TestCase.php";
                    }
                }
            }
        }

        include_once "Stagehand/TestRunner/$testRunnerName.php";
        $className = "Stagehand_TestRunner_$testRunnerName";
        $testRunner = &new $className();

        if (!$isRecursive) {
            if (is_null($directory)) {
                $directory = getcwd();
            }

            $result = $testRunner->run($directory);
        } else {
            $directory = getcwd();
            $result = $testRunner->runRecursively($directory);
        }

        printf('### Results ###
%s
Runs     : %d
Passes   : %d (%d%%)
Failures : %d (%d%%), %d failures, %d errors
',
               $result->text,
               $result->runCount,
               $result->passCount, $result->runCount ? $result->passCount / $result->runCount * 100 : 0,
               $result->runCount - $result->passCount, $result->runCount ? ($result->runCount - $result->passCount) / $result->runCount * 100 : 0, $result->failureCount, $result->errorCount
               );

        return 0;
    }

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
    function _displayUsage()
    {
        echo "Usage: {$_SERVER['SCRIPT_NAME']} [OPTION]... [TESTCASE]

Options:
  -h
        display this help and exit
  -V
        display version information and exit
  -R
        run tests recursively

With no TESTCASE, run all tests in the current directory.
";
    }

    // }}}
    // {{{ _displayVersion()

    /**
     * Displays the version.
     */
    function _displayVersion()
    {
        echo "Stagehand_TestRunner @package_version@

Copyright (c) 2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
?>
