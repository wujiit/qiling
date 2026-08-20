<?php
/**
 * Data config for page structure field definitions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'card_radius' => array(
                'label'       => __( '卡片圆角', 'developer-starter' ),
                'type'        => 'text',
                'placeholder' => '8px',
            ),
            'button_radius' => array(
                'label'       => __( '按钮圆角', 'developer-starter' ),
                'type'        => 'text',
                'placeholder' => '8px',
            ),
            'input_radius' => array(
                'label'       => __( '输入框圆角', 'developer-starter' ),
                'type'        => 'text',
                'placeholder' => '8px',
            ),
            'animation_speed' => array(
                'label'       => __( '动效速度', 'developer-starter' ),
                'type'        => 'text',
                'placeholder' => '0.25s',
            ),
        );
