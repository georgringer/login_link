<?php

namespace GeorgRinger\LoginLink\Service;

use GeorgRinger\LoginLink\Repository\TokenRepository;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Mail\FluidEmail;
use TYPO3\CMS\Core\Mail\Mailer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class SendMail
{
    protected TokenGenerator $tokenGenerator;
    protected TokenRepository $tokenRepository;

    protected array $loginlinkExtensionConfiguration;

    /**
     * @param TokenGenerator $tokenGenerator
     * @param TokenRepository $tokenRepository
     * @param array $loginlinkExtensionConfiguration
     */
    public function __construct(TokenGenerator $tokenGenerator, TokenRepository $tokenRepository, array $loginlinkExtensionConfiguration)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenRepository = $tokenRepository;
        $this->loginlinkExtensionConfiguration = $loginlinkExtensionConfiguration;
    }

    /**
     * @param int $recordId
     * @param string $receiverEmailAddress
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function sendMailToFrontendUser(int $recordId, string $receiverEmailAddress): void
    {
        $this->getLanguageService()->includeLLFile('EXT:login_link/Resources/Private/Language/locallang.xlf');

        $authType = 'fe';
        $token = $this->tokenGenerator->generate();
        $this->tokenRepository->add(
            $recordId,
            $authType,
            $token,
            0
        );
        $url = GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder::class)
            ->setTargetPageUid($GLOBALS['TYPO3_REQUEST']->getAttribute('frontend.controller')->id)
            ->setArguments(['byToken' => $token, 'logintype' => 'login'])
            ->setCreateAbsoluteUri(true)
            ->buildFrontendUri();

        $email = GeneralUtility::makeInstance(FluidEmail::class);
        $email->setRequest($GLOBALS['TYPO3_REQUEST']);
        $mailFromAddress = $this->loginlinkExtensionConfiguration['pluginMailFromAddress'] ?? ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] ?? null);
        $mailFromName = $this->loginlinkExtensionConfiguration['pluginMailFromAddress'] ?? ($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] ?? '');
        if(!$mailFromAddress) {
            throw new Exception('The pluginMailFromAddress extension configuration or $GLOBALS[\'TYPO3_CONF_VARS\'][\'MAIL\'][\'defaultMailFromAddress\'] needs to be configured to be able to send an e-email.');
        }
        $email
            ->to($receiverEmailAddress)
            ->from(new Address($mailFromAddress, $mailFromName))
            ->subject(sprintf($this->getLanguageService()->getLL('plugin.email_subject'), $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getAttribute('websiteTitle')))
            ->format('html') // only HTML mail
            ->setTemplate('MagicLoginLink')
            ->assign('headline', sprintf($this->getLanguageService()->getLL('plugin.email_subject'), $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getAttribute('websiteTitle')))
            ->assign('introduction', sprintf($this->getLanguageService()->getLL('plugin.email_introduction'), $receiverEmailAddress))
            ->assign('content', $this->getLanguageService()->getLL('plugin.email_content'))
            ->assign('email', $receiverEmailAddress)
            ->assign('loginUrl', $url)
            ->assign('site', $GLOBALS['TYPO3_REQUEST']->getAttribute('site')->getConfiguration());
            GeneralUtility::makeInstance(Mailer::class)->send($email);
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }


}