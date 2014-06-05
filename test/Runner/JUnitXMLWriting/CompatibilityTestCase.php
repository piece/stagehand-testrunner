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

namespace Stagehand\TestRunner\Runner\JUnitXMLWriting;

use Stagehand\TestRunner\Runner\TestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
abstract class CompatibilityTestCase extends TestCase
{
    const RESULT_PASS = 1;
    const RESULT_FAILURE = 2;
    const RESULT_ERROR = 3;
    const LOG_REALTIME = true;
    const LOG_REALTIME_NOT = false;

    /**
     * @return array
     */
    abstract public function dataForJUnitXML();

    /**
     * @test
     * @dataProvider dataForJUnitXML
     * @param string $testClass
     * @param array $testMethods
     * @param integer $result
     */
    public function logsTestResults($testClass, array $testMethods, $result)
    {
        $this->createCollector()->collectTestCase($testClass);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate($this->getSchemaFile(self::LOG_REALTIME_NOT)));

        $rootTestSuiteNode = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($rootTestSuiteNode->hasChildNodes());
        $this->assertEquals(count($testMethods), $rootTestSuiteNode->getAttribute('tests'));
        $this->assertEquals(1, $rootTestSuiteNode->childNodes->length);

        if ($result == self::RESULT_ERROR) {
            $this->assertEquals(0, $rootTestSuiteNode->getAttribute('assertions'));
        } else {
            $this->assertThat($rootTestSuiteNode->getAttribute('assertions'), $this->greaterThanOrEqual(count($testMethods)));
        }

        if ($result == self::RESULT_FAILURE) {
            $this->assertEquals(count($testMethods), $rootTestSuiteNode->getAttribute('failures'));
        } elseif ($result == self::RESULT_ERROR) {
            $this->assertEquals(count($testMethods), $rootTestSuiteNode->getAttribute('errors'));
        }

        if ($result == self::RESULT_PASS) {
            $this->verifyPass(new \ReflectionClass($testClass), $testMethods, $rootTestSuiteNode->childNodes->item(0), self::LOG_REALTIME_NOT);
        } elseif ($result == self::RESULT_FAILURE) {
            $this->verifyFailure(new \ReflectionClass($testClass), $testMethods, $rootTestSuiteNode->childNodes->item(0), self::LOG_REALTIME_NOT);
        } elseif ($result == self::RESULT_ERROR) {
            $this->verifyError(new \ReflectionClass($testClass), $testMethods, $rootTestSuiteNode->childNodes->item(0), self::LOG_REALTIME_NOT);
        }
    }

    /**
     * @test
     * @dataProvider dataForJUnitXML
     * @param string $testClass
     * @param array $testMethods
     * @param integer $result
     */
    public function logsTestResultsInRealtime($testClass, array $testMethods, $result)
    {
        $this->applicationContext->setComponentClass(
            $this->getPluginID() . '.runner',
            'Stagehand\TestRunner\Runner\JUnitXMLWriting\Streaming' . $this->getPluginID() . 'Runner'
        );
        $this->createRunner()->setJUnitXMLRealtime(true);
        $this->createCollector()->collectTestCase($testClass);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $streamContents = $this->createRunner()->getJUnitXMLStreamRecorder()->getStreamContents();
        $this->assertThat(count($streamContents), $this->greaterThan(2));
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<testsuites>', $streamContents[0]);
        $this->assertEquals('</testsuites>', $streamContents[ count($streamContents) - 1 ]);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate($this->getSchemaFile(self::LOG_REALTIME)));

        $rootTestSuiteNode = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($rootTestSuiteNode->hasChildNodes());
        $this->assertEquals(count($testMethods), $rootTestSuiteNode->getAttribute('tests'));
        $this->assertFalse($rootTestSuiteNode->hasAttribute('assertions'));
        $this->assertFalse($rootTestSuiteNode->hasAttribute('failures'));
        $this->assertFalse($rootTestSuiteNode->hasAttribute('errors'));
        $this->assertEquals(1, $rootTestSuiteNode->childNodes->length);

        if ($result == self::RESULT_PASS) {
            $this->verifyPass(new \ReflectionClass($testClass), $testMethods, $rootTestSuiteNode->childNodes->item(0), self::LOG_REALTIME);
        } elseif ($result == self::RESULT_FAILURE) {
            $this->verifyFailure(new \ReflectionClass($testClass), $testMethods, $rootTestSuiteNode->childNodes->item(0), self::LOG_REALTIME);
        } elseif ($result == self::RESULT_ERROR) {
            $this->verifyError(new \ReflectionClass($testClass), $testMethods, $rootTestSuiteNode->childNodes->item(0), self::LOG_REALTIME);
        }
    }

    /**
     * @test
     */
    public function logsTestResultsWhenNoTestsAreFound()
    {
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate($this->getSchemaFile(self::LOG_REALTIME_NOT)));

        $rootTestSuiteNode = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertFalse($rootTestSuiteNode->hasChildNodes());
        $this->assertEquals(0, $rootTestSuiteNode->getAttribute('tests'));
        $this->assertEquals(0, $rootTestSuiteNode->getAttribute('assertions'));
        $this->assertEquals(0, $rootTestSuiteNode->getAttribute('failures'));
        $this->assertEquals(0, $rootTestSuiteNode->getAttribute('errors'));
        $this->assertTrue($rootTestSuiteNode->hasAttribute('time'));
    }

    /**
     * @return array
     */
    abstract public function dataForTestMethods();

    /**
     * @test
     * @dataProvider dataForTestMethods
     * @param string $testClass
     * @param string $testMethod
     */
    public function countsTheNumberOfTestsWithTestMethodsInRealtime($testClass, $testMethod)
    {
        $this->createTestTargetRepository()->setMethods(array($testMethod));
        $this->createCollector()->collectTestCase($testClass);
        $this->createRunner()->setJUnitXMLRealtime(true);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);

        $rootTestSuiteNode = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertEquals(1, $rootTestSuiteNode->getAttribute('tests'));
        $testSuiteNode = $rootTestSuiteNode->childNodes->item(0);
        $this->assertEquals(1, $testSuiteNode->getAttribute('tests'));
    }

    /**
     * @return array
     */
    abstract public function dataForTestClasses();

    /**
     * @test
     * @dataProvider dataForTestClasses
     * @param array $collectingTestClasses
     * @param string $testClass
     */
    public function countsTheNumberOfTestsWithTestClassesInRealtime(array $collectingTestClasses, $testClass)
    {
        $this->createTestTargetRepository()->setClasses(array($testClass));
        $this->createCollector()->collectTestCase($testClass);
        $this->createRunner()->setJUnitXMLRealtime(true);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);

        $rootTestSuiteNode = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertEquals(2, $rootTestSuiteNode->getAttribute('tests'));
        $testSuiteNode = $rootTestSuiteNode->childNodes->item(0);
        $this->assertEquals(2, $testSuiteNode->getAttribute('tests'));
    }

    /**
     * @return array
     */
    abstract public function dataForInheritedTestMethods();

    /**
     * @test
     * @dataProvider dataForInheritedTestMethods
     * @param string $testClass
     * @param string $testMethod
     * @link http://redmine.piece-framework.com/issues/261
     */
    public function logsTheClassAndFileWhereTheTestCaseHasBeenDeclared($testClass, $testMethod)
    {
        $this->createCollector()->collectTestCase($testClass);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $testClassReflection = new \ReflectionClass($testClass);
        $testCases = $this->createXPath()->query(sprintf(
            "//testcase[@name='%s'][@class='%s']",
            $this->getTestMethodName($testMethod),
            $testClassReflection->getParentClass()->getName()
        ));
        $this->assertEquals(1, $testCases->length);

        $testCase = $testCases->item(0);
        $this->assertTrue($testCase->hasAttribute('file'));
        $this->assertEquals($testClassReflection->getParentClass()->getFileName(), $testCase->getAttribute('file'));
    }

    /**
     * @return array
     */
    abstract public function dataForFailuresInInheritedTestMethod();

    /**
     * @test
     * @dataProvider dataForFailuresInInheritedTestMethod
     * @param string  $testClass
     * @param string  $testMethod
     * @link http://redmine.piece-framework.com/issues/261
     */
    public function logsTheFileAndLineWhereTheFailureOrErrorHasBeenOccurredInRealtime($testClass, $testMethod)
    {
        $this->createTestTargetRepository()->setMethods(array($testMethod));
        $this->createCollector()->collectTestCase($testClass);
        $this->createRunner()->setJUnitXMLRealtime(true);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $testClassReflection = new \ReflectionClass($testClass);
        $failures = $this->createXPath()->query(sprintf(
            "//testcase[@name='%s'][@class='%s']/failure",
            $this->getTestMethodName($testMethod),
            $testClassReflection->getParentClass()->getName()
        ));
        $this->assertEquals(1, $failures->length);

        $failure = $failures->item(0);
        $this->assertTrue($failure->hasAttribute('file'));
        $this->assertTrue($failure->hasAttribute('line'));

        $this->assertEquals($testClassReflection->getParentClass()->getFileName(), $failure->getAttribute('file'));
        $this->assertTrue($testClassReflection->getParentClass()->hasMethod($testMethod));
        $this->assertThat($failure->getAttribute('line'), $this->greaterThan($testClassReflection->getParentClass()->getMethod($testMethod)->getStartLine()));
        $this->assertThat($failure->getAttribute('line'), $this->lessThan($testClassReflection->getParentClass()->getMethod($testMethod)->getEndLine()));
    }

    /**
     * @param string $testClass
     * @param array $testMethods
     * @param \DOMNode $testSuite
     * @param boolean $inRealtime
     */
    protected function verifyPass(\ReflectionClass $testClass, array $testMethods, \DOMNode $testSuite, $inRealtime)
    {
        $this->verifyTestSuite($testClass, $testMethods, $testSuite, $inRealtime, self::RESULT_PASS);
    }

    /**
     * @param string $testClass
     * @param array $testMethods
     * @param \DOMNode $testSuite
     * @param boolean $inRealtime
     */
    protected function verifyFailure(\ReflectionClass $testClass, array $testMethods, \DOMNode $testSuite, $inRealtime)
    {
        $this->verifyTestSuite($testClass, $testMethods, $testSuite, $inRealtime, self::RESULT_FAILURE);
    }

    /**
     * @param string $testClass
     * @param array $testMethods
     * @param \DOMNode $testSuite
     * @param boolean $inRealtime
     */
    protected function verifyError(\ReflectionClass $testClass, array $testMethods, \DOMNode $testSuite, $inRealtime)
    {
        $this->verifyTestSuite($testClass, $testMethods, $testSuite, $inRealtime, self::RESULT_ERROR);
    }

    /**
     * @param string $testClass
     * @param array $testMethods
     * @param \DOMNode $testSuite
     * @param boolean $inRealtime
     * @param integer $result
     */
    protected function verifyTestSuite(\ReflectionClass $testClass, array $testMethods, \DOMNode $testSuite, $inRealtime, $result)
    {
        $this->assertTrue($testSuite->hasChildNodes());
        $this->assertEquals($testClass->getName(), $testSuite->getAttribute('name'));
        $this->assertTrue($testSuite->hasAttribute('file'));
        $this->assertEquals($testClass->getFileName(), $testSuite->getAttribute('file'));
        $this->assertEquals(count($testMethods), $testSuite->getAttribute('tests'));
        $this->assertEquals(count($testMethods), $testSuite->childNodes->length);
        if ($inRealtime == self::LOG_REALTIME) {
            $this->assertFalse($testSuite->hasAttribute('assertions'));
            $this->assertFalse($testSuite->hasAttribute('failures'));
            $this->assertFalse($testSuite->hasAttribute('errors'));
            $this->verifyExtendedTestSuiteElements($testClass, $testSuite);
        } else {
            if ($result == self::RESULT_PASS) {
                $this->assertThat($testSuite->getAttribute('assertions'), $this->greaterThanOrEqual(count($testMethods)));
                $this->assertEquals(0, $testSuite->getAttribute('failures'));
                $this->assertEquals(0, $testSuite->getAttribute('errors'));
            } elseif ($result == self::RESULT_FAILURE) {
                $this->assertThat($testSuite->getAttribute('assertions'), $this->greaterThanOrEqual(count($testMethods)));
                $this->assertThat($testSuite->getAttribute('failures'), $this->greaterThanOrEqual(count($testMethods)));
                $this->assertEquals(0, $testSuite->getAttribute('errors'));
            } elseif ($result == self::RESULT_ERROR) {
                $this->assertEquals(0, $testSuite->getAttribute('assertions'));
                $this->assertEquals(0, $testSuite->getAttribute('failures'));
                $this->assertThat($testSuite->getAttribute('errors'), $this->greaterThanOrEqual(count($testMethods)));
            }
        }

        foreach ($testMethods as $i => $testMethod) {
            $this->verifyTestCase($testClass, $testClass->getMethod($testMethod), $testSuite->childNodes->item($i), $inRealtime, $result);
        }
    }

    /**
     * @param \ReflectionClass $testClass
     * @param \ReflectionMethod $testMethod
     * @param \DOMNode $testSuite
     * @param boolean $inRealtime
     * @param integer $result
     */
    protected function verifyTestCase(\ReflectionClass $testClass, \ReflectionMethod $testMethod, \DOMNode $testCase, $inRealtime, $result)
    {
        $this->assertEquals($result != self::RESULT_PASS, $testCase->hasChildNodes());
        $this->assertEquals($this->getTestMethodName($testMethod->getName()), $testCase->getAttribute('name'));
        $this->assertEquals($testClass->getName(), $testCase->getAttribute('class'));
        $this->assertEquals($testClass->getFileName(), $testCase->getAttribute('file'));
        $this->assertEquals($testMethod->getStartLine(), $testCase->getAttribute('line'));
        if ($inRealtime == self::LOG_REALTIME) {
            $this->assertFalse($testCase->hasAttribute('assertions'));
            $this->verifyExtendedTestCaseElements($testMethod, $testCase);
        } else {
            if ($result == self::RESULT_PASS || $result == self::RESULT_FAILURE) {
                $this->assertThat($testCase->getAttribute('assertions'), $this->greaterThanOrEqual(1));
            } elseif ($result == self::RESULT_ERROR) {
                $this->assertEquals(0, $testCase->getAttribute('assertions'));
            }
        }

        if ($result == self::RESULT_FAILURE || $result == self::RESULT_ERROR) {
            $failureOrError = $testCase->childNodes->item(0);
            $this->assertThat(strlen($failureOrError->nodeValue), $this->greaterThan(0));

            if ($inRealtime == self::LOG_REALTIME) {
                $this->verifyExtendedFailureElements($testClass, $testMethod, $failureOrError);
            }
        }
    }

    /**
     * @param \ReflectionClass $testClass
     * @param \DOMNode $testSuite
     * @link http://piece-framework.com/issues/415
     * @since Method available since Release 3.1.0
     */
    protected function verifyExtendedTestSuiteElements(\ReflectionClass $testClass, \DOMNode $testSuite)
    {
        $this->assertTrue($testSuite->hasAttribute('class'));
        $this->assertEquals($testClass->getName(), $testSuite->getAttribute('class'));
    }

    /**
     * @param \ReflectionMethod $testMethod
     * @param \DOMNode $testCase
     * @link http://piece-framework.com/issues/415
     * @since Method available since Release 3.1.0
     */
    protected function verifyExtendedTestCaseElements(\ReflectionMethod $testMethod, \DOMNode $testCase)
    {
        $this->assertTrue($testCase->hasAttribute('method'));
        $this->assertEquals($testMethod->getName(), $testCase->getAttribute('method'));
    }

    /**
     * @param \ReflectionClass $testClass
     * @param \ReflectionMethod $testMethod
     * @param \DOMNode $failureOrError
     */
    protected function verifyExtendedFailureElements(\ReflectionClass $testClass, \ReflectionMethod $testMethod, \DOMNode $failureOrError)
    {
        $this->assertTrue($failureOrError->hasAttribute('file'));
        $this->assertTrue($failureOrError->hasAttribute('line'));
        $this->assertTrue($failureOrError->hasAttribute('message'));

        $this->assertEquals($testClass->getFileName(), $failureOrError->getAttribute('file'));
        $this->assertThat($failureOrError->getAttribute('line'), $this->greaterThan($testMethod->getStartLine()));
        $this->assertThat($failureOrError->getAttribute('line'), $this->lessThan($testMethod->getEndLine()));
        $this->assertThat(strlen($failureOrError->getAttribute('message')), $this->greaterThan(0));
    }

    /**
     * @param boolean $inRealtime
     * @return string
     */
    protected function getSchemaFile($inRealtime)
    {
        return __DIR__ .
            '/../../../src/Resources/config/schema/' .
            ($inRealtime ? 'junit-xml-stream-3.1.0.rng' : 'junit-xml-dom-2.10.0.rng');
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
