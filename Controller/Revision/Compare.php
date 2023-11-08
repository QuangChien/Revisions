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

namespace Magezon\Revisions\Controller\Revision;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magezon\Revisions\Model\ResourceModel\Revision\CollectionFactory;
use Magezon\Revisions\Model\RevisionFactory;
use Magezon\Revisions\Model\Config;
use Magezon\Revisions\Model\TextDiff\TextDiffUi;
use Magento\User\Model\UserFactory;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\BlockFactory;

class Compare extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CollectionFactory
     */
    protected $revisionCollectionFactory;

    /**
     * @var RevisionFactory
     */
    protected $revisionFactory;

    /**
     * @var TextDiffUi
     */
    protected $textDiffUi;

    /**
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var BlockFactory
     */
    protected $blockFactory;

    /**
     * @var string
     */
    protected $_type;

    public $dataCurrent;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param CollectionFactory $revisionCollectionFactory
     * @param RevisionFactory $revisionFactory
     * @param TextDiffUi $textDiffUi
     * @param UserFactory $userFactory
     * @param PageFactory $pageFactory
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        CollectionFactory $revisionCollectionFactory,
        RevisionFactory $revisionFactory,
        TextDiffUi $textDiffUi,
        UserFactory $userFactory,
        PageFactory $pageFactory,
        BlockFactory $blockFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->revisionCollectionFactory = $revisionCollectionFactory;
        $this->revisionFactory = $revisionFactory;
        $this->textDiffUi = $textDiffUi;
        $this->userFactory = $userFactory;
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * @param $revisions
     * @param $dataCurrent
     * @return mixed
     */
    public function addCurrentValueOfEntity(&$revisions, $timeUpdate = '', $dataCurrent = [])
    {
        $revisionEnd = $revisions[count($revisions) - 1];
        if($revisionEnd['revision_type'] == Config::REVISION_TYPE_PAGE) {
            $dataCurrent = $this->pageFactory->create()->load($revisionEnd['entity_id'])->getData();
            $timeUpdate = $dataCurrent['update_time'];
            $dataCurrent = array_intersect_key($dataCurrent, array_flip(['title', 'content_heading', 'content']));
        }elseif($revisionEnd['revision_type'] == Config::REVISION_TYPE_BLOCK) {
            $dataCurrent = $this->blockFactory->create()->load($revisionEnd['entity_id'])->getData();
            $timeUpdate = $dataCurrent['update_time'];
            $dataCurrent = array_intersect_key($dataCurrent, array_flip(['title', 'content']));
        }

        $revisionEnd = unserialize($revisionEnd['revision_serialize']);
        $dataCurrentSort = $dataCurrent;
        ksort($dataCurrentSort);
        ksort($revisionEnd);
        $dataCurrentJson = json_encode($dataCurrentSort);
        $revisionEndJson = json_encode($revisionEnd);
        if(strcmp($dataCurrentJson, $revisionEndJson) != 0) {
            $arrayAdd = [
                'revision_serialize' => serialize($dataCurrent),
                'created_at' => $timeUpdate,
                'admin_user_id' => null,
                'revision_id' => null
            ];
            $revisions[] = $arrayAdd;
        }else{
            $revisions[count($revisions) - 1]['admin_user_id'] = null;
        }

        return $revisions;
    }

    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->resultRedirectFactory->create()->setPath('');
        }
        try {
            $data = $this->getRequest()->getPostValue();
            $revisions = $this->revisionCollectionFactory->create();
            if($data['revisionType'] == Config::REVISION_TYPE_PAGE) {
                $revisions->addFieldToFilter('revision_type', Config::REVISION_TYPE_PAGE);
            }elseif($data['revisionType'] == Config::REVISION_TYPE_BLOCK){
                $revisions->addFieldToFilter('revision_type', Config::REVISION_TYPE_BLOCK);
            }elseif($data['revisionType'] == Config::REVISION_TYPE_MAGEZON_BLOG_POST){
                $revisions->addFieldToFilter('revision_type', Config::REVISION_TYPE_MAGEZON_BLOG_POST);
            }
            $revisions->addFieldToFilter('entity_id', $data['entityId']);
            $revisions = $revisions->getData();

            $revisions = $this->addCurrentValueOfEntity($revisions);

            $revisionIndex1 = $data['revisionIndex1'];
            if(isset($data['revisionIndex2'])) {
                $revisionIndex2 = $data['revisionIndex2'];
            }else {
                $revisionIndex1 = $revisionIndex1 - 1;
                $revisionIndex2 = $revisionIndex1 + 1;
            }
            if($revisionIndex1 < 0) {
                $revisionIndex1 = 0;
                $temp = $revisions[$revisionIndex2]['revision_serialize'];
                $revisionIndex2 = 1;
                $revisions[$revisionIndex2]['revision_serialize'] = $temp;

                $revisions[$revisionIndex2]['revision_serialize'] = unserialize($revisions[$revisionIndex2]['revision_serialize']);
                $revisions[$revisionIndex1]['revision_serialize'] = unserialize($revisions[$revisionIndex1]['revision_serialize']);
                unset($revisions['revision_id'], $revisions['revision_type'], $revisions['entity_id']);
                foreach (array_keys($revisions[$revisionIndex2]['revision_serialize']) as $value) {
                    $revisions[$revisionIndex1]['revision_serialize'][$value] = [];
                }
            }else{
                $revisions[$revisionIndex1]['revision_serialize'] = unserialize($revisions[$revisionIndex1]['revision_serialize']);
                $revisions[$revisionIndex2]['revision_serialize'] = unserialize($revisions[$revisionIndex2]['revision_serialize']);

                unset($revisions['revision_id'], $revisions['revision_type'], $revisions['entity_id']);
            }

            $content = '';
            $compareKeys = array_intersect_key($revisions[$revisionIndex1]['revision_serialize'],
                $revisions[$revisionIndex2]['revision_serialize']);
            foreach ($compareKeys as $key => $value) {
                if(!empty($revisions[$revisionIndex1]['revision_serialize'][$key]) ||
                    !empty($revisions[$revisionIndex2]['revision_serialize'][$key])) {
                    $content .= "<h3 class='revision-title'>" . ucfirst(str_replace("_", " ", $key)) . "</h3>";
                    $content .= $this->textDiffUi->textDiff($revisions[$revisionIndex1]['revision_serialize'][$key],
                        $revisions[$revisionIndex2]['revision_serialize'][$key]);
                }
            }

            $response = [
                'content' => $content,
                'admin_username' => [
                    'from' => $this->getRevisionAuthor($revisions[$revisionIndex1]['admin_user_id']),
                    'to' => $this->getRevisionAuthor($revisions[$revisionIndex2]['admin_user_id']),
                ],
                'time_create' => [
                    'from' => $revisions[$revisionIndex1]['created_at'],
                    'to' => $revisions[$revisionIndex2]['created_at']
                ],
                'current_revisionId' => $revisions[$data['revisionIndex1']]['revision_id']
            ];
        } catch (Exception $e) {
            $response = ['code' => 500, 'message' => $e->getMessage()];
        }
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($response);
        return $resultJson;
    }

    public function getRevisionAuthor($userId)
    {
        if($userId) {
            $adminUser = $this->userFactory->create()->load($userId);
            if($adminUser) {
                return __('Revision by ') . $adminUser->getUsername();
            }else{
                return 'User has been deleted';
            }
        }else{
            return __('Current data');
        }
    }
}
