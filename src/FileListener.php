<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class FileListener
{
    /**
     * @param SubmissionProcessedEvent $event
     */
    public function onSubmissionProcessed(SubmissionProcessedEvent $event)
    {
        $submission = $event->getSubmission();
        $form = $submission->getForm();

        $saveFile = $form->getCacheDir().'/submissions.tsv';

        // get formatted row
        $fields = $form->getFields();
        if (!file_exists($saveFile)) {
            $headings = implode("\t", array_values($fields))."\n";
            file_put_contents($saveFile, $headings);
        }

        $row = [];
        $data = $submission->getData();
        foreach (array_keys($fields) as $key) {
            $row[] = $data[$key];
        }

        // write row to file
        file_put_contents($saveFile, implode("\t", $row)."\n", FILE_APPEND);
    }
}

