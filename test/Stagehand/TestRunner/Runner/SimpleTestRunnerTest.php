<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2009-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2010 KUMAKURA Yousuke <kumatch@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

namespace Stagehand\TestRunner\Runner;

use Stagehand\TestRunner\Core\Plugin\SimpleTestPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2010 KUMAKURA Yousuke <kumatch@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class SimpleTestRunnerTest extends CompatibilityTestCase
{
    protected $oldErrorHandler;

    public function handleError()
    {
    }

    protected function configure()
    {
        $preparer = $this->createPreparer();
        $preparer->prepare();

        class_exists('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClassesTest');
        class_exists('\Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespaceTest');
    }

    /**
     * @since Method available since Release 3.0.0
     */
    protected function getPluginID()
    {
        return SimpleTestPlugin::getPluginID();
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

    public function dataForTestMethods()
    {
        $firstTestClass = 'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test';
        $secondTestClass = 'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses2Test';
        $specifyingTestMethod = 'testPass1';
        $runningTestMethod = $specifyingTestMethod;
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestMethod, $runningTestMethod),
        );
    }

    public function dataForTestClasses()
    {
        $firstTestClass = 'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test';
        $secondTestClass = 'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses2Test';
        $specifyingTestClass = $firstTestClass;
        $runningTestMethod1 = 'testPass1';
        $runningTestMethod2 = 'testPass2';
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestClass, $runningTestMethod1, $runningTestMethod2),
        );
    }

    /**
     * @since Method available since Release 2.11.0
     */
    public function dataForStopOnFailure()
    {
        $firstTestClass1 = 'Stagehand_TestRunner_' . $this->getPluginID() . 'FailureAndPassTest';
        $firstTestClass2 = 'Stagehand_TestRunner_' . $this->getPluginID() . 'ErrorAndPassTest';
        $secondTestClass1 = 'Stagehand_TestRunner_' . $this->getPluginID() . 'PassTest';
        $secondTestClass2 = $secondTestClass1;
        $failingTestMethod1 = 'testIsFailure';
        $failingTestMethod2 = 'testIsError';
        return array(
            array($firstTestClass1, $secondTestClass1, $failingTestMethod1),
            array($firstTestClass2, $secondTestClass2, $failingTestMethod2),
        );
    }

    public function dataForNotify()
    {
        return array(
            array('Stagehand_TestRunner_'. $this->getPluginID() . 'PassTest', static::$RESULT_PASSED, static::$COLORS, 'OK Test cases run: 1/1, Passes: 4, Failures: 0, Exceptions: 0 '),
            array('Stagehand_TestRunner_'. $this->getPluginID() . 'PassTest', static::$RESULT_PASSED, static::$NOT_COLOR, 'OK Test cases run: 1/1, Passes: 4, Failures: 0, Exceptions: 0 '),
            array('Stagehand_TestRunner_'. $this->getPluginID() . 'FailureTest', static::$RESULT_NOT_PASSED, static::$COLORS, 'FAILURES!!! Test cases run: 1/1, Passes: 0, Failures: 1, Exceptions: 0 '),
            array('Stagehand_TestRunner_'. $this->getPluginID() . 'FailureTest', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, 'FAILURES!!! Test cases run: 1/1, Passes: 0, Failures: 1, Exceptions: 0 '),
        );
    }

    public function dataForMultipleFailures()
    {
        return array(
            array('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleFailuresTest', 'testIsFailure'),
        );
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
