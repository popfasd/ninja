<?php

/**
 * ninja - sneaky HTML form processor
 * github.com/popfasd/ninja
 *
 * index.php
 * @copyright Copyright (c) 2016 POPFASD
 * @author Matt Ferris <mferris@fasdoutreach.ca>
 *
 * Licensed under BSD 2-clause license
 * github.com/popfasd/ninja/blob/master/License.txt
 */

require('vendor/autoload.php');

if (isset($_GET['q'])) {
    $_SERVER['REQUEST_URI'] = $_GET['q'];
    unset($_GET['q']);
}

Kispiox\Kispiox::start();

