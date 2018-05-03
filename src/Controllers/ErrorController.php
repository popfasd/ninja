<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Controllers/ErrorController.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Controllers;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Kispiox\Controller;

class ErrorController extends Controller
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function error405Action(ServerRequestInterface $request, array $methods)
    {
        $response = $this->textResponse('This URI only accepts POST method', 405)
            ->withHeader('Allow', strtoupper(implode($methods, ',')));
        return $response;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function error404Action(ServerRequestInterface $request)
    {
        return $this->textResponse('Resource not found', 404);
    }
}

