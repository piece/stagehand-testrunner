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

namespace Stagehand\TestRunner\Core;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

use Stagehand\TestRunner\Core\DependencyInjection\Compiler\TestFilePatternPass;
use Stagehand\TestRunner\Core\DependencyInjection\Extension\ExtensionFinder;
use Stagehand\TestRunner\Util\String;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class ConfigurationTransformer
{
    /**
     * @var array
     */
    protected $configuration = array();

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerBuilder
     */
    protected $container;

    /**
     * @var string
     */
    protected $configurationFile;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $configurationID
     * @param array $configurationPart
     * @return \Stagehand\TestRunner\Core\ConfigurationTransformer
     */
    public function setConfigurationPart($configurationID, array $configurationPart)
    {
        if (array_key_exists($configurationID, $this->configuration)) {
            $this->configuration[$configurationID] = array_merge_recursive($this->configuration[$configurationID], $configurationPart);
        } else {
            $this->configuration[$configurationID] = $configurationPart;
        }
        return $this;
    }

    /**
     * @throws \Stagehand\TestRunner\Core\Exception
     */
    public function transformToContainer()
    {
        $this->container->addCompilerPass(new TestFilePatternPass());

        foreach (ExtensionFinder::findAll() as $extension) {
            $this->container->registerExtension($extension);
        }

        if (!is_null($this->configurationFile)) {
            $loader = new YamlFileLoader(
                $this->container,
                new FileLocator(dirname($this->configurationFile))
            );
            $loader->load(basename($this->configurationFile));
        }

        $normalizedConfiguration = String::applyFilter($this->configuration, function ($v) {
            return urldecode($v);
        });
        foreach ($this->container->getExtensions() as $extension) { /* @var $extension \Symfony\Component\DependencyInjection\Extension\ExtensionInterface */
            $this->container->loadFromExtension(
                $extension->getAlias(),
                array_key_exists($extension->getAlias(), $normalizedConfiguration) ?
                    $normalizedConfiguration[ $extension->getAlias() ] :
                    array()
            );
        }

        $this->container->compile();
    }

    /**
     * @param string $configurationFile
     */
    public function setConfigurationFile($configurationFile)
    {
        $this->configurationFile = $configurationFile;
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
