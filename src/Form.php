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

use RuntimeException;

class Form
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $origin;

    /**
     * @var string
     */
    protected $fname;

    /**
     * @var array
     */
    protected $fields;

    /**
     * @var array
     */
    protected $data = [];

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
     * @param string $id
     * @param string $origin
     * @param string $fname
     * @param array $data
     */
    public function __construct($id, $origin, $fname, array $data = [])
    {
        $this->id = $id;
        $this->origin = $origin;
        $this->fname = $fname;
        $this->data = $data;
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
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * @return string
     */
    public function getFormName()
    {
        return $this->fname;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param array $fields
     * @return self
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
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
     * @param array $rules
     * @return self
     */
    public function setValidationRules(array $rules)
    {
        $this->validationRules = $rules;
        return $this;
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
        if (is_array($this->validationRules) && count($this->validationRules) > 0) {
            // check fields
            foreach ($this->validationRules as $field => $rule) {
                if (!isset($this->data[$field])) {
                    $this->validationErrors[$field] = 'not received';
                } elseif (empty($this->data[$field])) {
                    $this->validationErrors[$field] = 'empty';
                } elseif (!is_bool($rule) && !preg_match($rule, $this->data[$field])) {
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
     * @throws RuntimeException Submitted data doesn't contain a defined field
     */
    public function process()
    {
        // populate default field names
        $fields = $this->fields;
        if (is_null($fields)) {
            $fields = ['__id' => 'Submission ID', '__ts' => 'Submission Timestamp'];
        }
        foreach (array_keys($this->data) as $k) {
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

            $data[$k] = $this->data[$k];
        }

        // generate submission ID and add field/data
        $submissionId = sha1($this->id.serialize($data));
 
        $submission = new Submission($submissionId, $this, $data);

        // raise submission processed event
        DomainEvents::dispatch(new SubmissionProcessedEvent($submission));

        return $submission;
    }
}

