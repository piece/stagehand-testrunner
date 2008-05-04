<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.1.0
 */

require_once 'Stagehand/TestRunner/Collector/Common.php';
require_once 'PHPUnit/Framework/TestSuite.php';

// {{{ Stagehand_TestRunner_Collector_PHPUnit

/**
 * A test collector for PHPUnit.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.1.0
 */
class Stagehand_TestRunner_Collector_PHPUnit extends Stagehand_TestRunner_Collector_Common
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $_excludePattern = '^PHPUnit';
    protected $_baseClass = 'PHPUnit_Framework_TestCase';

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
     * Creates a test suite object.
     *
     * @return PHPUnit_Framework_TestSuite
     */
    protected function _createTestSuite()
    {
        return new PHPUnit_Framework_TestSuite();
    }

    // }}}
    // {{{ _doBuildTestSuite()

    /**
     * Aggregates a test suite object to an aggregate test suite object.
     *
     * @param PHPUnit_Framework_TestSuite $aggregateSuite
     * @param PHPUnit_Framework_TestSuite $suite
     */
    protected function _doBuildTestSuite($aggregateSuite, $suite)
    {
        if (!$suite->count()) {
            return;
        }

        $aggregateSuite->addTest($suite);
    }

    // }}}
    // {{{ _addTestCase()

    /**
     * Adds a test case to a test suite object.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @param string                      $testCase
     */
    protected function _addTestCase($suite, $testCase)
    {
        $suite->addTestSuite($testCase);
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
