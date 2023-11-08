<?php
/**
 * Magezon
 *
 * This source file is subject to the Magezon Software License, which is available at https://magezon.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to https://www.magezon.com for more information.
 *
 * @category  Magezon
 * @package   Magezon_Revisions
 * @copyright Copyright (C) 2023 Magezon (https://magezon.com)
 */

namespace Magezon\Revisions\Plugin;
use Magento\Cms\Controller\Adminhtml\Page\Save;
use Magento\Backend\Model\Auth\Session;
use Magezon\Revisions\Model\Config;
use Magezon\Revisions\Helper\Data;
use Magezon\Revisions\Model\RevisionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Framework\Message\ManagerInterface;

class SavePageRevisionPlugin
{
    /**
     * Infinite number
     */
    const INFINITE_NUMBER = 100000000;

    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @var Session
     */
    protected $authSession;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var CollectionFactory
     */
    protected $revisionCollectionFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param RevisionFactory $revisionFactory
     * @param Session $authSession
     * @param Data $helperData
     * @param ResultFactory $resultFactory
     * @param CollectionFactory $revisionCollectionFactory
     * @param PageFactory $pageFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RevisionFactory $revisionFactory,
        Session $authSession,
        Data $helperData,
        ResultFactory $resultFactory,
        CollectionFactory $revisionCollectionFactory,
        PageFactory $pageFactory,
        ManagerInterface $messageManager
    )
    {
        $this->revisionFactory = $revisionFactory;
        $this->authSession = $authSession;
        $this->helperData = $helperData;
        $this->resultFactory = $resultFactory;
        $this->revisionCollectionFactory = $revisionCollectionFactory;
        $this->pageFactory = $pageFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @throws \Exception
     */
    public function aroundExecute(Save $subject, \Closure $proceed)
    {
        $data = $subject->getRequest()->getPostValue();
        $revision = $this->revisionFactory->create();
        $adminUser = $this->authSession->getUser();
        if ($adminUser && $adminUser->getId()) {
            $adminUserId = $adminUser->getId();
        }

        if (isset($data['save_type'])) {
            $proceed();
            $pageId = $data['page_id'] ?: $this->getPageId();
            $dataRevision = [
                'revision_type' => Config::REVISION_TYPE_PAGE,
                'entity_id' => $pageId,
                'admin_user_id' => $adminUserId ?? null,
                'revision_serialize' => serialize(array_intersect_key($data, array_flip(['title', 'content_heading', 'content'])))
            ];
            $revision->setData($dataRevision);
            $revision->save();
            $this->deleteRevision($pageId);
            if($this->getRevisionLimit() > 0) {
                $this->messageManager->addSuccessMessage(__('You created a revision.'));
            }

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/edit', ['page_id' => $pageId]);
            return $resultRedirect;
        }

        if ($this->helperData->isCreatedRevisionOnSave(Config::REVISION_TYPE_PAGE)) {
            $oldData = $this->pageFactory->create()->load($data['page_id'])->getData();
            $proceed();
            $pageId = $data['page_id'] ?: $this->getPageId();
            $oldData = array_intersect_key($oldData, array_flip(['title', 'content_heading', 'content']));
            $newData = array_intersect_key($data, array_flip(['title', 'content_heading', 'content']));
            $newDataSave = $newData;
            ksort($oldData);
            ksort($newData);
            $oldDataJson = json_encode($oldData);
            $newDataJson = json_encode($newData);

            if(strcmp($oldDataJson, $newDataJson) !== 0) {
                $dataRevision = [
                    'revision_type' => Config::REVISION_TYPE_PAGE,
                    'entity_id' => $pageId,
                    'admin_user_id' => $adminUserId ?? null,
                    'revision_serialize' => serialize($newDataSave)
                ];

                $revision->setData($dataRevision);
                $revision->save();
                $this->deleteRevision($pageId);
                if($this->getRevisionLimit() > 0) {
                    $this->messageManager->addSuccessMessage(__('You created a revision.'));
                }
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/edit', ['page_id' => $pageId]);
            return $resultRedirect;
        }

        return $proceed();
    }

    /**
     * @return mixed
     */
    public function getPageId()
    {
        $pageCollection = $this->pageFactory->create()->getCollection();
        return $pageCollection->setOrder('creation_time', 'DESC')->getFirstItem()->getId();
    }

    /**
     * @return int
     */
    public function getRevisionLimit()
    {
        if(is_null($this->helperData->revisionLimit(Config::REVISION_TYPE_PAGE))){
            return self::INFINITE_NUMBER;
        }else{
            return (int)$this->helperData->revisionLimit(Config::REVISION_TYPE_PAGE);
        }
    }

    /**
     * @param $entityId
     * @return void
     * @throws \Exception
     */
    public function deleteRevision($entityId)
    {
        $revisions = $this->revisionCollectionFactory->create()
            ->addFieldToFilter('revision_type', Config::REVISION_TYPE_PAGE)
            ->addFieldToFilter('entity_id', ['like' => $entityId]);
        $revisionTotal = $revisions->getAllIds();
        rsort($revisionTotal);
        $revisionDelete = array_slice($revisionTotal, $this->getRevisionLimit());
        if(count($revisionDelete) > 0) {
            $this->revisionFactory->create()->deleteRevisions($revisionDelete);
        }
    }
}
