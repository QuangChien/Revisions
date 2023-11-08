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

namespace Magezon\Revisions\Ui\DataProvider\Block\Form\Modifier;

use Magento\Ui\Component\Form;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\App\Request\Http;

class Revisions implements ModifierInterface
{
    /**
     * Group order
     */
    const GROUP_ORDER = 'revisions';

    /**
     * sort order
     */
    const SORT_ORDER = 1000;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Http
     */
    protected $request;


    public function __construct(
        UrlInterface $urlBuilder,
        Http $request

    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getCurrentBlock()
    {
        return $this->request->getParam('block_id');
    }

    /**
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        if($this->getCurrentBlock()) {
            $meta[static::GROUP_ORDER] = [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'label' => __('Block Revisions'),
                            'collapsible' => true,
                            'opened' => false,
                            'componentType' => Form\Fieldset::NAME,
                            'sortOrder' => 1000
                        ]
                    ]
                ],
                'children' => [
                    'mgz_revision_page_listing' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'autoRender' => true,
                                    'componentType' => 'insertListing',
                                    'dataScope' => 'mgz_revision_block_listing',
                                    'externalProvider' => 'mgz_revision_block_listing.mgz_revision_block_listing_data_source',
                                    'selectionsProvider' => 'mgz_revision_block_listing.mgz_revision_block_listing.revision_block_columns.ids',
                                    'ns' => 'mgz_revision_block_listing',
                                    'render_url' => $this->urlBuilder->getUrl('mui/index/render'),
                                    'realTimeLink' => false,
                                    'behaviourType' => 'simple',
                                    'externalFilterMode' => false,
                                    'imports' => [
                                        'revisionId' => '${ $.provider }:data.block_id',
                                        '__disableTmpl' => ['revisionId' => false],
                                    ],
                                    'exports' => [
                                        'revisionId' => '${ $.externalProvider }:params.block_id',
                                        '__disableTmpl' => ['revisionId' => false],
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        return $meta;
    }

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        $data['block_id'] = $this->getCurrentBlock();
        return $data;
    }
}
