<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class AdminNotifyListener
{
    /**
     * @var array
     */
    protected $emails;

    /**
     * @param array $emails
     */
    public function __construct(array $emails)
    {
        $this->emails = $emails;
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
        foreach ($this->emails as $addr) {
            mail($addr, 'ninja: '.$form->getUrl(), $body);
        }
    }
}

