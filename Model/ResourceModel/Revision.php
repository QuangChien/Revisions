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

namespace Magezon\Revisions\Model\ResourceModel;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Revision extends AbstractDb
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'mgz_revisions_resource_model';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('mgz_revisions', 'revision_id');
        $this->_useIsObjectNew = true;
    }

    /**
     * @param $ids
     * @return void
     * @throws LocalizedException
     */
    public function deleteRevisions($ids)
    {
        $connection = $this->getConnection();
        $table = $this->getMainTable();
        $idFieldName = $this->getIdFieldName();
        $where = $connection->quoteInto("$idFieldName IN (?)", $ids);
        $connection->delete($table, $where);
    }
}
