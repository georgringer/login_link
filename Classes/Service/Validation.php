<?php

namespace GeorgRinger\LoginLink\Service;

use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class Validation
{

    public function isValid(string $table, int $recordId):bool
    {
        if (!in_array($table, ['be_users', 'fe_users'], true)) {
            return false;
        }

        $user = $this->getBackendUser();
        if ($user->isAdmin()) {
            return true;
        }
        if ($table === 'be_users') {
            return false;
        }

        // todo allow editors to login for fe_users

        return true;
    }


    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
