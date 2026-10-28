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
                'label' => __( '桌面端', 'qiling' ),
            ),
            'tablet'  => array(
                'label' => __( '平板端', 'qiling' ),
            ),
            'mobile'  => array(
                'label' => __( '手机端', 'qiling' ),
            ),
        );
