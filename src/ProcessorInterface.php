<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

interface ProcessorInterface
{
    public function process(RequestInterface $request, $fields);
}

