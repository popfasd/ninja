<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Controllers/ApiTokenController.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Controllers;

use MattFerris\Di\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Kispiox\Controller;
use Popfasd\Ninja\Authentication\ApiTokenRequest;

class ApiTokenController extends Controller
{
    /**
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function verifyAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');

        // return if tokens aren't required
        if ($config->has('app.requireApiToken') && $config->get('app.requireApiToken') === false) {
            return;
        }

        $tokField = '__nat';
        if ($config->has('app.apiTokenFieldName')) {
            $keyField = $config->get('app.apiTokenFieldName');
        }

        // check if API token provided
        $fields = $request->getParsedBody();
        if (!array_key_exists($tokField, $fields)) {
            return $this->textResponse('Missing API token', 401);
        }

        // validate API token
        $response = $this->container->get('Auth')->authenticate(
            new ApiTokenRequest($fields[$tokField])
        );
        if (!$response->isValid()) {
            return $this->textResponse('Authentication failed: "'.$response->getStatus().'"');
        }

        $claims = $response->getAttributes();

        if (!array_key_exists('origin', $claims)) {
            return $this->textResponse('Missing origin claim in API token');
        }

        if (!array_key_exists('fname', $claims)) {
            return $this->textResponse('Missing fname claim in API token');
        }

        $origin = $claims['origin'];
        $fname = $claims['fname'];

        if (!preg_match('/^[a-zA-Z0-9-\.]+$/', $origin)) {
            return $this->textResponse('Invalid origin in API token: '.$host);
        }

        if (!preg_match('/^[a-zA-Z0-9-_]+$/', $fname)) {
            return $this->textResponse('Invalid fname in API token: '.$fname);
        }

        // verify referrer host matches API token origin
        $referer = $request->getHeaderLine('Referer');
        $refererHost = (new Request($referer))
            ->getUri()
            ->getHost();

        if ($refererHost !== $origin) {
            return $this->textResponse('Referer doesn\'t match origin claim in API token', 401);
        }

        return $request
            ->withAttribute('origin', $origin)
            ->withAttribute('fname', $fname);
    }
}

