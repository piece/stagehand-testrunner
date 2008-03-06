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
 * @since      File available since Release 2.1.0
 */

require_once 'Stagehand/TestRunner/Exception.php';
require_once 'Stagehand/TestRunner/DirectoryScanner.php';

// {{{ Stagehand_TestRunner_Collector_Common

/**
 * The base class for test collectors.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 2.1.0
 */
abstract class Stagehand_TestRunner_Collector_Common
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
    protected $_includePattern;

    /**#@-*/

    /**#@+
     * @access private
     */

    private $_targetPath;
    private $_isRecursive;
    private $_testCases = array();

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Initializes some properties of an instance.
     *
     * @param string  $targetPath
     * @param boolean $isRecursive
     */
    public function __construct($targetPath, $isRecursive)
    {
        $this->_targetPath = $targetPath;
        $this->_isRecursive = $isRecursive;
    }

    // }}}
    // {{{ collect()

    /**
     * Collects tests.
     *
     * @return mixed
     * @throws Stagehand_TestRunner_Exception
     */
    public function collect()
    {
        if (!file_exists($this->_targetPath)) {
            if (preg_match("/{$this->_suffix}\.php\$/", $this->_targetPath)) {
                throw new Stagehand_TestRunner_Exception("The directory or file [ {$this->_targetPath} ] is not found.");
            }

            $this->_targetPath = "{$this->_targetPath}{$this->_suffix}.php";
        }

        $absoluteTargetPath = realpath($this->_targetPath);
        if ($absoluteTargetPath === false) {
            throw new Stagehand_TestRunner_Exception("The directory or file [ {$this->_targetPath} ] is not found.");
        }

        if (is_dir($absoluteTargetPath)) {
            $directoryScanner = new Stagehand_TestRunner_DirectoryScanner(array($this, 'collectTestCases'), $this->_isRecursive);
            $directoryScanner->scan($absoluteTargetPath);
        } else {
            $this->_collectTestCasesFromFile($absoluteTargetPath);
        }

        return $this->_buildTestSuite();
    }

    // }}}
    // {{{ collectTestCases()

    /**
     * Collects all test cases included in the specified directory.
     *
     * @param string $element
     */
    public function collectTestCases($element)
    {
        if (!is_dir($element)) {
            $this->_collectTestCasesFromFile($element);
        }
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
     * @return mixed
     */
    private function _createTestSuiteFromTestCases()
    {
        $suite = $this->_createTestSuite();
        foreach ($this->_testCases as $testCase) {
            $this->_addTestCase($suite, $testCase);
        }

        return $suite;
    }

    // }}}
    // {{{ _buildTestSuite()

    /**
     * Builds a test suite object.
     *
     * @return mixed
     */
    private function _buildTestSuite()
    {
        $suite = $this->_createTestSuite();
        $this->_doBuildTestSuite($suite, $this->_createTestSuiteFromTestCases());
        return $suite;
    }

    // }}}
    // {{{ _collectTestCasesFromFile()

    /**
     * Collects all test cases included in the given file.
     *
     * @param string $file
     */
    private function _collectTestCasesFromFile($file)
    {
        if (!preg_match("/{$this->_suffix}\.php\$/", $file)) {
            return;
        }

        print "Loading [ $file ] ... ";

        $currentClasses = get_declared_classes();

        if (!include_once($file)) {
            print "Failed!\n";
            return;
        }

        print "Succeeded.\n";

        $newClasses = array_values(array_diff(get_declared_classes(), $currentClasses));
        for ($i = 0, $count = count($newClasses); $i < $count; ++$i) {
            if (!is_subclass_of($newClasses[$i], $this->_baseClass)) {
                continue;
            }

            if (!is_null($this->_excludePattern)
                && preg_match($this->_excludePattern, $newClasses[$i])
                ) {
                continue;
            }

            if (!is_null($this->_includePattern)
                && !preg_match($this->_includePattern, $newClasses[$i])
                ) {
                continue;
            }

            $this->_testCases[] = $newClasses[$i];
            print "  => Added [ {$newClasses[$i]} ]\n";
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
