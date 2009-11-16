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

// {{{ Stagehand_TestRunner_Runner_SimpleTestRunner_TextReporter

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://simpletest.org/
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_SimpleTestRunner_TextReporter extends TextReporter
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
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
    // {{{ shouldInvokeOnlySpecified()

    /**
     * @param string $testCase
     * @param string $method
     * @return boolean
     */
    protected function shouldInvokeOnlySpecified($testCase, $method)
    {
        $test = new ReflectionClass($testCase);
        if ($test->isAbstract()) {
            return false;
        }

        if ($this->config->testsOnlySpecifiedMethods) {
            $methodsToBeTested = array_map('strtolower', $this->config->elementsToBeTested);
            foreach (array(strtolower($testCase) . '::' . strtolower($method),
                           strtolower($method)
                           ) as $methodName
                     ) {
                if (in_array($methodName, $methodsToBeTested)) {
                    return true;
                }
            }
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
