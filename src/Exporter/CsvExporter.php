<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Exporter\CsvExporter.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Exporter;

class CsvExporter implements ExporterInterface
{
    /**
     * {@inheritDoc}
     */
    public function getMimeType()
    {
        return 'text/csv';
    }

    /**
     * {@inheritDoc}
     */
    public function export(array $data)
    {
        $rows = [];

        // headings
        $headers = array_keys($data[0]->__toArray());
        $row = [];
        foreach ($headers as $h) {
            $row[] = '"'.addslashes($h).'"';
        }
        $rows[] = implode($row, ',');

        // body
        foreach ($data as $s) {
            $row = [];
            foreach ($s->__toArray() as $c) {
                $row[] = '"'.addslashes($c).'"';
            }
            $rows[] = implode($row, ',');
        }

        return implode("\n", $rows);
    }
}

