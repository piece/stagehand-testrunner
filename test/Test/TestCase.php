<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2014 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\Test;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\Environment;
use Stagehand\TestRunner\Core\Plugin\PluginRepository;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Stagehand\TestRunner\Core\ApplicationContext
     */
    protected $oldApplicationContext;

    /**
     * @var \Stagehand\TestRunner\Test\TestApplicationContext
     */
    protected $applicationContext;

    protected function setUp()
    {
        $this->oldApplicationContext = ApplicationContext::getInstance();

        $this->applicationContext = $this->createApplicationContext();
        $this->applicationContext->setComponent('environment', $this->applicationContext->getEnvironment());
        $this->applicationContext->setComponent('input', new ArgvInput());
        $this->applicationContext->setComponent('plugin', $this->getPlugin());
        $output = new ConsoleOutput();
        $output->setDecorated(false);
        $this->applicationContext->setComponent('output', $output);
        ApplicationContext::setInstance($this->applicationContext);
    }

    protected function tearDown()
    {
        $this->applicationContext->getComponentFactory()->clearComponents();
        ApplicationContext::getInstance()->getEnvironment()->setWorkingDirectoryAtStartup(null);
        ApplicationContext::getInstance()->getEnvironment()->setPreloadScript(null);
        ApplicationContext::setInstance($this->oldApplicationContext);
    }

    /**
     * @return string
     */
    abstract protected function getPluginID();

    /**
     * @return \Stagehand\TestRunner\Core\ApplicationContext
     */
    protected function createApplicationContext()
    {
        $containerClass = 'Stagehand\TestRunner\DependencyInjection\\' . $this->getPluginID() . 'Container';
        $container = new $containerClass();
        $componentFactory = new TestComponentFactory();
        $componentFactory->setContainer($container);
        $applicationContext = new TestApplicationContext();
        $applicationContext->setComponentFactory($componentFactory);
        $applicationContext->setEnvironment(new Environment());

        return $applicationContext;
    }

    /**
     * @return \Stagehand\TestRunner\Core\Plugin\Plugin
     */
    protected function getPlugin()
    {
        return PluginRepository::findByPluginID($this->getPluginID());
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
