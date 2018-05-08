<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Component/AuthProvider.php
 * @copyright Copyright (c) 2018 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * githubb.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Component;

use Popfasd\Ninja\Authentication\ApiTokenRequest;
use Popfasd\Ninja\Authentication\ApiTokenHandler;
use MattFerris\Provider\ProviderInterface;
use MattFerris\Configuration\ConfigurationInterface;

class AuthProvider implements ProviderInterface
{
    /**
     * @var MattFerris\Configuration\ConfigurationInterface;
     */
    protected $config;

    /**
     * @param MattFerris\Configuration\ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function provides($consumer)
    {
        if (!$this->config->has('app.auth.key')) {
            throw new RuntimeException('failed to prepare auth handler, missing auth key');
        }

        $consumer->handle(
            ApiTokenRequest::class,
            [new ApiTokenHandler($this->config->get('app.auth.key')), 'handleApiToken']
        );
    }
}

