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

namespace Stagehand\TestRunner\Core\DependencyInjection;

use Symfony\Component\Config\Definition\BooleanNode;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\Package;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class GeneralConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder
            ->root(Package::PACKAGE_ID)
                ->children()
                    ->scalarNode('testing_framework')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->booleanNode('recursively_scans')
                        ->defaultFalse()
                    ->end()
                    ->booleanNode('colors')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('preload_file')
                        ->defaultNull()
                    ->end()
                    ->booleanNode('enables_autotest')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('monitoring_directories')
                        ->addDefaultsIfNotSet()
                        ->defaultValue(array())
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->booleanNode('uses_notification')
                    ->end()
                    ->arrayNode('test_methods')
                        ->addDefaultsIfNotSet()
                        ->defaultValue(array())
                        ->prototype('scalar')
                        ->end()
                    ->end()
                    ->arrayNode('test_classes')
                        ->addDefaultsIfNotSet()
                        ->defaultValue(array())
                        ->prototype('scalar')
                        ->end()
                    ->end()
                   ->arrayNode('junit_xml')
                       ->children()
                           ->scalarNode('file')
                               ->isRequired()
                               ->cannotBeEmpty()
                           ->end()
                           ->booleanNode('realtime')
                               ->defaultFalse()
                           ->end()
                       ->end()
                    ->end()
                    ->booleanNode('stops_on_failure')
                        ->defaultFalse()
                    ->end()
                    ->scalarNode('phpunit_config_file')
                    ->end()
                    ->scalarNode('cakephp_app_path')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('cakephp_core_path')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('ciunit_path')
                        ->defaultNull()
                    ->end()
                    ->scalarNode('test_file_pattern')
                    ->end()
                    ->booleanNode('prints_detailed_progress_report')
                        ->defaultFalse()
                    ->end()
                    ->arrayNode('test_resources')
                        ->addDefaultsIfNotSet()
                        ->defaultValue(array(ApplicationContext::getInstance()->getEnvironment()->getWorkingDirectoryAtStartup()))
                        ->prototype('scalar')
                        ->end()
                    ->end()
                ->end()
            ->end();
        return $treeBuilder;
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
