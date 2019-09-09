<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'Authorized Previews',
    'description' => 'Generate URLs to preview hidden languages without a backend login',
    'category' => 'be',
    'state' => 'stable',
    'uploadfolder' => 0,
    'clearCacheOnLoad' => 0,
    'author' => 'Daniel Goerz',
    'author_email' => 'daniel.goerz@b13.com',
    'author_company' => 'b13 GmbH',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0'
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
