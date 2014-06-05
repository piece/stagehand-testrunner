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

namespace Stagehand\TestRunner\Process\ContinuousTesting;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class FatalErrorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     * @dataProvider messagesOfAFatalOrParseError
     * @param string $output
     * @param string $fullMessage
     * @param string $message
     * @param string $file
     * @param integer $line
     * @link http://redmine.piece-framework.com/issues/333
     */
    public function findsTheMessageOfAFatalOrParseError($output, $fullMessage, $message, $file, $line)
    {
        $fatalError = new FatalError($output);

        $this->assertEquals($fullMessage, $fatalError->getFullMessage());
        $this->assertEquals($message, $fatalError->getMessage());
        $this->assertEquals($file, $fatalError->getFile());
        $this->assertEquals($line, $fatalError->getLine());
    }

    /**
     * @return array
     */
    public function messagesOfAFatalOrParseError()
    {
        $fatalErrorMessage = "Fatal error: Class 'Stagehand\\FSM\\Events' not found";
        $fatalErrorFile = '/home/iteman/GITREPOS/stagehand-fsm/test/Stagehand/FSM/EventTest.php';
        $fatalErrorLine = 52;
        $fatalErrorFullMessage = $fatalErrorMessage . ' in ' . $fatalErrorFile . ' on line ' . $fatalErrorLine;
        $fatalErrorOutput =
'PHPUnit 3.5.14 by Sebastian Bergmann.' . PHP_EOL .
PHP_EOL .
PHP_EOL .
$fatalErrorFullMessage . PHP_EOL;
        $parseErrorMessage = "Parse error: syntax error, unexpected T_CONST, expecting '{'";
        $parseErrorFile = '/home/iteman/GITREPOS/stagehand-fsm/src/Stagehand/FSM/Event.php';
        $parseErrorLine = 53;
        $parseErrorFullMessage = $parseErrorMessage . ' in ' . $parseErrorFile . ' on line ' . $parseErrorLine;
        $parseErrorOutput =
'PHPUnit 3.5.14 by Sebastian Bergmann.' . PHP_EOL .
PHP_EOL .
PHP_EOL .
$parseErrorFullMessage . PHP_EOL;
        $evaldErrorMessage = 'Parse error: syntax error, unexpected T_VARIABLE, expecting T_STRING';
        $evaldErrorFile = '/home/iteman/GITREPOS/stagehand-testrunner/src/Stagehand/TestRunner/Process/Autotest.php';
        $evaldErrorLine = 172;
        $evaldErrorFullMessage = $evaldErrorMessage . ' in ' . $evaldErrorFile . '(' . $evaldErrorLine . ") : eval()'d code on line 1";
        $evaldErrorOutput = $evaldErrorFullMessage;
        $nestedEvaldErrorMessage = 'Parse error: syntax error, unexpected T_VARIABLE, expecting T_STRING';
        $nestedEvaldErrorFile = '/home/iteman/GITREPOS/stagehand-testrunner/src/Stagehand/TestRunner/Process/Autotest.php';
        $nestedEvaldErrorLine = 172;
        $nestedEvaldErrorFullMessage = $nestedEvaldErrorMessage . ' in ' . $nestedEvaldErrorFile . '(' . $nestedEvaldErrorLine . ") : eval()'d code(1) : eval()'d code on line 1";
        $nestedEvaldErrorOutput = $nestedEvaldErrorFullMessage;
        $unknownOutput =
'PHPUnit 3.5.14 by Sebastian Bergmann.' . PHP_EOL .
PHP_EOL .
'..';
        return array(
            array($fatalErrorOutput, $fatalErrorFullMessage, $fatalErrorMessage, $fatalErrorFile, $fatalErrorLine),
            array($parseErrorOutput, $parseErrorFullMessage, $parseErrorMessage, $parseErrorFile, $parseErrorLine),
            array($evaldErrorOutput, $evaldErrorFullMessage, $evaldErrorMessage, $evaldErrorFile, $evaldErrorLine),
            array($nestedEvaldErrorOutput, $nestedEvaldErrorFullMessage, $nestedEvaldErrorMessage, $nestedEvaldErrorFile, $nestedEvaldErrorLine),
            array($unknownOutput, $unknownOutput, null, null, null),
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
