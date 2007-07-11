@ECHO OFF
REM $Id: pearenv.bat 15 2007-05-26 17:31:39Z iteman $

REM %~dp0 is expanded pathname of the current script under NT
set SCRIPT_DIR=%~dp0

IF "%PHP_PEAR_PHP_BIN%"=="" SET "PHP_PEAR_PHP_BIN=@php_bin@"

IF EXIST ".\testrunner1" (
  %PHP_PEAR_PHP_BIN% -d html_errors=off -d open_basedir= -q ".\testrunner1" %1 %2 %3 %4 %5 %6 %7 %8 %9
) ELSE (
  %PHP_PEAR_PHP_BIN% -d html_errors=off -d open_basedir= -q "%SCRIPT_DIR%testrunner1" %1 %2 %3 %4 %5 %6 %7 %8 %9
)

REM Local Variables:
REM mode: bat-generic
REM coding: iso-8859-1
REM indent-tabs-mode: nil
REM End:
