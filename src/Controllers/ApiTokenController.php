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
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class ApiTokenController extends Controller
{
    /**
     * @param \Psr\Htp\Message\ServerRequestInterface $request
     */
    public function verifyAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');

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
        $token = (new Parser)->parse($fields[$keyField]);
        if (is_null($token) || !$token->verify(new Sha256(), $config->get('app.auth.key'))) {
            return $this->textResponse('Invalid API token', 401);
        }

        if (!$token->hasClaim('host')) {
            return $this->textResponse('Missing host claim in API token');
        }

        if (!$token->hasClaim('fname')) {
            return $this->textResponse('Missing fname claim in API token');
        }

        $host = $token->getClaim('host');
        $fname = $token->getClaim('fname');

        if (!preg_match('/^[a-zA-Z0-9-\.]+$/', $host)) {
            return $this->textResponse('Invalid host in API token: '.$host);
        }

        if (!preg_match('/^[a-zA-Z0-9-_]+$/', $fname)) {
            return $this->textResponse('Invalid fname in API token: '.$fname);
        }

        // verify referrer host matches API token host
        $referer = $request->getHeaderLine('Referer');
        $refererHost = (new Request($referer))
            ->getUri()
            ->getHost();

        if ($refererHost !== $host) {
            return $this->textResponse('Referer host doesn\'t match host claim in API token', 401);
        }
    }
}

