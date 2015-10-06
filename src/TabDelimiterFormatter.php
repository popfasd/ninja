<?php

namespace Popfasd\Ninja;

class TabDelimiterFormatter implements FormatterInterface
{
    /**
     * @param array $row
     * @param array $fields
     * @return string
     */
    public function format($row, $fields = null) {
        $outRow = [];
        if (is_array($fields)) {
            foreach ($fields as $name) {
                $outRow[] = $row[$name];
            }
        } else {
            $outRow = $row;
        }
        $return = implode("\t", $outRow)."\n";
        return $return;
    }
}

