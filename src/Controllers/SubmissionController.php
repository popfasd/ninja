<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Controllers/SubmissionController.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Controllers;

use Popfasd\Ninja\Form;
use MattFerris\Di\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Kispiox\Controller;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class SubmissionController extends Controller
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
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function postSubmitAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');

        $tokField = '__nat';
        if ($config->has('app.apiTokenFieldName')) {
            $tokField = $config->get('app.apiTokenFieldName');
        }

        $fields = $request->getParsedBody();
        $token = (new Parser)->parse($fields[$tokField]);
        unset($fields[$keyField]);

        $host = $token->getClaim('host');
        $fname = $token->getClaim('fname');

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

        $formId = sha1($host.'$'.$fname);
        $form = new Form($formId, $host, $fname, $fields);

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
        if (!isset($nexturl) || !is_null($nexturl) || empty($nexturl)) {
            $prefix = str_replace('/index.php', '', $config->get('app.uriPrefix'));
            $path = $prefix.'public/thanks.html';
            $uri = $request->getUri()->withPath($path);
            $nexturl = (string)$uri;
        }

        $response = $this->redirectResponse($nexturl, 303);

        return $response;
    }
}

