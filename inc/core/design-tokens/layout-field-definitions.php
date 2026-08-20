<?php
/**
 * Data config for layout field definitions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'container_width' => array(
                'label'       => __( '容器宽度', 'developer-starter' ),
                'type'        => 'responsive_length',
                'placeholder' => array(
                    'desktop' => '1200px',
                    'tablet'  => '960px',
                    'mobile'  => '100%',
                ),
            ),
            'section_spacing' => array(
                'label'       => __( '区块上下间距', 'developer-starter' ),
                'type'        => 'responsive_length',
                'placeholder' => array(
                    'desktop' => '50px',
                    'tablet'  => '40px',
                    'mobile'  => '32px',
                ),
            ),
            'grid_gap'        => array(
                'label'       => __( '栅格间距', 'developer-starter' ),
                'type'        => 'responsive_length',
                'placeholder' => array(
                    'desktop' => '30px',
                    'tablet'  => '24px',
                    'mobile'  => '18px',
                ),
            ),
            'breakpoints'     => array(
                'label'       => __( '响应断点', 'developer-starter' ),
                'type'        => 'breakpoints',
                'placeholder' => array(
                    'tablet' => '992px',
                    'mobile' => '768px',
                ),
            ),
            'layout_mode'     => array(
                'label'   => __( '布局模式', 'developer-starter' ),
                'type'    => 'choice',
                'choices' => array(
                    'wide'  => __( '宽屏', 'developer-starter' ),
                    'boxed' => __( '盒式', 'developer-starter' ),
                ),
            ),
        );
