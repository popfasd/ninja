<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class SubmissionReceiptListener
{
     /**
     * @param SubmissionProcessedEvent $event
     */
    public function onSubmissionProcessed(SubmissionProcessedEvent $event)
    {
        $submission = $event->getSubmission();
        $form = $submission->getForm();

        $tplFile = $form->getCacheDir().'/receipt.tpl';
        if (!file_exists($tplFile)) {
            return;
        } 

        $fh = fopen($tplFile, 'r');
        $subject = fgets($fh);
        $from = fgets($fh);
        $tpl = '';
        while (($line = fgets($fh)) !== false) {
            $tpl .= $line;
        }
        fclose($fh);

        $data = $submission->getData();
        if (!isset($data['email'])) {
            return;
        }
        $email = $data['email'];
        
        foreach ($data as $key => $value) {
            $tpl = str_replace("%$key%", $value, $tpl);
        }

        // send email to submitter
        mail($email, $subject, $tpl, 'From: '.$from);
    }
}

