<?php

/*
 * Ninja is an HTML form processor that can email/save the contents of forms in
 * a variety of ways.
 */

namespace Popfasd\Ninja;

use MattFerris\HttpRouting\RequestInterface;

class FileProcessor implements ProcessorInterface
{
    /**
     * @var FormatterInterface
     */
    protected $formatter;

    /**
     * @var string
     */
    protected $formDir;

    /**
     * @param FormatterInterface $formatter
     * @param string $formDir
     */
    public function __construct(FormatterInterface $formatter, $formDir) {
        $this->formatter = $formatter;

        if (!file_exists($formDir)) {
            throw new FormDirectoryDoesntExistException($formDir);
        }

        $this->formDir = $formDir;
    }

    /**
     * @param RequestInterface $request
     * @param array $fields
     */
    public function process(RequestInterface $request, array $fields = null) {
        $formId = sha1($request->getHeader('Referer'));
        $formFile = $this->formDir.'/'.$formId.'.tsv';

        // if specified, load field names from file
        $fieldsDef = $this->formDir.'/'.$formId.'.php';
        if (file_exists($fieldsDef)) {
            require($fieldsDef);
        }

        $inRow = $request->post();

        // generate default fields names from form field keys
        if (!is_array($fields)) {
            $fields = [];
            foreach (array_keys($inRow) as $key) {
                $fields[$key] = $key;
            }
        }

        // add time field
        $fields['ninja_ts'] = 'Timestamp';
        $inRow['ninja_ts'] = date('Y/M/d H:i:s');

        // populate $row by field names
        $row = [];
        foreach ($fields as $key => $name) {
            $row[$name] = $inRow[$key];
        }

        // get formatted row
        $row = $this->formatter->format($row, $fields);
        if (!file_exists($formFile)) {
            $headings = $this->formatter->format($fields, array_keys($fields));
            file_put_contents($formFile, $headings);
        }

        // write row to file
        file_put_contents($formFile, $row, FILE_APPEND);
    }
}

