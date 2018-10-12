<?php

namespace LCI\MODX\LexiconHelper\Console;

use LCI\MODX\Console\Application;
use LCI\MODX\Console\Command\PackageCommands;
use LCI\MODX\Console\Console;

class ActivePackageCommands implements PackageCommands
{
    /** @var Console  */
    protected $console;

    /** @var array  */
    protected $commands = [
        'LCI\MODX\LexiconHelper\Console\Command\CSVCommand',
        'LCI\MODX\LexiconHelper\Console\Command\CompareCommand'
    ];

    public function __construct(Console $console)
    {
        $this->console = $console;
    }

    /**
     * @return array ~ of Fully qualified names of all command class
     */
    public function getAllCommands()
    {
        return $this->commands;
    }

    /**
     * @return array ~ of Fully qualified names of active command classes. This could differ from all if package creator
     *      has different commands based on the state like the DB. Example has Install and Uninstall, only one would
     *      be active/available depending on the state
     */
    public function getActiveCommands()
    {
        return $this->commands;
    }

    /**
     * @param \LCI\MODX\Console\Application $application
     * @return \LCI\MODX\Console\Application
     */
    public function loadActiveCommands(Application $application)
    {
        $commands = $this->getActiveCommands();

        foreach ($commands as $command) {
            $class = new $command();

            if (is_object($class) ) {
                if (method_exists($class, 'setConsole')) {
                    $class->setConsole($this->console);
                }

                $application->add($class);
            }
        }

        return $application;
    }
}