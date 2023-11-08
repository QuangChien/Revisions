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

class TextDiffRendererTable extends TextDiffRenderer {

    public $_leading_context_lines = 10000;

    public $_trailing_context_lines = 10000;

    protected $_diff_threshold = 0.6;

    protected $_show_split_view = true;

    protected $compat_fields = array( '_show_split_view', 'inline_diff_renderer', '_diff_threshold' );

    protected $count_cache = array();

    protected $difference_cache = array();

    /**
     * @param $params
     */
    public function __construct( $params = array() ) {
        parent::__construct( $params );
        if ( isset( $params['show_split_view'] ) ) {
            $this->_show_split_view = $params['show_split_view'];
        }
    }

    /**
     * @param $header
     * @return string
     */
    public function _startBlock( $header ) {
        return '';
    }

    /**
     * @param $lines
     * @param $prefix
     * @return void
     */
    public function _lines( $lines, $prefix = ' ' ) {
    }

    /**
     * @param $line
     * @return string
     */
    public function addedLine( $line ) {
        return "<td class='diff-addedline'><span aria-hidden='true' class='dashicons dashicons-plus'></span><span class='screen-reader-text'>" . __( 'Added:' ) . " </span>{$line}</td>";

    }

    /**
     * @param $line
     * @return string
     */
    public function deletedLine( $line ) {
        return "<td class='diff-deletedline'><span aria-hidden='true' class='dashicons dashicons-minus'></span><span class='screen-reader-text'>" . __( 'Deleted:' ) . " </span>{$line}</td>";
    }

    /**
     * @param $line
     * @return string
     */
    public function contextLine( $line ) {
        return "<td class='diff-context'><span class='screen-reader-text'>" . __( 'Unchanged:' ) . " </span>{$line}</td>";
    }

    /**
     * @return string
     */
    public function emptyLine() {
        return '<td>&nbsp;</td>';
    }

    /**
     * @param $lines
     * @param $encode
     * @return string
     */
    public function _added( $lines, $encode = true ) {
        $r = '';
        foreach ( $lines as $line ) {
            if ( $encode ) {
                $processed_line = htmlspecialchars( $line );
            }

            if ( $this->_show_split_view ) {
                $r .= '<tr>' . $this->emptyLine() . $this->addedLine( $line ) . "</tr>\n";
            } else {
                $r .= '<tr>' . $this->addedLine( $line ) . "</tr>\n";
            }
        }
        return $r;
    }

    /**
     * @param $lines
     * @param $encode
     * @return string
     */
    public function _deleted( $lines, $encode = true ) {
        $r = '';
        foreach ( $lines as $line ) {
            if ( $encode ) {
                $processed_line = htmlspecialchars( $line );
            }
            if ( $this->_show_split_view ) {
                $r .= '<tr>' . $this->deletedLine( $line ) . $this->emptyLine() . "</tr>\n";
            } else {
                $r .= '<tr>' . $this->deletedLine( $line ) . "</tr>\n";
            }
        }
        return $r;
    }

    /**
     * @param $lines
     * @param $encode
     * @return string
     */
    public function _context( $lines, $encode = true ) {
        $r = '';
        foreach ( $lines as $line ) {
            if ( $encode ) {
                $processed_line = htmlspecialchars( $line );

            }
            if ( $this->_show_split_view ) {
                $r .= '<tr>' . $this->contextLine( $line ) . $this->contextLine( $line ) . "</tr>\n";
            } else {
                $r .= '<tr>' . $this->contextLine( $line ) . "</tr>\n";
            }
        }
        return $r;
    }

    /**
     * @param $orig
     * @param $final
     * @return string
     */
    public function _changed( $orig, $final ) {
        $r = '';
        list($orig_matches, $final_matches, $orig_rows, $final_rows) = $this->interleave_changed_lines( $orig, $final );

        $orig_diffs  = array();
        $final_diffs = array();

        foreach ( $orig_matches as $o => $f ) {
            if ( is_numeric( $o ) && is_numeric( $f ) ) {
                $text_diff = new TextDiff( 'auto', array( array( $orig[ $o ] ), array( $final[ $f ] ) ) );
                $renderer  = new TextDiffRendererInline();
                $diff      = $renderer->render( $text_diff );

                if ( preg_match_all( '!(<ins>.*?</ins>|<del>.*?</del>)!', $diff, $diff_matches ) ) {
                    $stripped_matches = strlen( strip_tags( implode( ' ', $diff_matches[0] ) ) );
                    $stripped_diff = strlen( strip_tags( $diff ) ) * 2 - $stripped_matches;
                    $diff_ratio    = $stripped_matches / $stripped_diff;
                    if ( $diff_ratio > $this->_diff_threshold ) {
                        continue;
                    }
                }

                $orig_diffs[ $o ]  = preg_replace( '|<ins>.*?</ins>|', '', $diff );
                $final_diffs[ $f ] = preg_replace( '|<del>.*?</del>|', '', $diff );
            }
        }

        foreach ( array_keys( $orig_rows ) as $row ) {
            if ( $orig_rows[ $row ] < 0 && $final_rows[ $row ] < 0 ) {
                continue;
            }

            if ( isset( $orig_diffs[ $orig_rows[ $row ] ] ) ) {
                $orig_line = $orig_diffs[ $orig_rows[ $row ] ];
            } elseif ( isset( $orig[ $orig_rows[ $row ] ] ) ) {
                $orig_line = htmlspecialchars( $orig[ $orig_rows[ $row ] ] );
            } else {
                $orig_line = '';
            }

            if ( isset( $final_diffs[ $final_rows[ $row ] ] ) ) {
                $final_line = $final_diffs[ $final_rows[ $row ] ];
            } elseif ( isset( $final[ $final_rows[ $row ] ] ) ) {
                $final_line = htmlspecialchars( $final[ $final_rows[ $row ] ] );
            } else {
                $final_line = '';
            }

            if ( $orig_rows[ $row ] < 0 ) {
                $r .= $this->_added( array( $final_line ), false );
            } elseif ( $final_rows[ $row ] < 0 ) {
                $r .= $this->_deleted( array( $orig_line ), false );
            } else {
                if ( $this->_show_split_view ) {
                    $r .= '<tr>' . $this->deletedLine( $orig_line ) . $this->addedLine( $final_line ) . "</tr>\n";
                } else {
                    $r .= '<tr>' . $this->deletedLine( $orig_line ) . '</tr><tr>' . $this->addedLine( $final_line ) . "</tr>\n";
                }
            }
        }

        return $r;
    }

    /**
     * @param $orig
     * @param $final
     * @return array
     */
    public function interleave_changed_lines( $orig, $final ) {

        $matches = array();
        foreach ( array_keys( $orig ) as $o ) {
            foreach ( array_keys( $final ) as $f ) {
                $matches[ "$o,$f" ] = $this->compute_string_distance( $orig[ $o ], $final[ $f ] );
            }
        }
        asort( $matches );

        $orig_matches  = array();
        $final_matches = array();

        foreach ( $matches as $keys => $difference ) {
            list($o, $f) = explode( ',', $keys );
            $o           = (int) $o;
            $f           = (int) $f;

            if ( isset( $orig_matches[ $o ] ) && isset( $final_matches[ $f ] ) ) {
                continue;
            }

            if ( ! isset( $orig_matches[ $o ] ) && ! isset( $final_matches[ $f ] ) ) {
                $orig_matches[ $o ]  = $f;
                $final_matches[ $f ] = $o;
                continue;
            }

            if ( isset( $orig_matches[ $o ] ) ) {
                $final_matches[ $f ] = 'x';
            } elseif ( isset( $final_matches[ $f ] ) ) {
                $orig_matches[ $o ] = 'x';
            }
        }

        ksort( $orig_matches );
        ksort( $final_matches );

        $orig_rows      = array_keys( $orig_matches );
        $orig_rows_copy = $orig_rows;
        $final_rows     = array_keys( $final_matches );

        foreach ( $orig_rows_copy as $orig_row ) {
            $final_pos = array_search( $orig_matches[ $orig_row ], $final_rows, true );
            $orig_pos  = (int) array_search( $orig_row, $orig_rows, true );

            if ( false === $final_pos ) {
                array_splice( $final_rows, $orig_pos, 0, -1 );
            } elseif ( $final_pos < $orig_pos ) {
                $diff_array = range( -1, $final_pos - $orig_pos );
                array_splice( $final_rows, $orig_pos, 0, $diff_array );
            } elseif ( $final_pos > $orig_pos ) {
                $diff_array = range( -1, $orig_pos - $final_pos );
                array_splice( $orig_rows, $orig_pos, 0, $diff_array );
            }
        }

        $diff_count = count( $orig_rows ) - count( $final_rows );
        if ( $diff_count < 0 ) {
            while ( $diff_count < 0 ) {
                array_push( $orig_rows, $diff_count++ );
            }
        } elseif ( $diff_count > 0 ) {
            $diff_count = -1 * $diff_count;
            while ( $diff_count < 0 ) {
                array_push( $final_rows, $diff_count++ );
            }
        }

        return array( $orig_matches, $final_matches, $orig_rows, $final_rows );
    }

    /**
     * @param $string1
     * @param $string2
     * @return float|int|mixed
     */
    public function compute_string_distance( $string1, $string2 ) {
        $count_key1 = md5( $string1 );
        $count_key2 = md5( $string2 );

        if ( ! isset( $this->count_cache[ $count_key1 ] ) ) {
            $this->count_cache[ $count_key1 ] = count_chars( $string1 );
        }
        if ( ! isset( $this->count_cache[ $count_key2 ] ) ) {
            $this->count_cache[ $count_key2 ] = count_chars( $string2 );
        }

        $chars1 = $this->count_cache[ $count_key1 ];
        $chars2 = $this->count_cache[ $count_key2 ];

        $difference_key = md5( implode( ',', $chars1 ) . ':' . implode( ',', $chars2 ) );
        if ( ! isset( $this->difference_cache[ $difference_key ] ) ) {
            $this->difference_cache[ $difference_key ] = array_sum( array_map( array( $this, 'difference' ), $chars1, $chars2 ) );
        }

        $difference = $this->difference_cache[ $difference_key ];

        if ( ! $string1 ) {
            return $difference;
        }

        return $difference / strlen( $string1 );
    }

    /**
     * @param $a
     * @param $b
     * @return float|int
     */
    public function difference( $a, $b ) {
        return abs( $a - $b );
    }

    /**
     * @param $name
     * @return void
     */
    public function __get( $name ) {
        if ( in_array( $name, $this->compat_fields, true ) ) {
            return $this->$name;
        }
    }

    /**
     * @param $name
     * @param $value
     * @return void
     */
    public function __set( $name, $value ) {
        if ( in_array( $name, $this->compat_fields, true ) ) {
            return $this->$name = $value;
        }
    }

    /**
     * @param $name
     * @return bool|void
     */
    public function __isset( $name ) {
        if ( in_array( $name, $this->compat_fields, true ) ) {
            return isset( $this->$name );
        }
    }

    /**
     * @param $name
     * @return void
     */
    public function __unset( $name ) {
        if ( in_array( $name, $this->compat_fields, true ) ) {
            unset( $this->$name );
        }
    }
}
