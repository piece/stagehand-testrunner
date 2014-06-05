<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2009-2013 KUBO Atsuhiro <kubo@iteman.jp>,
 *               2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>,
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
 * @copyright  2009-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

namespace Stagehand\TestRunner\Runner;

use Stagehand\TestRunner\Test\ComponentAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
abstract class TestCase extends ComponentAwareTestCase
{
    protected $tmpDirectory;
    protected $output;
    protected $backupGlobalsBlacklist =
        array(
            '_CONSOLE_COLOR_CODES',
            '_ENV',
            '_POST',
            '_GET',
            '_COOKIE',
            '_SERVER',
            '_FILES',
            '_REQUEST'
        );

    /**
     * @var string
     * @since Property available since Release 3.0.0
     */
    protected $junitXMLFile;

    protected function setUp()
    {
        parent::setUp();

        $this->tmpDirectory = __DIR__;
        $this->junitXMLFile = $this->tmpDirectory . '/' . $this->getName(false) . '.xml';

        $legacyProxy = \Phake::partialMock('Stagehand\TestRunner\Util\LegacyProxy');
        \Phake::when($legacyProxy)->ob_get_level()->thenReturn(0);
        \Phake::when($legacyProxy)->system($this->anything(), $this->anything())->thenReturn(null);
        $this->setComponent('legacy_proxy', $legacyProxy);

        $this->configure();
    }

    protected function tearDown()
    {
        parent::tearDown();
        if (file_exists($this->junitXMLFile)) {
            unlink($this->junitXMLFile);
        }
    }

    protected function assertTestCaseCount($count)
    {
        $testcases = $this->createXPath()->query('//testcase');
        $this->assertEquals($count, $testcases->length);
    }

    /**
     * @param integer $count
     * @since Method available since Release 2.17.0
     */
    protected function assertCollectedTestCaseCount($count)
    {
        $testsuites = $this->createXPath()->query('/testsuites/testsuite');
        $this->assertEquals(1, $testsuites->length);
        $testsuite = $testsuites->item(0);
        $this->assertTrue($testsuite->hasAttribute('tests'));
        $this->assertEquals($count, $testsuite->getAttribute('tests'));
    }

    /**
     * @param integer $count
     * @param string  $method
     * @param string  $class
     * @since Method available since Release 2.14.0
     */
    protected function assertTestCaseAssertionCount($count, $method, $class)
    {
        $testcases = $this->createXPath()
                          ->query("//testcase[@name='$method'][@class='$class']");
        $this->assertEquals(1, $testcases->length);
        $testcase = $testcases->item(0);
        $this->assertTrue($testcase->hasAttribute('assertions'));
        $this->assertEquals($count, $testcase->getAttribute('assertions'));
    }

    protected function assertTestCaseExists($method, $class)
    {
        $testcases = $this->createXPath()
                          ->query("//testcase[@name='$method'][@class='$class']");
        $this->assertEquals(
            1,
            $testcases->length,
            'The test case [ ' . $class . '::' . $method . ' ] is not found in the result report.'
        );
    }

    /**
     * @param string $method
     * @param string $class
     * @since Method available since Release 2.16.0
     */
    protected function assertTestCasePassed($method, $class)
    {
        $this->assertTestCaseExists($method, $class);

        $failures = $this->createXPath()
                         ->query("//testcase[@name='$method'][@class='$class']/failure");
        if ($failures->length) {
            $this->fail($failures->item(0)->nodeValue);
        }

        $errors = $this->createXPath()
                         ->query("//testcase[@name='$method'][@class='$class']/error");
        if ($errors->length) {
            $this->fail($errors->item(0)->nodeValue);
        }
    }

    /**
     * @param string $method
     * @param string $class
     * @since Method available since Release 2.14.0
     */
    protected function assertTestCaseFailed($method, $class)
    {
        $failures = $this->createXPath()
                         ->query("//testcase[@name='$method'][@class='$class']/failure");
        $this->assertEquals(1, $failures->length);
    }

    /**
     * @param string $pattern
     * @param string $method
     * @param string $class
     * @since Method available since Release 2.14.0
     */
    protected function assertTestCaseFailureMessageEquals($pattern, $method, $class)
    {
        $failures = $this->createXPath()
                         ->query("//testcase[@name='$method'][@class='$class']/failure");
        $this->assertEquals(1, $failures->length);
        $this->assertRegExp($pattern, $failures->item(0)->nodeValue, $failures->item(0)->nodeValue);
    }

    protected function createXPath()
    {
        $junitXML = new \DOMDocument();
        $junitXML->load($this->junitXMLFile);
        return new \DOMXPath($junitXML);
    }

    protected function runTests()
    {
        $runner = $this->createRunner();
        $runner->setJUnitXMLFile($this->junitXMLFile);

        $testCaseClass = new \ReflectionClass('PHPUnit_Framework_TestCase');
        $phpunitOutputBuffering = $testCaseClass->hasMethod('startOutputBuffering');

        if ($phpunitOutputBuffering) {
            $startOutputBufferingMethod = $testCaseClass->getMethod('startOutputBuffering');
            $startOutputBufferingMethod->setAccessible(true);
            $stopOutputBufferingMethod = $testCaseClass->getMethod('stopOutputBuffering');
            $stopOutputBufferingMethod->setAccessible(true);

            $startOutputBufferingMethod->invoke($this);
        } else {
            ob_start();
        }

        $this->createComponent('test_runner')->run();

        $this->output = $phpunitOutputBuffering ? $this->getActualOutput() : ob_get_contents();

        if ($phpunitOutputBuffering) {
            $stopOutputBufferingMethod->invoke($this);

            $startOutputBufferingMethod->setAccessible(false);
            $stopOutputBufferingMethod->setAccessible(false);
        } else {
            ob_end_clean();
        }
    }

    /**
     * @since Method available since Release 2.14.1
     */
    protected function configure()
    {
    }

    /**
     * @return \Stagehand\TestRunner\CLI\Terminal
     * @since Method available since Release 3.0.0
     */
    protected function createTerminal()
    {
        return $this->createComponent('terminal');
    }

    /**
     * @return string
     * @since Method available since Release 3.0.0
     */
    protected function getTestMethodName($testMethodName)
    {
        return $testMethodName;
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
