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
 * @link       http://simpletest.org/
 * @since      File available since Release 2.10.0
 */

require_once 'simpletest/reporter.php';

// {{{ Stagehand_TestRunner_Runner_SimpleTestRunner_JUnitXMLReporter

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://simpletest.org/
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_SimpleTestRunner_JUnitXMLReporter extends SimpleReporter implements Stagehand_TestRunner_Runner_JUnitXMLWriterAdapter
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**
     * @var Stagehand_TestRunner_JUnitXMLWriter
     */
    protected $xmlWriter;

    /**
     * @var Stagehand_TestRunner_Runner_SimpleTestRunner_TestSuite
     */
    protected $testSuite;

    /**
     * @var Stagehand_TestRunner_Config
     */
    protected $config;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * @param Stagehand_TestRunner_Config $config
     */
    public function __construct(Stagehand_TestRunner_Config $config)
    {
        $this->config = $config;
    }

    // }}}
    // {{{ setXMLWriter()

    /**
     * @param Stagehand_TestRunner_JUnitXMLWriter $xmlWriter
     */
    public function setXMLWriter(Stagehand_TestRunner_JUnitXMLWriter $xmlWriter)
    {
        $this->xmlWriter = $xmlWriter;
    }

    // }}}
    // {{{ setTestSuite()

    /**
     * @param Stagehand_TestRunner_Runner_SimpleTestRunner_TestSuite $testSuite
     */
    public function setTestSuite(Stagehand_TestRunner_Runner_SimpleTestRunner_TestSuite $testSuite)
    {
        $this->testSuite = $testSuite;
    }

    // }}}
    // {{{ paintGroupStart()

    /**
     * @param string  $testName
     * @param integer $size
     */
    public function paintGroupStart($testName, $size)
    {
        parent::paintGroupStart($testName, $size);
        $this->xmlWriter->startTestSuite($testName, $this->testSuite->getTestCount());
    }

    // }}}
    // {{{ paintGroupEnd()

    /**
     * @param string  $testName
     */
    public function paintGroupEnd($testName)
    {
        parent::paintGroupEnd($testName);
        $this->xmlWriter->endTestSuite();
    }

    // }}}
    // {{{ paintCasetart()

    /**
     * @param string $testName
     */
    public function paintCaseStart($testName)
    {
        parent::paintCaseStart($testName);
        $this->xmlWriter->startTestSuite(
            $testName,
            count(SimpleTest::getContext()->getTest()->getTests())
        );
    }

    // }}}
    // {{{ paintCaseEnd()

    /**
     * @param string $testName
     */
    public function paintCaseEnd($testName)
    {
        parent::paintCaseEnd($testName);
        $this->xmlWriter->endTestSuite();
    }

    // }}}
    // {{{ paintMethodStart()

    /**
     * @param string $testName
     */
    public function paintMethodStart($testName)
    {
        parent::paintMethodStart($testName);
        $this->xmlWriter->startTestCase(
            $testName,
            SimpleTest::getContext()->getTest()
        );
    }

    // }}}
    // {{{ paintMethodEnd()

    /**
     * @param string $testName
     */
    public function paintMethodEnd($testName)
    {
        parent::paintMethodEnd($testName);
        $this->xmlWriter->endTestCase();
    }

    // }}}
    // {{{ paintHeader()

    /**
     * @param string $testName
     */
    public function paintHeader($testName)
    {
        parent::paintHeader($testName);
        $this->xmlWriter->startTestSuites();
    }

    // }}}
    // {{{ paintFooter()

    /**
     * @param string $testName
     */
    public function paintFooter($testName)
    {
        parent::paintFooter($testName);
        $this->xmlWriter->endTestSuites();
    }

    // }}}
    // {{{ paintFail()

    /**
     * @param string $message
     */
    public function paintFail($message)
    {
        parent::paintFail($message);
        $this->paintFailureOrError($message, 'failure');
    }

    // }}}
    // {{{ paintError()

    /**
     * @param string $message
     */
    public function paintError($message)
    {
        parent::paintError($message);
        $this->paintFailureOrError($message, 'error');
    }

    // }}}
    // {{{ paintException()

    /**
     * @param Exception $e
     */
    public function paintException(Exception $e)
    {
        parent::paintException($e);
        $this->paintFailureOrError(get_class($e) . ': ' . $e->getMessage(), 'error');
    }

    // }}}
    // {{{ paintSkip()

    /**
     * @param string $message
     */
    public function paintSkip($message)
    {
        parent::paintSkip($message);
        $this->paintFailureOrError('Skip: ' . $message, 'error');
    }

    // }}}
    // {{{ shouldInvoke()

    /**
     * @param string $testCase
     * @param string $method
     * @return boolean
     */
    public function shouldInvoke($testCase, $method)
    {
        if ($this->config->testsOnlySpecified()) {
            return $this->shouldInvokeOnlySpecified($testCase, $method);
        }

        return parent::shouldInvoke($testCase, $method);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ buildFailureTrace()

    /**
     * @param array $backtrace
     */
    protected function buildFailureTrace(array $backtrace)
    {
        $failureTrace = '';
        for ($i = 0, $count = count($backtrace); $i < $count; ++$i) {
            if (!array_key_exists('file', $backtrace[$i])) {
                continue;
            }

            $failureTrace .=
                $backtrace[$i]['file'] .
                ':' .
                (array_key_exists('line', $backtrace[$i]) ? $backtrace[$i]['line']
                                                          : '?') .
                "\n";
        }

        return $failureTrace;
    }

    // }}}
    // {{{ paintFailureOrError()

    /**
     * @param string $message
     * @param string $failureOrError
     */
    protected function paintFailureOrError($message, $failureOrError)
    {
        $this->xmlWriter->{ 'write' . $failureOrError }(
            $message . "\n\n" .
            $this->buildFailureTrace(debug_backtrace())
        );
    }

    // }}}
    // {{{ shouldInvokeOnlySpecified()

    /**
     * @param string $testCase
     * @param string $method
     * @return boolean
     */
    protected function shouldInvokeOnlySpecified($testCase, $method)
    {
        if ($this->config->testsOnlySpecifiedMethods) {
            return $this->config->inMethodsToBeTested($testCase, $method);
        } elseif ($this->config->testsOnlySpecifiedClasses) {
            return $this->config->inClassesToBeTested($testCase);
        }

        return false;
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
