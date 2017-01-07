<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Form.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

use Psr\Http\Message\RequestInterface;

class Form
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var string
     */
    protected $nextUrl;

    /**
     * @var array
     */
    protected $validationRules;

    /**
     * @var array
     */
    protected $validationErrors;

    /**
     * @var string
     */
    protected $validationKey = '__nv';

    /**
     * @param RequestInterface $request
     * @param $string $cacheDir
     */
    public function __construct(RequestInterface $request, $cacheDir)
    {
        $referer = $request->getHeaderLine('Referer');
        $this->id = sha1($referer);
        $this->request = $request;
        $this->cacheDir = $cacheDir.'/'.$this->id;

        $fields = $nexturl = $validationRules = null;
        if (file_exists($this->cacheDir)) {
            $settingsCache = $this->cacheDir.'/settings.php';
            if (file_exists($settingsCache)) {
                require($settingsCache);
            }
        } else {
            mkdir($this->cacheDir);
            $settings = "<?php\n\n// url: ".$referer."\n";
            file_put_contents($this->cacheDir.'/settings.php', $settings);
        }

        $this->fields = $fields;
        $this->nextUrl = $nexturl;
        $this->validationRules = $validationRules;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->request->getHeaderLine('Referer');
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param string $key
     * @return string
     */
    public function getFieldTitle($key)
    {
        $title = null;
        if (isset($this->fields[$key]) || array_key_exists($key, $this->fields)) {
            $title = $this->fields[$key];
        }
        return $title;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function hasField($key)
    {
        $hasField = false;
        if (isset($this->fields[$key]) || array_key_exists($key, $this->fields)) {
            $hasField = true;
        }
        return $hasField;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return string
     */
    public function getNextUrl()
    {
        return $this->nextUrl;
    }

    /**
     * @param string $key
     */
    public function setValidationKey($key)
    {
        if (empty($key)) {
            throw new \InvalidArgumentException('$key expects non-empty string');
        }
        $this->valdationKey = $key;
        return $this;
    }

    /**
     * @return bool
     */
    public function validate()
    {
        $post = $this->request->getParsedBody();

        if (is_array($this->validationRules) && count($this->validationRules) > 0) {
            // check fields
            foreach ($this->validationRules as $field => $rule) {
                if (!isset($post[$field])) {
                    $this->validationErrors[$field] = 'not received';
                } elseif (empty($post[$field])) {
                    $this->validationErrors[$field] = 'empty';
                } elseif (!is_bool($rule) && !preg_match($rule, $post[$field])) {
                    $this->validationErrors[$field] = 'failed';
                }
            }
        }

        return (!is_array($this->validationErrors) || count($this->validationErrors) === 0);
    }

    /**
     * @return array
     */
    public function getValidationErrors()
    {
        return $this->validationErrors;
    }

    /**
     * @return Submission The processed submission
     */
    public function process()
    {
        $post = $this->request->getParsedBody();

        // populate default field names
        $fields = $this->fields;
        if (is_null($fields)) {
            $fields = ['__id' => 'Submission ID', '__ts' => 'Submission Timestamp'];
        }
        foreach (array_keys($post) as $k) {
            $fields[$k] = $k;
        }
        $this->fields = $fields;

        // pull wanted data from post values
        $data = [];
        foreach (array_keys($this->fields) as $k) {
            // ignore internal fields
            if (strpos($k, '__') === 0) {
                continue;
            }
            $data[$k] = $post[$k];
        }

        // generate submission ID and add field/data
        $submissionId = sha1($this->id.serialize($data));
 
        $submission = new Submission($submissionId, $this, $data);

        // raise submission processed event
        DomainEvents::dispatch(new SubmissionProcessedEvent($submission));

        return $submission;
    }
}

