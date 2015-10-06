<?php

namespace Popfasd\Ninja;

interface FormatterInterface
{
    /**
     * @param array $row
     * @param array $fields
     * @return string
     */
    public function format($row, $fields = null);
}

