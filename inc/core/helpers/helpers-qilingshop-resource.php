<?php
/**
 * QilingShop resource display helpers.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_qilingshop_parse_structured_value' ) ) {
    /**
     * Parse JSON/serialized arrays while leaving scalar values intact.
     *
     * @param mixed $value Raw value.
     * @return mixed
     */
    function developer_starter_qilingshop_parse_structured_value( $value ) {
        if ( is_array( $value ) ) {
            return $value;
        }

        if ( ! is_string( $value ) ) {
            return $value;
        }

        $trimmed = trim( $value );
        if ( '' === $trimmed ) {
            return '';
        }

        $json = json_decode( $trimmed, true );
        if ( is_array( $json ) ) {
            return $json;
        }

        if ( function_exists( 'is_serialized' ) && is_serialized( $trimmed ) ) {
            $maybe = maybe_unserialize( $trimmed );
            if ( is_array( $maybe ) ) {
                return $maybe;
            }
        }

        return $trimmed;
    }
}

if ( ! function_exists( 'developer_starter_qilingshop_value_has_content' ) ) {
    /**
     * Determine whether a mixed resource field contains meaningful content.
     *
     * @param mixed $value Raw value.
     * @return bool
     */
    function developer_starter_qilingshop_value_has_content( $value ) {
        $value = developer_starter_qilingshop_parse_structured_value( $value );

        if ( is_array( $value ) ) {
            foreach ( $value as $item ) {
                if ( developer_starter_qilingshop_value_has_content( $item ) ) {
                    return true;
                }
            }

            return false;
        }

        if ( is_bool( $value ) || null === $value ) {
            return false;
        }

        if ( is_numeric( $value ) ) {
            return true;
        }

        $value = trim( (string) $value );
        if ( '' === $value || '[]' === $value || '{}' === $value ) {
            return false;
        }

        $text_value = trim( wp_strip_all_tags( $value ) );
        if ( '' !== $text_value ) {
            return true;
        }

        return (bool) preg_match( '/\b(?:href|src)\s*=\s*([\'"])(?!\s*\1).+?\1/i', $value );
    }
}

if ( ! function_exists( 'developer_starter_count_qilingshop_download_items' ) ) {
    /**
     * Count meaningful download entries from array/JSON/serialized/plain values.
     *
     * @param mixed $downloads_raw Raw download field value.
     * @return int
     */
    function developer_starter_count_qilingshop_download_items( $downloads_raw ) {
        $downloads = developer_starter_qilingshop_parse_structured_value( $downloads_raw );

        if ( is_array( $downloads ) ) {
            $count = 0;

            foreach ( $downloads as $item ) {
                if ( is_array( $item ) ) {
                    if ( developer_starter_qilingshop_value_has_content( $item ) ) {
                        ++$count;
                    }
                    continue;
                }

                if ( developer_starter_qilingshop_value_has_content( $item ) ) {
                    ++$count;
                }
            }

            return $count;
        }

        if ( ! is_string( $downloads ) ) {
            return developer_starter_qilingshop_value_has_content( $downloads ) ? 1 : 0;
        }

        $downloads = trim( $downloads );
        if ( '' === $downloads || '[]' === $downloads || '{}' === $downloads ) {
            return 0;
        }

        $lines = preg_split( '/\r\n|\r|\n/', $downloads );
        if ( is_array( $lines ) && count( $lines ) > 1 ) {
            return count(
                array_filter(
                    array_map( 'trim', $lines ),
                    static function ( $line ) {
                        return '' !== $line;
                    }
                )
            );
        }

        return 1;
    }
}

if ( ! function_exists( 'developer_starter_get_qilingshop_resource_snapshot' ) ) {
    /**
     * Resolve a strict display-only resource snapshot for a post.
     *
     * @param int $post_id Post ID.
     * @return array<string,mixed>
     */
    function developer_starter_get_qilingshop_resource_snapshot( $post_id ) {
        $post_id = (int) $post_id;
        $empty = array(
            'enabled'          => false,
            'has_resource'     => false,
            'has_downloads'    => false,
            'download_count'   => 0,
            'has_hidden'       => false,
            'has_price'        => false,
            'points_price'     => 0.0,
            'sale_mode'        => '',
            'is_free'          => false,
            'is_paid'          => false,
            'is_vip'           => false,
            'vip_discount'     => '',
            'has_vip_discount' => false,
        );

        if ( $post_id <= 0 ) {
            return $empty;
        }

        $resource_enabled = function_exists( 'qilingshop_points_resource_enabled' )
            ? (bool) qilingshop_points_resource_enabled( $post_id )
            : true;
        if ( ! $resource_enabled ) {
            return $empty;
        }

        $downloads_raw = get_post_meta( $post_id, '_qilingshop_download_urls', true );
        $hidden_raw = get_post_meta( $post_id, '_qilingshop_hidden_content', true );
        $vip_discount = trim( (string) get_post_meta( $post_id, '_qilingshop_vip_discount', true ) );
        $price_meta = get_post_meta( $post_id, '_qilingshop_price', true );
        $sale_mode = trim( (string) get_post_meta( $post_id, '_qilingshop_sale_mode', true ) );

        $download_count = developer_starter_count_qilingshop_download_items( $downloads_raw );
        $has_downloads = $download_count > 0;
        $has_hidden = developer_starter_qilingshop_value_has_content( $hidden_raw );
        $has_price_meta = metadata_exists( 'post', $post_id, '_qilingshop_price' );
        $price_string = is_scalar( $price_meta ) ? trim( (string) $price_meta ) : '';
        $has_price = $has_price_meta && '' !== $price_string && is_numeric( $price_string );
        $points_price = $has_price ? (float) $price_string : 0.0;
        $has_vip_discount = '' !== $vip_discount && ! in_array( $vip_discount, array( 'none', 'default' ), true );

        $api_has_paid = false;
        $api_has_downloads = false;
        $api_has_features = false;
        if ( class_exists( 'QilingShop_Resource' ) && method_exists( 'QilingShop_Resource', 'instance' ) ) {
            $resource = QilingShop_Resource::instance();
            if ( is_object( $resource ) ) {
                $api_has_paid = method_exists( $resource, 'is_paid_resource' ) && (bool) $resource->is_paid_resource( $post_id );
                $api_has_downloads = method_exists( $resource, 'has_download_urls' ) && (bool) $resource->has_download_urls( $post_id );
                $api_has_features = method_exists( $resource, 'has_resource_features' ) && (bool) $resource->has_resource_features( $post_id );

                if ( '' === $sale_mode && method_exists( $resource, 'get_sale_mode' ) ) {
                    $sale_mode = trim( (string) $resource->get_sale_mode( $post_id ) );
                }

                if ( ! $has_price && method_exists( $resource, 'get_points_price' ) && $api_has_paid ) {
                    $points_price = (float) $resource->get_points_price( $post_id );
                    $has_price = true;
                }
            }
        }

        $has_resource_features = $api_has_features && ( $has_downloads || $has_hidden || $has_price || $has_vip_discount );
        $has_resource = $has_downloads || $has_hidden || $has_price || $api_has_paid || $api_has_downloads || $has_resource_features;
        $is_free = $has_resource && ( ( 'free' === $sale_mode && ( $has_downloads || $has_hidden || $api_has_downloads || $api_has_features ) ) || ( $has_price && $points_price <= 0 ) );
        $is_paid = $has_resource && ! $is_free && ( ( $has_price && $points_price > 0 ) || $api_has_paid );
        $is_vip = $has_resource && ! $is_free && $has_vip_discount;

        return array(
            'enabled'          => true,
            'has_resource'     => $has_resource,
            'has_downloads'    => $has_downloads || $api_has_downloads,
            'download_count'   => $download_count,
            'has_hidden'       => $has_hidden,
            'has_price'        => $has_price,
            'points_price'     => $points_price,
            'sale_mode'        => $sale_mode,
            'is_free'          => $is_free,
            'is_paid'          => $is_paid,
            'is_vip'           => $is_vip,
            'vip_discount'     => $vip_discount,
            'has_vip_discount' => $has_vip_discount,
        );
    }
}

if ( ! function_exists( 'developer_starter_get_qilingshop_box_allowed_html' ) ) {
    /**
     * Allowed HTML for QilingShop-rendered action boxes.
     *
     * @return array<string,array<string,bool>>
     */
    function developer_starter_get_qilingshop_box_allowed_html() {
        $allowed = wp_kses_allowed_html( 'post' );
        $global = array(
            'id'          => true,
            'class'       => true,
            'style'       => true,
            'title'       => true,
            'role'        => true,
            'aria-*'      => true,
            'data-*'      => true,
            'hidden'      => true,
            'tabindex'    => true,
            'aria-label'  => true,
            'aria-hidden' => true,
        );

        foreach ( array( 'a', 'div', 'span', 'p', 'section', 'article', 'header', 'footer', 'ul', 'ol', 'li', 'strong', 'em', 'small', 'label', 'img' ) as $tag ) {
            $allowed[ $tag ] = array_merge( isset( $allowed[ $tag ] ) && is_array( $allowed[ $tag ] ) ? $allowed[ $tag ] : array(), $global );
        }

        $allowed['a'] = array_merge(
            $allowed['a'],
            array(
                'href'     => true,
                'target'   => true,
                'rel'      => true,
                'download' => true,
            )
        );
        $allowed['label'] = array_merge(
            $allowed['label'],
            array(
                'for' => true,
            )
        );
        $allowed['form'] = array_merge(
            $global,
            array(
                'action'         => true,
                'method'         => true,
                'name'           => true,
                'target'         => true,
                'autocomplete'   => true,
                'novalidate'     => true,
                'accept-charset' => true,
            )
        );
        $allowed['input'] = array_merge(
            $global,
            array(
                'type'         => true,
                'name'         => true,
                'value'        => true,
                'placeholder'  => true,
                'autocomplete' => true,
                'checked'      => true,
                'disabled'     => true,
                'readonly'     => true,
                'required'     => true,
                'min'          => true,
                'max'          => true,
                'step'         => true,
            )
        );
        $allowed['button'] = array_merge(
            $global,
            array(
                'type'     => true,
                'name'     => true,
                'value'    => true,
                'disabled' => true,
            )
        );
        $allowed['select'] = array_merge(
            $global,
            array(
                'name'     => true,
                'multiple' => true,
                'disabled' => true,
                'required' => true,
            )
        );
        $allowed['option'] = array_merge(
            $global,
            array(
                'value'    => true,
                'selected' => true,
                'disabled' => true,
            )
        );
        $allowed['textarea'] = array_merge(
            $global,
            array(
                'name'        => true,
                'placeholder' => true,
                'rows'        => true,
                'cols'        => true,
                'disabled'    => true,
                'readonly'    => true,
                'required'    => true,
            )
        );
        $allowed['svg'] = array_merge(
            $global,
            array(
                'xmlns'        => true,
                'viewbox'      => true,
                'width'        => true,
                'height'       => true,
                'fill'         => true,
                'stroke'       => true,
                'stroke-width' => true,
                'aria-hidden'  => true,
                'focusable'    => true,
            )
        );
        $allowed['path'] = array_merge(
            $global,
            array(
                'd'               => true,
                'fill'            => true,
                'stroke'          => true,
                'stroke-width'    => true,
                'stroke-linecap'  => true,
                'stroke-linejoin' => true,
            )
        );

        return apply_filters( 'developer_starter_qilingshop_box_allowed_html', $allowed );
    }
}

if ( ! function_exists( 'developer_starter_kses_qilingshop_box' ) ) {
    /**
     * Sanitize QilingShop box markup without stripping expected controls.
     *
     * @param string $html Box HTML.
     * @return string
     */
    function developer_starter_kses_qilingshop_box( $html ) {
        return wp_kses( (string) $html, developer_starter_get_qilingshop_box_allowed_html() );
    }
}
