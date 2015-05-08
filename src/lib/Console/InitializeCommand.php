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

        $this->setName('init_hex')
                ->setDescription('Initialize Blacksmith with appropriate configs and files within the project, (specific to hexagonal architecture)')
               ->addArgument('path', InputArgument::OPTIONAL, "Location of your Domain lib path. Defaults to: app/lib.");
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    protected function fire()
    {
        // Simplest Solution here,
        $args = $this->input->getArguments();
        $path = (in_array('path', $args)) ? $args['path'] : 'app/lib';
        
        shell_exec('git clone https://github.com/devcflynn/blacksmith-contracts.git '.$path.'; cd '.$path.'; rm README.md; rm -rf .git');
    }
}
