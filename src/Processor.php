<?php

/*
 * Ninja is an HTML form processor that can email/save the contents of forms in
 * a variety of ways.
 */

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class Processor implements ProcessorInterface
{
    protected $formatter;
    protected $formDir;

    public function __construct(FormatterInterface $formatter, $formDir) {
        $this->formatter = $formatter;

        if (!file_exists($formDir)) {
            throw new FormDirectoryDoesntExistException($formDir);
        }

        $this->formDir = $formDir;
    }

    public function process(RequestInterface $request, $fields = null) {
        $formId = sha1($request->getHeader('Referer'));
        $formFile = $this->formDir.'/'.$formId.'.tsv';

        $fieldsDef = $this->formDir.'/'.$formId.'.php';
        if (file_exists($fieldsDef)) {
            require($fieldsDef);
        }

        if (is_array($fields)) {
            $row = [];
            foreach ($fields as $key => $name) {
                $row[$name] = $request->post($key);
            }

            $row = $this->formatter->format($row, $fields);
            if (!file_exists($formFile)) {
                $headings = $this->formatter->format($fields, array_keys($fields));
                file_put_contents($formFile, $headings);
            }
        } else {
            $row = $this->formatter->format($request->post());
        }

        file_put_contents($formFile, $row, FILE_APPEND);
    }
}

