<?php
/**
 * 针对未安装 mbstring 扩展主机的轻量兼容补丁。
 *
 * 补丁实现保持轻量，仅覆盖主题所需的最小函数子集。
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_mb_polyfill_chars' ) ) {
    /**
     * 将字符串拆分为单字符数组（优先使用 UTF-8 安全模式）。
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
     * mb_strlen() 兼容垫片实现。
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
     * mb_substr() 兼容垫片实现。
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
     * mb_strtolower() 兼容垫片实现。
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
