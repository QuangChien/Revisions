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

namespace Magezon\Revisions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /**
     * Section Revisions Extension Path in system.xml
     */
    const XML_PATH_MGZ_REVISIONS = 'mgz_revisions';

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param string $revisionType
     * @return mixed
     */
    public function isCreatedRevisionOnSave($revisionType)
    {
        return $this->getConfigValue(
            self::XML_PATH_MGZ_REVISIONS . '/revision_' . $revisionType . '/revision_' . $revisionType . '_save'
        );
    }

    /**
     * @param $revisionType
     * @return mixed
     */
    public function revisionLimit($revisionType)
    {
        return $this->getConfigValue(
            self::XML_PATH_MGZ_REVISIONS . '/revision_' . $revisionType . '/revision_' . $revisionType . '_limit'
        );
    }
}
