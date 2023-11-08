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
use Magezon\Revisions\Model\RevisionFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Backend\App\Action;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;

class   Restore extends Action
{
    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @var PageFactory
     */
    protected $cmsPageFactory;

    /**
     * @var CollectionFactory
     */
    protected $revisionCollecitonFactory;

    /**
     * @param Context $context
     * @param RevisionFactory $revisionFactory
     * @param PageFactory $cmsPage
     * @param CollectionFactory $revisionCollecitonFactory
     */
    public function __construct(
        Context $context,
        RevisionFactory $revisionFactory,
        PageFactory $cmsPageFactory,
        CollectionFactory $revisionCollecitonFactory
    ) {
        $this->revisionFactory = $revisionFactory;
        $this->cmsPageFactory = $cmsPageFactory;
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
                if($revision->getData()){
                    $data = unserialize($revision->getRevisionSerialize());
                    $cmsPage = $this->cmsPageFactory->create()->load($revision->getEntityId());
                    if($cmsPage) {
                        $cmsPage->addData($data);
                        $cmsPage->save();
                    }else{
                        $cmsPage = $this->cmsPageFactory->create();
                        $cmsPage->setData($data);
                        $cmsPage->save();
                    }

                    $this->messageManager->addSuccessMessage(__('The page has been restored.'));
                    $resultRedirect->setPath('cms/page/edit', ['page_id' => $cmsPage->getId()]);
                    return $resultRedirect;
                }else{
                    $this->messageManager->addErrorMessage(__('The page revision does not exist.'));
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
        return $this->_authorization->isAllowed('Magezon_Revisions::revision_page_restore');
    }
}
