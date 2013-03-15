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
 * @since      File available since Release 3.0.0
 */

namespace Stagehand\TestRunner\Util;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2012 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 3.0.0
 */
class FailureTrace
{
    /**
     * @param array $testClassSuperTypes
     * @param \Exception $e
     * @param \ReflectionClass $class
     * @return array
     */
    public static function findFileAndLineOfFailureOrError(array $testClassSuperTypes, \Exception $e, \ReflectionClass $class)
    {
        if (in_array($class->getName(), $testClassSuperTypes)) return;
        if ($e->getFile() == $class->getFileName()) {
            return array($e->getFile(), $e->getLine());
        }
        foreach ($e->getTrace() as $trace) {
            if (array_key_exists('file', $trace) && $trace['file'] == $class->getFileName()) {
                return array($trace['file'], $trace['line']);
            }
        }
        if (method_exists($class, 'getTraits')) {
            foreach ($class->getTraits() as $trait) {
                $ret = self::findFileAndLineOfFailureOrError($testClassSuperTypes, $e, $trait);
                if ($ret) {
                    return $ret;
                }
            }
        }
        return self::findFileAndLineOfFailureOrError($testClassSuperTypes, $e, $class->getParentClass());
    }

    /**
     * @param array $backtrace
     * @return string
     */
    public static function buildFailureTrace(array $backtrace)
    {
        $failureTrace = '';
        for ($i = 0, $count = count($backtrace); $i < $count; ++$i) {
            if (!array_key_exists('file', $backtrace[$i])) {
                continue;
            }

            $failureTrace .=
                $backtrace[$i]['file'] .
                ':' .
                (array_key_exists('line', $backtrace[$i]) ? $backtrace[$i]['line']
                                                          : '?') .
                PHP_EOL;
        }

        return $failureTrace;
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
