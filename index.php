<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * index.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

require('private/parameters.php');
require('vendor/autoload.php');

use MattFerris\Application\Application;
use MattFerris\Application\Component;
use MattFerris\Di\Di;
use MattFerris\Bridge\Components\Di\DiComponent;
use MattFerris\Bridge\Components\HttpRouting\HttpRoutingComponent;
use MattFerris\Bridge\Components\Events\EventsComponent;
use Popfasd\Ninja\Component\PopfasdNinjaComponent;

$di = new Di();
$di->setParameters($parameters);

$app = new Application($di, [
    EventsComponent::class,
    Component::class,
    HttpRoutingComponent::class,
    PopfasdNinjaComponent::class
]);

if (isset($_GET['q'])) {
    $_SERVER['REQUEST_URI'] = $_GET['q'];
}

$app->run([HttpRoutingComponent::class, 'run']);    
