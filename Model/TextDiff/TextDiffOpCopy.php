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

class TextDiffOpCopy extends TextDiffOp {

    /**
     * @param $orig
     * @param $final
     */
    public function __construct( $orig, $final = false )
    {
        if (!is_array($final)) {
            $final = $orig;
        }
        $this->orig = $orig;
        $this->final = $final;
    }

    /**
     * @param $orig
     * @param $final
     * @return void
     */
    public function TextDiffOpCopy( $orig, $final = false ) {
        self::__construct( $orig, $final );
    }

    /**
     * @return TextDiffOpCopy
     */
    public function &reverse()
    {
        $reverse = new TextDiffOpCopy($this->final, $this->orig);
        return $reverse;
    }
}
