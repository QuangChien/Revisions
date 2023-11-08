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

namespace Magezon\Revisions\Block\Revision\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;
use Magento\Cms\Model\Template\FilterProvider;

class Preview extends Template
{

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FilterProvider $filterProvider
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FilterProvider $filterProvider
    )
    {
        parent::__construct($context);
        $this->registry = $registry;
        $this->filterProvider = $filterProvider;
    }

    /**
     * @return mixed|null
     */
    public function getCurrentRevision()
    {
        return $this->registry->registry('revision_current');
    }

    /**
     * @return mixed|string
     */
    public function getContentPreview()
    {
        $revision = $this->getCurrentRevision();
        $html = '';
        if($revision) {
            $blockData = unserialize($revision->getRevisionSerialize());
            $html = $this->filterProvider->getBlockFilter()->filter($blockData['content']);
        }
        return $html;
    }
}
