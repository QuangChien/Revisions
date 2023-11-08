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

class TextDiffRenderer {

    protected $_leading_context_lines = 0;

    protected $_trailing_context_lines = 0;

    /**
     * @param $params
     */
    public function __construct( $params = array() )
    {
        foreach ($params as $param => $value) {
            $v = '_' . $param;
            if (isset($this->$v)) {
                $this->$v = $value;
            }
        }
    }

    /**
     * @param $params
     * @return void
     */
    public function TextDiffRenderer( $params = array() ) {
        self::__construct( $params );
    }

    /**
     * @return array
     */
    public function getParams()
    {
        $params = array();
        foreach (get_object_vars($this) as $k => $v) {
            if ($k[0] == '_') {
                $params[substr($k, 1)] = $v;
            }
        }

        return $params;
    }

    /**
     * @param $diff
     * @return string
     */
    public function render($diff)
    {
        $xi = $yi = 1;
        $block = false;
        $context = array();

        $nlead = $this->_leading_context_lines;
        $ntrail = $this->_trailing_context_lines;

        $output = $this->_startDiff();

        $diffs = $diff->getDiff();
        foreach ($diffs as $i => $edit) {
            if (is_a($edit, 'TextDiffOpCopy')) {
                if (is_array($block)) {
                    $keep = $i == count($diffs) - 1 ? $ntrail : $nlead + $ntrail;
                    if (count($edit->orig) <= $keep) {
                        $block[] = $edit;
                    } else {
                        if ($ntrail) {
                            $context = array_slice($edit->orig, 0, $ntrail);
                            $block[] = new TextDiffOpCopy($context);
                        }

                        $output .= $this->_block($x0, $ntrail + $xi - $x0,
                            $y0, $ntrail + $yi - $y0,
                            $block);
                        $block = false;
                    }
                }

                $context = $edit->orig;
            } else {

                if (!is_array($block)) {
                    $context = array_slice($context, count($context) - $nlead);
                    $x0 = $xi - count($context);
                    $y0 = $yi - count($context);
                    $block = array();
                    if ($context) {
                        $block[] = new TextDiffOpCopy($context);
                    }
                }
                $block[] = $edit;
            }

            if ($edit->orig) {
                $xi += count($edit->orig);
            }
            if ($edit->final) {
                $yi += count($edit->final);
            }
        }

        if (is_array($block)) {
            $output .= $this->_block($x0, $xi - $x0,
                $y0, $yi - $y0,
                $block);
        }

        return $output . $this->_endDiff();
    }

    /**
     * @param $obj
     * @return string
     */
    public function getClassName($obj) {
            $class = explode( '\\', get_class($obj) );
            end( $class );
            $last  = key( $class );
            $class = $class[ $last ];
        return $class;
    }

    /**
     * @param $xbeg
     * @param $xlen
     * @param $ybeg
     * @param $ylen
     * @param $edits
     * @return string
     */
    public function _block($xbeg, $xlen, $ybeg, $ylen, &$edits)
    {
        $output = $this->_startBlock($this->_blockHeader($xbeg, $xlen, $ybeg, $ylen));

        foreach ($edits as $edit) {
            switch ($this->getClassName($edit)) {
                case 'TextDiffOpCopy':
                    $output .= $this->_context($edit->orig);
                    break;

                case 'TextDiffOpAdd':
                    $output .= $this->_added($edit->final);
                    break;

                case 'TextDiffOpDelete':
                    $output .= $this->_deleted($edit->orig);
                    break;

                case 'TextDiffOpChange':
                    $output .= $this->_changed($edit->orig, $edit->final);
                    break;
            }
        }

        return $output . $this->_endBlock();
    }

    /**
     * @return string
     */
    public function _startDiff()
    {
        return '';
    }

    /**
     * @return string
     */
    public function _endDiff()
    {
        return '';
    }

    /**
     * @param $xbeg
     * @param $xlen
     * @param $ybeg
     * @param $ylen
     * @return string
     */
    public function _blockHeader($xbeg, $xlen, $ybeg, $ylen)
    {
        if ($xlen > 1) {
            $xbeg .= ',' . ($xbeg + $xlen - 1);
        }
        if ($ylen > 1) {
            $ybeg .= ',' . ($ybeg + $ylen - 1);
        }

        // this matches the GNU Diff behaviour
        if ($xlen && !$ylen) {
            $ybeg--;
        } elseif (!$xlen) {
            $xbeg--;
        }

        return $xbeg . ($xlen ? ($ylen ? 'c' : 'd') : 'a') . $ybeg;
    }

    /**
     * @param $header
     * @return string
     */
    public function _startBlock($header)
    {
        return $header . "\n";
    }

    /**
     * @return string
     */
    public function _endBlock()
    {
        return '';
    }

    /**
     * @param $lines
     * @param $prefix
     * @return string
     */
    public function _lines($lines, $prefix = ' ')
    {
        return $prefix . implode("\n$prefix", $lines) . "\n";
    }

    /**
     * @param $lines
     * @return string
     */
    public function _context($lines)
    {
        return $this->_lines($lines, '  ');
    }

    /**
     * @param $lines
     * @return string
     */
    public function _added($lines)
    {
        return $this->_lines($lines, '> ');
    }

    /**
     * @param $lines
     * @return string
     */
    public function _deleted($lines)
    {
        return $this->_lines($lines, '< ');
    }

    /**
     * @param $orig
     * @param $final
     * @return string
     */
    public function _changed($orig, $final)
    {
        return $this->_deleted($orig) . "---\n" . $this->_added($final);
    }
}
