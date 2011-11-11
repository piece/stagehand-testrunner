<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5.3
 *
 * Copyright (c) 2007-2011 KUBO Atsuhiro <kubo@iteman.jp>,
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
 * @copyright  2007-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      File available since Release 0.5.0
 */

namespace Stagehand\TestRunner\CLI;

use Stagehand\TestRunner\Process\Autotest;
use Stagehand\TestRunner\Core\ComponentFactory;
use Stagehand\TestRunner\Core\Config;
use Stagehand\TestRunner\Core\ConfigurationTransformer;
use Stagehand\TestRunner\Core\Exception;
use Stagehand\TestRunner\Process\TestRunner;
use Stagehand\TestRunner\Util\OutputBuffering;

/**
 * A testrunner script to run tests automatically.
 *
 * @package    Stagehand_TestRunner
 * @copyright  2007-2011 KUBO Atsuhiro <kubo@iteman.jp>
 * @license    http://www.opensource.org/licenses/bsd-license.php  New BSD License
 * @version    Release: @package_version@
 * @since      Class available since Release 0.5.0
 */
class TestRunnerCLIController extends \Stagehand_CLIController
{
    protected $exceptionClass = '\Stagehand\TestRunner\Core\Exception';
    protected $shortOptions = 'hVRcp:aw:gm:vn';
    protected $longOptions =
        array(
            'growl-password=',
            'log-junit=',
            'log-junit-realtime',
            'classes=',
            'stop-on-failure',
            'phpunit-config=',
            'cakephp-app-path=',
            'cakephp-core-path=',
            'test-file-pattern=',
            'ciunit-path=',
        );

    /**
     * @var \Stagehand\TestRunner\Core\Config
     */
    protected $config;

    /**
     * @var \Stagehand\TestRunner\Core\ConfigurationTransformer
     * @since Property available since Release 3.0.0
     */
    protected $configurationTransformer;

    /**
     * @var string
     * @since Property available since Release 3.0.0
     */
    protected $testingFramework;

    /**
     * @param string $framework
     */
    public function __construct($framework)
    {
        $this->configurationTransformer = new ConfigurationTransformer();
        $this->configurationTransformer->setConfigurationPart(array('testing_framework' => $framework));
        $this->testingFramework = $framework;
    }

    /**
     * @param string $option
     * @param string $value
     * @return boolean
     */
    protected function configureByOption($option, $value)
    {
        switch ($option) {
        case 'h':
            $this->printUsage();
            return false;
        case 'V':
            $this->printVersion();
            return false;
        case 'R':
            $this->configurationTransformer->setConfigurationPart(array('recursively_scans' => true));
            return true;
        case 'c':
            $this->configurationTransformer->setConfigurationPart(array('colors' => true));
            return true;
        case 'p':
            $this->configurationTransformer->setConfigurationPart(array('preload_file' => $value));
            return true;
        case 'a':
            $this->configurationTransformer->setConfigurationPart(array('enables_autotest' => true));
            return true;
        case 'w':
            $this->configurationTransformer->setConfigurationPart(array('monitoring_directories' => explode(',', $value)));
            return true;
        case 'n':
        case 'g':
            $this->configurationTransformer->setConfigurationPart(array('uses_notification' => true));
            return true;
        case '--growl-password':
            $this->configurationTransformer->setConfigurationPart(array('growl_password' => $value));
            return true;
        case 'm':
            $this->configurationTransformer->setConfigurationPart(array('test_methods' => explode(',', $value)));
            return true;
        case '--classes':
            $this->configurationTransformer->setConfigurationPart(array('test_classes' => explode(',', $value)));
            return true;
        case '--log-junit':
            $this->configurationTransformer->setConfigurationPart(array('junit_xml' => array('file' => $value)));
            return true;
        case '--log-junit-realtime':
            $this->configurationTransformer->setConfigurationPart(array('junit_xml' => array('realtime' => true)));
            return true;
        case '--stop-on-failure':
            $this->configurationTransformer->setConfigurationPart(array('stops_on_failure' => true));
            return true;
        case '--phpunit-config':
            $this->configurationTransformer->setConfigurationPart(array('phpunit_config_file' => $value));
            return true;
        case '--cakephp-app-path':
            $this->validateDirectory($value, $option);
            $this->configurationTransformer->setConfigurationPart(array('cakephp_app_path' => $value));
            return true;
        case '--cakephp-core-path':
            $this->validateDirectory($value, $option);
            $this->configurationTransformer->setConfigurationPart(array('cakephp_core_path' => $value));
            return true;
        case '--ciunit-path':
            $this->validateDirectory($value, $option);
            $this->configurationTransformer->setConfigurationPart(array('ciunit_path' => $value));
            return true;
        case '--test-file-pattern':
            $this->configurationTransformer->setConfigurationPart(array('test_file_pattern' => $value));
            return true;
        case 'v':
            $this->configurationTransformer->setConfigurationPart(array('prints_detailed_progress_report' => $value));
            return true;
        }
    }

    /**
     * @param string $arg
     * @return boolean
     */
    protected function configureByArg($arg)
    {
        $this->configurationTransformer->setConfigurationPart(array('test_resources' => array($arg)));
        return true;
    }

    /**
     */
    protected function doRun()
    {
        $this->configurePHPRuntimeConfiguration();

        if (!$this->config->enablesAutotest) {
            $this->runTests();
        } else {
            $autotest = $this->createAutotest($this->config);
            $autotest->runTests();
            $autotest->monitorAlteration();
        }
    }

    /**
     * Prints the usage.
     */
    protected function printUsage()
    {
        echo "USAGE
  {$_SERVER['SCRIPT_NAME']} [OPTIONS] DIRECTORY_OR_FILE1 DIRECTORY_OR_FILE2 ...

NOTES
  If no directories and files are given, {$_SERVER['SCRIPT_NAME']} runs all the tests
  in the current directory.

OPTIONS

  -h
     Prints this help and exit.

  -V
     Prints version information and exit.

  -R
     Recursively runs tests in the specified directory.

  -c
     Colors the output.

  -p FILE
     Preloads FILE before running tests.

  -a
     Monitors for changes in the specified directories and run tests when changes
     are detected.

  -w DIRECTORY1,DIRECTORY2,...
     Specifies one or more directories to be monitored for changes.

  -n
  -g
     Notifies test results by using the growlnotify command in Mac OS X and Windows
     or the notify-send command in Linux.

  --growl-password=PASSWORD
     Specifies PASSWORD for Growl.

  -m METHOD1,METHOD2,...
     Runs only the specified tests in the specified file.
     (PHPUnit, CIUnit, SimpleTest, CakePHP)

  --classes=CLASS1,CLASS2,...
     Runs only the specified test classes in the specified file.
     (PHPUnit, CIUnit, SimpleTest, CakePHP)

  --log-junit=FILE
     Logs test results into the specified file in the JUnit XML format.
     (PHPUnit, CIUnit, SimpleTest, and CakePHP)

  --log-junit-realtime
     Logs test results in real-time into the specified file in the JUnit XML format.
     (PHPUnit, CIUnit, SimpleTest, and CakePHP)

  -v
     Prints detailed progress report.
     (PHPUnit and CIUnit)

  --stop-on-failure
     Stops the test run when the first failure or error is raised.
     (PHPUnit, CIUnit, SimpleTest, and CakePHP)

  --phpunit-config=FILE
     Configures the PHPUnit runtime environment by the specified XML configuration
     file.
     (PHPUnit and CIUnit)

  --cakephp-app-path=DIRECTORY
     Specifies the path of your app folder.
     By default, the current working directory is used.
     (CakePHP)

  --cakephp-core-path=DIRECTORY
     Specifies the path of your CakePHP libraries folder (/path/to/cake).
     By default, the \"cake\" directory under the parent directory of your app
     folder is used. (/path/to/app/../cake)
     (CakePHP)

  --ciunit-path=DIRECTORY
     Specifies the path of your CIUnit tests directory.
     By default, the current working directory is used.
     (CIUnit)

  --test-file-pattern=PATTERN
     Specifies the pattern of your test files by a regular expression literal.
     The default values are:
       PHPUnit: Test(?:Case)?\.php$
       CIUnit:  ^test.+\.php$
       SimpleTest: Test(?:Case)?\.php$
       CakePHP: \.test\.php$
       PHPSpec: Spec\.php$
     (PHPUnit, CIUnit, SimpleTest, CakePHP, and PHPSpec)
";
    }

    /**
     * Prints the version.
     */
    protected function printVersion()
    {
        echo "Stagehand_TestRunner @package_version@ ({$this->testingFramework})

Copyright (c) 2005-2011 KUBO Atsuhiro <kubo@iteman.jp>,
              2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>,
              2010 KUMAKURA Yousuke <kumatch@gmail.com>,
              2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>,
              2011 KUBO Noriko <noricott@gmail.com>,
All rights reserved.
";
    }

    /**
     * @param \Stagehand\TestRunner\Core\Config $config
     * @return \Stagehand\TestRunner\Process\Autotest
     * @since Method available since Release 2.18.0
     */
    protected function createAutotest(Config $config)
    {
        return new Autotest($config);
    }

    /**
     * Runs tests.
     *
     * @since Method available since Release 2.1.0
     */
    protected function runTests()
    {
        $runner = new TestRunner($this->config);
        $runner->run();
    }

    /**
     * @since Method available since Release 2.14.0
     */
    protected function configurePHPRuntimeConfiguration()
    {
        ini_set('display_errors', true);
        ini_set('html_errors', false);
        ini_set('implicit_flush', true);
        ini_set('max_execution_time', 0);
        $this->createOutputBuffering()->clearOutputHandlers();
    }

    /**
     * @return \Stagehand\TestRunner\Util\OutputBuffering
     * @since Method available since Release 2.20.0
     */
    protected function createOutputBuffering()
    {
        return new OutputBuffering();
    }

    /**
     * @param string $directory
     * @param string $option
     * @throws \Stagehand\TestRunner\Core\Exception
     * @since Method available since Release 2.14.0
     */
    protected function validateDirectory($directory, $option)
    {
        if (!is_readable($directory)) {
            throw new Exception(
                      'The specified path [ ' .
                      $directory .
                      ' ] by the ' .
                      $option .
                      ' option is not found or not readable.'
                  );
        }

        if (!is_dir($directory)) {
            throw new Exception(
                      'The specified path [ ' .
                      $directory .
                      ' ] by the ' .
                      $option .
                      ' option is not a directory.'
                  );
        }
    }

    /**
     * {@inheritDoc}
     * @since Method available since Release 3.0.0
     */
    protected function configure(array $options, array $args)
    {
        $continues = parent::configure($options, $args);
        if ($continues) {
            ComponentFactory::getInstance()->setContainer($this->configurationTransformer->transformToContainer());
            $this->config = ComponentFactory::getInstance()->create('config');
            return true;
        } else {
            return false;
        }
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
