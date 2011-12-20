<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.13.0
 */

namespace Stagehand\TestRunner\CLI;

use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;
use Stagehand\TestRunner\Test\TestCase;
use Stagehand\TestRunner\Test\TestContainerBuilder;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.13.0
 */
class TestRunnerCLIControllerTest extends TestCase
{
    /**
     * @var \Stagehand\TestRunner\Util\OutputBuffering
     */
    protected $outputBuffering;

    /**
     * @return string
     * @since Method available since Release 3.0.0
     */
    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/202
     * @since Method available since Release 2.14.0
     */
    public function supportsPhpunitXmlConfigurationFile()
    {
        $phpunitConfigFile = 'phpunit.xml';
        $_SERVER['argv'] = $GLOBALS['argv'] = array(
            'bin/phpunitrunner',
            '-p', 'tests/prepare.php',
            '--phpunit-config=' . $phpunitConfigFile
        );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);

        $phpunitXMLConfigurationFactory = \Phake::mock('\Stagehand\TestRunner\Core\PHPUnitXMLConfigurationFactory');
        \Phake::when($phpunitXMLConfigurationFactory)->maybeCreate($this->anything())->thenReturn(null);
        $this->applicationContext->setComponent('phpunit.phpunit_xml_configuration_factory', $phpunitXMLConfigurationFactory);

        $this->createTestRunnerCLIController()->run();
        $this->applicationContext->createComponent('phpunit.phpunit_xml_configuration');

        \Phake::verify($phpunitXMLConfigurationFactory)->maybeCreate($this->equalTo($phpunitConfigFile));
    }

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/230
     * @since Method available since Release 2.16.0
     */
    public function supportsTestFilesWithAnyPattern()
    {
        $testFilePattern = '^test_';
        $_SERVER['argv'] = $GLOBALS['argv'] = array(
            'bin/phpunitrunner',
            '-p', 'tests/prepare.php',
            '--test-file-pattern=' . $testFilePattern
        );
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);

        $this->createTestRunnerCLIController()->run();

        $this->assertEquals($testFilePattern, $this->applicationContext->createComponent('test_targets')->getFilePattern());
    }

    /**
     * @test
     * @dataProvider notificationOptions
     * @param string $option
     * @link http://redmine.piece-framework.com/issues/311
     * @since Method available since Release 2.18.0
     */
    public function supportsNotifications($option)
    {
        $_SERVER['argv'] = $GLOBALS['argv'] = array('bin/phpunitrunner', $option);
        $_SERVER['argc'] = $GLOBALS['argc'] = count($_SERVER['argv']);
        $testRunner = \Phake::mock('\Stagehand\TestRunner\CLI\TestRunner');

        $this->createTestRunnerCLIController()->run();

        $this->assertTrue($this->applicationContext->createComponent('runner_factory')->create()->usesNotification());
    }

    /**
     * @return array
     * @since Method available since Release 2.18.0
     */
    public function notificationOptions()
    {
        return array(array('-n'), array('-g'),);
    }

    /**
     * @return Stagehand\TestRunner\CLI\TestRunnerCLIController
     * @since Method available since Release 2.20.0
     */
    protected function createTestRunnerCLIController()
    {
        $testRunner = \Phake::mock('\Stagehand\TestRunner\CLI\TestRunner');
        \Phake::when($testRunner)->run()->thenReturn(null);
        $this->applicationContext->setComponent('test_runner', $testRunner);

        $controller = \Phake::partialMock(
            '\Stagehand\TestRunner\CLI\TestRunnerCLIController',
            $this->getPluginID()
        );
        \Phake::when($controller)->createContainer()->thenReturn($this->applicationContext->getComponentFactory()->getContainer());

        return $controller;
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
