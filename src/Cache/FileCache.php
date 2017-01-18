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

use Popfasd\Ninja\Form;
use Popfasd\Ninja\Submission;
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
    public function addForm(Form $form, array $settings = [])
    {
        $formId = $form->getId();
        if ($this->hasForm($formId)) {
            throw new RuntimeException(
                'form "'.$formId.'" already exists in the cache'
            );
        }

        if (count($settings) === 0) {
            $settings['url'] = $form->getUrl();
        }

        $formPath = $this->cacheDir.'/'.$formId;

        mkdir($formPath);
        mkdir($formPath.'/submissions');
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

    /**
     * {@inheritDoc}
     */
    public function getForms()
    {
        $forms = [];

        $dh = opendir($this->cacheDir);
        while ($f = readdir($dh)) {
            if (strpos($f, '.') === 0) {
                continue;
            }

            $settings = $this->getForm($f);

            $forms[] = new Form($f, $settings->get('url'));
        }
        closedir($fh);

        return $forms;
    }

    /**
     * {@inheritDoc}
     */
    public function addSubmission(Submission $submission)
    {
        $formId = $submission->getForm()->getId();
        $subId = $submission->getId();
        if (!$this->hasForm($formId)) {
            throw new RuntimeException(
                'submission "'.$subId.'" belongs to form "'.$formId.'" which does not exist'
            );
        }

        $subPath = $this->cacheDir.'/'.$formId.'/submissions/'.$subId;

        if (file_exists($subPath)) {
            throw new RuntimeException(
                'submission "'.$subId.'" (form "'.$formId.'") already exists'
            );
        }

        file_put_contents($subPath, serialize($submission->__toArray()));
    }

    /**
     * {@inheritDoc}
     */
    public function getSubmissionsByForm(Form $form)
    {
        $formId = $form->getId();

        if (!$this->hasForm($formId)) {
            throw new RuntimeException('form "'.$formId,'" does not exist');
        }

        $subsPath = $this->cacheDir.'/'.$formId.'/submissions';
        if (!file_exists($subsPath)) {
            throw new RuntimeException('path "'.$subPath.'" does not exist');
        }

        $submissions = [];
        $dh = opendir($subsPath);
        while ($f = readdir($dh)) {
            if (strpos($f, '.') === 0) {
                continue;
            }

            $data = unserialize(file_get_contents($subsPath.'/'.$f));
            $submissions[] = new Submission($data['__id'], $form, $data, $data['__ts']);
        }
        closedir($dh);

        return $submissions;
    }
}

