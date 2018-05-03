<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * DomainEventLoggerHelpers.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use MattFerris\Events\AbstractLoggerHelpers;

class DomainEventLoggerHelpers extends AbstractLoggerHelpers
{
    static public function onSubmissionProcessedEvent(SubmissionProcessedEvent $e)
    {
        $submission = $e->getSubmission();
        $form = $submission->getForm();

        return 'processed submission ('.$submission->getId().') for ['.$form->getDomain().':'.$form->getName().'] ('.$form->getId().')';
    }
}

