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

class TextDiffOpChange extends TextDiffOp {

    function __construct( $orig, $final )
    {
        $this->orig = $orig;
        $this->final = $final;
    }

    /**
     * @param $orig
     * @param $final
     * @return void
     */
    public function TextDiffOpChange( $orig, $final ) {
        self::__construct( $orig, $final );
    }

    /**
     * @return TextDiffOpChange
     */
    function &reverse()
    {
        $reverse = new TextDiffOpChange($this->final, $this->orig);
        return $reverse;
    }

}
