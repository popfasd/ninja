<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * AdminNotifyListener.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use MattFerris\Configuration\ConfigurationInterface;
use MattFerris\HttpRouting\RequestInterface;

class AdminNotifyListener
{
    /**
     * @var ConfigurationInterface
     */
    protected $config;

    /**
     * @param ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param SubmissionProcessedEvent $event
     */
    public function onSubmissionProcessed(SubmissionProcessedEvent $event)
    {
        $submission = $event->getSubmission();
        $form = $submission->getForm();

        $body = 'Submitted via '.$form->getUrl()."\n\n";

        foreach ($submission->getData() as $key => $value) {
            $body .= $form->getFieldTitle($key).":\n$value\n\n";
        }

        // send emails to listed recipients
        $emails = $this->config->get('app.mailto');
        foreach ($emails as $addr) {
            mail($addr, 'ninja: '.$form->getUrl(), $body);
        }
    }
}

