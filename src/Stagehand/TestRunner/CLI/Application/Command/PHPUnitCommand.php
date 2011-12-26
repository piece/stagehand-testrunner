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
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\CLI\Application\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Stagehand\TestRunner\Core\ConfigurationTransformer;
use Stagehand\TestRunner\Core\Configuration\PHPUnitConfiguration;
use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;
use Stagehand\TestRunner\Core\Plugin\PluginFinder;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class PHPUnitCommand extends PluginCommand
{
    /**
     * @return \Stagehand\TestRunner\Core\Plugin\Plugin
     */
    protected function getPlugin()
    {
        return PluginFinder::findByPluginID(PHPUnitPlugin::getPluginID());
    }

    protected function doConfigure()
    {
        if ($this->getPlugin()->hasFeature('phpunit_config_file')) {
            $this->addOption('phpunit-config', null, InputOption::VALUE_REQUIRED, 'The PHPUnit XML configuration file');
        }

        if ($this->getPlugin()->hasFeature('prints_detailed_progress_report')) {
            $this->addOption('print-detailed-progress', null, InputOption::VALUE_NONE, 'Prints detailed progress report.');
        }
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \Stagehand\TestRunner\Core\ConfigurationTransformer $configurationTransformer
     */
    protected function doTransformToConfiguration(InputInterface $input, OutputInterface $output, ConfigurationTransformer $configurationTransformer)
    {
        if ($this->getPlugin()->hasFeature('phpunit_config_file')) {
            if (!is_null($input->getOption('phpunit-config'))) {
                $configurationTransformer->setConfigurationPart(PHPUnitConfiguration::getConfigurationID(), array('phpunit_config_file' => $input->getOption('phpunit-config')));
            }
        }

        if ($this->getPlugin()->hasFeature('prints_detailed_progress_report')) {
            $configurationTransformer->setConfigurationPart(PHPUnitConfiguration::getConfigurationID(), array('prints_detailed_progress_report' => $input->getOption('print-detailed-progress')));
        }
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
