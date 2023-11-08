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

namespace Magezon\Revisions\Block\Adminhtml\Renderer\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Module\ModuleResource;

class Version extends Field
{
    /**
     * @var ModuleResource
     */
    protected $moduleResource;

    /**
     * @param ModuleResource $moduleResource
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        ModuleResource $moduleResource,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->moduleResource = $moduleResource;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->moduleResource->getDataVersion('Magezon_Revisions');
    }
}
