<?php

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
