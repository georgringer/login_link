<?php
defined('TYPO3') or die();

(static function (): void {
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    // extension name, matching the PHP namespaces (but without the vendor)
        'LoginLink',
        // arbitrary, but unique plugin name (not visible in the backend)
        'MagicLoginLinkForm',
        // plugin title, as visible in the drop-down in the backend, use "LLL:" for localization
        'Magic Login Link Form',
        null,
        'forms'
    );
})();

$GLOBALS['TCA']['tt_content']['types']['loginlink_magicloginlinkform']['showitem'] = str_replace('headers,', 'headers, pages:pages;pages,',$GLOBALS['TCA']['tt_content']['types']['loginlink_magicloginlinkform']['showitem']);
