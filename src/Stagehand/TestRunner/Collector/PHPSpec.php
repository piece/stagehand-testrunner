<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://www.phpspec.org/
 * @since      File available since Release 2.1.0
 */

require_once 'Stagehand/TestRunner/Collector/Common.php';

// {{{ Stagehand_TestRunner_Collector_PHPSpec

/**
 * A test collector for PHPSpec.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2008 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://www.phpspec.org/
 * @since      Class available since Release 2.1.0
 */
class Stagehand_TestRunner_Collector_PHPSpec extends Stagehand_TestRunner_Collector_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $_baseClass = 'PHPSpec_Context';
    protected $_suffix = 'Spec';
    protected $_includePattern = '(^[Dd]escribe|[Ss]pec$)';

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ _createTestSuite()

    /**
     * Creates a test suite ArrayObject.
     *
     * @return ArrayObject
     */
    protected function _createTestSuite()
    {
        return new ArrayObject();
    }

    // }}}
    // {{{ _doBuildTestSuite()

    /**
     * Aggregates a test suite ArrayObject to an aggregate test suite ArrayObject.
     *
     * @param ArrayObject $aggregateSuite
     * @param ArrayObject $suite
     */
    protected function _doBuildTestSuite($aggregateSuite, $suite)
    {
        if (!count($suite)) {
            return;
        }

        foreach ($suite as $testCase) {
            $aggregateSuite->append($testCase);
        }
    }

    // }}}
    // {{{ _addTestCase()

    /**
     * Adds a test case to a test suite ArrayObject.
     *
     * @param ArrayObject $suite
     * @param string      $testCase
     */
    protected function _addTestCase($suite, $testCase)
    {
        $suite[] = $testCase;
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
