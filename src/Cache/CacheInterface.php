<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Cache/CacheInterface.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Cache;

use Popfasd\Ninja\Form;
use Popfasd\Ninja\Submission;

interface CacheInterface
{
    /**
     * Checks if the form exists in the cache.
     *
     * @param string $formId
     * @returns bool
     */
    public function hasForm($formId);

    /**
     * Add a form to the cache with optional settings.
     *
     * @param Form $form
     * @param array $settings Optional
     * @return self
     * @throws \RuntimeException If form already exists
     */
    public function addForm(Form $form, array $settings = []);

    /**
     * Get a form from the cache.
     *
     * @param string $formId
     * @return \MattFerris\Configuration\ConfigurationInterface
     * @throws \RuntimeException If $formId doesn't exist
     */
    public function getForm($formId);

    /**
     * Add a form submission to the cache.
     *
     * @param Submission $submission
     * @return self
     * @throws \RuntimeException If submission already exists, or form doesn't exist
     */
    public function addSubmission(Submission $submission);
}

