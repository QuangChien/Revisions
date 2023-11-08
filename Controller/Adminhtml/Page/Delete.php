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

namespace Magezon\Revisions\Controller\Adminhtml\Page;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magezon\Revisions\Model\Revision;
use Magento\Backend\App\Action;

class Delete extends Action
{
    /**
     * @var Revision
     */
    protected $revision;

    /**
     * @param Context $context
     * @param Revision $revision
     */
    public function __construct(
        Context $context,
        Revision $revision
    ) {
        $this->revision = $revision;
        parent::__construct($context);
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($id) {
            try {
                $this->revision->load($id)->delete();
                $this->messageManager->addSuccessMessage(__('The record has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('An error occurred while deleting the record: %1',
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
        return $this->_authorization->isAllowed('Magezon_Revisions::revision_page_delete');
    }
}
