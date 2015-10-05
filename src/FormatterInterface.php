<?php

namespace Popfasd\Ninja;

interface FormatterInterface
{
    /**
     * @param string $input
     * @return string
     */
    public function format($input);

    /**
     * @param stream $stream
     * @return stream
     */
    public function formatStream(resource $stream);
}

