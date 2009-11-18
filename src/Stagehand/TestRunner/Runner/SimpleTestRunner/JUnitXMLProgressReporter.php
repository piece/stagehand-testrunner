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

// {{{ Stagehand_TestRunner_Runner_SimpleTestRunner_JUnitXMLProgressReporter

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://simpletest.org/
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_SimpleTestRunner_JUnitXMLProgressReporter extends SimpleReporter
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $xmlWriter;

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
     */
    public function __construct()
    {
        $this->xmlWriter = new XMLWriter();
        $this->xmlWriter->openMemory();

        parent::SimpleReporter();
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

        $this->xmlWriter->startElement('testsuite');
        $this->xmlWriter->writeAttribute('name', $testName);
        $this->xmlWriter->writeAttribute('tests', $size);

        if (strlen($testName) && class_exists($testName, false)) {
            try {
                $class = new ReflectionClass($testName);
                $this->xmlWriter->writeAttribute('file', $class->getFileName());
            } catch (ReflectionException $e) {
            }
        }

        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintGroupEnd()

    /**
     * @param string  $testName
     */
    public function paintGroupEnd($testName)
    {
        parent::paintGroupEnd($testName);

        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintCasetart()

    /**
     * @param string $testName
     */
    public function paintCaseStart($testName)
    {
        parent::paintCaseStart($testName);

        $this->xmlWriter->startElement('testsuite');
        $this->xmlWriter->writeAttribute('name', $testName);

        if (strlen($testName) && class_exists($testName, false)) {
            try {
                $class = new ReflectionClass($testName);
                $this->xmlWriter->writeAttribute('file', $class->getFileName());
            } catch (ReflectionException $e) {
            }
        }

        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintCaseEnd()

    /**
     * @param string $testName
     */
    public function paintCaseEnd($testName)
    {
        parent::paintCaseEnd($testName);

        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintMethodStart()

    /**
     * @param string $testName
     */
    public function paintMethodStart($testName)
    {
        parent::paintMethodStart($testName);

        $this->xmlWriter->startElement('testcase');
        $this->xmlWriter->writeAttribute('name', $testName);

        $context = SimpleTest::getContext();
        $test = $context->getTest();

        if ($test instanceof SimpleTestCase) {
            $class      = new ReflectionClass($test);
            $methodName = $testName;

            if ($class->hasMethod($methodName)) {
                $method = $class->getMethod($methodName);

                $this->xmlWriter->writeAttribute('class', $class->getName());
                $this->xmlWriter->writeAttribute('file', $class->getFileName());
                $this->xmlWriter->writeAttribute('line', $method->getStartLine());
            }
        }

        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintMethodEnd()

    /**
     * @param string $testName
     */
    public function paintMethodEnd($testName)
    {
        parent::paintMethodEnd($testName);

        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintHeader()

    /**
     * @param string $testName
     */
    public function paintHeader($testName)
    {
        parent::paintHeader($testName);

        $this->xmlWriter->startDocument('1.0', 'UTF-8');
        $this->xmlWriter->startElement('testsuites');
    }

    // }}}
    // {{{ paintFooter()

    /**
     * @param string $testName
     */
    public function paintFooter($testName)
    {
        parent::paintFooter($testName);

        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintFail()

    /**
     * @param string $message
     */
    public function paintFail($message)
    {
        parent::paintFail($message);

        $failureTrace = $message . "\n\n";
        for ($backtrace = debug_backtrace(), $i = 0, $count = count($backtrace); $i < $count; ++$i) {
            $failureTrace .= $backtrace[$i]['file'] . ':' . $backtrace[$i]['line'] . "\n";
        }

        $this->xmlWriter->startElement('failure');
        $this->xmlWriter->text($failureTrace);
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintError()

    /**
     * @param string $message
     */
    public function paintError($message)
    {
        parent::paintError($message);

        $failureTrace = $message . "\n\n";
        for ($backtrace = debug_backtrace(), $i = 0, $count = count($backtrace); $i < $count; ++$i) {
            $failureTrace .= $backtrace[$i]['file'] . ':' . $backtrace[$i]['line'] . "\n";
        }

        $this->xmlWriter->startElement('error');
        $this->xmlWriter->text($failureTrace);
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintException()

    /**
     * @param Exception $e
     */
    public function paintException(Exception $e)
    {
        parent::paintException($e);

        $failureTrace = get_class($e) . ': ' . $e->getMessage() . "\n\n";
        for ($backtrace = $e->getTrace(), $i = 0, $count = count($backtrace); $i < $count; ++$i) {
            $failureTrace .= $backtrace[$i]['file'] . ':' . $backtrace[$i]['line'] . "\n";
        }

        $this->xmlWriter->startElement('error');
        $this->xmlWriter->text($failureTrace);
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    // }}}
    // {{{ paintSkip()

    /**
     * @param string $message
     */
    public function paintSkip($message)
    {
        parent::paintSkip($message);

        $failureTrace = 'Skip: ' . $message . "\n\n";
        for ($backtrace = debug_backtrace(), $i = 0, $count = count($backtrace); $i < $count; ++$i) {
            $failureTrace .= $backtrace[$i]['file'] . ':' . $backtrace[$i]['line'] . "\n";
        }

        $this->xmlWriter->startElement('error');
        $this->xmlWriter->text($failureTrace);
        $this->xmlWriter->endElement();
        echo $this->xmlWriter->outputMemory();
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
