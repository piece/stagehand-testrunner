<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.2.0
 */

require_once 'PHPUnit/TextUI/ResultPrinter.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Framework/Test.php';
require_once 'PHPUnit/Framework/AssertionFailedError.php';
require_once 'PHPUnit/Framework/TestSuite.php';

// {{{ Stagehand_TestRunner_Runner_PHPUnit_Printer_DetailedProgress

/**
 * A result printer for PHPUnit.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.2.0
 */
class Stagehand_TestRunner_Runner_PHPUnit_Printer_DetailedProgress extends PHPUnit_TextUI_ResultPrinter
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

    private $_color;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Constructor.
     *
     * @param  mixed   $out
     * @param  boolean $verbose
     * @param  boolean $color
     * @since  Method available since Release 2.4.0
     */
    public function __construct($out, $verbose, $color)
    {
        parent::__construct($out, $verbose);
        $this->_color = $color;

        if ($this->_color) {
            include_once 'Console/Color.php';
            include_once 'Stagehand/TestRunner/Coloring.php';
        }
    }

    // }}}
    // {{{ addError()

    /**
     * An error occurred.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->write($this->_color ? Stagehand_TestRunner_Coloring::magenta('raised an error')
                                   : 'raised an error'
                     );

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ addFailure()

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->write($this->_color ? Stagehand_TestRunner_Coloring::red('failed')
                                   : 'failed'
                     );

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ addIncompleteTest()

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->write($this->_color ? Stagehand_TestRunner_Coloring::yellow('was incomplete')
                                   : 'was incomplete'
                     );

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ addSkippedTest()

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->write($this->_color ? Stagehand_TestRunner_Coloring::yellow('skipped')
                                   : 'skipped'
                     );

        $this->lastTestFailed = true;
    }

    // }}}
    // {{{ startTestSuite()

    /**
     * A testsuite started.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (strlen($suite->getName())) {
            if ($this->lastEvent == PHPUnit_TextUI_ResultPrinter::EVENT_TESTSUITE_END) {
                $this->write("\n\n");
            }

            $this->write($suite->getName() . "\n");
        }

        $this->lastEvent = PHPUnit_TextUI_ResultPrinter::EVENT_TESTSUITE_START;
    }

    // }}}
    // {{{ endTestSuite()

    /**
     * A testsuite ended.
     *
     * @param  PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->lastEvent = PHPUnit_TextUI_ResultPrinter::EVENT_TESTSUITE_END;
    }

    // }}}
    // {{{ startTest()

    /**
     * A test started.
     *
     * @param  PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($this->lastEvent == PHPUnit_TextUI_ResultPrinter::EVENT_TEST_END) {
            $this->write("\n");
        }

        $this->write('  ' . $test->getName() . ' ... ');
        $this->lastEvent = PHPUnit_TextUI_ResultPrinter::EVENT_TEST_START;
    }

    // }}}
    // {{{ endTest()

    /**
     * A test ended.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        if (!$this->lastTestFailed) {
            $this->write($this->_color ? Stagehand_TestRunner_Coloring::green('passed')
                                       : 'passed'
                         );
        }

        $this->lastEvent = PHPUnit_TextUI_ResultPrinter::EVENT_TEST_END;
        $this->lastTestFailed = false;
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
