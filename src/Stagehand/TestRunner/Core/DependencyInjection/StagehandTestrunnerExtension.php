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

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Stagehand\TestRunner\Core\Package;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class StagehandTestrunnerExtension implements ExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__));
        $loader->load(Package::PACKAGE_ID . '.yml');

        $this->transformConfiguration($container, $config);
    }

    /**
     * {@inheritDoc}
     */
    public function getNamespace()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getXsdValidationBasePath()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function getAlias()
    {
        return Package::PACKAGE_ID;
    }

    /**
     * @param ContainerBuilder $container
     * @param array $config
     */
    protected function transformConfiguration(ContainerBuilder $container, array $config)
    {
        $container->setParameter(Package::PACKAGE_ID . '.' . 'testing_framework', $config['testing_framework']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'recursively_scans', $config['recursively_scans']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'colors', $config['colors']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'preload_file', $config['preload_file']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'enables_autotest', $config['enables_autotest']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'monitoring_directories', $config['monitoring_directories']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'uses_notification', $config['uses_notification']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'growl_password', $config['growl_password']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_methods', $config['test_methods']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_classes', $config['test_classes']);

        if (array_key_exists('junit_xml', $config)) {
            $container->setParameter(Package::PACKAGE_ID . '.' . 'logs_results_in_junit_xml', $config['junit_xml']['enabled']);
            $container->setParameter(Package::PACKAGE_ID . '.' . 'logs_results_in_junit_xml_in_realtime', $config['junit_xml']['realtime']);
            $container->setParameter(Package::PACKAGE_ID . '.' . 'junit_xml_file', $config['junit_xml']['file']);
        }

        $container->setParameter(Package::PACKAGE_ID . '.' . 'stops_on_failure', $config['stops_on_failure']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'phpunit_config_file', $config['phpunit_config_file']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'cakephp_app_path', $config['cakephp_app_path']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'cakephp_core_path', $config['cakephp_core_path']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'ciunit_path', $config['ciunit_path']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_file_pattern', $config['test_file_pattern']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'prints_detailed_progress_report', $config['prints_detailed_progress_report']);
        $container->setParameter(Package::PACKAGE_ID . '.' . 'test_resources', $config['test_resources']);
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
