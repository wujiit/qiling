<?php
/**
 * Data config for typography property definitions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'font_size'      => array(
                'label'       => __( '字号', 'developer-starter' ),
                'placeholder' => '16px',
            ),
            'line_height'    => array(
                'label'       => __( '行高', 'developer-starter' ),
                'placeholder' => '1.6',
            ),
            'font_weight'    => array(
                'label'       => __( '字重', 'developer-starter' ),
                'placeholder' => '400',
            ),
            'letter_spacing' => array(
                'label'       => __( '字间距', 'developer-starter' ),
                'placeholder' => '0em',
            ),
        );
