<?php
/**
 * Template Name: 个人中心
 * Template Post Type: page
 *
 * @package Developer_Starter
 */

// 未登录用户跳转到登录页或首页
if ( ! is_user_logged_in() ) {
    $login_page = developer_starter_get_option( 'login_page_id', '' );
    if ( $login_page ) {
        wp_redirect( get_permalink( $login_page ) );
    } else {
        wp_redirect( wp_login_url( get_permalink() ) );
    }
    exit;
}

// 加载个人中心专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-account',
        DEVELOPER_STARTER_ASSETS . '/css/account.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );

    $sanitize_css_color = static function( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return '';
        }
        $hex = sanitize_hex_color( $value );
        if ( is_string( $hex ) && '' !== $hex ) {
            return $hex;
        }
        if ( preg_match( '/^(rgba?|hsla?)\(\s*[-0-9.,%\s]+\)$/i', $value ) ) {
            return $value;
        }
        return '';
    };

    $sanitize_css_background = static function( $value ) use ( $sanitize_css_color ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return '';
        }
        $simple_color = $sanitize_css_color( $value );
        if ( '' !== $simple_color ) {
            return $simple_color;
        }
        if ( preg_match( '/^(linear|radial|conic)-gradient\(\s*[-#(),.%\sa-zA-Z0-9+\/]+\)$/i', $value ) ) {
            return $value;
        }
        return '';
    };

    $sanitize_css_length = static function( $value, $allow_negative = false ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return '';
        }
        if ( preg_match( '/^(?:0|-?\d+(?:\.\d+)?(?:px|r?em|%|vw|vh))$/i', $value ) ) {
            if ( ! $allow_negative && strpos( $value, '-' ) === 0 ) {
                return '';
            }
            return $value;
        }
        return '';
    };

    $sanitize_css_padding_pair = static function( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        if ( '' === $value ) {
            return '';
        }
        if ( preg_match( '/^(?:0|\d+(?:\.\d+)?(?:px|r?em|%))(?:\s+(?:0|\d+(?:\.\d+)?(?:px|r?em|%)))?$/i', $value ) ) {
            return $value;
        }
        return '';
    };

    $css_vars = array();

    $css_background_options = array(
        'account_header_bg_color'      => '--account-header-bg',
        'account_page_bg_color'        => '--account-page-bg',
        'account_public_id_bg'         => '--account-public-id-bg',
        'account_public_id_label_bg'   => '--account-public-id-label-bg',
        'account_home_button_bg'       => '--account-home-button-bg',
        'account_home_button_hover_bg' => '--account-home-button-hover-bg',
        'account_nav_bg'               => '--account-nav-bg',
        'account_nav_hover_bg'         => '--account-nav-hover-bg',
        'account_nav_active_bg'        => '--account-nav-active-bg',
        'account_nav_logout_hover_bg'  => '--account-nav-logout-hover-bg',
        'account_nav_badge_bg'         => '--account-nav-badge-bg',
        'account_button_bg'            => '--account-button-bg',
        'account_field_bg'             => '--account-field-bg',
    );
    foreach ( $css_background_options as $option_key => $css_key ) {
        $css_value = $sanitize_css_background( developer_starter_get_option( $option_key, '' ) );
        if ( '' !== $css_value ) {
            $css_vars[ $css_key ] = $css_value;
        }
    }

    $css_color_options = array(
        'account_header_name_color'          => '--account-header-name-color',
        'account_header_text_color'          => '--account-header-text-color',
        'account_header_muted_text_color'    => '--account-header-muted-color',
        'account_avatar_border_color'        => '--account-avatar-border-color',
        'account_public_id_text_color'       => '--account-public-id-text',
        'account_public_id_border_color'     => '--account-public-id-border',
        'account_public_id_label_text_color' => '--account-public-id-label-text',
        'account_home_button_text_color'     => '--account-home-button-text',
        'account_card_bg_color'              => '--account-card-bg',
        'account_card_text_color'            => '--account-card-text',
        'account_muted_text_color'           => '--account-muted-text',
        'account_border_color'               => '--account-border-color',
        'account_nav_text_color'             => '--account-nav-text',
        'account_nav_hover_text_color'       => '--account-nav-hover-text',
        'account_nav_active_text_color'      => '--account-nav-active-text',
        'account_nav_logout_text_color'      => '--account-nav-logout-text',
        'account_nav_badge_text_color'       => '--account-nav-badge-text',
        'account_button_text_color'          => '--account-button-text',
        'account_field_text_color'           => '--account-field-text',
        'account_field_border_color'         => '--account-field-border',
        'account_field_focus_color'          => '--account-field-focus-color',
    );
    foreach ( $css_color_options as $option_key => $css_key ) {
        $css_value = $sanitize_css_color( developer_starter_get_option( $option_key, '' ) );
        if ( '' !== $css_value ) {
            $css_vars[ $css_key ] = $css_value;
        }
    }

    $sidebar_width = $sanitize_css_length( developer_starter_get_option( 'account_sidebar_width', '' ) );
    if ( '' !== $sidebar_width ) {
        $css_vars['--account-sidebar-width'] = $sidebar_width;
    }
    $layout_gap = $sanitize_css_length( developer_starter_get_option( 'account_content_gap', '' ) );
    if ( '' !== $layout_gap ) {
        $css_vars['--account-layout-gap'] = $layout_gap;
    }
    $card_radius = $sanitize_css_length( developer_starter_get_option( 'account_card_radius', '' ) );
    if ( '' !== $card_radius ) {
        $css_vars['--account-card-radius'] = $card_radius;
    }
    $section_padding = $sanitize_css_length( developer_starter_get_option( 'account_section_padding', '' ) );
    if ( '' !== $section_padding ) {
        $css_vars['--account-section-padding'] = $section_padding;
    }
    $nav_item_padding = $sanitize_css_padding_pair( developer_starter_get_option( 'account_nav_item_padding', '' ) );
    if ( '' !== $nav_item_padding ) {
        $css_vars['--account-nav-item-padding'] = $nav_item_padding;
    }
    $button_radius = $sanitize_css_length( developer_starter_get_option( 'account_button_radius', '' ) );
    if ( '' !== $button_radius ) {
        $css_vars['--account-button-radius'] = $button_radius;
    }
    $avatar_size = $sanitize_css_length( developer_starter_get_option( 'account_avatar_size', '' ) );
    if ( '' !== $avatar_size ) {
        $css_vars['--account-avatar-size'] = $avatar_size;
    }
    $avatar_border = $sanitize_css_length( developer_starter_get_option( 'account_avatar_border_width', '' ) );
    if ( '' !== $avatar_border ) {
        $css_vars['--account-avatar-border-width'] = $avatar_border;
    }

    $shadow_preset = sanitize_key( (string) developer_starter_get_option( 'account_card_shadow_preset', 'soft' ) );
    if ( 'none' === $shadow_preset ) {
        $css_vars['--account-card-shadow'] = 'none';
    } elseif ( 'medium' === $shadow_preset ) {
        $css_vars['--account-card-shadow'] = '0 12px 36px var(--qiling-color-rgba-15-23-42-014)';
    }

    if ( ! empty( $css_vars ) ) {
        $custom_css = '.account-page{';
        foreach ( $css_vars as $css_key => $css_value ) {
            $custom_css .= $css_key . ':' . $css_value . ';';
        }
        $custom_css .= '}';
        wp_add_inline_style( 'developer-starter-account', $custom_css );
    }
}, 20 );

get_header();

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$account_layout_mode = sanitize_key( (string) developer_starter_get_option( 'account_style_layout_mode', 'sidebar' ) );
if ( ! in_array( $account_layout_mode, array( 'sidebar', 'top_tabs' ), true ) ) {
    $account_layout_mode = 'sidebar';
}
$account_style_density = sanitize_key( (string) developer_starter_get_option( 'account_style_density', 'comfortable' ) );
if ( ! in_array( $account_style_density, array( 'comfortable', 'compact' ), true ) ) {
    $account_style_density = 'comfortable';
}
$account_page_classes = array( 'account-page' );
if ( 'top_tabs' === $account_layout_mode ) {
    $account_page_classes[] = 'account-layout-mode-top';
}
if ( 'compact' === $account_style_density ) {
    $account_page_classes[] = 'account-density-compact';
}
$notification_unread_count = function_exists( 'developer_starter_get_unread_notification_count' )
    ? developer_starter_get_unread_notification_count( $user_id )
    : 0;

// 当前激活的标签
$active_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'profile';

// 检查 WooCommerce 是否激活
$woo_active = class_exists( 'WooCommerce' );

// 定义可用标签
$tabs = array(
    'profile' => array( 'icon' => 'user', 'label' => __( '我的资料', 'developer-starter' ) ),
    'social' => array( 'icon' => 'share', 'label' => __( '社交媒体', 'developer-starter' ) ),
    'posts'    => array( 'icon' => 'file-text', 'label' => __( '投稿管理', 'developer-starter' ) ),
    'history'  => array( 'icon' => 'clock', 'label' => __( '互动记录', 'developer-starter' ) ),
    'notifications' => array( 'icon' => 'bell', 'label' => __( '站内通知', 'developer-starter' ) ),
    'security' => array( 'icon' => 'lock', 'label' => __( '账户安全', 'developer-starter' ) ),
);

// WooCommerce 标签
if ( $woo_active ) {
    $tabs['orders'] = array( 'icon' => 'package', 'label' => __( 'Woo订单', 'developer-starter' ) );
    $tabs['address'] = array( 'icon' => 'map', 'label' => __( 'Woo地址', 'developer-starter' ) );
}

// 身份证实名认证标签（功能启用时才显示）
$id_verification_enable = developer_starter_get_option( 'id_verification_enable', '' );
if ( $id_verification_enable === '1' ) {
    $tabs['verification'] = array( 'icon' => 'shield', 'label' => __( '实名认证', 'developer-starter' ) );
}

$account_deletion_enabled = developer_starter_get_option( 'account_deletion_request_enable', '' ) === '1';
$account_deletion_agreement = (string) developer_starter_get_option( 'account_deletion_request_agreement', '' );
if ( trim( $account_deletion_agreement ) === '' ) {
    $account_deletion_agreement = __( '提交注销申请后，账号不会立即删除。管理员将在后台审核后人工处理删除。请确认已备份个人数据。', 'developer-starter' );
}
$account_deletion_request = null;
$account_deletion_status = '';
if ( class_exists( '\Developer_Starter\Core\Account_Deletion_Manager' ) ) {
    $account_deletion_request = \Developer_Starter\Core\Account_Deletion_Manager::get_latest_request_for_user( $user_id );
    if ( $account_deletion_request ) {
        $account_deletion_status = sanitize_key( (string) $account_deletion_request->status );
    }
}
$account_deletion_pending = in_array( $account_deletion_status, array( 'pending', 'approved' ), true );
$account_deletion_can_submit = $account_deletion_enabled && ! $account_deletion_pending;

// 允许插件扩展标签页
$tabs = apply_filters( 'qiling_account_tabs', $tabs, $user_id );


// 处理表单提交
$message = '';
$message_type = '';

$account_request_method = isset( $_SERVER['REQUEST_METHOD'] )
    ? strtoupper( sanitize_text_field( wp_unslash( (string) $_SERVER['REQUEST_METHOD'] ) ) )
    : 'GET';
if ( 'POST' === $account_request_method && isset( $_POST['account_nonce'] ) ) {
    if ( wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['account_nonce'] ) ), 'developer_starter_account' ) ) {
        $action = isset( $_POST['account_action'] ) ? sanitize_key( wp_unslash( $_POST['account_action'] ) ) : '';
        
        if ( $action === 'update_profile' ) {
            $display_name = sanitize_text_field( wp_unslash( $_POST['display_name'] ?? '' ) );
            $user_email = sanitize_email( wp_unslash( $_POST['user_email'] ?? '' ) );
            $user_url = esc_url_raw( wp_unslash( $_POST['user_url'] ?? '' ) );
            $description = sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) );
            $birthday = sanitize_text_field( wp_unslash( $_POST['birthday'] ?? '' ) );
            $birthday_valid = true;
            if ( $birthday !== '' ) {
                if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $birthday ) ) {
                    list( $byear, $bmonth, $bday ) = array_map( 'intval', explode( '-', $birthday ) );
                    if ( ! checkdate( $bmonth, $bday, $byear ) || strtotime( $birthday ) > current_time( 'timestamp' ) ) {
                        $birthday_valid = false;
                    }
                } else {
                    $birthday_valid = false;
                }
            }
            
            $userdata = array(
                'ID' => $user_id,
                'display_name' => $display_name,
                'user_email' => $user_email,
                'user_url' => $user_url,
                'description' => $description,
            );
            
            $result = wp_update_user( $userdata );
            if ( is_wp_error( $result ) ) {
                $message = $result->get_error_message();
                $message_type = 'error';
            } else {
                if ( $birthday !== '' && ! $birthday_valid ) {
                    $message = __( '资料更新成功，但生日格式不正确', 'developer-starter' );
                    $message_type = 'error';
                } else {
                    $message = __( '资料更新成功！', 'developer-starter' );
                    $message_type = 'success';
                }
                $current_user = get_userdata( $user_id ); // 刷新用户数据
                if ( $birthday !== '' && $birthday_valid ) {
                    update_user_meta( $user_id, 'qilingshop_birthday', $birthday );
                    $birthday_md = sprintf( '%02d-%02d', $bmonth, $bday );
                    update_user_meta( $user_id, 'qilingshop_birthday_md', $birthday_md );
                    // 同步通用字段，兼容主题/插件间读取差异
                    update_user_meta( $user_id, 'birthday', $birthday );
                } elseif ( $birthday === '' ) {
                    delete_user_meta( $user_id, 'qilingshop_birthday' );
                    delete_user_meta( $user_id, 'qilingshop_birthday_md' );
                    delete_user_meta( $user_id, 'birthday' );
                }
            }
        }
        
        if ( $action === 'update_password' ) {
            // 密码需要按原文比较/设置，只做 wp_unslash()，不要做文本 sanitize。
            $current_pass = isset( $_POST['current_password'] ) && ! is_array( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '';
            $new_pass = isset( $_POST['new_password'] ) && ! is_array( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
            $confirm_pass = isset( $_POST['confirm_password'] ) && ! is_array( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : '';
            
            if ( empty( $current_pass ) || empty( $new_pass ) || empty( $confirm_pass ) ) {
                $message = __( '请填写所有密码字段', 'developer-starter' );
                $message_type = 'error';
            } elseif ( $new_pass !== $confirm_pass ) {
                $message = __( '新密码与确认密码不一致', 'developer-starter' );
                $message_type = 'error';
            } elseif ( strlen( $new_pass ) < 6 ) {
                $message = __( '新密码长度至少6位', 'developer-starter' );
                $message_type = 'error';
            } elseif ( ! wp_check_password( $current_pass, $current_user->user_pass, $user_id ) ) {
                $message = __( '当前密码不正确', 'developer-starter' );
                $message_type = 'error';
            } else {
                wp_set_password( $new_pass, $user_id );
                
                // 销毁当前用户的所有其他会话，确保其他设备需要重新登录
                $sessions = WP_Session_Tokens::get_instance( $user_id );
                $sessions->destroy_all();
                
                // 重新登录当前用户
                wp_set_current_user( $user_id );
                wp_set_auth_cookie( $user_id );
                $message = __( '密码修改成功！其他设备已自动登出。', 'developer-starter' );
                $message_type = 'success';
            }
        }
        
        if ( $action === 'update_social' ) {
            // 保存社交媒体链接
            $social_keys = array(
                'user_weibo' => 'url',
                'user_twitter' => 'url',
                'user_wechat' => 'url',
                'user_github' => 'url',
                'user_bilibili' => 'url',
                'user_zhihu' => 'url',
                'user_website' => 'url',
                'user_linkedin' => 'url',
                'user_youtube' => 'url',
                'user_instagram' => 'url',
                'user_tiktok' => 'url',
                'user_wechat_mp' => 'url',
                'user_qq' => 'text',
                'user_custom' => 'url',
            );
            foreach ( $social_keys as $key => $sanitize_type ) {
                if ( isset( $_POST[ $key ] ) ) {
                    $raw_value = wp_unslash( $_POST[ $key ] );
                    $value = ( $sanitize_type === 'text' )
                        ? sanitize_text_field( $raw_value )
                        : esc_url_raw( $raw_value );
                    update_user_meta( $user_id, $key, $value );
                }
            }
            // 使用 PRG 模式重定向，避免表单重复提交
            wp_safe_redirect( add_query_arg( array( 'tab' => 'social', 'saved' => '1' ), get_permalink() ) );
            exit;
        }

        if ( $action === 'mark_notice_read' ) {
            $notice_id = isset( $_POST['notice_id'] ) ? absint( wp_unslash( $_POST['notice_id'] ) ) : 0;
            if ( $notice_id && function_exists( 'developer_starter_mark_notification_read' ) ) {
                developer_starter_mark_notification_read( $notice_id, $user_id );
            }
            $redirect_args = array( 'tab' => 'notifications' );
            $notice_filter_redirect = isset( $_POST['notice_filter'] ) ? sanitize_key( wp_unslash( $_POST['notice_filter'] ) ) : '';
            if ( '' !== $notice_filter_redirect ) {
                $redirect_args['notice'] = $notice_filter_redirect;
            }
            if ( isset( $_POST['notice_page'] ) && '' !== (string) wp_unslash( $_POST['notice_page'] ) ) {
                $redirect_args['npage'] = absint( wp_unslash( $_POST['notice_page'] ) );
            }
            wp_safe_redirect( add_query_arg( $redirect_args, get_permalink() ) );
            exit;
        }

        if ( $action === 'mark_notice_all_read' ) {
            if ( function_exists( 'developer_starter_mark_all_notifications_read' ) ) {
                developer_starter_mark_all_notifications_read( $user_id );
            }
            $redirect_args = array( 'tab' => 'notifications' );
            $notice_filter_redirect = isset( $_POST['notice_filter'] ) ? sanitize_key( wp_unslash( $_POST['notice_filter'] ) ) : '';
            if ( '' !== $notice_filter_redirect ) {
                $redirect_args['notice'] = $notice_filter_redirect;
            }
            wp_safe_redirect( add_query_arg( $redirect_args, get_permalink() ) );
            exit;
        }

        if ( $action === 'clear_notice_all' ) {
            if ( function_exists( 'developer_starter_clear_all_notifications' ) ) {
                developer_starter_clear_all_notifications( $user_id );
            }
            wp_safe_redirect( add_query_arg( array( 'tab' => 'notifications', 'notice' => 'all' ), get_permalink() ) );
            exit;
        }
    }
}

// 检查是否刚保存成功
if ( isset( $_GET['saved'] ) && '1' === (string) wp_unslash( $_GET['saved'] ) ) {
    $message = __( '保存成功！', 'developer-starter' );
    $message_type = 'success';
}

if ( isset( $_GET['account_delete'] ) ) {
    $result = sanitize_key( wp_unslash( (string) $_GET['account_delete'] ) );
    if ( $result === 'success' ) {
        $message = __( '账号注销申请已提交，请等待管理员审核处理。', 'developer-starter' );
        $message_type = 'success';
    } elseif ( $result === 'exists' ) {
        $message = __( '您已有待审核的注销申请，请勿重复提交。', 'developer-starter' );
        $message_type = 'error';
    } elseif ( $result === 'agreement' ) {
        $message = __( '请先阅读并同意注销协议说明。', 'developer-starter' );
        $message_type = 'error';
    } elseif ( $result === 'disabled' ) {
        $message = __( '当前站点未开启注销申请功能。', 'developer-starter' );
        $message_type = 'error';
    } elseif ( $result === 'nonce' ) {
        $message = __( '安全校验失败，请刷新页面后重试。', 'developer-starter' );
        $message_type = 'error';
    } elseif ( $result === 'error' ) {
        $message = __( '提交失败，请稍后再试。', 'developer-starter' );
        $message_type = 'error';
    }
}

// 获取头像
$avatar_url = get_avatar_url( $user_id, array( 'size' => 150 ) );
$show_user_ip_location = developer_starter_get_option( 'show_user_ip_location', '' );
$ip_location = '';
if ( $show_user_ip_location && function_exists( 'developer_starter_get_user_ip_location' ) ) {
    $ip_location = developer_starter_get_user_ip_location( $user_id );
    if ( ! $ip_location && function_exists( 'developer_starter_update_user_ip_location_on_login' ) ) {
        developer_starter_update_user_ip_location_on_login( $current_user->user_login, $current_user );
        $ip_location = developer_starter_get_user_ip_location( $user_id );
    }
}
$birthday_profile_value = get_user_meta( $user_id, 'qilingshop_birthday', true );
if ( empty( $birthday_profile_value ) ) {
    $birthday_profile_value = get_user_meta( $user_id, 'birthday', true );
}
$user_public_profile_id = function_exists( 'developer_starter_get_user_public_profile_id' ) ? developer_starter_get_user_public_profile_id( $user_id ) : '';
?>

<div class="<?php echo esc_attr( implode( ' ', $account_page_classes ) ); ?>">
    <div class="account-header">
        <div class="container">
            <div class="account-user-card" data-aos="fade-up">
                <!-- Mobile Menu Toggle -->
                <button class="account-mobile-toggle" aria-label="Toggle Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <div class="user-avatar">
                    <?php echo get_avatar( $user_id, 100 ); ?>
                </div>
                <div class="user-info">
                    <h2 class="user-name">
                        <span class="qiling-user-display-name"><?php echo esc_html( $current_user->display_name ); ?></span>
                        <?php if ( $user_public_profile_id ) : ?>
                            <span class="qiling-user-public-id">
                                <span class="qiling-user-public-id-label">ID</span>
                                <span class="qiling-user-public-id-value"><?php echo esc_html( $user_public_profile_id ); ?></span>
                            </span>
                        <?php endif; ?>
                        <a href="<?php echo esc_url( get_author_posts_url( $user_id ) ); ?>" class="qiling-user-home-btn" title="<?php esc_attr_e( '个人主页', 'developer-starter' ); ?>" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        </a>
                    </h2>
                    <p class="user-email"><?php echo esc_html( $current_user->user_email ); ?></p>
                    <p class="user-meta">
                        <span><?php printf( esc_html__( '注册时间：%s', 'developer-starter' ), esc_html( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( $current_user->user_registered, false, true ) : date_i18n( 'Y-m-d', strtotime( $current_user->user_registered ) ) ) ); ?></span>
                        <?php if ( $show_user_ip_location && $ip_location ) : ?>
                            <span><?php printf( esc_html__( 'IP归属地：%s', 'developer-starter' ), esc_html( $ip_location ) ); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="account-content section-padding">
        <div class="container">
            <div class="account-layout">
                <!-- 侧边栏 -->
                <aside class="account-sidebar" data-aos="fade-right">
                    <nav class="account-nav">
                        <?php foreach ( $tabs as $tab_key => $tab_data ) : 
                            $is_active = $active_tab === $tab_key;
                            // Icon handling
                            $icon_html = '';
                            if ( $tab_key === 'posts' ) {
                                $icon_html = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
                            } elseif ( $tab_key === 'history' ) {
                                $icon_html = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
                            } else {
                                $icon_html = developer_starter_account_icon( $tab_data['icon'] );
                            }

                            $label_html = esc_html( $tab_data['label'] );
                            if ( $tab_key === 'notifications' && $notification_unread_count > 0 ) {
                                $label_html = '<span class="nav-text">' . esc_html( $tab_data['label'] ) . '</span><span class="nav-badge">' . intval( $notification_unread_count ) . '</span>';
                                $label_html = wp_kses( $label_html, array( 'span' => array( 'class' => true ) ) );
                            }
                        ?>
                            <a href="?tab=<?php echo $tab_key; ?>" class="account-nav-item <?php echo $is_active ? 'active' : ''; ?>">
                                <span class="nav-icon"><?php echo $icon_html; ?></span>
                                <span class="nav-label <?php echo $tab_key === 'notifications' ? 'has-badge' : ''; ?>"><?php echo $label_html; ?></span>
                            </a>
                        <?php endforeach; ?>
                        
                        <div class="nav-divider"></div>
                        
                        <a href="<?php echo esc_url( function_exists( 'developer_starter_get_front_logout_url' ) ? developer_starter_get_front_logout_url() : wp_logout_url( home_url() ) ); ?>" class="account-nav-item logout">
                            <span class="nav-icon"><?php echo developer_starter_account_icon( 'logout' ); ?></span>
                            <span class="nav-label"><?php esc_html_e( '退出登录', 'developer-starter' ); ?></span>
                        </a>
                    </nav>
                </aside>
                
                <!-- 主内容区 -->
                <main class="account-main" data-aos="fade-left">
                    <?php if ( $message ) : ?>
                        <div class="account-message <?php echo $message_type; ?>">
                            <?php echo esc_html( $message ); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $active_tab === 'profile' ) : 
                        $avatar_upload_enable = developer_starter_get_option( 'user_avatar_upload_enable', '' );
                    ?>
                    <!-- 我的资料 -->
                    <div class="account-section">
                        <?php if ( $avatar_upload_enable ) : ?>
                        <h3 class="section-title"><?php esc_html_e( '我的头像', 'developer-starter' ); ?></h3>
                        <div class="avatar-upload-section" style="margin-bottom: var(--qiling-space-30);">
                            <div class="avatar-upload-container" id="avatar-upload-container" style="
                                display: flex;
                                align-items: center;
                                gap: var(--qiling-space-24);
                                padding: var(--qiling-space-24);
                                background: var(--account-nav-hover-bg);
                                border-radius: 12px;
                                border: 2px dashed var(--account-border-color);
                                transition: all 0.3s ease;
                            ">
                                <div class="current-avatar" style="flex-shrink: 0;">
                                    <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php esc_attr_e( '当前头像', 'developer-starter' ); ?>" id="current-avatar-img" style="
                                        width: 100px;
                                        height: 100px;
                                        border-radius: 50%;
                                        object-fit: cover;
                                        border: 3px solid var(--account-card-bg);
                                        box-shadow: 0 4px 12px var(--qiling-color-rgba-0-0-0-01);
                                    " />
                                </div>
                                <div class="avatar-upload-info" style="flex: 1;">
                                    <h4 style="margin: 0 0 var(--qiling-space-8); font-size: var(--qiling-text-rem-1); font-weight: 600;"><?php esc_html_e( '上传新头像', 'developer-starter' ); ?></h4>
                                    <p style="margin: 0 0 var(--qiling-space-12); color: var(--account-muted-text); font-size: var(--qiling-text-rem-0p875);">
                                        <?php esc_html_e( '支持 JPG、PNG、GIF、WebP、AVIF 格式，最大 2MB', 'developer-starter' ); ?>
                                    </p>
                                    <div class="avatar-upload-actions">
                                        <label for="avatar-file-input" class="btn-secondary" style="
                                            display: inline-flex;
                                            align-items: center;
                                            gap: var(--qiling-space-6);
                                            padding: var(--qiling-space-10) var(--qiling-space-20);
                                            font-size: var(--qiling-text-rem-0p875);
                                            border-radius: 8px;
                                            cursor: pointer;
                                            background: var(--account-button-bg);
                                            color: var(--account-button-text);
                                            border: none;
                                            transition: all 0.3s;
                                        ">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                            <?php esc_html_e( '选择图片', 'developer-starter' ); ?>
                                        </label>
                                        <input type="file" id="avatar-file-input" accept="image/jpeg,image/png,image/gif,image/webp,image/avif" style="display: none;" />
                                    </div>
                                    <div id="avatar-upload-status" style="margin-top: var(--qiling-space-10); font-size: var(--qiling-text-rem-0p875);"></div>
                                </div>
                            </div>
                        </div>
                        <script>
                        (function() {
                            var container = document.getElementById('avatar-upload-container');
                            var fileInput = document.getElementById('avatar-file-input');
                            var avatarImg = document.getElementById('current-avatar-img');
                            var statusDiv = document.getElementById('avatar-upload-status');
                            var ajaxUrl = <?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>;
                            var nonce = <?php echo wp_json_encode( wp_create_nonce( 'developer_starter_avatar_upload' ) ); ?>;
                            
                            // 拖拽效果
                            container.addEventListener('dragover', function(e) {
                                e.preventDefault();
                                container.style.borderColor = 'var(--account-field-focus-color)';
                                container.style.background = 'var(--qiling-color-primary-alpha-008)';
                            });
                            container.addEventListener('dragleave', function(e) {
                                e.preventDefault();
                                container.style.borderColor = 'var(--account-border-color)';
                                container.style.background = '';
                            });
                            container.addEventListener('drop', function(e) {
                                e.preventDefault();
                                container.style.borderColor = 'var(--account-border-color)';
                                container.style.background = '';
                                if (e.dataTransfer.files.length) {
                                    handleFile(e.dataTransfer.files[0]);
                                }
                            });
                            
                            // 文件选择
                            fileInput.addEventListener('change', function() {
                                if (this.files.length) {
                                    handleFile(this.files[0]);
                                }
                            });
                            
                            function handleFile(file) {
                                // 验证文件类型
                                var allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif'];
                                if (allowedTypes.indexOf(file.type) === -1) {
                                    statusDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '只允许上传 JPG、PNG、GIF、WebP、AVIF 格式', 'developer-starter' ) ); ?></span>';
                                    return;
                                }
                                
                                // 验证文件大小
                                if (file.size > 2 * 1024 * 1024) {
                                    statusDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '图片大小不能超过 2MB', 'developer-starter' ) ); ?></span>';
                                    return;
                                }
                                
                                // 显示预览
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    avatarImg.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                                
                                // 上传
                                statusDiv.innerHTML = '<span style="color: var(--account-field-focus-color);"><?php echo esc_js( __( '上传中...', 'developer-starter' ) ); ?></span>';
                                
                                var formData = new FormData();
                                formData.append('action', 'developer_starter_upload_avatar');
                                formData.append('nonce', nonce);
                                formData.append('avatar', file);
                                
                                fetch(ajaxUrl, {
                                    method: 'POST',
                                    body: formData,
                                    credentials: 'same-origin'
                                })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        statusDiv.innerHTML = '<span style="color: var(--qiling-color-10b981);">✓ ' + data.data.message + '</span>';
                                        avatarImg.src = data.data.avatar_url;
                                        // 更新页面其他头像
                                        document.querySelectorAll('.user-avatar img, .avatar').forEach(function(img) {
                                            if (img.id !== 'current-avatar-img') {
                                                img.src = data.data.avatar_url;
                                            }
                                        });
                                    } else {
                                        statusDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);">' + data.data.message + '</span>';
                                    }
                                })
                                .catch(function() {
                                    statusDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '上传失败，请重试', 'developer-starter' ) ); ?></span>';
                                });
                            }
                        })();
                        </script>
                        <?php endif; ?>
                        
                        <h3 class="section-title"><?php esc_html_e( '基本资料', 'developer-starter' ); ?></h3>
                        <form method="post" class="account-form">
                            <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                            <input type="hidden" name="account_action" value="update_profile" />
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php esc_html_e( '用户名', 'developer-starter' ); ?></label>
                                    <input type="text" value="<?php echo esc_attr( $current_user->user_login ); ?>" disabled />
                                    <p class="form-hint"><?php esc_html_e( '用户名不可修改', 'developer-starter' ); ?></p>
                                </div>
                                <div class="form-group">
                                    <label><?php esc_html_e( '显示名称', 'developer-starter' ); ?></label>
                                    <input type="text" name="display_name" value="<?php echo esc_attr( $current_user->display_name ); ?>" required />
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php esc_html_e( '电子邮箱', 'developer-starter' ); ?></label>
                                    <input type="email" name="user_email" value="<?php echo esc_attr( $current_user->user_email ); ?>" required />
                                </div>
                                <div class="form-group">
                                    <label><?php esc_html_e( '个人网站', 'developer-starter' ); ?></label>
                                    <input type="url" name="user_url" value="<?php echo esc_attr( $current_user->user_url ); ?>" placeholder="https://" />
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php esc_html_e( '生日', 'developer-starter' ); ?></label>
                                    <input type="date" name="birthday" value="<?php echo esc_attr( $birthday_profile_value ); ?>" max="<?php echo esc_attr( date( 'Y-m-d', current_time( 'timestamp' ) ) ); ?>" />
                                    <p class="form-hint"><?php esc_html_e( '用于生日礼遇发放', 'developer-starter' ); ?></p>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><?php esc_html_e( '个人简介', 'developer-starter' ); ?></label>
                                <textarea name="description" rows="4" placeholder="<?php esc_attr_e( '介绍一下自己...', 'developer-starter' ); ?>"><?php echo esc_textarea( $current_user->description ); ?></textarea>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-primary"><?php esc_html_e( '保存修改', 'developer-starter' ); ?></button>
                            </div>
                        </form>
                    </div>
                    
                    <?php elseif ( $active_tab == 'posts' ) : ?>
                    <?php
                        // 动态获取投稿页面链接
                        $submit_page_id = developer_starter_get_option( 'submit_post_page_id' );
                        if ( ! $submit_page_id ) {
                            // 尝试通过页面模板查找
                            $pages = get_pages( array(
                                'meta_key'   => '_wp_page_template',
                                'meta_value' => 'templates/template-submit-post.php',
                                'number'     => 1
                            ) );
                            
                            if ( empty( $pages ) ) {
                                // 尝试不带目录的模板路径
                                $pages = get_pages( array(
                                    'meta_key'   => '_wp_page_template',
                                    'meta_value' => 'template-submit-post.php',
                                    'number'     => 1
                                ) );
                            }
                            
                            if ( ! empty( $pages ) ) {
                                $submit_page_id = $pages[0]->ID;
                            }
                        }
                        
                        $submit_url = $submit_page_id ? get_permalink( $submit_page_id ) : '#';
                        if ( ! $submit_page_id ) {
                            $submit_url = home_url( '/submit-post' ); // Fallback assuming a slug if all else fails
                        }
                    ?>
                    <div class="account-section-header">
                        <h2 class="account-section-title"><?php esc_html_e( '投稿管理', 'developer-starter' ); ?></h2>
                        <a href="<?php echo esc_url( $submit_url ); ?>" class="btn-primary btn-sm"><?php esc_html_e( '新建投稿', 'developer-starter' ); ?></a>
                    </div>
                    
                    <div class="account-posts-list">
                        <?php
                        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
                        if ( isset( $_GET['fpaged'] ) ) {
                            $paged = absint( wp_unslash( $_GET['fpaged'] ) );
                        }
                        
                        $args = array(
                            'author'         => $current_user->ID,
                            'post_type'      => 'post',
                            'post_status'    => array( 'publish', 'pending', 'draft', 'trash' ),
                            'posts_per_page' => 10,
                            'paged'          => $paged,
                            'ignore_sticky_posts' => true
                        );
                        
                        $user_posts = new WP_Query( $args );
                        
                        if ( $user_posts->have_posts() ) :
                        ?>
                            <div class="posts-table-wrapper">
                                <table class="posts-table">
                                    <thead>
                                        <tr>
                                            <th><?php esc_html_e( '标题', 'developer-starter' ); ?></th>
                                            <th><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                                            <th><?php esc_html_e( '时间', 'developer-starter' ); ?></th>
                                            <th><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ( $user_posts->have_posts() ) : $user_posts->the_post(); 
                                            $status = get_post_status();
                                            $status_label = '';
                                            $status_class = '';
                                            
                                            switch ( $status ) {
                                                case 'publish':
                                                    $status_label = __( '已发布', 'developer-starter' );
                                                    $status_class = 'status-publish';
                                                    break;
                                                case 'pending':
                                                    $status_label = __( '待审核', 'developer-starter' );
                                                    $status_class = 'status-pending';
                                                    break;
                                                case 'draft':
                                                    $status_label = __( '草稿', 'developer-starter' );
                                                    $status_class = 'status-draft';
                                                    break;
                                                case 'trash':
                                                    $status_label = __( '回收站', 'developer-starter' );
                                                    $status_class = 'status-trash';
                                                    break;
                                                default:
                                                    $status_label = $status;
                                                    break;
                                            }
                                        ?>
                                        <tr>
                                            <td class="post-title">
                                                <a href="<?php echo esc_url( get_permalink() ); ?>" target="_blank"><?php echo esc_html( get_the_title() ); ?></a>
                                                <?php if ( $status == 'pending' ) : ?>
                                                    <span class="badge" style="background:var(--color-warning);color:var(--color-neutral-0);font-size:var(--qiling-text-12);padding:var(--qiling-space-2) var(--qiling-space-6);border-radius:4px;margin-left:var(--qiling-space-5);"><?php esc_html_e( '审核中', 'developer-starter' ); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="post-status <?php echo esc_attr( $status_class ); ?>"><?php echo esc_html( $status_label ); ?></span></td>
                                            <td><?php echo esc_html( get_the_date( get_option( 'date_format' ) ) ); ?></td>
                                            <td class="post-actions">
                                                <?php if ( $status !== 'trash' ) : ?>
                                                    <?php 
                                                    $edit_url = $submit_page_id ? add_query_arg( array( 'post_id' => get_the_ID() ), get_permalink( $submit_page_id ) ) : '#';
                                                    ?>
                                                    <a href="<?php echo esc_url( $edit_url ); ?>" class="btn-text btn-edit">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                                        <?php esc_html_e( '编辑', 'developer-starter' ); ?>
                                                    </a>
                                                    <button type="button" class="btn-text btn-delete" onclick="developerStarterDeletePost(<?php the_ID(); ?>)">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                        <?php esc_html_e( '删除', 'developer-starter' ); ?>
                                                    </button>
                                                <?php else : ?>
                                                    <span class="text-muted"><?php esc_html_e( '已删除', 'developer-starter' ); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- 分页 -->
                            <?php
                            $big = 999999999;
                            echo '<div class="pagination">';
                            echo paginate_links( array(
                                'base'    => add_query_arg( 'fpaged', '%#%' ),
                                'format'  => '',
                                'current' => $paged,
                                'total'   => $user_posts->max_num_pages,
                                'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>',
                                'next_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                                'type'      => 'list'
                            ) );
                            echo '</div>';
                            ?>
                        <?php else : ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="var(--qiling-color-e5e7eb)" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                                <p><?php esc_html_e( '暂无投稿记录', 'developer-starter' ); ?></p>
                                <a href="<?php echo esc_url( $submit_url ); ?>" class="btn-primary"><?php esc_html_e( '去投稿', 'developer-starter' ); ?></a>
                            </div>
                        <?php endif; wp_reset_postdata(); ?>
                    </div>
                    
                    <script>
                    function developerStarterDeletePost(postId) {
                        if (confirm('<?php echo esc_js( __( '确定要删除这篇文章吗？删除后将移入回收站。', 'developer-starter' ) ); ?>')) {
                            var formData = new FormData();
                            formData.append('action', 'developer_starter_delete_post');
                            formData.append('post_id', postId);
                            formData.append('nonce', <?php echo wp_json_encode( wp_create_nonce( 'developer_starter_delete_post' ) ); ?>);
                            
                            fetch(<?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>, {
                                method: 'POST',
                                body: formData
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.success) {
                                    alert(data.data.message);
                                    window.location.reload();
                                } else {
                                    alert(data.data.message || '<?php echo esc_js( __( '删除失败', 'developer-starter' ) ); ?>');
                                }
                            })
                            .catch(function(e) {
                                alert('<?php echo esc_js( __( '网络错误，请重试', 'developer-starter' ) ); ?>');
                                console.error(e);
                            });
                        }
                    }
                    </script>
                    
                    <style>
                    /* 投稿列表样式优化 */
                    .account-section-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin-bottom: var(--qiling-space-24);
                        padding-bottom: var(--qiling-space-16);
                        border-bottom: 1px solid var(--account-border-color);
                    }
                    .account-section-title {
                        font-size: var(--qiling-text-rem-1p25);
                        font-weight: 600;
                        color: var(--account-field-text);
                        margin: 0;
                    }
                    .posts-table-wrapper {
                        background: var(--account-card-bg);
                        border-radius: 12px;
                        box-shadow: 0 1px 3px var(--qiling-color-rgba-0-0-0-005);
                        overflow: hidden;
                        margin-bottom: var(--qiling-space-24);
                        border: 1px solid var(--account-border-color);
                    }
                    .posts-table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .posts-table th {
                        background: var(--account-page-bg);
                        padding: var(--qiling-space-16);
                        font-weight: 500;
                        color: var(--account-muted-text);
                        text-align: left;
                        font-size: var(--qiling-text-rem-0p875);
                        border-bottom: 1px solid var(--account-border-color);
                    }
                    .posts-table td {
                        padding: var(--qiling-space-16);
                        border-bottom: 1px solid var(--account-border-color);
                        color: var(--account-card-text);
                    }
                    .posts-table tr:last-child td {
                        border-bottom: none;
                    }
                    .posts-table tr:hover td {
                        background-color: var(--account-nav-hover-bg);
                    }
                    
                    /* 状态标签 */
                    .post-status {
                        display: inline-flex;
                        align-items: center;
                        padding: var(--qiling-space-4) var(--qiling-space-10);
                        border-radius: 9999px;
                        font-size: var(--qiling-text-12);
                        font-weight: 500;
                    }
                    .status-publish { background-color: var(--qiling-color-d1fae5); color: var(--qiling-color-059669); }
                    .status-pending { background-color: var(--qiling-color-fef3c7); color: var(--color-warning-dark); }
                    .status-draft { background-color: var(--qiling-color-f3f4f6); color: var(--qiling-color-4b5563); }
                    .status-trash { background-color: var(--qiling-color-fee2e2); color: var(--color-error); }
                    
                    /* 操作按钮 */
                    .post-actions {
                        display: flex;
                        gap: var(--qiling-space-12);
                    }
                    .btn-text {
                        background: none;
                        border: none;
                        padding: var(--qiling-space-4) var(--qiling-space-8);
                        cursor: pointer;
                        font-size: var(--qiling-text-13);
                        display: flex;
                        align-items: center;
                        gap: var(--qiling-space-4);
                        border-radius: 4px;
                        transition: all 0.2s;
                    }
                    .btn-edit { color: var(--account-nav-hover-text); background: var(--account-nav-hover-bg); }
                    .btn-edit:hover { background: var(--account-home-button-hover-bg); }
                    .btn-delete { color: var(--qiling-color-ef4444); background: var(--qiling-color-rgba-239-68-68-005); }
                    .btn-delete:hover { background: var(--qiling-color-rgba-239-68-68-01); }
                    
                    /* 分页样式 */
                    .pagination {
                        margin-top: var(--qiling-space-30);
                        display: flex;
                        justify-content: center;
                    }
                    .pagination ul {
                        display: flex;
                        list-style: none;
                        padding: 0;
                        margin: 0;
                        gap: var(--qiling-space-8);
                    }
                    .pagination li .page-numbers {
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        min-width: var(--qiling-measure-36);
                        height: 36px;
                        padding: 0 var(--qiling-space-10);
                        border-radius: 8px;
                        background: var(--account-card-bg);
                        color: var(--account-muted-text);
                        font-size: var(--qiling-text-14);
                        font-weight: 500;
                        border: 1px solid var(--account-border-color);
                        transition: all 0.2s;
                    }
                    .pagination li .page-numbers.current,
                    .pagination li .page-numbers:hover {
                        background: var(--account-button-bg);
                        color: var(--account-button-text);
                        border-color: var(--account-button-bg);
                    }
                    .pagination li .page-numbers svg {
                        width: 16px;
                        height: 16px;
                    }
                    
                    /* 空状态 */
                    .empty-state {
                        text-align: center;
                        padding: var(--qiling-space-60) 0;
                        background: var(--account-card-bg);
                        border-radius: 12px;
                        border: 1px dashed var(--account-border-color);
                    }
                    .empty-icon {
                        margin-bottom: var(--qiling-space-16);
                    }
                    .empty-state p {
                        color: var(--account-muted-text);
                        margin-bottom: var(--qiling-space-24);
                    }
                    </style>

                    <?php elseif ( $active_tab === 'notifications' ) : ?>
                    <!-- 站内通知 -->
                    <div class="account-section">
                        <div class="notice-toolbar">
                            <h3 class="section-title"><?php esc_html_e( '站内通知', 'developer-starter' ); ?></h3>
                            <div class="notice-toolbar-actions">
                                <form method="post" class="notice-action-form">
                                    <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                                    <input type="hidden" name="account_action" value="mark_notice_all_read" />
                                    <input type="hidden" name="notice_filter" value="<?php echo esc_attr( isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : 'all' ); ?>" />
                                    <button type="submit" class="notice-btn notice-btn-secondary"><?php esc_html_e( '全部标为已读', 'developer-starter' ); ?></button>
                                </form>
                                <form
                                    method="post"
                                    class="notice-action-form"
                                    data-ds-confirm="1"
                                    data-ds-confirm-title="<?php echo esc_attr__( '清空站内通知', 'developer-starter' ); ?>"
                                    data-ds-confirm-message="<?php echo esc_attr__( '确定要清空全部站内通知吗？该操作不可恢复。', 'developer-starter' ); ?>"
                                    data-ds-confirm-ok="<?php echo esc_attr__( '确认清空', 'developer-starter' ); ?>"
                                >
                                    <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                                    <input type="hidden" name="account_action" value="clear_notice_all" />
                                    <button type="submit" class="notice-btn notice-btn-danger"><?php esc_html_e( '清空全部通知', 'developer-starter' ); ?></button>
                                </form>
                            </div>
                        </div>

                        <?php
                        $notice_filter = isset( $_GET['notice'] ) ? sanitize_key( wp_unslash( $_GET['notice'] ) ) : 'all';
                        if ( ! in_array( $notice_filter, array( 'all', 'unread' ), true ) ) {
                            $notice_filter = 'all';
                        }
                        $notice_page = isset( $_GET['npage'] ) ? max( 1, absint( wp_unslash( $_GET['npage'] ) ) ) : 1;
                        $notice_per_page = 10;
                        $notice_args = array(
                            'status' => $notice_filter,
                            'limit'  => $notice_per_page,
                            'offset' => ( $notice_page - 1 ) * $notice_per_page,
                        );
                        $notice_total = function_exists( 'developer_starter_get_user_notification_count' )
                            ? developer_starter_get_user_notification_count( $user_id, $notice_filter )
                            : 0;
                        $notice_items = function_exists( 'developer_starter_get_user_notifications' )
                            ? developer_starter_get_user_notifications( $user_id, $notice_args )
                            : array();
                        $notice_pages = $notice_per_page > 0 ? (int) ceil( $notice_total / $notice_per_page ) : 1;
                        ?>

                        <div class="notice-filter">
                            <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'notifications', 'notice' => 'all' ), get_permalink() ) ); ?>" class="notice-filter-item <?php echo $notice_filter === 'all' ? 'active' : ''; ?>">
                                <?php esc_html_e( '全部', 'developer-starter' ); ?>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'notifications', 'notice' => 'unread' ), get_permalink() ) ); ?>" class="notice-filter-item <?php echo $notice_filter === 'unread' ? 'active' : ''; ?>">
                                <?php esc_html_e( '未读', 'developer-starter' ); ?>
                            </a>
                        </div>

                        <?php if ( empty( $notice_items ) ) : ?>
                            <div class="notice-empty">
                                <div class="notice-empty-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 01-3.46 0"></path></svg>
                                </div>
                                <p><?php esc_html_e( '暂无通知记录', 'developer-starter' ); ?></p>
                            </div>
                        <?php else : ?>
                            <div class="notice-list">
                                <?php foreach ( $notice_items as $notice ) : ?>
                                    <?php
                                    $is_read = ! empty( $notice->is_read );
                                    $notice_type = isset( $notice->notice_type ) ? sanitize_key( $notice->notice_type ) : 'info';
                                    ?>
                                    <div class="notice-item <?php echo $is_read ? '' : 'unread'; ?>" data-type="<?php echo esc_attr( $notice_type ); ?>">
                                        <div class="notice-header">
                                            <div class="notice-title"><?php echo esc_html( $notice->title ); ?></div>
                                            <?php if ( ! $is_read ) : ?>
                                                <span class="notice-badge"><?php esc_html_e( '未读', 'developer-starter' ); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ( ! empty( $notice->content ) ) : ?>
                                            <div class="notice-content"><?php echo wp_kses_post( wpautop( $notice->content ) ); ?></div>
                                        <?php endif; ?>
                                        <div class="notice-meta">
                                            <span class="notice-time">
                                                <?php echo esc_html( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( $notice->created_at, true ) : date_i18n( 'Y-m-d H:i', strtotime( $notice->created_at ) ) ); ?>
                                            </span>
                                            <?php if ( ! empty( $notice->link_url ) ) : ?>
                                                <a class="notice-link" href="<?php echo esc_url( $notice->link_url ); ?>" target="_blank" rel="noopener noreferrer">
                                                    <?php esc_html_e( '查看详情', 'developer-starter' ); ?>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ( ! $is_read ) : ?>
                                                <form method="post" class="notice-action-form">
                                                    <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                                                    <input type="hidden" name="account_action" value="mark_notice_read" />
                                                    <input type="hidden" name="notice_id" value="<?php echo esc_attr( $notice->id ); ?>" />
                                                    <input type="hidden" name="notice_filter" value="<?php echo esc_attr( $notice_filter ); ?>" />
                                                    <input type="hidden" name="notice_page" value="<?php echo esc_attr( $notice_page ); ?>" />
                                                    <button type="submit" class="notice-btn"><?php esc_html_e( '标记已读', 'developer-starter' ); ?></button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <?php if ( $notice_pages > 1 ) : ?>
                                <div class="notice-pagination">
                                    <?php
                                    echo paginate_links( array(
                                        'base'    => add_query_arg( array( 'tab' => 'notifications', 'notice' => $notice_filter, 'npage' => '%#%' ), get_permalink() ),
                                        'format'  => '',
                                        'current' => $notice_page,
                                        'total'   => $notice_pages,
                                        'type'    => 'list',
                                    ) );
                                    ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php elseif ( $active_tab === 'history' ) : ?>
                    <!-- 互动记录 -->
                    <div class="account-section">
                        <h3 class="section-title"><?php esc_html_e( '互动记录', 'developer-starter' ); ?></h3>
                        
                        <?php
                        $history_type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : 'all';
                        if ( ! in_array( $history_type, array( 'all', 'favorite', 'like' ), true ) ) {
                            $history_type = 'all';
                        }
                        ?>
                        
                        <div class="history-tabs">
                            <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'history', 'type' => 'all' ), get_permalink() ) ); ?>" class="history-tab-item <?php echo $history_type === 'all' ? 'active' : ''; ?>">
                                <?php esc_html_e( '全部', 'developer-starter' ); ?>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'history', 'type' => 'favorite' ), get_permalink() ) ); ?>" class="history-tab-item <?php echo $history_type === 'favorite' ? 'active' : ''; ?>">
                                <?php esc_html_e( '我的收藏', 'developer-starter' ); ?>
                            </a>
                            <a href="<?php echo esc_url( add_query_arg( array( 'tab' => 'history', 'type' => 'like' ), get_permalink() ) ); ?>" class="history-tab-item <?php echo $history_type === 'like' ? 'active' : ''; ?>">
                                <?php esc_html_e( '我的点赞', 'developer-starter' ); ?>
                            </a>
                        </div>
                        
                        <div class="history-content">
                            <?php
                            $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
                            if ( isset( $_GET['fpaged'] ) ) {
                                $paged = absint( wp_unslash( $_GET['fpaged'] ) );
                            }
                            
                            $interactions = Developer_Starter\Core\Post_Interaction_Manager::get_user_interactions( $user_id, $history_type, 20, $paged );
                            
                            if ( ! empty( $interactions['items'] ) ) :
                            ?>
                                <div class="history-list">
                                    <?php foreach ( $interactions['items'] as $item ) : 
                                        $post_id = $item->post_id;
                                        if ( ! get_post_status( $post_id ) ) continue; // Skip if post deleted
                                    ?>
                                    <div class="history-item">
                                        <div class="history-item-content">
                                            <a href="<?php echo esc_url( get_permalink( $post_id ) ); ?>" class="history-item-title" target="_blank">
                                                <?php echo get_the_title( $post_id ); ?>
                                            </a>
                                            <div class="history-item-meta">
                                                <span class="history-date">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                    <?php echo esc_html( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( $item->created_at, true ) : date_i18n( 'Y-m-d H:i', strtotime( $item->created_at ) ) ); ?>
                                                </span>
                                                <?php if ( $item->interaction_type === 'favorite' ) : ?>
                                                    <span class="history-badge favorite">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                        <?php esc_html_e( '收藏', 'developer-starter' ); ?>
                                                    </span>
                                                <?php else : ?>
                                                    <span class="history-badge like">
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
                                                        <?php esc_html_e( '点赞', 'developer-starter' ); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Pagination -->
                                <?php
                                $total_pages = ceil( $interactions['total'] / 20 );
                                if ( $total_pages > 1 ) {
                                    echo '<div class="pagination">';
                                    echo paginate_links( array(
                                        'base'    => add_query_arg( 'fpaged', '%#%' ),
                                        'format'  => '',
                                        'current' => $paged,
                                        'total'   => $total_pages,
                                        'prev_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"></polyline></svg>',
                                        'next_text' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"></polyline></svg>',
                                        'type'      => 'list'
                                    ) );
                                    echo '</div>';
                                }
                                ?>
                                
                            <?php else : ?>
                                <div class="empty-state">
                                    <p><?php esc_html_e( '暂无互动记录', 'developer-starter' ); ?></p>
                                    <p class="empty-hint"><?php esc_html_e( '去发现感兴趣的文章，点赞或收藏吧！', 'developer-starter' ); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <style>
                    .history-tabs {
                        display: flex;
                        gap: var(--qiling-space-16);
                        margin-bottom: var(--qiling-space-24);
                        border-bottom: 1px solid var(--account-border-color);
                        padding-bottom: var(--qiling-space-16);
                    }
                    .history-tab-item {
                        padding: var(--qiling-space-8) var(--qiling-space-16);
                        border-radius: 9999px;
                        font-size: var(--qiling-text-14);
                        font-weight: 500;
                        color: var(--account-muted-text);
                        background: var(--account-nav-hover-bg);
                        transition: all 0.2s;
                        text-decoration: none;
                    }
                    .history-tab-item.active {
                        background: var(--account-button-bg);
                        color: var(--account-button-text);
                    }
                    
                    .history-list {
                        display: flex;
                        flex-direction: column;
                        gap: var(--qiling-space-16);
                    }
                    .history-item {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        padding: var(--qiling-space-16);
                        background: var(--account-card-bg);
                        border: 1px solid var(--account-border-color);
                        border-radius: 12px;
                        transition: all 0.2s;
                    }
                    .history-item:hover {
                        border-color: var(--account-field-focus-color);
                        box-shadow: 0 4px 12px var(--qiling-color-rgba-0-0-0-005);
                    }
                    .history-item-content {
                        flex: 1;
                    }
                    .history-item-title {
                        display: block;
                        font-size: var(--qiling-text-16);
                        font-weight: 600;
                        color: var(--account-field-text);
                        margin-bottom: var(--qiling-space-8);
                        text-decoration: none;
                        transition: color 0.2s;
                    }
                    .history-item-title:hover {
                        color: var(--account-field-focus-color);
                    }
                    .history-item-meta {
                        display: flex;
                        align-items: center;
                        gap: var(--qiling-space-12);
                        font-size: var(--qiling-text-13);
                        color: var(--account-muted-text);
                    }
                    .history-date {
                        display: flex;
                        align-items: center;
                        gap: var(--qiling-space-4);
                    }
                    .history-badge {
                        display: inline-flex;
                        align-items: center;
                        gap: var(--qiling-space-4);
                        padding: var(--qiling-space-2) var(--qiling-space-8);
                        border-radius: 4px;
                        font-size: var(--qiling-text-12);
                    }
                    .history-badge.favorite {
                        background: var(--qiling-color-fffbeb);
                        color: var(--color-warning-dark);
                    }
                    .history-badge.like {
                        background: var(--qiling-color-fee2e2);
                        color: var(--qiling-color-ef4444);
                    }
                    @media (max-width: 640px) {
                        .history-item {
                            flex-direction: column;
                            align-items: flex-start;
                            gap: var(--qiling-space-12);
                        }
                    }
                    </style>
                    
                    <?php elseif ( $active_tab === 'security' ) : ?>
                    <!-- 账户安全 -->
                    <div class="account-section">
                        <h3 class="section-title"><?php esc_html_e( '修改密码', 'developer-starter' ); ?></h3>
                        <form method="post" class="account-form">
                            <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                            <input type="hidden" name="account_action" value="update_password" />
                            
                            <div class="form-group">
                                <label><?php esc_html_e( '当前密码', 'developer-starter' ); ?></label>
                                <input type="password" name="current_password" required autocomplete="current-password" />
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><?php esc_html_e( '新密码', 'developer-starter' ); ?></label>
                                    <input type="password" name="new_password" required minlength="6" autocomplete="new-password" />
                                </div>
                                <div class="form-group">
                                    <label><?php esc_html_e( '确认新密码', 'developer-starter' ); ?></label>
                                    <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password" />
                                </div>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn-primary"><?php esc_html_e( '更新密码', 'developer-starter' ); ?></button>
                            </div>
                        </form>
                    </div>
                    
                    <?php 
                    // 手机号绑定功能（如果启用SMS）
                    $sms_enable = developer_starter_get_option( 'sms_enable', '' ) === '1';
                    if ( $sms_enable ) :
                        $user_phone = get_user_meta( $user_id, 'qiling_phone', true );
                        $phone_verified = get_user_meta( $user_id, 'qiling_phone_verified', true );
                        $masked_phone = $user_phone ? substr( $user_phone, 0, 3 ) . '****' . substr( $user_phone, -4 ) : '';
                        $phone_location_text = ( $user_phone && function_exists( 'developer_starter_get_user_phone_location_text' ) )
                            ? developer_starter_get_user_phone_location_text( $user_id, $user_phone )
                            : '';
                    ?>
                    <div class="account-section" style="margin-top: var(--qiling-space-40);">
                        <h3 class="section-title"><?php esc_html_e( '手机号绑定', 'developer-starter' ); ?></h3>
                        <p class="section-desc" style="color: var(--account-muted-text); margin-bottom: var(--qiling-space-24);">
                            <?php esc_html_e( '绑定手机号后，可使用手机验证码快捷登录', 'developer-starter' ); ?>
                        </p>
                        
                        <?php if ( $user_phone && $phone_verified ) : ?>
                        <!-- 已绑定手机号 -->
                        <div class="phone-bound-info" style="
                            display: flex;
                            align-items: center;
                            gap: var(--qiling-space-16);
                            padding: var(--qiling-space-20);
                            background: linear-gradient(135deg, var(--qiling-color-rgba-16-185-129-01) 0%, var(--qiling-color-rgba-52-211-153-01) 100%);
                            border-radius: 12px;
                            margin-bottom: var(--qiling-space-24);
                        ">
                            <div class="phone-icon" style="
                                width: 48px;
                                height: 48px;
                                background: linear-gradient(135deg, var(--qiling-color-10b981) 0%, var(--qiling-color-34d399) 100%);
                                border-radius: 12px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            ">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-neutral-0)" stroke-width="2"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
                            </div>
                            <div class="phone-info" style="flex: 1;">
                                <div style="font-weight: 600; margin-bottom: var(--qiling-space-4); color: var(--text-primary);">
                                    <?php echo esc_html( $masked_phone ); ?>
                                    <span style="
                                        display: inline-flex;
                                        align-items: center;
                                        gap: var(--qiling-space-4);
                                        padding: var(--qiling-space-2) var(--qiling-space-8);
                                        background: var(--qiling-color-10b981);
                                        color: var(--color-neutral-0);
                                        font-size: var(--qiling-text-12);
                                        border-radius: 4px;
                                        margin-left: var(--qiling-space-8);
                                    ">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                        <?php esc_html_e( '已验证', 'developer-starter' ); ?>
                                    </span>
                                </div>
                                <div style="font-size: var(--qiling-text-14); color: var(--account-muted-text);"><?php esc_html_e( '已绑定手机号', 'developer-starter' ); ?></div>
                                <?php if ( $phone_location_text ) : ?>
                                    <div style="font-size: var(--qiling-text-13); color: var(--color-neutral-600); margin-top: var(--qiling-space-6);">
                                        <?php printf( esc_html__( '手机号归属地：%s', 'developer-starter' ), esc_html( $phone_location_text ) ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- 换绑手机号 -->
                        <div id="rebind-phone-section" style="display: none;">
                            <h4 style="font-size: var(--qiling-text-16); margin-bottom: var(--qiling-space-16);"><?php esc_html_e( '换绑新手机号', 'developer-starter' ); ?></h4>
                            <div class="sms-form-group">
                                <label><?php esc_html_e( '新手机号', 'developer-starter' ); ?></label>
                                <input type="tel" id="rebind-phone-input" placeholder="<?php esc_attr_e( '请输入新手机号', 'developer-starter' ); ?>" maxlength="11" style="
                                    width: 100%;
                                    padding: var(--qiling-space-12) var(--qiling-space-16);
                                    border: 1px solid var(--account-field-border);
                                    background: var(--account-field-bg);
                                    color: var(--account-field-text);
                                    border-radius: 8px;
                                    font-size: var(--qiling-text-14);
                                    margin-bottom: var(--qiling-space-16);
                                " />
                            </div>
                            <div class="sms-form-group" style="position: relative;">
                                <label><?php esc_html_e( '验证码', 'developer-starter' ); ?></label>
                                <div style="display: flex; gap: var(--qiling-space-12);">
                                    <input type="text" id="rebind-code-input" placeholder="<?php esc_attr_e( '请输入验证码', 'developer-starter' ); ?>" maxlength="6" style="
                                        flex: 1;
                                        padding: var(--qiling-space-12) var(--qiling-space-16);
                                        border: 1px solid var(--account-field-border);
                                        background: var(--account-field-bg);
                                        color: var(--account-field-text);
                                        border-radius: 8px;
                                        font-size: var(--qiling-text-14);
                                    " />
                                    <button type="button" id="rebind-send-btn" style="
                                        padding: var(--qiling-space-10) var(--qiling-space-18);
                                        border: none;
                                        background: var(--account-button-bg);
                                        color: var(--account-button-text);
                                        font-size: var(--qiling-text-14);
                                        font-weight: 500;
                                        border-radius: 8px;
                                        cursor: pointer;
                                        white-space: nowrap;
                                        box-shadow: var(--account-button-hover-shadow);
                                        transition: all 0.3s;
                                    "><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                                </div>
                            </div>
                            <div id="rebind-message" style="margin: var(--qiling-space-12) 0; font-size: var(--qiling-text-14);"></div>
                            <div style="display: flex; gap: var(--qiling-space-12); margin-top: var(--qiling-space-16);">
                                <button type="button" id="rebind-submit-btn" class="btn-primary"><?php esc_html_e( '确认换绑', 'developer-starter' ); ?></button>
                                <button type="button" id="rebind-cancel-btn" class="btn-secondary"><?php esc_html_e( '取消', 'developer-starter' ); ?></button>
                            </div>
                        </div>
                        
                        <button type="button" id="show-rebind-btn" style="
                            display: flex;
                            align-items: center;
                            gap: var(--qiling-space-8);
                            padding: var(--qiling-space-10) var(--qiling-space-20);
                            border: none;
                            background: var(--account-button-bg);
                            color: var(--account-button-text);
                            font-size: var(--qiling-text-14);
                            font-weight: 500;
                            border-radius: 8px;
                            cursor: pointer;
                            box-shadow: var(--account-button-hover-shadow);
                            transition: all 0.3s;
                        ">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0118.8-4.3M22 12.5a10 10 0 01-18.8 4.3"/></svg>
                            <?php esc_html_e( '更换手机号', 'developer-starter' ); ?>
                        </button>
                        
                        <?php else : ?>
                        <!-- 未绑定手机号 -->
                        <div id="bind-phone-section">
                            <div class="sms-form-group">
                                <label style="display: block; margin-bottom: var(--qiling-space-8); font-weight: 500;"><?php esc_html_e( '手机号', 'developer-starter' ); ?></label>
                                <input type="tel" id="bind-phone-input" placeholder="<?php esc_attr_e( '请输入手机号', 'developer-starter' ); ?>" maxlength="11" style="
                                    width: 100%;
                                    padding: var(--qiling-space-12) var(--qiling-space-16);
                                    border: 1px solid var(--account-field-border);
                                    background: var(--account-field-bg);
                                    color: var(--account-field-text);
                                    border-radius: 8px;
                                    font-size: var(--qiling-text-14);
                                    margin-bottom: var(--qiling-space-16);
                                " />
                            </div>
                            <div class="sms-form-group" style="position: relative;">
                                <label style="display: block; margin-bottom: var(--qiling-space-8); font-weight: 500;"><?php esc_html_e( '验证码', 'developer-starter' ); ?></label>
                                <div style="display: flex; gap: var(--qiling-space-12);">
                                    <input type="text" id="bind-code-input" placeholder="<?php esc_attr_e( '请输入验证码', 'developer-starter' ); ?>" maxlength="6" style="
                                        flex: 1;
                                        padding: var(--qiling-space-12) var(--qiling-space-16);
                                        border: 1px solid var(--account-field-border);
                                        background: var(--account-field-bg);
                                        color: var(--account-field-text);
                                        border-radius: 8px;
                                        font-size: var(--qiling-text-14);
                                    " />
                                    <button type="button" id="bind-send-btn" style="
                                        padding: var(--qiling-space-10) var(--qiling-space-18);
                                        border: none;
                                        background: var(--account-button-bg);
                                        color: var(--account-button-text);
                                        font-size: var(--qiling-text-14);
                                        font-weight: 500;
                                        border-radius: 8px;
                                        cursor: pointer;
                                        white-space: nowrap;
                                        box-shadow: var(--account-button-hover-shadow);
                                        transition: all 0.3s;
                                    "><?php esc_html_e( '获取验证码', 'developer-starter' ); ?></button>
                                </div>
                            </div>
                            <div id="bind-message" style="margin: var(--qiling-space-12) 0; font-size: var(--qiling-text-14);"></div>
                            <button type="button" id="bind-submit-btn" class="btn-primary" style="margin-top: var(--qiling-space-8);"><?php esc_html_e( '绑定手机号', 'developer-starter' ); ?></button>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <script>
                    (function() {
                        var ajaxUrl = <?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>;
                        var smsNonce = <?php echo wp_json_encode( wp_create_nonce( 'sms_nonce' ) ); ?>;

                        function getDeviceFingerprint() {
                            try {
                                var key = 'ds_device_fingerprint_v1';
                                var fp = localStorage.getItem(key) || '';
                                if (!fp) {
                                    fp = (window.crypto && window.crypto.randomUUID)
                                        ? window.crypto.randomUUID()
                                        : ('ds-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2));
                                    localStorage.setItem(key, fp);
                                }
                                return String(fp || '').trim().toLowerCase();
                            } catch (e) {
                                return '';
                            }
                        }
                        
                        // 发送验证码通用函数
                        function sendCode(phoneInput, sendBtn, messageDiv, callback) {
                            var phone = phoneInput.value.trim();
                            if (!/^1[3-9]\d{9}$/.test(phone)) {
                                messageDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '请输入正确的手机号', 'developer-starter' ) ); ?></span>';
                                return;
                            }
                            
                            sendBtn.disabled = true;
                            sendBtn.textContent = '<?php echo esc_js( __( '发送中...', 'developer-starter' ) ); ?>';
                            
                            var formData = new FormData();
                            formData.append('action', 'sms_send_code');
                            formData.append('nonce', smsNonce);
                            formData.append('phone', phone);
                            formData.append('device_fingerprint', getDeviceFingerprint());
                            
                            fetch(ajaxUrl, { method: 'POST', body: formData })
                            .then(function(r) { return r.json(); })
                            .then(function(data) {
                                if (data.success) {
                                    messageDiv.innerHTML = '<span style="color: var(--qiling-color-10b981);">' + data.data.message + '</span>';
                                    var countdown = 60;
                                    var timer = setInterval(function() {
                                        countdown--;
                                        if (countdown > 0) {
                                            sendBtn.textContent = countdown + 's';
                                        } else {
                                            clearInterval(timer);
                                            sendBtn.disabled = false;
                                            sendBtn.textContent = '<?php echo esc_js( __( '获取验证码', 'developer-starter' ) ); ?>';
                                        }
                                    }, 1000);
                                    if (callback) callback();
                                } else {
                                    messageDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);">' + data.data.message + '</span>';
                                    sendBtn.disabled = false;
                                    sendBtn.textContent = '<?php echo esc_js( __( '获取验证码', 'developer-starter' ) ); ?>';
                                }
                            })
                            .catch(function() {
                                messageDiv.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '网络错误', 'developer-starter' ) ); ?></span>';
                                sendBtn.disabled = false;
                                sendBtn.textContent = '<?php echo esc_js( __( '获取验证码', 'developer-starter' ) ); ?>';
                            });
                        }
                        
                        // 绑定手机号
                        var bindPhoneInput = document.getElementById('bind-phone-input');
                        var bindCodeInput = document.getElementById('bind-code-input');
                        var bindSendBtn = document.getElementById('bind-send-btn');
                        var bindSubmitBtn = document.getElementById('bind-submit-btn');
                        var bindMessage = document.getElementById('bind-message');
                        
                        if (bindSendBtn) {
                            bindSendBtn.addEventListener('click', function() {
                                sendCode(bindPhoneInput, bindSendBtn, bindMessage);
                            });
                        }
                        
                        if (bindSubmitBtn) {
                            bindSubmitBtn.addEventListener('click', function() {
                                var phone = bindPhoneInput.value.trim();
                                var code = bindCodeInput.value.trim();
                                
                                if (!/^1[3-9]\d{9}$/.test(phone)) {
                                    bindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '请输入正确的手机号', 'developer-starter' ) ); ?></span>';
                                    return;
                                }
                                if (!/^\d{6}$/.test(code)) {
                                    bindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '请输入6位验证码', 'developer-starter' ) ); ?></span>';
                                    return;
                                }
                                
                                bindSubmitBtn.disabled = true;
                                bindSubmitBtn.textContent = '<?php echo esc_js( __( '绑定中...', 'developer-starter' ) ); ?>';
                                
                                var formData = new FormData();
                                formData.append('action', 'sms_bind_phone');
                                formData.append('nonce', smsNonce);
                                formData.append('phone', phone);
                                formData.append('code', code);
                                formData.append('device_fingerprint', getDeviceFingerprint());
                                
                                fetch(ajaxUrl, { method: 'POST', body: formData })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        bindMessage.innerHTML = '<span style="color: var(--qiling-color-10b981);">' + data.data.message + '</span>';
                                        setTimeout(function() { location.reload(); }, 1500);
                                    } else {
                                        bindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);">' + data.data.message + '</span>';
                                        bindSubmitBtn.disabled = false;
                                        bindSubmitBtn.textContent = '<?php echo esc_js( __( '绑定手机号', 'developer-starter' ) ); ?>';
                                    }
                                })
                                .catch(function() {
                                    bindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '网络错误', 'developer-starter' ) ); ?></span>';
                                    bindSubmitBtn.disabled = false;
                                    bindSubmitBtn.textContent = '<?php echo esc_js( __( '绑定手机号', 'developer-starter' ) ); ?>';
                                });
                            });
                        }
                        
                        // 换绑手机号
                        var showRebindBtn = document.getElementById('show-rebind-btn');
                        var rebindSection = document.getElementById('rebind-phone-section');
                        var rebindCancelBtn = document.getElementById('rebind-cancel-btn');
                        var rebindPhoneInput = document.getElementById('rebind-phone-input');
                        var rebindCodeInput = document.getElementById('rebind-code-input');
                        var rebindSendBtn = document.getElementById('rebind-send-btn');
                        var rebindSubmitBtn = document.getElementById('rebind-submit-btn');
                        var rebindMessage = document.getElementById('rebind-message');
                        
                        if (showRebindBtn) {
                            showRebindBtn.addEventListener('click', function() {
                                this.style.display = 'none';
                                rebindSection.style.display = 'block';
                            });
                        }
                        
                        if (rebindCancelBtn) {
                            rebindCancelBtn.addEventListener('click', function() {
                                rebindSection.style.display = 'none';
                                showRebindBtn.style.display = 'flex';
                            });
                        }
                        
                        if (rebindSendBtn) {
                            rebindSendBtn.addEventListener('click', function() {
                                sendCode(rebindPhoneInput, rebindSendBtn, rebindMessage);
                            });
                        }
                        
                        if (rebindSubmitBtn) {
                            rebindSubmitBtn.addEventListener('click', function() {
                                var phone = rebindPhoneInput.value.trim();
                                var code = rebindCodeInput.value.trim();
                                
                                if (!/^1[3-9]\d{9}$/.test(phone)) {
                                    rebindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '请输入正确的手机号', 'developer-starter' ) ); ?></span>';
                                    return;
                                }
                                if (!/^\d{6}$/.test(code)) {
                                    rebindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '请输入6位验证码', 'developer-starter' ) ); ?></span>';
                                    return;
                                }
                                
                                rebindSubmitBtn.disabled = true;
                                rebindSubmitBtn.textContent = '<?php echo esc_js( __( '换绑中...', 'developer-starter' ) ); ?>';
                                
                                var formData = new FormData();
                                formData.append('action', 'sms_bind_phone');
                                formData.append('nonce', smsNonce);
                                formData.append('phone', phone);
                                formData.append('code', code);
                                formData.append('device_fingerprint', getDeviceFingerprint());
                                
                                fetch(ajaxUrl, { method: 'POST', body: formData })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        rebindMessage.innerHTML = '<span style="color: var(--qiling-color-10b981);">' + data.data.message + '</span>';
                                        setTimeout(function() { location.reload(); }, 1500);
                                    } else {
                                        rebindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);">' + data.data.message + '</span>';
                                        rebindSubmitBtn.disabled = false;
                                        rebindSubmitBtn.textContent = '<?php echo esc_js( __( '确认换绑', 'developer-starter' ) ); ?>';
                                    }
                                })
                                .catch(function() {
                                    rebindMessage.innerHTML = '<span style="color: var(--qiling-color-ef4444);"><?php echo esc_js( __( '网络错误', 'developer-starter' ) ); ?></span>';
                                    rebindSubmitBtn.disabled = false;
                                    rebindSubmitBtn.textContent = '<?php echo esc_js( __( '确认换绑', 'developer-starter' ) ); ?>';
                                });
                            });
                        }
                    })();
                    </script>
                    <?php endif; ?>

                    <?php
                    // 微信绑定（启灵微信登录插件）
                    $weixin_login_enabled = function_exists( 'developer_starter_is_weixin_registration_allowed' )
                        ? developer_starter_is_weixin_registration_allowed()
                        : ( developer_starter_get_option( 'weixin_login_enable', '' ) === '1' && class_exists( 'qiling_weixin_login' ) );
                    $weixin_plugin_ready = class_exists( 'qiling_weixin_login' ) && class_exists( 'qiling_weixin_plugin' );
                    $show_weixin_bind_card = $weixin_login_enabled && $weixin_plugin_ready;
                    if ( $show_weixin_bind_card ) :
                    $weixin_openid = get_user_meta( $user_id, 'qiling_weixin_openid', true );
                    $weixin_avatar = get_user_meta( $user_id, 'qiling_weixin_avatar', true );
                    $weixin_bound = ! empty( $weixin_openid );
                    $weixin_appid = qiling_weixin_plugin::get_option( 'appid' );
                    $weixin_user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ) : '';
                    $weixin_is_wechat = '' !== $weixin_user_agent && preg_match( '/micromessenger/i', $weixin_user_agent );
                    ?>
                    <style>
                    .weixin-action-btn {
                        padding: var(--qiling-space-10) var(--qiling-space-18);
                        border-radius: 10px;
                        border: 1px solid var(--color-neutral-200);
                        background: var(--color-surface-alt);
                        color: var(--color-neutral-800);
                        font-weight: 600;
                        cursor: pointer;
                        transition: all 0.2s;
                    }
                    .weixin-action-btn:hover {
                        transform: translateY(-1px);
                        box-shadow: 0 6px 16px var(--qiling-color-rgba-15-23-42-008);
                    }
                    .weixin-action-primary {
                        border: none;
                        background: var(--qiling-gradient-cool);
                        color: var(--color-text-inverse);
                    }
                    .weixin-action-danger {
                        border: none;
                        background: var(--qiling-gradient-error);
                        color: var(--color-text-inverse);
                    }
                    .weixin-action-outline {
                        border: 1px solid var(--qiling-color-bfdbfe);
                        background: var(--qiling-color-eff6ff);
                        color: var(--color-primary-hover);
                    }
                    </style>
                    <div class="account-section" style="margin-top: var(--qiling-space-40);">
                        <h3 class="section-title"><?php esc_html_e( '微信绑定', 'developer-starter' ); ?></h3>
                        <p class="section-desc" style="color: var(--account-muted-text); margin-bottom: var(--qiling-space-24);">
                            <?php esc_html_e( '绑定微信后可使用扫码或微信内浏览器快捷登录。', 'developer-starter' ); ?>
                        </p>
                        <?php if ( $weixin_is_wechat && $weixin_plugin_ready && ! empty( $weixin_appid ) ) : ?>
                            <div class="notice" style="padding: var(--qiling-space-10) var(--qiling-space-14); background: var(--qiling-color-ecfeff); border: 1px solid var(--qiling-color-a5f3fc); border-radius: 8px; margin-bottom: var(--qiling-space-16);">
                                <?php esc_html_e( '检测到微信内浏览器，可直接使用 H5 授权完成登录/绑定。', 'developer-starter' ); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( ! $weixin_plugin_ready ) : ?>
                            <div class="notice" style="padding: var(--qiling-space-12) var(--qiling-space-16); background: var(--qiling-color-fff7ed); border: 1px solid var(--qiling-color-fed7aa); border-radius: 8px;">
                                <?php esc_html_e( '请先安装并启用“启灵微信登录”插件。', 'developer-starter' ); ?>
                            </div>
                        <?php elseif ( empty( $weixin_appid ) ) : ?>
                            <div class="notice" style="padding: var(--qiling-space-12) var(--qiling-space-16); background: var(--qiling-color-eff6ff); border: 1px solid var(--qiling-color-bfdbfe); border-radius: 8px;">
                                <?php esc_html_e( '请在后台完成启灵微信登录插件的 AppID / AppSecret 配置后再绑定。', 'developer-starter' ); ?>
                            </div>
                        <?php elseif ( $weixin_bound ) : ?>
                            <div class="phone-bound-info" style="
                                display: flex;
                                align-items: center;
                                gap: var(--qiling-space-16);
                                padding: var(--qiling-space-20);
                                background: linear-gradient(135deg, rgba(var(--color-primary-rgb), 0.08) 0%, rgba(var(--color-success-rgb), 0.08) 100%);
                                border-radius: 12px;
                                margin-bottom: var(--qiling-space-16);
                            ">
                                <div class="phone-icon" style="
                                    width: 48px;
                                    height: 48px;
                                    background: var(--qiling-gradient-cool);
                                    border-radius: 12px;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    overflow: hidden;
                                ">
                                    <?php if ( $weixin_avatar ) : ?>
                                        <img src="<?php echo esc_url( $weixin_avatar ); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else : ?>
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--color-text-inverse)" stroke-width="2">
                                            <path d="M3 12a9 9 0 0 1 9-9h0a9 9 0 0 1 9 9v3a3 3 0 0 1-3 3h-2l-3 3-3-3H6a3 3 0 0 1-3-3z"/>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="phone-info" style="flex: 1;">
                                    <div style="font-weight: 600; margin-bottom: var(--qiling-space-4); color: var(--text-primary);">
                                        <?php esc_html_e( '已绑定微信', 'developer-starter' ); ?>
                                        <span style="
                                            display: inline-flex;
                                            align-items: center;
                                            gap: var(--qiling-space-4);
                                            padding: var(--qiling-space-2) var(--qiling-space-8);
                                            background: var(--color-success);
                                            color: var(--color-text-inverse);
                                            font-size: var(--qiling-text-12);
                                            border-radius: 4px;
                                            margin-left: var(--qiling-space-8);
                                        ">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                            <?php esc_html_e( '已验证', 'developer-starter' ); ?>
                                        </span>
                                    </div>
                                    <div style="font-size: var(--qiling-text-14); color: var(--account-muted-text);">
                                        <?php esc_html_e( '如需更换微信，可重新绑定。', 'developer-starter' ); ?>
                                    </div>
                                </div>
                            </div>

                            <div style="display: flex; gap: var(--qiling-space-12); flex-wrap: wrap; margin-bottom: var(--qiling-space-12);">
                                <button type="button" id="show-weixin-bind-btn" class="weixin-action-btn weixin-action-primary">
                                    <?php esc_html_e( '重新绑定', 'developer-starter' ); ?>
                                </button>
                                <button type="button" id="show-weixin-unbind-btn" class="weixin-action-btn weixin-action-danger">
                                    <?php esc_html_e( '解绑微信', 'developer-starter' ); ?>
                                </button>
                            </div>
                            <div id="weixin-bind-section" style="display: none;">
                                <?php echo do_shortcode( '[qiling_weixin_bind autoload="0" layout="embed"]' ); ?>
                            </div>
                            <div id="weixin-unbind-section" style="display: none; margin-top: var(--qiling-space-12);">
                                <div style="margin-bottom: var(--qiling-space-8); font-weight: 500;"><?php esc_html_e( '确认解绑', 'developer-starter' ); ?></div>
                                <input type="password" id="weixin-unbind-password" placeholder="<?php esc_attr_e( '请输入登录密码', 'developer-starter' ); ?>" style="
                                    width: 100%;
                                    padding: var(--qiling-space-12) var(--qiling-space-16);
                                    border: 1px solid var(--account-field-border);
                                    background: var(--account-field-bg);
                                    color: var(--account-field-text);
                                    border-radius: 8px;
                                    font-size: var(--qiling-text-14);
                                    margin-bottom: var(--qiling-space-12);
                                " />
                                <button type="button" id="weixin-unbind-submit" class="weixin-action-btn weixin-action-danger">
                                    <?php esc_html_e( '确认解绑', 'developer-starter' ); ?>
                                </button>
                                <div id="weixin-unbind-message" style="margin-top: var(--qiling-space-10); font-size: var(--qiling-text-14);"></div>
                            </div>
                        <?php else : ?>
                            <button type="button" id="show-weixin-start-btn" class="weixin-action-btn weixin-action-outline" style="margin-bottom: var(--qiling-space-12);">
                                <?php esc_html_e( '开始绑定', 'developer-starter' ); ?>
                            </button>
                            <div id="weixin-bind-section" style="display:none;">
                                <?php echo do_shortcode( '[qiling_weixin_bind autoload="0" layout="embed"]' ); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <script>
                    (function() {
                        var showBindBtn = document.getElementById('show-weixin-bind-btn');
                        var showStartBtn = document.getElementById('show-weixin-start-btn');
                        var bindSection = document.getElementById('weixin-bind-section');
                        var showUnbindBtn = document.getElementById('show-weixin-unbind-btn');
                        var unbindSection = document.getElementById('weixin-unbind-section');
                        var unbindSubmit = document.getElementById('weixin-unbind-submit');
                        var unbindPassword = document.getElementById('weixin-unbind-password');
                        var unbindMessage = document.getElementById('weixin-unbind-message');
                        var ajaxUrl = <?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>;
                        var unbindNonce = <?php echo wp_json_encode( wp_create_nonce( 'qiling_weixin_unbind_nonce' ) ); ?>;

                        function showWeixinBind() {
                            if (!bindSection) return;
                            bindSection.style.display = 'block';
                            if (showBindBtn) showBindBtn.style.display = 'none';
                            if (showStartBtn) showStartBtn.style.display = 'none';
                            if (window.qilingWeixinStartQr) {
                                var box = bindSection.querySelector('.qiling-weixin-qr');
                                if (box) {
                                    window.qilingWeixinStartQr(box);
                                }
                            }
                        }

                        if (showBindBtn) {
                            showBindBtn.addEventListener('click', function() {
                                showWeixinBind();
                            });
                        }
                        if (showStartBtn) {
                            showStartBtn.addEventListener('click', function() {
                                showWeixinBind();
                            });
                        }

                        if (showUnbindBtn && unbindSection) {
                            showUnbindBtn.addEventListener('click', function() {
                                unbindSection.style.display = 'block';
                                showUnbindBtn.style.display = 'none';
                            });
                        }

                        if (unbindSubmit) {
                            unbindSubmit.addEventListener('click', function() {
                                var password = unbindPassword ? unbindPassword.value.trim() : '';
                                if (!password) {
                                    if (unbindMessage) {
                                        unbindMessage.innerHTML = '<span style="color:var(--qiling-color-ef4444);"><?php echo esc_js( __( '请输入登录密码', 'developer-starter' ) ); ?></span>';
                                    }
                                    return;
                                }

                                unbindSubmit.disabled = true;
                                unbindSubmit.textContent = '<?php echo esc_js( __( '解绑中...', 'developer-starter' ) ); ?>';

                                var formData = new FormData();
                                formData.append('action', 'qiling_weixin_unbind');
                                formData.append('nonce', unbindNonce);
                                formData.append('password', password);

                                fetch(ajaxUrl, { method: 'POST', body: formData })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    if (data.success) {
                                        if (unbindMessage) {
                                            unbindMessage.innerHTML = '<span style=\"color:var(--qiling-color-10b981);\">' + data.data.message + '</span>';
                                        }
                                        setTimeout(function() { location.reload(); }, 1200);
                                    } else {
                                        if (unbindMessage) {
                                            unbindMessage.innerHTML = '<span style=\"color:var(--qiling-color-ef4444);\">' + data.data.message + '</span>';
                                        }
                                        unbindSubmit.disabled = false;
                                        unbindSubmit.textContent = '<?php echo esc_js( __( '确认解绑', 'developer-starter' ) ); ?>';
                                    }
                                })
                                .catch(function() {
                                    if (unbindMessage) {
                                        unbindMessage.innerHTML = '<span style=\"color:var(--qiling-color-ef4444);\"><?php echo esc_js( __( '网络错误', 'developer-starter' ) ); ?></span>';
                                    }
                                    unbindSubmit.disabled = false;
                                    unbindSubmit.textContent = '<?php echo esc_js( __( '确认解绑', 'developer-starter' ) ); ?>';
                                });
                            });
                        }
                    })();
                    </script>
                    <?php endif; ?>

                    <?php if ( $account_deletion_enabled || $account_deletion_request ) : ?>
                    <div class="account-section account-deletion-section">
                        <h3 class="section-title"><?php esc_html_e( '账号注销申请', 'developer-starter' ); ?></h3>
                        <p class="section-desc">
                            <?php esc_html_e( '该功能为人工审核流程，提交后不会立即删除账号。管理员审核并手动处理后，账号才会被删除。', 'developer-starter' ); ?>
                        </p>

                        <?php if ( $account_deletion_request ) : ?>
                            <?php
                            $status_label = class_exists( '\Developer_Starter\Core\Account_Deletion_Manager' )
                                ? \Developer_Starter\Core\Account_Deletion_Manager::get_status_label( $account_deletion_status )
                                : __( '待审核', 'developer-starter' );
                            $status_class = 'is-' . ( $account_deletion_status ? $account_deletion_status : 'pending' );
                            $requested_at = ! empty( $account_deletion_request->created_at ) ? ( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( (string) $account_deletion_request->created_at, true ) : date_i18n( 'Y-m-d H:i', strtotime( (string) $account_deletion_request->created_at ) ) ) : '';
                            $reviewed_at  = ! empty( $account_deletion_request->reviewed_at ) ? ( function_exists( 'developer_starter_format_date_value' ) ? developer_starter_format_date_value( (string) $account_deletion_request->reviewed_at, true ) : date_i18n( 'Y-m-d H:i', strtotime( (string) $account_deletion_request->reviewed_at ) ) ) : '';
                            ?>
                            <div class="account-delete-status <?php echo esc_attr( $status_class ); ?>">
                                <div class="account-delete-status__badge"><?php echo esc_html( $status_label ); ?></div>
                                <?php if ( $requested_at ) : ?>
                                    <div class="account-delete-status__meta">
                                        <?php echo esc_html( sprintf( __( '申请时间：%s', 'developer-starter' ), $requested_at ) ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ( $reviewed_at ) : ?>
                                    <div class="account-delete-status__meta">
                                        <?php echo esc_html( sprintf( __( '处理时间：%s', 'developer-starter' ), $reviewed_at ) ); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ( ! empty( $account_deletion_request->reviewed_note ) ) : ?>
                                    <div class="account-delete-status__note">
                                        <?php echo esc_html( (string) $account_deletion_request->reviewed_note ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( $account_deletion_can_submit ) : ?>
                            <button type="button" class="notice-btn notice-btn-danger" id="ds-open-account-deletion-modal">
                                <?php esc_html_e( '申请注销账号', 'developer-starter' ); ?>
                            </button>
                        <?php elseif ( ! $account_deletion_enabled ) : ?>
                            <p class="form-hint"><?php esc_html_e( '管理员已关闭此功能。', 'developer-starter' ); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <?php if ( $account_deletion_can_submit ) : ?>
                    <div id="ds-account-deletion-modal" class="ds-account-deletion-modal" hidden>
                        <div class="ds-account-deletion-modal__backdrop" data-role="backdrop"></div>
                        <div class="ds-account-deletion-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="ds-account-deletion-title">
                            <h4 id="ds-account-deletion-title" class="ds-account-deletion-modal__title"><?php esc_html_e( '账号注销协议说明', 'developer-starter' ); ?></h4>
                            <div class="ds-account-deletion-modal__agreement">
                                <?php echo wp_kses_post( wpautop( $account_deletion_agreement ) ); ?>
                            </div>
                            <form method="post" class="ds-account-deletion-modal__form">
                                <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                                <input type="hidden" name="account_action" value="request_account_deletion" />
                                <label class="ds-account-deletion-modal__agree">
                                    <input type="checkbox" name="account_delete_agree" value="1" required />
                                    <span><?php esc_html_e( '我已阅读并同意以上协议说明', 'developer-starter' ); ?></span>
                                </label>
                                <div class="ds-account-deletion-modal__actions">
                                    <button type="button" class="notice-btn notice-btn-secondary" data-role="close"><?php esc_html_e( '取消', 'developer-starter' ); ?></button>
                                    <button type="submit" class="notice-btn notice-btn-danger"><?php esc_html_e( '确认提交申请', 'developer-starter' ); ?></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php elseif ( $active_tab === 'social' ) : ?>
                    <!-- 社交媒体设置 -->
                    <div class="account-section">
                        <h3 class="section-title"><?php esc_html_e( '社交媒体链接', 'developer-starter' ); ?></h3>
                        <p class="section-desc" style="color: var(--account-muted-text); margin-bottom: var(--qiling-space-24);"><?php esc_html_e( '设置您的社交媒体链接，这些信息将显示在您的作者信息卡片中。', 'developer-starter' ); ?></p>
                        <form method="post" class="account-form" enctype="multipart/form-data">
                            <?php wp_nonce_field( 'developer_starter_account', 'account_nonce' ); ?>
                            <input type="hidden" name="account_action" value="update_social" />
                            
                            <?php 
                            // 获取后台启用的社交链接字段
                            $social_fields = array(
                                'user_social_weibo' => array( 'key' => 'user_weibo', 'label' => __( '微博', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://weibo.com/u/...' ),
                                'user_social_twitter' => array( 'key' => 'user_twitter', 'label' => __( 'X (Twitter)', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://x.com/...' ),
                                'user_social_wechat' => array( 'key' => 'user_wechat', 'label' => __( '微信二维码', 'developer-starter' ), 'type' => 'image', 'placeholder' => __( '上传微信二维码图片', 'developer-starter' ) ),
                                'user_social_github' => array( 'key' => 'user_github', 'label' => __( 'GitHub', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://github.com/...' ),
                                'user_social_bilibili' => array( 'key' => 'user_bilibili', 'label' => __( 'B站', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://space.bilibili.com/...' ),
                                'user_social_zhihu' => array( 'key' => 'user_zhihu', 'label' => __( '知乎', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://www.zhihu.com/people/...' ),
                                'user_social_website' => array( 'key' => 'user_website', 'label' => __( '个人网站', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://...' ),
                                'user_social_linkedin' => array( 'key' => 'user_linkedin', 'label' => __( 'LinkedIn', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://www.linkedin.com/in/...' ),
                                'user_social_youtube' => array( 'key' => 'user_youtube', 'label' => __( 'YouTube', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://www.youtube.com/@...' ),
                                'user_social_instagram' => array( 'key' => 'user_instagram', 'label' => __( 'Instagram', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://www.instagram.com/...' ),
                                'user_social_tiktok' => array( 'key' => 'user_tiktok', 'label' => __( 'TikTok', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://www.tiktok.com/@...' ),
                                'user_social_wechat_mp' => array( 'key' => 'user_wechat_mp', 'label' => __( '公众号二维码', 'developer-starter' ), 'type' => 'image', 'placeholder' => __( '上传公众号二维码图片', 'developer-starter' ) ),
                                'user_social_qq' => array( 'key' => 'user_qq', 'label' => __( 'QQ', 'developer-starter' ), 'type' => 'text', 'placeholder' => __( 'QQ号或链接', 'developer-starter' ) ),
                                'user_social_custom' => array( 'key' => 'user_custom', 'label' => __( '自定义链接', 'developer-starter' ), 'type' => 'url', 'placeholder' => 'https://...' ),
                            );
                            
                            $has_fields = false;
                            foreach ( $social_fields as $option_key => $field ) :
                                if ( ! developer_starter_get_option( $option_key, '' ) ) continue;
                                $has_fields = true;
                                $current_value = get_user_meta( $user_id, $field['key'], true );
                            ?>
                                <div class="form-group">
                                    <label><?php echo esc_html( $field['label'] ); ?></label>
                                    <?php if ( $field['type'] === 'image' ) : ?>
                                        <div class="wechat-qr-upload">
                                            <?php if ( $current_value ) : ?>
                                                <div class="current-qr" style="margin-bottom: var(--qiling-space-10);">
                                                    <img src="<?php echo esc_url( $current_value ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" style="max-width: var(--qiling-measure-120); border-radius: 8px;" />
                                                </div>
                                            <?php endif; ?>
                                            <input type="url" name="<?php echo esc_attr( $field['key'] ); ?>" value="<?php echo esc_attr( $current_value ); ?>" placeholder="<?php esc_attr_e( '输入二维码图片URL', 'developer-starter' ); ?>" />
                                            <p class="form-hint"><?php esc_html_e( '请输入二维码图片的URL地址，鼠标悬停时会显示此二维码', 'developer-starter' ); ?></p>
                                        </div>
                                    <?php else : ?>
                                        <?php $input_type = ( $field['type'] === 'text' ) ? 'text' : 'url'; ?>
                                        <input type="<?php echo esc_attr( $input_type ); ?>" name="<?php echo esc_attr( $field['key'] ); ?>" value="<?php echo esc_attr( $current_value ); ?>" placeholder="<?php echo esc_attr( $field['placeholder'] ); ?>" />
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ( ! $has_fields ) : ?>
                                <div class="notice-info" style="padding: var(--qiling-space-20); background: var(--color-surface-alt); border-radius: 8px; text-align: center; color: var(--account-muted-text);">
                                    <p><?php esc_html_e( '管理员尚未启用任何社交链接字段。', 'developer-starter' ); ?></p>
                                </div>
                            <?php else : ?>
                                <div class="form-actions">
                                    <button type="submit" class="btn-primary"><?php esc_html_e( '保存社交信息', 'developer-starter' ); ?></button>
                                </div>
                            <?php endif; ?>
                        </form>
                    </div>
                    
                    <?php elseif ( $active_tab === 'orders' && $woo_active ) : ?>
                    <!-- 我的Woo订单 (WooCommerce) -->
                    <div class="account-section">
                        <h3 class="section-title"><?php esc_html_e( 'Woo订单', 'developer-starter' ); ?></h3>
                        <?php 
                        // 使用 WooCommerce 的订单列表
                        echo do_shortcode( '[woocommerce_my_account]' );
                        ?>
                    </div>
                    
                    <?php elseif ( $active_tab === 'address' && $woo_active ) : ?>
                    <!-- Woo收货地址 (WooCommerce) -->
                    <div class="account-section">
                        <h3 class="section-title"><?php esc_html_e( 'Woo地址', 'developer-starter' ); ?></h3>
                        <?php 
                        // 使用 WooCommerce 的地址管理
                        $addresses = wc_get_account_menu_items();
                        wc_get_template( 'myaccount/my-address.php' );
                        ?>
                    </div>
                    
                    <?php elseif ( $active_tab === 'verification' && $id_verification_enable === '1' ) : ?>
                    <!-- 实名认证 -->
                    <div class="account-section" id="id-verification-section">
                        <h3 class="section-title"><?php esc_html_e( '身份证实名认证', 'developer-starter' ); ?></h3>
                        <p class="section-desc" style="color: var(--account-muted-text); margin-bottom: var(--qiling-space-24);"><?php esc_html_e( '根据相关法规要求，请完成实名认证。您的信息将被严格保密。', 'developer-starter' ); ?></p>
                        
                        <div id="id-verification-container">
                            <div class="id-verification-loading" style="text-align: center; padding: var(--qiling-space-40);">
                                <svg class="spinner" viewBox="0 0 24 24" width="32" height="32" style="margin-bottom: var(--qiling-space-12);">
                                    <circle cx="12" cy="12" r="10" stroke="var(--account-field-focus-color)" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10">
                                        <animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/>
                                    </circle>
                                </svg>
                                <p style="color: var(--account-muted-text);"><?php esc_html_e( '加载中...', 'developer-starter' ); ?></p>
                            </div>
                        </div>
                        
                        <div id="id-verification-message" style="display: none; margin-top: var(--qiling-space-16);"></div>
                    </div>
                    
                    <script>
                    (function() {
                        var container = document.getElementById('id-verification-container');
                        var messageDiv = document.getElementById('id-verification-message');
                        var apiUrl = <?php echo wp_json_encode( esc_url_raw( rest_url( 'qiling/v1/id-verification' ) ) ); ?>;
                        var nonce = <?php echo wp_json_encode( wp_create_nonce( 'wp_rest' ) ); ?>;
                        
                        // 获取验证状态
                        fetch(apiUrl + '/status', {
                            method: 'GET',
                            headers: { 'X-WP-Nonce': nonce }
                        })
                        .then(function(r) { return r.json(); })
                        .then(function(data) {
                            if (data.verified) {
                                renderVerifiedUI(data);
                            } else {
                                renderVerificationForm();
                            }
                        })
                        .catch(function() {
                            showMessage('<?php echo esc_js( __( '获取验证状态失败，请刷新页面重试', 'developer-starter' ) ); ?>', 'error');
                        });
                        
                        // 渲染验证表单
                        function renderVerificationForm() {
                            var html = '<form id="id-verification-form" class="account-form" style="max-width: var(--qiling-measure-500);">' +
                                '<div class="form-group">' +
                                '    <label><?php echo esc_js( __( '真实姓名', 'developer-starter' ) ); ?> <span style="color: var(--qiling-color-ef4444);">*</span></label>' +
                                '    <input type="text" id="verify-name" required placeholder="<?php echo esc_js( __( '请输入身份证上的姓名', 'developer-starter' ) ); ?>" />' +
                                '</div>' +
                                '<div class="form-group">' +
                                '    <label><?php echo esc_js( __( '手机号码', 'developer-starter' ) ); ?> <span style="color: var(--qiling-color-ef4444);">*</span></label>' +
                                '    <input type="tel" id="verify-mobile" required placeholder="<?php echo esc_js( __( '请输入手机号码', 'developer-starter' ) ); ?>" maxlength="11" />' +
                                '</div>' +
                                '<div class="form-group">' +
                                '    <label><?php echo esc_js( __( '身份证号', 'developer-starter' ) ); ?> <span style="color: var(--qiling-color-ef4444);">*</span></label>' +
                                '    <input type="text" id="verify-idcard" required placeholder="<?php echo esc_js( __( '请输入18位身份证号码', 'developer-starter' ) ); ?>" maxlength="18" />' +
                                '</div>' +
                                '<div class="form-actions">' +
                                '    <button type="submit" class="btn-primary" id="verify-submit-btn" disabled>' +
                                '        <span class="btn-text"><?php echo esc_js( __( '提交验证', 'developer-starter' ) ); ?></span>' +
                                '        <span class="btn-loading" style="display:none;">' +
                                '            <svg class="spinner" viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-linecap="round" stroke-dasharray="31.416" stroke-dashoffset="10"><animateTransform attributeName="transform" type="rotate" from="0 12 12" to="360 12 12" dur="1s" repeatCount="indefinite"/></circle></svg>' +
                                '        </span>' +
                                '    </button>' +
                                '</div>' +
                            '</form>' +
                            '<div style="margin-top: var(--qiling-space-20); padding: var(--qiling-space-16); background: var(--color-surface-alt); border-radius: 8px; border-left: 4px solid var(--account-field-focus-color);">' +
                            '    <p style="margin: 0; color: var(--color-gray-600); font-size: var(--qiling-text-rem-0p875);">' +
                            '        <strong><?php echo esc_js( __( '温馨提示：', 'developer-starter' ) ); ?></strong><?php echo esc_js( __( '请确保输入的姓名、手机号与身份证信息一致，系统将通过运营商数据进行三要素核验。', 'developer-starter' ) ); ?>' +
                            '    </p>' +
                            '</div>';
                            
                            container.innerHTML = html;
                            
                            // 表单验证
                            var form = document.getElementById('id-verification-form');
                            var nameInput = document.getElementById('verify-name');
                            var mobileInput = document.getElementById('verify-mobile');
                            var idcardInput = document.getElementById('verify-idcard');
                            var submitBtn = document.getElementById('verify-submit-btn');
                            
                            function checkFormValid() {
                                var name = nameInput.value.trim();
                                var mobile = mobileInput.value.trim();
                                var idcard = idcardInput.value.trim();
                                var isValid = name && /^1[3-9]\d{9}$/.test(mobile) && /^\d{17}[\dXx]$/.test(idcard);
                                submitBtn.disabled = !isValid;
                            }
                            
                            nameInput.addEventListener('input', checkFormValid);
                            mobileInput.addEventListener('input', checkFormValid);
                            idcardInput.addEventListener('input', checkFormValid);
                            
                            // 表单提交
                            form.addEventListener('submit', function(e) {
                                e.preventDefault();
                                
                                submitBtn.disabled = true;
                                submitBtn.querySelector('.btn-text').style.display = 'none';
                                submitBtn.querySelector('.btn-loading').style.display = 'inline-flex';
                                
                                var formData = new FormData();
                                formData.append('name', nameInput.value.trim());
                                formData.append('mobile', mobileInput.value.trim());
                                formData.append('idcard', idcardInput.value.trim().toUpperCase());
                                
                                fetch(apiUrl + '/verify', {
                                    method: 'POST',
                                    headers: { 'X-WP-Nonce': nonce },
                                    body: formData
                                })
                                .then(function(r) { return r.json(); })
                                .then(function(data) {
                                    submitBtn.querySelector('.btn-text').style.display = 'inline';
                                    submitBtn.querySelector('.btn-loading').style.display = 'none';
                                    
                                    if (data.success) {
                                        showMessage('<?php echo esc_js( __( '实名认证成功！页面将自动刷新...', 'developer-starter' ) ); ?>', 'success');
                                        setTimeout(function() {
                                            location.reload();
                                        }, 1500);
                                    } else {
                                        var msg = data.message || (data.data && data.data.message) || '<?php echo esc_js( __( '验证失败，请检查信息是否正确', 'developer-starter' ) ); ?>';
                                        showMessage(msg, 'error');
                                        submitBtn.disabled = false;
                                    }
                                })
                                .catch(function(err) {
                                    submitBtn.querySelector('.btn-text').style.display = 'inline';
                                    submitBtn.querySelector('.btn-loading').style.display = 'none';
                                    showMessage('<?php echo esc_js( __( '网络错误，请稍后重试', 'developer-starter' ) ); ?>', 'error');
                                    submitBtn.disabled = false;
                                });
                            });
                        }
                        
                        // 渲染已验证界面
                        function renderVerifiedUI(data) {
                            var html = '<div class="verified-info" style="' +
                                'background: linear-gradient(135deg, var(--qiling-color-ecfdf5) 0%, var(--qiling-color-d1fae5) 100%);' +
                                'border-radius: 12px;' +
                                'padding: var(--qiling-space-24);' +
                                'margin-bottom: var(--qiling-space-24);' +
                                '">' +
                                '<div style="display: flex; align-items: center; gap: var(--qiling-space-12); margin-bottom: var(--qiling-space-16);">' +
                                '    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--qiling-color-10b981)" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>' +
                                '    <h4 style="margin: 0; color: var(--qiling-color-065f46); font-size: var(--qiling-text-rem-1p1);"><?php echo esc_js( __( '实名认证已完成', 'developer-starter' ) ); ?></h4>' +
                                '</div>' +
                                '<div style="display: grid; gap: var(--qiling-space-12); color: var(--qiling-color-047857);">' +
                                '    <div style="display: flex; gap: var(--qiling-space-8);"><span style="min-width: var(--qiling-measure-70);"><?php echo esc_js( __( '姓名：', 'developer-starter' ) ); ?></span><span>' + data.name + '</span></div>' +
                                '    <div style="display: flex; gap: var(--qiling-space-8);"><span style="min-width: var(--qiling-measure-70);"><?php echo esc_js( __( '手机号：', 'developer-starter' ) ); ?></span><span>' + data.mobile + '</span></div>' +
                                '    <div style="display: flex; gap: var(--qiling-space-8);"><span style="min-width: var(--qiling-measure-70);"><?php echo esc_js( __( '身份证：', 'developer-starter' ) ); ?></span><span>' + data.idcard + '</span></div>' +
                                '</div>' +
                                '</div>' +
                                '<button type="button" class="btn-secondary" id="reverify-btn" style="' +
                                'padding: var(--qiling-space-10) var(--qiling-space-20);' +
                                'border-radius: 8px;' +
                                'border: 1px solid var(--account-border-color);' +
                                'background: var(--account-card-bg);' +
                                'cursor: pointer;' +
                                'transition: all 0.3s;' +
                                '"><?php echo esc_js( __( '修改认证信息', 'developer-starter' ) ); ?></button>';
                            
                            container.innerHTML = html;
                            
                            document.getElementById('reverify-btn').addEventListener('click', function() {
                                if (confirm('<?php echo esc_js( __( '修改认证信息将消耗一次验证次数，确定要修改吗？', 'developer-starter' ) ); ?>')) {
                                    renderVerificationForm();
                                }
                            });
                        }
                        
                        // 显示消息
                        function showMessage(msg, type) {
                            messageDiv.style.display = 'block';
                            messageDiv.className = 'account-message ' + type;
                            messageDiv.textContent = msg;
                            
                            if (type !== 'success') {
                                setTimeout(function() {
                                    messageDiv.style.display = 'none';
                                }, 5000);
                            }
                        }
                    })();
                    </script>
                    
                    <?php else : ?>
                    
                    <?php 
                    // 允许插件渲染自定义标签页内容
                    do_action( 'qiling_account_tab_content', $active_tab, $user_id ); 
                    ?>
                    
                    <?php endif; ?>
                    
                </main>
            </div>
        </div>
    </div>
</div>

<div id="ds-account-confirm" class="ds-account-confirm" hidden>
    <div class="ds-account-confirm__backdrop" data-role="backdrop"></div>
    <div class="ds-account-confirm__dialog" role="dialog" aria-modal="true" aria-labelledby="ds-account-confirm-title">
        <h4 id="ds-account-confirm-title" class="ds-account-confirm__title"><?php esc_html_e( '操作确认', 'developer-starter' ); ?></h4>
        <p id="ds-account-confirm-message" class="ds-account-confirm__message"><?php esc_html_e( '确定要继续执行该操作吗？', 'developer-starter' ); ?></p>
        <div class="ds-account-confirm__actions">
            <button type="button" class="notice-btn notice-btn-secondary" data-role="cancel"><?php esc_html_e( '取消', 'developer-starter' ); ?></button>
            <button type="button" class="notice-btn notice-btn-danger" data-role="confirm"><?php esc_html_e( '确认', 'developer-starter' ); ?></button>
        </div>
    </div>
</div>

<?php
// 辅助函数：输出图标
function developer_starter_account_icon( $icon ) {
    $icons = array(
        'user' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'share' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
        'lock' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>',
        'shield' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'package' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16.5 9.4l-9-5.19M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
        'map' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'bell' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8a6 6 0 00-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 01-3.46 0"></path></svg>',
        'logout' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>',
        // 以下图标用于插件扩展
        'shopping-cart' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
        'award' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>',
        'list' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
        'users' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'credit-card' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
    );
    
    // 允许插件添加自定义图标
    $icons = apply_filters( 'qiling_account_icons', $icons );
    
    return isset( $icons[ $icon ] ) ? $icons[ $icon ] : '';
}

?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.account-mobile-toggle');
    const sidebar = document.querySelector('.account-sidebar');
    const overlay = document.createElement('div');
    overlay.className = 'account-sidebar-overlay';
    document.body.appendChild(overlay);

    const confirmRoot = document.getElementById('ds-account-confirm');
    const deletionRoot = document.getElementById('ds-account-deletion-modal');

    if (deletionRoot && deletionRoot.parentNode !== document.body) {
        document.body.appendChild(deletionRoot);
    }

    function syncBodyLock() {
        const menuOpen = !!(sidebar && sidebar.classList.contains('active'));
        const confirmOpen = !!(confirmRoot && !confirmRoot.hidden);
        const deletionOpen = !!(deletionRoot && !deletionRoot.hidden);
        document.body.style.overflow = (menuOpen || confirmOpen || deletionOpen) ? 'hidden' : '';
    }

    function closeMenu() {
        if (!sidebar || !mobileToggle) {
            return;
        }
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        mobileToggle.classList.remove('active');
        syncBodyLock();
    }

    function toggleMenu() {
        if (!sidebar || !mobileToggle) {
            return;
        }
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        mobileToggle.classList.toggle('active');
        syncBodyLock();
    }

    if (mobileToggle) {
        mobileToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleMenu();
        });
    }

    overlay.addEventListener('click', closeMenu);
    
    // 点击导航链接后自动关闭菜单
    const navLinks = document.querySelectorAll('.account-nav-item');
    navLinks.forEach(function(link) {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 991) {
                closeMenu();
            }
        });
    });

    const confirmTitle = document.getElementById('ds-account-confirm-title');
    const confirmMessage = document.getElementById('ds-account-confirm-message');
    const confirmCancel = confirmRoot ? confirmRoot.querySelector('[data-role="cancel"]') : null;
    const confirmSubmit = confirmRoot ? confirmRoot.querySelector('[data-role="confirm"]') : null;
    const confirmBackdrop = confirmRoot ? confirmRoot.querySelector('[data-role="backdrop"]') : null;
    let pendingForm = null;

    function closeAccountConfirm() {
        if (!confirmRoot) {
            return;
        }
        confirmRoot.classList.remove('is-active');
        window.setTimeout(function() {
            confirmRoot.hidden = true;
            syncBodyLock();
        }, 120);
        pendingForm = null;
    }

    function openAccountConfirm(form) {
        if (!confirmRoot || !form) {
            return;
        }
        pendingForm = form;
        const titleText = form.getAttribute('data-ds-confirm-title') || '<?php echo esc_js( __( '操作确认', 'developer-starter' ) ); ?>';
        const messageText = form.getAttribute('data-ds-confirm-message') || '<?php echo esc_js( __( '确定要继续执行该操作吗？', 'developer-starter' ) ); ?>';
        const confirmText = form.getAttribute('data-ds-confirm-ok') || '<?php echo esc_js( __( '确认', 'developer-starter' ) ); ?>';
        if (confirmTitle) {
            confirmTitle.textContent = titleText;
        }
        if (confirmMessage) {
            confirmMessage.textContent = messageText;
        }
        if (confirmSubmit) {
            confirmSubmit.textContent = confirmText;
        }
        confirmRoot.hidden = false;
        requestAnimationFrame(function() {
            confirmRoot.classList.add('is-active');
            syncBodyLock();
        });
    }

    if (confirmRoot) {
        document.querySelectorAll('form[data-ds-confirm="1"]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                if (form.dataset.dsConfirmed === '1') {
                    form.dataset.dsConfirmed = '';
                    return;
                }
                e.preventDefault();
                openAccountConfirm(form);
            });
        });

        if (confirmCancel) {
            confirmCancel.addEventListener('click', closeAccountConfirm);
        }
        if (confirmBackdrop) {
            confirmBackdrop.addEventListener('click', closeAccountConfirm);
        }
        if (confirmSubmit) {
            confirmSubmit.addEventListener('click', function() {
                if (!pendingForm) {
                    closeAccountConfirm();
                    return;
                }
                pendingForm.dataset.dsConfirmed = '1';
                const formToSubmit = pendingForm;
                closeAccountConfirm();
                formToSubmit.submit();
            });
        }
    }

    const deletionTrigger = document.getElementById('ds-open-account-deletion-modal');
    const deletionBackdrop = deletionRoot ? deletionRoot.querySelector('[data-role="backdrop"]') : null;
    const deletionCloseButtons = deletionRoot ? deletionRoot.querySelectorAll('[data-role="close"]') : [];
    const deletionForm = deletionRoot ? deletionRoot.querySelector('form') : null;
    const deletionAgree = deletionRoot ? deletionRoot.querySelector('input[name="account_delete_agree"]') : null;

    function closeDeletionModal() {
        if (!deletionRoot) {
            return;
        }
        deletionRoot.classList.remove('is-active');
        window.setTimeout(function() {
            deletionRoot.hidden = true;
            deletionRoot.setAttribute('hidden', 'hidden');
            if (deletionForm) {
                deletionForm.reset();
            } else if (deletionAgree) {
                deletionAgree.checked = false;
            }
            syncBodyLock();
        }, 120);
    }

    function openDeletionModal() {
        if (!deletionRoot) {
            return;
        }
        deletionRoot.hidden = false;
        deletionRoot.removeAttribute('hidden');
        requestAnimationFrame(function() {
            deletionRoot.classList.add('is-active');
            syncBodyLock();
        });
    }

    if (deletionTrigger && deletionRoot) {
        deletionTrigger.addEventListener('click', openDeletionModal);
    }
    if (deletionBackdrop) {
        deletionBackdrop.addEventListener('click', closeDeletionModal);
    }
    if (deletionCloseButtons && deletionCloseButtons.length > 0) {
        deletionCloseButtons.forEach(function(button) {
            button.addEventListener('click', closeDeletionModal);
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Escape') {
            return;
        }
        if (deletionRoot && !deletionRoot.hidden) {
            closeDeletionModal();
        }
        if (confirmRoot && !confirmRoot.hidden) {
            closeAccountConfirm();
        }
    });
});
</script>

<?php get_footer(); ?>
