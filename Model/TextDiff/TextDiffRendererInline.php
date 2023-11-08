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

class TextDiffRendererInline extends TextDiffRendererInlines {

    /**
     * @param $string
     * @param $newlineEscape
     * @return array|false|string|string[]
     */
    public function _splitOnWords( $string, $newlineEscape = "\n" ) {
        $string = str_replace( "\0", '', $string );
        $words  = preg_split( '/([^\w])/u', $string, -1, PREG_SPLIT_DELIM_CAPTURE );
        $words  = str_replace( "\n", $newlineEscape, $words );
        return $words;
    }

}
