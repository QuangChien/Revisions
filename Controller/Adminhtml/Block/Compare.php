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

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;
use Magezon\Revisions\Model\Config;
use Magento\Framework\Registry;

class Compare extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CollectionFactory
     */
    protected $revisionCollectionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     * @param CollectionFactory $revisionCollectionFactory
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory,
        CollectionFactory $revisionCollectionFactory,
        Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->revisionCollectionFactory = $revisionCollectionFactory;
        $this->registry = $registry;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();

        $blockId = $this->getRequest()->getParam('id');
        $revisions = $this->revisionCollectionFactory->create()
        ->addFieldToFilter('revision_type', Config::REVISION_TYPE_BLOCK)
        ->addFieldToFilter('entity_id', ['like' => $blockId]);

        if(!isset($blockId) || $revisions->getSize() == 0 || $revisions->getSize() < 2){
            $this->messageManager->addErrorMessage(__('You can only compare when there are 2 or more revisions.'));
            $resultRedirect->setPath('mgz_revision/block/index');
            return $resultRedirect;
        }
        $this->registry->register('current_block_revisions', $revisions);
        $this->_setActiveMenu("Magezon_Revisions::revisions");
        $resultPage->getConfig()->getTitle()->prepend(__('Block Revision List'));
        return $resultPage;
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magezon_Revisions::revision_block_compare');
    }
}
