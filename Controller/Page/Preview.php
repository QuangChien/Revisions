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

namespace Magezon\Revisions\Controller\Page;

use Magento\Cms\Helper\Page as PageHelper;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magezon\Revisions\Model\RevisionFactory;

class Preview extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var PageHelper
     */
    private $pageHelper;

    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @param Context $context
     * @param PageHelper $pageHelper
     * @param RevisionFactory $revisionFactory
     */
    public function __construct(
        Context $context,
        PageHelper $pageHelper,
        RevisionFactory $revisionFactory
    ) {
        parent::__construct($context);
        $this->pageHelper = $pageHelper;
        $this->revisionFactory = $revisionFactory;
    }

    /**
     * View CMS page action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $revisionId = $this->_request->getParam('revision_id');
        if($revisionId) {
            $pageId = $this->revisionFactory->create()->load($revisionId)->getEntityId();
            $resultPage = $this->pageHelper->prepareResultPage($this, $pageId);
            if(!$resultPage) {
                $this->_response->setRedirect($this->_redirect->getRefererUrl());
            }
            return $resultPage;
        }else {
            $this->_response->setRedirect($this->_redirect->getRefererUrl());
        }
    }
}
