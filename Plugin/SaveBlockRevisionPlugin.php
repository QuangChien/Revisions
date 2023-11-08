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
use Magento\Cms\Controller\Adminhtml\Block\Save;
use Magento\Backend\Model\Auth\Session;
use Magezon\Revisions\Model\Config;
use Magezon\Revisions\Helper\Data;
use Magezon\Revisions\Model\RevisionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;
use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Message\ManagerInterface;

class SaveBlockRevisionPlugin
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
     * @var BlockFactory
     */
    protected $blockFactory;

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
     * @param BlockFactory $blockFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        RevisionFactory $revisionFactory,
        Session $authSession,
        Data $helperData,
        ResultFactory $resultFactory,
        CollectionFactory $revisionCollectionFactory,
        BlockFactory $blockFactory,
        ManagerInterface $messageManager
    )
    {
        $this->revisionFactory = $revisionFactory;
        $this->authSession = $authSession;
        $this->helperData = $helperData;
        $this->resultFactory = $resultFactory;
        $this->revisionCollectionFactory = $revisionCollectionFactory;
        $this->blockFactory = $blockFactory;
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
            $blockId = $data['block_id'] ?: $this->getBlockId();
            $dataRevision = [
                'revision_type' => Config::REVISION_TYPE_BLOCK,
                'entity_id' => $blockId,
                'admin_user_id' => $adminUserId ?? null,
                'revision_serialize' => serialize(array_intersect_key($data, array_flip(['title', 'content'])))
            ];
            $revision->setData($dataRevision);
            $revision->save();
            $this->deleteRevision($blockId);
            if($this->getRevisionLimit() > 0) {
                $this->messageManager->addSuccessMessage(__('You created a revision.'));
            }

            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/edit', ['block_id' => $blockId]);
            return $resultRedirect;
        }

        if ($this->helperData->isCreatedRevisionOnSave(Config::REVISION_TYPE_BLOCK)) {
            $oldData = $this->blockFactory->create()->load($data['block_id'])->getData();
            $proceed();
            $blockId = $data['block_id'] ?: $this->getBlockId();
            $oldData = array_intersect_key($oldData, array_flip(['title', 'content']));
            $newData = array_intersect_key($data, array_flip(['title', 'content']));
            $newDataSave = $newData;
            ksort($oldData);
            ksort($newData);
            $oldDataJson = json_encode($oldData);
            $newDataJson = json_encode($newData);

            if(strcmp($oldDataJson, $newDataJson) !== 0) {
                $dataRevision = [
                    'revision_type' => Config::REVISION_TYPE_BLOCK,
                    'entity_id' => $blockId,
                    'admin_user_id' => $adminUserId ?? null,
                    'revision_serialize' => serialize($newDataSave)
                ];

                $revision->setData($dataRevision);
                $revision->save();
                $this->deleteRevision($blockId);
                if($this->getRevisionLimit() > 0) {
                    $this->messageManager->addSuccessMessage(__('You created a revision.'));
                }
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath('*/*/edit', ['block_id' => $blockId]);
            return $resultRedirect;
        }

        return $proceed();
    }

    /**
     * @return mixed
     */
    public function getBlockId()
    {
        $blockCollection = $this->blockFactory->create()->getCollection();
        return $blockCollection->setOrder('creation_time', 'DESC')->getFirstItem()->getId();
    }

    /**
     * @return int
     */
    public function getRevisionLimit()
    {
        if(is_null($this->helperData->revisionLimit(Config::REVISION_TYPE_BLOCK))){
            return self::INFINITE_NUMBER;
        }else{
            return (int)$this->helperData->revisionLimit(Config::REVISION_TYPE_BLOCK);
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
            ->addFieldToFilter('revision_type', Config::REVISION_TYPE_BLOCK)
            ->addFieldToFilter('entity_id', ['like' => $entityId]);
        $revisionTotal = $revisions->getAllIds();
        rsort($revisionTotal);
        $revisionDelete = array_slice($revisionTotal, $this->getRevisionLimit());
        if(count($revisionDelete) > 0) {
            $this->revisionFactory->create()->deleteRevisions($revisionDelete);
        }
    }
}
