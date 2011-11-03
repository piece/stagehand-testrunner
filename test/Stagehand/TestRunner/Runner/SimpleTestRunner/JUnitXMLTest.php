<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
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

namespace Stagehand\TestRunner\Runner\SimpleTestRunner;

use Stagehand\TestRunner\TestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class JUnitXMLTest extends TestCase
{
    protected $framework = \Stagehand_TestRunner_Framework::SIMPLETEST;
    protected $oldErrorHandler;

    public function handleError()
    {
    }

    protected function loadClasses()
    {
        class_exists('Stagehand_TestRunner_' . $this->framework . 'MultipleClassesTest');
    }

    protected function setUp()
    {
        $this->oldErrorHandler = set_error_handler(array($this, 'handleError'));
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (!is_null($this->oldErrorHandler)) {
            set_error_handler($this->oldErrorHandler);
        }
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormat()
    {
        $this->loadClasses();
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'PassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'FailureTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'ErrorTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'SkipClassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'SkipMethodTest');
        $this->runTests();
        $this->assertFileExists($this->config->getJUnitXMLFile());

        $junitXML = new \DOMDocument();
        $junitXML->load($this->config->getJUnitXMLFile());
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(8, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(6, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(3, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(5, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'PassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(3, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(3, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('testPassWithAnAssertion', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testPassWithAnAssertion');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('testPassWithMultipleAssertions',
                            $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testPassWithMultipleAssertions');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(2, $testcase->getAttribute('assertions'));

        $testcase = $childTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('test日本語を使用できる', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('test日本語を使用できる');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'FailureTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'FailureTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testIsFailure', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'FailureTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testIsFailure');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $failure = $testcase->childNodes->item(0);
        $this->assertRegexp('/^This is an error message\./', $failure->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(2);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'ErrorTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'ErrorTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testIsError', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'ErrorTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testIsError');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $error = $testcase->childNodes->item(0);
        $this->assertRegexp('/^Exception: This is an exception message\./',
                            $error->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(3);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipClassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'SkipClassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('skip', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipClassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('skip');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $error = $testcase->childNodes->item(0);
        $this->assertRegexp('/^This is a skip message\./', $error->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(4);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipMethodTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'SkipMethodTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(2, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(2, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('testPass', $testcase->getAttribute('name'));
        $this->assertEquals(
            'Stagehand_TestRunner_' . $this->framework . 'SkipMethodTest',
            $testcase->getAttribute('class')
        );
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testPass');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testIsSkipped', $testcase->getAttribute('name'));
        $this->assertEquals(
            'Stagehand_TestRunner_' . $this->framework . 'SkipMethodTest',
            $testcase->getAttribute('class')
        );
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testIsSkipped');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $error = $testcase->childNodes->item(0);
        $this->assertRegexp('/^This is a skip message\./', $error->nodeValue);
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormatIfNoTestsAreFound()
    {
        $this->loadClasses();
        $this->runTests();
        $this->assertFileExists($this->config->getJUnitXMLFile());

        $junitXML = new \DOMDocument();
        $junitXML->load($this->config->getJUnitXMLFile());
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertFalse($parentTestsuite->hasChildNodes());
        $this->assertEquals(0, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(sprintf('%F', 0), $parentTestsuite->getAttribute('time'));
    }

    /**
     * @test
     */
    public function logsTestResultsInRealtimeIntoTheSpecifiedFileInTheJunitXmlFormat()
    {
        $this->loadClasses();
        $this->config->setLogsResultsInJUnitXMLInRealtime(true);
        $this->config->runnerClass = '\Stagehand\TestRunner\Runner\\' . $this->framework . 'Runner\JUnitXMLTest\Mock' . $this->framework . 'Runner';
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'PassTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'FailureTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'ErrorTest');
        $this->runTests();
        $this->assertFileExists($this->config->getJUnitXMLFile());

        $streamContents = $this->readAttribute($this->runner, 'streamContents');
        $this->assertEquals(22, count($streamContents));
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<testsuites>', $streamContents[0]);
        $this->assertEquals('<testsuite name="The test suite generated by Stagehand_TestRunner" tests="5">', $streamContents[1]);
        $this->assertRegExp('!^<testsuite name="Stagehand_TestRunner_' . $this->framework . 'PassTest" tests="3" file=".+">$!', $streamContents[2]);
        $this->assertRegExp('!^<testcase name="testPassWithAnAssertion" class="Stagehand_TestRunner_' . $this->framework . 'PassTest" file=".+" line="\d+">$!', $streamContents[3]);
        $this->assertEquals('</testcase>', $streamContents[4]);
        $this->assertRegExp('!^<testcase name="testPassWithMultipleAssertions" class="Stagehand_TestRunner_' . $this->framework . 'PassTest" file=".+" line="\d+">$!', $streamContents[5]);
        $this->assertEquals('</testcase>', $streamContents[6]);
        $this->assertRegExp('!^<testcase name="test日本語を使用できる" class="Stagehand_TestRunner_' . $this->framework . 'PassTest" file=".+" line="\d+">$!', $streamContents[7]);
        $this->assertEquals('</testcase>', $streamContents[8]);
        $this->assertEquals('</testsuite>', $streamContents[9]);
        $this->assertEquals('</testsuite>', $streamContents[20]);
        $this->assertEquals('</testsuites>', $streamContents[21]);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->config->getJUnitXMLFile());
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLStream.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(5, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(3, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'PassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(3, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(3, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('testPassWithAnAssertion', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testPassWithAnAssertion');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('testPassWithMultipleAssertions',
                            $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testPassWithMultipleAssertions');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $childTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('test日本語を使用できる', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'PassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('test日本語を使用できる');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'FailureTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'FailureTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testIsFailure', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'FailureTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testIsFailure');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $failure = $testcase->childNodes->item(0);
        $this->assertRegexp('/^This is an error message\./', $failure->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(2);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'ErrorTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'ErrorTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testIsError', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'ErrorTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testIsError');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $error = $testcase->childNodes->item(0);
        $this->assertRegexp('/^Exception: This is an exception message\./',
                            $error->nodeValue);
    }

    /**
     * @test
     * @since Method available since Release 2.11.1
     */
    public function countsTheNumberOfTestsForMethodFiltersWithTheRealtimeOption()
    {
        $this->loadClasses();
        $this->config->setLogsResultsInJUnitXMLInRealtime(true);
        $this->config->addTestingMethod('testPass1');
        class_exists('Stagehand_TestRunner_' . $this->framework . 'MultipleClassesTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test');
        $this->runTests();
        $junitXML = new \DOMDocument();
        $junitXML->load($this->config->getJUnitXMLFile());

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertEquals(1, $parentTestsuite->getAttribute('tests'));
        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
    }

    /**
     * @test
     * @since Method available since Release 2.11.1
     */
    public function countsTheNumberOfTestsForClassFiltersWithTheRealtimeOption()
    {
        $this->loadClasses();
        $this->config->setLogsResultsInJUnitXMLInRealtime(true);
        $this->config->addTestingClass('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test');
        class_exists('Stagehand_TestRunner_' . $this->framework . 'MultipleClassesTest');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses1Test');
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'MultipleClasses2Test');
        $this->runTests();
        $junitXML = new \DOMDocument();
        $junitXML->load($this->config->getJUnitXMLFile());

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertEquals(2, $parentTestsuite->getAttribute('tests'));
        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertEquals(2, $childTestsuite->getAttribute('tests'));
    }

    /**
     * @test
     * @dataProvider provideWritingModes
     * @param boolean $logsResultsInJUnitXMLInRealtime
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function logsTheClassAndFileWhereATestCaseHasBeenDefined($logsResultsInJUnitXMLInRealtime)
    {
        $methodName = 'testTestShouldPassCommon';
        $className = 'Stagehand_TestRunner_' . $this->framework . 'ExtendedTest';
        $parentClassName = 'Stagehand_TestRunner_' . $this->framework . 'CommonTest';
        $this->config->setLogsResultsInJUnitXMLInRealtime($logsResultsInJUnitXMLInRealtime);
        $this->collector->collectTestCase($className);
        $this->runTests();

        $testcases = $this->createXPath()
                          ->query("//testcase[@name='$methodName'][@class='$parentClassName']");
        $this->assertEquals(1, $testcases->length);
        $testcase = $testcases->item(0);
        $this->assertTrue($testcase->hasAttribute('file'));

        $parentClass = new \ReflectionClass($parentClassName);
        $this->assertEquals($parentClass->getFileName(), $testcase->getAttribute('file'));
    }

    public function provideWritingModes()
    {
        return array(array(false), array(true));
    }

    /**
     * @test
     * @dataProvider provideFailurePatterns
     * @param string  $methodName
     * @param string  $className
     * @param integer $line
     * @param string  $message
     * @param string  $actualClassName
     * @param boolean $requiresPHP53
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function logsTheFileAndLineWhereAFailureOrErrorHasOccurredInRealtime($methodName, $className, $line, $message, $actualClassName, $requiresPHP53)
    {
        if ($requiresPHP53 && version_compare(PHP_VERSION, '5.3.0', '<')) {
            $this->markTestSkipped('Your PHP version is less than 5.3.0.');
        }

        $this->loadClasses();

        if (is_null($actualClassName)) {
            $actualClassName = $className;
        }
        $this->config->setLogsResultsInJUnitXMLInRealtime(true);
        $this->config->addTestingMethod($methodName);
        $this->collector->collectTestCase($className);
        $this->runTests();

        $failures = $this->createXPath()
                         ->query("//testcase[@name='$methodName'][@class='$actualClassName']/failure | //testcase[@name='$methodName'][@class='$actualClassName']/error");
        $this->assertEquals(1, $failures->length);
        $failure = $failures->item(0);
        $this->assertTrue($failure->hasAttribute('file'));
        $this->assertTrue($failure->hasAttribute('line'));
        $this->assertTrue($failure->hasAttribute('message'));

        $actualClass = new \ReflectionClass($actualClassName);
        $this->assertEquals($actualClass->getFileName(), $failure->getAttribute('file'));
        $this->assertTrue($actualClass->hasMethod($methodName));
        $this->assertEquals($line, $failure->getAttribute('line'));
        if (strlen($message)) {
            $this->assertRegExp('/' . preg_quote($message, '/') . '/', $failure->getAttribute('message'));
        }
    }

    /**
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function provideFailurePatterns()
    {
        return array(
            array('testIsFailure', 'Stagehand_TestRunner_SimpleTestFailureTest', 51, 'This is an error message.', null, false),
            array('testIsError', 'Stagehand_TestRunner_SimpleTestErrorTest', 51, 'This is an exception message.', null, false),
            array('testTestShouldFailCommon', 'Stagehand_TestRunner_SimpleTestExtendedTest', 59, '', 'Stagehand_TestRunner_SimpleTestCommonTest', false),
            array('testIsFailure', 'Stagehand_TestRunner_SimpleTestFailureInAnonymousFunctionTest', 49, 'This is an error message.', null, true),
        );
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
