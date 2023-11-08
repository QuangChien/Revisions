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

class TextDiff {

    public $_edits;

    /**
     * @param $engine
     * @param $params
     */
    public function __construct( $engine, $params )
    {
        if (!is_string($engine)) {
            $params = array($engine, $params);
            $engine = 'auto';
        }

        $diff_engine = new TextDiffEngineNative();
        $this->_edits = call_user_func_array(array($diff_engine, 'diff'), $params);
    }

    /**
     * @param $engine
     * @param $params
     * @return void
     */
    public function TextDiff( $engine, $params ) {
        self::__construct( $engine, $params );
    }

    /**
     * @return mixed
     */
    public function getDiff()
    {
        return $this->_edits;
    }

    /**
     * @param $line
     * @param $key
     * @return void
     */
    public static function trimNewlines(&$line, $key)
    {
        $line = str_replace(array("\n", "\r"), '', $line);
    }

}
