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

namespace Magezon\Revisions\Model;

class Config
{
    /**
     * Page Revision value
     */
    const REVISION_TYPE_PAGE = 'page';

    /**
     * Block Revision value
     */
    const REVISION_TYPE_BLOCK = 'block';

    /**
     * Magezon Blog Post Revision value
     */
    const REVISION_TYPE_MAGEZON_BLOG_POST = 'magezon_blog_post';

    /**
     * Magezon Blog Post Revision value
     */
    const REVISION_TYPE_MAGEFAN_BLOG_POST = 'magefan_blog_post';
}
