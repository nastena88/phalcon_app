<?php
use Phalcon\Loader;

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir,
        $config->application->libraryDir,
        $config->application->interfacesDir,
        $config->application->repositoriesDir,
    ]
)->register();

$loader->registerNamespaces([
    'App\Models' => $config->application->modelsDir,
    'App\Controllers' => $config->application->controllersDir,
    'App\Library' => $config->application->libraryDir,
    'App\CInterface' => $config->application->interfacesDir,
    'App\Repositories' => $config->application->repositoriesDir,
    'App\Validations' => $config->application->validationsDir,
])->register();
