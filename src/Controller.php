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
use Kispiox\Controller as KispioxController;

class Controller extends KispioxController
{
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
        $response->getBody()->write('This URI only accepts POST method');
        return $response;
    }

    /**
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function postSubmitAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');

        $referer = $request->getHeaderLine('Referer');
        $validationKey = $config->get('app.validationKey');

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

        $referer = $request->getHeaderLine('Referer');
        $formId = sha1($referer);

        $cache = $this->container->get('FormCache');
        if (!$cache->hasForm($formId)) {
            $cache->addForm($formId, []);
        }
        $settings = $cache->getForm($formId);

        $form = new Form($formId, $referer, $request->getParsedBody());

        if ($settings->has('fields')) {
            $form->setFields($settings->get('fields'));
        }

        if ($settings->has('validationRules')) {
            $form->setValidationRules($settings->get('validationRules'));
        }

        // validate the form, if it fails, redirect back to the form
        // URL with a base64 encoded JSON string in the query string
        // detailing why validation failed
        if (!$form->validate()) {
            // build the base64 encoded json string
            $details = urlencode(base64_encode(json_encode($form->getValidationErrors())));

            $url = parse_url($referer);

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

        $nexturl = null;
        if ($settings->has('nextUrl')) {
            $nexturl = $settings->get('nextUrl');
        }

        if (!isset($nexturl) || empty($nexturl)) {
            $prefix = str_replace('/index.php', '', $config->get('app.uriPrefix'));
            $path = $prefix.'public/thanks.html';
            $uri = $request->getUri()->withPath($path);
            $nexturl = (string)$uri;
        }

        $response = new Response('php://memory', 303, [
            'Content-Type' => 'text/plain',
            'Location' => $nexturl
        ]);
        $response->getBody()->write('Form submitted');

        return $response;
    }

    /**
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function error404Action(ServerRequestInterface $request)
    {
        return $this->textResponse('page not found', 404);
    }
}

