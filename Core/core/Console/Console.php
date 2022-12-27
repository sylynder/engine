<?php

/**
 * Webby Console
 * 
 * The CLI Engine for Webby
 * 
 * @author  Kwame Oteng Appiah-Nti <developerkwame@gmail.com> (Developer Kwame)
 * 
 */

namespace Base\Console;

class Console 
{

    protected static $phpCommand = 'php public/index.php ';

    private static $rootpath = '';

    protected static $cliversion = '';

    private static $env = '';

    private static $removeComposerDev = 'composer --no-dev update';

    private static $composerCommand = 'composer ';

    private const WEBBY_CLI_VERSION = '1.8.0';

    private const DEFAULT_HOST = "localhost";
    
    private const DEFAULT_PORT = 8085;

    private static $sylynderEngine = 'sylynder/engine';

    /** 
     * Grab available defined user constants
    */
    protected static function userConstants()
    {
        return (object) get_defined_constants(true)['user'];
    }

    /**
     * Webby Cli welcome message
     *
     * @return string
     */
    protected static function welcome()
    {
        // static::$cliversion = defined('WEBBY_VERSION') ? WEBBY_VERSION : static::userConstants()->WEBBY_CLI_VERSION;
        
        static::$cliversion = defined('WEBBY_VERSION') ? WEBBY_VERSION : self::WEBBY_CLI_VERSION;

        return ConsoleColor::cyan("Welcome to Webby PHP Framework") . " " . ConsoleColor::green(static::$cliversion) . "\n";
    }

    /**
     * Display when command
     * not found
     *
     * @return void
     */
    public static function noCommand(): void
    {
        $output =   " \n";
        $output .=  static::welcome() . "\n";
        $output .=  ConsoleColor::white(" Sorry this command is not known", 'light', 'red') . " \n";

        echo $output . "\n";
    }

    /**
     * Execute cli command
     *
     * @return void
     */
    public static function executeCommand()
    {
        $arguments = func_get_args();

        [$listArguments] = $arguments;

        // $count = is_countable($listArguments) ? count($listArguments) : 0;
        $count = $_SERVER['argc'];

        if ($count === 0) {
            static::noCommand();
            exit;
        }

        // Declare empty argX variables
        $arg2 = '';
        $arg3 = '';
        $arg4 = '';
        $arg5 = '';
        $arg6 = '';

        list($webby, $arg1) = $listArguments;

        if ($count === 3) {
            list($webby, $arg1, $arg2) = $listArguments;
        }

        if ($count === 4) {
            list($webby, $arg1, $arg2, $arg3) = $listArguments;
        }

        if ($count === 5) {
            list($webby, $arg1, $arg2, $arg3, $arg4) = $listArguments;
        }

        if ($count === 6) {
            list($webby, $arg1, $arg2, $arg3, $arg4, $arg5) = $listArguments;
        }

        if (!isset($webby)) 
        {
            static::noCommand();
            exit;
        }

        switch($arg1)
        {
            case '--help':
            case '-h':
                static::consoleEnv();
                
                if (!empty($arg2)) {
                    \Base\Console\Commands\Help::whichHelp($arg2);
                    exit;
                }

                \Base\Console\Commands\Help::runHelp();
            break;
            case '--version':
            case '-v':
                static::consoleEnv();

                if (!empty($arg2)) {
                    \Base\Console\Commands\Help::whichHelp($arg2);
                    exit;
                }

                \Base\Console\Commands\Help::runHelp();
            break;
            case '--env':
                static::consoleEnv();

                if (!empty($arg2)) {
                    \Base\Console\Commands\Help::whichHelp($arg2);
                    exit;
                }

                \Base\Console\Commands\Help::runHelp();
            break;
            case 'key:generate':
                static::consoleEnv();
                static::runSystemCommand(static::$phpCommand . 'key/prepare');

                if ($arg2 == "--regenerate") {
                    static::runSystemCommand(static::$phpCommand . 'key/regenerate');
                    exit;
                }

                static::runSystemCommand(static::$phpCommand . 'key');
            break;
            case 'migrate':
                static::consoleEnv();
                static::runSystemCommand(static::$phpCommand . 'migrate');
            break;
            case 'list:routes':
                static::runSystemCommand(static::$phpCommand . 'routes');
            break;
            case 'app:on':
                static::runSystemCommand(static::$phpCommand . 'maintenance/on');
            break;
            case 'app:off':
                static::runSystemCommand(static::$phpCommand . 'maintenance/off');
            break;
            case 'app:to-production':
                static::runSystemCommand(static::$phpCommand . 'environment/production');
            break;
            case 'app:to-testing':
                static::runSystemCommand(static::$phpCommand . 'environment/testing');
            break;
            case 'app:to-development':
                static::runSystemCommand(static::$phpCommand . 'environment/development');
            break;
            case 'resource:link':
                static::consoleEnv();
                static::runSystemCommand(static::$phpCommand . 'create/resourcelink');
            break;
            case 'use:command':
                if (empty($arg2)) {
                    echo ConsoleColor::red("No arguments provided!") . "\n";
                    exit;
                }
                static::runSystemCommand(static::$phpCommand . $arg2);
            break;
            case 'git:init':
                static::consoleEnv();
                static::runSystemCommand('git init');
            break;
            case 'create:package':
                static::consoleEnv();
                static::createPackage($arg2, $arg3, $arg4);
            break;
            case 'create:module':
                static::consoleEnv();
                static::createModule($arg2, $arg3, $arg4);
            break;
            case 'create:controller':
                static::consoleEnv();
                static::createController($arg2, $arg3, $arg4);
            break;
            case 'create:model':
                static::consoleEnv();
                static::createModel($arg2, $arg3, $arg4, $arg5);
            break;
            case 'create:view':
                static::consoleEnv();
                static::createView($arg2, $arg3, $arg4);
            break;
            case 'create:service':
                static::consoleEnv();
                static::createService($arg2, $arg3, $arg4);
            break;
            case 'create:action':
                static::consoleEnv();
                static::createAction($arg2, $arg3, $arg4);
            break;
            case 'create:library':
                static::consoleEnv();
                static::createLibrary($arg2, $arg3, $arg4);
            break;
            case 'create:helper':
                static::consoleEnv();
                static::createHelper($arg2, $arg3, $arg4);
            break;
            case 'create:form':
                static::consoleEnv();
                static::createForm($arg2, $arg3, $arg4);
            break;
            case 'create:rule':
                static::consoleEnv();
                static::createRule($arg2, $arg3, $arg4);
            break;
            case 'create:middleware':
                static::consoleEnv();
                static::createMiddleware($arg2, $arg3, $arg4);
            break;
            case 'create:enum':
                static::consoleEnv();
                static::createEnum($arg2, $arg3, $arg4);
            break;
            case 'create:migration':
                static::consoleEnv();
                static::createMigration($arg2, $arg3, $arg4);
            break;
            case 'run:migration':
                static::consoleEnv();
                static::runMigration($arg2, $arg3, $arg4);
            break;
            case 'update:engine':
                static::consoleEnv();
                $installOption = 'require ';
                static::runSystemCommand(static::$composerCommand . $installOption . static::$sylynderEngine);
            break;
            case 'create:jsondb':
                static::consoleEnv();
                static::createJsonDb($arg2, $arg3, $arg4);
            break;
            case 'install:package':
                static::consoleEnv();
                $installOption = 'require ';
                static::runSystemCommand(static::$composerCommand . $installOption. $arg2 . ' ' . $arg3);
            break;
            // case 'publish:package':
            //     static::consoleEnv();
            // break;
            case 'clear:cache':
                static::consoleEnv();
                static::clearCache($arg2, $arg3, $arg4);
            break;
            case 'update:composer':
                static::consoleEnv();
                static::runSystemCommand(static::$composerCommand .'self-update');
            break;
            default:
                static::noCommand();
            break;
        }
    }
    
    protected static function createPackage(...$args)
    {
        $name = '';
        $type = '';
        $with = '';

        if (isset($args[0])) {
            $name = $args[0];
        }

        if (isset($args[1])) {
            $with = $args[1];
        }

        $command = static::$phpCommand . 'create/createpackage/' . $name . '/' . $type . '/' . $with;
        static::runSystemCommand($command);
    }

    protected static function createModule(...$args)
    {
        $module = explode(':', $args[0]);
        $name = '';
        $type = '';
        $with = '';

        if (isset($module[0])) {
            $type = $module[0];
        }

        if (isset($module[1])) {
            $name = $module[1];
        }

        if (isset($args[1])) {
            $with = $args[1];
        }

        $command = static::$phpCommand . 'create/createmodule/' . $name . '/' . $type . '/' . $with;
        static::runSystemCommand($command);
    }

    protected static function createController(...$args)
    {
        $module = $args[0];
        $controllerName = '';

        $controller = str_replace('=', ':', $args[1]);
        $controller = explode(':', $controller);

        $addController = '';

        if ($controller[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:controller", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($controller[1])) {
            $controllerName = $controller[1];
        }

        if (isset($args[2])) {
            $addController = $args[2];
        }
        
        $module = str_replace('=',':', $module);
        $command = static::$phpCommand . 'create/createcontroller/' . $module . '/' . $controllerName . '/' . $addController;
        static::runSystemCommand($command);
    }

    protected static function createModel(...$args)
    {
        $module = $args[0];
        $modelName = '';

        $model = str_replace('=', ':', $args[1]);
        $model = explode(':', $model);

        $modelType = '';
        $removeModel = '';

        if ($model[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:model", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($model[1])) {
            $modelName = $model[1];
        }

        if (isset($args[2])) {
            $modelType = $args[2];
        }
        
        if (isset($args[3])) {
            $removeModel = $args[3];
        }
        
        if ($modelType == '--remove-model') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:model", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createmodel/' . $module . '/' . $modelName . '/' . $modelType. '/' . $removeModel;
        static::runSystemCommand($command);
    }

    protected static function createService(...$args)
    {
        $module = $args[0];
        $serviceName = '';

        $service = str_replace('=', ':', $args[1]);
        $service = explode(':', $service);

        if ($service[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:service", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($service[1])) {
            $serviceName = $service[1];
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createservice/' . $module . '/' . $serviceName;
        static::runSystemCommand($command);
    }

    protected static function createAction(...$args)
    {
        $module = $args[0];
        $actionName = '';

        $action = str_replace('=', ':', $args[1]);
        $action = explode(':', $action);

        $actionType = '';

        if ($action[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:action", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($action[1])) {
            $actionName = $action[1];
        }

        if (isset($args[2])) {
            $actionType = $args[2];
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createaction/' . $module . '/' . $actionName . '/' . $actionType;
        static::runSystemCommand($command);
    }

    protected static function createLibrary(...$args)
    {
        $module = $args[0];
        $libraryName = '';

        $library = str_replace('=', ':', $args[1]);
        $library = explode(':', $library);

        if ($library[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax for create:library", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($library[1])) {
            $libraryName = $library[1];
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createlibrary/' . $module . '/' . $libraryName;
        static::runSystemCommand($command);
    }

    protected static function createHelper(...$args)
    {
        $module = $args[0];
        $helperName = '';

        $helper = str_replace('=', ':', $args[1]);
        $helper = explode(':', $helper);

        $helperType = '';

        if ($helper[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:helper", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($helper[1])) {
            $helperName = $helper[1];
        }

        if (isset($args[2])) {
            $helperType = $args[2];
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createhelper/' . $module . '/' . $helperName . '/' . $helperType;
        static::runSystemCommand($command);
    }

    protected static function createForm(...$args)
    {
        $module = $args[0];
        $formName = '';

        $form = str_replace('=', ':', $args[1]);
        $form = explode(':', $form);

        if ($form[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:form", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($form[1])) {
            $formName = $form[1];
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createform/' . $module . '/' . $formName;
        static::runSystemCommand($command);
    }

    /**
     * Create Rule
     *
     * @param mixed ...$args
     * @return void
     */
    protected static function createRule(...$args)
    {
        $module = $args[0];
        $ruleName = '';

        $rule = str_replace('=', ':', $args[1]);
        $rule = explode(':', $rule);

        if ($rule[0] !== '--name') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:rule", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (isset($rule[1])) {
            $ruleName = $rule[1];
        }

        $module = str_replace('=', ':', $module);
        $command = static::$phpCommand . 'create/createrule/' . $module . '/' . $ruleName;
        static::runSystemCommand($command);
    }

    protected static function createMiddleware(...$args)
    {
        $name = '';
        $type = '';

        if (isset($args[0])) {
            $name = $args[0];
        }

        if (isset($args[1])) {
            $type = $args[1];
        }

        if ($name === '') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:middleware", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (strpos($name, '-') !== false) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:middleware", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $command = static::$phpCommand . 'create/createmiddleware/' . $name . '/' . $type;
        static::runSystemCommand($command);
    }

    protected static function createEnum(...$args)
    {
        $name = '';
        $type = '';

        if (isset($args[0])) {
            $name = $args[0];
        }

        if (isset($args[1])) {
            $type = $args[1];
        }

        if ($name === '') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:enum", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if (strpos($name, '-') !== false) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:enum", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $command = static::$phpCommand . 'create/createenum/' . $name . '/' . $type;
        static::runSystemCommand($command);
    }

    protected static function createJsonDb(...$args)
    {
        $name = '';
        $type = '';

        if (isset($args[0])) {
            $name = $args[0];
        }

        $name = str_replace(['-', '='], '', $name);

        if ($name === '') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:jsondb", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $command = static::$phpCommand . 'create/createjsondb/' . $name;
        static::runSystemCommand($command);
    }

    protected static function createMigration(...$args)
    {
        $name = '';
        $type = '';

        if (isset($args[0])) {
            $name = $args[0];
        }

        if (isset($args[1])) {
            $type = $args[1];
        }

        $name = str_replace(['-', '='], '', $name);

        if ($name === '') {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:migration", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $command = static::$phpCommand . 'create/createmigration/' . $name . '/' . $type;
        static::runSystemCommand($command);
    }

    protected static function runMigration(...$args)
    {
        $key = $args[0];
        $steps = $args[1];

        if ($key == null) {
            $command = static::$phpCommand . 'migration/run';
            static::runSystemCommand($command);
            exit;
        }

        if (!empty($steps)) {
            $steps = explode('=', $steps);
        }

        if ($key === '--rollback') {

            $step = 0;

            if (!empty($steps[0]) && $steps[0] !== '--step') {
                $output =   " \n";
                $output .=  ConsoleColor::white(" Please check docs for correct syntax to use for run:migration --rollback", 'light', 'red') . " \n";
                echo $output . "\n";
                exit;
            }
            
            if (!empty($steps[1]) && is_numeric($steps[1])) {
                $step = $steps[1];
            }

            if (!empty($step) && !is_string($step) && is_infinite($step)) {
                $command = static::$phpCommand . 'migration/rollback/' . $step;
                static::runSystemCommand($command);
                exit;
            }
  
            if (!empty($step) && is_numeric($step)) {
                $command = static::$phpCommand . 'migration/rollback/' . $step;
                static::runSystemCommand($command);
                exit;
            }

            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to use for run:migration --rollback", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;

        }

        if ($key === '--later') {
            $command = static::$phpCommand . 'migration/future';
            static::runSystemCommand($command);
            exit;
        }

        if ($key === '--status') {
            $command = static::$phpCommand . 'migration/status';
            static::runSystemCommand($command);
            exit;
        }

        if ($key === '--reset') {
            $command = static::$phpCommand . 'migration/reset';
            static::runSystemCommand($command);
            exit;
        }

        if ($key === '--latest') {
            $command = static::$phpCommand . 'migration/latest';
            static::runSystemCommand($command);
            exit;
        }

        if ($key === '--truncate') {
            $command = static::$phpCommand . 'migration/truncate';
            static::runSystemCommand($command);
            exit;
        }

        if ($key != null ) {
            $steps = explode('=', $key);
        }
        
        if ($steps[0] === '--step' && !empty($steps[1]) && is_numeric($steps[1])) {
            $steps = $steps[1];
            $command = static::$phpCommand . 'migration/run/' . $steps;
            static::runSystemCommand($command);
            exit;
        }

        $output =   " \n";
        $output .=  ConsoleColor::white(" Please check docs for correct syntax to use for run:migration", 'light', 'red') . " \n";
        echo $output . "\n";
        exit;
    }

    protected static function createView(...$args)
    {
        $view = $args;
        $filename = '';
        $module = '';

        if (strpos($view[0], ':') !== false) {
            $module = $view[0];
        }

        $viewType = '';

        if (!empty($module) && !empty($view[1]) ) {
            $filename = $view[1];
        }

        if (empty($module) && !empty($view[1])) {
            $filename = $view[0];
            $viewType = $view[1];
        }

        if (empty($module) && !empty($view[0]) ) {
            $filename = $view[0];
        }

        if (!empty($view[2])) {
            $viewType = $args[2];
        }
        
        $filename = str_replace('/', '-', $filename);
        $module = empty($module) ? 'empty' : $module;
        $command = static::$phpCommand . 'create/createview/' . $module . '/' . $filename . '/' . $viewType;

        static::runSystemCommand($command);
    }

    protected static function clearCache(...$args)
    {
        $type = $args[0];
        $file = $args[1];

        if ($type == null) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to use for clear:cache", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        if ($type == '--files') {

            $type = str_replace('-', '', $type);

            $command = static::$phpCommand . 'cache/clearpath/' . $type;
            static::runSystemCommand($command);
            exit;
        }

        if ($type == '--arrayz') {

            $type = str_replace('-', '', $type);

            $command = static::$phpCommand . 'cache/clearpath/' . $type;
            static::runSystemCommand($command);
            exit;
        }

        if ($type == '--plates') {

            $type = str_replace('-', '', $type);

            $command = static::$phpCommand . 'cache/clearpath/' . $type;
            static::runSystemCommand($command);
            exit;
        }

        if ($type == '--web') {

            $type = str_replace('-', '', $type);

            $command = static::$phpCommand . 'cache/clearpath/' . $type;
            static::runSystemCommand($command);
            exit;
        }

        $output =   " \n";
        $output .=  ConsoleColor::white(" Please check docs for correct syntax to use for clear:cache", 'light', 'red') . " \n";
        echo $output . "\n";
        exit;
    }

    /**
     * Check Console Environment
     *
     * @return void
     */
    private static function consoleEnv(): void
    {
        static::$env = static::userConstants()->ENVIRONMENT;

        if (static::$env !== 'development') {
            exit;
        }
    }

    /**
     * Set environment function
     *
     * @return void
     */
    private static function setenv(): void
    {
        static::$rootpath = static::userConstants()->WEBBY_ROOTPATH;
        
        $envExampleFile =  static::$rootpath . '/.env.example';
        $envFile = static::$rootpath . '/.env';

        if (file_exists($envFile)) {
            echo ConsoleColor::red("Environment file exists already!") . "\n";
            exit;
        }

        // Copy content from .env.example file
        // to .env file
        if (!copy($envExampleFile, $envFile)) {
            echo ConsoleColor::red("Environment was not able to be set!") . "\n";
        } else {
            echo ConsoleColor::green("Environment has been set successfully!") . "\n";
        }
    }

    /**
     * Run system command
     *
     * @param string $command
     * @return void
     */
    public static function runSystemCommand($command = ''): void
    {
        system($command);
    }

    /**
     * Serve Webby application
     *
     * @param array $args
     * @return void
     */
    private static function serve($args = []): void
    {
        static::$rootpath = static::userConstants()->WEBBY_ROOTPATH;
        
        $host = static::DEFAULT_HOST;
        $port = 0;
        $number = static::DEFAULT_PORT;
        
        if (isset($args[2]) && $args[2] === '--port') {
            $number = isset($args[3]) ? (int)$args[3] : "";
            $port = (isset($number) && is_int($number)) ? $number : static::DEFAULT_PORT;
            $port = intval($port);

        } else if ((isset($args[2]) && ($args[2] === '--host'))
            && (isset($args[2]) && $args[4] === '--port')
        ) {
            
            $host = isset($args[3]) ? (string)$args[3] : "localhost";
            $number = isset($args[5]) ? (int)$args[5] : "";
            $port = (isset($number) && is_int($number)) ? $number : static::DEFAULT_PORT;
            $port = intval($port);

        } else {
            
            $port = $number;
            $port = intval($port);

        }

        if ($port === 0) {
            echo ConsoleColor::red("\n\tPlease choose a valid port number!\n") . "\n";
            exit;
        }

        $project_dir = static::$rootpath; //__DIR__;
        $dir = realpath($project_dir . '/public/');

        $output =  ConsoleColor::green("\n\tPHP Built-In Web Server Started for Webby \n\n");
        $output .= ConsoleColor::green("\tNavigate to ");
        $output .= ConsoleColor::cyan("http://{$host}:{$port} ");
        $output .= ConsoleColor::green("to view your project.\n");
        $output .= ConsoleColor::yellow("\n\tPress Ctrl+C to stop Webby Server!");
        " \n";

        echo ConsoleColor::green($output) . "\n\n";

        static::runSystemCommand("php -S {$host}:" . $port . " -t " . $dir);
    }

    /**
     * Quit Webby Server
     *
     * @param array $args Console arguments
     * @param integer $ttq Time given to quit server
     * @return void
     */
    public static function quitServer(array $args = [], int $ttq = 10): void
    {

        $count = $ttq;
        $port = static::DEFAULT_PORT;
        
        if (isset($args[2]) && $args[2] === '--port') {
            $port = (int)$args[3];
        }

        if (isset($args[2]) && ($args[2] === '--in' || $args[2] === 'in')) {
            $count = (int)$args[3];
        }

        if (isset($args[4]) && ($args[4] === '--in' || $args[4] === 'in')) {
            $count = (int)$args[5];
        }

        echo ConsoleColor::yellow("\n\tQuitting Webby Server on Port: {$port} \n\t\t");
        
        while($count > 0)
        {
            echo ConsoleColor::yellow(".");
            sleep(1);
            $count--;
        }

        echo ConsoleColor::yellow("\n\tDone! \n\n");

        $os = (stripos(PHP_OS, "WIN") === 0) ? "WINDOWS" : "UNIX";

        static::consoleEnv();
        
        if ($os === "WINDOWS") {
            exec("netstat -ano | findstr :{$port}");
            exit;
        }

        exec("fuser -n tcp -k {$port}");

    }

    /**
     * Run Webby Console
     *
     * @param array $args
     * @return void
     */
    public static function run(array $args): void
    {
        if (
            (isset($args[2]) && $args[2] === '--host') 
                && (isset($args[4]) && $args[4] === '--port')
        ) {
            Console::serve($args);
        } else if (isset($args[2]) && $args[2] === '--port' && $args[1] !== 'quit') {
            Console::serve($args);
        } else if (isset($args[1]) && $args[1] === 'serve') {
            Console::serve();
        } else if (isset($args[1]) && $args[1] === 'set' && @$args[2] === '--env') {
            Console::setenv();
        } else if (isset($args[1]) && $args[1] === 'quit') {
            Console::quitServer($args);
        } /*else if (isset($args[1]) && $args[1] === '--help') {
            Console::showHelp();
        }*/ else if (!empty($args[1])) {
            Console::executeCommand($args);
        } else if (!isset($args[1])) {
            \Base\Console\Commands\Help::showHelp();
        } else {
            Console::noCommand();
        }
    }

}
