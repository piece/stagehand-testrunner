<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.10.0
 */

// {{{ Stagehand_TestRunner_Runner_SimpleTestRunnerTest

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.10.0
 */
class Stagehand_TestRunner_Runner_SimpleTestRunnerTest extends Stagehand_TestRunner_Runner_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */
 
    /**
     * @test
     */
    public function runsOnlyTheSpecifiedMethods()
    {
        $this->config->addMethodToBeTested('testPass1');
        $suite = new Stagehand_TestRunner_Runner_SimpleTestRunner_TestSuite();
        class_exists('Stagehand_TestRunner_SimpleTestMultipleClassesTest');
        $suite->add(new Stagehand_TestRunner_SimpleTestMultipleClasses1Test());
        $suite->add(new Stagehand_TestRunner_SimpleTestMultipleClasses2Test());
        $runner = new Stagehand_TestRunner_Runner_SimpleTestRunner($this->config);
        ob_start();
        $runner->run($suite);
        ob_end_clean();

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $xpath = new DOMXPath($junitXML);
        $testcases = $xpath->query('//testcase');
        $this->assertEquals(2, $testcases->length);
        $testcase = $testcases->item(0);
        $this->assertEquals('testPass1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_SimpleTestMultipleClasses1Test',
                            $testcase->getAttribute('class')
        );
        $testcase = $testcases->item(1);
        $this->assertEquals('testPass1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_SimpleTestMultipleClasses2Test',
                            $testcase->getAttribute('class')
        );
    }

    /**
     * @test
     */
    public function runsOnlyTheSpecifiedMethodsByFullyQualifiedMethodName()
    {
        $this->config->addMethodToBeTested(
            'Stagehand_TestRunner_SimpleTestMultipleClasses1Test::testPass1'
        );
        $suite = new Stagehand_TestRunner_Runner_SimpleTestRunner_TestSuite();
        class_exists('Stagehand_TestRunner_SimpleTestMultipleClassesTest');
        $suite->add(new Stagehand_TestRunner_SimpleTestMultipleClasses1Test());
        $suite->add(new Stagehand_TestRunner_SimpleTestMultipleClasses2Test());
        $runner = new Stagehand_TestRunner_Runner_SimpleTestRunner($this->config);
        ob_start();
        $runner->run($suite);
        ob_end_clean();

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $xpath = new DOMXPath($junitXML);
        $testcases = $xpath->query('//testcase');
        $this->assertEquals(1, $testcases->length);
        $testcase = $testcases->item(0);
        $this->assertEquals('testPass1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_SimpleTestMultipleClasses1Test',
                            $testcase->getAttribute('class')
        );
    }

    /**
     * @test
     */
    public function runsOnlyTheSpecifiedClasses()
    {
        $this->config->addClassToBeTested(
            'Stagehand_TestRunner_SimpleTestMultipleClasses1Test'
        );
        $suite = new Stagehand_TestRunner_Runner_SimpleTestRunner_TestSuite();
        class_exists('Stagehand_TestRunner_SimpleTestMultipleClassesTest');
        $suite->add(new Stagehand_TestRunner_SimpleTestMultipleClasses1Test());
        $suite->add(new Stagehand_TestRunner_SimpleTestMultipleClasses2Test());
        $runner = new Stagehand_TestRunner_Runner_SimpleTestRunner($this->config);
        ob_start();
        $runner->run($suite);
        ob_end_clean();

        $junitXML = new DOMDocument();
        $junitXML->load($this->config->junitXMLFile);
        $xpath = new DOMXPath($junitXML);
        $testcases = $xpath->query('//testcase');
        $this->assertEquals(2, $testcases->length);
        $testcase = $testcases->item(0);
        $this->assertEquals('testPass1', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_SimpleTestMultipleClasses1Test',
                            $testcase->getAttribute('class')
        );
        $testcase = $testcases->item(1);
        $this->assertEquals('testPass2', $testcase->getAttribute('name'));
        $this->assertEquals('Stagehand_TestRunner_SimpleTestMultipleClasses1Test',
                            $testcase->getAttribute('class')
        );
     }

    /**#@-*/

    /**#@+
     * @access protected
     */

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

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
