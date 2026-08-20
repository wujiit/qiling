<?php
/**
 * Icon Helper Functions
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_get_svg_allowed_schema' ) ) {
    /**
     * Get the strict SVG element and attribute allowlist.
     *
     * @return array<string,array<int,string>>
     */
    function developer_starter_get_svg_allowed_schema() {
        $paint_attrs = array(
            'fill',
            'fill-opacity',
            'fill-rule',
            'stroke',
            'stroke-dasharray',
            'stroke-dashoffset',
            'stroke-linecap',
            'stroke-linejoin',
            'stroke-miterlimit',
            'stroke-opacity',
            'stroke-width',
            'clip-path',
            'clip-rule',
            'color',
            'opacity',
        );

        $position_attrs = array(
            'cx',
            'cy',
            'd',
            'height',
            'points',
            'preserveAspectRatio',
            'r',
            'rx',
            'ry',
            'transform',
            'viewBox',
            'width',
            'x',
            'x1',
            'x2',
            'y',
            'y1',
            'y2',
        );

        $global_attrs = array(
            'aria-hidden',
            'aria-label',
            'aria-labelledby',
            'class',
            'focusable',
            'id',
            'role',
        );

        return array(
            '*'              => $global_attrs,
            'svg'            => array_merge(
                $paint_attrs,
                $position_attrs,
                array( 'xmlns', 'xmlns:xlink', 'version' )
            ),
            'g'              => array_merge( $paint_attrs, array( 'transform' ) ),
            'path'           => array_merge( $paint_attrs, array( 'd', 'transform' ) ),
            'circle'         => array_merge( $paint_attrs, array( 'cx', 'cy', 'r', 'transform' ) ),
            'rect'           => array_merge( $paint_attrs, array( 'height', 'rx', 'ry', 'transform', 'width', 'x', 'y' ) ),
            'line'           => array_merge( $paint_attrs, array( 'x1', 'x2', 'y1', 'y2', 'transform' ) ),
            'polyline'       => array_merge( $paint_attrs, array( 'points', 'transform' ) ),
            'polygon'        => array_merge( $paint_attrs, array( 'points', 'transform' ) ),
            'ellipse'        => array_merge( $paint_attrs, array( 'cx', 'cy', 'rx', 'ry', 'transform' ) ),
            'defs'           => array(),
            'clipPath'       => array( 'clipPathUnits', 'id', 'transform' ),
            'mask'           => array_merge( $paint_attrs, array( 'height', 'id', 'maskContentUnits', 'maskUnits', 'width', 'x', 'y' ) ),
            'symbol'         => array_merge( $paint_attrs, array( 'id', 'preserveAspectRatio', 'viewBox' ) ),
            'use'            => array_merge( $paint_attrs, array( 'height', 'href', 'transform', 'width', 'x', 'xlink:href', 'y' ) ),
            'title'          => array( 'id' ),
            'desc'           => array( 'id' ),
            'linearGradient' => array( 'gradientTransform', 'gradientUnits', 'id', 'spreadMethod', 'x1', 'x2', 'y1', 'y2' ),
            'radialGradient' => array( 'cx', 'cy', 'fx', 'fy', 'gradientTransform', 'gradientUnits', 'id', 'r', 'spreadMethod' ),
            'stop'           => array( 'offset', 'stop-color', 'stop-opacity' ),
        );
    }
}

if ( ! function_exists( 'developer_starter_normalize_svg_name' ) ) {
    /**
     * Normalize SVG tag and attribute names while preserving SVG camelCase names.
     *
     * @param string $name Raw XML name.
     * @return string
     */
    function developer_starter_normalize_svg_name( $name ) {
        static $names = array(
            'clippath'          => 'clipPath',
            'clippathunits'     => 'clipPathUnits',
            'gradienttransform' => 'gradientTransform',
            'gradientunits'     => 'gradientUnits',
            'lineargradient'    => 'linearGradient',
            'maskcontentunits'  => 'maskContentUnits',
            'maskunits'         => 'maskUnits',
            'preserveaspectratio' => 'preserveAspectRatio',
            'radialgradient'    => 'radialGradient',
            'spreadmethod'      => 'spreadMethod',
            'viewbox'           => 'viewBox',
        );

        $name  = trim( (string) $name );
        $lower = strtolower( $name );

        return isset( $names[ $lower ] ) ? $names[ $lower ] : $lower;
    }
}

if ( ! function_exists( 'developer_starter_get_allowed_svg_attributes_for_tag' ) ) {
    /**
     * Get allowed attributes for a sanitized SVG tag.
     *
     * @param string $tag SVG tag name.
     * @return array<int,string>
     */
    function developer_starter_get_allowed_svg_attributes_for_tag( $tag ) {
        $schema = developer_starter_get_svg_allowed_schema();
        $attrs  = isset( $schema['*'] ) ? $schema['*'] : array();

        if ( isset( $schema[ $tag ] ) ) {
            $attrs = array_merge( $attrs, $schema[ $tag ] );
        }

        return array_values( array_unique( array_map( 'developer_starter_normalize_svg_name', $attrs ) ) );
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_id_list' ) ) {
    /**
     * Sanitize id/class-like SVG attribute values.
     *
     * @param string $value Raw value.
     * @param bool   $allow_spaces Whether spaces are allowed.
     * @return string
     */
    function developer_starter_sanitize_svg_id_list( $value, $allow_spaces ) {
        $pattern = $allow_spaces ? '/[^A-Za-z0-9_:\.\-\s]/' : '/[^A-Za-z0-9_:\.\-]/';
        $value   = trim( (string) preg_replace( $pattern, '', (string) $value ) );

        return $allow_spaces ? preg_replace( '/\s+/', ' ', $value ) : $value;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_attribute_value' ) ) {
    /**
     * Validate and normalize a single SVG attribute value.
     *
     * @param string $name  Attribute name.
     * @param string $value Attribute value.
     * @return string|false
     */
    function developer_starter_sanitize_svg_attribute_value( $name, $value ) {
        $name  = developer_starter_normalize_svg_name( $name );
        $value = trim( (string) $value );

        if ( '' === $value ) {
            return '';
        }

        if ( preg_match( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', $value ) ) {
            return false;
        }

        $decoded = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        if ( false !== strpos( $decoded, '<' ) || false !== strpos( $decoded, '>' ) ) {
            return false;
        }

        $compact = strtolower( (string) preg_replace( '/[\s\x00-\x1F]+/', '', $decoded ) );
        if (
            false !== strpos( $compact, 'javascript:' ) ||
            false !== strpos( $compact, 'vbscript:' ) ||
            false !== strpos( $compact, 'data:' )
        ) {
            return false;
        }

        if ( 'style' === $name || 0 === strpos( $name, 'on' ) ) {
            return false;
        }

        if ( in_array( $name, array( 'href', 'xlink:href' ), true ) ) {
            return preg_match( '/^#[A-Za-z][A-Za-z0-9_:\.\-]*$/', $value ) ? $value : false;
        }

        if ( preg_match( '/url\s*\(/i', $decoded ) && ! preg_match( '/^url\s*\(\s*[\'"]?#[A-Za-z][A-Za-z0-9_:\.\-]*[\'"]?\s*\)$/i', $decoded ) ) {
            return false;
        }

        if ( 'id' === $name ) {
            return developer_starter_sanitize_svg_id_list( $value, false );
        }

        if ( 'class' === $name || 'aria-labelledby' === $name ) {
            return developer_starter_sanitize_svg_id_list( $value, true );
        }

        return $value;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_dom_element' ) ) {
    /**
     * Recursively sanitize an SVG DOM element.
     *
     * @param DOMElement $element DOM element.
     * @return void
     */
    function developer_starter_sanitize_svg_dom_element( $element ) {
        $tag           = developer_starter_normalize_svg_name( $element->localName ? $element->localName : $element->nodeName );
        $allowed_attrs = developer_starter_get_allowed_svg_attributes_for_tag( $tag );
        $remove_attrs  = array();
        $set_attrs     = array();

        if ( $element->hasAttributes() ) {
            foreach ( $element->attributes as $attribute ) {
                $attr_name = developer_starter_normalize_svg_name( $attribute->nodeName );
                if ( ! in_array( $attr_name, $allowed_attrs, true ) ) {
                    $remove_attrs[] = $attribute->nodeName;
                    continue;
                }

                $attr_value = developer_starter_sanitize_svg_attribute_value( $attr_name, $attribute->nodeValue );
                if ( false === $attr_value || '' === $attr_value ) {
                    $remove_attrs[] = $attribute->nodeName;
                    continue;
                }

                if ( (string) $attribute->nodeValue !== (string) $attr_value ) {
                    $set_attrs[ $attribute->nodeName ] = $attr_value;
                }
            }
        }

        foreach ( $remove_attrs as $attr_name ) {
            $element->removeAttribute( $attr_name );
        }

        foreach ( $set_attrs as $attr_name => $attr_value ) {
            $element->setAttribute( $attr_name, $attr_value );
        }

        $schema = developer_starter_get_svg_allowed_schema();
        $child  = $element->firstChild;
        while ( $child ) {
            $next = $child->nextSibling;

            if ( 1 === (int) $child->nodeType ) {
                $child_tag = developer_starter_normalize_svg_name( $child->localName ? $child->localName : $child->nodeName );
                if ( ! isset( $schema[ $child_tag ] ) ) {
                    $element->removeChild( $child );
                } else {
                    developer_starter_sanitize_svg_dom_element( $child );
                }
            } elseif ( 3 !== (int) $child->nodeType ) {
                $element->removeChild( $child );
            }

            $child = $next;
        }
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_with_dom' ) ) {
    /**
     * Sanitize SVG using DOMDocument when available.
     *
     * @param string $svg Raw SVG code.
     * @return string
     */
    function developer_starter_sanitize_svg_with_dom( $svg ) {
        if ( ! class_exists( 'DOMDocument' ) ) {
            return '';
        }

        $previous_errors = libxml_use_internal_errors( true );
        $previous_loader = null;
        if ( defined( 'PHP_VERSION_ID' ) && PHP_VERSION_ID < 80000 && function_exists( 'libxml_disable_entity_loader' ) ) {
            $previous_loader = libxml_disable_entity_loader( true );
        }

        $dom    = new DOMDocument( '1.0', 'UTF-8' );
        $loaded = $dom->loadXML( $svg, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT );

        if ( null !== $previous_loader ) {
            libxml_disable_entity_loader( $previous_loader );
        }
        libxml_clear_errors();
        libxml_use_internal_errors( $previous_errors );

        if ( ! $loaded || ! $dom->documentElement ) {
            return '';
        }

        $root = $dom->documentElement;
        if ( 'svg' !== developer_starter_normalize_svg_name( $root->localName ? $root->localName : $root->nodeName ) ) {
            return '';
        }

        developer_starter_sanitize_svg_dom_element( $root );

        $output = $dom->saveXML( $root );
        return is_string( $output ) ? trim( $output ) : '';
    }
}

if ( ! function_exists( 'developer_starter_get_svg_kses_allowed_tags' ) ) {
    /**
     * Convert the SVG schema to a KSES allowlist.
     *
     * @return array<string,array<string,bool>>
     */
    function developer_starter_get_svg_kses_allowed_tags() {
        $schema = developer_starter_get_svg_allowed_schema();
        $global = isset( $schema['*'] ) ? $schema['*'] : array();
        $tags   = array();

        foreach ( $schema as $tag => $attrs ) {
            if ( '*' === $tag ) {
                continue;
            }

            $tag_attrs = array_merge( $global, $attrs );
            $allowed   = array();
            foreach ( $tag_attrs as $attr ) {
                $allowed[ strtolower( $attr ) ] = true;
            }

            $tags[ strtolower( $tag ) ] = $allowed;
        }

        return $tags;
    }
}

if ( ! function_exists( 'developer_starter_sanitize_svg_with_kses' ) ) {
    /**
     * Strict KSES fallback for environments without DOMDocument.
     *
     * @param string $svg Raw SVG code.
     * @return string
     */
    function developer_starter_sanitize_svg_with_kses( $svg ) {
        $svg = preg_replace( '/<\s*(script|style|iframe|object|embed|foreignObject)\b[^>]*>.*?<\s*\/\s*\1\s*>/is', '', $svg );
        $svg = preg_replace( '/\s*on\w+\s*=\s*(["\']).*?\1/i', '', (string) $svg );
        $svg = preg_replace( '/\s*on\w+\s*=\s*[^\s>]*/i', '', (string) $svg );
        $svg = preg_replace( '/\s*style\s*=\s*(["\']).*?\1/i', '', (string) $svg );
        $svg = preg_replace( '/\s*style\s*=\s*[^\s>]*/i', '', (string) $svg );
        $svg = preg_replace( '/\s*(?:xlink:href|href)\s*=\s*(["\'])(?!#[A-Za-z])[^"\']*\1/i', '', (string) $svg );
        $svg = preg_replace( '/\s*(?:xlink:href|href)\s*=\s*(?!#[A-Za-z])[^"\s>]*/i', '', (string) $svg );

        return wp_kses( (string) $svg, developer_starter_get_svg_kses_allowed_tags() );
    }
}

/**
 * Sanitize SVG code to prevent XSS attacks.
 *
 * @param string $svg Raw SVG code.
 * @return string Sanitized SVG code.
 */
function developer_starter_sanitize_svg( $svg ) {
    $svg = trim( (string) $svg );
    if ( '' === $svg || false === stripos( $svg, '<svg' ) ) {
        return '';
    }

    $svg = preg_replace( '/^\xEF\xBB\xBF/', '', $svg );
    $svg = preg_replace( '/<\?xml[^>]*\?>/i', '', (string) $svg );

    if ( preg_match( '/<!\s*(doctype|entity)\b/i', $svg ) ) {
        return '';
    }

    $sanitized = developer_starter_sanitize_svg_with_dom( $svg );
    if ( '' !== $sanitized ) {
        return $sanitized;
    }

    return developer_starter_sanitize_svg_with_kses( $svg );
}

/**
 * Get icon HTML (Emoji, SVG code, or SVG Symbol)
 *
 * @param string $icon Icon class/name, Emoji, or SVG code
 * @param string $class Extra CSS classes
 * @return string HTML output
 */
function developer_starter_get_icon_html( $icon, $class = '' ) {
    if ( empty( $icon ) ) {
        return '';
    }
    
    $icon = trim( $icon );

    // 1. 检测是否是完整的 SVG 代码
    if ( strpos( $icon, '<svg' ) !== false ) {
        // 清理 SVG 代码，防止 XSS
        $safe_svg = developer_starter_sanitize_svg( $icon );
        
        // 如果传入了额外的 class，添加到 SVG
        if ( ! empty( $class ) ) {
            if ( preg_match( '/<svg([^>]*)class\s*=\s*["\']([^"\']*)["\']/', $safe_svg, $matches ) ) {
                // 已有 class，追加
                $safe_svg = preg_replace( 
                    '/(<svg[^>]*)class\s*=\s*["\']([^"\']*)["\']/', 
                    '$1class="$2 ' . esc_attr( $class ) . '"', 
                    $safe_svg 
                );
            } else {
                // 没有 class，添加
                $safe_svg = preg_replace( '/<svg/', '<svg class="' . esc_attr( $class ) . '"', $safe_svg );
            }
        }
        
        return $safe_svg;
    }
    
    // 2. 检测是否是 Emoji
    $is_emoji = preg_match( '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F700}-\x{1F77F}\x{1F780}-\x{1F7FF}\x{1F800}-\x{1F8FF}\x{1F900}-\x{1F9FF}\x{1FA00}-\x{1FA6F}\x{1FA70}-\x{1FAFF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}]/u', $icon );
    
    // 检测是否是纯数字或短文本（步骤编号等）
    $is_text = ( is_numeric( $icon ) || mb_strlen( $icon ) <= 2 ) && strpos( $icon, 'icon-' ) === false && strpos( $icon, 'ri-' ) === false;

    if ( $is_emoji || $is_text ) {
        $span_class = $is_emoji ? 'emoji-icon' : 'text-icon';
        return '<span class="' . esc_attr( $span_class ) . ' ' . esc_attr( $class ) . '">' . esc_html( $icon ) . '</span>';
    }
    
    // 3. 作为 SVG Symbol 类名处理
    $parts = explode( ' ', $icon );
    $icon_name = $parts[0];
    $extra_classes = isset( $parts[1] ) ? implode( ' ', array_slice( $parts, 1 ) ) : '';
    
    $icon_id = $icon_name;
    if ( strpos( $icon_id, '#' ) !== 0 ) {
        $icon_id = '#' . $icon_name;
    }
    
    if ( ! empty( $extra_classes ) ) {
        $class .= ' ' . $extra_classes;
    }
    
    return '<svg class="qs-icon ' . esc_attr( trim( $class ) ) . '" aria-hidden="true"><use xlink:href="' . esc_attr( $icon_id ) . '"></use></svg>';
}
