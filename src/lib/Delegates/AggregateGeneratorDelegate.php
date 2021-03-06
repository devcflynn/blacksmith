<?php namespace Delegates;

use Console\OptionReader;
use Console\GenerateCommand;
use Configuration\ConfigReaderInterface;
use Configuration\ConfigReader;
use Factories\GeneratorFactory;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Pluralizer;

/**
 * Class for delegation of application operation related
 * to executing a generation request and seeing that it is 
 * completed properly
 */
class AggregateGeneratorDelegate implements GeneratorDelegateInterface
{
        /**
     * Command that delegated the request
     * 
     * @var \Console\GenerateCommand
     */
    protected $command;

    /**
     * Configuration of generation
     * 
     * @var \Configuration\ConfigReader
     */
    protected $config;

    /**
     * Generator factory for making
     * aggregate generators
     * 
     * @var \Factories\GeneratorFactory
     */
    protected $generator_factory;

    /**
     * String containing the requested generation action
     * 
     * @var string
     */
    protected $generation_request;

    /**
     * String containing the entity name the 
     * requested generation should use
     * 
     * @var string
     */
    protected $generate_for_entity;

    /**
     * Options passed in for generation
     * 
     * @var \Console\OptionReader
     */
    protected $optionReader;

    /**
     * Filesystem object for interacting
     * with the filesystem when needed
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * Constructor to setup up our class variables
     * 
     * @param GenerateCommand           $cmd          executed command
     * @param ConfigReaderInterface     $cfg          reader of the config file
     * @param GeneratorFactory          $genFactory   generator factory  
     * @param array                     $command_args command arguments
     * @param OptionReader              $optionReader command options
     */
    public function __construct(
        GenerateCommand $cmd,
        ConfigReaderInterface $cfg,
        GeneratorFactory $genFactory,
        Filesystem $filesystem,
        array $command_args,
        OptionReader $optionReader
    ) {
        $this->command             = $cmd;
        $this->config              = $cfg;
        $this->generator_factory   = $genFactory;
        $this->filesystem          = $filesystem;
        $this->generate_for_entity = $command_args['entity'];
        $this->generation_request  = $command_args['what'];
        $this->optionReader        = $optionReader;
    }


    /**
     * Function to run the delegated operations and return boolean
     * status of result.  If false the command should comment out
     * the reasons for the failure.
     * 
     * @return bool success / failure of operations
     */
    public function run()
    {
        //check if the loaded config is valid
        if (! $this->config->validateConfig()) {
            $this->command->comment(
                'Error',
                'The loaded configuration file is invalid',
                true
            );
            return false;
        }

        //get possible generations
        $possible_aggregates = $this->config->getAvailableAggregates();

        //see if passed in command is one that is available
        if (! in_array($this->generation_request, $possible_aggregates)) {
            $this->command->comment(
                'Error',
                "{$this->generation_request} is not a valid option",
                true
            );

            $this->command->comment(
                'Error Details',
                "Please choose from: ". implode(", ", $possible_aggregates),
                true
            );
            return false;
        }

        //get all the generators for this aggregate
        $generators = $this->config->getAggregateValues($this->generation_request);

        foreach ($generators as $to_generate) {

            //get the settings, options etc.
            $settings  = $this->config->getConfigValue($to_generate);
            if ($settings === false) {
                $this->command->comment(
                    'Blacksmith',
                    'I skipped "'.$to_generate.'"'
                );
                continue;
            }

            $tplFile   = $settings[ConfigReader::CONFIG_VAL_TEMPLATE];
            $template  = implode(DIRECTORY_SEPARATOR, [$this->config->getConfigDirectory(), $tplFile]);
            $directory = $settings[ConfigReader::CONFIG_VAL_DIRECTORY];
            $filename  = $settings[ConfigReader::CONFIG_VAL_FILENAME];

            //create the generator
            $generator = $this->generator_factory->make($to_generate, $this->optionReader);

            //run generator
            $success = $generator->make(
                $this->generate_for_entity,
                $template,
                $directory,
                $filename,
                $this->optionReader
            );

            if ($success) {

                $this->command->comment(
                    'Blacksmith',
                    'Success, I generated the code for you in '. $generator->getTemplateDestination()
                );

            } else {

                $this->command->comment(
                    'Blacksmith',
                    "An unknown error occurred, nothing was generated for {$to_generate}",
                    true
                );
            }

        }//end foreach

        $collectionName = Str::plural(Str::snake($this->generate_for_entity));
        $this->updateRoutesFile($collectionName, getcwd());

        return true;
        
    }//end run function



    /**
     * Function to handle updating the routes file
     * for us
     * 
     * @param  string $name
     * @param  string $dir
     * @return void
     */
    public function updateRoutesFile($name, $dir)
    {
         $entity = strtolower(Pluralizer::singular($name));
         $name = strtolower(Pluralizer::plural($name));


        $routes = implode(DIRECTORY_SEPARATOR, [$dir, 'app', 'routes.php']);

        if ($this->filesystem->exists($routes)) {
            $route = '/**** Route and IOC Binding for '.ucwords($entity)." ****/"."\n";
            $route .= "Route::resource('" . $name . "', '" . ucwords($name) . "Controller');"."\n";
            // App binding if not setup properly
            $route .= "App::bind('Contracts\Repositories\\" . ucwords($entity) . "RepositoryInterface', 'Repositories\Db" . ucwords($entity) . "Repository');";
           
            $contents = $this->filesystem->get($routes);
            if (str_contains($contents, $route)) {
                return;
            }

            $this->filesystem->append(
                $routes,
                "\n\n".$route
            );
        }
    }


}//end class
