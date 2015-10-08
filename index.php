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

$di->set('AdminNotifyListener', function ($di) {
    $listener = new Popfasd\Ninja\AdminNotifyListener(
        $di->getParameter('mailto')
    );
    return $listener;
}, true);

$di->set('FileListener', function ($di) {
    return new Popfasd\Ninja\FileListener();
}, true);

$di->set('SubmissionReceiptListener', function ($di) {
    return new Popfasd\Ninja\SubmissionReceiptListener();
}, true);

$ed = $di->get('EventDispatcher');

$ed->addListener('Popfasd.Ninja.SubmissionProcessedEvent', array(
    $di->get('AdminNotifyListener'), 'onSubmissionProcessed'
));
$ed->addListener('Popfasd.Ninja.SubmissionProcessedEvent', array(
    $di->get('FileListener'), 'onSubmissionProcessed'
));
$ed->addListener('Popfasd.Ninja.SubmissionProcessedEvent', array(
    $di->get('SubmissionReceiptListener'), 'onSubmissionProcessed'
));

MattFerris\HttpRouting\DomainEvents::setDispatcher($ed);
MattFerris\HttpRouting\DomainEventLoggerHelpers::addHelpers($di->get('EventLogger'));
Popfasd\Ninja\DomainEvents::setDispatcher($ed);
Popfasd\Ninja\DomainEventLoggerHelpers::addHelpers($di->get('EventLogger'));

$server = $_SERVER;
$server['REQUEST_URI'] = $_GET['q'];
$request = new Popfasd\Ninja\Request($server);

foreach ($parameters as $key => $val) {
    $request->setAttribute($key, $val);
}

echo $di->get('Dispatcher')->dispatch($request)->send();
