<?php

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\IO\BufferIO;
use Rabus\Composer\WebUI\Controller\MainController;
use Rabus\Composer\WebUI\Controller\AjaxController;
use Rabus\Composer\WebUI\Controller\SimpleAjaxController;
use Silex\Application;
use Silex\Provider\ServiceControllerServiceProvider;

require dirname(__DIR__) . '/vendor/autoload.php';

$app = new Application;
$app->register(new ServiceControllerServiceProvider());

$app['io'] = function()
{
    $io = new NullIO;
//    $io = new BufferIO;
    return $io;
};

$app['composer_file'] = function()
{
    $file = realpath(__DIR__.'/composer.json');
    if( !file_exists($file) ){
        $file = __DIR__ . '/../composer.json';
    }
    return $file;
};


// Services
$app['composer'] = function($app)
{
    $factory = new Factory;
    return $factory->create(
        $app['io'],
        $app['composer_file']
    );
};

$app['controllers.main'] = function ($app)
{
    return new MainController($app['composer']);
};

$app['controllers.ajax'] = function ($app)
{
    return new AjaxController($app['composer']);
};

$app['controllers.simpleajax'] = function ($app)
{
    return new SimpleAjaxController($app['composer_file']);
};

// Routes
$app->get('/', 'controllers.main:indexAction');
$app->get('/api/', 'controllers.ajax:indexAction');
$app->get('/api/validate', 'controllers.simpleajax:validateAction');
$app->get('/api/composer.json', 'controllers.simpleajax:composerJsonAction');

// Run it!
$app->run();
