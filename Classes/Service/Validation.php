<?php

namespace GeorgRinger\LoginLink\Service;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class Validation
{

    public function isValid(string $table, int $recordId): bool
    {
        if (!in_array($table, ['be_users', 'fe_users'], true)) {
            return false;
        }

        $user = $this->getBackendUser();
        $row = BackendUtility::getRecord($table, $recordId);

        // BE
        if ($table === 'be_users') {
            if ($row['disable'] || $row['admin']) {
                return false;
            }
            return $user->isAdmin();
        }

        // FE
        // todo allow editors to login for fe_users
        return $user->isAdmin();
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
