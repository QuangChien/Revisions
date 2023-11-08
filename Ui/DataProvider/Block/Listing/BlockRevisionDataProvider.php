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

namespace Magezon\Revisions\Ui\DataProvider\Block\Listing;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class BlockRevisionDataProvider extends DataProvider
{
    /**
     * @param SearchResultInterface $searchResult
     * @return array
     */
    protected function searchResultToOutput(SearchResultInterface $searchResult)
    {
        $arrItems = [];
        $arrItems['items'] = [];

        foreach ($searchResult->getItems() as $item) {
            $itemData = [];
            foreach ($item->getCustomAttributes() as $attribute) {
                if($attribute->getAttributeCode() == 'revision_serialize' && $attribute->getValue()) {
                    $pageData = unserialize($attribute->getValue());
                    foreach ($pageData as $key => $fieldItem) {
                        $itemData[$key] = $fieldItem;
                    }
                }
                $itemData[$attribute->getAttributeCode()] = $attribute->getValue();
            }

            $arrItems['items'][] = $itemData;
        }

        $arrItems['totalRecords'] = $searchResult->getTotalCount();

        return $arrItems;
    }

    /**
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter)
    {
        if($filter->getField() == 'title' || $filter->getField() == 'identifier' || $filter->getField() == 'fulltext'){
            $filter->setConditionType('like');
            $filter->setField('revision_serialize');
            $filter->setValue('%' . $filter->getValue() . '%');
            parent::addFilter($filter);
        }
    }
}
