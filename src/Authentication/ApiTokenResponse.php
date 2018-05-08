<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Authentication/ApiTokenResponse.php
 * @copyright Copyright (c) 2018 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Authentication;

use MattFerris\Auth\Response;

class ApiTokenResponse extends Response
{
    /**
     * @const string
     */
    const AUTH_ERROR = 'encountered error during authentication';

    /**
     * @const string
     */
    const AUTH_FAILED = 'authentication failed';

    /**
     * @const string
     */
    const VERIFICATION_FAILED = 'failed to verify signature';

    /**
     * @var string
     */
    protected $status = self::AUTH_FAILED;

    /**
     * @param bool $valid
     * @param array $attributes
     * @param string $status
     */
    public function __construct($valid = false, array $attributes = [], $status = null)
    {
        parent::__construct($valid, $attributes);

        if (!is_null($status)) {
            $this->status = $status;
        }
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}

