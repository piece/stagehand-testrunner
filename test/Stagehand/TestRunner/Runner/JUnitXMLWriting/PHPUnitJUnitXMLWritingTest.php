<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2012, 2014 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2012, 2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\Runner\JUnitXMLWriting;

use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012, 2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class PHPUnitJUnitXMLWritingTest extends CompatibilityTestCase
{
    protected function configure()
    {
        class_exists('Stagehand_TestRunner_PHPUnitMultipleClassesTest');
    }

    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    public function dataForJUnitXML()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitPassTest', array('passWithAnAssertion', 'passWithMultipleAssertions', '日本語を使用できる'), self::RESULT_PASS),
            array('Stagehand_TestRunner_PHPUnitFailureTest', array('isFailure'), self::RESULT_FAILURE),
            array('Stagehand_TestRunner_PHPUnitErrorTest', array('isError'), self::RESULT_ERROR),
        );
    }

    public function dataForTestMethods()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitMultipleClasses1Test', 'pass1'),
        );
    }

    public function dataForTestClasses()
    {
        return array(
            array(array('Stagehand_TestRunner_PHPUnitMultipleClasses1Test', 'Stagehand_TestRunner_PHPUnitMultipleClasses2Test'), 'Stagehand_TestRunner_PHPUnitMultipleClasses1Test'),
        );
    }

    public function dataForInheritedTestMethods()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitExtendedTest', 'testTestShouldPassCommon'),
        );
    }

    public function dataForFailuresInInheritedTestMethod()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitExtendedTest', 'testTestShouldFailCommon'),
        );
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/216
     */
    public function generatesTheValidXmlWithSkippedTestsByDependsAnnotations()
    {
        $this->createCollector()->collectTestCase('Stagehand_TestRunner_PHPUnitDependsTest');
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate($this->getSchemaFile(self::LOG_REALTIME_NOT)));
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/261
     */
    public function logsTheFileAndLineWhereTheFailureOrErrorHasBeenOccurredInRealtimeForWarning()
    {
        $testClass = 'Stagehand_TestRunner_PHPUnitNoTestsTest';
        $this->createCollector()->collectTestCase($testClass);
        $this->createRunner()->setJUnitXMLRealtime(true);
        $this->runTests();
        $this->assertFileExists($this->junitXMLFile);

        $failures = $this->createXPath()->query(sprintf(
            "//testsuite[@name='%s']/testcase/failure",
            $testClass
        ));
        $this->assertEquals(1, $failures->length);

        $failure = $failures->item(0);
        $this->assertTrue($failure->hasAttribute('file'));
        $this->assertTrue($failure->hasAttribute('line'));
        $this->assertTrue($failure->hasAttribute('message'));

        $testClassReflection = new \ReflectionClass($testClass);
        $this->assertEquals($testClassReflection->getFileName(), $failure->getAttribute('file'));
        $this->assertEquals(1, $failure->getAttribute('line'));
        $this->assertRegExp(sprintf('/No tests found in class "%s"\./', $testClass), $failure->getAttribute('message'));
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
