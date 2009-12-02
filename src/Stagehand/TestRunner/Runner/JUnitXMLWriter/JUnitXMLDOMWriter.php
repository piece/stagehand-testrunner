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
 * @since      File available since Release 2.10.0
 */

// {{{ Stagehand_TestRunner_Runner_JUnitXMLWriter_JUnitXMLDOMWriter

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_JUnitXMLWriter_JUnitXMLDOMWriter implements Stagehand_TestRunner_Runner_JUnitXMLWriter
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
    protected $streamWriter;
    protected $elementStack = array();

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
     * @param callback $streamWriter
     */
    public function __construct($streamWriter)
    { 
        $this->streamWriter = $streamWriter;
        $this->xmlWriter = new DOMDocument('1.0', 'UTF-8');
    }

    // }}}
    // {{{ startTestSuites()

    /**
     */
    public function startTestSuites()
    {
        $testsuites = $this->xmlWriter->createElement('testsuites');
        $this->xmlWriter->appendChild($testsuites);
        $this->elementStack[] = $testsuites;
    }

    // }}}
    // {{{ startTestSuite()

    /**
     * @param string  $name
     * @param integer $testCount
     */
    public function startTestSuite($name, $testCount = null)
    {
        $testsuite =
            new Stagehand_TestRunner_Runner_JUnitXMLWriter_JUnitXMLDOMWriter_TestsuiteDOMElement();
        $this->getCurrentTestsuite()->appendChild($testsuite);
        $testsuite->setAttribute('name', $name);
        $testsuite->setAttribute('tests', 0);
        $testsuite->setAttribute('failures', 0);
        $testsuite->setAttribute('errors', 0);

        if (strlen($name) && class_exists($name, false)) {
            try {
                $class = new ReflectionClass($name);
                $testsuite->setAttribute('file', $class->getFileName());
            } catch (ReflectionException $e) {
            }
        }

        $this->elementStack[] = $testsuite;
    }

    // }}}
    // {{{ startTestCase()

    /**
     * @param string $name
     * @param mixed  $test
     */
    public function startTestCase($name, $test)
    {
        $testcase = $this->xmlWriter->createElement('testcase');
        $this->getCurrentTestsuite()->appendChild($testcase);
        $testcase->setAttribute('name', $name);

        $class = new ReflectionClass($test);
        if ($class->hasMethod($name)) {
            $method = $class->getMethod($name);

            $testcase->setAttribute('class', $class->getName());
            $testcase->setAttribute('file', $class->getFileName());
            $testcase->setAttribute('line', $method->getStartLine());
        }

        $this->elementStack[] = $testcase;
    }

    // }}}
    // {{{ writeError()

    /**
     * @param string $text
     * @param string $type
     */
    public function writeError($text, $type = null)
    {
        $this->writeFailureOrError($text, $type, 'error');
    }

    // }}}
    // {{{ writeFailure()

    /**
     * @param string $text
     * @param string $type
     */
    public function writeFailure($text, $type = null)
    {
        $this->writeFailureOrError($text, $type, 'failure');
    }

    // }}}
    // {{{ endTestCase()

    /**
     * @param float   $time
     * @param integer $assertionCount
     */
    public function endTestCase($time = null, $assertionCount = null)
    {
        $testCase = array_pop($this->elementStack);

        if (!is_null($assertionCount)) {
            $testCase->setAttribute('assertions', $assertionCount);
            $this->getCurrentTestsuite()->addAssertionCount($assertionCount);
        }

        if (!is_null($time)) {
            $testCase->setAttribute('time', $time);
            $this->getCurrentTestsuite()->addTime($time);
        }

        $this->getCurrentTestsuite()->increaseTestCount();
    }

    // }}}
    // {{{ endTestSuite()

    /**
     */
    public function endTestSuite()
    {
        $testSuite = array_pop($this->elementStack);
        if ($this->getCurrentTestsuite() instanceof
            Stagehand_TestRunner_Runner_JUnitXMLWriter_JUnitXMLDOMWriter_TestsuiteDOMElement) {
            $this->getCurrentTestsuite()->addTestCount($testSuite->getAttribute('tests'));
            if ($testSuite->hasAttribute('assertions')) {
                $this->getCurrentTestsuite()->addAssertionCount(
                    $testSuite->getAttribute('assertions')
                );
            }
            $this->getCurrentTestsuite()->addErrorCount($testSuite->getAttribute('errors'));
            $this->getCurrentTestsuite()->addFailureCount($testSuite->getAttribute('failures'));
            if ($testSuite->hasAttribute('time')) {
                $this->getCurrentTestsuite()->addTime($testSuite->getAttribute('time'));
            }
        }
    }

    // }}}
    // {{{ endTestSuites()

    /**
     */
    public function endTestSuites()
    {
        $this->flush();
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ writeFailureOrError()

    /**
     * @param string $text
     * @param string $type
     * @param string $failureOrError
     */
    protected function writeFailureOrError($text, $type, $failureOrError)
    {
        $error = $this->xmlWriter->createElement($failureOrError, $text);
        $this->getCurrentElement()->appendChild($error);
        if (!is_null($type)) {
            $error->setAttribute('type', $type);
        }

        $this->getCurrentTestsuite()->{ 'increase' . $failureOrError . 'Count' }();
    }

    // }}}
    // {{{ flush()

    /**
     */
    protected function flush()
    {
        call_user_func($this->streamWriter, $this->xmlWriter->saveXML());
    }

    // }}}
    // {{{ getCurrentTestsuite()

    /**
     * @return Stagehand_TestRunner_Runner_JUnitXMLWriter_JUnitXMLDOMWriter_TestsuiteDOMElement
     */
    protected function getCurrentTestsuite()
    {
        if ($this->getCurrentElement()->tagName == 'testcase') {
            return $this->getPreviousElement();
        }

        return $this->getCurrentElement();
    }

    // }}}
    // {{{ getCurrentElement()

    /**
     * @return DOMElement
     */
    protected function getCurrentElement()
    {
        return $this->elementStack[ count($this->elementStack) - 1 ];
    }

    // }}}
    // {{{ getPreviousElement()

    /**
     * @return DOMElement
     */
    protected function getPreviousElement()
    {
        return $this->elementStack[ count($this->elementStack) - 2 ];
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
