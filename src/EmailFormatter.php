<?php

namespace Popfasd\Ninja;

class EmailFormatter implements FormatterInterface
{
    /**
     * @param array $row
     * @param array $fields
     * @return string
     */
    public function format(array $row, array $fields) {
        $email = "The Ninja strikes again!\n\nThe following information was intercepted:\n\n";

        foreach ($fields as $field) {
            $email .= $field."\n".$row[$field]."\n\n";
        }

        return $email;
    }
}

