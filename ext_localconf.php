<?php

$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = true;
$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = true;

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['login_link'] = \GeorgRinger\LoginLink\Hooks\DataHandlerHook::class;

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addService(
    'login_link',
    'auth',
    \GeorgRinger\LoginLink\Authentication\TokenAuthenticationService::class,
    [
        'title' => 'User authentication',
        'description' => 'Authentication with token.',
        'subtype' => 'getUserBE,getUserFE,authUserBE,authUserFE',
        'available' => true,
        'priority' => 80,
        'quality' => 50,
        'os' => '',
        'exec' => '',
        'className' => \GeorgRinger\LoginLink\Authentication\TokenAuthenticationService::class,
    ]
);
