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
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;

class TokenController
{

    protected TokenGenerator $tokenGenerator;
    protected TokenRepository $tokenRepository;
    protected Validation $validation;

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

        if (!$this->validation->isValid($table, $recordId)) {
            return new HtmlResponse('Not valid');
        }

        $token = $this->tokenGenerator->generate();
        $authType = $table === 'fe_users' ? 'fe' : 'be';

        $this->tokenRepository->add(
            $recordId,
            $authType,
            $token
        );
        $url = $this->getUrl($recordId, $token, $authType);

        $content = '<div><h3>Login link</h3>';

        if ($authType === 'fe' && !$url) {
            $content .= 'error: no configuration found';
        } else {
            $content .= '<p>This login link is only valid <strong>once</strong>! Use URL in a different browser.</p>
<textarea readonly class="form-control">' . htmlspecialchars($url) . '</textarea>
</div>';
        }


        return new HtmlResponse($content);
    }

    private function getUrl(int $id, string $token, string $authType): ?string
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
                return null;
            }
            $url = PreviewUriBuilder::create($targetPage)
                    ->buildUri() . '?byToken=' . $token;
        }

        return $url;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

}