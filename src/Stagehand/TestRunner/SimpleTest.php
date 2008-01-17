<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>,
 *               2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://simpletest.org/
 * @since      File available since Release 1.1.0
 */

require_once 'Stagehand/TestRunner/Common.php';
require_once 'simpletest/test_case.php';
require_once 'simpletest/reporter.php';
require_once 'simpletest/scorer.php';

// {{{ Stagehand_TestRunner_SimpleTest

/**
 * A test runner for SimpleTest.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://simpletest.org/
 * @since      Class available since Release 1.1.0
 */
class Stagehand_TestRunner_SimpleTest extends Stagehand_TestRunner_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $_excludePattern = '!^(UnitTestCase$|PHPUnit)!i';
    protected $_baseClass = 'UnitTestCase';

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ _doRun()

    /**
     * Runs tests based on the given test suite object.
     *
     * @param TestSuite $suite
     */
    protected function _doRun($suite)
    {
        $reporter = new TextReporter();
        ob_start();
        $suite->run($reporter);
        $output = ob_get_contents();
        ob_end_clean();

        if ($this->_color) {
            include_once 'Console/Color.php';
            print Console_Color::convert(preg_replace(array('/^(OK.+)/ms',
                                                            '/^(FAILURES!!!.+)/ms',
                                                            '/^(\d+\)\s)(.+at \[.+\]$\s+in .+)$/m',
                                                            '/^(Exception \d+!)/m',
                                                            '/^(Unexpected exception of type \[.+\] with message \[.+\] in \[.+\]$\s+in .+)$/m'),
                                                      array('%g$1%n',
                                                            '%r$1%n',
                                                            "\$1%r\$2%n",
                                                            '%p$1%n',
                                                            '%p$1%n'),
                                                      Console_Color::escape($output))
                                         );
        } else {
            print $output;
        }
    }

    // }}}
    // {{{ _createTestSuite()

    /**
     * Creates a test suite object.
     *
     * @return TestSuite
     */
    protected function _createTestSuite()
    {
        return new TestSuite();
    }

    // }}}
    // {{{ _doBuildTestSuite()

    /**
     * Aggregates a test suite object to an aggregate test suite object.
     *
     * @param TestSuite $aggregateSuite
     * @param TestSuite $suite
     */
    protected function _doBuildTestSuite($aggregateSuite, $suite)
    {
        if (!$suite->getSize()) {
            return;
        }

        $aggregateSuite->addTestCase($suite);
    }

    // }}}
    // {{{ _addTestCase()

    /**
     * Adds a test case to a test suite object.
     *
     * @param TestSuite $suite
     * @param string    $testCase
     */
    protected function _addTestCase($suite, $testCase)
    {
        $suite->addTestClass($testCase); // TODO NOT addTestCases()?
    }

    /**#@-*/

    /**#@+
     * @access private
     */

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
