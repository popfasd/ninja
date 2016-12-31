<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * RoutingBundle.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use MattFerris\Http\Routing\BundleInterface;
use MattFerris\Provider\ConsumerInterface;
use Zend\Diactoros\Response;

class RoutingBundle implements BundleInterface
{
    public function provides(ConsumerInterface $consumer)
    {
        $consumer
            ->get('/submit', '\\Popfasd\\Ninja\\Controller:getSubmitAction')
            ->post('/submit', '\\Popfasd\\Ninja\\Controller:postSubmitAction')
            ->any('/', function () {
                return new Response('php://memory', 404);
            });
    }
}

