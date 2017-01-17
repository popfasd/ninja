<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Cache/FileCache.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja\Cache;

use RuntimeException;
use Symfony\Component\Yaml\Yaml;
use MattFerris\Configuration\Configuration;
use MattFerris\Configuration\Locators\FileLocator;
use MattFerris\Configuration\Loaders\YamlLoader;

class FileCache implements CacheInterface
{
    /**
     * @var string Cache directory path
     */
    protected $cacheDir;

    /**
     * @param string $cacheDir The cache directory path
     * @throws RuntimeException If $cacheDir doesn't exist
     */
    public function __construct($cacheDir)
    {
        if (!file_exists($cacheDir)) {
            throw new RuntimeException(
                'cache directory "'.$cacheDir.'" does not exist'
            );
        }
        $this->cacheDir = $cacheDir;
    }

    /**
     * {@inheritDoc}
     */
    public function hasForm($formId)
    {
        return file_exists($this->cacheDir.'/'.$formId.'/settings.yaml');
    }

    /**
     * {@inheritDoc}
     */
    public function addForm($formId, array $settings = [])
    {
        if ($this->hasForm($formId)) {
            throw new RuntimeException(
                'form "'.$formId.'" already exists in the cache'
            );
        }

        mkdir($this->cacheDir.'/'.$formId);
        file_put_contents($this->cacheDir.'/'.$formId.'/settings.yaml', Yaml::dump($settings));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getForm($formId)
    {
        if (!$this->hasForm($formId)) {
            throw new RuntimeException(
                'form "'.$formId.'" does not exist in the cache'
            );
        }

        $settings = new Configuration(
            new FileLocator([$this->cacheDir.'/'.$formId]),
            new YamlLoader()
        );
        $settings->load('settings.yaml');

        return $settings;
    }
}

