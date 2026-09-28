<?php
/**
 * 响应式设备定义数据配置。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'desktop' => array(
                'label' => __( '桌面端', 'developer-starter' ),
            ),
            'tablet'  => array(
                'label' => __( '平板端', 'developer-starter' ),
            ),
            'mobile'  => array(
                'label' => __( '手机端', 'developer-starter' ),
            ),
        );
