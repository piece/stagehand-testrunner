<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2010-2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2010-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.11.1
 */

namespace Stagehand\TestRunner\Runner\PHPUnitRunner\Printer;

use Stagehand\TestRunner\Core\Plugin\PHPUnitPlugin;
use Stagehand\TestRunner\Runner\TestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.11.1
 */
class DetailedProgressPrinterTest extends TestCase
{
    /**
     * @since Method available since Release 3.0.0
     */
    protected function getPluginID()
    {
        return PHPUnitPlugin::getPluginID();
    }

    /**
     * @test
     * @dataProvider targets
     */
    public function printsSkippedTestsWithADataProvider($testClass, $testMethod)
    {
        $collector = $this->createCollector();
        $collector->collectTestCase($testClass);
        $runner = $this->createRunner();
        $runner->setDetailedProgress(true);

        $this->runTests();

        $expected = PHP_EOL .
$testClass . PHP_EOL .
'  pass1 ... passed' . PHP_EOL .
PHP_EOL .
$testClass . '::' . $testMethod . PHP_EOL .
'  ' . $testMethod . ' with data set #0 ... skipped' . PHP_EOL .
'  pass2 ... passed' . PHP_EOL;
        $this->assertTrue(strstr($this->output, $expected) !== false);
    }

    /**
     * @return array
     * @since Method available since Release 2.17.0
     */
    public function targets()
    {
        return array(
            array('Stagehand_TestRunner_PHPUnitSkippedWithDataProviderTest', 'isSkippedWithTheDataProvider'),
            array('Stagehand_TestRunner_PHPUnitIncompleteWithDataProviderTest', 'isIncompleteWithTheDataProvider'),
        );
    }
}

/*
 * Local Variables:
 * mode: php
 * coding: utf-8
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
