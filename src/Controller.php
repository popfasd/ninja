<?php

namespace Popfasd\Ninja;

use MattFerris\Di\ContainerInterface;
use MattFerris\HttpRouting\RequestInterface;
use MattFerris\HttpRouting\Response;

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
     * @param RequestInterface $request
     */
    public function getSubmitAction(RequestInterface $request)
    {
        $response = new Response('This URI only accepts POST method', 405, 'text/plain');
        $response->setHeader('Allow', 'POST');
        return $response;
    }

    /**
     * @param RequestInterface $request
     */
    public function postSubmitAction(RequestInterface $request)
    {
        $referer = $request->getHeader('Referer');
        $validationKey = $request->getAttribute('validationKey');

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

        $form = new Form($request, $request->getAttribute('cacheDir'));

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
            $response = new Response('Form failed validation', 303, 'text/plain');
            $response->setHeader('Location', $urlstr);

            // by returning, we prevent anything else from running
            return $response;
        }

        $form->process();

        $nexturl = $form->getNextUrl();
        if (!isset($nexturl) || empty($nexturl)) {
            $prefix = str_replace('/index.php', '', $request->getAttribute('uriPrefix'));
            $nexturl = $prefix.'/public/thanks.html';
        }

        $response = new Response('Form submitted', 303, 'text/plain');
        $response->setHeader('Location', $nexturl);

        return $response;
    }
}

