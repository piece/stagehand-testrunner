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

// {{{ Stagehand_TestRunner_JUnitXMLWriter_JUnitXMLDOMWriter_TestsuiteDOMElement

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_JUnitXMLWriter_JUnitXMLDOMWriter_TestsuiteDOMElement extends DOMElement
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

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * @param string $name
     * @param string $value
     * @param string $namespaceURI
     */
    public function __construct($name = null, $value = null, $namespaceURI = null)
    {
        parent::__construct('testsuite', $value, $namespaceURI);
    }

    // }}}
    // {{{ addTestCount()

    /**
     * @param integer $testCount
     */
    public function addTestCount($testCount)
    {
        $this->setAttribute('tests', $this->getAttribute('tests') + $testCount);
    }

    // }}}
    // {{{ increaseTestCount()

    /**
     */
    public function increaseTestCount()
    {
        $this->addTestCount(1);
    }

    // }}}
    // {{{ addAssertionCount()

    /**
     * @param integer $assertionCount
     */
    public function addAssertionCount($assertionCount)
    {
        if (!$this->hasAttribute('assertions')) {
            $this->setAttribute('assertions', $assertionCount);
        } else {
            $this->setAttribute('assertions', $this->getAttribute('assertions') + $assertionCount);
        }
    }

    // }}}
    // {{{ increaseAssertionCount()

    /**
     */
    public function increaseAssertionCount()
    {
        $this->addAssertionCount(1);
    }

    // }}}
    // {{{ addFailureCount()

    /**
     * @param integer $failureCount
     */
    public function addFailureCount($failureCount)
    {
        $this->setAttribute('failures', $this->getAttribute('failures') + $failureCount);
    }

    // }}}
    // {{{ increaseFailureCount()

    /**
     */
    public function increaseFailureCount()
    {
        $this->addFailureCount(1);
    }

    // }}}
    // {{{ addErrorCount()

    /**
     * @param integer $errorCount
     */
    public function addErrorCount($errorCount)
    {
        $this->setAttribute('errors', $this->getAttribute('errors') + $errorCount);
    }

    // }}}
    // {{{ increaseErrorCount()

    /**
     */
    public function increaseErrorCount()
    {
        $this->addErrorCount(1);
    }

    // }}}
    // {{{ addTime()

    /**
     * @param float $time
     */
    public function addTime($time)
    {
        if (!$this->hasAttribute('time')) {
            $this->setAttribute('time', $time);
        } else {
            $this->setAttribute('time', $this->getAttribute('time') + $time);
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
