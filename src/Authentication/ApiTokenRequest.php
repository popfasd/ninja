<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Authentication/ApiTokenRequest.php
 * @copyright Copyright (c) 2018 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Authentication;

use MattFerris\Auth\RequestInterface;

class ApiTokenRequest implements RequestInterface
{
    /**
     * @var string
     */
    protected $tokenString;

    /**
     * @param string $tokenString;
     */
    public function __construct($tokenString)
    {
        $this->tokenString = $tokenString;
    }

    /**
     * @return string
     */
    public function getTokenString()
    {
        return $this->tokenString;
    }
}

