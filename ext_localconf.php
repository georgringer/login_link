<?php

$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['BE_fetchUserIfNoSession'] = true;
$GLOBALS['TYPO3_CONF_VARS']['SVCONF']['auth']['setup']['FE_fetchUserIfNoSession'] = true;
$GLOBALS['TYPO3_CONF_VARS']['MAIL']['templateRootPaths'][1695727924] = 'EXT:login_link/Resources/Private/Templates/Email/';

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

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'LoginLink',
    'MagicLoginLinkForm',
    // all actions
    [\GeorgRinger\LoginLink\Controller\PluginController::class => 'showForm,sendMail'],
    [\GeorgRinger\LoginLink\Controller\PluginController::class => 'showForm,sendMail'],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);