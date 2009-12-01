<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.10.0
 */

require_once 'PHPUnit/Framework/SelfDescribing.php';
require_once 'PHPUnit/Framework/Test.php';
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Framework/TestFailure.php';
require_once 'PHPUnit/Framework/TestListener.php';
require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/Util/Class.php';
require_once 'PHPUnit/Util/Filter.php';
require_once 'PHPUnit/Util/Printer.php';
require_once 'PHPUnit/Util/XML.php';

// {{{ Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_JUnitXMLPrinter

/**
 * A result printer for PHPUnit.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_PHPUnitRunner_Printer_JUnitXMLPrinter extends PHPUnit_Util_Printer implements PHPUnit_Framework_TestListener
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $autoFlush = true;
    protected $xmlWriter;
    protected $testSuitesWrote = false;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ flush()

    /**
     */
    public function flush()
    {
        $this->xmlWriter->endTestSuites();
        parent::flush();
    }

    // }}}
    // {{{ addError()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addFailureOrError($test, $e, $time, 'error');
    }

    // }}}
    // {{{ addFailure()

    /**
     * @param PHPUnit_Framework_Test                 $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float                                  $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->addFailureOrError($test, $e, $time, 'failure');
    }

    // }}}
    // {{{ addIncompleteTest()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->writeFailureOrError('Incomplete Test', $e, 'error');
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
        $this->writeFailureOrError('Skipped Test', $e, 'error');
    }

    // }}}
    // {{{ startTestSuite()

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if (!$this->testSuitesWrote) {
            $this->xmlWriter->startTestSuites();
            $this->testSuitesWrote = true;
        }

        $this->xmlWriter->startTestSuite($suite->getName(), count($suite));
    }

    // }}}
    // {{{ endTestSuite()

    /**
     * @param PHPUnit_Framework_TestSuite $suite
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $this->xmlWriter->endTestSuite();
    }

    // }}}
    // {{{ startTest()

    /**
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $this->xmlWriter->startTestCase($test->getName(), $test);
    }

    // }}}
    // {{{ endTest()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param float                  $time
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
       if (!$test instanceof PHPUnit_Framework_TestCase) {
           $this->xmlWriter->endTestCase($time);
           return;
       }

       $this->xmlWriter->endTestCase($time, $test->getNumAssertions());
    }

    // }}}
    // {{{ setXMLWriter()

    /**
     * @param Stagehand_TestRunner_Runner_JUnitXMLWriter $xmlWriter
     */
    public function setXMLWriter(Stagehand_TestRunner_Runner_JUnitXMLWriter $xmlWriter)
    {
        $this->xmlWriter = $xmlWriter;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ writeFailureOrError()

    /**
     * @param string    $message
     * @param Exception $e
     * @param string    $failureOrError
     */
    protected function writeFailureOrError(
        $message,
        Exception $e,
        $failureOrError)
    {
        $this->xmlWriter->{ 'write' . $failureOrError }(
            PHPUnit_Util_XML::convertToUtf8(
                $message .
                PHPUnit_Util_Filter::getFilteredStacktrace($e, false)
            ),
            get_class($e)
        );
    }

    // }}}
    // {{{ addFailureOrError()

    /**
     * @param PHPUnit_Framework_Test $test
     * @param Exception              $e
     * @param float                  $time
     * @param string                 $failureOrError
     */
    protected function addFailureOrError(
        PHPUnit_Framework_Test $test,
        Exception $e,
        $time,
        $failureOrError)
    {
        if ($test instanceof PHPUnit_Framework_SelfDescribing) {
            $message = $test->toString() . "\n\n";
        } else {
            $message = '';
        }

        $message .= PHPUnit_Framework_TestFailure::exceptionToString($e) . "\n";

        $this->writeFailureOrError($message, $e, $failureOrError);
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
