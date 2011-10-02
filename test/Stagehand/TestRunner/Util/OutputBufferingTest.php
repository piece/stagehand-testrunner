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
 * @since      File available since Release 2.20.0
 */

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.20.0
 */
class Stagehand_TestRunner_Util_OutputBufferingTest extends PHPUnit_Framework_TestCase
{
    protected $nestingLevel = 0;

    /**
     * @test
     * @link http://redmine.piece-framework.com/issues/323
     */
    public function clearsThePrecedingOutputHandlers()
    {
        $this->nestingLevel = 2;
        $outputBuffering = $this->getMock(
            'Stagehand_TestRunner_Util_OutputBuffering',
            array('getNestingLevel', 'clearOutputHandler')
        );
        $outputBuffering->expects($this->exactly(3))
            ->method('getNestingLevel')
            ->will($this->returnCallback(array($this, 'getNestingLevel')));
        $outputBuffering->expects($this->exactly(2))
            ->method('clearOutputHandler')
            ->will($this->returnValue(null));
        $outputBuffering->clearOutputHandlers();
        $this->assertEquals(-1, $this->nestingLevel);
    }

    /**
     * @test
     * @expectedException Stagehand_TestRunner_CannotRemoveException
     * @link http://redmine.piece-framework.com/issues/323
     */
    public function raisesAnExceptionWhenAPrecedingOutputBufferCannotBeRemoved()
    {
        $this->nestingLevel = 1;
        $outputBuffering = $this->getMock(
            'Stagehand_TestRunner_Util_OutputBuffering',
            array('getNestingLevel', 'clearOutputHandler')
        );
        $outputBuffering->expects($this->once())
            ->method('getNestingLevel')
            ->will($this->returnCallback(array($this, 'getNestingLevel')));
        $outputBuffering->expects($this->once())
            ->method('clearOutputHandler')
            ->will($this->returnCallback(array($this, 'clearOutputHandler')));
        $outputBuffering->clearOutputHandlers();
        $this->assertEquals(0, $this->nestingLevel);
    }

    /**
     * @return integer
     */
    public function getNestingLevel()
    {
        return $this->nestingLevel--;
    }

    /**
     * @throws Stagehand_LegacyError_PHPError_Exception
     */
    public function clearOutputHandler()
    {
        throw new Stagehand_LegacyError_PHPError_Exception();
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
