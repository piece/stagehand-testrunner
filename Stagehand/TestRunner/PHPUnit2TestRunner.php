<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 5
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
 * @see        PHPUnit2_Framework_TestSuite, PHPUnit2_TextUI_TestRunner::run()
 * @since      File available since Release 0.1.0
 */

require_once 'PHPUnit2/TextUI/TestRunner.php';

// {{{ Stagehand_TestRunner_PHPUnit2TestRunner

/**
 * A test runner for PHPUnit version 2.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @see        PHPUnit2_Framework_TestSuite, PHPUnit2_TextUI_TestRunner::run()
 * @since      Class available since Release 0.1.0
 */
class Stagehand_TestRunner_PHPUnit2TestRunner
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    private static $_directories = array();

    /**#@-*/

    /**#@+
     * @access public
     * @static
     */

    // }}}
    // {{{ run()

    /**
     * Runs target test cases in the directory.
     *
     * @param string $directory
     * @param string $excludePattern
     * @return PHPUnit2_Framework_TestResult
     */
    public static function run($directory, $excludePattern = '!^PHPUnit!')
    {
        return PHPUnit2_TextUI_TestRunner::run(self::_getTestSuite($directory, $excludePattern));
    }

    // }}}
    // {{{ runAll()

    /**
     * Runs all test cases under the directory.
     *
     * @param string $directory
     * @param string $excludePattern
     * @return PHPUnit2_Framework_TestResult
     */
    public static function runAll($directory, $excludePattern = '!^PHPUnit!')
    {
        $suite = new PHPUnit2_Framework_TestSuite();
        $directories = self::getDirectories($directory);

        for ($i = 0, $count = count($directories); $i < $count; ++$i) {
            $test = self::_getTestSuite($directories[$i], $excludePattern);
            if (!$test->countTestCases()) {
                continue;
            }
            $suite->addTest($test);
        }

        return PHPUnit2_TextUI_TestRunner::run($suite);
    }

    // }}}
    // {{{ getDirectories()

    /**
     * Returns all directories under the directory.
     *
     * @param string $directory
     * @return array
     */
    public static function getDirectories($directory)
    {
        $directory = realpath($directory);
        array_push(self::$_directories, $directory);
        $files = scandir($directory);

        for ($i = 0, $count = count($files); $i < $count; ++$i) {
            if ($files[$i] == '.' || $files[$i] == '..') {
                continue;
            }

            $next = $directory . DIRECTORY_SEPARATOR . $files[$i];
            if (!is_dir($next)) {
                continue;
            }

            self::getDirectories($next);
        }

        return self::$_directories;
    }

    /**#@-*/

    /**#@+
     * @access private
     * @static
     */

    // }}}
    // {{{ _getTestSuite()

    /**
     * Returns the test suite that contains all of the test cases in the
     * directory.
     *
     * @param string $directory
     * @param string $excludePattern
     * @return PHPUnit2_Framework_TestSuite
     */
    private static function _getTestSuite($directory,
                                          $excludePattern = '!^PHPUnit!'
                                          )
    {
        $directory = realpath($directory);
        $suite = new PHPUnit2_Framework_TestSuite();
        $testCases = self::_getTestCases($directory, $excludePattern);

        for ($i = 0, $count = count($testCases); $i < $count; ++$i) {
            $suite->addTestSuite($testCases[$i]);
        }

        return $suite;
    }

    // }}}
    // {{{ _getTestCases()

    /**
     * Returns target test cases in the directory.
     *
     * @param string $directory
     * @param string $excludePattern
     * @return array
     */
    private static function _getTestCases($directory,
                                          $excludePattern = '!^PHPUnit!'
                                          )
    {
        $testCases = array();
        if (is_dir($directory)) {
            $files = scandir($directory);
        } else {
            $files = (array)$directory;
        }

        for ($i = 0, $iCount = count($files); $i < $iCount; ++$i) {
            if (is_dir($directory)) {
                $target = $directory . DIRECTORY_SEPARATOR . $files[$i];
            } else {
                $target = $files[$i];
            }

            if (!is_file($target)) {
                continue;
            }

            if (!preg_match('/TestCase\.php$/', $files[$i])) {
                continue;
            }

            print "Loading [ {$files[$i]} ] ... ";

            $currentClasses = get_declared_classes();

            if (!include_once($target)) {
                print "Failed!\n";
                continue;
            }

            print "Succeeded.\n";

            $newClasses = array_values(array_diff(get_declared_classes(), $currentClasses));
            for ($j = 0, $jCount = count($newClasses); $j < $jCount; ++$j) {
                if (self::_exclude($newClasses[$j], $excludePattern)) {
                    continue;
                }

                $testCases[] = $newClasses[$j];
                print "  => Added [ {$newClasses[$j]} ]\n";
            }
        }

        return $testCases;
    }

    // }}}
    // {{{ _exclude()

    /**
     * Returns whether the class should be exclude or not.
     *
     * @param string $class
     * @param string $excludePattern
     * @return boolean
     */
    private static function _exclude($class, $excludePattern = '!^PHPUnit!')
    {
        if (!preg_match('/TestCase$/', $class)) {
            return true;
        }

        if (strlen($excludePattern) && preg_match($excludePattern, $class)) {
            return true;
        }

        $test = new $class();
        return !($test instanceof PHPUnit2_Framework_TestCase);
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
