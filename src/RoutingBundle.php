<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\BundleInterface;

class RoutingBundle implements BundleInterface
{
    public function provides() {
        return [
            ['method' => 'GET', 'uri' => '/submit', 'action' => '\\Popfasd\\Ninja\\Controller:getSubmitAction'],
            ['method' => 'POST', 'uri' => '/submit', 'action' => '\\Popfasd\\Ninja\\Controller:postSubmitAction']
        ];
    }
}

