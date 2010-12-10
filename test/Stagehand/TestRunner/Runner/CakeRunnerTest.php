<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2010 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.14.0
 */

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/mock_objects.php';
require_once 'simpletest/web_tester.php';

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.14.0
 */
class Stagehand_TestRunner_Runner_CakeRunnerTest extends Stagehand_TestRunner_Runner_SimpleTestRunnerTest
{
    protected $framework = Stagehand_TestRunner_Framework::CAKE;

    /**
     * @test
     */
    public function runsTests()
    {
        $this->loadClasses();
        $this->collector->collectTestCase('Stagehand_TestRunner_CakePassTest');
        $this->runTests();

        $this->assertTestCaseCount(3);
        $this->assertTestCaseExists('testPassWithAnAssertion', 'Stagehand_TestRunner_CakePassTest');
        $this->assertTestCaseExists('testPassWithMultipleAssertions', 'Stagehand_TestRunner_CakePassTest');
        $this->assertTestCaseExists('test日本語を使用できる', 'Stagehand_TestRunner_CakePassTest');
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/211
     * @since Method available since Release 2.14.0
     */
    public function runsTheFilesWithTheSpecifiedSuffix()
    {
        $file = dirname(__FILE__) .
            '/../../../../examples/Stagehand/TestRunner/cake_with_any_suffix_test_.php';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(0);

        $this->config->testFileSuffix = '_test_';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testPass', 'Stagehand_TestRunner_CakeWithAnySuffixTest');
    }

    protected function loadClasses()
    {
        include_once 'Stagehand/TestRunner/cake_pass.test.php';
        include_once 'Stagehand/TestRunner/cake_multiple_classes.test.php';
        include_once 'Stagehand/TestRunner/cake_multiple_classes_with_namespace.test.php';
        include_once 'Stagehand/TestRunner/cake_failure_and_pass.test.php';
        include_once 'Stagehand/TestRunner/cake_error_and_pass.test.php';
        include_once 'Stagehand/TestRunner/cake_multiple_failures.test.php';
        include_once 'Stagehand/TestRunner/cake_web_page.test.php';
        include_once 'Stagehand/TestRunner/cake_always_called_methods.test.php';
    }

    /**
     * @param Stagehand_TestRunner_Config $config
     * @since Method available since Release 2.14.1
     */
    protected function configure(Stagehand_TestRunner_Config $config)
    {
        $config->cakephpAppPath = dirname(__FILE__) . '/../../../../vendor/cakephp/app';
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/235
     * @since Method available since Release 2.14.2
     */
    public function executesTheSpecialMethodsWhenRunningOnlyTheSpecifiedMethods()
    {
        $testClass = 'Stagehand_TestRunner_CakeAlwaysCalledMethodsTest';
        $specialMethods = array('start', 'end', 'startcase', 'endcase', 'starttest', 'endtest');
        $GLOBALS['STAGEHAND_TESTRUNNER_RUNNER_CAKERUNNERTEST_calledMethods'] = array();
        foreach ($specialMethods as $specialMethod) {
            $GLOBALS['STAGEHAND_TESTRUNNER_RUNNER_CAKERUNNERTEST_calledMethods'][$specialMethod] = 0;
        }
        $this->config->addTestingMethod('testPass');
        $this->collector->collectTestCase($testClass);
        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testPass', $testClass);
        foreach ($specialMethods as $specialMethod) {
            $this->assertEquals(1, $GLOBALS['STAGEHAND_TESTRUNNER_RUNNER_CAKERUNNERTEST_calledMethods'][$specialMethod]);
        }
    }
}

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
