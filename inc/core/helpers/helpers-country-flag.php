<?php
/**
 * Country flag helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 将国家代码转换为国旗 Emoji
 *
 * @param string $country_code 国家/地区代码或已配置图标。
 * @return string
 */
function developer_starter_country_to_flag( $country_code ) {
    $country_code = strtoupper( trim( $country_code ) );

    // 如果已经是 emoji（以字节判断）或包含 http，直接返回。
    if ( strlen( $country_code ) > 10 || strpos( $country_code, 'HTTP' ) === 0 ) {
        return $country_code;
    }

    if ( strlen( $country_code ) !== 2 ) {
        return $country_code;
    }

    // A = 0x1F1E6, B = 0x1F1E7, ...
    $first  = 0x1F1E6 + ord( $country_code[0] ) - ord( 'A' );
    $second = 0x1F1E6 + ord( $country_code[1] ) - ord( 'A' );

    return html_entity_decode( '&#' . $first . ';&#' . $second . ';', ENT_NOQUOTES, 'UTF-8' );
}
