<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2010-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.14.0
 */

namespace Stagehand\TestRunner\Runner\CakeRunner;

use Stagehand\TestRunner\Core\TestingFramework;
use Stagehand\TestRunner\Core\Config;

require_once 'simpletest/unit_tester.php';
require_once 'simpletest/web_tester.php';
require_once 'simpletest/mock_objects.php';

/**
 * @package    Stagehand_TestRunner
 * @copyright  2010-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.14.0
 */
class JUnitXMLTest extends \Stagehand\TestRunner\Runner\SimpleTestRunner\JUnitXMLTest
{
    protected $framework = TestingFramework::CAKE;

    protected function loadClasses()
    {
        include_once 'Stagehand/TestRunner/cake_pass.test.php';
        include_once 'Stagehand/TestRunner/cake_failure.test.php';
        include_once 'Stagehand/TestRunner/cake_error.test.php';
        include_once 'Stagehand/TestRunner/cake_multiple_classes.test.php';
        include_once 'Stagehand/TestRunner/cake_common.test.php';
        include_once 'Stagehand/TestRunner/cake_extended.test.php';
        include_once 'Stagehand/TestRunner/cake_failure_in_anonymous_function.test.php';
        include_once 'Stagehand/TestRunner/cake_skip_class.test.php';
        include_once 'Stagehand/TestRunner/cake_skip_method.test.php';

        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            include_once 'Stagehand/TestRunner/cake_multiple_classes_with_namespace.test.php';
        }
    }

    /**
     * @param \Stagehand\TestRunner\Core\Config $config
     * @since Method available since Release 2.14.1
     */
    protected function configure(Config $config)
    {
        $config->cakephpAppPath = dirname(__FILE__) . '/../../../../../vendor/cakephp/app';
    }

    /**
     * @link http://redmine.piece-framework.com/issues/261
     * @since Method available since Release 2.16.0
     */
    public function provideFailurePatterns()
    {
        return array(
            array('testIsFailure', 'Stagehand_TestRunner_CakeFailureTest', 49, 'This is an error message.', null, false),
            array('testIsError', 'Stagehand_TestRunner_CakeErrorTest', 49, 'This is an exception message.', null, false),
            array('testTestShouldFailCommon', 'Stagehand_TestRunner_CakeExtendedTest', 54, '', 'Stagehand_TestRunner_CakeCommonTest', false),
            array('testIsFailure', 'Stagehand_TestRunner_CakeFailureInAnonymousFunctionTest', 49, 'This is an error message.', null, true),
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
