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

namespace Magezon\Revisions\Model\TextDiff;

class TextDiffOpDelete extends TextDiffOp {

    /**
     * @param $lines
     */
    function __construct( $lines )
    {
        $this->orig = $lines;
        $this->final = false;
    }

    /**
     * @param $lines
     * @return void
     */
    public function TextDiffOpDelete( $lines ) {
        self::__construct( $lines );
    }

    /**
     * @return TextDiffOpAdd
     */
    function &reverse()
    {
        $reverse = new TextDiffOpAdd($this->orig);
        return $reverse;
    }

}
