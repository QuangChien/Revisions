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

namespace Magezon\Revisions\Controller\Adminhtml\Block;

use Magento\Backend\App\Action;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Backend\Model\UrlInterface;

class Preview extends Action
{

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ResponseHttp
     */
    protected $response;

    /**
     * @var UrlInterface
     */
    protected $backendUrl;

    /**
     * @param Action\Context $context
     * @param Http $http
     * @param ResponseHttp $response
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        Action\Context $context,
        Http $http,
        ResponseHttp $response,
        UrlInterface $backendUrl

    ) {
        $this->request = $http;
        $this->response = $response;
        $this->backendUrl = $backendUrl;
        parent::__construct($context);
    }

    public function execute()
    {
        $baseUrl = $this->backendUrl->getBaseUrl();
        $revisionId = $this->request->getParam('id');
        $this->response->setRedirect($baseUrl . 'mgz_revision/block/preview/revision_id/' . $revisionId);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magezon_Revisions::revision_block_preview');
    }
}
