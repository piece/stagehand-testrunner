<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2009-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 2.7.0
 */

namespace Stagehand\TestRunner\Core;

/**
 * @package    Stagehand_TestRunner
 * @copyright  2009-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 2.7.0
 */
class Config
{
    protected $testingResources = array();
    public $recursivelyScans = false;
    protected $colors = false;
    public $preloadFile;
    public $enablesAutotest = false;
    public $monitoringDirectories = array();
    public $usesNotification = false;
    public $growlPassword;
    public $testsOnlySpecifiedMethods = false;
    public $testsOnlySpecifiedClasses = false;
    public $printsDetailedProgressReport = false;
    protected $junitXMLFile;
    protected $logsResultsInJUnitXML = false;
    protected $logsResultsInJUnitXMLInRealtime = false;
    public $framework;
    public $runnerClass;
    public $stopsOnFailure = false;
    public $phpunitConfigFile;
    public $cakephpAppPath;
    public $cakephpCorePath;

    /**
     * The path of your CIUnit tests directory.
     *
     * @var string
     * @since Property available since Release 2.16.0
     */
    public $ciunitPath;

    /**
     * The pattern of test files by a regular expression literal.
     * The regular expression literal must not contain .php.
     *
     * @var string
     * @since Property available since Release 2.16.0
     */
    public $testFilePattern;

    protected $testingMethods = array();
    protected $testingClasses = array();

    /**
     * @param string $testingMethod
     * @since Method available since Release 2.8.0
     */
    public function addTestingMethod($testingMethod)
    {
        $this->testsOnlySpecifiedMethods = true;
        $this->testingMethods[] = strtolower(ltrim($testingMethod, '\\'));
    }

    /**
     * @param string $testingClass
     * @since Method available since Release 2.8.0
     */
    public function addTestingClass($testingClass)
    {
        $this->testsOnlySpecifiedClasses = true;
        $this->testingClasses[] = strtolower(ltrim($testingClass, '\\'));
    }

    /**
     * @return boolean
     * @since Method available since Release 2.8.0
     */
    public function testsOnlySpecified()
    {
        return $this->testsOnlySpecifiedMethods || $this->testsOnlySpecifiedClasses;
    }

    /**
     * @param string $class
     * @param string $method
     * @return boolean
     * @since Method available since Release 2.10.0
     */
    public function isTestingMethod($class, $method)
    {
        foreach (array($class . '::' . $method, $method) as $fullyQualifiedMethodName) {
            if (in_array(strtolower($fullyQualifiedMethodName), $this->testingMethods)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $class
     * @return boolean
     * @since Method available since Release 2.10.0
     */
    public function isTestingClass($class)
    {
        return in_array(strtolower($class), $this->testingClasses);
    }

    /**
     * @param boolean $colors
     * @since Method available since Release 2.19.0
     */
    public function setColors($colors)
    {
        if ($colors) {
            if ($this->canColor()) {
                $this->colors = true;
            }
        } else {
            $this->colors = false;
        }
    }

    /**
     * @return boolean
     * @since Method available since Release 2.19.0
     */
    public function colors()
    {
        return $this->colors;
    }

    /**
     * @return array
     * @since Method available since Release 2.20.0
     */
    public function getTestingResources()
    {
        if (count($this->testingResources) == 0) return array($this->getWorkingDirectoryAtStartup());
        return $this->testingResources;
    }

    /**
     * @param string $testingResource
     * @since Method available since Release 2.20.0
     */
    public function addTestingResource($testingResource)
    {
        $this->testingResources[] = $testingResource;
    }

    /**
     * @return string
     * @since Method available since Release 2.20.0
     */
    public function getWorkingDirectoryAtStartup()
    {
        return @$GLOBALS['STAGEHAND_TESTRUNNER_CONFIG_workingDirectoryAtStartup'];
    }

    /**
     * @param string $junitXMLFile
     * @since Method available since Release 2.20.0
     */
    public function setJUnitXMLFile($junitXMLFile)
    {
        $this->logsResultsInJUnitXML = true;
        $this->junitXMLFile = $junitXMLFile;
    }

    /**
     * @return string
     * @since Method available since Release 2.20.0
     */
    public function getJUnitXMLFile()
    {
        return $this->junitXMLFile;
    }

    /**
     * @return boolean
     * @since Method available since Release 2.20.0
     */
    public function logsResultsInJUnitXML()
    {
        return $this->logsResultsInJUnitXML;
    }

    /**
     * @param boolean $logsResultsInJUnitXMLInRealtime
     * @since Method available since Release 2.20.0
     */
    public function setLogsResultsInJUnitXMLInRealtime($logsResultsInJUnitXMLInRealtime)
    {
        $this->logsResultsInJUnitXMLInRealtime = $logsResultsInJUnitXMLInRealtime;
    }

    /**
     * @return boolean
     * @since Method available since Release 2.20.0
     */
    public function logsResultsInJUnitXMLInRealtime()
    {
        return $this->logsResultsInJUnitXMLInRealtime;
    }

    /**
     * @param string $testingFramework
     * @since Method available since Release 3.0.0
     */
    public function setTestingFramework($testingFramework)
    {
        $this->framework = $testingFramework;
    }

    /**
     * @param boolean $recursivelyScans
     * @since Method available since Release 3.0.0
     */
    public function setRecursivelyScans($recursivelyScans)
    {
        $this->recursivelyScans = $recursivelyScans;
    }

    /**
     * @param string $preloadFile
     * @since Method available since Release 3.0.0
     */
    public function setPreloadFile($preloadFile)
    {
        $this->preloadFile = $preloadFile;
    }

    /**
     * @param boolean $enablesAutotest
     * @since Method available since Release 3.0.0
     */
    public function setEnablesAutotest($enablesAutotest)
    {
        $this->enablesAutotest = $enablesAutotest;
    }

    /**
     * @param array $monitoringDirectories
     * @since Method available since Release 3.0.0
     */
    public function setMonitoringDirectories(array $monitoringDirectories)
    {
        $this->monitoringDirectories = $monitoringDirectories;
    }

    /**
     * @param boolean $usesNotification
     * @since Method available since Release 3.0.0
     */
    public function setUsesNotification($usesNotification)
    {
        $this->usesNotification = $usesNotification;
    }

    /**
     * @param string $growlPassword
     * @since Method available since Release 3.0.0
     */
    public function setGrowlPassword($growlPassword)
    {
        $this->growlPassword = $growlPassword;
    }

    /**
     * @param array $testMethods
     * @since Method available since Release 3.0.0
     */
    public function setTestMethods(array $testMethods)
    {
        foreach ($testMethods as $testMethod) {
            $this->addTestingMethod($testMethod);
        }
    }

    /**
     * @param array $testClasses
     * @since Method available since Release 3.0.0
     */
    public function setTestClasses(array $testClasses)
    {
        foreach ($testClasses as $testClass) {
            $this->addTestingClass($testClass);
        }
    }

    /**
     * @param boolean $stopsOnFailure
     * @since Method available since Release 3.0.0
     */
    public function setStopsOnFailure($stopsOnFailure)
    {
        $this->stopsOnFailure = $stopsOnFailure;
    }

    /**
     * @param string $phpunitConfigFile
     * @since Method available since Release 3.0.0
     */
    public function setPHPUnitConfigFile($phpunitConfigFile)
    {
        $this->phpunitConfigFile = $phpunitConfigFile;
    }

    /**
     * @param string $cakephpAppPath
     * @since Method available since Release 3.0.0
     */
    public function setCakePHPAppPath($cakephpAppPath)
    {
        $this->cakephpAppPath = $cakephpAppPath;
    }

    /**
     * @param string $cakephpCorePath
     * @since Method available since Release 3.0.0
     */
    public function setCakePHPCorePath($cakephpCorePath)
    {
        $this->cakephpCorePath = $cakephpCorePath;
    }

    /**
     * @param string $ciunitPath
     * @since Method available since Release 3.0.0
     */
    public function setCIUnitPath($ciunitPath)
    {
        $this->ciunitPath = $ciunitPath;
    }

    /**
     * @param string $testFilePattern
     * @since Method available since Release 3.0.0
     */
    public function setTestFilePattern($testFilePattern)
    {
        $this->testFilePattern = $testFilePattern;
    }

    /**
     * @param boolean $printsDetailedProgressReport
     * @since Method available since Release 3.0.0
     */
    public function setPrintsDetailedProgressReport($printsDetailedProgressReport)
    {
        $this->printsDetailedProgressReport = $printsDetailedProgressReport;
    }

    /**
     * @param array $testResources
     * @since Method available since Release 3.0.0
     */
    public function setTestResources(array $testResources)
    {
        $this->testingResources = $testResources;
    }

    /**
     * @return boolean
     * @since Method available since Release 2.19.0
     */
    protected function canColor()
    {
        return @include_once 'Console/Color.php';
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
