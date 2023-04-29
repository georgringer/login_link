<?php

namespace GeorgRinger\LoginLink\EventListener;

use GeorgRinger\LoginLink\Service\Validation;
use TYPO3\CMS\Backend\RecordList\Event\ModifyRecordListRecordActionsEvent;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;

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
            $url = $this->uriBuilder->buildUriFromRoute('loginlink_token', [
                'table' => $table,
                'id' => $recordId,
            ]);
            $icon = $this->iconFactory->getIcon('txloginlink-loginlink', Icon::SIZE_SMALL);
            $html = '<button class="btn btn-default t3js-modal-trigger"
        data-title="Login link"
        data-bs-content=""
        data-url="' . htmlspecialchars($url) . '"
        >
            ' . $icon->render() . '
    </button>';
            $event->setAction(
                $html,
                'loginlink',
                'primary',
                'delete'
            );
        }
    }
}
