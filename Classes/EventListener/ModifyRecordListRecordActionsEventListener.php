<?php
declare(strict_types=1);

namespace GeorgRinger\LoginLink\EventListener;

use GeorgRinger\LoginLink\Service\Validation;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent as ModifyRecordListRecordActionsEventV12;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Recordlist\Event\ModifyRecordListRecordActionsEvent;

class ModifyRecordListRecordActionsEventListener
{

    protected IconFactory $iconFactory;
    protected UriBuilder $uriBuilder;
    protected Validation $validation;

    /**
     * @param IconFactory $iconFactory
     * @param UriBuilder $uriBuilder
     */
    public function __construct(IconFactory $iconFactory, UriBuilder $uriBuilder, Validation $validation)
    {
        $this->iconFactory = $iconFactory;
        $this->uriBuilder = $uriBuilder;
        $this->validation = $validation;
    }

    public function modifyRecordActions(ModifyRecordListRecordActionsEvent $event): void
    {
        $table = $event->getTable();
        $recordId = $event->getRecord()['uid'];
        if ($this->validation->isValid($table, $recordId)) {
            $html = $this->getHtml($table, $recordId);
            $event->setAction(
                $html,
                'loginlink',
                'primary',
                'delete'
            );
        }
    }

    public function modifyRecordActionsV12(ModifyRecordListRecordActionsEventV12 $event): void
    {
        $table = $event->getTable();
        $recordId = $event->getRecord()['uid'];
        if ($this->validation->isValid($table, $recordId)) {
            $html = $this->getHtml($table, $recordId);
            $event->setAction(
                $html,
                'loginlink',
                'primary',
                'delete'
            );
        }
    }

    protected function getHtml(string $table, int $recordId): string
    {
        $lang = $this->getLanguageService();
        $url = $this->uriBuilder->buildUriFromRoute('loginlink_token', [
            'table' => $table,
            'id' => $recordId,
        ]);
        $icon = $this->iconFactory->getIcon('txloginlink-loginlink', Icon::SIZE_SMALL);
        $title = $lang->sL('LLL:EXT:login_link/Resources/Private/Language/locallang.xlf:trigger.title');
        $html = '<button class="btn btn-default t3js-modal-trigger"
        data-title="' . htmlspecialchars($title) . '"
        title="' . htmlspecialchars($title) . '"
        data-bs-content=""
        data-url="' . htmlspecialchars($url) . '"
        >
            ' . $icon->render() . '
    </button>';
        return $html;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
