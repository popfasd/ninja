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
     * @param string $formId
     * @param array $settings Optional
     * @return self
     * @throws \RuntimeException If $formId already exists
     */
    public function addForm($formId, array $settings = []);

    /**
     * Get a form from the cache.
     *
     * @param string $formId
     * @return \MattFerris\Configuration\ConfigurationInterface
     * @throws \RuntimeException If $formId doesn't exist
     */
    public function getForm($formId);
}

