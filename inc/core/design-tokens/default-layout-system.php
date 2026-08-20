<?php
/**
 * Data config for default layout system.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'container_width' => array(
                'desktop' => '1200px',
                'tablet'  => '960px',
                'mobile'  => '100%',
            ),
            'section_spacing' => array(
                'desktop' => '50px',
                'tablet'  => '40px',
                'mobile'  => '32px',
            ),
            'grid_gap'        => array(
                'desktop' => '30px',
                'tablet'  => '24px',
                'mobile'  => '18px',
            ),
            'breakpoints'     => array(
                'tablet' => '992px',
                'mobile' => '768px',
            ),
            'layout_mode'     => 'wide',
        );
