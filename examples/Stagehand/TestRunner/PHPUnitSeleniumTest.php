<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
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
 * @since      File available since Release 2.16.0
 */

if (!class_exists('PHPUnit_Framework_TestCase')) return;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.16.0
 */
class Stagehand_TestRunner_PHPUnitSeleniumTest extends PHPUnit_Extensions_SeleniumTestCase
{
    protected function setUp()
    {
        if (count($this->drivers)) {
            $driver = $this->getMock(
                          'PHPUnit_Extensions_SeleniumTestCase_Driver',
                          array('start', 'doCommand')
                      );
            $driver->expects($this->any())
                   ->method('start')
                   ->will($this->returnValue(null));
            $driver->expects($this->any())
                   ->method('doCommand')
                   ->will($this->returnValue(null));
            $driver->setName($this->readAttribute($this->drivers[0], 'name'));
            $driver->setBrowser($this->readAttribute($this->drivers[0], 'browser'));
            $driver->setHost($this->readAttribute($this->drivers[0], 'host'));
            $driver->setPort($this->readAttribute($this->drivers[0], 'port'));
            $driver->setTimeout($this->readAttribute($this->drivers[0], 'seleniumTimeout'));
            $driver->setHttpTimeout($this->readAttribute($this->drivers[0], 'httpTimeout'));
            $driver->setTestCase($this->readAttribute($this->drivers[0], 'testCase'));
            $driver->setTestId($this->readAttribute($this->drivers[0], 'testId'));
            $this->drivers[0] = $driver;
        }
    }

    /**
     * @test
     */
    public function supportsTheSeleniumElementInTheXmlConfigurationFile()
    {
        if (!@$GLOBALS['STAGEHAND_TESTRUNNER_PHPUNITSELENIUMTEST_enables']) {
            $this->markTestSkipped('This test should not be run directly.');
        }

        $this->assertEquals(1, count($this->drivers));
        $this->assertEquals('Firefox on Linux', $this->readAttribute($this->drivers[0], 'name'));
        $this->assertEquals('*firefox /usr/lib/firefox/firefox-bin', $this->readAttribute($this->drivers[0], 'browser'));
        $this->assertEquals('my.linux.box', $this->readAttribute($this->drivers[0], 'host'));
        $this->assertEquals('4444', $this->readAttribute($this->drivers[0], 'port'));
        $this->assertEquals('30000', $this->readAttribute($this->drivers[0], 'seleniumTimeout'));
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
