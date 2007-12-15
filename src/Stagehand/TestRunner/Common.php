<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 4 and 5
 *
 * Copyright (c) 2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 1.1.0
 */

require_once 'PHP/Compat.php';

PHP_Compat::loadFunction('scandir');

// {{{ Stagehand_TestRunner_Common

/**
 * The base class for test runners.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.1.0
 */
class Stagehand_TestRunner_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_excludePattern;
    var $_baseClass;
    var $_color;
    var $_suffix = 'TestCase';
    var $_isFile;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * @param boolean $color
     * @param boolean $isFile
     */
    function Stagehand_TestRunner_Common($color, $isFile)
    {
        $this->_color = $color;
        $this->_isFile = $isFile;
    }

    // }}}
    // {{{ run()

    /**
     * Runs tests in the directory.
     *
     * @param string $directory
     * @return stdClass
     */
    function run($directory)
    {
        if ($this->_isFile) {
            if (!preg_match("/{$this->_suffix}\.php\$/", $directory)) {
                $directory = "$directory{$this->_suffix}.php";
            }
        }

        return $this->_doRun($this->_buildTestSuite($this->_createTestSuite(), $directory));
    }

    // }}}
    // {{{ runRecursively()

    /**
     * Runs tests under the directory recursively.
     *
     * @param string $directory
     * @return stdClass
     */
    function runRecursively($directory)
    {
        $suite = &$this->_createTestSuite();
        $directories = $this->_getDirectories($directory);
        for ($i = 0, $count = count($directories); $i < $count; ++$i) {
            $this->_buildTestSuite($suite, $directories[$i]);
        }

        return $this->_doRun($suite);
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _doRun()

    /**
     * Runs tests based on the given test suite object.
     *
     * @param mixed &$suite
     * @return stdClass
     * @abstract
     */
    function _doRun(&$suite) {}

    // }}}
    // {{{ _createTestSuiteFromTestCases()

    /**
     * Creates a test suite object that contains all of the test cases in the
     * directory.
     *
     * @param array $testCases
     * @return mixed
     */
    function &_createTestSuiteFromTestCases($testCases)
    {
        $suite = &$this->_createTestSuite();
        foreach ($testCases as $testCase) {
            $this->_addTestCase($suite, $testCase);
        }

        return $suite;
    }

    // }}}
    // {{{ _createTestSuite()

    /**
     * Creates a test suite object.
     *
     * @return mixed
     * @abstract
     */
    function &_createTestSuite() {}

    // }}}
    // {{{ _doBuildTestSuite()

    /**
     * Aggregates a test suite object to an aggregate test suite object.
     *
     * @param mixed &$aggregateSuite
     * @param mixed &$suite
     * @abstract
     */
    function _doBuildTestSuite(&$aggregateSuite, &$suite) {}

    // }}}
    // {{{ _addTestCase()

    /**
     * Adds a test case to a test suite object.
     *
     * @param mixed  &$suite
     * @param string $testCase
     * @abstract
     */
    function _addTestCase(&$suite, $testCase) {}

    // }}}
    // {{{ _buildTestSuite()

    /**
     * Builds a test suite object.
     *
     * @param mixed  &$suite
     * @param string $directory
     * @return mixed
     */
    function &_buildTestSuite(&$suite, $directory)
    {
        $this->_doBuildTestSuite($suite, $this->_createTestSuiteFromTestCases($this->_collectTestCases(realpath($directory))));
        return $suite;
    }

    // }}}
    // {{{ _exclude()

    /**
     * Returns whether the class should be exclude or not.
     *
     * @param string $class
     * @return boolean
     */
    function _exclude($class)
    {
        if (strlen($this->_excludePattern) && preg_match($this->_excludePattern, $class)) {
            return true;
        }

        if (version_compare(phpversion(), '5.0.3', '>=')) {
            return !is_subclass_of($class, $this->_baseClass);
        } else {
            $instance = &new $class();
            return !is_subclass_of($instance, $this->_baseClass);
        }
    }

    // }}}
    // {{{ _collectTestCases()

    /**
     * Collects test cases in the directory.
     *
     * @param string $directory
     * @return array
     */
    function _collectTestCases($directory)
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

            if (!preg_match("/{$this->_suffix}\.php\$/", $files[$i])) {
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
                if ($this->_exclude($newClasses[$j])) {
                    continue;
                }

                $testCases[] = $newClasses[$j];
                print "  => Added [ {$newClasses[$j]} ]\n";
            }
        }

        return $testCases;
    }

    // }}}
    // {{{ _getDirectories()

    /**
     * Returns all directories under the directory.
     *
     * @param string $directory
     * @return array
     */
    function _getDirectories($directory)
    {
        static $directories;
        if (is_null($directories)) {
            $directories = array();
        }

        $directory = realpath($directory);
        $directories[] = $directory;
        $files = scandir($directory);

        for ($i = 0, $count = count($files); $i < $count; ++$i) {
            if ($files[$i] == '.' || $files[$i] == '..') {
                continue;
            }

            $next = $directory . DIRECTORY_SEPARATOR . $files[$i];
            if (!is_dir($next)) {
                continue;
            }

            $this->_getDirectories($next);
        }

        return $directories;
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