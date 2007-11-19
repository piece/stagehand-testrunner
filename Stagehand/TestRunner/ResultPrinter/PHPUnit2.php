<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP versions 5
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
 * @link       http://www.phpunit.de/
 * @since      File available since Release 1.2.0
 */

require_once 'PHPUnit2/TextUI/ResultPrinter.php';
require_once 'PHPUnit2/Util/Filter.php';
require_once 'Console/Color.php';

// {{{ Stagehand_TestRunner_ResultPrinter_PHPUnit2

/**
 * A result printer for PHPUnit version 2.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 1.2.0
 */
class Stagehand_TestRunner_ResultPrinter_PHPUnit2 extends PHPUnit2_TextUI_ResultPrinter
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

    private $lastTestFailed = FALSE;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ addError()

    /**
     * An error occurred.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addError(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->write(Console_Color::convert('%pE%n'));
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    // }}}
    // {{{ addFailure()

    /**
     * A failure occurred.
     *
     * @param  PHPUnit2_Framework_Test                 $test
     * @param  PHPUnit2_Framework_AssertionFailedError $e
     * @access public
     */
    public function addFailure(PHPUnit2_Framework_Test $test, PHPUnit2_Framework_AssertionFailedError $e) {
        $this->write(Console_Color::convert('%rF%n'));
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    // }}}
    // {{{ addIncompleteTest()

    /**
     * Incomplete test.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @param  Exception               $e
     * @access public
     */
    public function addIncompleteTest(PHPUnit2_Framework_Test $test, Exception $e) {
        $this->write(Console_Color::convert('%yI%n'));
        $this->nextColumn();

        $this->lastTestFailed = TRUE;
    }

    // }}}
    // {{{ endTest()

    /**
     * A test ended.
     *
     * @param  PHPUnit2_Framework_Test $test
     * @access public
     */
    public function endTest(PHPUnit2_Framework_Test $test) {
        if (!$this->lastTestFailed) {
            $this->write(Console_Color::convert('%g.%n'));
            $this->nextColumn();
        }

        $this->lastTestFailed = FALSE;
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
    protected function printDefects($defects, $count, $type) {
        if ($count == 0) {
            return;
        }

        $this->write(
          sprintf(
            Console_Color::convert("%rThere %%s %%d %%s%%s:\n%n"),

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
     * @param  PHPUnit2_Framework_TestFailure $defect
     * @param  integer                        $count
     */
    protected function printDefectHeader(PHPUnit2_Framework_TestFailure $defect, $count) {
        $this->write(
          sprintf(
            Console_Color::convert('%%d) ' . ($defect->isFailure() ? '%r' : '%p') . "%%s\n%n"),

            $count,
            $defect->failedTest()->toString()
          )
        );
    }

    // }}}
    // {{{ printDefectTrace()

    /**
     * @param  PHPUnit2_Framework_TestFailure $defect
     */
    protected function printDefectTrace(PHPUnit2_Framework_TestFailure $defect) {
        $e       = $defect->thrownException();
        $message = method_exists($e, 'toString') ? $e->toString() : $e->getMessage();

        $this->write(Console_Color::convert(($defect->isFailure() ? '%r' : '%p') . Console_Color::escape($message) . "\n%n"));

        $this->write(
          PHPUnit2_Util_Filter::getFilteredStacktrace(
            $defect->thrownException()
          )
        );
    }

    // }}}
    // {{{ printFooter()

    /**
     * @param  PHPUnit2_Framework_TestResult  $result
     */
    protected function printFooter(PHPUnit2_Framework_TestResult $result) {
        if ($result->allCompletlyImplemented() &&
            $result->wasSuccessful()) {
            $this->write(
              sprintf(
                Console_Color::convert("\n%gOK (%%d test%%s)\n%n"),

                $result->runCount(),
                ($result->runCount() == 1) ? '' : 's'
              )
            );
        }
        
        else if (!$result->allCompletlyImplemented() &&
                 $result->wasSuccessful()) {
            $this->write(
              sprintf(
                Console_Color::convert("\n%yOK, but incomplete test cases!!!\nTests run: %%d, Incomplete Tests: %%d.\n%n"),

                $result->runCount(),
                $result->notImplementedCount()
              )
            );
        }        
        
        else {
            $this->write(
              sprintf(
                Console_Color::convert("\n%rFAILURES!!!\nTests run: %%d, Failures: %%d, Errors: %%d, Incomplete Tests: %%d.\n%n"),

                $result->runCount(),
                $result->failureCount(),
                $result->errorCount(),
                $result->notImplementedCount()
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
?>
