<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * FileListener.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

class FileListener
{
    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @param string $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
    }

    /**
     * @param SubmissionProcessedEvent $event
     */
    public function onSubmissionProcessed(SubmissionProcessedEvent $event)
    {
        $submission = $event->getSubmission();
        $form = $submission->getForm();

        $saveFile = $this->cacheDir.'/'.$form->getId().'/submissions.tsv';

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

