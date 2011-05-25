<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.14.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.14.0
 */
class Stagehand_TestRunner_Runner_CakeRunner_JUnitXMLTest extends Stagehand_TestRunner_Runner_SimpleTestRunner_JUnitXMLTest
{
    protected $framework = Stagehand_TestRunner_Framework::CAKE;

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormatIfSkipTest()
    {
        $this->loadClasses();
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'SkipTest');
        $this->runTests();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertFalse($parentTestsuite->hasChildNodes());
        $this->assertEquals(0, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(0, $parentTestsuite->childNodes->length);
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormatIfSkipIfTest()
    {
        $this->loadClasses();
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'SkipIfTest');
        $this->runTests();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(1, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipIfTest',
                            $childTestsuite->hasAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'SkipIfTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(1, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(0, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testSkipIf', $testcase->hasAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipIfTest',
                            $testcase->hasAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testSkipIf');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $error = $testcase->childNodes->item(0);
        $this->assertRegexp('/^This is an skip message\./',
                            $error->nodeValue);
    }

    /**
     * @test
     */
    public function logsTestResultsIntoTheSpecifiedFileInTheJunitXmlFormatIfSkipIfWithPassTest()
    {
        $this->loadClasses();
        $this->collector->collectTestCase('Stagehand_TestRunner_' . $this->framework . 'SkipIfWithPassTest');
        $this->runTests();
        $this->assertFileExists($this->config->junitXMLFile);

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $parentTestsuite = $junitXML->childNodes->item(0)->childNodes->item(0);
        $this->assertTrue($parentTestsuite->hasChildNodes());
        $this->assertEquals(2, $parentTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $parentTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $parentTestsuite->getAttribute('errors'));
        $this->assertEquals(1, $parentTestsuite->childNodes->length);

        $childTestsuite = $parentTestsuite->childNodes->item(0);
        $this->assertTrue($childTestsuite->hasChildNodes());
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipIfWithPassTest',
                            $childTestsuite->hasAttribute('name'));
        $this->assertTrue($childTestsuite->hasAttribute('file'));
        $class = new ReflectionClass('Stagehand_TestRunner_' . $this->framework . 'SkipIfWithPassTest');
        $this->assertEquals($class->getFileName(), $childTestsuite->getAttribute('file'));
        $this->assertEquals(2, $childTestsuite->getAttribute('tests'));
        $this->assertEquals(1, $childTestsuite->getAttribute('assertions'));
        $this->assertEquals(0, $childTestsuite->getAttribute('failures'));
        $this->assertEquals(1, $childTestsuite->getAttribute('errors'));
        $this->assertEquals(2, $childTestsuite->childNodes->length);

        $testcase = $childTestsuite->childNodes->item(0);
        $this->assertTrue($testcase->hasChildNodes());
        $this->assertEquals('testSkipIf', $testcase->hasAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipIfWithPassTest',
                            $testcase->hasAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testSkipIf');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(0, $testcase->getAttribute('assertions'));
        $error = $testcase->childNodes->item(0);
        $this->assertRegexp('/^This is an skip message\./',
                            $error->nodeValue);

        $testcase = $childTestsuite->childNodes->item(1);
        $this->assertFalse($testcase->hasChildNodes());
        $this->assertEquals('testSkipIf', $testcase->hasAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_' . $this->framework . 'SkipIfWithPassTest',
                            $testcase->hasAttribute('class'));
        $this->assertEquals($class->getFileName(), $testcase->getAttribute('file'));
        $method = $class->getMethod('testPass');
        $this->assertEquals($method->getStartLine(), $testcase->getAttribute('line'));
        $this->assertEquals(1, $testcase->getAttribute('assertions'));
    }

    protected function loadClasses()
    {
        include_once 'Stagehand/TestRunner/cake_pass.test.php';
        include_once 'Stagehand/TestRunner/cake_failure.test.php';
        include_once 'Stagehand/TestRunner/cake_error.test.php';
        include_once 'Stagehand/TestRunner/cake_multiple_classes.test.php';
        include_once 'Stagehand/TestRunner/cake_common.test.php';
        include_once 'Stagehand/TestRunner/cake_extended.test.php';
        include_once 'Stagehand/TestRunner/cake_failure_in_anonymous_function.test.php';
        include_once 'Stagehand/TestRunner/cake_skip.test.php';
        include_once 'Stagehand/TestRunner/cake_skip_if.test.php';
        include_once 'Stagehand/TestRunner/cake_skip_if_with_pass.test.php';

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            include_once 'Stagehand/TestRunner/cake_multiple_classes_with_namespace.test.php';
        }
    }

    /**
     * @param Stagehand_TestRunner_Config $config
     * @since Method available since Release 2.14.1
     */
    protected function configure(Stagehand_TestRunner_Config $config)
    {
        $config->cakephpAppPath = dirname(__FILE__) . '/../../../../../vendor/cakephp/app';
    }

    /**
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function provideFailurePatterns()
    {
        return array(
            array('testIsFailure', 'Stagehand_TestRunner_CakeFailureTest', 49, 'This is an error message.', null, false),
            array('testIsError', 'Stagehand_TestRunner_CakeErrorTest', 49, 'This is an exception message.', null, false),
            array('testTestShouldFailCommon', 'Stagehand_TestRunner_CakeExtendedTest', 54, '', 'Stagehand_TestRunner_CakeCommonTest', false),
            array('testIsFailure', 'Stagehand_TestRunner_CakeFailureInAnonymousFunctionTest', 49, 'This is an error message.', null, true),
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
