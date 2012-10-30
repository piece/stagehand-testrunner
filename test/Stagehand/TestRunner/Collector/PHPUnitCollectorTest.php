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
 * @since      File available since Release 3.4.0
 */

namespace Stagehand\TestRunner\Runner;

use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;
use Stagehand\TestRunner\Test\ComponentAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.4.0
 */
class PHPUnitCollectorTest extends ComponentAwareTestCase
{
    const SPECIFYING_CLASS = true;
    const SPECIFYING_CLASS_NOT = false;
    const SPECIFYING_METHOD = true;
    const SPECIFYING_METHOD_NOT = false;

    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    /**
     * @param boolean $specifyingClass
     * @param boolean $specifyingMethod
     * @link https://github.com/piece/stagehand-testrunner/issues/12
     *
     * @test
     * @dataProvider bar
     */
    public function collectsTestsWithDataProvider($specifyingClass, $specifyingMethod)
    {
        if ($specifyingClass) {
            $testTargetRepository = $this->createTestTargetRepository();
            $testTargetRepository->setClasses(array('Stagehand_TestRunner_PHPUnitDataProviderTest'));
        }

        if ($specifyingMethod) {
            $testTargetRepository = $this->createTestTargetRepository();
            $testTargetRepository->setMethods(array('Stagehand_TestRunner_PHPUnitDataProviderTest::passWithDataProvider'));
        }

        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_PHPUnitDataProviderTest');
        $testSuite = $this->readAttribute($collector, 'suite'); /* @var $testSuite \PHPUnit_Framework_TestSuite */

        $tests = $testSuite->tests();
        $this->assertThat(count($tests), $this->equalTo(1));
        $this->assertThat($tests[0], $this->isInstanceOf('PHPUnit_Framework_TestSuite'));

        $tests = $tests[0]->tests();
        $this->assertThat(count($tests), $this->equalTo(1));
        $this->assertThat($tests[0], $this->isInstanceOf('PHPUnit_Framework_TestSuite'));
        $this->assertThat($tests[0]->getName(), $this->equalTo('Stagehand_TestRunner_PHPUnitDataProviderTest::passWithDataProvider'));

        $tests = $tests[0]->tests();
        $this->assertThat(count($tests), $this->equalTo(4));
        $this->assertThat($tests[0], $this->isInstanceOf('PHPUnit_Framework_TestCase'));
        $this->assertThat($tests[0]->getName(), $this->equalTo('passWithDataProvider with data set #0'));
    }

    public function bar()
    {
        return array(
            array(self::SPECIFYING_CLASS_NOT, self::SPECIFYING_METHOD_NOT),
            array(self::SPECIFYING_CLASS_NOT, self::SPECIFYING_METHOD),
            array(self::SPECIFYING_CLASS, self::SPECIFYING_METHOD_NOT),
            array(self::SPECIFYING_CLASS, self::SPECIFYING_METHOD),
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
