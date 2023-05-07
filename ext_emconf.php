<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Login link',
    'description' => 'Fast way to login as different backend or frontenduser by generating a link including a one-time token within the backend.',
    'category' => '',
    'author' => 'Georg Ringer',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
