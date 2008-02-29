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
 * @since      File available since Release 1.1.0
 */

require_once 'Stagehand/TestRunner/Exception.php';

// {{{ Stagehand_TestRunner_Common

/**
 * The base class for test runners.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 1.1.0
 */
abstract class Stagehand_TestRunner_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $_excludePattern;
    protected $_baseClass;
    protected $_suffix = 'TestCase';
    protected $_color;
    protected $_includePattern;

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_targetPath;
    private $_isRecursive;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * @param boolean $color
     * @param string  $targetPath
     * @param boolean $isRecursive
     * @throws Stagehand_TestRunner_Exception
     */
    public function __construct($color, $targetPath, $isRecursive)
    {
        if (is_null($targetPath)) {
            $absoluteTargetPath = getcwd();
        } else {
            if (!file_exists($targetPath)) {
                if (preg_match("/{$this->_suffix}\.php\$/", $targetPath)) {
                    throw new Stagehand_TestRunner_Exception("The directory or file [ $targetPath ] is not found.");
                }

                $targetPath = "$targetPath{$this->_suffix}.php";
            }

            $absoluteTargetPath = realpath($targetPath);
            if ($absoluteTargetPath === false) {
                throw new Stagehand_TestRunner_Exception("The directory or file [ $targetPath ] is not found.");
            }
        }

        $this->_targetPath = $absoluteTargetPath;
        $this->_color = $color;
        $this->_isRecursive = is_dir($absoluteTargetPath) && $isRecursive;
    }

    // }}}
    // {{{ run()

    /**
     * Runs tests in the directory.
     *
     * @return stdClass
     */
    public function run()
    {
        if ($this->_isRecursive) {
            $suite = $this->_createTestSuite();
            $directories = $this->_getDirectories($this->_targetPath);
            for ($i = 0, $count = count($directories); $i < $count; ++$i) {
                $this->_buildTestSuite($suite, $directories[$i]);
            }
        } else {
            $suite = $this->_buildTestSuite($this->_createTestSuite(), $this->_targetPath);
        }

        return $this->_doRun($suite);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ _doRun()

    /**
     * Runs tests based on the given test suite object.
     *
     * @param mixed $suite
     * @return stdClass
     * @abstract
     */
    abstract protected function _doRun($suite);

    // }}}
    // {{{ _createTestSuite()

    /**
     * Creates a test suite object.
     *
     * @return mixed
     * @abstract
     */
    abstract protected function _createTestSuite();

    // }}}
    // {{{ _doBuildTestSuite()

    /**
     * Aggregates a test suite object to an aggregate test suite object.
     *
     * @param mixed $aggregateSuite
     * @param mixed $suite
     * @abstract
     */
    abstract protected function _doBuildTestSuite($aggregateSuite, $suite);

    // }}}
    // {{{ _addTestCase()

    /**
     * Adds a test case to a test suite object.
     *
     * @param mixed  $suite
     * @param string $testCase
     * @abstract
     */
    abstract protected function _addTestCase($suite, $testCase);

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _createTestSuiteFromTestCases()

    /**
     * Creates a test suite object that contains all of the test cases in the
     * directory.
     *
     * @param array $testCases
     * @return mixed
     */
    private function _createTestSuiteFromTestCases($testCases)
    {
        $suite = $this->_createTestSuite();
        foreach ($testCases as $testCase) {
            $this->_addTestCase($suite, $testCase);
        }

        return $suite;
    }

    // }}}
    // {{{ _buildTestSuite()

    /**
     * Builds a test suite object.
     *
     * @param mixed  $suite
     * @param string $directory
     * @return mixed
     */
    private function _buildTestSuite($suite, $directory)
    {
        $this->_doBuildTestSuite($suite, $this->_createTestSuiteFromTestCases($this->_collectTestCases(realpath($directory))));
        return $suite;
    }

    // }}}
    // {{{ _collectTestCases()

    /**
     * Collects test cases in the directory.
     *
     * @param string $directory
     * @return array
     */
    private function _collectTestCases($directory)
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
                if (!is_subclass_of($newClasses[$j], $this->_baseClass)) {
                    continue;
                }

                if (!is_null($this->_excludePattern)
                    && preg_match($this->_excludePattern, $newClasses[$j])
                    ) {
                    continue;
                }

                if (!is_null($this->_includePattern)
                    && !preg_match($this->_includePattern, $newClasses[$j])
                    ) {
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
    private function _getDirectories($directory)
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
