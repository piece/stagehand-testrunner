<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.20.0
 */

namespace Stagehand\TestRunner\Process\Autotest;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;
use Stagehand\TestRunner\Test\FactoryAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class PHPUnitAutotestTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        TestCase::initializeConfigurators();
        static::$configurators[] = function () {
            $runner = ApplicationContext::getInstance()->createComponent('runner_factory')->create(); /* @var $runner \Stagehand\TestRunner\Runner\PHPUnitRunner */
            $runner->setPrintsDetailedProgressReport(true);
        };
        static::$configurators[] = function () {
            $phpunitXMLConfiguration = \Phake::mock('\Stagehand\TestRunner\Core\PHPUnitXMLConfiguration'); /* @var $phpunitXMLConfiguration \Stagehand\TestRunner\Core\PHPUnitXMLConfiguration */
            \Phake::when($phpunitXMLConfiguration)->getFileName()->thenReturn('FILE');
            $autotest = ApplicationContext::getInstance()->createComponent('autotest_factory')->create(); /* @var $autotest \Stagehand\TestRunner\Process\AutoTest */
            $autotest->setPHPUnitXMLConfiguration($phpunitXMLConfiguration);
        };
    }

    /**
     * @return string
     */
    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    /**
     * @return array
     */
    public function preservedConfigurations()
    {
        $preservedConfigurations = parent::preservedConfigurations();
        $index = count($preservedConfigurations);
        return array_merge($preservedConfigurations, array(
            array($index++, array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--print-detailed-progress'), array(true, true, true)),
            array($index++, array(escapeshellarg(strtolower($this->getPluginID())), '-R', '--phpunit-config=' . escapeshellarg('FILE')), array(true, true, true)),
        ));
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
