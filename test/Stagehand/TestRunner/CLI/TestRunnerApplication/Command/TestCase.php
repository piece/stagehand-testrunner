<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\CLI\TestRunnerApplication\Command;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\DependencyInjection\Transformation\Transformation;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
abstract class TestCase extends \Stagehand\TestRunner\Test\TestCase
{
    /**
     * @since Method available since Release 3.5.0
     */
    protected function setUp()
    {
        parent::setUp();

        $containerClass = 'Stagehand\TestRunner\DependencyInjection\\' . $this->getPluginID() . 'Container';
        $this->applicationContext->getComponentFactory()->setContainer(new $containerClass());
    }

    /**
     * @return array
     */
    public function options()
    {
        return array_merge($this->generalOptions(), $this->pluginOptions());
    }

    /**
     * @return array
     */
    protected function generalOptions()
    {
        return array(
            array(
                array('--test-file-pattern=^test_'),
                function (\PHPUnit_Framework_TestCase $test, ApplicationContext $applicationContext, Transformation $transformation) {
                },
                function (\PHPUnit_Framework_TestCase $test, ApplicationContext $applicationContext, Transformation $transformation) {
                    $test->assertEquals('^test_', $applicationContext->createComponent('test_target_repository')->getFilePattern());
                }
            ),
            array(
                array('--notify'),
                function (\PHPUnit_Framework_TestCase $test, ApplicationContext $applicationContext, Transformation $transformation) {
                },
                function (\PHPUnit_Framework_TestCase $test, ApplicationContext $applicationContext, Transformation $transformation) {
                    $test->assertTrue($applicationContext->createComponent('runner')->shouldNotify());
                }
            ),
            array(
                array('--config=example.yml'),
                function (\PHPUnit_Framework_TestCase $test, ApplicationContext $applicationContext, Transformation $transformation) {
                    \Phake::when($transformation)->setConfigurationFile($test->anything())->thenReturn(null);
                },
                function (\PHPUnit_Framework_TestCase $test, ApplicationContext $applicationContext, Transformation $transformation) {
                    \Phake::verify($transformation)->setConfigurationFile($applicationContext->getEnvironment()->getWorkingDirectoryAtStartup() . DIRECTORY_SEPARATOR . 'example.yml');
                }
            ),
        );
    }

    /**
     * @return array
     */
    abstract protected function pluginOptions();

    /**
     * @test
     * @dataProvider options
     */
    public function transformsOptionsToConfiguration(array $options, \Closure $preparer, \Closure $verifier)
    {
        $transformation = \Phake::partialMock(
            'Stagehand\TestRunner\DependencyInjection\Transformation\Transformation',
            $this->applicationContext->getComponentFactory()->getContainer(),
            $this->getPlugin()
        );
        $command = \Phake::partialMock('Stagehand\TestRunner\CLI\TestRunnerApplication\Command\\' . $this->getPluginID() . 'Command');
        \Phake::when($command)->createContainer($this->anything())
            ->thenReturn($this->applicationContext->getComponentFactory()->getContainer());
        \Phake::when($command)->createTransformation($this->anything())
            ->thenReturn($transformation);
        $testRunner = \Phake::mock('Stagehand\TestRunner\Process\TestRunner');
        \Phake::when($testRunner)->run()->thenReturn(null);
        $this->applicationContext->setComponent('test_runner', $testRunner);

        $preparer($this, $this->applicationContext, $transformation);

        $command->run(
            new ArgvInput(array_merge(array('testrunner', strtolower($this->getPluginID())), $options)),
            new ConsoleOutput()
        );

        $verifier($this, $this->applicationContext, $transformation);
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
