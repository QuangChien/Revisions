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

class TextDiffOpAdd extends TextDiffOp {

    /**
     * @param $lines
     */
    function __construct( $lines )
    {
        $this->final = $lines;
        $this->orig = false;
    }

    /**
     * @param $lines
     * @return void
     */
    public function TextDiffOpAdd( $lines ) {
        self::__construct( $lines );
    }

    /**
     * @return TextDiffOpDelete
     */
    function &reverse()
    {
        $reverse = new TextDiffOpDelete($this->final);
        return $reverse;
    }

}
