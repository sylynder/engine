<?php

use Base\Helpers\Inflector;
use Base\Console\ConsoleColor;
use Base\Controllers\ConsoleController;

class Create extends ConsoleController
{
    /**
     * Stub Constant Path
     * 
     * @var const
     */
    private const STUBPATH = COREPATH .'core'. DS . 'Console' . DS . 'Stubs'. DS;

    /**
     * Stub Path variable
     *
     * @var string
     */
    private $stubpath = self::STUBPATH;

    /**
     * File Extension variable
     *
     * @var string
     */
    private $fileExtention = '.php';

    /**
     * Set Namespace variable
     *
     * @var string
     */
    private $namespace = '';

    /**
     * Config variable
     *
     * @var string
     */
    private $config = 'Config';

    /**
     * Models variable
     *
     * @var string
     */
    private $model = 'Models';

    /**
     * Controllers variable
     *
     * @var string
     */
    private $controller = 'Controllers';


    /**
     * Commands variable
     *
     * @var string
     */
    private $command = 'Commands';

    /**
     * Views variable
     *
     * @var string
     */
    private $view = 'Views';

    /**
     * Actions variable
     *
     * @var string
     */
    private $action = 'Actions';

    /**
     * Services variable
     *
     * @var string
     */
    private $service = 'Services';

    /**
     * Libraries variable
     *
     * @var string
     */
    private $library = 'Libraries';


    /**
     * Helpers variable
     *
     * @var string
     */
    private $helper = 'Helpers';

    /**
     * Forms variable
     *
     * @var string
     */
    private $form = 'Forms';

    /**
     * Rules variable
     *
     * @var string
     */
    private $rule = 'Rules';

    /**
     * Middlewares variable
     *
     * @var string
     */
    private $middleware = 'Middleware';

    /**
     * Enums variable
     *
     * @var string
     */
    private $enum = 'Enums';

    /**
     * Migrations variable
     *
     * @var string
     */
    private $migration = 'Migrations';

    public function __construct()
    {
        parent::__construct();
        $this->onlydev();
    }

    /**
     * Failure Console Output
     *
     * @param string $message
     * @return void
     */
    private function failureOutput($message = 'Failed')
    {
        $output = "";
        $output .= ConsoleColor::red($message);
        echo $output . "\n";
    }

    /**
     * Warning Console Output
     *
     * @param string $message
     * @return void
     */
    private function warnOutput($message = 'Exists')
    {
        $output = "";
        $output .= ConsoleColor::yellow($message);
        echo $output . "\n";
    }

    /**
     * Success Console Output
     *
     * @param string $message
     * @return void
     */
    private function successOutput($message = 'Success')
    {
        $output = "";
        $output .= ConsoleColor::green($message);
        echo $output . "\n";
    }

    /**
     * Create a symlink resources path
     *
     * @return void
     */
    public function resourcelink()
    {
        $resourcesDirectory = WRITABLEPATH . 'resources' . DS;
        $publicResourcesLinkDirectory = FCPATH . 'resources';

        $created = true;

        shut_up();
        $created = symlink($resourcesDirectory, $publicResourcesLinkDirectory);
        speak_up();

        if (!$created) {

            $output =   " \n";
            $output .=  ConsoleColor::white(" Sorry symlink for resources was not created", 'light', 'red') . " \n";

            echo $output . "\n";
        }

        if ($created) {
            $output =   " \n";
            $output .=  ConsoleColor::green(" Symlink for resources created successfully") . " \n";

            echo $output . "\n";
        }
    }

    /**
     * Get Stub Templates
     *
     * @param string $type
     * @param string $stubType
     * @return mixed
     */
    private function getStub($type, $stubType)
    {

        $stublocation = $this->stubpath . $stubType .DS. $type . '.stub';

        if (file_exists($stublocation)) {
            $contents = file_get_contents($stublocation);
            return $contents;
        } else {

            $text = ($type === 'package_controller') 
                ? " Sorry, Controllers must be created manually in packages \n Even so, it's not advisable to create them here."
                : " Cannot find " . $type . " stub" ;
            $output =   " \n";
            $output .=  ConsoleColor::white($text, 'light', 'red') . " \n";
            echo $output . "\n";
            return false;
        }
    }

    /**
     * Create Files
     *
     * @param string $filePath
     * @param string $fileType
     * @param string $stubType
     * @return bool
     */
    private function createFile($filePath, $fileType, $stubType = '')
    {
        $result = false;

        $className = basename($filePath, '.php');

        $contents = ($stubType === $this->view) ? '' : "<?php \n\n";
        
        if (!empty($this->namespace)) {
            $contents .= "namespace " . $this->namespace . "; \n\n";
        }
        
        $fileContent = $this->fileContent($fileType, $className, $stubType);
        $contents .= $fileContent;
        
        $exists = file_exists($filePath);
        
        if ($exists) {
            $output =   " \n";
            $output .=  ConsoleColor::white($className . " exists already", 'light', 'red') . " \n";
            echo $output . "\n";
            return false;
        }

        try {

            $filePath = ($stubType === $this->view) ? $filePath :  $filePath . '.php';

            $result = file_put_contents($filePath, $contents);

            return ($result) ? true : false;
        } catch (Exception $ex) {
            $result = false;
        }

        return $result;
    }

    /**
     * Prepare File Contents
     *
     * @param string $fileType
     * @param string $className
     * @param string $stubType
     * @return mixed
     */
    private function fileContent($fileType, $className, $stubType)
    {

        $fileContent = $this->getStub($fileType, $stubType);

        switch ($fileType) {
            case 'controller':
            case 'web_controller':
            case 'api_controller':
            case 'console_controller':
                return str_replace('{{CONTROLLER}}', $className, $fileContent);
            break;
            case 'raw_command':
            case 'base_command':
            case 'package_command':
            case 'console_command':
                return str_replace('{{COMMAND}}', $className, $fileContent);
            break;
            case 'easy_model':
            case 'base_model':
            case 'orm_model':
            case 'json_model':
                return str_replace('{{MODEL}}', $className, $fileContent);
            break;
            case 'service':
                return str_replace('{{SERVICE}}', $className, $fileContent);
            break;
            case 'default_action':
            case 'crud_action':
            case 'job_action':
                return str_replace('{{ACTION}}', $className, $fileContent);
            break;
            case 'library':
                return str_replace('{{LIBRARY}}', $className, $fileContent);
            break;
            case 'base_helper':
                return str_replace('{{HELPER}}', $className, $fileContent);
            break;
            case 'static_helper':
                $className = substr($className, 0, -7);
                return str_replace('{{HELPER}}', $className, $fileContent);
            break;
            case 'form':
                return str_replace('{{FORM}}', $className, $fileContent);
            break;
            case 'rule':
                return str_replace('{{RULE}}', $className, $fileContent);
            break;
            case 'empty':
            case 'php':
            case 'plates':
            case 'blade':
                return $fileContent;
            break;
            case 'middleware':
            case 'web_middleware':
            case 'api_middleware':
            case 'console_middleware':
            case 'command_middleware':
                return str_replace('{{MIDDLEWARE}}', $className, $fileContent);
            break;
            case 'fake_enum':
            case 'real_enum':
                return str_replace('{{ENUM}}', $className, $fileContent);
            break;
            case 'default_migration':
                
                $className = substr($className, strpos($className, "_") + 1);
                $className = 'Migration_'. $className;

                return str_replace('{{MIGRATION}}', $className, $fileContent);
            break;
            default:
                ConsoleColor::white(" Sorry no file was created", 'light', 'red');
            exit;
        }
    }

    /**
     * Create Module Directory
     *
     * @param string $moduleName
     * @param string $app
     * @return mixed
     */
    private function createModuleDirectory($moduleName, $app)
    {
        
        $module = 'module';

        if ($app == 'Package') {
            $app = 'Packages';
            $module = 'package';
        }

        if (!in_array($app, ['Web', 'Api', 'Packages', 'Console'])) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please check docs for correct syntax to create:{$module}", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $fullpath = APPROOT . $app . DIRECTORY_SEPARATOR . ucfirst($moduleName);

        try {

            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0755, true);
            }

            return $fullpath;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Create Sub Directory
     *
     * @param string $moduleDirectory
     * @param string $with
     * @return void
     */
    private function createSubDirectory($moduleDirectory, $with = '')
    {
        if ($with === '--config') {
            static::createConfigDirectory($moduleDirectory);
        }

        if ($with == '--command') {
            static::createCommandsDirectory($moduleDirectory);
        }

        if ($with === '--m') {
            static::createModelsDirectory($moduleDirectory);
        }

        if ($with == '--c') {
            static::createControllersDirectory($moduleDirectory);
        }

        if ($with == '--s') {
            static::createServicesDirectory($moduleDirectory);
        }

        if ($with == '--a') {
            static::createActionsDirectory($moduleDirectory);
        }

        if ($with == '--l') {
            static::createLibrariesDirectory($moduleDirectory);
        }

        if ($with == '--h') {
            static::createHelpersDirectory($moduleDirectory);
        }

        if ($with == '--v') {
            static::createViewsDirectory($moduleDirectory);
        }

        if ($with == '--f') {
            static::createFormsDirectory($moduleDirectory);
        }

        if ($with == '--r') {
            static::createRulesDirectory($moduleDirectory);
        }

        if ($with === '--mc') {
            static::createModelsDirectory($moduleDirectory);
            static::createControllersDirectory($moduleDirectory);
        }

        if ($with === '--mca') {
            static::createModelsDirectory($moduleDirectory);
            static::createControllersDirectory($moduleDirectory);
            static::createActionsDirectory($moduleDirectory);
        }

        if ($with === '--mcs') {
            static::createModelsDirectory($moduleDirectory);
            static::createControllersDirectory($moduleDirectory);
            static::createServicesDirectory($moduleDirectory);
        }

        if ($with === '--mcsa') {
            static::createModelsDirectory($moduleDirectory);
            static::createControllersDirectory($moduleDirectory);
            static::createServicesDirectory($moduleDirectory);
            static::createActionsDirectory($moduleDirectory);
        }

        if ($with === '--mvc') {
            static::createModelsDirectory($moduleDirectory);
            static::createViewsDirectory($moduleDirectory);
            static::createControllersDirectory($moduleDirectory);
        }

        if ($with === '--all') {
            static::createConfigDirectory($moduleDirectory);
            static::createModelsDirectory($moduleDirectory);
            static::createControllersDirectory($moduleDirectory);
            static::createCommandsDirectory($moduleDirectory);
            static::createServicesDirectory($moduleDirectory);
            static::createActionsDirectory($moduleDirectory);
            static::createLibrariesDirectory($moduleDirectory);
            static::createHelpersDirectory($moduleDirectory);
            static::createViewsDirectory($moduleDirectory);
        }

    }

    /**
     * Create Config Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createConfigDirectory($directoryPath)
    {
        $config = $directoryPath . DS . $this->config;
        $exists = file_exists($config);

        if (!is_dir($config)) {
            mkdir($config, 0755, true) or die("Unable to create a config directory");
        }
        
        $moduleName = str_last_word($directoryPath, '/');

        ($exists) 
            ? $this->failureOutput($moduleName . " Config folder exists already ")
            : $this->successOutput($moduleName . " Config folder created successfully ");
    }

    /**
     * Create Models Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createModelsDirectory($directoryPath)
    {
        $models = $directoryPath . DS . $this->model;

        $exists = file_exists($models);

        if (!is_dir($models)) {
            mkdir($models, 0755, true) or die("Unable to create a model directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Models folder exists already ")
            : $this->successOutput($moduleName . " Models folder created successfully ");
    }

    /**
     * Create Controllers Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createControllersDirectory($directoryPath)
    {
        $controllers = $directoryPath . DS . $this->controller;

        $exists = file_exists($controllers);

        if (!is_dir($controllers)) {
            mkdir($controllers, 0755, true) or die("Unable to create a controller directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Controllers folder exists already ")
            : $this->successOutput($moduleName . " Controllers folder created successfully ");
    }

    /**
     * Create Commands Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createCommandsDirectory($directoryPath)
    {
        $commands = $directoryPath . DS . $this->command;

        $exists = file_exists($commands);

        if (!is_dir($commands)) {
            mkdir($commands, 0755, true) or die("Unable to create a command directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Commands folder exists already ")
            : $this->successOutput($moduleName . " Commands folder created successfully ");
    }

    /**
     * Create Views Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createViewsDirectory($directoryPath)
    {
        $views = $directoryPath . DS . $this->view;

        $exists = file_exists($views);

        if (!is_dir($views)) {
            mkdir($views, 0755, true) or die("Unable to create a view directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Views folder exists already ")
            : $this->successOutput($moduleName . " Views folder created successfully ");
    }

    /**
     * Create Services Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createServicesDirectory($directoryPath)
    {
        $services = $directoryPath . DS . $this->service;

        $exists = file_exists($services);

        if (!is_dir($services)) {
            mkdir($services, 0755, true) or die("Unable to create a service directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Services folder exists already ")
            : $this->successOutput($moduleName . " Services folder created successfully ");
    }

    /**
     * Create Actions Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createActionsDirectory($directoryPath)
    {
        $actions = $directoryPath . DS . $this->action;

        $exists = file_exists($actions);

        if (!is_dir($actions)) {
            mkdir($actions, 0755, true) or die("Unable to create a action directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Actions folder exists already ")
            : $this->successOutput($moduleName . " Actions folder created successfully ");
    }

    /**
     * Create Libraries Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createLibrariesDirectory($directoryPath)
    {
        $libraries = $directoryPath . DS . $this->library;

        $exists = file_exists($libraries);

        if (!is_dir($libraries)) {
            mkdir($libraries, 0755, true) or die("Unable to create a library directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Libraries folder exists already ")
            : $this->successOutput($moduleName . " Libraries folder created successfully ");
    }

    /**
     * Create Helpers Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createHelpersDirectory($directoryPath)
    {
        $helpers = $directoryPath . DS . $this->helper;

        $exists = file_exists($helpers);

        if (!is_dir($helpers)) {
            mkdir($helpers, 0755, true) or die("Unable to create a helper directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Helpers folder exists already ")
            : $this->successOutput($moduleName . " Helpers folder created successfully ");
    }

    /**
     * Create Form Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createFormsDirectory($directoryPath)
    {
        $forms = $directoryPath . DS . $this->form;

        $exists = file_exists($forms);

        if (!is_dir($forms)) {
            mkdir($forms, 0755, true) or die("Unable to create a form directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Forms folder exists already ")
            : $this->successOutput($moduleName . " Forms folder created successfully ");
    }

    /**
     * Create Rules Directory
     *
     * @param string $directoryPath
     * @return void
     */
    private function createRulesDirectory($directoryPath)
    {
        $rules = $directoryPath . DS . $this->rule;

        $exists = file_exists($rules);

        if (!is_dir($rules)) {
            mkdir($rules, 0755, true) or die("Unable to create a rule directory");
        }

        $moduleName = str_last_word($directoryPath, '/');

        ($exists)
            ? $this->failureOutput($moduleName . " Rules folder exists already ")
            : $this->successOutput($moduleName . " Rules folder created successfully ");
    }

    /**
     * Create App Root Directory
     *
     * @param string $directoryName
     * @param string $type
     * @return mixed
     */
    private function createAppRootDirectory($directoryName = '', $type = '--web')
    {
        $fullpath = APPROOT . $directoryName;

        try {

            if (!is_dir($fullpath)) {
                mkdir($fullpath, 0755, true);
            }

            return $fullpath;
        } catch (Exception $ex) {
            return false;
        }
    }

    /**
     * Create Package
     *
     * @param string $packageName
     * @param string $with
     * @param string $defaultApp
     * @return void
     */
    public function createPackage($packageName = '', $with = '', $defaultApp = '--packages')
    {
        if (empty($packageName)) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please enter a package name ", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $app = str_replace('-', '', $defaultApp);
        $app = ucfirst($app);
        
        if (contains('--', $packageName)) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Wrong package name: " . $packageName, 'light', 'red') . " \n";

            echo $output . "\n";
            exit;
        }

        $packageDirectory = $this->createModuleDirectory($packageName, $app);

        $exists = file_exists($packageDirectory);

        if ($exists) {
            $this->createSubDirectory($packageDirectory, $with);
        }

        $this->successOutput(ucfirst($packageName) . " Package created successfully");
        exit;
    }

    /**
     * Create Module
     *
     * @param string $moduleName
     * @param string $defaultApp
     * @param string $with
     * @return void
     */
    public function createModule($moduleName = '', $defaultApp = '--web', $with = '')
    {
        if (empty($moduleName)) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Please enter a module name ", 'light', 'red') . " \n";
            echo $output . "\n";
            exit;
        }

        $app = str_replace('-', '', $defaultApp);
        $app = ucfirst($app);
        
        if (contains('--', $moduleName)) {
            $output =   " \n";
            $output .=  ConsoleColor::white(" Wrong module name: " . $moduleName, 'light', 'red') . " \n";

            echo $output . "\n";
            exit;
        }
        
        $moduleDirectory = $this->createModuleDirectory($moduleName, $app);
        
        $exists = file_exists($moduleDirectory);

        if ($exists) {

            $this->createSubDirectory($moduleDirectory, $with);
        }

        $this->successOutput(ucfirst($moduleName) . " Module created successfully");
        exit;
    }

    /**
     * Create Command
     *
     * @param string $location
     * @param string $commandName
     * @param string $defaultCommand
     * @return void
     */
    public function createCommand($location = '', $commandName = '', $defaultCommand = 'raw')
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);
        
        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);
        $commandDirectory = $moduleDirectory .DS. $this->command;

        if ($exists && $moduleName != 'commands') {
            // $this->createSubDirectory($moduleDirectory, '--c');
            $this->createSubDirectory($moduleDirectory, '--command');
        } else {
            $commandDirectory = $moduleDirectory .DS;
        }

        $commandName = ucwords($commandName);

        if (contains('Command', $commandName) || contains('command', $commandName)) {
            $commandName = ucfirst(substr($commandName, 0, -7));
        }

        $commandName = $commandName . 'Command';
        
        if (file_exists($commandDirectory .DS. $commandName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($commandName). " Command exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        $moduleType = strtolower($moduleType);
        $defaultCommandStub = str_replace('-', '', $defaultCommand);

        if ($moduleType === 'api') {
            $defaultCommandStub = 'raw';
        }

        if ($moduleType === 'web') {
            $defaultCommandStub = 'raw';
        }

        if ($moduleType === 'package') {
            $defaultCommandStub = $defaultCommand;
        }

        if ($moduleType === 'console') {
            $defaultCommandStub = $defaultCommand;
        }

        if ($commandDirectory && is_dir($commandDirectory)) {
            $filePath = $commandDirectory . DS . $commandName;
            $created = $this->createFile($filePath, strtolower($defaultCommandStub.'_') .'command', $this->command); 
        }

        if ($created) {
            $this->successOutput(ucfirst($commandName) . " Command created successfully ");
            return;
        }
    }

    /**
     * Create A Non Module Command
     *
     * @param string $controllerName
     * @param string $addCommand
     * @param string $location
     * @return void
     */
    public function createNonModuleCommand($commandName = '', $commandType = '', $location = 'Console/Commands')
    {
        $created = '';

        $commandName = ucwords($commandName) . 'Command';
        $commandType = str_replace('-', '', (string)$commandType);
        $fileType = ($commandType) ? $commandType.'_command' : 'raw_command';
        
        // if ($addController == '--addcontroller') {
        //     $commandName = Inflector::singularize($commandName) . 'Controller';
        // }
        
        $this->commands = 'Console/Commands';

        $commandDirectory = $this->createAppRootDirectory($this->commands);

        $location = 'App/' . $this->commands;

        if (file_exists($commandDirectory . DS . $commandName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($commandName). " Command exists already in the " . $location . " directory");
            return;
        }

        if ($commandDirectory && is_dir($commandDirectory)) {
            $filePath = $commandDirectory . DS . $commandName;
            $created = $this->createFile($filePath, $fileType, $this->command); 
        }

        if ($created) {
            $this->successOutput(ucfirst($commandName) . " Command created successfully ");
            return;
        }
    }
    
    /**
     * Create Controller
     *
     * @param string $location
     * @param string $controllerName
     * @param string $addController
     * @return void
     */
    public function createController($location = '', $controllerName = '', $addController = '')
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);
        
        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--c');
        }

        $controllerDirectory = $moduleDirectory .DS. $this->controller;
        $controllerName = ucwords($controllerName);
        
        if ($addController == '--addcontroller') {
            $controllerName = Inflector::singularize($controllerName) . 'Controller';
        }

        if (file_exists($controllerDirectory .DS. $controllerName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($controllerName). " Controller exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($controllerDirectory && is_dir($controllerDirectory)) {
            $filePath = $controllerDirectory . DS . $controllerName;
            $created = $this->createFile($filePath, strtolower($moduleType.'_') .'controller', $this->controller); 
        }

        if ($created) {
            $this->successOutput(ucfirst($controllerName) . " Controller created successfully ");
            return;
        }
    }

    /**
     * Create A Non Module Controller
     *
     * @param string $controllerName
     * @param string $addController
     * @param string $location
     * @return void
     */
    public function createNonModuleController($controllerName = '', $addController = '', $location = 'Controllers')
    {
        $created = '';

        $controllerName = ucwords($controllerName);
        $fileType = "web_controller";

        if ($addController == '--addcontroller') {
            $controllerName = Inflector::singularize($controllerName) . 'Controller';
        }
        
        $this->controllers = 'Controllers';

        if ($addController == '--dir' && $location != 'Controllers') {
            $location = str_replace('_', ' ', $location);
            $location = ucwords($location);
            $location = str_replace(' ', '/', $location);
            $this->controllers = 'Controllers' .DS. $location;
        }

        $controllerDirectory = $this->createAppRootDirectory($this->controllers);

        $location = 'App/' . $this->controllers;

        if (file_exists($controllerDirectory . DS . $controllerName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($controllerName). " Controller exists already in the " . $location . " directory");
            return;
        }

        if ($controllerDirectory && is_dir($controllerDirectory)) {
            $filePath = $controllerDirectory . DS . $controllerName;
            $created = $this->createFile($filePath, $fileType, $this->controller); 
        }

        if ($created) {
            $this->successOutput(ucfirst($controllerName) . " Controller created successfully ");
            return;
        }
    }

    /**
     * Create A Non Module Model
     *
     * @param string $modelName
     * @param string $modelType
     * @param string $removeModel
     * @param string $location
     * @return void
     */
    public function createNonModuleModel($modelName = '', $modelType = '--easy', $removeModel = '', $location = 'Models') 
    {

        $created = '';
        $namespace = 'App\Models';
        $modelName = ucwords($modelName);
        
        $this->model = 'Models';
        $this->namespace = $namespace;

        if ($removeModel == '--dir' && $location != 'Models') {
            $location = str_replace('_', ' ', $location);
            $location = ucwords($location);
            $location = str_replace(' ', '/', $location);

            $this->model = 'Models' .DS. $location;
            $this->namespace = 'App\\' .  str_replace('/', '\\', $this->model);
            $location = $this->model;
        }

        $modelDirectory = $this->createAppRootDirectory($this->model);

        if ($removeModel == '--remove-model') {
            $modelName = $modelName;
        } else {
            $modelName = Inflector::singularize($modelName) . 'Model';
        }

        $location = 'App/' . ucwords($location);

        if (file_exists($modelDirectory . DS . $modelName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($modelName) . " exists already in the " . $location . " directory");
            return;
        }

        $this->model = 'Models';

        if ($modelDirectory && is_dir($modelDirectory)) {
            $filePath = $modelDirectory . DS . $modelName;
            $modelType = str_replace('-', '', $modelType);
            $created = $this->createFile($filePath, strtolower($modelType . '_') . 'model', $this->model);
        }

        if ($created) {
            $this->successOutput(ucfirst($modelName) . " " .ucfirst($modelType) . " Model created successfully ");
            return;
        }
    }

    /**
     * Create Model
     *
     * @param string $location
     * @param string $modelName
     * @param string $modelType
     * @param string $removeModel
     * @return void
     */
    public function createModel($location = '', $modelName = '', $modelType = '--easy', $removeModel = '') 
    {

        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--m');
        }

        $modelDirectory = $moduleDirectory . DS . $this->model;
        $modelName = ucwords($modelName);
        
        if ($removeModel == '--remove-model') {
            $modelName = $modelName;
        } else {
            $modelName = Inflector::singularize($modelName) . 'Model';
        }

        if (file_exists($modelDirectory . DS . $modelName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($modelName) . " exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($modelDirectory && is_dir($modelDirectory)) {
            $filePath = $modelDirectory . DS . $modelName;
            $modelType = str_replace('-', '', $modelType);
            $created = $this->createFile($filePath, strtolower($modelType . '_') . 'model', $this->model);
        }

        if ($created) {
            $this->successOutput(ucfirst(substr($modelName, 0, -5)) . " " .ucfirst($modelType) . " Model created successfully ");
            return;
        }
    }

    /**
     * Create Service
     *
     * @param string $location
     * @param string $serviceName
     * @param string $defaultType
     * @return void
     */
    public function createService($location = '', $serviceName = '', $defaultType = '--service')
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--s');
        }

        $serviceDirectory = $moduleDirectory . DS . $this->service;
        $serviceName = ucwords($serviceName);

        if (contains('Service', $serviceName) || contains('service', $serviceName)) {
            $serviceName = ucfirst(substr($serviceName, 0, -7));
        }

        $serviceName = Inflector::singularize($serviceName) . 'Service';
        

        if (file_exists($serviceDirectory . DS . $serviceName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($serviceName) . " exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($serviceDirectory && is_dir($serviceDirectory)) {
            $filePath = $serviceDirectory . DS . $serviceName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType), $this->service);
        }

        if ($created) {
            $this->successOutput(ucfirst(substr($serviceName, 0, -7)) . " Service created successfully ");
            return;
        }
    }

    /**
     * Create Action
     *
     * @param string $location
     * @param string $actionName
     * @param string $actionType
     * @param string $removeAction
     * @return void
     */
    public function createAction($location = '', $actionName = '', $actionType = '--default', $removeAction = '')
    {

        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--a');
        }

        $actionDirectory = $moduleDirectory . DS . $this->action;
        $actionName = ucwords($actionName);

        if ($removeAction == '--remove-action') {
            $actionName = $actionName;
        } else {
            $actionName = Inflector::singularize($actionName) . 'Action';
        }

        if (file_exists($actionDirectory . DS . $actionName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($actionName) . " exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($actionDirectory && is_dir($actionDirectory)) {
            $filePath = $actionDirectory . DS . $actionName;
            $actionType = str_replace('-', '', $actionType);
            $created = $this->createFile($filePath, strtolower($actionType . '_') . 'action', $this->action);
        }

        if ($created) {
            $this->successOutput(ucfirst(substr($actionName, 0, -6)) . " " . ucfirst($actionType) . " Action created successfully ");
            return;
        }
    }

    /**
     * Create Library
     *
     * @param string $location
     * @param string $libraryName
     * @param string $defaultType
     * @return void
     */
    public function createLibrary($location = '', $libraryName = '', $defaultType = '--library')
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--l');
        }

        $libraryDirectory = $moduleDirectory . DS . $this->library;
        $libraryName = ucwords($libraryName);

        if (file_exists($libraryDirectory . DS . $libraryName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($libraryName) . " exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($libraryDirectory && is_dir($libraryDirectory)) {
            $filePath = $libraryDirectory . DS . $libraryName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType), $this->library);
        }

        if ($created) {
            $this->successOutput(ucfirst($libraryName) . " Library created successfully ");
            return;
        }
    }

    /**
     * Create Helper
     *
     * @param string $location
     * @param string $helperName
     * @param string $helperType
     * @return void
     */
    public function createHelper($location = '', $helperName = '', $helperType = '--base')
    {

        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--h');
        }

        $helperDirectory = $moduleDirectory . DS . $this->helper;
        $helperName = ucwords($helperName);

        $helperName = $helperName . '_helper';

        if (file_exists($helperDirectory . DS . $helperName . $this->fileExtention)) {
            $this->failureOutput(ucfirst(substr($helperName, 0, -7)) . " Helper exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($helperDirectory && is_dir($helperDirectory)) {
            $filePath = $helperDirectory . DS . $helperName;
            $helperType = str_replace('-', '', $helperType);
            $created = $this->createFile($filePath, strtolower($helperType . '_') . 'helper', $this->helper);
        }

        // if ($helperType === '--static') {
        //     $helperName = substr($helperName, 0, -7);
        // }
        
        if ($created) {
            $this->successOutput(ucfirst(substr($helperName, 0, -7)) . " " . ucfirst($helperType) . " Helper created successfully ");
            return;
        }
    }

    /**
     * Create Form
     *
     * @param string $location
     * @param string $formName
     * @param string $defaultType
     * @return void
     */
    public function createForm($location = '', $formName = '', $defaultType = '--form') 
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--f');
        }

        $formDirectory = $moduleDirectory . DS . $this->form;
        $formName = ucwords($formName);
        $formName = Inflector::singularize($formName) . 'Forms';
        
        if (file_exists($formDirectory . DS . $formName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($formName) . " exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($formDirectory && is_dir($formDirectory)) {
            $filePath = $formDirectory . DS . $formName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType), $this->form);
        }

        if ($created) {
            $this->successOutput(ucfirst($formName) . " Form created successfully ");
            return;
        }
    }

    /**
     * Create Rule
     *
     * @param string $location
     * @param string $ruleName
     * @param string $defaultType
     * @return void
     */
    public function createRule($location = '', $ruleName = '', $defaultType = '--rule') 
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = $this->createModuleDirectory($moduleName, $moduleType);

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--r');
        }

        $ruleDirectory = $moduleDirectory . DS . $this->rule;
        $ruleName = ucwords($ruleName);
        $ruleName = Inflector::singularize($ruleName) . 'Rules';

        if (file_exists($ruleDirectory . DS . $ruleName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($ruleName) . " exists already in the " . ucfirst($moduleName) . " module");
            return;
        }

        if ($ruleDirectory && is_dir($ruleDirectory)) {
            $filePath = $ruleDirectory . DS . $ruleName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType), $this->rule);
        }

        if ($created) {
            $this->successOutput(ucfirst($ruleName) . " Rule created successfully ");
            return;
        }
    }

    /**
     * Create Middleware
     *
     * @param string $name
     * @param string $defaultType
     * @return void
     */
    public function createMiddleware($name = '', $defaultType = '--web') 
    {
        $middlewareName = $name;
        $created = '';

        $defaultType = str_replace('-', '', $defaultType);
        $defaultType = ucfirst($defaultType);

        $middlewareDirectory = $this->createAppRootDirectory($this->middleware);

        // $exists = file_exists($middlewareDirectory);

        $middlewareName = ucwords($middlewareName);

        if (contains('Middleware', $middlewareName) || contains('middleware', $middlewareName)) {
            $middlewareName = ucfirst(substr($middlewareName, 0, -10));
        }

        $middlewareName = ($middlewareName == 'Api') 
            ? ucwords($middlewareName) . 'Middleware'  
            : Inflector::singularize($middlewareName) . 'Middleware';

        if (file_exists($middlewareDirectory . DS . $middlewareName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($middlewareName) . " exists already in the middlewares directory");
            return;
        }

        if ($middlewareDirectory && is_dir($middlewareDirectory)) {
            $filePath = $middlewareDirectory . DS . $middlewareName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType).'_middleware', $this->middleware);
        }

        if ($created) {
            $this->successOutput(ucfirst(substr($middlewareName, 0, -10)) . " Middleware created successfully ");
            return;
        }

    }

    /**
     * Create Enum
     *
     * @param string $name
     * @param string $defaultType
     * @return void
     */
    public function createEnum($name = '', $defaultType = '--fake')
    {
        $enumName = $name;
        $created = '';

        $defaultType = str_replace('-', '', $defaultType);
        $defaultType = ucfirst($defaultType);

        $enumDirectory = $this->createAppRootDirectory($this->enum);

        // $exists = file_exists($enumDirectory);

        $enumName = ucwords($enumName);

        // $enumName = Inflector::singularize($enumName) . 'Enum';

        if (file_exists($enumDirectory . DS . $enumName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($enumName) . " Enum exists already in the enums directory");
            return;
        }

        if ($enumDirectory && is_dir($enumDirectory)) {
            $filePath = $enumDirectory . DS . $enumName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType) . '_enum', $this->enum);
        }

        if ($created) {
            $this->successOutput(ucfirst($enumName) . " Enum created successfully ");
            return;
        }
    }

    public function createMigration($name = '', $defaultType = '--default')
    {
        $migrationName = $name;
        $created = '';

        $defaultType = str_replace('-', '', $defaultType);
        $defaultType = ucfirst($defaultType);

        $migrationDirectory = ROOTPATH . 'database/migrations';

        $migrationType = config_item('migration_type');
            
        if ($migrationType == 'timestamp') {
            $migrationName = date('YmdHis') .'_'. ucfirst($migrationName);
        } else {
            $migrationName = 'Migration_' . ucfirst($migrationName);
        }

        if (file_exists($migrationDirectory . DS . $migrationName . $this->fileExtention)) {
            $this->failureOutput(ucfirst($migrationName) . " Migration file exists already in the migrations directory");
            return;
        }

        if ($migrationDirectory && is_dir($migrationDirectory)) {
            $filePath = $migrationDirectory . DS . $migrationName;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType).'_migration', $this->migration);
        }

        if ($created) {
            $this->successOutput($migrationName . " Migration file created successfully ");
            return;
        }
    }

    public function createView($location = '', $viewFile = '', $defaultType = '--empty')
    {
        $module = explode(':', $location);
        $moduleName = '';
        $moduleType = '';
        $created = '';
        $viewFile = str_replace('-', '/', $viewFile);
        $viewFile = str_replace('__', '-', $viewFile);

        $pathinfo = (object) pathinfo($viewFile);

        if (isset($module[0])) {
            $moduleType = $module[0];
        }

        if (isset($module[1])) {
            $moduleName = $module[1];
        }

        $moduleType = str_replace('-', '', $moduleType);
        $moduleType = ucfirst($moduleType);

        $moduleDirectory = ($moduleName) ? $this->createModuleDirectory($moduleName, $moduleType) : '';

        $exists = file_exists($moduleDirectory);

        if ($exists) {
            $this->createSubDirectory($moduleDirectory, '--v');
        }

        $viewDirectory = $moduleDirectory . DS . $this->view;

        if ($module[0] === 'empty') {
            $viewDirectory = rtrim(VIEWPATH, '/');
        }

        $file = $pathinfo->basename;

        if (!isset($pathinfo->dirname)) {
            $this->failureOutput("Please check docs for correct syntax to create:view");
            return;
        }

        $directory = $viewDirectory . DS . $pathinfo->dirname;

        $filename = str_ext($file, true);
        $extension = str_ext($file);

        if (file_exists($directory . DS . $file)) {
            $this->failureOutput($filename . $extension . " exists already in the " . ucfirst($moduleName ?? '') . " specified view directory");
            return;
        }

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
        
        if ($viewDirectory && is_dir($viewDirectory)) {
            $filePath = $viewDirectory . DS . $viewFile;
            $defaultType = str_replace('-', '', $defaultType);
            $created = $this->createFile($filePath, strtolower($defaultType), $this->view);
        }

        if ($created) {
            $this->successOutput(ucfirst($filename) . " View created successfully ");
            return;
        }

    }

    public function createJsonDb($name = '')
    {
        $databaseName = $name;
        $created = '';

        $db = new \Base\Json\Db;

        $created = $db->createDatabase($databaseName);
        
        if ($created === 'exists') {
            $this->warnOutput(ucfirst($databaseName) . " Database exists already ");
            return;
        }

        if (!$created) {
            $this->failureOutput(ucfirst($databaseName) . " Database was not created ");
            return;
        }

        if ($created) {
            $this->successOutput(ucfirst($databaseName) . " Database created successfully ");
            return;
        }

    }

}
