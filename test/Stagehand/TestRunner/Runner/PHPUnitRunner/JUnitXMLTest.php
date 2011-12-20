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

namespace Stagehand\TestRunner\Runner\PHPUnitRunner;

use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;
use Stagehand\TestRunner\Runner\TestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class JUnitXMLTest extends TestCase
{
    /**
     * @return string
     * @since Method available since Release 3.0.0
     */
    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormat()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitFailureTest');
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitErrorTest');

        $this->runTests();

        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(5, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(5, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(3, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitPassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(3, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(3, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithAnAssertion', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithAnAssertion');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithMultipleAssertions',
                            $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithMultipleAssertions');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(2, $testcase->getAttribute('assertions'));

        $testcase = $childTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('日本語を使用できる', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('日本語を使用できる');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitFailureTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isFailure', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isFailure');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitFailureTest::isFailure\s+This is an error message\./', $failure->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(2);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitErrorTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isError', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isError');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $error = $testcase->childNodes->item(0);
        $this->assertEquals('Stagehand_LegacyError_PHPError_Exception',
                            $error->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitErrorTest::isError\s+Stagehand_LegacyError_PHPError_Exception:/', $error->nodeValue);
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormatIfNoTestsAreFound()
    {
        $this->runTests();

        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertFalse($parentTestsuite->hasChildNodes());
        $this->assertEquals(0, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(0, $parentTestsuite->childNodes->length);
        $this->assertEquals(sprintf('%F', 0), $parentTestsuite->getAttribute('time'));
    }

    /**
     * @test
     */
    public function treatsDataProvider()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitDataProviderTest');

        $this->runTests();

        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(4, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(4, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $grandChildTestsuite = $childTestsuite->childNodes->item(0);
        $this->assertTrue($grandChildTestsuite->hasChildNodes());
        $this->assertEquals('passWithDataProvider',
                            $grandChildTestsuite->getAttribute('name'));
        $this->assertTrue($grandChildTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $grandChildTestsuite->getAttribute('file'));
        $this->assertEquals(4, $grandChildTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $grandChildTestsuite->getAttribute('assertions'));
        $this->assertEquals(1, $grandChildTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $grandChildTestsuite->getAttribute('errors'));
        $this->assertEquals(4, $grandChildTestsuite->childNodes->length);

        $testcase = $grandChildTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #0', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $testcase = $grandChildTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $testcase = $grandChildTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #2', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));

        $testcase = $grandChildTestsuite->childNodes->item(3);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #3', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitDataProviderTest::passWithDataProvider with data set #3/', $failure->nodeValue);
    }

    /**
     * @test
     */
    public function logsTestResultsInRealtimeIntoTheSpecifiedFileInTheJunitXmlFormat()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitPassTest');
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitFailureTest');
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitErrorTest');
        $this->applicationContext->setComponentClass(
            'phpunit.runner',
            '\Stagehand\TestRunner\Runner\PHPUnitRunner\JUnitXMLTest\MockPHPUnitRunner'
        );
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);

        $this->runTests();

        $this->assertFileExists($this->junitXMLFile);

        $streamContents = $this->readAttribute($this->createRunner()->getJUnitXMLStreamRecorder(), 'streamContents');
        $this->assertEquals(22, count($streamContents));
        $this->assertEquals('<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL . '<testsuites>', $streamContents[0]);
        $this->assertEquals('<testsuite name="The test suite generated by Stagehand_TestRunner" tests="5">', $streamContents[1]);
        $this->assertRegExp('!^<testsuite name="Stagehand_TestRunner_PHPUnitPassTest" tests="3" file=".+>$!', $streamContents[2]);
        $this->assertRegExp('!^<testcase name="passWithAnAssertion" class="Stagehand_TestRunner_PHPUnitPassTest" file=".+" line="\d+">$!', $streamContents[3]);
        $this->assertEquals('</testcase>', $streamContents[4]);
        $this->assertRegExp('!<testcase name="passWithMultipleAssertions" class="Stagehand_TestRunner_PHPUnitPassTest" file=".+">!', $streamContents[5]);
        $this->assertEquals('</testcase>', $streamContents[6]);
        $this->assertRegExp('!<testcase name="日本語を使用できる" class="Stagehand_TestRunner_PHPUnitPassTest" file=".+">!', $streamContents[7]);
        $this->assertEquals('</testcase>', $streamContents[8]);
        $this->assertEquals('</testsuite>', $streamContents[9]);
        $this->assertEquals('</testsuites>', $streamContents[21]);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLStream.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(5, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(3, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitPassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(3, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(3, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithAnAssertion', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithAnAssertion');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithMultipleAssertions',
                            $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithMultipleAssertions');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $childTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('日本語を使用できる', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitPassTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('日本語を使用できる');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitFailureTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isFailure', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitFailureTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isFailure');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitFailureTest::isFailure\s+This is an error message\./', $failure->nodeValue);

        $childTestsuite = $parentTestsuite->childNodes->item(2);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitErrorTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('isError', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitErrorTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('isError');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $error = $testcase->childNodes->item(0);
        $this->assertEquals('Stagehand_LegacyError_PHPError_Exception',
                            $error->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitErrorTest::isError\s+Stagehand_LegacyError_PHPError_Exception:/', $error->nodeValue);
    }

    /**
     * @test
     */
    public function treatsDataProviderInRealtime()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);

        $this->runTests();

        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLStream.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(4, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $childTestsuite->getAttribute('name'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(4, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $grandChildTestsuite = $childTestsuite->childNodes->item(0);
        $this->assertTrue($grandChildTestsuite->hasChildNodes());
        $this->assertEquals('passWithDataProvider',
                            $grandChildTestsuite->getAttribute('name'));
        $class = new \ReflectionClass('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $this->assertEquals($class->getFileName(), $grandChildTestsuite->getAttribute('file'));
        $this->assertEquals(4, $grandChildTestsuite->getAttribute('tests'));
        $this->assertEquals(4, $grandChildTestsuite->childNodes->length);

        $testcase = $grandChildTestsuite->childNodes->item(0);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #0', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $grandChildTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $grandChildTestsuite->childNodes->item(2);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #2', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));

        $testcase = $grandChildTestsuite->childNodes->item(3);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('passWithDataProvider with data set #3', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_PHPUnitDataProviderTest',
                            $testcase->getAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('passWithDataProvider');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $failure = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_ExpectationFailedException',
                            $failure->getAttribute('type'));
        $this->assertRegexp('/^Stagehand_TestRunner_PHPUnitDataProviderTest::passWithDataProvider with data set #3/', $failure->nodeValue);
    }

    /**
     * @test
     */
    public function includesTheSpecifiedMessageAtTheValueOfTheErrorElementForIncompleteAndSkippedTests()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitIncompleteTest');
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitSkippedTest');

        $this->runTests();

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertEquals(
            'Stagehand_TestRunner_PHPUnitIncompleteTest',
            $childTestsuite->getAttribute('name')
        );
        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertEquals('isIncomplete', $testcase->getAttribute('name'));
        $error = $testcase->childNodes->item(0);
        $this->assertEquals(
            'PHPUnit_Framework_IncompleteTestError',
            $error->getAttribute('type')
        );
        $this->assertRegExp(
            '!^Stagehand_TestRunner_PHPUnitIncompleteTest::isIncomplete\s+This test has not been implemented yet\s+!',
            $error->nodeValue
        );
        $childTestsuite = $parentTestsuite->childNodes->item(1);
        $this->assertEquals(
            'Stagehand_TestRunner_PHPUnitSkippedTest',
            $childTestsuite->getAttribute('name')
        );
        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertEquals('isSkipped', $testcase->getAttribute('name'));
        $error = $testcase->childNodes->item(0);
        $this->assertEquals(
            'PHPUnit_Framework_SkippedTestError',
            $error->getAttribute('type')
        );
        $this->assertRegExp(
            '!^Stagehand_TestRunner_PHPUnitSkippedTest::isSkipped\s+Foo is not available\s+!',
            $error->nodeValue
        );
    }

    /**
     * @test
     * @since Method available since Release 2.11.1
     */
    public function countsTheNumberOfTestsForMethodFiltersWithTheRealtimeOption()
    {
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array('pass1'));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);

        $this->runTests();

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);

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
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
        $testTargets = $this->createTestTargets();
        $testTargets->setClasses(array('Stagehand_TestRunner_PHPUnitMultipleClasses1Test'));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses1Test');
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitMultipleClasses2Test');
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);

        $this->runTests();

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertEquals(2, $parentTestsuite->getAttribute('tests'));
        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertEquals(2, $childTestsuite->getAttribute('tests'));
    }

    /**
     * @test
     * @dataProvider provideWritingModes
     * @param boolean $logsResultsInJUnitXMLInRealtime
     * @param string $relaxNGSchema
     * @link http://redmine.piece-framework.com/issues/216
     * @since Method available since Release 2.14.0
     */
    public function generatesAValidXmlIfAnyTestCasesAreSkippedByDependsAnnotations($logsResultsInJUnitXMLInRealtime, $relaxNGSchema)
    {
        $testClass = 'Stagehand_TestRunner_PHPUnitDependsTest';
        class_exists($testClass);
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime($logsResultsInJUnitXMLInRealtime);

        $this->runTests();

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate($relaxNGSchema));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(2, $parentTestsuite->getAttribute('tests'));
        if (!$logsResultsInJUnitXMLInRealtime) {
            $this->assertEquals(1, $parentTestsuite->getAttribute('assertions'));
            $this->assertEquals(1, $parentTestsuite->getAttribute('failures'));
            $this->assertEquals(1, $parentTestsuite->getAttribute('errors'));
        }
        $this->assertEquals(1, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals($testClass, $childTestsuite->getAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new \ReflectionClass($testClass);
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(2, $childTestsuite->getAttribute('tests'));
        if (!$logsResultsInJUnitXMLInRealtime) {
            $this->assertEquals(1, $childTestsuite->getAttribute('assertions'));
            $this->assertEquals(1, $childTestsuite->getAttribute('failures'));
            $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        }
        $this->assertEquals(2, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertEquals('pass', $testcase->getAttribute('name'));
        if (!$logsResultsInJUnitXMLInRealtime) {
            $this->assertEquals(1, $testcase->getAttribute('assertions'));
        }

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('skip', $testcase->getAttribute('name'));
        if (!$logsResultsInJUnitXMLInRealtime) {
            $this->assertEquals(0, $testcase->getAttribute('assertions'));
        }
        $error = $testcase->childNodes->item(0);
        $this->assertEquals('PHPUnit_Framework_SkippedTestError', $error->getAttribute('type'));
        $this->assertRegExp('/^Stagehand_TestRunner_PHPUnitDependsTest::skip\s+This test depends on "Stagehand_TestRunner_PHPUnitDependsTest::pass" to pass./', $error->nodeValue);
    }

    public function provideWritingModes()
    {
        return array(
            array(false, __DIR__ . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'),
            array(true, __DIR__ . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLStream.rng'),
        );
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
        $className = 'Stagehand_TestRunner_PHPUnitExtendedTest';
        $parentClassName = 'Stagehand_TestRunner_PHPUnitCommonTest';
        $collector = $this->createCollector();
        $collector->collectTestCase($className);
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime($logsResultsInJUnitXMLInRealtime);

        $this->runTests();

        $testcases = $this->createXPath()
                          ->query("//testcase[@name='$methodName'][@class='$parentClassName']");
        $this->assertEquals(1, $testcases->length);
        $testcase = $testcases->item(0);
        $this->assertTrue($testcase->hasAttribute('file'));

        $parentClass = new \ReflectionClass($parentClassName);
        $this->assertEquals($parentClass->getFileName(), $testcase->getAttribute('file'));
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

        if (is_null($actualClassName)) {
            $actualClassName = $className;
        }

        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array($methodName));
        $collector = $this->createCollector();
        $collector->collectTestCase($className);
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);

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
        $this->assertRegExp($message, $failure->getAttribute('message'));
    }

    /**
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function provideFailurePatterns()
    {
        return array(
            array('isFailure', 'Stagehand_TestRunner_PHPUnitFailureTest', 56, '/^This is an error message\./m', null, false),
            array('isError', 'Stagehand_TestRunner_PHPUnitErrorTest', 56, '/Undefined property: Stagehand_TestRunner_PHPUnitErrorTest::\$foo/m', null, false),
            array('testTestShouldFailCommon', 'Stagehand_TestRunner_PHPUnitExtendedTest', 61, '/^Failed asserting that (?:false|<boolean:false>) is true\./m', 'Stagehand_TestRunner_PHPUnitCommonTest', false),
            array('isFailure', 'Stagehand_TestRunner_PHPUnitFailureInAnonymousFunctionTest', 56, '/^This is an error message\./m', null, true),
            array('isException', 'Stagehand_TestRunner_PHPUnitExceptionTest', 54, '/This is an error message\./m', null, false),
        );
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function logsTheFileAndLineWhereAFailureOrErrorHasOccurredInRealtimeForWarning()
    {
        $className = 'Stagehand_TestRunner_PHPUnitNoTestsTest';
        $collector = $this->createCollector();
        $collector->collectTestCase($className);
        $junitXMLWriterFactory = $this->applicationContext->createComponent('junit_xml_writer_factory'); /* @var $junitXMLWriterFactory \Stagehand\TestRunner\JUnitXMLWriter\JUnitXMLWriterFactory */
        $junitXMLWriterFactory->setLogsResultsInRealtime(true);

        $this->runTests();

        $failures = $this->createXPath()
                         ->query("//testsuite[@name='$className']/testcase/failure");
        $this->assertEquals(1, $failures->length);
        $failure = $failures->item(0);
        $this->assertTrue($failure->hasAttribute('file'));
        $this->assertTrue($failure->hasAttribute('line'));
        $this->assertTrue($failure->hasAttribute('message'));

        $class = new \ReflectionClass($className);
        $this->assertEquals($class->getFileName(), $failure->getAttribute('file'));
        $this->assertEquals(1, $failure->getAttribute('line'));
        $this->assertRegExp('/No tests found in class "Stagehand_TestRunner_PHPUnitNoTestsTest"\./', $failure->getAttribute('message'));
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
