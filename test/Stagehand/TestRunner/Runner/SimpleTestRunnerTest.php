<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009-2010 KUBO Atsuhiro <kubo@iteman.jp>,
 *               2010 KUMAKURA Yousuke <kumatch@gmail.com>,
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
 * @copyright  2010 KUMAKURA Yousuke <kumatch@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2010 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2010 KUMAKURA Yousuke <kumatch@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_SimpleTestRunnerTest extends Stagehand_TestRunner_TestCase
{
    protected $framework = Stagehand_TestRunner_Framework::SIMPLETEST;
    protected $oldErrorHandler;

    /**
     * @param string $method
     * @test
     * @dataProvider provideMethods
     */
    public function runsOnlyTheSpecifiedMethods($method)
    {
        $this->loadClasses();
        $this->config->addTestingMethod($method);
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses2Test');
        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test'
        );
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->framework . 'MultipleClasses2Test'
        );
    }

    public function provideMethods()
    {
        return array(array('testPass1'), array('testpass1'));
    }

    /**
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNames
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodName($method)
    {
        $this->loadClasses();
        $this->config->addTestingMethod($method);
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses2Test');
        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test'
        );
    }

    public function provideFullyQualifiedMethodNames()
    {
        return array(
                   array('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test::testPass1'),
                   array('stagehand_testrunner_' . strtolower($this->framework) . 'multipleclasses1test::testpass1')
               );
    }

    /**
     * @param string $method
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesWithNamespaces
     * @since Method available since Release 2.15.0
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodNameWithNamespaces($method)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Your PHP version is less than 5.3.0.');
        }

        $this->loadClasses();
        $this->config->addTestingMethod($method);
        $this->collector->collectTestCase('Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test');
        $this->collector->collectTestCase('Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace2Test');
        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test'
        );
    }

    /**
     * @since Method available since Release 2.15.0
     */
    public function provideFullyQualifiedMethodNamesWithNamespaces()
    {
        return array(
                   array('\Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test::testPass1'),
                   array('\stagehand\testrunner\\' . strtolower($this->framework) . 'multipleclasseswithnamespace1test::testpass1'),
                   array('Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test::testPass1')
               );
    }

    /**
     * @param $string $class
     * @test
     * @dataProvider provideClasses
     */
    public function runsOnlyTheSpecifiedClasses($class)
    {
        $this->loadClasses();
        $this->config->addTestingClass($class);
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses2Test');
        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test'
        );
        $this->assertTestCaseExists(
            'testPass2',
            'Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test'
        );
    }

    public function provideClasses()
    {
        return array(
                   array('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test'),
                   array('stagehand_testrunner_' . $this->framework . 'multipleclasses1test')
               );
    }

    /**
     * @param $string $class
     * @test
     * @dataProvider provideClassesWithNamespaces
     * @since Method available since Release 2.15.0
     */
    public function runsOnlyTheSpecifiedClassesWithNamespaces($class)
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Your PHP version is less than 5.3.0.');
        }

        $this->loadClasses();
        $this->config->addTestingClass($class);
        $this->collector->collectTestCase('Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test');
        $this->collector->collectTestCase('Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace2Test');
        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test'
        );
        $this->assertTestCaseExists(
            'testPass2',
            'Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test'
        );
    }

    /**
     * @since Method available since Release 2.15.0
     */
    public function provideClassesWithNamespaces()
    {
        return array(
                   array('\Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test'),
                   array('\stagehand\testrunner\\' . $this->framework . 'multipleclassesWithNamespace1test'),
                   array('Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespace1Test')
               );
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function stopsTheTestRunWhenTheFirstFailureIsRaised()
    {
        $this->loadClasses();
        $this->config->stopsOnFailure = true;
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'FailureAndPassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'PassTest');
        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testIsFailure',
            'Stagehand_TestRunner_' . $this->framework . 'FailureAndPassTest'
        );
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function stopsTheTestRunWhenTheFirstErrorIsRaised()
    {
        $this->loadClasses();
        $this->config->stopsOnFailure = true;
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'ErrorAndPassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'PassTest');
        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testIsError',
            'Stagehand_TestRunner_' . $this->framework . 'ErrorAndPassTest'
        );
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/211
     * @since Method available since Release 2.14.0
     */
    public function runsTheFilesWithTheSpecifiedSuffix()
    {
        $file = dirname(__FILE__) .
            '/../../../../examples/Stagehand/TestRunner/SimpleTestWithAnySuffix_test_.php';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(0);

        $this->config->testFileSuffix = '_test_';
        $this->collector->collectTestCases($file);

        $this->runTests();
        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testPass', 'Stagehand_TestRunner_SimpleTestWithAnySuffixTest');
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/219
     * @since Method available since Release 2.14.0
     */
    public function reportsOnlyTheFirstFailureInASingleTestToJunitXml()
    {
        $this->loadClasses();
        $testClass = 'Stagehand_TestRunner_' . $this->framework . 'MultipleFailuresTest';
        $this->collector->collectTestCase($testClass);
        $this->runTests();

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testIsFailure', $testClass);
        $this->assertTestCaseAssertionCount(1, 'testIsFailure', $testClass);
        $this->assertTestCaseHasFailure('testIsFailure', $testClass);
        $this->assertTestCaseFailureMessageEquals('/^The First Failure/', 'testIsFailure', $testClass);
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/222
     * @since Method available since Release 2.14.0
     */
    public function supportsWebPageTesting()
    {
        if (!fsockopen('www.example.com', 80, $errno, $errstr, 1)) {
            $this->markTestSkipped('Cannot connect to http://www.example.com.');
        }

        $this->loadClasses();
        $testClass = 'Stagehand_TestRunner_' . $this->framework . 'WebPageTest';
        $this->collector->collectTestCase($testClass);
        $this->runTests();

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testIsPass', $testClass);
    }

    protected function loadClasses()
    {
        class_exists('Stagehand_TestRunner_' . $this->framework . 'MultipleClassesTest');
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            class_exists('\Stagehand\TestRunner\\' . $this->framework . 'MultipleClassesWithNamespaceTest');
        }
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
