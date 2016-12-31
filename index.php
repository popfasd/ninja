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

$di = new MattFerris\Di\Di();
$di->setParameters($parameters);

$di->set('Dispatcher', function ($di) {
    $dispatcher = new \MattFerris\Http\Routing\Dispatcher($di);
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

MattFerris\Http\Routing\DomainEvents::setDispatcher($ed);
MattFerris\Http\Routing\DomainEventLoggerHelpers::addHelpers($di->get('EventLogger'));
Popfasd\Ninja\DomainEvents::setDispatcher($ed);
Popfasd\Ninja\DomainEventLoggerHelpers::addHelpers($di->get('EventLogger'));

$server = $_SERVER;
$server['REQUEST_URI'] = array_key_exists('q', $_GET) ? $_GET['q'] : $_SERVER['REQUEST_URI'];
$request = Zend\Diactoros\ServerRequestFactory::fromGlobals($server);

$response = $di->get('Dispatcher')->dispatch($request);

foreach (array_keys($response->getHeaders()) as $header) {
    header($header.': '.$response->getHeaderLine($header));
}

echo $response->getBody()->getContents();
