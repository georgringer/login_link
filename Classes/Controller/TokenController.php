<?php
declare(strict_types=1);

namespace GeorgRinger\LoginLink\Controller;

use GeorgRinger\LoginLink\Repository\TokenRepository;
use GeorgRinger\LoginLink\Service\TokenGenerator;
use GeorgRinger\LoginLink\Service\Validation;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\PreviewUriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class TokenController
{

    protected TokenGenerator $tokenGenerator;
    protected TokenRepository $tokenRepository;
    protected Validation $validation;
    protected const PAID_VERSION = true;

    public function __construct(TokenGenerator $tokenGenerator, TokenRepository $tokenRepository, Validation $validation)
    {
        $this->tokenGenerator = $tokenGenerator;
        $this->tokenRepository = $tokenRepository;
        $this->validation = $validation;
    }

    public function verifyAction(ServerRequestInterface $request): ResponseInterface
    {
        $recordId = (int)($request->getQueryParams()['id'] ?? 0);
        $table = $request->getQueryParams()['table'] ?? '';
        $lang = $this->getLanguageService();

        if (!$this->validation->isValid($table, $recordId)) {
            return new HtmlResponse('Not valid');
        }

        $authType = $table === 'fe_users' ? 'fe' : 'be';

        $content = '';
        if (!self::PAID_VERSION) {
            $token = 'dummy123';
            $content .= '<div class="alert alert-info">'
                . htmlspecialchars($lang->sL('LLL:EXT:login_link/Resources/Private/Language/locallang.xlf:demo.information')) . '</div>';
        } else {
            $token = $this->tokenGenerator->generate();
            $this->tokenRepository->add(
                $recordId,
                $authType,
                $token,
                $this->getBackendUser()->user['uid']
            );
        }

        $url = $this->getUrl($recordId, $token, $authType);

        if ($authType === 'fe' && !$url) {
            $content .= htmlspecialchars($lang->sL('LLL:EXT:login_link/Resources/Private/Language/locallang.xlf:modal.fe.error'));
        } else {
            $content .= '<p>' . htmlspecialchars($lang->sL('LLL:EXT:login_link/Resources/Private/Language/locallang.xlf:modal.description')) . '</p>
<textarea readonly class="form-control">' . htmlspecialchars($url) . '</textarea>
</div>';
        }

        return new HtmlResponse($content);
    }

    private function getUrl(int $id, string $token, string $authType): string
    {
        if ($authType === 'be') {
            /** @var ServerRequest $request */
            $request = $GLOBALS['TYPO3_REQUEST'];
            $url = $request->getUri()->withPath('/typo3/login')->withQuery('login_status=login&byToken=' . $token);
        } else {
            $row = BackendUtility::getRecord('fe_users', $id);
            $tsconfig = BackendUtility::getPagesTSconfig($row['pid']);

            $targetPage = (int)($tsconfig['tx_loginlink.']['fe.']['loginPage'] ?? 0);
            if (!$targetPage) {
                return '';
            }
            $url = PreviewUriBuilder::create($targetPage)
                    ->buildUri() . '?byToken=' . $token;
        }

        return (string)$url;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }

}
