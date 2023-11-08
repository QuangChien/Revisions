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

namespace Magezon\Revisions\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magezon\Revisions\Model\Config;

class RevisionActions extends Column
{
    /**
     * Url delete revision page
     */
    const URL_PATH_PAGE_DELETE = 'mgz_revision/page/delete';

    /**
     * Url preview revision page
     */
    const URL_PATH_PAGE_PREVIEW = 'mgz_revision/page/preview';

    /**
     * Url restore revision page
     */
    const URL_PATH_PAGE_RESTORE = 'mgz_revision/page/restore';

    /**
     * Url compare revision page
     */
    const URL_PATH_PAGE_COMPARE = 'mgz_revision/page/compare';

    /**
     * Url delete revision block
     */
    const URL_PATH_BLOCK_DELETE = 'mgz_revision/block/delete';

    /**
     * Url preview revision block
     */
    const URL_PATH_BLOCK_PREVIEW = 'mgz_revision/block/preview';

    /**
     * Url restore revision block
     */
    const URL_PATH_BLOCK_RESTORE = 'mgz_revision/block/restore';

    /**
     * Url compare revision block
     */
    const URL_PATH_BLOCK_COMPARE = 'mgz_revision/block/compare';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $HelloWorldFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $HelloWorldFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $HelloWorldFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['revision_id'])) {
                    $item[$this->getData('name')] = [
                        'preview' => [
                            'href' => $this->urlBuilder->getUrl(
                                $item['revision_type'] == Config::REVISION_TYPE_PAGE ?
                                    static::URL_PATH_PAGE_PREVIEW : static::URL_PATH_BLOCK_PREVIEW,
                                [
                                    'id' => $item['revision_id'],
                                ]
                            ),
                            'label' => __('Preview'),
                            'target' => '_blank'
                        ],
                        'compare' => [
                            'href' => $this->urlBuilder->getUrl(
                                $item['revision_type'] == Config::REVISION_TYPE_PAGE ?
                                    static::URL_PATH_PAGE_COMPARE : static::URL_PATH_BLOCK_COMPARE,
                                [
                                    'id' => $item['entity_id'],
                                ]
                            ),
                            'label' => __('Compare'),
                            'target' => '_blank'
                        ],
                        'restore' => [
                            'href' => $this->urlBuilder->getUrl(
                                $item['revision_type'] == Config::REVISION_TYPE_PAGE ?
                                    static::URL_PATH_PAGE_RESTORE : static::URL_PATH_BLOCK_RESTORE,
                                [
                                    'id' => $item['revision_id'],
                                ]
                            ),
                            'label' => __('Restore'),
                            'confirm' => [
                                'title' => __('Restore record #%1?', $item['revision_id']),
                                'message' => __('Are you sure you want to restore the record #%1?', $item['revision_id'])
                            ],
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                $item['revision_type'] == Config::REVISION_TYPE_PAGE ?
                                    static::URL_PATH_PAGE_DELETE : static::URL_PATH_BLOCK_DELETE,
                                [
                                    'id' => $item['revision_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete record #%1?', $item['revision_id']),
                                'message' => __('Are you sure you want to delete the record #%1?', $item['revision_id']),
                            ],
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
