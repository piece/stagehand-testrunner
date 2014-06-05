<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\Runner;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
abstract class CompatibilityTestCase extends TestCase
{
    protected static $RESULT_PASSED = true;
    protected static $RESULT_NOT_PASSED = false;
    protected static $COLORS = true;
    protected static $NOT_COLOR = false;

    /**
     * @test
     * @dataProvider dataForTestMethods
     * @param string $firstTestClass
     * @param string $secondTestClass
     * @param string $specyfyingTestMethod
     * @param string $runningTestMethod
     */
    public function runsOnlyTheSpecifiedMethods($firstTestClass, $secondTestClass, $specyfyingTestMethod, $runningTestMethod)
    {
        $testTargetRepository = $this->createTestTargetRepository();
        $testTargetRepository->setMethods(array($specyfyingTestMethod));
        $collector = $this->createCollector();
        $collector->collectTestCase($firstTestClass);
        $collector->collectTestCase($secondTestClass);

        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists($this->getTestMethodName($runningTestMethod), $firstTestClass);
        $this->assertTestCaseExists($this->getTestMethodName($runningTestMethod), $secondTestClass);
    }

    /**
     * @return array
     */
    abstract public function dataForTestMethods();

    /**
     * @test
     * @dataProvider dataForTestClasses
     * @param string $firstTestClass
     * @param string $secondTestClass
     * @param string $specifyingTestClass
     * @param string $runningTestMethod1
     * @param string $runningTestMethod2
     */
    public function runsOnlyTheSpecifiedClasses($firstTestClass, $secondTestClass, $specifyingTestClass, $runningTestMethod1, $runningTestMethod2)
    {
        $testTargetRepository = $this->createTestTargetRepository();
        $testTargetRepository->setClasses(array($specifyingTestClass));
        $collector = $this->createCollector();
        $collector->collectTestCase($firstTestClass);
        $collector->collectTestCase($secondTestClass);

        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists($this->getTestMethodName($runningTestMethod1), $firstTestClass);
        $this->assertTestCaseExists($this->getTestMethodName($runningTestMethod2), $firstTestClass);
    }

    /**
     * @return array
     */
    abstract public function dataForTestClasses();

    /**
     * @test
     * @dataProvider dataForStopOnFailure
     * @param string $firstTestClass
     * @param string $secondTestClass
     * @param string $failingTestMethod
     */
    public function stopsTheTestRunWhenTheFirstFailureIsRaised($firstTestClass, $secondTestClass, $failingTestMethod)
    {
        $testTargetRepository = $this->createTestTargetRepository();
        $testTargetRepository->setClasses(array($firstTestClass, $secondTestClass));
        $collector = $this->createCollector();
        $collector->collectTestCase($firstTestClass);
        $collector->collectTestCase($secondTestClass);
        $runner = $this->createRunner();
        $runner->setStopOnFailure(true);

        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists($this->getTestMethodName($failingTestMethod), $firstTestClass);
    }

    /**
     * @return array
     */
    abstract public function dataForStopOnFailure();

    /**
     * @test
     * @dataProvider dataForNotify
     * @param string $testClass
     * @param boolean $testResult
     * @param boolean $colors
     * @param string $description
     * @link http://redmine.piece-framework.com/issues/192
     */
    public function createsANotification($testClass, $testResult, $colors, $description)
    {
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);
        $runner = $this->createRunner();
        $runner->setNotify(true);
        $terminal = $this->createTerminal();
        $terminal->setColor($colors);

        $this->runTests();

        $notification = $runner->getNotification();
        if ($testResult == self::$RESULT_PASSED) {
            $this->assertTrue($notification->isPassed());
            $this->assertFalse($notification->isFailed());
            $this->assertFalse($notification->isStopped());
        } else {
            $this->assertFalse($notification->isPassed());
            $this->assertTrue($notification->isFailed());
            $this->assertFalse($notification->isStopped());
        }
        $this->assertEquals($description, $notification->getMessage());
    }

    abstract public function dataForNotify();

    /**
     * @test
     * @dataProvider dataForMultipleFailures
     * @param string $testClass
     * @param string $failingMethod
     * @link http://redmine.piece-framework.com/issues/219
     */
    public function reportsOnlyTheFirstFailureInASingleTestToJunitXml($testClass, $failingMethod)
    {
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);

        $this->runTests();

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(__DIR__ . '/../../src/Resources/config/schema/junit-xml-dom-2.10.0.rng'));

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists($this->getTestMethodName($failingMethod), $testClass);
        $this->assertTestCaseAssertionCount(1, $this->getTestMethodName($failingMethod), $testClass);
        $this->assertTestCaseFailed($this->getTestMethodName($failingMethod), $testClass);
        $this->assertTestCaseFailureMessageEquals('/The First Failure/', $this->getTestMethodName($failingMethod), $testClass);
    }

    /**
     * @return array
     */
    abstract public function dataForMultipleFailures();
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
