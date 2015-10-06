<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

interface ProcessorInterface
{
    /**
     * @param RequestInterface $request
     * @param array $fields
     */
    public function process(RequestInterface $request, array $fields = null);
}

