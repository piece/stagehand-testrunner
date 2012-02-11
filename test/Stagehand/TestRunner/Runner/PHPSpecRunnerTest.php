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

namespace Stagehand\TestRunner\Runner;

use PHPSpec\Util\Filter;

use Stagehand\TestRunner\Core\Plugin\PHPSpecPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class PHPSpecRunnerTest extends CompatibilityTestCase
{
    protected function configure()
    {
        $preparer = $this->createPreparer(); /* @var $preparer \Stagehand\TestRunner\Preparer\Preparer */
        $preparer->prepare();

        require_once 'Stagehand/TestRunner/PHPSpecErrorAndPassSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecFailureAndPassSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecFailureSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecMultipleClassesSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecMultipleFailuresSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecPassSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecPendingSpec.php';
    }

    protected function getPluginID()
    {
        return PHPSpecPlugin::getPluginID();
    }

    protected function getTestMethodName($testMethodName)
    {
        return Filter::camelCaseToSpace(substr($testMethodName, 2));
    }

    public function dataForTestMethods()
    {
        $firstTestClass = 'Stagehand\TestRunner\DescribePhpSpecMultipleClasses1';
        $secondTestClass = 'Stagehand\TestRunner\DescribePhpSpecMultipleClasses2';
        $specifyingTestMethod = 'itShouldPass1';
        $runningTestMethod = $specifyingTestMethod;
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestMethod, $runningTestMethod),
        );
    }

    public function dataForTestClasses()
    {
        $firstTestClass = 'Stagehand\TestRunner\DescribePhpSpecMultipleClasses1';
        $secondTestClass = 'Stagehand\TestRunner\DescribePhpSpecMultipleClasses2';
        $specifyingTestClass = $firstTestClass;
        $runningTestMethod1 = 'itShouldPass1';
        $runningTestMethod2 = 'itShouldPass2';
        return array(
            array($firstTestClass, $secondTestClass, $specifyingTestClass, $runningTestMethod1, $runningTestMethod2),
        );
    }

    public function dataForStopOnFailure()
    {
        $firstTestClass1 = 'Stagehand\TestRunner\DescribePhpSpecFailureAndPass';
        $firstTestClass2 = 'Stagehand\TestRunner\DescribePhpSpecErrorAndPass';
        $secondTestClass1 = 'Stagehand\TestRunner\DescribePhpSpecPass';
        $secondTestClass2 = $secondTestClass1;
        $failingTestMethod1 = 'itShouldBeFailure';
        $failingTestMethod2 = 'itShouldBeError';
        return array(
            array($firstTestClass1, $secondTestClass1, $failingTestMethod1),
            array($firstTestClass2, $secondTestClass2, $failingTestMethod2),
        );
    }

    public function dataForNotify()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecPass', static::$RESULT_PASSED, static::$COLORS, '3 examples'),
            array('Stagehand\TestRunner\DescribePhpSpecPass', static::$RESULT_PASSED, static::$NOT_COLOR, '3 examples'),
            array('Stagehand\TestRunner\DescribePhpSpecFailure', static::$RESULT_NOT_PASSED, static::$COLORS, '1 example, 1 failure'),
            array('Stagehand\TestRunner\DescribePhpSpecFailure', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, '1 example, 1 failure'),
            array('Stagehand\TestRunner\DescribePhpSpecPending', static::$RESULT_NOT_PASSED, static::$COLORS, '1 example, 1 pending'),
            array('Stagehand\TestRunner\DescribePhpSpecPending', static::$RESULT_NOT_PASSED, static::$NOT_COLOR, '1 example, 1 pending'),
        );
    }

    public function dataForMultipleFailures()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecMultipleFailures', 'itShouldBeFailure'),
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
