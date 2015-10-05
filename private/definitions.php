<?php

$definitions = [
    'Dispatcher' => [
        'class' => 'MattFerris\\HttpRouter\\Dispatcher',
        'constructor' => ['dispatcher' => '%DI'],
    ],
    'Formatter' => [
        'class' => 'Popfasd\\Ninja\\TabDelimitedFormatter'
    ]
];
