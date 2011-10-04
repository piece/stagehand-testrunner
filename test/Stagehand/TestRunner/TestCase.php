<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
abstract class Stagehand_TestRunner_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Stagehand_TestRunner_Config
     */
    protected $config;
    protected $tmpDirectory;

    /**
     * @var Stagehand_TestRunner_Preparer
     */
    protected $preparer;

    /**
     * @var Stagehand_TestRunner_Collector
     */
    protected $collector;

    /**
     * @var Stagehand_TestRunner_Runner
     */
    protected $runner;

    /**
     * @var Stagehand_TestRunner_Notification_Notifier
     * @since Property available since Release 2.18.0
     */
    protected $notifier;

    protected $framework;
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
     * @since Property available since Release 2.18.0
     */
    protected $phpOS = 'Linux';

    protected function setUp()
    {
        Stagehand_LegacyError_PHPError::enableConversion(error_reporting());
        $this->tmpDirectory = dirname(__FILE__) . '/../../../tmp';
        $this->config = new Stagehand_TestRunner_Config();
        $this->config->framework = $this->framework;
        $this->config->setJUnitXMLFile(
            $this->tmpDirectory .
            '/' .
            get_class($this) .
            '.' .
            $this->getName(false) .
            '.xml'
        );
        $this->configure($this->config);

        $preparerFactory = new Stagehand_TestRunner_Preparer_PreparerFactory($this->config);
        $this->preparer = $preparerFactory->create();
        $this->preparer->prepare();

        $collectorFactory = new Stagehand_TestRunner_Collector_CollectorFactory($this->config);
        $this->collector = $collectorFactory->create();

        $this->notifier = $this->getMock('Stagehand_TestRunner_Notification_Notifier', array('executeNotifyCommand', 'getPHPOS'));
        $this->notifier->expects($this->any())
                    ->method('executeNotifyCommand')
                    ->will($this->returnValue(null));
        $this->notifier->expects($this->any())
                    ->method('getPHPOS')
                    ->will($this->returnCallback(array($this, 'getPHPOS')));

        $this->loadClasses();
    }

    protected function tearDown()
    {
        unlink($this->config->getJUnitXMLFile());
    }

    public function removeJUnitXMLFile($element)
    {
        unlink($element);
    }

    /**
     * @since Method available since Release 2.18.0
     */
    public function getPHPOS()
    {
        return $this->phpOS;
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
        $junitXML = new DOMDocument();
        $junitXML->load($this->config->getJUnitXMLFile());
        return new DOMXPath($junitXML);
    }

    protected function runTests()
    {
        $factory = new Stagehand_TestRunner_Runner_RunnerFactory($this->config);
        $this->runner = $factory->create();
        $testRunner = $this->getMock(
                          'Stagehand_TestRunner_TestRunner',
                          array('createPreparer', 'createCollector', 'createRunner', 'createNotifier'),
                          array($this->config)
                      );
        $testRunner->expects($this->any())
                   ->method('createPreparer')
                   ->will($this->returnValue($this->preparer));
        $testRunner->expects($this->any())
                   ->method('createCollector')
                   ->will($this->returnValue($this->collector));
        $testRunner->expects($this->any())
                   ->method('createRunner')
                   ->will($this->returnValue($this->runner));
        $testRunner->expects($this->any())
                   ->method('createNotifier')
                   ->will($this->returnValue($this->notifier));

        ob_start();
        $testRunner->run();
        $this->output = ob_get_contents();
        ob_end_clean();
    }

    /**
     * @param Stagehand_TestRunner_Config $config
     * @since Method available since Release 2.14.1
     */
    protected function configure(Stagehand_TestRunner_Config $config)
    {
    }

    /**
     * @since Method available since Release 2.16.0
     */
    protected function loadClasses()
    {
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
