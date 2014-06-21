<?php

use Composer\Factory;
use Composer\IO\NullIO;
use Composer\IO\BufferIO;
use Rabus\Composer\WebUI\Controller\MainController;
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


// Services
$app['composer'] = function($app)
{
    $factory = new Factory;
    return $factory->create($app['io']);
};

$app['controllers.main'] = function ($app)
{
    return new MainController($app['composer']);
};

// Routes
$app->get('/', 'controllers.main:indexAction');

// Run it!
$app->run();
