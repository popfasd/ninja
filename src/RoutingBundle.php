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

use MattFerris\HttpRouting\BundleInterface;

class RoutingBundle implements BundleInterface
{
    public function provides()
    {
        return [
            ['method' => 'GET', 'uri' => '/submit', 'action' => '\\Popfasd\\Ninja\\Controller:getSubmitAction'],
            ['method' => 'POST', 'uri' => '/submit', 'action' => '\\Popfasd\\Ninja\\Controller:postSubmitAction']
        ];
    }
}

