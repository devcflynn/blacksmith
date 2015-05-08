<?php namespace Console;

/**
 * This file was copied from and inspired by a similar
 * file in the Laravel / Envoy package which is released
 * under the the MIT license.
 *
 * @see  https://github.com/laravel/envoy
 */

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Factories\GeneratorDelegateFactory;
use Factories\ConfigReaderFactory;
use Factories\GeneratorFactory;
use Illuminate\Filesystem\Filesystem;

/**
 * CLI command used to build a new Laravel app
 * This check for necessary directories and loads 
 *  the necessary files if they are not available
 */
class InitializeCommand extends \Symfony\Component\Console\Command\Command
{

    use Command;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this->ignoreValidationErrors();

        $this->setName('init')
                ->setDescription('Initialize Blacksmith with appropriate configs and files within the project, (specific to hexagonal architecture)')
               ->addArgument('path', InputArgument::REQUIRED, "Location of your Domain lib path. Typically - this is typically app/lib.");
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    protected function fire()
    {
     
    }
}
