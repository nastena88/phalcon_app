<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

use App\Library\HttpStatusCodes;
use App\Library\ResponseCodes;
use App\Library\ResponseMessages;
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    include dirname(BASE_PATH).'/vendor/autoload.php';

    // we do not want to use .env files on production
    if (getenv('APPLICATION_ENV') !== 'production') {
        $envFile = ((getenv('APPLICATION_ENV') === 'testing') ? '.env.test' : '.env');
        $dotEnv = new Dotenv\Dotenv(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'app/env', $envFile);
        $dotEnv->load();
    }

    include APP_PATH . '/config/constants.php';

    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    $app = new Micro($di);

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    $app->response->setContentType('application/json');

    //handle invalid routes
    $app->notFound(
        function () use ($app) {
            $app->response->setStatusCode(404, HttpStatusCodes::getMessage(404))->sendHeaders();
            $app->response->setContentType('application/json');
            $app->response->setJsonContent(
                [
                'status' => 'error',
                'result' => ResponseMessages::getMessageFromCode(ResponseCodes::METHOD_NOT_IMPLEMENTED),
                ]
            );
            $app->response->send();
        }
    );

    $app->error(
        function (Exception $exception) use ($app) {
            $app->response->setContentType('application/json');
            $app->response->setStatusCode(500, HttpStatusCodes::getMessage(500))->sendHeaders();
            $app->response->setJsonContent(
                [
                'status' => 'error',
                'result' => ResponseMessages::getMessageFromCode(ResponseCodes::UNEXPECTED_ERROR),
                ]
            );
            $app->response->send();
        }
    );


    $app->handle();

} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
