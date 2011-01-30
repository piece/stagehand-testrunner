<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_PHPUnitRunnerTest extends Stagehand_TestRunner_TestCase
{
    protected $framework = Stagehand_TestRunner_Framework::PHPUNIT;

    /**
     * @since Method available since Release 2.16.0
     */
    protected function loadClasses()
    {
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            class_exists('\Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespaceTest');
        }
    }

    /**
     * @param string $method
     * @test
     * @dataProvider provideMethods
     */
    public function runsOnlyTheSpecifiedMethods($method)
    {
        $this->config->addTestingMethod($method);
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
        $this->config->addTestingMethod($method);
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
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesWithNamespaces
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodNameWithNamespaces($method)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Your PHP version is less than 5.3.0.');
        }

        $this->config->addTestingMethod($method);
        $this->collector->collectTestCase('Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test');
        $this->collector->collectTestCase('Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace2Test');
        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'pass1',
            'Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test'
        );
    }

    /**
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function provideFullyQualifiedMethodNamesWithNamespaces()
    {
        return array(
                   array('\Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test::pass1'),
                   array('\STAGEHAND\TESTRUNNER\PHPUNITMULTIPLECLASSESWITHNAMESPACE1TEST::PASS1'),
                   array('Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test::pass1')
               );
    }

    /**
     * @param $string $class
     * @test
     * @dataProvider provideClasses
     */
    public function runsOnlyTheSpecifiedClasses($class)
    {
        $this->config->addTestingClass($class);
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
     * @param $string $class
     * @test
     * @dataProvider provideClassesWithNamespaces
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function runsOnlyTheSpecifiedClassesWithNamespaces($class)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Your PHP version is less than 5.3.0.');
        }

        $this->config->addTestingClass($class);
        $this->collector->collectTestCase('Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test');
        $this->collector->collectTestCase('Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace2Test');
        $this->runTests();
        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'pass1',
            'Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test'
        );
        $this->assertTestCaseExists(
            'pass2',
            'Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test'
        );
    }

    /**
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function provideClassesWithNamespaces()
    {
        return array(
                   array('\Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test'),
                   array('\stagehand\testrunner\phpunitmultipleclasseswithnamespace1test'),
                   array('Stagehand\TestRunner\PHPUnitMultipleClassesWithNamespace1Test')
               );
    }

    /**
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesForIncompleteAndSkippedTests
     * @since Method available since Release 2.11.0
     */
    public function printsTheSpecifiedMessageForIncompleteAndSkippedTests($method)
    {
        $this->config->printsDetailedProgressReport = true;
        $this->config->addTestingMethod($method);
        preg_match('/^(.*?)::(.*)/', $method, $matches);
        $class = $matches[1];
        $method = $matches[2];
        $this->collector->collectTestCase($class);
        $this->runTests();
        $this->assertRegExp(
            '/^  ' . $method . ' ... .+\s\(.+\)$/m', $this->output
        );
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
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesForIncompleteAndSkippedTestsWithoutMessage
     * @since Method available since Release 2.11.0
     */
    public function printsNormalOutputForIncompleteAndSkippedTestsIfTheMessageIsNotSpecified($method)
    {
        $this->config->printsDetailedProgressReport = true;
        $this->config->addTestingMethod($method);
        preg_match('/^(.*?)::(.*)/', $method, $matches);
        $class = $matches[1];
        $method = $matches[2];
        $this->collector->collectTestCase($class);
        $this->runTests();
        $this->assertRegExp('/^  ' . $method . ' ... [^()]+$/m', $this->output);
        $this->assertRegExp('/^ \[ \] [^()]+$/m', $this->output);
    }

    /**
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
     * @test
     * @since Method available since Release 2.11.0
     */
    public function stopsTheTestRunWhenTheFirstFailureIsRaised()
    {
        $this->config->stopsOnFailure = true;
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitFailureAndPassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'isFailure',
            'Stagehand_TestRunner_PHPUnitFailureAndPassTest'
        );
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function stopsTheTestRunWhenTheFirstErrorIsRaised()
    {
        $this->config->stopsOnFailure = true;
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitErrorAndPassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'isError',
            'Stagehand_TestRunner_PHPUnitErrorAndPassTest'
        );
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function notStopTheTestRunWhenATestCaseIsSkipped()
    {
        $this->config->stopsOnFailure = true;
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitSkippedTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
        $this->runTests();
        $this->assertTestCaseCount(5);
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function notStopTheTestRunWhenATestCaseIsIncomplete()
    {
        $this->config->stopsOnFailure = true;
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitIncompleteTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
        $this->runTests();
        $this->assertTestCaseCount(5);
    }

    /**
     * @test
     * @since Method available since Release 2.11.2
     */
    public function notBreakTestDoxOutputIfTheSameTestMethodNamesExceptTrailingNumbers()
    {
        $this->config->addTestingClass('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $this->runTests();
        $this->assertRegExp(
            '/^Stagehand_TestRunner_PHPUnitMultipleClasses1\n \[x\] Pass 1\n \[x\] Pass 2$/m',
            $this->output
        );
    }

    /**
     * @test
     * @dataProvider provideDataForCreatesANotificationForGrowl
     * @link http://redmine.piece-framework.com/issues/192
     * @since Method available since Release 2.13.0
     */
    public function createsANotificationForGrowlWithColors($testClass, $name, $description)
    {
        require_once 'Console/Color.php';
        $this->config->usesGrowl = true;
        $this->config->colors = true;
        $this->collector->collectTestCase($testClass);
        $this->runTests();
        $notification = $this->runner->getNotification();
        $this->assertEquals($name, $notification->name);
        $this->assertEquals($description, $notification->description);
    }

    /**
     * @test
     * @dataProvider provideDataForCreatesANotificationForGrowl
     * @link http://redmine.piece-framework.com/issues/192
     * @since Method available since Release 2.13.0
     */
    public function createsANotificationForGrowlWithoutColors($testClass, $name, $description)
    {
        require_once 'Console/Color.php';
        $this->config->usesGrowl = true;
        $this->config->colors = false;
        $this->collector->collectTestCase($testClass);
        $this->runTests();
        $notification = $this->runner->getNotification();
        $this->assertEquals($name, $notification->name);
        $this->assertEquals($description, $notification->description);
    }

    public function provideDataForCreatesANotificationForGrowl()
    {
        return array(
                   array('Stagehand_TestRunner_PHPUnitPassTest', 'Green', 'OK (3 tests, 4 assertions)'),
                   array('Stagehand_TestRunner_PHPUnitFailureTest', 'Red', "FAILURES!\nTests: 1, Assertions: 1, Failures: 1."),
                   array('Stagehand_TestRunner_PHPUnitErrorTest', 'Red', "FAILURES!\nTests: 1, Assertions: 0, Errors: 1."),
                   array('Stagehand_TestRunner_PHPUnitIncompleteTest', 'Red', "OK, but incomplete or skipped tests!\nTests: 2, Assertions: 0, Incomplete: 2."),
                   array('Stagehand_TestRunner_PHPUnitSkippedTest', 'Red', "OK, but incomplete or skipped tests!\nTests: 2, Assertions: 0, Skipped: 2.")
               );
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/202
     * @since Method available since Release 2.14.0
     */
    public function configuresPhpUnitRuntimeEnvironmentByTheXmlConfigurationFile()
    {
        $GLOBALS['STAGEHAND_TESTRUNNER_RUNNER_PHPUNITRUNNERTEST_bootstrapLoaded'] = false;
        $configDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR . basename(__FILE__, '.php');
        $oldWorkingDirectory = getcwd();
        chdir($configDirectory);
        $logFile = $configDirectory . DIRECTORY_SEPARATOR . 'logfile.tap';
        $oldIncludePath = set_include_path($configDirectory . PATH_SEPARATOR . get_include_path());
        $this->config->phpunitConfigFile = $configDirectory . DIRECTORY_SEPARATOR . 'phpunit.xml';

        $e = null;
        try {
            $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
            $this->runTests();
            $this->assertTrue($GLOBALS['STAGEHAND_TESTRUNNER_RUNNER_PHPUNITRUNNERTEST_bootstrapLoaded']);
            $this->assertFileExists($logFile);

            $expectedLog = 'TAP version 13' . PHP_EOL .
'ok 1 - Stagehand_TestRunner_PHPUnitPassTest::passWithAnAssertion' . PHP_EOL .
'ok 2 - Stagehand_TestRunner_PHPUnitPassTest::passWithMultipleAssertions' . PHP_EOL .
'ok 3 - Stagehand_TestRunner_PHPUnitPassTest::日本語を使用できる' . PHP_EOL .
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
     * @test
     * @link http://redmine.piece-framework.com/issues/230
     * @since Method available since Release 2.16.0
     */
    public function runsTheFilesWithTheSpecifiedPattern()
    {
        $file = dirname(__FILE__) .
            '/../../../../examples/Stagehand/TestRunner/test_PHPUnitWithAnyPattern.php';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(0);

        $this->config->testFilePattern = '^test_.+\.php$';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('pass', 'Stagehand_TestRunner_PHPUnitWithAnyPatternTest');
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/211
     * @since Method available since Release 2.14.0
     */
    public function runsTheFilesWithTheSpecifiedSuffix()
    {
        $file = dirname(__FILE__) .
            '/../../../../examples/Stagehand/TestRunner/PHPUnitWithAnySuffix_test_.php';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(0);

        $this->config->testFileSuffix = '_test_';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('pass', 'Stagehand_TestRunner_PHPUnitWithAnySuffixTest');
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/219
     * @since Method available since Release 2.14.0
     */
    public function reportsOnlyTheFirstFailureInASingleTestToJunitXml()
    {
        $testClass = 'Stagehand_TestRunner_' . $this->framework . 'MultipleFailuresTest';
        $this->collector->collectTestCase($testClass);
        $this->runTests();

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('isFailure', $testClass);
        $this->assertTestCaseAssertionCount(1, 'isFailure', $testClass);
        $this->assertTestCaseFailed('isFailure', $testClass);
        $this->assertTestCaseFailureMessageEquals('/The First Failure/', 'isFailure', $testClass);
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/237
     * @since Method available since Release 2.16.0
     */
    public function supportsTheSeleniumElementInTheXmlConfigurationFile()
    {
        $GLOBALS['STAGEHAND_TESTRUNNER_PHPUNITSELENIUMTEST_enables'] = true;
        $configDirectory = dirname(__FILE__) . DIRECTORY_SEPARATOR . basename(__FILE__, '.php');
        $this->config->phpunitConfigFile = $configDirectory . DIRECTORY_SEPARATOR . 'selenium.xml';
        $this->preparator->prepare();
        $this->collector->collectTestCase('Stagehand_TestRunner_PHPUnitSeleniumTest');
        $this->runTests();

        $this->assertTestCasePassed(__FUNCTION__, 'Stagehand_TestRunner_PHPUnitSeleniumTest');
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
