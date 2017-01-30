<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Controller.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use MattFerris\Di\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Kispiox\Controller as KispioxController;

class ApiController extends KispioxController
{
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
            $jsonForms[$form->getId()] = $apiUrl->__toString().$form->getId();
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
            $form = new Form($formId, $settings->get('url'));            
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
            $form = new Form($formId, $settings->get('url'));
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

