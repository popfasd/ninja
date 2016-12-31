<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Controller.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use MattFerris\Di\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class Controller
{
    /**
     * @var ContainerInterface $di
     */
    protected $di;

    /**
     * @param ContainerInterface $di
     */
    public function __construct(ContainerInterface $di)
    {
        $this->di = $di;
    }

    /**
     * @param array $url
     */
    protected function assembleUrl($url)
    {
        // rebuild the URL
        $urlstr = '';
        if (isset($url['host'])) {
            if (isset($url['scheme'])) {
                $urlstr = $url['scheme'].'://';
            } else {
                $urlstr = '//';
            }

            if (isset($url['user'])) {
                $urlstr .= $url['user'];
                if (isset($url['pass'])) {
                    $urlstr .= ':'.$url['pass'];
                }
                $urlstr .= '@';
            }

            $urlstr .= $url['host'];

            if (isset($url['port'])) {
                $urlstr .= ':'.$url['port'];
            }
        }

        if (isset($url['path'])) {
            $urlstr .= $url['path'];
        } else {
            $urlstr .= '/';
        }

        if (isset($url['query']) && !empty($url['query'])) {
            $urlstr .= '?'.$url['query'];
        }

        if (isset($url['fragment'])) {
            $urlstr .= '#'.$url['fragment'];
        }

        return $urlstr;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function getSubmitAction(ServerRequestInterface $request)
    {
        $response = new Response('php://memory', 405, [
            'Content-Type' => 'text/plain',
            'Allow' => 'POST'
        ]);
        $response->getBody()->write('This URI only accets POST method');
        return $response;
    }

    /**
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function postSubmitAction(ServerRequestInterface $request)
    {
        $referer = $request->getHeaderLine('Referer');
        $validationKey = $this->di->getParameter('validationKey');

        // if validation has already failed once, the form's URL will
        // include the validation details and therefore produce a new
        // sha1 hash, so we need to strip the validation out if it
        // exists
        if (strpos($referer, $validationKey.'=') !== false) {
            $url = parse_url($referer);
            if (isset($url['query'])) {
                $query = explode('&', $url['query']);
                foreach ($query as $i => $kv) {
                    if (strpos($kv, $validationKey.'=') === 0) {
                        unset($query[$i]);
                        break;
                    }
                }
                $url['query'] = implode('&', $query);
            }
            $referer = $this->assembleUrl($url);
            $request = $request->withHeader('Referer', $referer);
        }

        $form = new Form($request, $this->di->getParameter('cacheDir'));

        // validate the form, if it fails, redirect back to the form
        // URL with a base64 encoded JSON string in the query string
        // detailing why validation failed
        if (!$form->validate()) {
            // build the base64 encoded json string
            $details = urlencode(base64_encode(json_encode($form->getValidationErrors())));

            $url = parse_url($form->getUrl());

            $query = [];
            if (isset($url['query'])) {
                $query = explode('&', $url['query']);
            };
            $query[] = $validationKey.'='.$details;
            $url['query'] = implode('&', $query);

            $urlstr = $this->assembleUrl($url);

            // generate the response to redirect the user back to the form URL
            $response = new Response('php://memory', 303, [
                'Content-Type' => 'text/plain',
                'Location' => $urlstr]
            );
            $response->getBody()->write('Form failed validation');

            // by returning, we prevent anything else from running
            return $response;
        }

        $form->process();

        $nexturl = $form->getNextUrl();
        if (!isset($nexturl) || empty($nexturl)) {
            $prefix = str_replace('/index.php', '', $this->di->getParameter('uriPrefix'));
            $nexturl = $prefix.'/public/thanks.html';
        }

        $response = new Response('php://memory', 303, [
            'Content-Type' => 'text/plain',
            'Location' => $nexturl
        ]);
        $response->getBody()->write('Form submitted');

        return $response;
    }
}

