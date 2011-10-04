<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.16.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.16.0
 */
class Stagehand_TestRunner_Runner_CIUnitRunnerTest extends Stagehand_TestRunner_Runner_PHPUnitRunnerTest
{
    protected $framework = Stagehand_TestRunner_Framework::CIUNIT;

    protected function loadClasses()
    {
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
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            include_once 'Stagehand/TestRunner/testCIUnitMultipleClassesWithNamespace.php';
        }
    }

    /**
     * @param Stagehand_TestRunner_Config $config
     */
    protected function configure(Stagehand_TestRunner_Config $config)
    {
        $config->ciunitPath = dirname(__FILE__) . '/../../../../vendor/codeigniter/system/application/tests';
    }

    /**
     * @return array
     * @see Stagehand_TestRunner_Runner_PHPUnitRunnerTest::provideMethods()
     */
    public function provideMethods()
    {
        $class1 = 'testStagehand_TestRunner_CIUnitMultipleClasses1';
        $class2 = 'testStagehand_TestRunner_CIUnitMultipleClasses2';
        return array(
                   array('pass1', $class1, $class2),
                   array('PASS1', $class1, $class2),
               );
    }

    /**
     * @return array
     * @see Stagehand_TestRunner_Runner_PHPUnitRunnerTest::provideFullyQualifiedMethodNames()
     */
    public function provideFullyQualifiedMethodNames()
    {
        $class1 = 'testStagehand_TestRunner_CIUnitMultipleClasses1';
        $class2 = 'testStagehand_TestRunner_CIUnitMultipleClasses2';
        $fullyQualifiedMethod = $class1 . '::pass1';
        return array(
                   array($fullyQualifiedMethod, $class1, $class2),
                   array(strtoupper($fullyQualifiedMethod), $class1, $class2),
               );
    }

    /**
     * @return array
     * @see Stagehand_TestRunner_Runner_PHPUnitRunnerTest::provideFullyQualifiedMethodNamesWithNamespaces()
     */
    public function provideFullyQualifiedMethodNamesWithNamespaces()
    {
        $class1 = 'Stagehand\TestRunner\testCIUnitMultipleClassesWithNamespace1';
        $class2 = 'Stagehand\TestRunner\testCIUnitMultipleClassesWithNamespace2';
        $fullyQualifiedMethod = $class1 . '::pass1';
        return array(
                   array('\\' . $fullyQualifiedMethod, $class1, $class2),
                   array('\\' . strtoupper($fullyQualifiedMethod), $class1, $class2),
                   array($fullyQualifiedMethod, $class1, $class2),
               );
    }

    /**
     * @return array
     * @see Stagehand_TestRunner_Runner_PHPUnitRunnerTest::provideClasses()
     */
    public function provideClasses()
    {
        $class1 = 'testStagehand_TestRunner_CIUnitMultipleClasses1';
        $class2 = 'testStagehand_TestRunner_CIUnitMultipleClasses2';
        return array(
                   array($class1, $class1, $class2),
                   array(strtolower($class1), $class1, $class2),
               );
    }

    /**
     * @return array
     * @see Stagehand_TestRunner_Runner_PHPUnitRunnerTest::provideClassesWithNamespaces()
     */
    public function provideClassesWithNamespaces()
    {
        $class1 = 'Stagehand\TestRunner\testCIUnitMultipleClassesWithNamespace1';
        $class2 = 'Stagehand\TestRunner\testCIUnitMultipleClassesWithNamespace2';
        $fullyQualifiedMethod = $class1 . '::pass1';
        return array(
                   array('\\' . $class1, $class1, $class2),
                   array('\\' . strtolower($class1), $class1, $class2),
                   array($class1, $class1, $class2),
               );
    }

    /**
     * @return array
     * @see Stagehand_TestRunner_Runner_PHPUnitRunnerTest::provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests()
     */
    public function provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests()
    {
        return array(
                   array('testStagehand_TestRunner_CIUnitIncomplete::isIncomplete'),
                   array('testStagehand_TestRunner_CIUnitSkipped::isSkipped')
               );
    }

    /**
     * @return array
     */
    public function provideDataForStopsTheTestRunWhenTheFirstFailureIsRaised()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitFailureAndPass', 'testStagehand_TestRunner_CIUnitPass', 'isFailure'),
            array('testStagehand_TestRunner_CIUnitErrorAndPass', 'testStagehand_TestRunner_CIUnitPass', 'isError'),
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

    /**
     * @return array
     */
    public function provideDataForCreatesANotificationForGrowl()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitPass', true, 'OK (3 tests, 4 assertions)'),
            array('testStagehand_TestRunner_CIUnitFailure', false, 'FAILURES! Tests: 1, Assertions: 1, Failures: 1.'),
            array('testStagehand_TestRunner_CIUnitError', false, 'FAILURES! Tests: 1, Assertions: 0, Errors: 1.'),
            array('testStagehand_TestRunner_CIUnitIncomplete', false, 'OK, but incomplete or skipped tests! Tests: 2, Assertions: 0, Incomplete: 2.'),
            array('testStagehand_TestRunner_CIUnitSkipped', false, 'OK, but incomplete or skipped tests! Tests: 2, Assertions: 0, Skipped: 2.'),
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

    /**
     * @return array
     */
    public function provideDataForRunsTheFilesWithTheSpecifiedPattern()
    {
        return array(
            array(dirname(__FILE__) . '/../../../../examples/Stagehand/TestRunner/CIUnitWithAnyPatternTest.php', 'Test\.php$', 'testStagehand_TestRunner_CIUnitWithAnyPattern'),
        );
    }

    /**
     * @return array
     */
    public function provideDataForRunsTheFilesWithTheSpecifiedSuffix()
    {
        return array(
            array(dirname(__FILE__) . '/../../../../examples/Stagehand/TestRunner/CIUnitWithAnySuffix_test_.php', '_test_', 'testStagehand_TestRunner_CIUnitWithAnySuffix'),
        );
    }

    /**
     * @return array
     */
    public function provideDataForReportsOnlyTheFirstFailureInASingleTestToJunitXml()
    {
        return array(
            array('testStagehand_TestRunner_CIUnitMultipleFailures'),
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
