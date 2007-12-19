<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2007 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @link       http://www.phpunit.de/
 * @since      File available since Release 2.0.0
 */

// {{{ Stagehand_TestRunner_PHPSpec_Reporter

/**
 * A reporter for PHPSpec.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @link       http://www.phpunit.de/
 * @since      Class available since Release 2.0.0
 */
class Stagehand_TestRunner_PHPSpec_Reporter extends PHPSpec_Runner_Reporter_Text
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

    private $_color;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ constructor

    /**
     * @param PHPSpec_Runner_Result $result
     * @param boolean               $color
     */
    public function __construct(PHPSpec_Runner_Result $result, $color)
    {
        parent::__construct($result);

        if ($color) {
            include_once 'Console/Color.php';
        }

        $this->_color = $color;
    }

    // }}}
    // {{{ outputStatus()

    /**
     * @param string $symbol
     */
    public function outputStatus($symbol)
    {
        if ($this->_color) {
            switch ($symbol) {
            case '.':
                $symbol = Console_Color::convert("%g$symbol%n");
                break;
            case 'F':
                $symbol = Console_Color::convert("%r$symbol%n");
                break;
            case 'E':
                $symbol = Console_Color::convert("%p$symbol%n");
                break;
            case 'P':
                $symbol = Console_Color::convert("%y$symbol%n");
                break;
            }
        }

        parent::outputStatus($symbol);
    }

    // }}}
    // {{{ output()

    /**
     * @param boolean $specs
     */
    public function output($specs = false)
    {
        $output = preg_replace(array('/(\x0d|\x0a|\x0d\x0a){3,}/', '/^(  -)(.+)/m'),
                               array("\n\n", '$1 $2'),
                               $this->toString($specs)
                               );

        if ($this->_color) {
            $failuresCount = $this->_result->countFailures();
            $deliberateFailuresCount = $this->_result->countDeliberateFailures();
            $errorsCount = $this->_result->countErrors();
            $exceptionsCount = $this->_result->countExceptions();
            $pendingsCount = $this->_result->countPending();

            if ($failuresCount + $deliberateFailuresCount + $errorsCount + $exceptionsCount + $pendingsCount == 0) {
                $colorCode = '%g';
            } elseif ($pendingsCount && $failuresCount + $deliberateFailuresCount + $errorsCount + $exceptionsCount == 0) {
                $colorCode = '%y';
            } else {
                $colorCode = '%r';
            }

            $output = Console_Color::convert(preg_replace(array('/^(\d+ examples?.*)/m',
                                                                '/^(  -)(.+)( \(ERROR|EXCEPTION\))/m',
                                                                '/^(  -)(.+)( \(FAIL\))/m',
                                                                '/^(  -)(.+)( \(PENDING\))/m',
                                                                '/^(  -)(.+)/m',
                                                                '/(\d+\)\s+)(.+ (?:ERROR|EXCEPTION)\s+.+)/',
                                                                '/(\d+\)\s+)(.+ FAILED\s+.+)/',
                                                                '/(\d+\)\s+)(.+ PENDING\s+.+)/',
                                                                '/^((?:Errors|Exceptions):)/m',
                                                                '/^(Failures:)/m',
                                                                '/^(Pending:)/m'
                                                                ),
                                                          array("$colorCode\$1%n",
                                                                '%p$1$2$3%n',
                                                                '%r$1$2$3%n',
                                                                '%y$1$2$3%n',
                                                                '%g$1$2$3%n',
                                                                '$1%p$2%n',
                                                                '$1%r$2%n',
                                                                '$1%y$2%n',
                                                                '%p$1%n',
                                                                '%r$1%n',
                                                                '%y$1%n'
                                                                ),
                                                          Console_Color::escape($output))
                                             );
        }

        print $output;
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    // }}}
    // {{{ _format()

    /**
     * @param string $description
     */
    protected function _format($description)
    {
        return preg_replace('/spec$/', '', preg_replace('/^describe ?/', '', $description));
    }

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
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
?>
