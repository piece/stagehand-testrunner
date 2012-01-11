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
class SimpleTestRunnerTest extends TestCase
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
     * @return string
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

    /**
     * @param string $testMethod
     * @test
     * @dataProvider provideMethods
     */
    public function runsOnlyTheSpecifiedMethods($testMethod)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array($testMethod));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test');
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses2Test');

        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test'
        );
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses2Test'
        );
    }

    public function provideMethods()
    {
        return array(array('testPass1'), array('testpass1'));
    }

    /**
     * @param string $testMethod
     * @test
     * @dataProvider provideFullyQualifiedMethodNames
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodName($testMethod)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array($testMethod));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test');
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses2Test');

        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test'
        );
    }

    public function provideFullyQualifiedMethodNames()
    {
        return array(
                   array('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test::testPass1'),
                   array('stagehand_testrunner_' . strtolower($this->getPluginID()) . 'multipleclasses1test::testpass1')
               );
    }

    /**
     * @param string $testMethod
     * @test
     * @dataProvider provideFullyQualifiedMethodNamesWithNamespaces
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodNameWithNamespaces($testMethod)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setMethods(array($testMethod));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test');
        $collector->collectTestCase('Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace2Test');

        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test'
        );
    }

    /**
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function provideFullyQualifiedMethodNamesWithNamespaces()
    {
        return array(
                   array('\Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test::testPass1'),
                   array('\stagehand\testrunner\\' . strtolower($this->getPluginID()) . 'multipleclasseswithnamespace1test::testpass1'),
                   array('Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test::testPass1')
               );
    }

    /**
     * @param $string $testClass
     * @test
     * @dataProvider provideClasses
     */
    public function runsOnlyTheSpecifiedClasses($testClass)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setClasses(array($testClass));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test');
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses2Test');

        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test'
        );
        $this->assertTestCaseExists(
            'testPass2',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test'
        );
    }

    public function provideClasses()
    {
        return array(
                   array('Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleClasses1Test'),
                   array('stagehand_testrunner_' . $this->getPluginID() . 'multipleclasses1test')
               );
    }

    /**
     * @param $string $testClass
     * @test
     * @dataProvider provideClassesWithNamespaces
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function runsOnlyTheSpecifiedClassesWithNamespaces($testClass)
    {
        $testTargets = $this->createTestTargets();
        $testTargets->setClasses(array($testClass));
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test');
        $collector->collectTestCase('Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace2Test');

        $this->runTests();

        $this->assertTestCaseCount(2);
        $this->assertTestCaseExists(
            'testPass1',
            'Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test'
        );
        $this->assertTestCaseExists(
            'testPass2',
            'Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test'
        );
    }

    /**
     * @since Method available since Release 2.15.0
     * @link http://redmine.piece-framework.com/issues/245
     */
    public function provideClassesWithNamespaces()
    {
        return array(
                   array('\Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test'),
                   array('\stagehand\testrunner\\' . $this->getPluginID() . 'multipleclassesWithNamespace1test'),
                   array('Stagehand\TestRunner\\' . $this->getPluginID() . 'MultipleClassesWithNamespace1Test')
               );
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function stopsTheTestRunWhenTheFirstFailureIsRaised()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'FailureAndPassTest');
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'PassTest');
        $runner = $this->createRunner();
        $runner->setStopsOnFailure(true);

        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testIsFailure',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'FailureAndPassTest'
        );
    }

    /**
     * @test
     * @since Method available since Release 2.11.0
     */
    public function stopsTheTestRunWhenTheFirstErrorIsRaised()
    {
        $collector = $this->createCollector();
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'ErrorAndPassTest');
        $collector->collectTestCase('Stagehand_TestRunner_' . $this->getPluginID() . 'PassTest');
        $runner = $this->createRunner();
        $runner->setStopsOnFailure(true);

        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists(
            'testIsError',
            'Stagehand_TestRunner_' . $this->getPluginID() . 'ErrorAndPassTest'
        );
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/230
     * @since Method available since Release 2.16.0
     */
    public function runsTheFilesWithTheSpecifiedPattern()
    {
        $file = dirname(__FILE__) .
            '/../../../../examples/Stagehand/TestRunner/test_SimpleTestWithAnyPattern.php';
        $collector = $this->createCollector();
        $collector->collectTestCasesFromFile($file);

        $this->runTests();

        $this->assertTestCaseCount(0);

        $testTargets = $this->createTestTargets();
        $testTargets->setFilePattern('^test_.+\.php$');
        $collector->collectTestCasesFromFile($file);

        $this->runTests();

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testPass', 'Stagehand_TestRunner_SimpleTestWithAnyPatternTest');
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/219
     * @since Method available since Release 2.14.0
     */
    public function reportsOnlyTheFirstFailureInASingleTestToJunitXml()
    {
        $testClass = 'Stagehand_TestRunner_' . $this->getPluginID() . 'MultipleFailuresTest';
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);

        $this->runTests();

        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        $this->assertTrue($junitXML->relaxNGValidate(dirname(__FILE__) . '/../../../../data/pear.piece-framework.com/Stagehand_TestRunner/JUnitXMLDOM.rng'));

        $this->assertTestCaseCount(1);
        $this->assertTestCaseExists('testIsFailure', $testClass);
        $this->assertTestCaseAssertionCount(1, 'testIsFailure', $testClass);
        $this->assertTestCaseFailed('testIsFailure', $testClass);
        $this->assertTestCaseFailureMessageEquals('/^The First Failure/', 'testIsFailure', $testClass);
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
