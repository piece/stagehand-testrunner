<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3.
 *
 * Copyright (c) 2010-2014 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      File available since Release 2.12.0
 */
namespace Stagehand\TestRunner\Preparer;

use Stagehand\TestRunner\CLI\Terminal;
use Stagehand\TestRunner\DependencyInjection\PHPUnitConfigurationFactory;

/**
 * @copyright  2010-2014 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      Class available since Release 2.12.0
 */
class PHPUnitPreparer extends Preparer
{
    /**
     * @var \Stagehand\TestRunner\DependencyInjection\PHPUnitConfigurationFactory
     *
     * @since Property available since Release 3.6.0
     */
    protected $phpunitConfigurationFactory;

    /**
     * @var \Stagehand\TestRunner\CLI\Terminal
     *
     * @since Property available since Release 3.0.0
     */
    protected $terminal;

    public function prepare()
    {
        $phpunitConfiguration = $this->phpunitConfigurationFactory->create();
        if (!is_null($phpunitConfiguration)) {
            $this->earlyConfigure($phpunitConfiguration);
        }
    }

    /**
     * @param \Stagehand\TestRunner\DependencyInjection\PHPUnitConfigurationFactory $phpunitConfigurationFactory
     *
     * @since Method available since Release 3.6.0
     */
    public function setPHPUnitConfigurationFactory(PHPUnitConfigurationFactory $phpunitConfigurationFactory)
    {
        $this->phpunitConfigurationFactory = $phpunitConfigurationFactory;
    }

    /**
     * @param \Stagehand\TestRunner\CLI\Terminal $terminal
     *
     * @since Method available since Release 3.0.0
     */
    public function setTerminal(Terminal $terminal)
    {
        $this->terminal = $terminal;
    }

    /**
     * Loads a bootstrap file.
     *
     * @param string $filename
     * @param bool   $syntaxCheck
     *
     * @see \PHPUnit_TextUI_Command::handleBootstrap()
     * @since Method available since Release 2.16.0
     */
    protected function handleBootstrap($filename, $syntaxCheck = false)
    {
        try {
            \PHPUnit_Util_Fileloader::checkAndLoad($filename, $syntaxCheck);
        } catch (RuntimeException $e) {
            \PHPUnit_TextUI_TestRunner::showError($e->getMessage());
        }
    }

    /**
     * @param \PHPUnit_Util_Configuration $configuration $configuration
     *
     * @since Method available since Release 2.16.0
     */
    protected function earlyConfigure(\PHPUnit_Util_Configuration $configuration)
    {
        $configuration->handlePHPConfiguration();

        $phpunitConfiguration = $configuration->getPHPUnitConfiguration();
        if (array_key_exists('bootstrap', $phpunitConfiguration)) {
            if (array_key_exists('syntaxCheck', $phpunitConfiguration)) {
                $this->handleBootstrap($phpunitConfiguration['bootstrap'], $phpunitConfiguration['syntaxCheck']);
            } else {
                $this->handleBootstrap($phpunitConfiguration['bootstrap']);
            }
        }

        if (array_key_exists('colors', $phpunitConfiguration)) {
            $this->terminal->setColor($phpunitConfiguration['colors']);
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
