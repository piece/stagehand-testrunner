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
 * @since      File available since Release 2.20.0
 */

namespace Stagehand\TestRunner\Util;

use Stagehand\TestRunner\Test\PHPUnitComponentAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.20.0
 */
class TestTargetRepositoryTest extends PHPUnitComponentAwareTestCase
{
    const TREAT_AS_TEST = true;
    const NOT_TREAT_AS_TEST = false;

    /**
     * @test
     * @dataProvider dataForTestClasses
     * @param array $classes
     * @param string $targetClass
     * @param boolean $expectedResult
     */
    public function tellsWhetherAClassShouldTreatAsATest($classes, $targetClass, $expectedResult)
    {
        $testTargetRepository = $this->createComponent('test_target_repository');
        $testTargetRepository->setClasses($classes);
        $actualResult = $testTargetRepository->shouldTreatElementAsTest($targetClass);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function dataForTestClasses()
    {
        return array(
            array(array(), 'Foo', self::TREAT_AS_TEST),
            array(array('Foo'), 'Foo', self::TREAT_AS_TEST),
            array(array('FOO'), 'foo', self::TREAT_AS_TEST),
            array(array('foo'), 'FOO', self::TREAT_AS_TEST),
            array(array('\Foo'), 'Foo', self::TREAT_AS_TEST),
            array(array('Foo'), 'Bar', self::NOT_TREAT_AS_TEST),
            array(array('Foo\Bar'), 'Foo\Bar', self::TREAT_AS_TEST),
            array(array('Foo\Bar'), 'Bar', self::NOT_TREAT_AS_TEST),
            array(array('Foo', 'Bar'), 'Foo', self::TREAT_AS_TEST),
            array(array('Foo', 'Bar'), 'Bar', self::TREAT_AS_TEST),
            array(array('Foo', 'Bar'), 'Baz', self::NOT_TREAT_AS_TEST),
        );
    }

    /**
     * @test
     * @dataProvider dataForTestMethods
     * @param array $methods
     * @param string $targetClass
     * @param string $targetMethod
     * @param boolean $expectedResult
     */
    public function tellsWhetherAMethodShouldTreatAsATest($methods, $targetClass, $targetMethod, $expectedResult)
    {
        $testTargetRepository = $this->createComponent('test_target_repository');
        $testTargetRepository->setMethods($methods);
        $actualResult = $testTargetRepository->shouldTreatElementAsTest($targetClass, $targetMethod);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function dataForTestMethods()
    {
        return array(
            array(array(), 'Bar', 'foo', self::TREAT_AS_TEST),
            array(array('foo'), 'Bar', 'foo', self::TREAT_AS_TEST),
            array(array('FOO'), 'Bar', 'foo', self::TREAT_AS_TEST),
            array(array('foo'), 'Bar', 'FOO', self::TREAT_AS_TEST),
            array(array('Bar::foo'), 'Bar', 'foo', self::TREAT_AS_TEST),
            array(array('\Bar::foo'), 'Bar', 'foo', self::TREAT_AS_TEST),
            array(array('Bar::foo'), 'Bar', 'baz', self::NOT_TREAT_AS_TEST),
            array(array('Bar::foo'), 'Baz', 'foo', self::NOT_TREAT_AS_TEST),
            array(array('Foo\Bar::baz'), 'Foo\Bar', 'baz', self::TREAT_AS_TEST),
        );
    }

    /**
     * @test
     * @dataProvider dataForTestFilePatterns
     * @param string $filePattern
     * @param string $targetFile
     * @param boolean $expectedResult
     */
    public function tellsWhetherAFileShouldTreatAsATest($filePattern, $targetFile, $expectedResult)
    {
        $testTargetRepository = $this->createComponent('test_target_repository'); /* @var $testTargetRepository \Stagehand\TestRunner\Core\TestTargetRepository */
        $testTargetRepository->setFilePattern($filePattern);
        $actualResult = $testTargetRepository->shouldTreatFileAsTest($targetFile);
        $this->assertEquals($expectedResult, $actualResult);
    }

    /**
     * @return array
     */
    public function dataForTestFilePatterns()
    {
        return array(
            array('Test\.php$', '/path/to/FooTest.php', self::TREAT_AS_TEST),
            array('Test\.php$', '/path/to/testFoo.php', self::NOT_TREAT_AS_TEST),
            array('^test.+\\.php$', '/path/to/testFoo.php', self::TREAT_AS_TEST),
            array('Test\.php$', 'FooTest.php', self::TREAT_AS_TEST),
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
