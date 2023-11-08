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

namespace Magezon\Revisions\Controller\Block;
use Magento\Framework\App\Action\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magezon\Revisions\Model\RevisionFactory;
use Magento\Framework\Registry;
use Magezon\Revisions\Model\Config;

class Preview extends Action
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param RevisionFactory $revisionFactory
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        RevisionFactory $revisionFactory,
        Registry $registry
    )
    {
        $this->pageFactory = $pageFactory;
        $this->revisionFactory = $revisionFactory;
        $this->registry = $registry;
        return parent::__construct($context);
    }

    public function execute()
    {
        $revisionId = $this->_request->getParam('revision_id');
        $revision = $this->revisionFactory->create()->load($revisionId);

        if($revision->getData() && $revision->getData('revision_type') == Config::REVISION_TYPE_BLOCK) {
            $resultPage = $this->pageFactory->create();
            $this->registry->register('revision_current', $revision);
            return $resultPage;
        }else {
            $this->_response->setRedirect($this->_redirect->getRefererUrl());
        }
    }
}
