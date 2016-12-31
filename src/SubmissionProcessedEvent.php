<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * SubmissionProcessedEvent.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use MattFerris\Events\Event;

class SubmissionProcessedEvent extends Event
{
    /**
     * @var Submission
     */
    protected $submission;

    /**
     * @param Submission $submission
     */
    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    /**
     * @return Submission
     */
    public function getSubmission()
    {
        return $this->submission;
    }
}
