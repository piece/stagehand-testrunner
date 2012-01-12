<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2009-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

namespace Stagehand\TestRunner\Runner;

use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class PHPUnitRunnerTest extends CompatibilityTestCase
{
    /**
     * @since Method available since Release 2.16.0
     */
    protected function configure()
    {
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        class_exists('\Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespaceTest');
    }

    /**
     * @return string
     * @since Method available since Release 3.0.0
     */
    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    public function dataForTestMethods()
    {
        $firstTestClass = 'Stagehand_TestRunner_PHPUnitMultipleClasses1Test';
        $secondTestClass = 'Stagehand_TestRunner_PHPUnitMultipleClasses2Test';
        $specifyingTestMethod = 'pass1';
        $runningTestMethod = $specifyingTestMethod;
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestMethod, $runningTestMethod),
        );
    }

    public function dataForTestClasses()
    {
        $firstTestClass = 'Stagehand_TestRunner_PHPUnitMultipleClasses1Test';
        $secondTestClass = 'Stagehand_TestRunner_PHPUnitMultipleClasses2Test';
        $specifyingTestClass = $firstTestClass;
        $runningTestMethod1 = 'pass1';
        $runningTestMethod2 = 'pass2';
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestClass, $runningTestMethod1, $runningTestMethod2),
        );
    }

    /**
     * @param string $testMethod
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests
     * @since Method available since Release 2.11.0
     */
    public function printsTheSpecifiedMessageForIncompleteAndSkippedTests($testMethod)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array($testMethod));
        preg_match('/^(.*?)::(.*)/', $testMethod, $matches);
        $testClass = $matches[1];
        $testMethod = $matches[2];
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);
        $runner = $this->createRunner(); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
        $runner->setPrintsDetailedProgressReport(true);

        $this->runTests();

        $this->assertRegExp('/^  ' . $testMethod . ' ... .+\s\(.+\)/m', $this->output);
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

    /**
     * @param string $testMethod
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesForIncompleteAndSkippedTestsWithoutMessage
     * @since Method available since Release 2.11.0
     */
    public function printsNormalOutputForIncompleteAndSkippedTestsIfTheMessageIsNotSpecified($testMethod)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array($testMethod));
        preg_match('/^(.*?)::(.*)/', $testMethod, $matches);
        $testClass = $matches[1];
        $testMethod = $matches[2];
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);
        $runner = $this->createRunner(); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
        $runner->setPrintsDetailedProgressReport(true);

        $this->runTests();

        $this->assertRegExp('/^  ' . $testMethod . ' ... [^()]+$/m', $this->output);
        $this->assertRegExp('/^ \[ \] [^()]+$/m', $this->output);
    }

    /**
     * @return array
     * @since Method available since Release 2.11.0
     */
    public function provideFullyQualifiedMethodNamesForIncompleteAndSkippedTestsWithoutMessage()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitIncompleteTest::isIncompleteWithoutMessage'),
            array('Stagehand_TestRunner_PHPUnitSkippedTest::isSkippedWithoutMessage')
        );
    }

    /**
     * @since Method available since Release 2.16.0
     */
    public function dataForStopOnFailure()
    {
        $firstTestClass1 = 'Stagehand_TestRunner_PHPUnitFailureAndPassTest';
        $firstTestClass2 = 'Stagehand_TestRunner_PHPUnitErrorAndPassTest';
        $secondTestClass1 = 'Stagehand_TestRunner_PHPUnitPassTest';
        $secondTestClass2 = $secondTestClass1;
        $failingTestMethod1 = 'isFailure';
        $failingTestMethod2 = 'isError';
        return array(
            array($firstTestClass1, $secondTestClass1, $failingTestMethod1),
            array($firstTestClass2, $secondTestClass2, $failingTestMethod2),
        );
    }

    /**
     * @param string $firstTestClass
     * @param string $secondTestClass
     * @test
     * @dataProvider provideDataForNotStopTheTestRunWhenATestCaseIsSkipped
     * @since Method available since Release 2.11.0
     */
    public function notStopTheTestRunWhenATestCaseIsSkipped($firstTestClass, $secondTestClass)
    {
        $collector = $this->createCollector();
        $collector->collectTestCase($firstTestClass);
        $collector->collectTestCase($secondTestClass);
        $runner = $this->createRunner();
        $runner->setStopsOnFailure(true);

        $this->runTests();

        $this->assertTestCaseCount(5);
    }

    /**
     * @return array
     * @since Method available since Release 2.16.0
     */
    public function provideDataForNotStopTheTestRunWhenATestCaseIsSkipped()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitSkippedTest', 'Stagehand_TestRunner_PHPUnitPassTest'),
        );
    }

    /**
     * @param string $firstTestClass
     * @param string $secondTestClass
     * @test
     * @dataProvider provideDataForNotStopTheTestRunWhenATestCaseIsIncomplete
     * @since Method available since Release 2.11.0
     */
    public function notStopTheTestRunWhenATestCaseIsIncomplete($firstTestClass, $secondTestClass)
    {
        $collector = $this->createCollector();
        $collector->collectTestCase($firstTestClass);
        $collector->collectTestCase($secondTestClass);
        $runner = $this->createRunner();
        $runner->setStopsOnFailure(true);

        $this->runTests();

        $this->assertTestCaseCount(5);
    }

    /**
     * @return array
     * @since Method available since Release 2.16.0
     */
    public function provideDataForNotStopTheTestRunWhenATestCaseIsIncomplete()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitIncompleteTest', 'Stagehand_TestRunner_PHPUnitPassTest'),
        );
    }

    /**
     * @param $testClass
     * @param $testDoxClass
     * @test
     * @dataProvider provideDataForNotBreakTestDoxOutputIfTheSameTestMethodNamesExceptTrailingNumbers
     * @since Method available since Release 2.11.2
     */
    public function notBreakTestDoxOutputIfTheSameTestMethodNamesExceptTrailingNumbers($testClass, $testDoxClass)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setClasses(array($testClass));
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);

        $this->runTests();

        $this->assertRegExp('/^' . $testDoxClass . '\n \[x\] Pass 1\n \[x\] Pass 2$/m', $this->output, $this->output);
    }

    /**
     * @return array
     * @since Method available since Release 2.16.0
     */
    public function provideDataForNotBreakTestDoxOutputIfTheSameTestMethodNamesExceptTrailingNumbers()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitMultipleClasses1Test', 'Stagehand_TestRunner_PHPUnitMultipleClasses1'),
        );
    }

    public function dataForNotify()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitPassTest', static::$RESULT_PASSED, static::$COLORS, 'OK (3 tests, 4 assertions)'),
            array('Stagehand_TestRunner_PHPUnitPassTest', static::$RESULT_PASSED, static::$NOT_COLOR, 'OK (3 tests, 4 assertions)'),
            array('Stagehand_TestRunner_PHPUnitFailureTest', static::$RESULT_NOT_PASSED, static::$COLORS, 'FAILURES! Tests: 1, Assertions: 1, Failures: 1.'),
            array('Stagehand_TestRunner_PHPUnitFailureTest', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, 'FAILURES! Tests: 1, Assertions: 1, Failures: 1.'),
            array('Stagehand_TestRunner_PHPUnitIncompleteTest', static::$RESULT_NOT_PASSED, static::$COLORS, 'OK, but incomplete or skipped tests! Tests: 2, Assertions: 0, Incomplete: 2.'),
            array('Stagehand_TestRunner_PHPUnitIncompleteTest', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, 'OK, but incomplete or skipped tests! Tests: 2, Assertions: 0, Incomplete: 2.'),
        );
    }

    /**
     * @param string $testClass
     * @test
     * @dataProvider provideDataForConfiguresPhpUnitRuntimeEnvironmentByTheXmlConfigurationFile
     * @link http://redmine.piece-framework.com/issues/202
     * @since Method available since Release 2.14.0
     */
    public function configuresPhpUnitRuntimeEnvironmentByTheXmlConfigurationFile($testClass)
    {
        $marker = 'STAGEHAND_TESTRUNNER_RUNNER_' . strtoupper($this->getPluginID()) . 'RUNNERTEST_bootstrapLoaded';
        $GLOBALS[$marker] = false;
        $reflectionClass = new \ReflectionClass($this);
        $configDirectory = dirname($reflectionClass->getFileName()) . DIRECTORY_SEPARATOR . basename($reflectionClass->getFileName(), '.php');
        $oldWorkingDirectory = getcwd();
        chdir($configDirectory);
        $logFile = $configDirectory . DIRECTORY_SEPARATOR . 'logfile.tap';
        $oldIncludePath = set_include_path($configDirectory . PATH_SEPARATOR . get_include_path());

        $phpunitXMLConfigurationFactory = $this->createPHPUnitXMLConfigurationFactory();
        $phpunitXMLConfiguration = $phpunitXMLConfigurationFactory->maybeCreate($configDirectory . DIRECTORY_SEPARATOR . 'phpunit.xml');
        $this->applicationContext->setComponent('phpunit.phpunit_xml_configuration', $phpunitXMLConfiguration);

        $preparer = $this->createPreparer(); /* @var $preparer \Stagehand\TestRunner\Preparer\PHPUnitPreparer */
        $preparer->setPHPUnitXMLConfiguration($phpunitXMLConfiguration);

        $runner = $this->createRunner(); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
        $runner->setPHPUnitXMLConfiguration($phpunitXMLConfiguration);

        $e = null;
        try {
            $collector = $this->createCollector();
            $collector->collectTestCase($testClass);

            $this->runTests();

            $this->assertTrue($GLOBALS[$marker]);
            $this->assertFileExists($logFile);

            $expectedLog = 'TAP version 13' . PHP_EOL .
'ok 1 - ' . $testClass . '::passWithAnAssertion' . PHP_EOL .
'ok 2 - ' . $testClass . '::passWithMultipleAssertions' . PHP_EOL .
'ok 3 - ' . $testClass . '::日本語を使用できる' . PHP_EOL .
'1..3' . PHP_EOL;
            $actualLog = file_get_contents($logFile);
            $this->assertEquals($expectedLog, $actualLog, $actualLog);
        } catch (Exception $e) {
        }

        unlink($logFile);
        set_include_path($oldIncludePath);
        chdir($oldWorkingDirectory);
        if (!is_null($e)) {
            throw $e;
        }
    }

    /**
     * @retuan array
     * @since Method available since Release 2.16.0
     */
    public function provideDataForConfiguresPhpUnitRuntimeEnvironmentByTheXmlConfigurationFile()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitPassTest'),
        );
    }

    /**
     * @since Method available since Release 2.16.0
     */
    public function dataForMultipleFailures()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitMultipleFailuresTest', 'isFailure'),
        );
    }

    /**
     * @param string $testClass
     * @param array $testMethods
     * @param string $xmlConfigurationFile
     * @test
     * @dataProvider provideDataForGroupsTest
     * @link http://redmine.piece-framework.com/issues/288
     * @since Method available since Release 2.17.0
     */
    public function notCountTheExcludedTestsByTheGroupsElementAsTheDivisorOfATestRun($testClass, array $testMethods, $xmlConfigurationFile)
    {
        $reflectionClass = new \ReflectionClass($this);
        $configDirectory = dirname($reflectionClass->getFileName()) . DIRECTORY_SEPARATOR . basename($reflectionClass->getFileName(), '.php');
        $phpunitXMLConfigurationFactory = $this->createPHPUnitXMLConfigurationFactory();
        $phpunitXMLConfiguration = $phpunitXMLConfigurationFactory->maybeCreate($configDirectory . DIRECTORY_SEPARATOR . $xmlConfigurationFile);
        $this->applicationContext->setComponent('phpunit.phpunit_xml_configuration', $phpunitXMLConfiguration);
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);

        $this->runTests();

        $this->assertTestCaseCount(count($testMethods));
        foreach ($testMethods as $testMethod) {
            $this->assertTestCaseExists($testMethod, $testClass);
        }
        $this->assertCollectedTestCaseCount(count($testMethods));
    }

    /**
     * @return array
     * @since Method available since Release 2.17.0
     */
    public function provideDataForGroupsTest()
    {
        return array(
            array($this->groupsTest(), array('a'), 'groups_include.xml'),
            array($this->groupsTest(), array('b', 'c'), 'groups_exclude.xml'),
            array($this->groupsTest(), array(), 'groups_include_exclude.xml'),
        );
    }

    /**
     * @return string
     * @since Method available since Release 2.17.0
     */
    protected function groupsTest()
    {
        return 'Stagehand_TestRunner_PHPUnitGroupsTest';
    }

    /**
     * @return \Stagehand\TestRunner\Core\PHPUnitXMLConfigurationFactory
     * @since Method available since Release 3.0.0
     */
    protected function createPHPUnitXMLConfigurationFactory()
    {
        return $this->applicationContext->createComponent('phpunit.phpunit_xml_configuration_factory');
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
