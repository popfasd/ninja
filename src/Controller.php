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
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Kispiox\Controller as KispioxController;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;

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
        $response = $this->textResponse('This URI only accepts POST method');
        return $response;
    }

    /**
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function postSubmitAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');

        $keyField = '__nfk';
        if ($config->has('app.formKeyFieldName')) {
            $keyField = $config->get('app.formKeyFieldName');
        }

        // check if form key provided
        $fields = $request->getParsedBody();
        if (!array_key_exists($keyField, $fields)) {
            return $this->textResponse('Missing form key', 401);
        }

        // validate form key
        $token = (new Parser)->parse($fields[$keyField]);
        if (is_null($token) || !$token->verify(new Sha256(), $config->get('app.auth.key'))) {
            return $this->textResponse('Invalid form key', 401);
        }

        unset($fields[$keyField]);

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

        // verify domain and name claims

        if (!$token->hasClaim('domain')) {
            return $this->textResponse('Missing domain claim in form key');
        }

        if (!$token->hasClaim('name')) {
            return $this->textResponse('Missing form name claim in form key');
        }

        $domain = $token->getClaim('domain');
        $name = $token->getClaim('name');

        if (!preg_match('/^[a-zA-Z0-9-\.]+$/', $domain)) {
            return $this->textResponse('Invalid domain in form key');
        }

        if (!preg_match('/^[a-zA-Z0-9-_]+$/', $name)) {
            return $this->textResponse('Invalid form name in form key');
        }

        // verify referrer domain matches form key domain
        $refererDomain = (new Request($referer))
            ->getUri()
            ->getHost();

        if ($refererDomain !== $domain) {
            return $this->textResponse('Referer domain doesn\'t match domain claim in form key', 401);
        }

        $formId = sha1($domain.':'.$name);
        $form = new Form($formId, $domain, $name, $fields);

        $cache = $this->container->get('FormCache');

        $settings = null;
        if (!$cache->hasForm($formId)) {
            $cache->addForm($form);
        }
        $settings = $cache->getForm($formId);

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

            $response = $this->redirectResponse($urlstr, 303);

            // by returning, we prevent anything else from running
            return $response;
        }

        $form->process();

        $nexturl = null;
        if ($settings->has('nextUrl')) {
            $nexturl = $settings->get('nextUrl');
        }

        // if no nextUrl defined, use default
        if (!isset($nexturl) || empty($nexturl)) {
            $prefix = str_replace('/index.php', '', $config->get('app.uriPrefix'));
            $path = $prefix.'public/thanks.html';
            $uri = $request->getUri()->withPath($path);
            $nexturl = (string)$uri;
        }

        $response = $this->redirectResponse($nexturl, 303);

        return $response;
    }

    /**
     * @param \Psr\Htyp\Message\ServerRequestInterface $request
     */
    public function error404Action(ServerRequestInterface $request)
    {
        return $this->textResponse('page not found', 404);
    }
}

