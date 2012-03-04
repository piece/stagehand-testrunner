<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2011-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\Core;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class PHPUnitXMLConfiguration
{
    /**
     * @var \PHPUnit_Util_Configuration
     */
    protected $configuration;

    /**
     * @param \PHPUnit_Util_Configuration $configuration
     */
    public function __construct(\PHPUnit_Util_Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->configuration->getFilename();
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasPHPUnitConfiguration($name)
    {
        $phpunitConfiguration = $this->configuration->getPHPUnitConfiguration();
        return array_key_exists($name, $phpunitConfiguration);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getPHPUnitConfiguration($name)
    {
        $phpunitConfiguration = $this->configuration->getPHPUnitConfiguration();
        return $phpunitConfiguration[$name];
    }

    /**
     * @return boolean
     */
    public function hasSeleniumBrowserConfiguration()
    {
        return count($this->configuration->getSeleniumBrowserConfiguration());
    }

    /**
     * @return array
     */
    public function getSeleniumBrowserConfiguration()
    {
        return $this->configuration->getSeleniumBrowserConfiguration();
    }

    /**
     * @param string $name
     * @return boolean
     */
    public function hasGroupConfiguration($name)
    {
        $groupConfiguration = $this->configuration->getGroupConfiguration();
        return array_key_exists($name, $groupConfiguration) && count($groupConfiguration[$name]);
    }

    /**
     * @param string $name
     * @return array
     */
    public function getGroupConfiguration($name)
    {
        $groupConfiguration = $this->configuration->getGroupConfiguration();
        return $groupConfiguration[$name];
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
