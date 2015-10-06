<?php

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class EmailProcessor implements ProcessorInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var array
     */
    protected $emails;

    /**
     * @param array $emails
     */
    public function __construct(FormatterInterface $formatter, $formDir, array $emails) {
        $this->formatter = $formatter;
        $this->formDir = $formDir;
        $this->emails = $emails;
    }

    /**
     * @param RequestInterface $row
     * @param array $fields
     */
    public function process(RequestInterface $request, array $fields = null) {
        $formId = sha1($request->getHeader('Referer'));

        // if specified, load fields names from file
        $fieldDefs = $this->formDir.'/'.$formId.'.php';
        if (file_exists($fieldDefs)) {
            require($fieldDefs);
        }

        $inRow = $request->post();

        // generate default fields names from form field keys
        if (!is_array($fields)) {
            $fields = [];
            foreach (array_keys($inRow) as $key) {
                $fields[$key] = $key;
            }
        }

        // populate $row by field names
        $row = [];
        foreach ($fields as $key => $name) {
            $row[$name] = $inRow[$key];
        }

        // get formatted row
        $email = $this->formatter->format($row, $fields);

        // send emails to listed recipients
        foreach ($this->emails as $addr) {
            mail($addr, 'The Ninja Strikes Again!', $email);
        }
    }
}

