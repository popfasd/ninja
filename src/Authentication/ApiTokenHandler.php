<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Authentication/ApiTokenHandler.php
 * @copyright Copyright (c) 2018 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Authentication;

use MattFerris\Auth\RequestInterface;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class ApiTokenHandler
{
    /**
     * @var string
     */
    protected $key;

    /**
     * @param string $key
     */
    public function __construct($key)
    {
        $this->key = $key;
    }

    /**
     * @return \MattFerris\Auth\Response
     */
    public function handleApiToken(ApiTokenRequest $request)
    {
        try {
            $token = (new Parser)->parse($request->getTokenString());
        }
        catch (\Exception $e) {
            return new ApiTokenResponse(false, [], ApiTokenResponse::AUTH_ERROR);
        }

        if (is_null($token) || !$token->verify(new Sha256(), $this->key)) {
            return new ApiTokenResponse(false, [], ApiTokenResponse::VERIFICATION_FAILED);
        }

        $claims = [];
        foreach ($token->getClaims() as $name => $value) {
            $claims[$name] = (string)$value;
        }

        return new ApiTokenResponse(true, $claims);
    }
}

