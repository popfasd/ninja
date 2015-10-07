<?php

namespace Popfasd\Ninja;

use MattFerris\Events\AbstractLoggerHelpers;

class DomainEventLoggerHelpers extends AbstractLoggerHelpers
{
    static public function onSubmissionProcessedEvent(SubmissionProcessedEvent $e)
    {
        $submission = $e->getSubmission();
        $form = $submission->getForm();

        return 'processed submission ('.$submission->getId().') from ['.$form->getUrl().'] ('.$form->getId().')';
    }
}

