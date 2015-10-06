<?php

namespace Popfasd\Ninja;

class FormDirectoryDoesntExistException extends \Exception
{
    public function __construct($msg) {
        parent::__construct('form dir "'.$msg.'" doesn\'t exist');
    }
}

