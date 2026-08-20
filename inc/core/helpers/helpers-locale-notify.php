<?php
/**
 * Helpers grouped split from class-helpers.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_locale_text' ) ) {

    /**
     * 根据当前 locale 返回中英文案。
     *
     * @param string $zh 中文文本。
     * @param string $en 英文文本。
     * @return string
     */
    function developer_starter_get_locale_text( $zh, $en ) {
        return developer_starter_is_chinese_locale() ? (string) $zh : (string) $en;
    }
}

if ( ! function_exists( 'developer_starter_translate_theme_option_text' ) ) {
    /**
     * 尝试将主题设置中的文本按当前语言包做回退翻译。
     *
     * 说明：
     * - 主要用于历史站点里已保存中文默认文案的场景；
     * - 若语言包中没有对应词条，则保持原值不变。
     *
     * @param string $text 原始文本。
     * @return string
     */
    function developer_starter_translate_theme_option_text( $text ) {
        $text = (string) $text;
        if ( '' === trim( $text ) ) {
            return $text;
        }

        $translated = translate( $text, 'developer-starter' );
        if ( is_string( $translated ) && '' !== $translated ) {
            $text = $translated;
        }

        // 对包含片段的组合文案（如版权信息）做短语级替换。
        $phrase_map = array(
            '关于我们' => __( '关于我们', 'developer-starter' ),
            '快速链接' => __( '快速链接', 'developer-starter' ),
            '联系方式' => __( '联系方式', 'developer-starter' ),
            '关注我们' => __( '关注我们', 'developer-starter' ),
            '扫码关注公众号' => __( '扫码关注公众号', 'developer-starter' ),
            '扫码关注抖音' => __( '扫码关注抖音', 'developer-starter' ),
            '友情链接：' => __( '友情链接：', 'developer-starter' ),
            '版权所有' => __( '版权所有', 'developer-starter' ),
            '专业的企业服务提供商，致力于为客户提供优质的产品与服务。' => __( '专业的企业服务提供商，致力于为客户提供优质的产品与服务。', 'developer-starter' ),
        );

        return strtr( $text, $phrase_map );
    }
}

if ( ! function_exists( 'developer_starter_format_date_value' ) ) {
    /**
     * 按站点日期/时间格式输出日期。
     *
     * @param mixed $value        时间戳或可被 strtotime 解析的日期字符串。
     * @param bool  $include_time 是否包含时间。
     * @param bool  $is_gmt       字符串是否为 GMT 时间。
     * @return string
     */
    function developer_starter_format_date_value( $value, $include_time = false, $is_gmt = false ) {
        $format = developer_starter_get_date_time_format( $include_time );

        if ( is_numeric( $value ) ) {
            return wp_date( $format, (int) $value );
        }

        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( $is_gmt ) {
            return (string) get_date_from_gmt( $value, $format );
        }

        $timestamp = strtotime( $value );
        if ( false === $timestamp ) {
            return $value;
        }

        return wp_date( $format, $timestamp );
    }
}

if ( ! function_exists( 'developer_starter_get_month_label' ) ) {
    /**
     * 获取当前 locale 下更自然的月份标签。
     *
     * @param int|null $timestamp Unix 时间戳，默认为当前时间。
     * @return string
     */
    function developer_starter_get_month_label( $timestamp = null ) {
        $locale    = developer_starter_get_current_locale();
        $timestamp = null === $timestamp ? current_time( 'timestamp' ) : (int) $timestamp;

        if ( 0 === strpos( $locale, 'zh_' ) ) {
            return wp_date( 'Y年n月', $timestamp );
        }

        return wp_date( 'F Y', $timestamp );
    }
}

if ( ! function_exists( 'developer_starter_format_currency_amount' ) ) {
    /**
     * 以 locale 友好的方式输出金额。
     *
     * @param mixed  $amount   金额。
     * @param string $currency 货币代码。
     * @param int    $decimals 小数位数。
     * @return string
     */
    function developer_starter_format_currency_amount( $amount, $currency = 'CNY', $decimals = 2 ) {
        $currency = strtoupper( sanitize_text_field( (string) $currency ) );
        if ( '' === $currency ) {
            $currency = 'CNY';
        }

        if ( ! is_numeric( $amount ) ) {
            $raw_amount = trim( (string) $amount );
            return '' === $raw_amount ? '' : trim( $raw_amount . ' ' . $currency );
        }

        $locale    = developer_starter_get_current_locale();
        $formatted = number_format_i18n( (float) $amount, (int) $decimals );
        $symbols   = apply_filters(
            'developer_starter_currency_symbols',
            array(
                'USD' => '$',
                'EUR' => 'EUR ',
                'GBP' => 'GBP ',
                'JPY' => 0 === strpos( $locale, 'ja_' ) ? '¥' : 'JPY ',
                'CNY' => 0 === strpos( $locale, 'zh_' ) ? '¥' : 'CNY ',
            )
        );

        if ( isset( $symbols[ $currency ] ) ) {
            $prefix = (string) $symbols[ $currency ];
            if ( '' !== $prefix && substr( $prefix, -1 ) === ' ' ) {
                return $prefix . $formatted;
            }

            return $prefix . $formatted;
        }

        return $formatted . ' ' . $currency;
    }
}

if ( ! function_exists( 'developer_starter_get_demo_currency_code' ) ) {
    /**
     * 获取 demo 默认货币代码。
     *
     * @return string
     */
    function developer_starter_get_demo_currency_code() {
        return developer_starter_is_chinese_locale() ? 'CNY' : 'USD';
    }
}

if ( ! function_exists( 'developer_starter_get_demo_price_text' ) ) {
    /**
     * 获取 locale-aware 的 demo 价格文本。
     *
     * @param mixed       $amount 金额。
     * @param string      $suffix 后缀。
     * @param string|null $currency 货币代码，空则自动判断。
     * @return string
     */
    function developer_starter_get_demo_price_text( $amount, $suffix = '', $currency = null ) {
        $currency = $currency ? strtoupper( (string) $currency ) : developer_starter_get_demo_currency_code();
        $decimals = is_numeric( $amount ) && (float) $amount === floor( (float) $amount ) ? 0 : 2;
        $text     = developer_starter_format_currency_amount( $amount, $currency, $decimals );

        return $text . (string) $suffix;
    }
}

if ( ! function_exists( 'developer_starter_get_demo_price_hint' ) ) {
    /**
     * 获取 locale-aware 的 demo 价格提示。
     *
     * @param mixed       $amount 金额。
     * @param string      $suffix 后缀。
     * @param string|null $currency 货币代码，空则自动判断。
     * @return string
     */
    function developer_starter_get_demo_price_hint( $amount, $suffix = '', $currency = null ) {
        $price = developer_starter_get_demo_price_text( $amount, $suffix, $currency );

        return developer_starter_is_chinese_locale() ? '如：' . $price : 'e.g. ' . $price;
    }
}

if ( ! function_exists( 'developer_starter_notify_method_has_email' ) ) {
    function developer_starter_notify_method_has_email( $mode ) {
        return in_array( (string) $mode, array( 'email', 'both' ), true );
    }
}

if ( ! function_exists( 'developer_starter_notify_method_has_push' ) ) {
    function developer_starter_notify_method_has_push( $mode ) {
        return in_array( (string) $mode, array( 'push', 'both' ), true );
    }
}

if ( ! function_exists( 'developer_starter_get_email_design_css_value' ) ) {
    /**
     * Resolve a design-token CSS variable to an email-safe static CSS value.
     *
     * Email clients rarely support CSS custom properties, so notification templates
     * should resolve tokens before writing inline styles.
     *
     * @param string $css_var  CSS variable name.
     * @param string $fallback Static fallback value.
     * @return string
     */
    function developer_starter_get_email_design_css_value( $css_var, $fallback ) {
        $css_var = is_string( $css_var ) ? trim( $css_var ) : '';
        $value   = '';

        if ( $css_var !== '' && class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
            $variables = \Developer_Starter\Core\Design_Tokens::get_css_variables();
            if ( is_array( $variables ) && isset( $variables[ $css_var ] ) ) {
                $value = (string) $variables[ $css_var ];

                if ( preg_match( '/^var\((--[a-z0-9\-_]+)\)$/i', $value, $matches ) && isset( $variables[ $matches[1] ] ) ) {
                    $value = (string) $variables[ $matches[1] ];
                }

                if ( preg_match( '/^rgb\(var\((--[a-z0-9\-_]+)\)\)$/i', $value, $matches ) && isset( $variables[ $matches[1] ] ) ) {
                    $value = 'rgb' . '(' . (string) $variables[ $matches[1] ] . ')';
                } elseif ( preg_match( '/^rgba\(var\((--[a-z0-9\-_]+)\),\s*([0-9.]+)\)$/i', $value, $matches ) && isset( $variables[ $matches[1] ] ) ) {
                    $value = 'rgba' . '(' . (string) $variables[ $matches[1] ] . ', ' . (string) $matches[2] . ')';
                }
            }
        }

        if ( '' === trim( $value ) ) {
            $value = (string) $fallback;
        }

        $value = trim( wp_strip_all_tags( $value ) );
        if ( '' === $value || preg_match( '/[;{}<>]/', $value ) ) {
            return trim( (string) $fallback );
        }

        if ( function_exists( 'sanitize_hex_color' ) ) {
            $hex = sanitize_hex_color( $value );
            if ( '' !== $hex && null !== $hex ) {
                return $hex;
            }
        }

        if ( preg_match( '/^(?:rgb|rgba|hsl|hsla)\([0-9\.\,\s%]+\)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^-?(?:\d+|\d*\.\d+)(?:px|rem|em|%)$/i', $value ) ) {
            return $value;
        }

        if ( preg_match( '/^[a-z]+$/i', $value ) ) {
            return strtolower( $value );
        }

        return trim( (string) $fallback );
    }
}

/**
 * 构建通用 HTML 邮件模板
 *
 * @param array $args 模板参数。
 * @return string
 */
if ( ! function_exists( 'developer_starter_build_html_email_template' ) ) {
    function developer_starter_build_html_email_template( $args = array() ) {
        $defaults = array(
            'title'       => '',
            'intro'       => '',
            'lines'       => array(),
            'button_text' => '',
            'button_url'  => '',
            'notice'      => '',
            'footer_text' => '',
        );
        $args = wp_parse_args( $args, $defaults );

        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        $title     = trim( wp_strip_all_tags( (string) $args['title'] ) );
        if ( $title === '' ) {
            $title = $site_name !== '' ? $site_name : __( '站点通知', 'developer-starter' );
        }

        $email_color_text         = developer_starter_get_email_design_css_value( '--color-text', 'darkslategray' );
        $email_color_heading      = developer_starter_get_email_design_css_value( '--color-heading', 'black' );
        $email_color_muted        = developer_starter_get_email_design_css_value( '--color-text-muted', 'slategray' );
        $email_color_primary      = developer_starter_get_email_design_css_value( '--color-primary', 'royalblue' );
        $email_color_primary_dark = developer_starter_get_email_design_css_value( '--color-primary-dark', 'mediumblue' );
        $email_color_surface      = developer_starter_get_email_design_css_value( '--color-neutral-0', 'white' );
        $email_color_page_bg      = developer_starter_get_email_design_css_value( '--qiling-color-f3f4f6', 'whitesmoke' );
        $email_color_footer_bg    = developer_starter_get_email_design_css_value( '--color-neutral-50', 'ghostwhite' );
        $email_color_border       = developer_starter_get_email_design_css_value( '--color-border', 'lightgray' );
        $email_color_header_end   = developer_starter_get_email_design_css_value( '--color-neutral-900', 'midnightblue' );
        $email_space_6            = developer_starter_get_email_design_css_value( '--qiling-space-6', '6px' );
        $email_space_8            = developer_starter_get_email_design_css_value( '--qiling-space-8', '8px' );
        $email_space_10           = developer_starter_get_email_design_css_value( '--qiling-space-10', '10px' );
        $email_space_12           = developer_starter_get_email_design_css_value( '--qiling-space-12', '12px' );
        $email_space_16           = developer_starter_get_email_design_css_value( '--qiling-space-16', '16px' );
        $email_space_18           = developer_starter_get_email_design_css_value( '--qiling-space-18', '18px' );
        $email_space_24           = developer_starter_get_email_design_css_value( '--qiling-space-24', '24px' );
        $email_space_28           = developer_starter_get_email_design_css_value( '--qiling-space-28', '28px' );
        $email_radius_10          = developer_starter_get_email_design_css_value( '--qiling-space-10', '10px' );
        $email_radius_16          = developer_starter_get_email_design_css_value( '--qiling-space-16', '16px' );
        $email_width_label        = developer_starter_get_email_design_css_value( '--qiling-measure-132', '132px' );
        $email_width_container    = developer_starter_get_email_design_css_value( '--qiling-measure-640', '640px' );
        $email_text_12            = developer_starter_get_email_design_css_value( '--qiling-text-rem-0p75', '12px' );
        $email_text_13            = developer_starter_get_email_design_css_value( '--qiling-email-text-13', '13px' );
        $email_text_14            = developer_starter_get_email_design_css_value( '--qiling-text-rem-0p875', '14px' );
        $email_text_15            = developer_starter_get_email_design_css_value( '--qiling-email-text-15', '15px' );
        $email_text_24            = developer_starter_get_email_design_css_value( '--qiling-text-rem-1p5', '24px' );

        $intro = trim( (string) $args['intro'] );
        $intro_html = '';
        if ( $intro !== '' ) {
            $intro_html = '<p style="margin:0 0 ' . esc_attr( $email_space_16 ) . '; color:' . esc_attr( $email_color_text ) . '; font-size:' . esc_attr( $email_text_15 ) . '; line-height:1.8;">' . nl2br( esc_html( $intro ) ) . '</p>';
        }

        $detail_rows = '';
        if ( is_array( $args['lines'] ) && ! empty( $args['lines'] ) ) {
            foreach ( $args['lines'] as $label => $value ) {
                if ( is_array( $value ) ) {
                    $value = implode( ', ', array_map( 'strval', $value ) );
                }

                $clean_value = trim( wp_strip_all_tags( (string) $value ) );
                if ( $clean_value === '' ) {
                    continue;
                }

                if ( wp_http_validate_url( $clean_value ) ) {
                    $value_html = '<a href="' . esc_url( $clean_value ) . '" style="color:' . esc_attr( $email_color_primary ) . '; text-decoration:none; word-break:break-all;">' . esc_html( $clean_value ) . '</a>';
                } else {
                    $value_html = nl2br( esc_html( $clean_value ) );
                }

                $clean_label = '';
                if ( is_string( $label ) && $label !== '' && ! is_numeric( $label ) ) {
                    $clean_label = trim( wp_strip_all_tags( $label ) );
                }

                if ( $clean_label !== '' ) {
                    $detail_rows .= '<tr>';
                    $detail_rows .= '<td style="padding:' . esc_attr( $email_space_10 ) . ' 0; border-bottom:1px solid ' . esc_attr( $email_color_border ) . '; width:' . esc_attr( $email_width_label ) . '; color:' . esc_attr( $email_color_muted ) . '; font-size:' . esc_attr( $email_text_13 ) . '; vertical-align:top;">' . esc_html( $clean_label ) . '</td>';
                    $detail_rows .= '<td style="padding:' . esc_attr( $email_space_10 ) . ' 0; border-bottom:1px solid ' . esc_attr( $email_color_border ) . '; color:' . esc_attr( $email_color_heading ) . '; font-size:' . esc_attr( $email_text_14 ) . '; vertical-align:top;">' . $value_html . '</td>';
                    $detail_rows .= '</tr>';
                } else {
                    $detail_rows .= '<tr>';
                    $detail_rows .= '<td colspan="2" style="padding:' . esc_attr( $email_space_10 ) . ' 0; border-bottom:1px solid ' . esc_attr( $email_color_border ) . '; color:' . esc_attr( $email_color_heading ) . '; font-size:' . esc_attr( $email_text_14 ) . '; vertical-align:top;">' . $value_html . '</td>';
                    $detail_rows .= '</tr>';
                }
            }
        }

        $details_html = '';
        if ( $detail_rows !== '' ) {
            $details_html  = '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; margin:' . esc_attr( $email_space_8 ) . ' 0 ' . esc_attr( $email_space_18 ) . ';">';
            $details_html .= $detail_rows;
            $details_html .= '</table>';
        }

        $button_text = trim( wp_strip_all_tags( (string) $args['button_text'] ) );
        $button_url  = trim( (string) $args['button_url'] );
        $button_html = '';
        if ( $button_url !== '' && wp_http_validate_url( $button_url ) ) {
            if ( $button_text === '' ) {
                $button_text = __( '查看详情', 'developer-starter' );
            }
            $button_html  = '<p style="margin:0 0 ' . esc_attr( $email_space_16 ) . '; text-align:center;">';
            $button_html .= '<a href="' . esc_url( $button_url ) . '" style="display:inline-block; background:' . esc_attr( $email_color_primary ) . '; color:' . esc_attr( $email_color_surface ) . '; text-decoration:none; font-weight:600; font-size:' . esc_attr( $email_text_14 ) . '; padding:' . esc_attr( $email_space_12 ) . ' ' . esc_attr( $email_space_24 ) . '; border-radius:' . esc_attr( $email_radius_10 ) . ';">' . esc_html( $button_text ) . '</a>';
            $button_html .= '</p>';
        }

        $notice = trim( (string) $args['notice'] );
        $notice_html = '';
        if ( $notice !== '' ) {
            $notice_html = '<p style="margin:0; color:' . esc_attr( $email_color_muted ) . '; font-size:' . esc_attr( $email_text_12 ) . '; line-height:1.7;">' . nl2br( esc_html( $notice ) ) . '</p>';
        }

        $footer_text = trim( (string) $args['footer_text'] );
        if ( $footer_text === '' ) {
            $footer_text = sprintf( __( '此邮件由 %s 自动发送', 'developer-starter' ), $site_name );
        }

        $send_time = sprintf( __( '发送时间：%s', 'developer-starter' ), current_time( 'Y-m-d H:i:s' ) );

        $html  = '<!DOCTYPE html><html><head><meta charset="' . esc_attr( get_bloginfo( 'charset' ) ) . '"></head>';
        $html .= '<body style="margin:0; padding:0; background:' . esc_attr( $email_color_page_bg ) . '; font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;">';
        $html .= '<table role="presentation" cellpadding="0" cellspacing="0" style="width:100%; border-collapse:collapse; background:' . esc_attr( $email_color_page_bg ) . ';">';
        $html .= '<tr><td align="center" style="padding:' . esc_attr( $email_space_24 ) . ' ' . esc_attr( $email_space_12 ) . ';">';
        $html .= '<table role="presentation" cellpadding="0" cellspacing="0" style="max-width:' . esc_attr( $email_width_container ) . '; width:100%; border-collapse:separate; border-spacing:0; background:' . esc_attr( $email_color_surface ) . '; border:1px solid ' . esc_attr( $email_color_border ) . '; border-radius:' . esc_attr( $email_radius_16 ) . '; overflow:hidden;">';
        $html .= '<tr><td style="padding:' . esc_attr( $email_space_24 ) . ' ' . esc_attr( $email_space_28 ) . '; background:linear-gradient(135deg,' . esc_attr( $email_color_primary ) . ' 0%,' . esc_attr( $email_color_primary_dark ) . ' 55%,' . esc_attr( $email_color_header_end ) . ' 100%); color:' . esc_attr( $email_color_surface ) . ';">';
        $html .= '<div style="font-size:' . esc_attr( $email_text_13 ) . '; opacity:0.9; line-height:1.6;">' . esc_html( $site_name ) . '</div>';
        $html .= '<div style="font-size:' . esc_attr( $email_text_24 ) . '; font-weight:700; line-height:1.4; margin-top:' . esc_attr( $email_space_6 ) . ';">' . esc_html( $title ) . '</div>';
        $html .= '</td></tr>';
        $html .= '<tr><td style="padding:' . esc_attr( $email_space_24 ) . ' ' . esc_attr( $email_space_28 ) . ';">';
        $html .= $intro_html . $details_html . $button_html . $notice_html;
        $html .= '</td></tr>';
        $html .= '<tr><td style="padding:' . esc_attr( $email_space_16 ) . ' ' . esc_attr( $email_space_28 ) . '; background:' . esc_attr( $email_color_footer_bg ) . '; border-top:1px solid ' . esc_attr( $email_color_border ) . '; color:' . esc_attr( $email_color_muted ) . '; font-size:' . esc_attr( $email_text_12 ) . '; line-height:1.7;">';
        $html .= esc_html( $footer_text ) . '<br>' . esc_html( $send_time );
        $html .= '</td></tr>';
        $html .= '</table>';
        $html .= '</td></tr></table>';
        $html .= '</body></html>';

        return $html;
    }
}

if ( ! function_exists( 'developer_starter_get_push_channels' ) ) {
    function developer_starter_get_push_channels( $scene ) {
        $scene = sanitize_key( $scene );
        $raw_channels = developer_starter_get_option( 'notify_' . $scene . '_push_channel', array() );

        if ( is_array( $raw_channels ) ) {
            // no-op
        } elseif ( $raw_channels === '' ) {
            $raw_channels = array();
        } else {
            $raw_channels = array( $raw_channels );
        }

        // 兼容过渡字段：notify_{scene}_push_channels
        if ( empty( $raw_channels ) ) {
            $legacy_channels = developer_starter_get_option( 'notify_' . $scene . '_push_channels', array() );
            if ( is_array( $legacy_channels ) ) {
                $raw_channels = $legacy_channels;
            } elseif ( $legacy_channels !== '' ) {
                $raw_channels = array( $legacy_channels );
            }
        }

        $channels = array();
        foreach ( $raw_channels as $channel_id ) {
            $clean_id = preg_replace( '/[^a-zA-Z0-9_-]/', '', (string) $channel_id );
            if ( $clean_id !== '' ) {
                $channels[] = $clean_id;
            }
        }

        return array_values( array_unique( $channels ) );
    }
}

if ( ! function_exists( 'developer_starter_get_push_channel' ) ) {
    function developer_starter_get_push_channel( $scene ) {
        $channels = developer_starter_get_push_channels( $scene );
        return isset( $channels[0] ) ? $channels[0] : '';
    }
}

/**
 * 判断站内通知是否启用
 *
 * @param string $scene   通知场景（可选）。
 * @param bool   $default 默认启用状态。
 * @return bool
 */
if ( ! function_exists( 'developer_starter_site_notify_enabled' ) ) {
    function developer_starter_site_notify_enabled( $scene = '', $default = true ) {
        $global_default = $default ? '1' : '';
        $global_enabled = developer_starter_get_option( 'site_notify_enable', $global_default );
        if ( (string) $global_enabled !== '1' ) {
            return false;
        }

        $scene = sanitize_key( (string) $scene );
        if ( $scene === '' ) {
            return true;
        }

        // 启灵积分商城场景统一使用主题侧总开关，具体场景由插件自身配置控制。
        if ( strpos( $scene, 'qilingshop_' ) === 0 || strpos( $scene, 'qls_' ) === 0 ) {
            $qilingshop_default = $default ? '1' : '';
            $qilingshop_enabled = developer_starter_get_option( 'site_notify_qilingshop', $qilingshop_default );
            return (string) $qilingshop_enabled === '1';
        }

        $scene_default = $default ? '1' : '';
        $scene_enabled = developer_starter_get_option( 'site_notify_' . $scene, $scene_default );
        return (string) $scene_enabled === '1';
    }
}

/**
 * 发送主题业务推送（通过启灵推送插件）
 *
 * @param string $scene   业务场景：message/form/careers。
 * @param string $title   标题。
 * @param array  $lines   内容行（支持键值对和纯文本行）。
 * @param array  $context 扩展参数。
 * @return array|WP_Error
 */
if ( ! function_exists( 'developer_starter_send_push_message' ) ) {
    function developer_starter_send_push_message( $scene, $title, $lines = array(), $context = array() ) {
        if ( ! function_exists( 'qilinghook_send' ) ) {
            return new \WP_Error( 'developer_starter_push_plugin_missing', __( '未检测到启灵推送插件。', 'developer-starter' ) );
        }

        $channel_ids = developer_starter_get_push_channels( $scene );
        if ( empty( $channel_ids ) ) {
            return new \WP_Error( 'developer_starter_push_channel_missing', __( '未配置推送通道。', 'developer-starter' ) );
        }

        $site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
        $message   = '[' . $site_name . '] ' . wp_strip_all_tags( (string) $title );

        if ( is_array( $lines ) && ! empty( $lines ) ) {
            $formatted_lines = array();
            foreach ( $lines as $label => $value ) {
                if ( is_array( $value ) ) {
                    $value = implode( ', ', array_map( 'strval', $value ) );
                }
                $value = trim( wp_strip_all_tags( (string) $value ) );
                if ( $value === '' ) {
                    continue;
                }

                if ( is_string( $label ) && $label !== '' && ! is_numeric( $label ) ) {
                    $formatted_lines[] = wp_strip_all_tags( $label ) . ': ' . $value;
                } else {
                    $formatted_lines[] = $value;
                }
            }
            if ( ! empty( $formatted_lines ) ) {
                $message .= "\n\n" . implode( "\n", $formatted_lines );
            }
        }

        $args = array(
            'type'   => 'text',
            'source' => 'qiling_theme_' . sanitize_key( $scene ),
        );

        if ( is_array( $context ) && isset( $context['args'] ) && is_array( $context['args'] ) ) {
            $args = array_merge( $args, $context['args'] );
        }

        $results = array();
        $success_count = 0;
        $fail_count = 0;

        foreach ( $channel_ids as $channel_id ) {
            $result = qilinghook_send( $channel_id, $message, $args );
            if ( is_wp_error( $result ) ) {
                $fail_count++;
                $results[] = array(
                    'channel_id' => $channel_id,
                    'success'    => false,
                    'error_code' => $result->get_error_code(),
                    'error'      => $result->get_error_message(),
                );
            } else {
                $success_count++;
                $results[] = array(
                    'channel_id' => $channel_id,
                    'success'    => true,
                );
            }
        }

        if ( $success_count === 0 ) {
            return new \WP_Error(
                'developer_starter_push_all_failed',
                __( '所有推送通道发送失败。', 'developer-starter' ),
                array(
                    'total'        => count( $channel_ids ),
                    'success_count'=> 0,
                    'fail_count'   => $fail_count,
                    'results'      => $results,
                )
            );
        }

        return array(
            'total'         => count( $channel_ids ),
            'success_count' => $success_count,
            'fail_count'    => $fail_count,
            'results'       => $results,
        );
    }
}
