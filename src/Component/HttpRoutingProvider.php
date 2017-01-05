<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Component/HttpRoutingProvider.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Component;

use MattFerris\Provider\ProviderInterface;
use MattFerris\Di\ContainerInterface;
use Popfasd\Ninja\Controller;
use Zend\Diactoros\Response;

class HttpRoutingProvider implements ProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function provides($consumer)
    {
        $consumer
            ->get('/submit', Controller::class.':getSubmitAction')
            ->post('/submit', Controller::class.':postSubmitAction')
            ->any('/', function () {
                return new Response('php://memory', 404);
            });
    }
}
