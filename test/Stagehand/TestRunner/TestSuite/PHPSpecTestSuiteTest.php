<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2012 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 3.0.4
 */

namespace Stagehand\TestRunner\TestSuite;

use Stagehand\TestRunner\Core\Plugin\PHPSpecPlugin;
use Stagehand\TestRunner\Test\FactoryAwareTestCase;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.4
 */
class PHPSpecTestSuiteTest extends FactoryAwareTestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->applicationContext->createComponent('preparer_factory')->create()->prepare();

        require_once 'Stagehand/TestRunner/PHPSpecPassSpec.php';
        require_once 'Stagehand/TestRunner/PHPSpecWithoutNamespaceSpec.php';
    }

    protected function getPluginID()
    {
        return PHPSpecPlugin::getPluginID();
    }

    /**
     * @test
     * @dataProvider exampleGroupsWithNames
     * @param string $exampleGroupClass
     * @param string $exampleGroupName
     * @link http://piece-framework.com/issues/406
     */
    public function getsAnExampleGroupClass($exampleGroupClass, $exampleGroupName)
    {
        $testSuite = new PHPSpecTestSuite(null);
        $testSuite->setTestTargets($this->createTestTargets());
        $testSuite->addExampleGroup(new \ReflectionClass($exampleGroupClass));

        $this->assertEquals($exampleGroupClass, $testSuite->getExampleGroupClass($exampleGroupName));
    }

    public function exampleGroupsWithNames()
    {
        return array(
            array('Stagehand\TestRunner\DescribePhpSpecPass', 'Stagehand\TestRunner\PhpSpecPass'),
            array('DescribePhpSpecWithoutNamespace', 'PhpSpecWithoutNamespace'),
        );
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
