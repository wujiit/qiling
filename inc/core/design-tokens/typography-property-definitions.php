<?php
/**
 * 排版属性定义数据配置。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'font_size'      => array(
                'label'       => __( '字号', 'qiling' ),
                'placeholder' => '16px',
            ),
            'line_height'    => array(
                'label'       => __( '行高', 'qiling' ),
                'placeholder' => '1.6',
            ),
            'font_weight'    => array(
                'label'       => __( '字重', 'qiling' ),
                'placeholder' => '400',
            ),
            'letter_spacing' => array(
                'label'       => __( '字间距', 'qiling' ),
                'placeholder' => '0em',
            ),
        );
