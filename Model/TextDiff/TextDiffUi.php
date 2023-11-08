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

class TextDiffUi {
    /**
     * @param $compare_from
     * @param $compare_to
     * @return string
     */
    public function getRevisionUiDiff( $compare_from, $compare_to ) {
        $args = array(
            'show_split_view' => true,
            'title_left'      => __('Removed'),
            'title_right'     => __('Added'),
        );

        $diff = $this->textDiff( $compare_from, $compare_to, $args );

        if (!$diff ) {
            $diff = '<table class="diff"><colgroup><col class="content diffsplit left"><col class="content diffsplit middle"><col class="content diffsplit right"></colgroup><tbody><tr>';

            if ( true === $args['show_split_view'] ) {
                $diff .= '<td>' . $compare_from . '</td><td></td><td>' . $compare_to . '</td>';
            } else {
                $diff .= '<td>' . $compare_from . '</td>';

                if ($compare_from!== $compare_to) {
                    $diff .= '</tr><tr><td>' . $compare_to . '</td>';
                }
            }

            $diff .= '</tr></tbody>';
            $diff .= '</table>';
        }

        return $diff;
    }

    /**
     * @param $string
     * @param $array
     * @return void
     */
    public function parseStr( $string, &$array ) {
        parse_str( (string) $string, $array );
    }

    /**
     * @param $args
     * @param $defaults
     * @return array
     */
    public function parseArgs( $args, $defaults = array() ) {
        if ( is_object( $args ) ) {
            $parsed_args = get_object_vars( $args );
        } elseif ( is_array( $args ) ) {
            $parsed_args =& $args;
        } else {
            $this->parseStr( $args, $parsed_args );
        }

        if ( is_array( $defaults ) && $defaults ) {
            return array_merge( $defaults, $parsed_args );
        }
        return $parsed_args;
    }

    /**
     * @param $str
     * @return array|string|string[]|null
     */
    public function normalize_whitespace( $str ) {
        $str = $str ? trim( $str ) : '';
        $str = str_replace( "\r", "\n", $str );
        $str = preg_replace( array( '/\n+/', '/[ \t]+/' ), array( "\n", ' ' ), $str );
        return $str;
    }

    /**
     * @param $left_string
     * @param $right_string
     * @param $args
     * @return string
     */
    public function textDiff( $left_string, $right_string, $args = null ) {
        $defaults = array(
            'title'           => '',
            'title_left'      => '',
            'title_right'     => '',
            'show_split_view' => true,
        );
        $args = $this->parseArgs( $args, $defaults );

        $left_string  = $this->normalize_whitespace( $left_string );
        $right_string = $this->normalize_whitespace( $right_string );

        $left_lines  = explode( "\n", $left_string );
        $right_lines = explode( "\n", $right_string );
        $text_diff   = new TextDiff($left_lines, $right_lines );
        $renderer    = new TextDiffRendererTable( $args );
        $diff        = $renderer->render( $text_diff );

        if ( ! $diff ) {
            return '';
        }

        $is_split_view = !empty( $args['show_split_view'] );
        $is_split_view_class = $is_split_view ? ' is-split-view' : '';

        $r = "<table class='diff$is_split_view_class'>\n";

        if ( $args['title'] ) {
            $r .= "<caption class='diff-title'>$args[title]</caption>\n";
        }

        if ( $args['title_left'] || $args['title_right'] ) {
            $r .= '<thead>';
        }

        if ( $args['title_left'] || $args['title_right'] ) {
            $th_or_td_left  = empty( $args['title_left'] ) ? 'td' : 'th';
            $th_or_td_right = empty( $args['title_right'] ) ? 'td' : 'th';

            $r .= "<tr class='diff-sub-title'>\n";
            $r .= "\t<$th_or_td_left>$args[title_left]</$th_or_td_left>\n";
            if ( $is_split_view ) {
                $r .= "\t<$th_or_td_right>$args[title_right]</$th_or_td_right>\n";
            }
            $r .= "</tr>\n";
        }

        if ( $args['title_left'] || $args['title_right'] ) {
            $r .= "</thead>\n";
        }

        $r .= "<tbody>\n$diff\n</tbody>\n";
        $r .= '</table>';

        return $r;
    }

}
