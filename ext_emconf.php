<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Login link',
    'description' => 'Fast way to login as different backend or frontenduser by generating a link including a one-time token within the backend.',
    'category' => '',
    'author' => 'Georg Ringer',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '12.4.27-13.4.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
