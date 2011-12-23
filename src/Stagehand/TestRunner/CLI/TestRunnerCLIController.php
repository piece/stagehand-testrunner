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

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Stagehand\TestRunner\Core\ApplicationContext;
use Stagehand\TestRunner\Core\ConfigurationTransformer;
use Stagehand\TestRunner\Core\Configuration\CakePHPConfiguration;
use Stagehand\TestRunner\Core\Configuration\CIUnitConfiguration;
use Stagehand\TestRunner\Core\Configuration\GeneralConfiguration;
use Stagehand\TestRunner\Core\Configuration\PHPUnitConfiguration;
use Stagehand\TestRunner\Core\Exception;

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
     * @var \Stagehand\TestRunner\Core\ConfigurationTransformer
     * @since Property available since Release 3.0.0
     */
    protected $configurationTransformer;

    /**
     * @var string
     * @since Property available since Release 3.0.0
     */
    protected $pluginID;

    /**
     * @param string $pluginID
     */
    public function __construct($pluginID)
    {
        $this->pluginID = $pluginID;
        ApplicationContext::getInstance()
            ->getEnvironment()
            ->setWorkingDirectoryAtStartup($GLOBALS['STAGEHAND_TESTRUNNER_CONFIG_workingDirectoryAtStartup']);
    }

    /**
     * @param \Stagehand\TestRunner\Core\ConfigurationTransformer $configurationTransformer
     */
    public function setConfigurationTransformer(ConfigurationTransformer $configurationTransformer)
    {
        $this->configurationTransformer = $configurationTransformer;
    }

    /**
     * @since Method available since Release 3.0.0
     */
    public function run()
    {
        $this->configurationTransformer = new ConfigurationTransformer($this->createContainer());
        $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('testing_framework' => $this->pluginID));
        return parent::run();
    }

    /**
     * @param string $option
     * @param string $value
     * @return boolean
     * @throws \Stagehand\TestRunner\Core\Exception
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
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('recursively_scans' => true));
            return true;
        case 'c':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('colors' => true));
            return true;
        case 'p':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('preload_file' => $value));
            return true;
        case 'a':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('enables_autotest' => true));
            return true;
        case 'w':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('monitoring_directories' => explode(',', $value)));
            return true;
        case 'n':
        case 'g':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('uses_notification' => true));
            return true;
        case 'm':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('test_methods' => explode(',', $value)));
            return true;
        case '--classes':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('test_classes' => explode(',', $value)));
            return true;
        case '--log-junit':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('junit_xml' => array('file' => $value)));
            return true;
        case '--log-junit-realtime':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('junit_xml' => array('realtime' => true)));
            return true;
        case '--stop-on-failure':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('stops_on_failure' => true));
            return true;
        case '--phpunit-config':
            $this->configurationTransformer->setConfigurationPart(PHPUnitConfiguration::getConfigurationID(), array('phpunit_config_file' => $value));
            return true;
        case '--cakephp-app-path':
            $this->validateDirectory($value, $option);
            $this->configurationTransformer->setConfigurationPart(CakePHPConfiguration::getConfigurationID(), array('cakephp_app_path' => $value));
            return true;
        case '--cakephp-core-path':
            $this->validateDirectory($value, $option);
            $this->configurationTransformer->setConfigurationPart(CakePHPConfiguration::getConfigurationID(), array('cakephp_core_path' => $value));
            return true;
        case '--ciunit-path':
            $this->validateDirectory($value, $option);
            $this->configurationTransformer->setConfigurationPart(CIUnitConfiguration::getConfigurationID(), array('ciunit_path' => $value));
            return true;
        case '--test-file-pattern':
            $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('test_file_pattern' => $value));
            return true;
        case 'v':
            $this->configurationTransformer->setConfigurationPart(PHPUnitConfiguration::getConfigurationID(), array('prints_detailed_progress_report' => $value));
            return true;
        }
    }

    /**
     * @param string $arg
     * @return boolean
     */
    protected function configureByArg($arg)
    {
        $this->configurationTransformer->setConfigurationPart(GeneralConfiguration::getConfigurationID(), array('test_resources' => array($arg)));
        return true;
    }

    /**
     * @throws \Stagehand\TestRunner\Core\Exception
     */
    protected function doRun()
    {
        $this->createTestRunner()->run();
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
        echo "Stagehand_TestRunner @package_version@ ({$this->pluginID})

Copyright (c) 2005-2011 KUBO Atsuhiro <kubo@iteman.jp>,
              2007 Masahiko Sakamoto <msakamoto-sf@users.sourceforge.net>,
              2010 KUMAKURA Yousuke <kumatch@gmail.com>,
              2011 Shigenobu Nishikawa <shishi.s.n@gmail.com>,
              2011 KUBO Noriko <noricott@gmail.com>,
All rights reserved.
";
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
     * @since Method available since Release 3.0.0
     */
    protected function configure(array $options, array $args)
    {
        $continues = parent::configure($options, $args);
        if ($continues) {
            ApplicationContext::getInstance()
                ->getComponentFactory()
                ->setContainer($this->configurationTransformer->transformToContainer());
            return true;
        } else {
            return false;
        }
    }

    /**
     * @return \Stagehand\TestRunner\CLI\TestRunner
     * @since Method available since Release 3.0.0
     */
    protected function createTestRunner()
    {
        return ApplicationContext::getInstance()->createComponent('test_runner');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerBuilder
     * @since Method available since Release 3.0.0
     */
    protected function createContainer()
    {
        return new ContainerBuilder();
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
