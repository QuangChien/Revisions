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

namespace Magezon\Revisions\Ui\Component\MassAction;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Filter extends \Magento\Ui\Component\MassAction\Filter {


    /**
     * @var DataProviderInterface
     */
    private $dataProvider;

    public function __construct(
        UiComponentFactory $factory,
        RequestInterface $request,
        FilterBuilder $filterBuilder
    )
    {
        parent::__construct($factory, $request, $filterBuilder);
    }

    /**
     * @param AbstractDb $collection
     * @return AbstractDb
     * @throws LocalizedException
     */
    public function getCollection(AbstractDb $collection)
    {
        $selected = $this->request->getParam(static::SELECTED_PARAM);
        $excluded = $this->request->getParam(static::EXCLUDED_PARAM);

        $isExcludedIdsValid = (is_array($excluded) && !empty($excluded));
        $isSelectedIdsValid = (is_array($selected) && !empty($selected));

        if ('false' !== $excluded) {
            if (!$isExcludedIdsValid && !$isSelectedIdsValid) {
                throw new LocalizedException(__('An item needs to be selected. Select and try again.'));
            }
        }

        $this->applySelectionOnTargetProvider();
        $filterIds = [];
        if(!$selected) {
            $dataProvider = $this->getDataProvider();
            $dataProvider->setLimit(0, false);
            $searchResult = $dataProvider->getSearchResult();

            foreach ($searchResult->getItems() as $key => $item) {
                if(in_array($key, is_array($excluded) ? $excluded : [])) continue;
                $filterIds[] = $item->getId();
            }
        }

        if (\is_array($selected)) {
            $filterIds = array_unique(array_merge($filterIds, $selected));
        }
        $collection->addFieldToFilter(
            $collection->getResource()->getIdFieldName(),
            ['in' => $filterIds]
        );

        return $collection;
    }

    /**
     * @return DataProviderInterface
     * @throws LocalizedException
     */
    private function getDataProvider()
    {
        if (!$this->dataProvider) {
            $component = $this->getComponent();
            $this->prepareComponent($component);
            $this->dataProvider = $component->getContext()->getDataProvider();
        }
        return $this->dataProvider;
    }
}