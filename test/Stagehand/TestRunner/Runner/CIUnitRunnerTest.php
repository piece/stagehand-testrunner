<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.16.0
 */

namespace Stagehand\TestRunner\Runner;

use Stagehand\TestRunner\Core\Plugin\CIUnitPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.16.0
 */
class CIUnitRunnerTest extends PHPUnitRunnerTest
{
    protected function configure()
    {
        $preparer = $this->createPreparer(); /* @var $preparer \Stagehand\TestRunner\Preparer\CIUnitPreparer */
        $preparer->setCIUnitPath(__DIR__ . '/../../../../vendor/codeigniter/system/application/tests');
        $preparer->prepare();

        include_once 'Stagehand/TestRunner/testCIUnitMultipleClasses.php';
        include_once 'Stagehand/TestRunner/testCIUnitIncomplete.php';
        include_once 'Stagehand/TestRunner/testCIUnitSkipped.php';
        include_once 'Stagehand/TestRunner/testCIUnitFailureAndPass.php';
        include_once 'Stagehand/TestRunner/testCIUnitErrorAndPass.php';
        include_once 'Stagehand/TestRunner/testCIUnitPass.php';
        include_once 'Stagehand/TestRunner/testCIUnitFailure.php';
        include_once 'Stagehand/TestRunner/testCIUnitError.php';
        include_once 'Stagehand/TestRunner/testCIUnitMultipleFailures.php';
        include_once 'Stagehand/TestRunner/testCIUnitGroups.php';
        include_once 'Stagehand/TestRunner/testCIUnitMultipleClassesWithNamespace.php';
    }

    /**
     * @return string
     * @since Method available since Release 3.0.0
     */
    protected function getPluginID()
    {
        return CIUnitPlugin::getPluginID();
    }

    public function dataForTestMethods()
    {
        $firstTestClass = 'testStagehand_TestRunner_CIUnitMultipleClasses1';
        $secondTestClass = 'testStagehand_TestRunner_CIUnitMultipleClasses2';
        $specifyingTestMethod = 'pass1';
        $runningTestMethod = $specifyingTestMethod;
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestMethod, $runningTestMethod),
        );
    }

    public function dataForTestClasses()
    {
        $firstTestClass = 'testStagehand_TestRunner_CIUnitMultipleClasses1';
        $secondTestClass = 'testStagehand_TestRunner_CIUnitMultipleClasses2';
        $specifyingTestClass = $firstTestClass;
        $runningTestMethod1 = 'pass1';
        $runningTestMethod2 = 'pass2';
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestClass, $runningTestMethod1, $runningTestMethod2),
        );
    }

    /**
     * @return array
     * @see \Stagehand\TestRunner\Runner\PHPUnitRunnerTest::provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests()
     */
    public function provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests()
    {
        return array(
                   array('testStagehand_TestRunner_CIUnitIncomplete::isIncomplete'),
                   array('testStagehand_TestRunner_CIUnitSkipped::isSkipped')
               );
    }

    public function dataForStopOnFailure()
    {
        $firstTestClass1 = 'testStagehand_TestRunner_CIUnitFailureAndPass';
        $firstTestClass2 = 'testStagehand_TestRunner_CIUnitErrorAndPass';
        $secondTestClass1 = 'testStagehand_TestRunner_CIUnitPass';
        $secondTestClass2 = $secondTestClass1;
        $failingTestMethod1 = 'isFailure';
        $failingTestMethod2 = 'isError';
        return array(
            array($firstTestClass1, $secondTestClass1, $failingTestMethod1),
            array($firstTestClass2, $secondTestClass2, $failingTestMethod2),
        );
    }

    /**
     * @return array
     */
    public function provideDataForNotStopTheTestRunWhenATestCaseIsSkipped()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitSkipped', 'testStagehand_TestRunner_CIUnitPass'),
        );
    }

    /**
     * @return array
     */
    public function provideDataForNotStopTheTestRunWhenATestCaseIsIncomplete()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitIncomplete', 'testStagehand_TestRunner_CIUnitPass'),
        );
    }

    /**
     * @return array
     */
    public function provideDataForNotBreakTestDoxOutputIfTheSameTestMethodNamesExceptTrailingNumbers()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitMultipleClasses1', 'testStagehand_TestRunner_CIUnitMultipleClasses1'),
        );
    }

    public function dataForNotify()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitPass', static::$RESULT_PASSED, static::$COLORS, 'OK (3 tests, 4 assertions)'),
            array('testStagehand_TestRunner_CIUnitPass', static::$RESULT_PASSED, static::$NOT_COLOR, 'OK (3 tests, 4 assertions)'),
            array('testStagehand_TestRunner_CIUnitFailure', static::$RESULT_NOT_PASSED, static::$COLORS, 'FAILURES! Tests: 1, Assertions: 1, Failures: 1.'),
            array('testStagehand_TestRunner_CIUnitFailure', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, 'FAILURES! Tests: 1, Assertions: 1, Failures: 1.'),
            array('testStagehand_TestRunner_CIUnitIncomplete', static::$RESULT_NOT_PASSED, static::$COLORS, 'OK, but incomplete or skipped tests! Tests: 2, Assertions: 0, Incomplete: 2.'),
            array('testStagehand_TestRunner_CIUnitIncomplete', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, 'OK, but incomplete or skipped tests! Tests: 2, Assertions: 0, Incomplete: 2.'),
        );
    }

    /**
     * @retuan array
     * @since Method available since Release 2.16.0
     */
    public function provideDataForConfiguresPhpUnitRuntimeEnvironmentByTheXmlConfigurationFile()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitPass'),
        );
    }

    public function dataForMultipleFailures()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitMultipleFailures', 'isFailure'),
        );
    }

    /**
     * @return string
     * @since Method available since Release 2.17.0
     */
    protected function groupsTest()
    {
        return 'testStagehand_TestRunner_CIUnitGroups';
    }
}

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
