<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
 *               2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>,
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
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.14.0
 */

namespace Stagehand\TestRunner\Process;

use Stagehand\TestRunner\Core\ComponentAwareFactory;
use Stagehand\TestRunner\Util\OutputBuffering;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @copyright  2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.14.0
 */
class TestRun
{
    /**
     * @var boolean $result
     * @since Property available since Release 2.18.0
     */
    protected $result;

    /**
     * @var \Stagehand\TestRunner\Util\OutputBuffering
     * @since Property available since Release 3.0.0
     */
    protected $outputBuffering;

    /**
     * @var \Stagehand\TestRunner\Core\ComponentAwareFactory
     * @since Property available since Release 3.0.0
     */
    protected $preparerFactory;

    /**
     * @var \Stagehand\TestRunner\Core\ComponentAwareFactory
     * @since Property available since Release 3.0.0
     */
    protected $collectorFactory;

    /**
     * @var \Stagehand\TestRunner\Core\ComponentAwareFactory
     * @since Property available since Release 3.0.0
     */
    protected $runnerFactory;

    /**
     * @var \Stagehand\TestRunner\Core\ComponentAwareFactory
     * @since Method available since Release 3.0.0
     */
    protected $notifierFactory;

    /**
     * Runs tests.
     *
     * @throws \Stagehand\TestRunner\Core\Exception
     * @since Method available since Release 2.1.0
     */
    public function run()
    {
        $this->outputBuffering->clearOutputHandlers();
        $this->preparerFactory->create()->prepare();

        $runner = $this->runnerFactory->create();
        $this->result = $runner->run($this->collectorFactory->create()->collect());

        if ($runner->usesNotification()) {
            $this->notifierFactory->create()->notifyResult($runner->getNotification());
        }
    }

    /**
     * @param \Stagehand\TestRunner\Util\OutputBuffering $outputBuffering
     * @since Method available since Release 3.0.0
     */
    public function setOutputBuffering(OutputBuffering $outputBuffering)
    {
        $this->outputBuffering = $outputBuffering;
    }

    /**
     * @param \Stagehand\TestRunner\Core\ComponentAwareFactory $preparerFactory
     * @since Method available since Release 3.0.0
     */
    public function setPreparerFactory(ComponentAwareFactory $preparerFactory)
    {
        $this->preparerFactory = $preparerFactory;
    }

    /**
     * @param \Stagehand\TestRunner\Core\ComponentAwareFactory $collectorFactory
     * @since Method available since Release 3.0.0
     */
    public function setCollectorFactory(ComponentAwareFactory $collectorFactory)
    {
        $this->collectorFactory = $collectorFactory;
    }

    /**
     * @param \Stagehand\TestRunner\Core\ComponentAwareFactory $runnerFactory
     * @since Method available since Release 3.0.0
     */
    public function setRunnerFactory(ComponentAwareFactory $runnerFactory)
    {
        $this->runnerFactory = $runnerFactory;
    }

    /**
     * @param \Stagehand\TestRunner\Core\ComponentAwareFactory $notifierFactory
     * @since Method available since Release 3.0.0
     */
    public function setNotifierFactory(ComponentAwareFactory $notifierFactory)
    {
        $this->notifierFactory = $notifierFactory;
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
