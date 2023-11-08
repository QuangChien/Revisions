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

namespace Magezon\Revisions\Model\ResourceModel\Revision;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magezon\Revisions\Model\ResourceModel\Revision as ResourceModel;
use Magezon\Revisions\Model\Revision as Model;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mgz_revisions_collection';

    /**
     * Initialize collection model.
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
