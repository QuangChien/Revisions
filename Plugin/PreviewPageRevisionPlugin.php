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
use Magento\Cms\Block\Page;
use Magento\Framework\App\RequestInterface;
use Magento\Cms\Model\PageFactory;
use Magezon\Revisions\Model\RevisionFactory;

class PreviewPageRevisionPlugin
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * Page Preview controller path
     */
    const MGZ_PAGE_PREVIEW_CONTROLLER_PATH = 'mgz_revision_page_preview';

    /**
     * @param RequestInterface $request
     * @param PageFactory $pageFactory
     * @param RevisionFactory $revisionFactory
     */
    public function __construct(
        RequestInterface $request,
        PageFactory $pageFactory,
        RevisionFactory $revisionFactory
    )
    {
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->revisionFactory = $revisionFactory;
    }

    public function aroundGetPage(Page $subject, callable $proceed)
    {
        if ($this->request->getFullActionName() == self::MGZ_PAGE_PREVIEW_CONTROLLER_PATH) {
            if (!$subject->hasData('page')) {
                $page = $this->pageFactory->create();
                $revisionId = $this->request->getParam('revision_id');
                $revision = $this->revisionFactory->create()->load($revisionId);
                $revisionData = unserialize($revision->getRevisionSerialize());
                foreach ($revisionData as $key => $item) {
                    $page->setData($key, $item);
                }
                $page->load($subject->getPageId(), 'identifier');
                $subject->setData('page', $page);
            }
            return $subject->getData('page');
        }
        return $proceed();
    }
}
