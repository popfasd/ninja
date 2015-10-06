<?php

require('private/parameters.php');
require('vendor/autoload.php');

$di = new MattFerris\Di\Di();
$di->setParameters($parameters);

$di->set('Dispatcher', function ($di) {
    $dispatcher = new \MattFerris\HttpRouting\Dispatcher($di);
    $dispatcher->register(new \Popfasd\Ninja\RoutingBundle());
    return $dispatcher;
}, true);

$di->set('EventDispatcher', function ($di) {
    $dispatcher = new \MattFerris\Events\Dispatcher();
    return $dispatcher;
}, true);

$di->set('EventLogger', function ($di) {
    $logger = new \MattFerris\Events\Logger($di->get('EventDispatcher'));
    return $logger;
}, true);

$di->set('FileProcessor', function ($di) {
    $processor = new \Popfasd\Ninja\FileProcessor(
        new \Popfasd\Ninja\TabDelimiterFormatter(),
        $di->getParameter('formDir')
    );
    return $processor;
}, true);

$di->set('EmailProcessor', function ($di) {
    $processor = new \Popfasd\Ninja\EmailProcessor(
        new \Popfasd\Ninja\EmailFormatter(),
        $di->getParameter('formDir'),
        $di->getParameter('mailto')
    );
    return $processor;
}, true);


MattFerris\HttpRouting\DomainEvents::setDispatcher($di->get('EventDispatcher'));
MattFerris\HttpRouting\DomainEventLoggerHelpers::addHelpers($di->get('EventLogger'));

$server = $_SERVER;
$server['REQUEST_URI'] = $_GET['q'];
$request = new MattFerris\HttpRouting\Request($server);

echo $di->get('Dispatcher')->dispatch($request)->send();
