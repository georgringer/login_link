<?php

namespace GeorgRinger\LoginLink\Controller;

use GeorgRinger\LoginLink\Exception\UserValidationException;
use GeorgRinger\LoginLink\Repository\TokenRepository;
use GeorgRinger\LoginLink\Service\SendMail;
use GeorgRinger\LoginLink\Service\TokenGenerator;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\TypoScript\TypoScriptService;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Http\ForwardResponse;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Exception\StopActionException;
use TYPO3\CMS\Extbase\Mvc\View\ViewInterface;

class PluginController extends ActionController
{
    protected TokenGenerator $tokenGenerator;
    protected TokenRepository $tokenRepository;

    protected array $settingsAsTypoScriptArray = [];

    protected LanguageService $languageService;

    /**
     * @param ConfigurationManagerInterface $configurationManager
     * @param TokenGenerator $tokenGenerator
     * @param TokenRepository $tokenRepository
     */
    public function __construct(TokenGenerator $tokenGenerator, TokenRepository $tokenRepository)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenRepository = $tokenRepository;
    }

    protected function initializeAction(): void
    {
        parent::initializeAction();
//        $this->languageService = $GLOBALS['LANG'];
        $this->configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $this->languageService = $this->getLanguageService();
        $this->languageService->includeLLFile('EXT:login_link/Resources/Private/Language/locallang.xlf');
    }

    /**
     * @return ResponseInterface
     */
    public function showFormAction(): ResponseInterface
    {
        $email = $this->getEmailAddress();
        try {
            if ($email && $this->getUserIdFromEmail($email)) {
                $response = new ForwardResponse('showFormPredefinedEmail');
                return $response->withArguments(['email' => $email]);
            }
        } catch (\Doctrine\DBAL\Driver\Exception $e) {
        } catch (UserValidationException $e) {
            $this->view->assignMultiple([
                'errorMessage' => $this->request->getArguments()['errorMessage'] ?? $e->getMessage(),
                'email' => $email,
                'emailFromCObjectData' => $this->configurationManager->getContentObject()->data['email'] ?? null
            ]);
            if($userObject = $GLOBALS['TSFE']->fe_user->user ?? false) {
                $this->view->assignMultiple([
                    'user' => $userObject,
                    'usernameAndEmail' => $userObject['username'] == $userObject['email'] ? $userObject['email'] : "{$userObject['username']} ({$userObject['email']})"
                ]);
            }

        }
        return $this->htmlResponse();
    }

    /**
     * @return string|null
     */
    private function getEmailAddress($email = null): ?string
    {
        if ($email === null) {
            $email = $this->request->getArguments()['email'] ?? null;
        }

        if ($email === null) {
            $email = $this->configurationManager->getContentObject()->data['email'] ?? null;
        }

        if ($email === null) {
            if($postVar = $this->configurationManager->getContentObject()->data['postVar'] ?? null) {
                $email = ArrayUtility::getValueByPath($GLOBALS['_POST'], $postVar, '.') ?? null;
            }
        }

        return $email;
    }

    /**
     * @param string $email
     * @return void
     */
    public function showFormPredefinedEmailAction(string $email = '')
    {
        $this->view->assignMultiple([
            'errorMessage' => $this->request->getArguments()['errorMessage'] ?? null,
            'email' => $this->getEmailAddress($email)
        ]);
        if($userObject = $GLOBALS['TSFE']->fe_user->user ?? false) {
            $this->view->assignMultiple([
                'user' => $userObject,
                'usernameAndEmail' => $userObject['username'] == $userObject['email'] ? $userObject['email'] : "{$userObject['username']} ({$userObject['email']})"
            ]);
        }

    }


    /**
     * @param string $email
     * @return void
     * @throws Exception
     * @throws StopActionException
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function sendMailAction(string $email = ''): void
    {
        try {
            $userId = $this->getUserIdFromEmail($email);
            GeneralUtility::makeInstance(SendMail::class)->sendMailToFrontendUser($userId, $email);
        } catch (TransportExceptionInterface $exception) {
            $this->redirect('showForm', null, null, ['email' => $email,
                'errorMessage' => $this->getLanguageService()->getLL('plugin.mailer_sending_error')]);
            error_log($exception->getMessage());
        } catch (UserValidationException $exception) {
            $this->redirect('showForm', null, null, ['email' => $email,
                'errorMessage' => $exception->getMessage()]);
        }
    }

    /**
     * @param string $email
     * @return int
     * @throws \Doctrine\DBAL\Driver\Exception|UserValidationException
     */
    private function getUserIdFromEmail(string $email): int
    {
        if (!GeneralUtility::validEmail($email)) {
            $validationError = $this->getLanguageService()->getLL('plugin.validation_email_syntax_error');
        } else {
            $searchIdentifiers['email'] = $email;
            if ($pageId = $this->getStoragePid()) {
                $searchIdentifiers['pid'] = $pageId;
            }
            $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
            $users = $connection->select(['uid'], 'fe_users', $searchIdentifiers)->fetchFirstColumn();
            if (count($users) === 0) {
                $validationError = $this->getLanguageService()->getLL('plugin.validation_no_users_found_error');
            } elseif (count($users) > 1) {
                $validationError = $this->getLanguageService()->getLL('plugin.validation_multiple_users_found_error');
            } else {
                return (int)$users[0];
            }
        }
        throw new UserValidationException($validationError);
    }

    protected function getLanguageService(): LanguageService
    {
        if (!isset($GLOBALS['LANG'])) {
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageService::class);
        }
        return $GLOBALS['LANG'];
    }

    protected function getStoragePid(): int
    {
        return $this->configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK)['persistence']['storagePid'] ?? 0;
    }
}