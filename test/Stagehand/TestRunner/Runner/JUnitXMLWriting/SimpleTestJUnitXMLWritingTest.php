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

use Stagehand\TestRunner\Core\Plugin\SimpleTestPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class SimpleTestJUnitXMLWritingTest extends CompatibilityTestCase
{
    protected function configure()
    {
        $this->createPreparer()->prepare();

        require_once 'Stagehand/TestRunner/SimpleTestMultipleClassesTest.php';
    }

    protected function getPluginID()
    {
        return SimpleTestPlugin::getPluginID();
    }

    public function dataForJUnitXML()
    {
        return array(
            array('Stagehand_TestRunner_SimpleTestPassTest', array('testPassWithAnAssertion', 'testPassWithMultipleAssertions', 'test日本語を使用できる'), self::RESULT_PASS),
            array('Stagehand_TestRunner_SimpleTestFailureTest', array('testIsFailure'), self::RESULT_FAILURE),
            array('Stagehand_TestRunner_SimpleTestErrorTest', array('testIsError'), self::RESULT_ERROR),
        );
    }

    public function dataForTestMethods()
    {
        return array(
            array('Stagehand_TestRunner_SimpleTestMultipleClasses1Test', 'testPass1'),
        );
    }

    public function dataForTestClasses()
    {
        return array(
            array(array('Stagehand_TestRunner_SimpleTestMultipleClasses1Test', 'Stagehand_TestRunner_SimpleTestMultipleClasses2Test'), 'Stagehand_TestRunner_SimpleTestMultipleClasses1Test'),
        );
    }

    public function dataForInheritedTestMethods()
    {
        return array(
            array('Stagehand_TestRunner_SimpleTestExtendedTest', 'testTestShouldPassCommon'),
        );
    }

    public function dataForFailuresInInheritedTestMethod()
    {
        return array(
            array('Stagehand_TestRunner_SimpleTestExtendedTest', 'testTestShouldFailCommon'),
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
