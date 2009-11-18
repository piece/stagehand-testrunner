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

// {{{ Stagehand_TestRunner_Runner_JUnitXMLWriter

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_JUnitXMLWriter
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
        $this->xmlWriter->startDocument('1.0', 'UTF-8');
    }

    // }}}
    // {{{ startTestSuites()

    /**
     * @return string
     */
    public function startTestSuites()
    {
        $this->xmlWriter->startElement('testsuites');
        return $this->xmlWriter->flush();
    }

    // }}}
    // {{{ startTestSuite()

    /**
     * @param string  $className
     * @param integer $testCount
     * @return string
     */
    public function startTestSuite($className, $testCount = null)
    {
        $this->xmlWriter->startElement('testsuite');
        $this->xmlWriter->writeAttribute('name', $className);
        if (!is_null($testCount)) {
            $this->xmlWriter->writeAttribute('tests', $testCount);
        }

        if (strlen($className) && class_exists($className, false)) {
            try {
                $class = new ReflectionClass($className);
                $this->xmlWriter->writeAttribute('file', $class->getFileName());
            } catch (ReflectionException $e) {
            }
        }

        return $this->xmlWriter->flush();
    }

    // }}}
    // {{{ startTestCase()

    /**
     * @param string $methodName
     * @param mixed  $test
     * @return string
     */
    public function startTestCase($methodName, $test)
    {
        $this->xmlWriter->startElement('testcase');
        $this->xmlWriter->writeAttribute('name', $methodName);

        $class = new ReflectionClass($test);
        if ($class->hasMethod($methodName)) {
            $method = $class->getMethod($methodName);

            $this->xmlWriter->writeAttribute('class', $class->getName());
            $this->xmlWriter->writeAttribute('file', $class->getFileName());
            $this->xmlWriter->writeAttribute('line', $method->getStartLine());
        }

        return $this->xmlWriter->flush();
    }

    // }}}
    // {{{ writeError()

    /**
     * @param string $text
     * @param string $type
     * @return string
     */
    public function writeError($text, $type = null)
    {
        return $this->writeFailureOrError($text, $type, 'error');
    }

    // }}}
    // {{{ writeFailure()

    /**
     * @param string $text
     * @param string $type
     * @return string
     */
    public function writeFailure($text, $type = null)
    {
        return $this->writeFailureOrError($text, $type, 'failure');
    }

    // }}}
    // {{{ endTestCase()

    /**
     * @return string
     */
    public function endTestCase()
    {
        return $this->endElementAndFlush();
    }

    // }}}
    // {{{ endTestSuite()

    /**
     * @return string
     */
    public function endTestSuite()
    {
        return $this->endElementAndFlush();
    }

    // }}}
    // {{{ endTestSuites()

    /**
     * @return string
     */
    public function endTestSuites()
    {
        $this->xmlWriter->endElement();
        $this->xmlWriter->endDocument();
        return $this->xmlWriter->flush();
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
     * @return string
     */
    protected function writeFailureOrError($text, $type, $failureOrError)
    {
        $this->xmlWriter->startElement($failureOrError);
        if (!is_null($type)) {
            $this->xmlWriter->writeAttribute('type', $type);
        }
        $this->xmlWriter->text($text);
        $this->xmlWriter->endElement();

        return $this->xmlWriter->flush();
    }

    // }}}
    // {{{ endElementAndFlush()

    /**
     * @return string
     */
    protected function endElementAndFlush()
    {
        $this->xmlWriter->endElement();
        return $this->xmlWriter->flush();
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
