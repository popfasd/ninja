<?php

namespace Popfasd\Ninja;

use MattFerris\Di\ContainerInterface;
use MattFerris\HttpRouting\RequestInterface;
use MattFerris\HttpRouting\Response;

class Controller
{
    protected $di;

    public function __construct(ContainerInterface $di) {
        $this->di = $di;
    }

    public function getSubmitAction(RequestInterface $request) {
        $response = new Response('This URI only accepts POST method', 405, 'text/plain');
        $response->setHeader('Allow', 'POST');
        return $response;
    }

    public function postSubmitAction(RequestInterface $request) {
        global $parameters;

        $this->di->get('FileProcessor')->process($request);
        $this->di->get('EmailProcessor')->process($request);

        $response = new Response('Form submitted', 303, 'text/plain');

        $nexturl = $request->get('nexturl');
        if (!isset($nexturl) || empty($nexturl)) {
            $prefix = str_replace('/index.php', '', $parameters['uriPrefix']);
            $nexturl = $prefix.'/public/thanks.html';
        }

        $response->setHeader('Location', $nexturl);

        return $response;
    }
}

