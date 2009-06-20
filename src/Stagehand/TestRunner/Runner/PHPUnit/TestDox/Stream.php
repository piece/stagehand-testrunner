<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2008 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.php.net/manual/ja/function.stream-wrapper-register.php
 * @since      File available since Release 2.4.0
 */

// {{{ Stagehand_TestRunner_Runner_PHPUnit_TestDox_Stream

/**
 * A stream wrapper to print TestDox documentation.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2008 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @link       http://www.php.net/manual/ja/function.stream-wrapper-register.php
 * @since      Class available since Release 2.4.0
 */
class Stagehand_TestRunner_Runner_PHPUnit_TestDox_Stream
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

    private $_position = 0;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ stream_open()

    /**
     * The implementation of stream_open().
     *
     * @param string  $path
     * @param string  $mode
     * @param integer $options
     * @param string  $opened_path
     * @return boolean
     */
    public function stream_open($path, $mode, $options, $opened_path)
    {
        return true;
    }

    // }}}
    // {{{ stream_close()

    /**
     * The implementation of stream_close().
     *
     * @param string  $path
     * @param string  $mode
     * @param integer $options
     * @param string  $opened_path
     * @return boolean
     */
    public function stream_close() {}

    // }}}
    // {{{ stream_read()

    /**
     * The implementation of stream_read().
     *
     * @param integer $count
     * @return string
     */
    public function stream_read($count)
    {
        $data = substr(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox, $this->_position, $count);
        $this->_position += strlen($data);
        return $data;
    }

    // }}}
    // {{{ stream_write()

    /**
     * The implementation of stream_write().
     *
     * @param string $data
     * @return integer
     */
    public function stream_write($data)
    {
        Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox =
            substr(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox, 0, $this->_position) .
            $data .
            substr(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox, $this->_position + strlen($data));
        $this->_position += strlen($data);
        return strlen($data);
    }

    // }}}
    // {{{ stream_eof()

    /**
     * The implementation of stream_eof().
     *
     * @return boolean
     */
    public function stream_eof()
    {
        return $this->_position >= strlen(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox);
    }

    // }}}
    // {{{ stream_tell()

    /**
     * The implementation of stream_tell().
     *
     * @return integer
     */
    public function stream_tell()
    {
        return $this->_position;
    }

    // }}}
    // {{{ stream_seek()

    /**
     * The implementation of stream_seek().
     *
     * @param integer $offset
     * @param integer $whence
     * @return boolean
     */
    public function stream_seek($offset, $whence)
    {
        switch ($whence) {
        case SEEK_SET:
            if ($offset < strlen(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox) && $offset >= 0) {
                $this->_position = $offset;
                return true;
            } else {
                return false;
            }
        case SEEK_CUR:
            if ($offset >= 0) {
                $this->_position += $offset;
                return true;
            } else {
                return false;
            }
        case SEEK_END:
            if (strlen(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox) + $offset >= 0) {
                $this->_position = strlen(Stagehand_TestRunner_Runner_PHPUnit_TestDox::$testDox) + $offset;
                return true;
            } else {
                return false;
            }
        default:
            return false;
        }        
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

stream_wrapper_register('testdox',
                        'Stagehand_TestRunner_Runner_PHPUnit_TestDox_Stream'
                        );

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
