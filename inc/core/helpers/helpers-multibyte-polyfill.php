<?php
/**
 * Local mbstring polyfills for hosts where the extension is unavailable.
 *
 * These are intentionally minimal and only cover the subset used by the theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_mb_polyfill_chars' ) ) {
    /**
     * Split a string into characters, preferring UTF-8 aware parsing.
     *
     * @param mixed       $value    Source string.
     * @param string|null $encoding Requested encoding.
     * @return array<int,string>
     */
    function developer_starter_mb_polyfill_chars( $value, $encoding = 'UTF-8' ) {
        $string = (string) $value;
        if ( '' === $string ) {
            return array();
        }

        $encoding = is_string( $encoding ) && '' !== $encoding ? strtoupper( $encoding ) : 'UTF-8';
        if ( 'UTF-8' !== $encoding ) {
            $chars = preg_split( '//', $string, -1, PREG_SPLIT_NO_EMPTY );
            return is_array( $chars ) ? $chars : array();
        }

        $chars = preg_split( '//u', $string, -1, PREG_SPLIT_NO_EMPTY );
        if ( is_array( $chars ) ) {
            return $chars;
        }

        $fallback = preg_split( '//', $string, -1, PREG_SPLIT_NO_EMPTY );
        return is_array( $fallback ) ? $fallback : array();
    }
}

if ( ! function_exists( 'mb_strlen' ) ) {
    /**
     * Polyfill for mb_strlen().
     *
     * @param mixed       $string   Source string.
     * @param string|null $encoding Requested encoding.
     * @return int
     */
    function mb_strlen( $string, $encoding = null ) {
        return count( developer_starter_mb_polyfill_chars( $string, $encoding ?: 'UTF-8' ) );
    }
}

if ( ! function_exists( 'mb_substr' ) ) {
    /**
     * Polyfill for mb_substr().
     *
     * @param mixed       $string   Source string.
     * @param int         $start    Character offset.
     * @param int|null    $length   Character length.
     * @param string|null $encoding Requested encoding.
     * @return string
     */
    function mb_substr( $string, $start, $length = null, $encoding = null ) {
        $chars = developer_starter_mb_polyfill_chars( $string, $encoding ?: 'UTF-8' );
        $count = count( $chars );

        $start = (int) $start;
        if ( $start < 0 ) {
            $start = max( 0, $count + $start );
        }

        if ( null === $length ) {
            $slice = array_slice( $chars, $start );
        } else {
            $length = (int) $length;
            $slice  = array_slice( $chars, $start, $length );
        }

        return implode( '', $slice );
    }
}

if ( ! function_exists( 'mb_strtolower' ) ) {
    /**
     * Polyfill for mb_strtolower().
     *
     * @param mixed       $string   Source string.
     * @param string|null $encoding Requested encoding.
     * @return string
     */
    function mb_strtolower( $string, $encoding = null ) {
        unset( $encoding );
        return strtolower( (string) $string );
    }
}
