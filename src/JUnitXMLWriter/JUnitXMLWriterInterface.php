<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3.
 *
 * Copyright (c) 2009-2013 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      File available since Release 2.10.0
 */
namespace Stagehand\TestRunner\JUnitXMLWriter;

/**
 * @copyright  2009-2013 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 *
 * @version    Release: @package_version@
 *
 * @since      Class available since Release 2.10.0
 */
interface JUnitXMLWriterInterface
{
    public function startTestSuites();

    /**
     * @param string $name
     * @param int    $testCount
     */
    public function startTestSuite($name, $testCount = null);

    /**
     * @param string $name
     * @param mixed  $test
     * @param string $methodName
     */
    public function startTestCase($name, $test, $methodName = null);

    /**
     * @param string $text
     * @param string $type
     * @param string $file
     * @param string $line
     * @param string $message
     */
    public function writeError($text, $type = null, $file = null, $line = null, $message = null);

    /**
     * @param string $text
     * @param string $type
     * @param string $file
     * @param string $line
     * @param string $message
     */
    public function writeFailure($text, $type = null, $file = null, $line = null, $message = null);

    /**
     * @param string $text
     * @param string $type
     * @param string $file
     * @param string $line
     * @param string $message
     */
    public function writeWarning($text, $type = null, $file = null, $line = null, $message = null);

    /**
     * @param float $time
     * @param int   $assertionCount
     */
    public function endTestCase($time, $assertionCount = null);

    public function endTestSuite();

    public function endTestSuites();
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
