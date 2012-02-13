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

use PHPSpec\Util\Filter;

use Stagehand\TestRunner\Core\Plugin\PHPSpecPlugin;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class PHPSpecJUnitXMLWritingTest extends CompatibilityTestCase
{
    protected function configure()
    {
        $this->createPreparer()->prepare();

        require_once 'Stagehand/TestRunner/PHPSpecErrorSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecExtendedSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecFailureSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecMultipleClassesSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecPassSpec.php';
    }

    protected function getPluginID()
    {
        return PHPSpecPlugin::getPluginID();
    }

    protected function getTestMethodName($testMethodName)
    {
        return Filter::camelCaseToSpace(substr($testMethodName, 2));
    }

    public function dataForJUnitXML()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecPass', array('itShouldPass', 'itShouldPassWithMultipleExpectations', 'itは日本語を使用できること'), self::RESULT_PASS),
            array('Stagehand\TestRunner\DescribePhpSpecFailure', array('itShouldBeFailure'), self::RESULT_FAILURE),
            array('Stagehand\TestRunner\DescribePhpSpecError', array('itShouldBeError'), self::RESULT_ERROR),
        );
    }

    public function dataForTestMethods()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecMultipleClasses1', 'itShouldPass1'),
        );
    }

    public function dataForTestClasses()
    {
        return array(
            array(array('Stagehand\TestRunner\DescribePhpSpecMultipleClasses1', 'Stagehand\TestRunner\DescribePhpSpecMultipleClasses2'), 'Stagehand\TestRunner\DescribePhpSpecMultipleClasses1'),
        );
    }

    public function dataForInheritedTestMethods()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecExtended', 'itShouldPassCommon'),
        );
    }

    public function dataForFailuresInInheritedTestMethod()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecExtended', 'itShouldFailCommon'),
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
