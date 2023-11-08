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

class TextDiffRendererInlines extends TextDiffRenderer {

    public $_leading_context_lines = 10000;

    public $_trailing_context_lines = 10000;

    public $_ins_prefix = '<ins>';

    public $_ins_suffix = '</ins>';

    public $_del_prefix = '<del>';

    public $_del_suffix = '</del>';

    public $_block_header = '';

    public $_split_characters = false;

    public $_split_level = 'lines';

    /**
     * @param $xbeg
     * @param $xlen
     * @param $ybeg
     * @param $ylen
     * @return mixed|string
     */
    public function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        return $this->_block_header;
    }

    /**
     * @param $header
     * @return string
     */
    public function _startBlock($header)
    {
        return $header;
    }

    /**
     * @param $lines
     * @param $prefix
     * @param $encode
     * @return string
     */
    public function _lines($lines, $prefix = ' ', $encode = true)
    {
        if ($encode) {
            array_walk($lines, array(&$this, '_encode'));
        }

        if ($this->_split_level == 'lines') {
            return implode("\n", $lines) . "\n";
        } else {
            return implode('', $lines);
        }
    }

    /**
     * @param $lines
     * @return string
     */
    public function _added($lines)
    {
        array_walk($lines, array(&$this, '_encode'));
        $lines[0] = $this->_ins_prefix . $lines[0];
        $lines[count($lines) - 1] .= $this->_ins_suffix;
        return $this->_lines($lines, ' ', false);
    }

    /**
     * @param $lines
     * @param $words
     * @return string
     */
    public function _deleted($lines, $words = false)
    {
        array_walk($lines, array(&$this, '_encode'));
        $lines[0] = $this->_del_prefix . $lines[0];
        $lines[count($lines) - 1] .= $this->_del_suffix;
        return $this->_lines($lines, ' ', false);
    }

    /**
     * @param $orig
     * @param $final
     * @return string
     */
    public function _changed($orig, $final)
    {
        if ($this->_split_level == 'characters') {
            return $this->_deleted($orig)
                . $this->_added($final);
        }

        if ($this->_split_level == 'words') {
            $prefix = '';
            while ($orig[0] !== false && $final[0] !== false &&
                substr($orig[0], 0, 1) == ' ' &&
                substr($final[0], 0, 1) == ' ') {
                $prefix .= substr($orig[0], 0, 1);
                $orig[0] = substr($orig[0], 1);
                $final[0] = substr($final[0], 1);
            }
            return $prefix . $this->_deleted($orig) . $this->_added($final);
        }

        $text1 = implode("\n", $orig);
        $text2 = implode("\n", $final);

        $nl = "\0";

        if ($this->_split_characters) {
            $diff = new TextDiff('native',
                array(preg_split('//', $text1),
                    preg_split('//', $text2)));
        } else {
            $diff = new TextDiff('native',
                array($this->_splitOnWords($text1, $nl),
                    $this->_splitOnWords($text2, $nl)));
        }

        $renderer = new TextDiffRendererInlines
        (array_merge($this->getParams(),
            array('split_level' => $this->_split_characters ? 'characters' : 'words')));

        return str_replace($nl, "\n", $renderer->render($diff)) . "\n";
    }

    /**
     * @param $string
     * @param $newlineEscape
     * @return array
     */
    public function _splitOnWords($string, $newlineEscape = "\n")
    {
        $string = str_replace("\0", '', $string);

        $words = array();
        $length = strlen($string);
        $pos = 0;

        while ($pos < $length) {
            $spaces = strspn(substr($string, $pos), " \n");
            $nextpos = strcspn(substr($string, $pos + $spaces), " \n");
            $words[] = str_replace("\n", $newlineEscape, substr($string, $pos, $spaces + $nextpos));
            $pos += $spaces + $nextpos;
        }

        return $words;
    }

    /**
     * @param $string
     * @return void
     */
    public function _encode(&$string)
    {
        $string = htmlspecialchars($string);
    }

}
