<?php
/**
 * China Features Class - 右侧浮动栏
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\China;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class China_Features {

    /**
     * 从百度统计配置中提取统计 ID
     *
     * @param string $value 原始配置值.
     * @return string
     */
    private function extract_baidu_analytics_id( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '#hm\.js\?([a-zA-Z0-9]+)#i', $value, $matches ) ) {
            return (string) $matches[1];
        }

        if ( preg_match( '/^[a-zA-Z0-9]+$/', $value ) ) {
            return $value;
        }

        return '';
    }

    public function __construct() {
        add_action( 'wp_footer', array( $this, 'render_float_widgets' ), 10 );
        add_action( 'wp_head', array( $this, 'render_baidu_analytics' ), 999 );
    }

    public function render_float_widgets() {
        // 未开启浮动栏时不输出
        if ( ! developer_starter_get_option( 'float_widget_enable', '1' ) ) {
            return;
        }
        $phone = developer_starter_get_option( 'float_phone', '' );
        $qq = developer_starter_get_option( 'float_qq', '' );
        $wechat_qrcode = developer_starter_get_option( 'float_wechat_qrcode', '' );
        // 修改：使用 repeater 字段读取多个自定义项目
        $custom_items = developer_starter_get_option( 'float_custom_items', array() );

        ?>
        <div class="float-widgets">
            <?php if ( ! empty( $phone ) ) : ?>
                <div class="float-widget widget-phone">
                    <span class="widget-icon" title="<?php esc_attr_e( '电话咨询', 'developer-starter' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72c.127.96.362 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
                    </span>
                    <div class="widget-popup">
                        <span class="popup-label"><?php esc_html_e( '联系电话', 'developer-starter' ); ?></span>
                        <span class="popup-content" style="user-select: all; cursor: text;"><?php echo esc_html( $phone ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $wechat_qrcode ) ) : ?>
                <div class="float-widget widget-wechat">
                    <span class="widget-icon" title="<?php esc_attr_e( '微信', 'developer-starter' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 01.213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 00.167-.054l1.903-1.114a.864.864 0 01.717-.098 10.16 10.16 0 002.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178A1.17 1.17 0 014.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18 0 .65-.52 1.178-1.162 1.178a1.17 1.17 0 01-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 01.598.082l1.584.926a.272.272 0 00.14.045c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 01-.023-.156.49.49 0 01.201-.398C23.024 18.48 24 16.82 24 14.98c0-3.21-2.931-5.837-7.062-6.122zm-2.036 2.83c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.97-.982z"/></svg>
                    </span>
                    <div class="widget-popup widget-popup-qr">
                        <img src="<?php echo esc_url( $wechat_qrcode ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" />
                        <span class="popup-label"><?php esc_html_e( '扫码添加微信', 'developer-starter' ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ( ! empty( $qq ) ) : ?>
                <div class="float-widget widget-qq">
                    <a href="http://wpa.qq.com/msgrd?v=3&uin=<?php echo esc_attr( $qq ); ?>&site=qq&menu=yes" target="_blank" class="widget-icon" title="<?php esc_attr_e( 'QQ咨询', 'developer-starter' ); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12.003 2c-2.265 0-6.29 1.364-6.29 7.325v1.195S3.55 14.96 3.55 17.474c0 .665.17 1.025.281 1.025.114 0 .902-.484 1.748-2.072 0 0-.18 2.197 1.904 3.967 0 0-1.77.495-1.77 1.182 0 .686 4.078.43 6.29 0 2.239.425 6.287.687 6.287 0 0-.688-1.768-1.182-1.768-1.182 2.085-1.77 1.905-3.967 1.905-3.967.845 1.588 1.634 2.072 1.746 2.072.111 0 .283-.36.283-1.025 0-2.514-2.166-6.954-2.166-6.954V9.325C18.29 3.364 14.268 2 12.003 2z"/></svg>
                    </a>
                    <div class="widget-popup">
                        <span class="popup-label"><?php esc_html_e( 'QQ在线咨询', 'developer-starter' ); ?></span>
                        <span class="popup-content"><?php echo esc_html( $qq ); ?></span>
                    </div>
                </div>
            <?php endif; ?>

            <?php 
            // 修复：循环渲染所有自定义项目
            if ( ! empty( $custom_items ) && is_array( $custom_items ) ) :
                foreach ( $custom_items as $item ) :
                    $item_title = isset( $item['title'] ) ? $item['title'] : '';
                    $item_url = isset( $item['url'] ) ? $item['url'] : '';
                    $item_icon = isset( $item['icon'] ) ? $item['icon'] : '';
                    $item_color = isset( $item['color'] ) ? $item['color'] : '';
                    
                    if ( empty( $item_title ) ) continue;
                    
                    $custom_style = ! empty( $item_color ) ? 'background: ' . esc_attr( $item_color ) . ';' : '';
            ?>
                <div class="float-widget widget-custom" style="<?php echo $custom_style; ?>">
                    <?php if ( ! empty( $item_url ) ) : ?>
                        <a href="<?php echo esc_url( $item_url ); ?>" class="widget-icon" title="<?php echo esc_attr( $item_title ); ?>" target="_blank">
                            <?php if ( ! empty( $item_icon ) ) : ?>
                                <?php echo developer_starter_get_icon_html( $item_icon ); ?>
                            <?php else : ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            <?php endif; ?>
                        </a>
                    <?php else : ?>
                        <span class="widget-icon">
                            <?php if ( ! empty( $item_icon ) ) : ?>
                                <?php echo developer_starter_get_icon_html( $item_icon ); ?>
                            <?php else : ?>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
                    <div class="widget-popup">
                        <span class="popup-content"><?php echo esc_html( $item_title ); ?></span>
                    </div>
                </div>
            <?php 
                endforeach;
            endif; 
            ?>

            <div class="float-widget widget-totop" id="back-to-top" title="<?php esc_attr_e( '返回顶部', 'developer-starter' ); ?>">
                <span class="widget-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
                </span>
            </div>
        </div>
        <?php
    }

    public function render_baidu_analytics() {
        $baidu_analytics = developer_starter_get_option( 'baidu_analytics', '' );
        if ( empty( $baidu_analytics ) ) {
            return;
        }

        $analytics_id = $this->extract_baidu_analytics_id( $baidu_analytics );
        if ( '' !== $analytics_id ) {
            $script_src = 'https://hm.baidu.com/hm.js?' . rawurlencode( $analytics_id );

            if ( function_exists( 'wp_get_inline_script_tag' ) ) {
                echo wp_get_inline_script_tag( 'window._hmt = window._hmt || [];', array( 'id' => 'qiling-baidu-analytics-init' ) );
            } else {
                echo '<script id="qiling-baidu-analytics-init">window._hmt = window._hmt || [];</script>';
            }

            if ( function_exists( 'wp_get_script_tag' ) ) {
                echo wp_get_script_tag(
                    array(
                        'id'    => 'qiling-baidu-analytics',
                        'src'   => esc_url( $script_src ),
                        'async' => true,
                    )
                );
            } else {
                echo '<script id="qiling-baidu-analytics" async src="' . esc_url( $script_src ) . '"></script>';
            }

            return;
        }

        echo $baidu_analytics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Admin-provided analytics snippet from theme settings.
    }

    /**
     * 渲染页脚备案信息（ICP + 公安备案）
     */
    public static function render_footer_filings() {
        $icp = developer_starter_get_option( 'icp_number', '' );
        $police = developer_starter_get_option( 'police_number', '' );
        $police_icon = developer_starter_get_option( 'police_icon', '' );
        
        if ( empty( $icp ) && empty( $police ) ) {
            return;
        }
        
        echo '<div class="footer-filing footer-filing--items">';
        
        // ICP 备案
        if ( $icp ) {
            echo '<a class="footer-filing-link" href="https://beian.miit.gov.cn/" target="_blank" rel="external nofollow noopener noreferrer">' . esc_html( $icp ) . '</a>';
        }
        
        // 公安备案
        if ( $police ) {
            // 自动识别公安备案号中的数字
            preg_match( '/(\d+)/', $police, $matches );
            $police_record_code = ! empty( $matches[1] ) ? $matches[1] : '';
            $police_url = $police_record_code ? 'http://www.beian.gov.cn/portal/registerSystemInfo?recordcode=' . $police_record_code : '#';
            
            echo '<a class="footer-filing-link footer-filing-link--police" href="' . esc_url( $police_url ) . '" target="_blank" rel="external nofollow noopener noreferrer">';
            if ( $police_icon ) {
                echo '<img class="footer-filing-icon" src="' . esc_url( $police_icon ) . '" alt="" />';
            }
            echo esc_html( $police );
            echo '</a>';
        }
        
        echo '</div>';
    }
}
