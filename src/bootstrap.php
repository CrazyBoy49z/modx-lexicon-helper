<?php

ini_set('display_errors', 1);

use LCI\MODX\Console\Console;
use LCI\MODX\LexiconHelper\Console\Application;

$includeIfReadable = function($file) {
    return is_readable($file) ? include $file : false;
};

if ((!$loader = $includeIfReadable(__DIR__.'/../vendor/autoload.php')) && (!$loader = $includeIfReadable(__DIR__.'/../../../autoload.php'))) {
    echo 'You must set up the project dependencies, run the following commands:'.PHP_EOL.
        'curl -sS https://getcomposer.org/installer | php'.PHP_EOL.
        'php composer.phar install'.PHP_EOL;
    exit(1);
}

if (!defined('ORCHESTRATOR_AUTOLOAD')) {
    define('ORCHESTRATOR_AUTOLOAD', true);
}


$console = new Console();
$console->registerPackageCommands('LCI\MODX\LexiconHelper\Console\ActivePackageCommands');

/** @var Application $application */
$application = new Application($console);
$application->loadCommands();
$application->run();