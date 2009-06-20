<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2008-2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2008-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.2.0
 */

require_once 'PHPUnit/TextUI/ResultPrinter.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Framework/Test.php';
require_once 'PHPUnit/Framework/AssertionFailedError.php';
require_once 'PHPUnit/Framework/TestResult.php';
require_once 'PHPUnit/Framework/TestFailure.php';

// {{{ Stagehand_TestRunner_Runner_PHPUnit_Printer_Result

/**
 * A result printer for PHPUnit.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2008-2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.2.0
 */
class Stagehand_TestRunner_Runner_PHPUnit_Printer_Result extends PHPUnit_TextUI_ResultPrinter
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
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time) {}

    // }}}
    // {{{ addFailure()

    /**
     * A failure occurred.
     *
     * @param  PHPUnit_Framework_Test                 $test
     * @param  PHPUnit_Framework_AssertionFailedError $e
     * @param  float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time) {}

    // }}}
    // {{{ addIncompleteTest()

    /**
     * Incomplete test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}

    // }}}
    // {{{ addSkippedTest()

    /**
     * Skipped test.
     *
     * @param  PHPUnit_Framework_Test $test
     * @param  Exception              $e
     * @param  float                  $time
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time) {}

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
        if ($test instanceof PHPUnit_Framework_TestCase) {
            $this->numAssertions += $test->getNumAssertions();
        }
    }

    // }}}
    // {{{ printResult()

    /**
     * @param PHPUnit_Framework_TestResult $result
     */
    public function printResult(PHPUnit_Framework_TestResult $result)
    {
        $this->printHeader($result->time());

        print Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox;

        if ($result->errorCount() > 0) {
            $this->printErrors($result);
        }

        if ($result->failureCount() > 0) {
            if ($result->errorCount() > 0) {
                print "\n--\n\n";
            }

            $this->printFailures($result);
        }

        if ($this->verbose) {
            if ($result->notImplementedCount() > 0) {
                if ($result->failureCount() > 0) {
                    print "\n--\n\n";
                }

                $this->printIncompletes($result);
            }

            if ($result->skippedCount() > 0) {
                if ($result->notImplementedCount() > 0) {
                    print "\n--\n\n";
                }

                $this->printSkipped($result);
            }
        }

        $this->printFooter($result);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ printDefects()

    /**
     * @param  array   $defects
     * @param  integer $count
     * @param  string  $type
     */
    protected function printDefects(array $defects, $count, $type)
    {
        if (!$this->_color) {
            parent::printDefects($defects, $count, $type);
            return;
        }

        if ($count == 0) {
            return;
        }

        if ($type == 'error') {
            $colorLabel = 'magenta';
        } else {
            $colorLabel = 'red';
        }

        $this->write(
          sprintf(
            Stagehand_TestRunner_Coloring::$colorLabel("There %%s %%d %%s%%s:\n"),

            ($count == 1) ? 'was' : 'were',
            $count,
            $type,
            ($count == 1) ? '' : 's'
          )
        );

        $i = 1;

        foreach ($defects as $defect) {
            $this->printDefect($defect, $i++);
        }
    }

    // }}}
    // {{{ printDefectHeader()

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @param  integer                       $count
     */
    protected function printDefectHeader(PHPUnit_Framework_TestFailure $defect, $count)
    {
        if (!$this->_color) {
            parent::printDefectHeader($defect, $count);
            return;
        }

        $failedTest = $defect->failedTest();

        if ($failedTest instanceof PHPUnit_Framework_SelfDescribing) {
            $testName = $failedTest->toString();
        } else {
            $testName = get_class($failedTest);
        }

        if ($defect->isFailure()) {
            $colorLabel = 'red';
        } else {
            $colorLabel = 'magenta';
        }

        $this->write(
          sprintf(
            Stagehand_TestRunner_Coloring::$colorLabel("\n%%d) %%s\n%n"),
            $count,
            $testName
          )
        );
    }

    // }}}
    // {{{ printDefectTrace()

    /**
     * @param  PHPUnit_Framework_TestFailure $defect
     * @access protected
     */
    protected function printDefectTrace(PHPUnit_Framework_TestFailure $defect)
    {
        if (!$this->_color) {
            parent::printDefectTrace($defect);
            return;
        }

        if ($defect->isFailure()) {
            $colorLabel = 'red';
        } else {
            $colorLabel = 'magenta';
        }

        $oldErrorReportingLevel = error_reporting(error_reporting() & ~E_STRICT);
        $this->write(
          Stagehand_TestRunner_Coloring::$colorLabel(Console_Color::escape($defect->toStringVerbose($this->verbose))) .
          PHPUnit_Util_Filter::getFilteredStacktrace(
            $defect->thrownException(),
            FALSE
          )
        );
        error_reporting($oldErrorReportingLevel);
    }

    // }}}
    // {{{ printFooter()

    /**
     * @param  PHPUnit_Framework_TestResult  $result
     */
    protected function printFooter(PHPUnit_Framework_TestResult $result)
    {
        if (!$this->_color) {
            parent::printFooter($result);
            return;
        }

        if ($result->wasSuccessful() &&
            $result->allCompletlyImplemented() &&
            $result->noneSkipped()) {
            $this->write(
              sprintf(
                Stagehand_TestRunner_Coloring::green("OK (%%d test%s, %%d assertion%%s)\n"),
                count($result),
                (count($result) == 1) ? '' : 's',
                $this->numAssertions,
                ($this->numAssertions == 1) ? '' : 's'
              )
            );
        }

        else if ((!$result->allCompletlyImplemented() ||
                  !$result->noneSkipped())&&
                 $result->wasSuccessful()) {
            $this->write(
              sprintf(
                Stagehand_TestRunner_Coloring::yellow("OK, but incomplete or skipped tests!\n" .
                                                      "Tests: %%d, Assertions: %%d%%s%%s.\n"),

                count($result),
                $this->numAssertions,
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }

        else {
            $this->write("\nFAILURES!\n");

            $this->write(
              sprintf(
                Stagehand_TestRunner_Coloring::red("Tests: %%d, Assertions: %%s%%s%%s%%s.\n"),

                count($result),
                $this->numAssertions,
                $this->getCountString($result->failureCount(), 'Failures'),
                $this->getCountString($result->errorCount(), 'Errors'),
                $this->getCountString($result->notImplementedCount(), 'Incomplete'),
                $this->getCountString($result->skippedCount(), 'Skipped')
              )
            );
        }
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
