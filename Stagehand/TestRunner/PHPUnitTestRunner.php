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
 * @link       http://www.phpunit.de/
 * @see        PHPUnit_TestSuite, PHPUnit::run()
 * @since      File available since Release 0.1.0
 */

require_once 'Stagehand/TestRunner/Common.php';
require_once 'PHPUnit.php';
require_once 'PHPUnit/TestSuite.php';
require_once 'PHP/Compat.php';

PHP_Compat::loadFunction('scandir');

// {{{ Stagehand_TestRunner_PHPUnitTestRunner

/**
 * A test runner for PHPUnit version 1.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2005-2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @see        PHPUnit_TestSuite, PHPUnit::run()
 * @since      Class available since Release 0.1.0
 */
class Stagehand_TestRunner_PHPUnitTestRunner extends Stagehand_TestRunner_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    var $_excludePattern = '!^PHPUnit!i';
    var $_baseClass = 'PHPUnit_TestCase';

    /**#@-*/

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    // }}}
    // {{{ _doRun()

    /**
     * Runs tests based on the given test suite object.
     *
     * @param PHPUnit_TestSuite &$suite
     * @return stdClass
     */
    function _doRun(&$suite)
    {
        $result = &PHPUnit::run($suite);
        return (object)array('runCount'     => $result->runCount(),
                             'passCount'    => count($result->passedTests()),
                             'failureCount' => $result->failureCount(),
                             'errorCount'   => $result->errorCount(),
                             'text'         => $result->toString()
                             );
    }

    // }}}
    // {{{ _createTestSuite()

    /**
     * Creates a test suite object.
     *
     * @return PHPUnit_TestSuite
     */
    function &_createTestSuite()
    {
        $suite = &new PHPUnit_TestSuite();
        return $suite;
    }

    // }}}
    // {{{ _doBuildTestSuite()

    /**
     * Aggregates a test suite object to an aggregate test suite object.
     *
     * @param PHPUnit_TestSuite &$aggregateSuite
     * @param PHPUnit_TestSuite &$suite
     */
    function _doBuildTestSuite(&$aggregateSuite, &$suite)
    {
        if (!$suite->countTestCases()) {
            return;
        }

        $aggregateSuite->addTest($suite);
    }

    // }}}
    // {{{ _addTestCase()

    /**
     * Adds a test case to a test suite object.
     *
     * @param PHPUnit_TestSuite &$suite
     * @param string            $testCase
     */
    function _addTestCase(&$suite, $testCase)
    {
        $suite->addTestSuite($testCase);
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
