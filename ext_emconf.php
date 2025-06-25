<?php

$EM_CONF[$_EXTKEY] = [
    'author'           => 'Daniel Ablass',
    'author_email'     => 'dn@phantasie-schmiede.de',
    'category'         => 'misc',
    'clearCacheOnLoad' => true,
    'constraints'      => [
        'conflicts' => [],
        'depends'   => [
            'php'   => '8.3',
            'typo3' => '11.5.0-13.4.99',
        ],
        'suggests'  => [],
    ],
    'description'      => 'Collection of debugging tools',
    'state'            => 'stable',
    'title'            => 'PSBits | Debug',
    'version'          => '2.0.0',
];
