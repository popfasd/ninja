<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Controllers/ApiController.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Controllers;

use Popfasd\Ninja\Form;
use MattFerris\Di\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Kispiox\Controller;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;

class ApiController extends Controller
{
    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function generateTokenAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');

        $params = $request->getParsedBody();

        if (!array_key_exists('host', $params) || empty($params['host'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No host specified',
                'debug' => 'failed to generate API token, no host specified'
            ], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9-\.]+$/', $params['host'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid host specified',
                'debug' => 'host contains invalid characters'
            ], 400);
        }

        if (!array_key_exists('name', $params) || empty($params['name'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'No form name specified',
                'debug' => 'failed to generate API token, no form name specified'
            ], 400);
        }

        if (!preg_match('/^[a-zA-Z0-9-_]+$/', $params['name'])) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Invalid form name specified',
                'debug' => 'form name contains invalid characters'
            ], 400);
        }

        if (!$config->has('app.auth.key')) {
            throw new RuntimeException('app.auth.key is not set');
        }

        $token = (new Builder())
            ->setIssuedAt(time())
            ->set('host', $params['host'])
            ->set('name', $params['name'])
            ->sign(new Sha256(), $config->get('app.auth.key'))
            ->getToken();

        return $this->jsonResponse([
            'status' => 'success',
            'href' => $request->getUri()->__toString(),
            'token' => (string)$token
        ]);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function getFormAction(ServerRequestInterface $request, $formId)
    {
        $config = $this->container->get('Config');
        $cache = $this->container->get('FormCache');

        $apiUrl = $request->getUri()
            ->withPath($config->get('app.uriPrefix').'/api/forms/')
            ->withQuery('');

        if (!$cache->hasForm($formId)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Form does not exist',
                'debug' => 'form ID "'.$formId.'" does not exist in the cache'
            ], 404);
        }

        $settings = $cache->getForm($formId);
        $form = new Form($formId, $settings->get('url'));

        return $this->jsonResponse([
            'status' => 'success',
            'href' => $request->getUri()->__toString(),
            'form' => [
                'id' => $form->getId(),
                'url' => $form->getUrl(),
                'submissions' => $apiUrl.$form->getId().'/submissions'
            ]
        ]);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function getFormsAction(ServerRequestInterface $request)
    {
        $config = $this->container->get('Config');
        $cache = $this->container->get('FormCache');

        $apiUrl = $request->getUri()
            ->withPath($config->get('app.uriPrefix').'/api/forms/')
            ->withQuery('');

        $forms = $cache->getForms();
        $jsonForms = [];
        foreach ($forms as $form) {
            $jsonForms[$form->getId()] = $form->getHost().'::'.$form->getName();
        }

        return $this->jsonResponse([
            'status' => 'success',
            'href' => $request->getUri()->__toString(),
            'forms' => $jsonForms
        ]);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string $formId
     */
    public function getSubmissionsAction(ServerRequestInterface $request, $formId)
    {
        $cache = $this->container->get('FormCache');

        if (!$cache->hasForm($formId)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Form does not exist',
                'debug' => 'form ID "'.$formId.'" does not exist in the cache'
            ], 404);
        }

        try {
            $settings = $cache->getForm($formId);
            $form = new Form($formId, $settings->get('host'), $settings->get('name'));            
            $submissions = $cache->getSubmissionsByForm($form);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to retrieve form submissions',
                'debug' => 'encountered exception "'.get_class($e).'" with '.$e->getMessage()
            ], 500);
        }

        $config = $this->container->get('Config');
        $formUri = $request->getUri()
            ->withPath($config->get('app.uriPrefix').'/api/forms/'.$formId)
            ->withQuery('');

        $jsonSubs = [];
        foreach ($submissions as $sub) {
            $jsonSubs[$sub->getId()] = $sub->__toArray();
        }

        return $this->jsonResponse([
            'status' => 'success',
            'href' => $request->getUri()->__toString(),
            'form' => $formUri->__toString(),
            'submissions' => $jsonSubs
        ]);
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string $formId
     */
    public function getExportAction($request, $formId)
    {
        $cache = $this->container->get('FormCache');
        $exporter = $this->container->get('Exporter');

        if (!$cache->hasForm($formId)) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Form does not exist',
                'debug' => 'form ID "'.$formId.'" does not exist in the cache'
            ], 404);
        }

        $submissions = [];
        try {
            $settings = $cache->getForm($formId);
            $form = new Form($formId, $settings->get('host'), $settings->get('name'));
            $submissions = $cache->getSubmissionsByForm($form);
        } catch (Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to retrieve form submissions',
                'debug' => 'encountered exception "'.get_class($e).'" with '.$e->getMessage()
            ], 500);
        }

        $response = (new Response())->withHeader('content-type', $exporter->getMimeType());
        $response->getBody()->write($exporter->export($submissions));
        return $response;
    }
}

