<?php
/**
 * 后台评论列表自定义列管理类
 *
 * 在后台评论列表中解析并显示用户客户端设备、操作系统、浏览器以及 IP 归属地，
 * 满足国内网信安全审计与跟帖评论日志留存查验要求。
 *
 * @package Developer_Starter
 * @since 1.0.0
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Comment_Columns {

    /**
     * 单例锁，防止重复挂载
     *
     * @var bool
     */
    private static $initialized = false;

    /**
     * 构造函数
     */
    public function __construct() {
        if ( self::$initialized ) {
            return;
        }
        self::$initialized = true;

        // 列表页自定义列
        add_filter( 'manage_edit-comments_columns', array( $this, 'add_columns' ) );
        add_action( 'manage_comments_custom_column', array( $this, 'manage_columns' ), 10, 2 );

        // 编辑单条评论详情页增加合规指纹面板
        add_action( 'add_meta_boxes_comment', array( $this, 'add_comment_meta_box' ) );

        // 注入管理后台样式
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * 添加自定义列到评论列表（紧随在“作者”列后）
     *
     * @param array $columns 原始列数组。
     * @return array
     */
    public function add_columns( $columns ) {
        $new_columns = array();

        foreach ( $columns as $key => $title ) {
            $new_columns[ $key ] = $title;
            if ( 'author' === $key ) {
                $new_columns['comment_device'] = __( '设备 / 归属地', 'qiling' );
            }
        }

        if ( ! isset( $new_columns['comment_device'] ) ) {
            $new_columns['comment_device'] = __( '设备 / 归属地', 'qiling' );
        }

        return $new_columns;
    }

    /**
     * 渲染自定义列内容
     *
     * @param string $column_name 列标识。
     * @param int    $comment_id  评论 ID。
     * @return void
     */
    public function manage_columns( $column_name, $comment_id ) {
        if ( 'comment_device' !== $column_name ) {
            return;
        }

        $comment = get_comment( $comment_id );
        if ( ! $comment ) {
            echo '-';
            return;
        }

        $ua     = trim( (string) $comment->comment_agent );
        $parsed = self::parse_user_agent( $ua );

        // 图标匹配
        $icon_class = 'dashicons-desktop';
        if ( 'mobile' === $parsed['type'] ) {
            $icon_class = 'dashicons-smartphone';
        } elseif ( 'tablet' === $parsed['type'] ) {
            $icon_class = 'dashicons-tablet';
        } elseif ( 'bot' === $parsed['type'] ) {
            $icon_class = 'dashicons-admin-generic';
        }

        // IP 归属地
        $location = '';
        if ( function_exists( 'developer_starter_get_comment_ip_location' ) ) {
            $location = developer_starter_get_comment_ip_location( (int) $comment->user_id, $comment );
        }

        $tooltip = ! empty( $ua )
            ? sprintf( __( '客户端指纹 (User-Agent): %s', 'qiling' ), $ua )
            : __( '未记录客户端 User-Agent 数据', 'qiling' );
        ?>
        <div class="qiling-comment-device" title="<?php echo esc_attr( $tooltip ); ?>">
            <div class="device-name" style="font-weight: 500; color: #1e293b; display: flex; align-items: center; gap: 5px;">
                <span class="dashicons <?php echo esc_attr( $icon_class ); ?>" style="font-size: 15px; width: 15px; height: 15px; color: #64748b;"></span>
                <span><?php echo esc_html( $parsed['full_label'] ); ?></span>
            </div>
            <?php if ( ! empty( $location ) ) : ?>
                <div class="device-location" style="margin-top: 3px; font-size: 12px; color: #64748b; display: flex; align-items: center; gap: 4px;">
                    <span class="dashicons dashicons-location" style="font-size: 14px; width: 14px; height: 14px; color: #0284c7;"></span>
                    <span><?php echo esc_html( $location ); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * 在单条评论编辑页注册合规指纹 MetaBox
     *
     * @param \WP_Comment $comment 当前评论对象。
     * @return void
     */
    public function add_comment_meta_box( $comment ) {
        add_meta_box(
            'qiling_comment_device_meta_box',
            __( '终端设备与合规指纹 (网络安全留存)', 'qiling' ),
            array( $this, 'render_comment_meta_box' ),
            'comment',
            'normal',
            'high'
        );
    }

    /**
     * 渲染单条评论详情页的终端环境与指纹
     *
     * @param \WP_Comment $comment 当前评论。
     * @return void
     */
    public function render_comment_meta_box( $comment ) {
        $ua       = trim( (string) $comment->comment_agent );
        $parsed   = self::parse_user_agent( $ua );
        $location = function_exists( 'developer_starter_get_comment_ip_location' )
            ? developer_starter_get_comment_ip_location( (int) $comment->user_id, $comment )
            : '';
        $ip       = trim( (string) $comment->comment_author_IP );
        ?>
        <table class="form-table" role="presentation" style="margin-top: 0;">
            <tr>
                <th scope="row" style="width: 140px;"><?php esc_html_e( '终端与系统', 'qiling' ); ?></th>
                <td><strong><?php echo esc_html( $parsed['full_label'] ); ?></strong></td>
            </tr>
            <?php if ( $location ) : ?>
            <tr>
                <th scope="row"><?php esc_html_e( 'IP 归属地', 'qiling' ); ?></th>
                <td><?php echo esc_html( $location ); ?> <?php if ( $ip ) : ?>(<code><?php echo esc_html( $ip ); ?></code>)<?php endif; ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <th scope="row"><?php esc_html_e( '原始 User-Agent', 'qiling' ); ?></th>
                <td>
                    <textarea class="large-text readonly" readonly rows="2" style="font-family: monospace; font-size: 12px;"><?php echo esc_textarea( $ua ? $ua : __( '（空）', 'qiling' ) ); ?></textarea>
                    <p class="description"><?php esc_html_e( '依据《网络安全法》与《互联网跟帖评论服务管理规定》，该客户端指纹用于审计核查与发帖溯源。', 'qiling' ); ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * 注入后台列表页样式
     *
     * @param string $hook 当前页面钩子。
     * @return void
     */
    public function enqueue_styles( $hook ) {
        if ( 'edit-comments.php' !== $hook ) {
            return;
        }

        wp_add_inline_style(
            'common',
            '
            .column-comment_device {
                width: 17%;
                min-width: 130px;
                vertical-align: top;
            }
            .qiling-comment-device {
                line-height: 1.4;
                cursor: help;
            }
            .qiling-comment-device .dashicons {
                vertical-align: middle;
            }
            '
        );
    }

    /**
     * 智能解析 User-Agent 为人类可读的操作系统与浏览器
     *
     * @param string $ua 原始 User-Agent 字符串。
     * @return array{os: string, browser: string, type: string, full_label: string}
     */
    public static function parse_user_agent( $ua ) {
        $ua = trim( (string) $ua );
        if ( '' === $ua ) {
            return array(
                'os'         => __( '未知系统', 'qiling' ),
                'browser'    => __( '未知客户端', 'qiling' ),
                'type'       => 'unknown',
                'full_label' => __( '未知设备', 'qiling' ),
            );
        }

        $os         = '';
        $browser    = '';
        $type       = 'desktop';

        // 1. 判断是否为爬虫 / 机器人
        if ( preg_match( '/(Googlebot|Baiduspider|Bytespider|bingbot|Sogou|YisouSpider|SemrushBot|AhrefsBot)/i', $ua, $matches ) ) {
            return array(
                'os'         => __( '网络爬虫', 'qiling' ),
                'browser'    => $matches[1],
                'type'       => 'bot',
                'full_label' => sprintf( __( '爬虫机器人 (%s)', 'qiling' ), $matches[1] ),
            );
        }

        // 2. 判断微信小程序环境
        $is_miniapp = false;
        if ( stripos( $ua, 'miniProgram' ) !== false || stripos( $ua, 'miniprogram' ) !== false ) {
            $is_miniapp = true;
        }

        // 3. 操作系统解析
        if ( preg_match( '/Windows NT 10\.0/i', $ua ) ) {
            $os = 'Windows 10/11';
        } elseif ( preg_match( '/Windows NT 6\.3/i', $ua ) ) {
            $os = 'Windows 8.1';
        } elseif ( preg_match( '/Windows NT 6\.2/i', $ua ) ) {
            $os = 'Windows 8';
        } elseif ( preg_match( '/Windows NT 6\.1/i', $ua ) ) {
            $os = 'Windows 7';
        } elseif ( preg_match( '/Windows NT 5\.1/i', $ua ) ) {
            $os = 'Windows XP';
        } elseif ( preg_match( '/Windows/i', $ua ) ) {
            $os = 'Windows';
        } elseif ( preg_match( '/iPhone/i', $ua ) ) {
            $os   = 'iOS (iPhone)';
            $type = 'mobile';
            if ( preg_match( '/OS (\d+[_\.]\d+)/i', $ua, $m ) ) {
                $os = 'iPhone (iOS ' . str_replace( '_', '.', $m[1] ) . ')';
            }
        } elseif ( preg_match( '/iPad/i', $ua ) ) {
            $os   = 'iPadOS';
            $type = 'tablet';
            if ( preg_match( '/OS (\d+[_\.]\d+)/i', $ua, $m ) ) {
                $os = 'iPad (iPadOS ' . str_replace( '_', '.', $m[1] ) . ')';
            }
        } elseif ( preg_match( '/Mac OS X/i', $ua ) || preg_match( '/Macintosh/i', $ua ) ) {
            $os = 'macOS';
            if ( preg_match( '/Mac OS X (\d+[_\.]\d+)/i', $ua, $m ) ) {
                $os = 'macOS ' . str_replace( '_', '.', $m[1] );
            }
        } elseif ( preg_match( '/HarmonyOS/i', $ua ) ) {
            $os   = 'HarmonyOS (鸿蒙)';
            $type = 'mobile';
        } elseif ( preg_match( '/Android/i', $ua ) ) {
            $os   = 'Android';
            $type = 'mobile';
            if ( preg_match( '/Android (\d+(\.\d+)?)/i', $ua, $m ) ) {
                $os = 'Android ' . $m[1];
            }
            if ( preg_match( '/(HUAWEI|HONOR|Redmi|MI |vivo|OPPO|SAMSUNG|SM-)/i', $ua, $brand_match ) ) {
                $brand       = strtoupper( trim( $brand_match[1] ) );
                $brand_names = array(
                    'HUAWEI'  => '华为',
                    'HONOR'   => '荣耀',
                    'REDMI'   => '红米',
                    'MI '     => '小米',
                    'VIVO'    => 'vivo',
                    'OPPO'    => 'OPPO',
                    'SAMSUNG' => '三星',
                    'SM-'     => '三星',
                );
                if ( isset( $brand_names[ $brand ] ) ) {
                    $os .= ' · ' . $brand_names[ $brand ];
                }
            }
        } elseif ( preg_match( '/Ubuntu/i', $ua ) ) {
            $os = 'Ubuntu Linux';
        } elseif ( preg_match( '/Linux/i', $ua ) ) {
            $os = 'Linux';
        } else {
            $os = __( '其它设备', 'qiling' );
        }

        // 4. 浏览器 / 客户端解析
        if ( $is_miniapp ) {
            $browser = __( '微信小程序', 'qiling' );
            $type    = 'mobile';
        } elseif ( stripos( $ua, 'MicroMessenger' ) !== false ) {
            $browser = __( '微信内置浏览器', 'qiling' );
            $type    = ( 'desktop' === $type && stripos( $ua, 'WindowsWechat' ) === false ) ? 'mobile' : $type;
        } elseif ( stripos( $ua, 'AlipayClient' ) !== false ) {
            $browser = __( '支付宝客户端', 'qiling' );
            $type    = 'mobile';
        } elseif ( stripos( $ua, 'DingTalk' ) !== false ) {
            $browser = __( '钉钉', 'qiling' );
        } elseif ( stripos( $ua, 'baiduboxapp' ) !== false ) {
            $browser = __( '百度APP', 'qiling' );
            $type    = 'mobile';
        } elseif ( stripos( $ua, 'Quark' ) !== false ) {
            $browser = __( '夸克浏览器', 'qiling' );
            $type    = 'mobile';
        } elseif ( stripos( $ua, 'UCBrowser' ) !== false || stripos( $ua, 'UBrowser' ) !== false ) {
            $browser = __( 'UC浏览器', 'qiling' );
            $type    = 'mobile';
        } elseif ( stripos( $ua, 'MQQBrowser' ) !== false || stripos( $ua, 'QQBrowser' ) !== false ) {
            $browser = __( 'QQ浏览器', 'qiling' );
        } elseif ( preg_match( '/Edg(?:e|A|iOS)?\/(\d+)/i', $ua, $m ) ) {
            $browser = 'Edge ' . $m[1];
        } elseif ( preg_match( '/(Chrome|CriOS)\/(\d+)/i', $ua, $m ) ) {
            $browser = 'Chrome ' . $m[2];
        } elseif ( preg_match( '/(Firefox|FxiOS)\/(\d+)/i', $ua, $m ) ) {
            $browser = 'Firefox ' . $m[2];
        } elseif ( preg_match( '/Version\/(\d+(\.\d+)?).*Safari/i', $ua, $m ) ) {
            $browser = 'Safari ' . $m[1];
        } elseif ( preg_match( '/OPR\/(\d+)/i', $ua, $m ) || preg_match( '/Opera\/(\d+)/i', $ua, $m ) ) {
            $browser = 'Opera ' . $m[1];
        } else {
            $browser = __( '标准浏览器', 'qiling' );
        }

        $full_label = trim( $os . ' · ' . $browser );

        return array(
            'os'         => $os,
            'browser'    => $browser,
            'type'       => $type,
            'full_label' => $full_label,
        );
    }
}

// 自动初始化
if ( is_admin() ) {
    new Comment_Columns();
}
