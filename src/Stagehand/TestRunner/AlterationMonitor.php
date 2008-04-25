<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2008 KUBO Atsuhiro <iteman@users.sourceforge.net>,
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
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    SVN: $Id$
 * @since      File available since Release 2.1.0
 */

require_once 'Stagehand/TestRunner/Exception.php';
require_once 'Stagehand/TestRunner/DirectoryScanner.php';
require_once 'Stagehand/TestRunner/DirectoryScanner/Exception.php';

// {{{ Stagehand_TestRunner_AlterationMonitor

/**
 * The file and directory alteration monitor.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2008 KUBO Atsuhiro <iteman@users.sourceforge.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 2.1.0
 */
class Stagehand_TestRunner_AlterationMonitor
{

    // {{{ constants

    const SCAN_INTERVAL_MIN = 5;

    // }}}
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

    private $_directories;
    private $_command;
    private $_currentElements;
    private $_previousElements;
    private $_isFirstTime = true;
    private $_scanInterval = self::SCAN_INTERVAL_MIN;
    private $_directoryScanner;

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ __construct()

    /**
     * Sets one or more target directories and command string to the properties.
     *
     * @param array  $directories
     * @param string $command
     */
    public function __construct($directories, $command)
    {
        $this->_directories = $directories;
        $this->_command = $command;
        $this->_directoryScanner = new Stagehand_TestRunner_DirectoryScanner(array($this, 'detectChanges'));
    }

    // }}}
    // {{{ monitor()

    /**
     * Watches for changes in the target directories and runs tests in the test
     * directory recursively when changes are detected.
     */
    public function monitor()
    {
        $this->_runTests();

        while (true) {
            print '
Waiting for changes in the following directories:
  - ' . implode("\n  - ", $this->_directories) . "\n";

            $this->_waitForChanges();

            print "Any changes are detected!
Running tests by the command [ {$this->_command} ] ...

";
            $this->_runTests();
        }
    }

    // }}}
    // {{{ detectChanges()

    /**
     * Detects any changes of a file or directory immediately.
     *
     * @param string $element
     * @throws Stagehand_TestRunner_Exception
     * @throws Stagehand_TestRunner_DirectoryScanner_Exception
     */
    public function detectChanges($element)
    {
        if (!$this->_isFirstTime) {
            $perms = fileperms($element);
            if ($perms === false) {
                throw new Stagehand_TestRunner_DirectoryScanner_Exception();
            }

            if (!array_key_exists($element, $this->_previousElements)) {
                throw new Stagehand_TestRunner_Exception();
            }

            if ($this->_previousElements[$element]['perms'] != $perms) {
                throw new Stagehand_TestRunner_Exception();
            }

            if (!is_dir($element)) {
                $mtime = filemtime($element);
                if ($mtime === false) {
                    throw new Stagehand_TestRunner_DirectoryScanner_Exception();
                }

                if ($this->_previousElements[$element]['mtime'] != $mtime) {
                    throw new Stagehand_TestRunner_Exception();
                }
            }
        }

        $perms = fileperms($element);
        if ($perms === false) {
            throw new Stagehand_TestRunner_DirectoryScanner_Exception();
        }

        $this->_currentElements[$element]['perms'] = $perms;
        if (!is_dir($element)) {
            $mtime = filemtime($element);
            if ($mtime === false) {
                throw new Stagehand_TestRunner_DirectoryScanner_Exception();
            }

            $this->_currentElements[$element]['mtime'] = $mtime;
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

    // }}}
    // {{{ _runTests()

    /**
     * Runs tests in the directory recursively.
     */
    private function _runTests()
    {
        passthru($this->_command);
    }

    // }}}
    // {{{ _waitForChanges()

    /**
     * Watches for changes in the target directories and returns immediately when
     * changes are detected.
     */
    private function _waitForChanges()
    {
        $this->_previousElements = array();
        $this->_isFirstTime = true;

        while (true) {
            sleep($this->_scanInterval);
            clearstatcache();

            try {
                $this->_currentElements = array();
                $startTime = time();
                foreach ($this->_directories as $directory) {
                    $this->_directoryScanner->scan($directory);
                }
                $endTime = time();
                $elapsedTime = $endTime - $startTime;
                if ($elapsedTime > self::SCAN_INTERVAL_MIN) {
                    $this->_scanInterval = $elapsedTime;
                }
            } catch (Stagehand_TestRunner_Exception $e) {
                return;
            }

            if (!$this->_isFirstTime) {
                reset($this->_previousElements);
                while (list($key, $value) = each($this->_previousElements)) {
                    if (!array_key_exists($key, $this->_currentElements)) {
                        return;
                    }
                }
            }

            $this->_previousElements = $this->_currentElements;
            $this->_isFirstTime = false;
        }
    }

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
