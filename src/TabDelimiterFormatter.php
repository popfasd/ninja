<?php

namespace Popfasd\Ninja;

class TabDelimiterFormatter implements FormatterInterface
{
    /**
     * @param array $row
     * @param array $fields
     * @return string
     */
    public function format(array $row, array $fields = null) {
        $outRow = [];
        foreach ($fields as $name) {
            $outRow[] = $row[$name];
        }
        $return = implode("\t", $outRow)."\n";
        return $return;
    }
}

