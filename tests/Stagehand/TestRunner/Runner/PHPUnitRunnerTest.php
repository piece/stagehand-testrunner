<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009-2010 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

// {{{ Stagehand_TestRunner_Runner_PHPUnitRunnerTest

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_PHPUnitRunnerTest extends Stagehand_TestRunner_Runner_PHPUnitRunner_TestCase
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
 
    /**
     * @param string $method
     * @test
     * @dataProvider provideMethods
     */
    public function runsOnlyTheSpecifiedMethods($method)
    {
        $this->config->addMethodToBeTested($method);
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses2Test');
        $this->runTests();
        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'pass1',
            'Stagehand_TestRunner_PHPUnitMultipleClasses1Test'
        );
        $this->assertTestCaseExists(
            'pass1',
            'Stagehand_TestRunner_PHPUnitMultipleClasses2Test'
        );
    }

    public function provideMethods()
    {
        return array(array('pass1'), array('PASS1'));
    }

    /**
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNames
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodName($method)
    {
        $this->config->addMethodToBeTested($method);
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses2Test');
        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'pass1',
            'Stagehand_TestRunner_PHPUnitMultipleClasses1Test'
        );
    }

    public function provideFullyQualifiedMethodNames()
    {
        return array(
                   array('Stagehand_TestRunner_PHPUnitMultipleClasses1Test::pass1'),
                   array('STAGEHAND_TESTRUNNER_PHPUNITMULTIPLECLASSES1TEST::PASS1')
               );
    }

    /**
     * @param $string $class
     * @test
     * @dataProvider provideClasses
     */
    public function runsOnlyTheSpecifiedClasses($class)
    {
        $this->config->addClassToBeTested($class);
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses2Test');
        $this->runTests();
        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'pass1',
            'Stagehand_TestRunner_PHPUnitMultipleClasses1Test'
        );
        $this->assertTestCaseExists(
            'pass2',
            'Stagehand_TestRunner_PHPUnitMultipleClasses1Test'
        );
    }

    public function provideClasses()
    {
        return array(
                   array('Stagehand_TestRunner_PHPUnitMultipleClasses1Test'),
                   array('stagehand_testrunner_phpunitmultipleclasses1test')
               );
    }

    /**
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests
     * @since Method available since Release 2.11.0
     */
    public function includesTheSpecifiedMessageAtTheTestDoxForIncompleteAndSkippedTests($method)
    {
        $this->config->addMethodToBeTested($method);
        $class = substr($method, 0, strpos($method, '::'));
        class_exists($class);
        $this->collector->collectTestCase($class);
        $this->runTests();
        $this->assertRegExp('/^ \[ \] .+\s\(.+\)$/m', $this->output);
    }

    /**
     * @since Method available since Release 2.11.0
     */
    public function provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests()
    {
        return array(
                   array('Stagehand_TestRunner_PHPUnitIncompleteTest::isIncomplete'),
                   array('Stagehand_TestRunner_PHPUnitSkippedTest::isSkipped')
               );
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
 * coding: utf-8
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
