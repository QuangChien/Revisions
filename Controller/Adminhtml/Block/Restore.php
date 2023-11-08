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

namespace Magezon\Revisions\Controller\Adminhtml\Block;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cms\Model\BlockFactory;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;
use Magezon\Revisions\Model\RevisionFactory;

class Restore extends Action
{
    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @var BlockFactory
     */
    protected $cmsBlockFactory;

    /**
     * @var CollectionFactory
     */
    protected $revisionCollecitonFactory;

    /**
     * @param Context $context
     * @param RevisionFactory $revisionFactory
     * @param BlockFactory $cmsBlockFactory
     * @param CollectionFactory $revisionCollecitonFactory
     */
    public function __construct(
        Context $context,
        RevisionFactory $revisionFactory,
        BlockFactory $cmsBlockFactory,
        CollectionFactory $revisionCollecitonFactory
    ) {
        $this->revisionFactory = $revisionFactory;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->revisionCollecitonFactory = $revisionCollecitonFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $revision = $this->revisionFactory->create()->load($id);
                if($revision->getData()) {
                    $data = unserialize($revision->getRevisionSerialize());
                    $cmsBlock = $this->cmsBlockFactory->create()->load($revision->getEntityId());
                    if($cmsBlock) {
                        $cmsBlock->addData($data);
                        $cmsBlock->save();
                    }else {
                        $cmsBlock = $this->cmsBlockFactory->create();
                        $cmsBlock->setData($data);
                        $cmsBlock->save();
                    }

                    $this->messageManager->addSuccessMessage(__('The page has been restored.'));
                    $resultRedirect->setPath('cms/block/edit', ['block_id' => $cmsBlock->getId()]);
                    return $resultRedirect;
                }else{
                    $this->messageManager->addSuccessMessage(__('The block revision does not exist.'));
                }
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while restore the record: %1',
                    $e->getMessage()));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Record ID is missing.'));
        }
        return $resultRedirect->setRefererUrl();
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magezon_Revisions::revision_block_restore');
    }
}
