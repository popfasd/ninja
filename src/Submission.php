<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * Submission.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

namespace Popfasd\Ninja;

class Submission
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var Form
     */
    protected $form;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @param string $id
     * @param Form $form
     * @param array $data
     * @param int $timestamp
     */
    public function __construct($id, Form $form, array $data, $timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        $this->id = $id;
        $this->form = $form;
        $this->data = $data;
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return array
     */
    public function getData()
    {
        $data = $this->data;
        $data['__id'] = $this->id;
        $data['__ts'] = $this->timestamp;
        return $data;
    }

    /**
     * @return array
     */
    public function __toArray()
    {
        return $this->getData();
    }
}

