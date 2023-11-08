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

namespace Magezon\Revisions\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;
use Magezon\Revisions\Model\RevisionFactory;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\BlockFactory;
use Magezon\Revisions\Model\Config;
use Magento\Framework\Registry;

class Compare extends Template
{
    /**
     * Controller compare revision block path
     */
    const REVISION_BLOCK_COMPARE_PATH = 'mgz_revision_block_compare';

    /**
     * Controller compare revision page path
     */
    const REVISION_PAGE_COMPARE_PATH = 'mgz_revision_page_compare';


    /**
     * @var CollectionFactory
     */
    protected $revisionCollectionFactory;

    /**
     * @var AssetRepository
     */
    protected $_assetRepo;

    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param CollectionFactory $revisionCollectionFactory
     * @param Template\Context $context
     * @param AssetRepository $assetRepo
     * @param RevisionFactory $revisionFactory
     * @param RequestInterface $request
     * @param PageFactory $pageFactory
     * @param BlockFactory $blockFactory
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        CollectionFactory $revisionCollectionFactory,
        Template\Context $context,
        AssetRepository $assetRepo,
        RevisionFactory $revisionFactory,
        RequestInterface $request,
        PageFactory $pageFactory,
        BlockFactory $blockFactory,
        Registry $registry,
        array $data = []
    ) {
        $this->revisionCollectionFactory = $revisionCollectionFactory;
        $this->_assetRepo = $assetRepo;
        $this->revisionFactory = $revisionFactory;
        $this->request = $request;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @param $revisions
     * @param $revisionEnd
     * @param $dataCurrent
     * @return int|void
     */
    public function getCountRecord(&$revisions, $revisionEnd, $dataCurrent)
    {
        $revisionEnd = unserialize($revisionEnd['revision_serialize']);
        ksort($dataCurrent);
        ksort($revisionEnd);
        $dataCurrentJson = json_encode($dataCurrent);
        $revisionEndJson = json_encode($revisionEnd);
        if(strcmp($dataCurrentJson, $revisionEndJson) != 0) {
            return $revisions->getSize() + 1;
        }
        return $revisions->getSize();
    }

    /**
     * @return int
     */
    public function getCountRevision()
    {
        if($this->getRevisionType() == Config::REVISION_TYPE_BLOCK) {
            $revisions = $this->registry->registry('current_block_revisions');
            $revisionEnd = $revisions->getLastItem()->getData();
            $dataCurrent = $this->blockFactory->create()->load($revisionEnd['entity_id'])->getData();
            $dataCurrent = array_intersect_key($dataCurrent, array_flip(['title', 'content']));
            return $this->getCountRecord($revisions, $revisionEnd, $dataCurrent);
        }elseif($this->getRevisionType() == Config::REVISION_TYPE_PAGE){
            $revisions = $this->registry->registry('current_page_revisions');
            $revisionEnd = $revisions->getLastItem()->getData();
            $dataCurrent = $this->pageFactory->create()->load($revisionEnd['entity_id'])->getData();
            $dataCurrent = array_intersect_key($dataCurrent, array_flip(['title', 'content_heading', 'content']));
            return $this->getCountRecord($revisions, $revisionEnd, $dataCurrent);
        }
    }

    /**
     * @return string
     */
    public function getRevisionType()
    {
        $controllerName = $this->request->getFullActionName();
        if($controllerName == self::REVISION_BLOCK_COMPARE_PATH) {
            return Config::REVISION_TYPE_BLOCK;
        }elseif($controllerName == self::REVISION_PAGE_COMPARE_PATH){
            return Config::REVISION_TYPE_PAGE;
        }
    }


    /**
     * @return string
     */
    public function getAvatarDefault()
    {
        return $this->_assetRepo->getUrl("Magezon_Revisions::images/avatar.png");
    }

    /**
     * @return string
     */
    public function getLoadingImage()
    {
        return $this->_assetRepo->getUrl("Magezon_Revisions::images/default/loading.gif");
    }

    /**
     * @return mixed
     */
    public function getEntityId()
    {
        return $this->getRequest()->getParam('id');
    }

    /**
     * @return string|void
     */
    public function getEntityTitle()
    {
        if($this->getRevisionType() == Config::REVISION_TYPE_PAGE) {
            $cmsPage = $this->pageFactory->create()->load($this->getEntityId());
            $title = $cmsPage->getTitle();
        }elseif($this->getRevisionType() == Config::REVISION_TYPE_BLOCK) {
            $cmsBlock = $this->blockFactory->create()->load($this->getEntityId());
            $title = $cmsBlock->getTitle();
        }else{
            $title = '';
        }
        return $title;
    }

    /**
     * @return mixed
     */
    public function getBackUrl()
    {
        return $this->request->getServer('HTTP_REFERER');
    }

    /**
     * @return string|void
     */
    public function getEntityUrl()
    {
        if($this->getRevisionType() == Config::REVISION_TYPE_BLOCK) {
            return $this->_urlBuilder->getUrl(
                'cms/block/edit',
                ['block_id' => $this->getEntityId(), '_nosid' => true]
            );
        }elseif($this->getRevisionType() == Config::REVISION_TYPE_PAGE){
            return $this->_urlBuilder->getUrl(
                'cms/page/edit',
                ['page_id' => $this->getEntityId(), '_nosid' => true]
            );
        }
    }

    /**
     * @param $revisionId
     * @return string|void
     */
    public function getRestorePath()
    {
        if($this->getRevisionType() == Config::REVISION_TYPE_BLOCK) {
            return $this->_urlBuilder->getUrl(
                'mgz_revision/block/restore',
                ['id' => $this->getEntityId(), '_nosid' => true]
            );
        }elseif($this->getRevisionType() == Config::REVISION_TYPE_PAGE){
            return $this->_urlBuilder->getUrl(
                'mgz_revision/page/restore',
                ['id' => $this->getEntityId(), '_nosid' => true]
            );
        }
    }
}
