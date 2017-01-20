<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Exporter\ExporterInterface.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Exporter;

interface ExporterInterface
{
    /**
     * Return the MIME type of the exported data.
     *
     * @return string
     */
    public function getMimeType();

    /**
     * Generate an export of passed submissions.
     *
     * @param array $data
     * @return mixed
     */
    public function export(array $data);
}

