<?php
/**
 * Changelog template helpers.
 *
 * @package Developer_Starter
 * @since 2.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'developer_starter_resolve_page_template_slug' ) ) {
    /**
     * Resolve a page template slug from the current request or stored post meta.
     *
     * @param int         $post_id  Post ID.
     * @param string|null $template Optional template slug.
     * @return string
     */
    function developer_starter_resolve_page_template_slug( $post_id, $template = null ) {
        $resolved = is_string( $template ) ? $template : '';

        if ( '' === trim( $resolved ) && isset( $_POST['page_template'] ) ) {
            $request_template = wp_unslash( $_POST['page_template'] );
            if ( is_scalar( $request_template ) ) {
                $resolved = (string) $request_template;
            }
        }

        if ( '' === trim( $resolved ) && isset( $_POST['meta_input'] ) && is_array( $_POST['meta_input'] ) && isset( $_POST['meta_input']['_wp_page_template'] ) ) {
            $request_template = wp_unslash( $_POST['meta_input']['_wp_page_template'] );
            if ( is_scalar( $request_template ) ) {
                $resolved = (string) $request_template;
            }
        }

        if ( '' === trim( $resolved ) && function_exists( 'get_page_template_slug' ) ) {
            $resolved = (string) get_page_template_slug( $post_id );
        }

        if ( '' === trim( $resolved ) ) {
            $resolved = (string) get_post_meta( $post_id, '_wp_page_template', true );
        }

        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $resolved = developer_starter_normalize_page_template_slug( $resolved );
        }

        return (string) $resolved;
    }
}

if ( ! function_exists( 'developer_starter_is_changelog_template_page' ) ) {
    /**
     * Whether the post is using the changelog template.
     *
     * @param int         $post_id  Post ID.
     * @param string|null $template Optional template slug.
     * @return bool
     */
    function developer_starter_is_changelog_template_page( $post_id, $template = null ) {
        $post = get_post( $post_id );
        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
            return false;
        }

        return 'templates/template-changelog.php' === developer_starter_resolve_page_template_slug( $post->ID, $template );
    }
}

if ( ! function_exists( 'developer_starter_cleanup_changelog_markup' ) ) {
    /**
     * Remove editor-only artifacts while keeping visible changelog HTML intact.
     *
     * @param string $html Source HTML.
     * @return string
     */
    function developer_starter_cleanup_changelog_markup( $html ) {
        $html = (string) $html;
        if ( '' === trim( $html ) ) {
            return '';
        }

        $html = str_replace( array( "\r\n", "\r" ), "\n", $html );
        $html = preg_replace( '/<!--\s*\/?wp:[\s\S]*?-->/', '', $html );
        $html = preg_replace( '/<br\b[^>]*data-mce-bogus=(["\'])1\1[^>]*>/i', '', $html );
        $html = preg_replace( '/<span\b[^>]*(?:data-mce-type=(["\'])bookmark\1|id=(["\'])_mce_caret\2)[^>]*>(?:.*?)<\/span>/is', '', $html );
        $html = preg_replace( '/\sdata-mce-[a-z0-9_-]+=(["\']).*?\1/i', '', $html );
        $html = preg_replace( '/\sid=(["\'])_mce_caret\1/i', '', $html );
        $html = preg_replace( '/<([a-z0-9]+)\b[^>]*style=(["\']).*?(?:display\s*:\s*none|visibility\s*:\s*hidden).*?\2[^>]*>.*?<\/\1>/is', '', $html );

        return trim( (string) $html );
    }
}

if ( ! function_exists( 'developer_starter_cleanup_changelog_section_html' ) ) {
    /**
     * Remove empty wrappers left behind by editor normalization.
     *
     * @param string $html Section HTML.
     * @return string
     */
    function developer_starter_cleanup_changelog_section_html( $html ) {
        $html = developer_starter_cleanup_changelog_markup( $html );
        if ( '' === $html ) {
            return '';
        }

        $patterns = array(
            '/<div[^>]*class=(["\'])[^"\']*wp-block[^"\']*\1[^>]*>\s*<\/div>/is',
            '/<(p|div|section|article|span)[^>]*>(?:\s|&nbsp;|&#xA0;|<br\s*\/?>)*<\/\1>/is',
        );

        do {
            $previous = $html;
            foreach ( $patterns as $pattern ) {
                $html = preg_replace( $pattern, '', $html );
            }
            $html = trim( (string) $html );
        } while ( $previous !== $html );

        return $html;
    }
}

if ( ! function_exists( 'developer_starter_extract_changelog_date_from_title' ) ) {
    /**
     * Extract the changelog date from a heading title.
     *
     * @param string $title Entry title.
     * @return string
     */
    function developer_starter_extract_changelog_date_from_title( $title ) {
        $title = (string) $title;

        if ( preg_match( '/(\d{4}[-\/\.]\d{1,2}[-\/\.]\d{1,2})/', $title, $matches ) ) {
            return (string) $matches[1];
        }

        return '';
    }
}

if ( ! function_exists( 'developer_starter_get_changelog_dom_load_flags' ) ) {
    /**
     * Build libxml flags used for changelog fragment parsing.
     *
     * @return int
     */
    function developer_starter_get_changelog_dom_load_flags() {
        $flags = 0;

        if ( defined( 'LIBXML_HTML_NOIMPLIED' ) ) {
            $flags |= LIBXML_HTML_NOIMPLIED;
        }

        if ( defined( 'LIBXML_HTML_NODEFDTD' ) ) {
            $flags |= LIBXML_HTML_NODEFDTD;
        }

        return $flags;
    }
}

if ( ! function_exists( 'developer_starter_get_changelog_dom_node_html' ) ) {
    /**
     * Serialize a DOM node back to HTML.
     *
     * @param DOMNode $node DOM node.
     * @return string
     */
    function developer_starter_get_changelog_dom_node_html( $node ) {
        if ( ! $node instanceof \DOMNode || ! $node->ownerDocument instanceof \DOMDocument ) {
            return '';
        }

        $html = $node->ownerDocument->saveHTML( $node );
        return is_string( $html ) ? $html : '';
    }
}

if ( ! function_exists( 'developer_starter_changelog_node_has_headings' ) ) {
    /**
     * Whether a DOM node contains any heading elements.
     *
     * @param DOMNode $node DOM node.
     * @return bool
     */
    function developer_starter_changelog_node_has_headings( $node ) {
        if ( ! $node instanceof \DOMNode || ! $node->hasChildNodes() ) {
            return false;
        }

        foreach ( $node->childNodes as $child ) {
            if ( $child instanceof \DOMElement && preg_match( '/^h[1-6]$/i', $child->tagName ) ) {
                return true;
            }

            if ( developer_starter_changelog_node_has_headings( $child ) ) {
                return true;
            }
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_is_changelog_transparent_wrapper' ) ) {
    /**
     * Whether a tag should be flattened when it only wraps changelog headings/body nodes.
     *
     * @param string $tag_name Element tag name.
     * @return bool
     */
    function developer_starter_is_changelog_transparent_wrapper( $tag_name ) {
        return in_array(
            strtolower( (string) $tag_name ),
            array( 'div', 'section', 'article', 'main', 'aside', 'header', 'footer' ),
            true
        );
    }
}

if ( ! function_exists( 'developer_starter_collect_changelog_dom_chunks' ) ) {
    /**
     * Flatten changelog DOM nodes into headings and body chunks in document order.
     *
     * @param DOMNode                                       $node   Root node.
     * @param array<int,array<string,string>>               $chunks Parsed chunks.
     * @return void
     */
    function developer_starter_collect_changelog_dom_chunks( $node, &$chunks ) {
        if ( ! $node instanceof \DOMNode || ! $node->hasChildNodes() ) {
            return;
        }

        foreach ( $node->childNodes as $child ) {
            if ( XML_COMMENT_NODE === $child->nodeType ) {
                continue;
            }

            if ( XML_TEXT_NODE === $child->nodeType || XML_CDATA_SECTION_NODE === $child->nodeType ) {
                $text = trim( (string) $child->nodeValue );
                if ( '' !== $text ) {
                    $chunks[] = array(
                        'type' => 'html',
                        'html' => esc_html( $text ),
                    );
                }
                continue;
            }

            if ( ! $child instanceof \DOMElement ) {
                $html = developer_starter_get_changelog_dom_node_html( $child );
                if ( '' !== trim( $html ) ) {
                    $chunks[] = array(
                        'type' => 'html',
                        'html' => $html,
                    );
                }
                continue;
            }

            $tag_name = strtolower( $child->tagName );

            if ( preg_match( '/^h[1-6]$/', $tag_name ) ) {
                $title = trim( wp_specialchars_decode( wp_strip_all_tags( developer_starter_get_changelog_dom_node_html( $child ) ), ENT_QUOTES ) );
                if ( '' === $title ) {
                    continue;
                }

                $chunks[] = array(
                    'type'        => 'heading',
                    'title'       => $title,
                    'heading_tag' => $tag_name,
                );
                continue;
            }

            if (
                developer_starter_is_changelog_transparent_wrapper( $tag_name )
                && developer_starter_changelog_node_has_headings( $child )
            ) {
                developer_starter_collect_changelog_dom_chunks( $child, $chunks );
                continue;
            }

            $html = developer_starter_get_changelog_dom_node_html( $child );
            if ( '' !== trim( $html ) ) {
                $chunks[] = array(
                    'type' => 'html',
                    'html' => $html,
                );
            }
        }
    }
}

if ( ! function_exists( 'developer_starter_split_changelog_sections_with_dom' ) ) {
    /**
     * Split changelog content with DOM parsing first so pasted classic-editor HTML can be repaired.
     *
     * @param string $content Raw post content.
     * @return array<int,array<string,mixed>>
     */
    function developer_starter_split_changelog_sections_with_dom( $content ) {
        if ( ! class_exists( '\DOMDocument' ) ) {
            return array();
        }

        $content = (string) $content;
        if ( '' === trim( $content ) ) {
            return array();
        }

        if ( function_exists( 'force_balance_tags' ) ) {
            $content = force_balance_tags( $content );
        }

        $dom  = new \DOMDocument();
        $prev = libxml_use_internal_errors( true );
        $html = sprintf(
            '<?xml encoding="utf-8" ?><body><div data-developer-starter-changelog-root="1">%s</div></body>',
            $content
        );

        $loaded = $dom->loadHTML( $html, developer_starter_get_changelog_dom_load_flags() );
        libxml_clear_errors();
        libxml_use_internal_errors( $prev );

        if ( ! $loaded ) {
            return array();
        }

        $xpath = new \DOMXPath( $dom );
        $nodes = $xpath->query( '//*[@data-developer-starter-changelog-root="1"]' );
        if ( ! $nodes instanceof \DOMNodeList || 0 === $nodes->length ) {
            return array();
        }

        $root = $nodes->item( 0 );
        if ( ! $root instanceof \DOMNode ) {
            return array();
        }

        $chunks = array();
        developer_starter_collect_changelog_dom_chunks( $root, $chunks );

        if ( empty( $chunks ) ) {
            return array();
        }

        $sections = array();
        $current  = null;

        foreach ( $chunks as $chunk ) {
            $type = isset( $chunk['type'] ) ? (string) $chunk['type'] : '';

            if ( 'heading' === $type ) {
                if ( null !== $current && '' !== $current['title'] ) {
                    $current['html'] = developer_starter_cleanup_changelog_section_html( $current['html'] );
                    $sections[]      = $current;
                }

                $title = isset( $chunk['title'] ) ? trim( (string) $chunk['title'] ) : '';
                if ( '' === $title ) {
                    $current = null;
                    continue;
                }

                $heading_tag = isset( $chunk['heading_tag'] ) ? strtolower( (string) $chunk['heading_tag'] ) : 'h2';
                if ( ! preg_match( '/^h[1-6]$/', $heading_tag ) ) {
                    $heading_tag = 'h2';
                }

                $current = array(
                    'title'       => $title,
                    'date'        => developer_starter_extract_changelog_date_from_title( $title ),
                    'html'        => '',
                    'heading_tag' => $heading_tag,
                );
                continue;
            }

            if ( null !== $current ) {
                $html = isset( $chunk['html'] ) ? trim( (string) $chunk['html'] ) : '';
                if ( '' !== $html ) {
                    $current['html'] .= ( '' === $current['html'] ? '' : "\n" ) . $html;
                }
            }
        }

        if ( null !== $current && '' !== $current['title'] ) {
            $current['html'] = developer_starter_cleanup_changelog_section_html( $current['html'] );
            $sections[]      = $current;
        }

        return $sections;
    }
}

if ( ! function_exists( 'developer_starter_split_changelog_sections_with_regex' ) ) {
    /**
     * Legacy regex splitter kept as a fallback when DOM parsing is unavailable.
     *
     * @param string $content Raw post content.
     * @return array<int,array<string,mixed>>
     */
    function developer_starter_split_changelog_sections_with_regex( $content ) {
        $pattern = '/(<h[1-6][^>]*>.*?<\/h[1-6]>)/is';
        $parts   = preg_split( $pattern, $content, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );

        if ( empty( $parts ) ) {
            return array();
        }

        $sections = array();
        $current  = null;

        foreach ( $parts as $part ) {
            $part = trim( (string) $part );
            if ( '' === $part ) {
                continue;
            }

            if ( preg_match( '/^<h([1-6])[^>]*>(.*?)<\/h\1>$/is', $part, $matches ) ) {
                if ( null !== $current && '' !== $current['title'] ) {
                    $current['html'] = developer_starter_cleanup_changelog_section_html( $current['html'] );
                    $sections[]      = $current;
                }

                $title = trim( wp_specialchars_decode( wp_strip_all_tags( $matches[2] ), ENT_QUOTES ) );
                if ( '' === $title ) {
                    $current = null;
                    continue;
                }

                $current = array(
                    'title'       => $title,
                    'date'        => developer_starter_extract_changelog_date_from_title( $title ),
                    'html'        => '',
                    'heading_tag' => 'h' . $matches[1],
                );
            } elseif ( null !== $current ) {
                $current['html'] .= ( '' === $current['html'] ? '' : "\n" ) . $part;
            }
        }

        if ( null !== $current && '' !== $current['title'] ) {
            $current['html'] = developer_starter_cleanup_changelog_section_html( $current['html'] );
            $sections[]      = $current;
        }

        return $sections;
    }
}

if ( ! function_exists( 'developer_starter_get_changelog_sections_score' ) ) {
    /**
     * Score parsed sections so we can prefer the more complete changelog split result.
     *
     * @param array<int,array<string,mixed>> $sections Parsed sections.
     * @return int
     */
    function developer_starter_get_changelog_sections_score( $sections ) {
        $score = 0;

        foreach ( $sections as $section ) {
            $title = isset( $section['title'] ) ? trim( (string) $section['title'] ) : '';
            $html  = isset( $section['html'] ) ? (string) $section['html'] : '';

            if ( '' !== $title ) {
                $score += 1000;
            }

            $score += strlen( trim( wp_strip_all_tags( $html ) ) );
        }

        return $score;
    }
}

if ( ! function_exists( 'developer_starter_split_changelog_sections' ) ) {
    /**
     * Split changelog content into heading-based sections.
     *
     * @param string $content Raw post content.
     * @return array<int,array<string,mixed>>
     */
    function developer_starter_split_changelog_sections( $content ) {
        $content = developer_starter_cleanup_changelog_markup( $content );
        if ( '' === trim( $content ) ) {
            return array();
        }

        $dom_sections   = developer_starter_split_changelog_sections_with_dom( $content );
        $regex_sections = developer_starter_split_changelog_sections_with_regex( $content );

        if ( empty( $dom_sections ) ) {
            return $regex_sections;
        }

        if ( empty( $regex_sections ) ) {
            return $dom_sections;
        }

        if ( developer_starter_get_changelog_sections_score( $dom_sections ) >= developer_starter_get_changelog_sections_score( $regex_sections ) ) {
            return $dom_sections;
        }

        return $regex_sections;
    }
}

if ( ! function_exists( 'developer_starter_normalize_changelog_sections' ) ) {
    /**
     * Collapse duplicate changelog versions while preferring the latest edited body.
     *
     * @param array<int,array<string,mixed>> $sections Parsed sections.
     * @return array<int,array<string,mixed>>
     */
    function developer_starter_normalize_changelog_sections( $sections ) {
        if ( empty( $sections ) ) {
            return array();
        }

        $normalized   = array();
        $index_by_key = array();

        foreach ( $sections as $section ) {
            $title = isset( $section['title'] ) ? trim( (string) $section['title'] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $html        = isset( $section['html'] ) ? developer_starter_cleanup_changelog_section_html( $section['html'] ) : '';
            $date        = isset( $section['date'] ) ? (string) $section['date'] : developer_starter_extract_changelog_date_from_title( $title );
            $heading_tag = isset( $section['heading_tag'] ) ? strtolower( (string) $section['heading_tag'] ) : 'h2';

            if ( ! preg_match( '/^h[1-6]$/', $heading_tag ) ) {
                $heading_tag = 'h2';
            }

            $section = array(
                'title'       => $title,
                'date'        => $date,
                'html'        => $html,
                'heading_tag' => $heading_tag,
            );

            $key = strtolower( preg_replace( '/\s+/u', ' ', $title ) );

            if ( isset( $index_by_key[ $key ] ) ) {
                $existing_index = $index_by_key[ $key ];

                if ( '' !== $section['html'] ) {
                    $normalized[ $existing_index ]['html']        = $section['html'];
                    $normalized[ $existing_index ]['heading_tag'] = $section['heading_tag'];
                }

                if ( '' === $normalized[ $existing_index ]['date'] && '' !== $section['date'] ) {
                    $normalized[ $existing_index ]['date'] = $section['date'];
                }

                continue;
            }

            $index_by_key[ $key ] = count( $normalized );
            $normalized[]         = $section;
        }

        foreach ( $normalized as $index => &$section ) {
            $section['is_latest'] = ( 0 === $index );
        }
        unset( $section );

        return $normalized;
    }
}

if ( ! function_exists( 'developer_starter_extract_changelog_downloads' ) ) {
    /**
     * Extract download links from a changelog section.
     *
     * @param string $html Section HTML.
     * @return array<int,array<string,string>>
     */
    function developer_starter_extract_changelog_downloads( $html ) {
        $downloads = array();
        $html      = (string) $html;

        if ( preg_match_all( '/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $text = trim( wp_strip_all_tags( $match[2] ) );
                if ( '' === $text || ! preg_match( '/(?:下载|download)/iu', $text ) ) {
                    continue;
                }

                $downloads[] = array(
                    'url'  => (string) $match[1],
                    'text' => $text,
                );
            }
        }

        return $downloads;
    }
}

if ( ! function_exists( 'developer_starter_remove_changelog_download_markup' ) ) {
    /**
     * Remove standalone download link paragraphs from displayed content.
     *
     * @param string $html Section HTML.
     * @return string
     */
    function developer_starter_remove_changelog_download_markup( $html ) {
        $html = (string) $html;

        $html = preg_replace( '/<p[^>]*>[^<]*(?:下载|Download)[^<]*<a[^>]+>.*?<\/a>[^<]*<\/p>/isu', '', $html );
        $html = preg_replace( '/<p[^>]*>\s*<a[^>]+>.*?(?:下载|Download).*?<\/a>\s*<\/p>/isu', '', $html );

        return developer_starter_cleanup_changelog_section_html( $html );
    }
}

if ( ! function_exists( 'developer_starter_render_changelog_section_html' ) ) {
    /**
     * Apply normal content filters after the section structure has been stabilized.
     *
     * @param string $html Section HTML.
     * @return string
     */
    function developer_starter_render_changelog_section_html( $html ) {
        $html = developer_starter_cleanup_changelog_section_html( $html );
        if ( '' === $html ) {
            return '';
        }

        $html = apply_filters( 'the_content', $html );
        return developer_starter_cleanup_changelog_section_html( $html );
    }
}

if ( ! function_exists( 'developer_starter_parse_changelog' ) ) {
    /**
     * Parse changelog content for front-end rendering.
     *
     * @param string $content Raw post content.
     * @return array<int,array<string,mixed>>
     */
    function developer_starter_parse_changelog( $content ) {
        $sections = developer_starter_normalize_changelog_sections( developer_starter_split_changelog_sections( $content ) );
        $entries  = array();

        foreach ( $sections as $section ) {
            $content_html = isset( $section['html'] ) ? (string) $section['html'] : '';
            $downloads    = developer_starter_extract_changelog_downloads( $content_html );
            $content_html = developer_starter_remove_changelog_download_markup( $content_html );

            $entries[] = array(
                'title'     => isset( $section['title'] ) ? (string) $section['title'] : '',
                'date'      => isset( $section['date'] ) ? (string) $section['date'] : '',
                'content'   => developer_starter_render_changelog_section_html( $content_html ),
                'downloads' => $downloads,
                'is_latest' => ! empty( $section['is_latest'] ),
            );
        }

        return $entries;
    }
}

if ( ! function_exists( 'developer_starter_build_changelog_content' ) ) {
    /**
     * Rebuild normalized changelog HTML for storage.
     *
     * @param array<int,array<string,mixed>> $sections Normalized changelog sections.
     * @return string
     */
    function developer_starter_build_changelog_content( $sections ) {
        if ( empty( $sections ) ) {
            return '';
        }

        $chunks = array();

        foreach ( $sections as $section ) {
            $title       = isset( $section['title'] ) ? trim( (string) $section['title'] ) : '';
            $heading_tag = isset( $section['heading_tag'] ) ? strtolower( (string) $section['heading_tag'] ) : 'h2';
            $html        = isset( $section['html'] ) ? developer_starter_cleanup_changelog_section_html( $section['html'] ) : '';

            if ( '' === $title ) {
                continue;
            }

            if ( ! preg_match( '/^h[1-6]$/', $heading_tag ) ) {
                $heading_tag = 'h2';
            }

            $chunks[] = sprintf(
                '<%1$s>%2$s</%1$s>',
                esc_html( $heading_tag ),
                esc_html( $title )
            );

            if ( '' !== $html ) {
                $chunks[] = $html;
            }
        }

        return trim( implode( "\n\n", $chunks ) );
    }
}

if ( ! function_exists( 'developer_starter_normalize_changelog_source_content' ) ) {
    /**
     * Normalize changelog source content before storing it back to the database.
     *
     * @param string $content Raw post content.
     * @return string
     */
    function developer_starter_normalize_changelog_source_content( $content ) {
        $sections = developer_starter_normalize_changelog_sections( developer_starter_split_changelog_sections( $content ) );

        if ( empty( $sections ) ) {
            return (string) $content;
        }

        return developer_starter_build_changelog_content( $sections );
    }
}

if ( ! function_exists( 'developer_starter_changelog_contains_classic_editor_artifacts' ) ) {
    /**
     * Detect TinyMCE/classic-editor markers that should never be kept in source content.
     *
     * @param string $content Raw post content.
     * @return bool
     */
    function developer_starter_changelog_contains_classic_editor_artifacts( $content ) {
        $content = (string) $content;

        return (bool) preg_match(
            '/data-mce-|_mce_caret|data-mce-bogus|data-mce-type=(["\'])bookmark\1|class=(["\'])[^"\']*mce-item[^"\']*\2/i',
            $content
        );
    }
}

if ( ! function_exists( 'developer_starter_changelog_has_duplicate_sections' ) ) {
    /**
     * Detect duplicate changelog headings, which usually indicates classic-editor residue.
     *
     * @param string $content Raw post content.
     * @return bool
     */
    function developer_starter_changelog_has_duplicate_sections( $content ) {
        $sections = developer_starter_split_changelog_sections( $content );
        if ( count( $sections ) < 2 ) {
            return false;
        }

        $seen_titles = array();

        foreach ( $sections as $section ) {
            $title = isset( $section['title'] ) ? trim( (string) $section['title'] ) : '';
            if ( '' === $title ) {
                continue;
            }

            $key = strtolower( preg_replace( '/\s+/u', ' ', $title ) );
            if ( isset( $seen_titles[ $key ] ) ) {
                return true;
            }

            $seen_titles[ $key ] = true;
        }

        return false;
    }
}

if ( ! function_exists( 'developer_starter_should_normalize_changelog_source_content' ) ) {
    /**
     * Only normalize stored changelog HTML when we detect classic-editor corruption signals.
     *
     * @param string $content Raw post content.
     * @return bool
     */
    function developer_starter_should_normalize_changelog_source_content( $content ) {
        return developer_starter_changelog_contains_classic_editor_artifacts( $content )
            || developer_starter_changelog_has_duplicate_sections( $content );
    }
}

if ( ! function_exists( 'developer_starter_normalize_changelog_compare_string' ) ) {
    /**
     * Normalize strings before comparing stored changelog content.
     *
     * @param string $content Changelog content.
     * @return string
     */
    function developer_starter_normalize_changelog_compare_string( $content ) {
        $content = str_replace( array( "\r\n", "\r" ), "\n", (string) $content );
        $content = preg_replace( "/\n{3,}/", "\n\n", $content );
        return trim( (string) $content );
    }
}

if ( ! function_exists( 'developer_starter_normalize_changelog_page_content_on_save' ) ) {
    /**
     * Stabilize classic-editor changelog HTML after saving the page.
     *
     * @param int      $post_id Post ID.
     * @param WP_Post  $post    Saved post object.
     * @param bool     $update  Whether this is an update.
     * @return void
     */
    function developer_starter_normalize_changelog_page_content_on_save( $post_id, $post, $update ) {
        unset( $update );

        if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! developer_starter_is_changelog_template_page( $post_id ) ) {
            return;
        }

        $raw_content = isset( $post->post_content ) ? (string) $post->post_content : '';
        if ( '' === trim( $raw_content ) ) {
            return;
        }

        if ( ! developer_starter_should_normalize_changelog_source_content( $raw_content ) ) {
            return;
        }

        $normalized_content = developer_starter_normalize_changelog_source_content( $raw_content );

        if ( developer_starter_normalize_changelog_compare_string( $normalized_content ) === developer_starter_normalize_changelog_compare_string( $raw_content ) ) {
            return;
        }

        static $running = array();
        if ( isset( $running[ $post_id ] ) ) {
            return;
        }

        $running[ $post_id ] = true;

        try {
            remove_action( 'save_post_page', 'developer_starter_normalize_changelog_page_content_on_save', 20 );

            wp_update_post(
                array(
                    'ID'           => $post_id,
                    'post_content' => $normalized_content,
                )
            );
        } finally {
            add_action( 'save_post_page', 'developer_starter_normalize_changelog_page_content_on_save', 20, 3 );
            unset( $running[ $post_id ] );
        }
    }
}
add_action( 'save_post_page', 'developer_starter_normalize_changelog_page_content_on_save', 20, 3 );
