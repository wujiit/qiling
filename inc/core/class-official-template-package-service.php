<?php
/**
 * Official Template Package Service
 *
 * 负责读取启灵官方内置行业模板 JSON，并写入页面模块数据。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Official_Template_Package_Service {

	/**
	 * Official package directory under inc/.
	 *
	 * @var string
	 */
	private $package_dir = 'template-center/official';

	/**
	 * Exact templates migrated to official JSON packages.
	 *
	 * @var array<string,string>
	 */
	private $package_map = array(
		'templates/template-home.php'                      => 'home.json',
		'templates/template-solutions.php'                 => 'solutions.json',
		'templates/template-products.php'                  => 'products.json',
		'templates/template-cases.php'                     => 'cases.json',
		'templates/template-blog.php'                      => 'blog.json',
		'templates/template-news.php'                      => 'news.json',
		'templates/template-topic.php'                     => 'topic.json',
		'templates/template-features-showcase.php'         => 'features-showcase.json',
		'templates/template-resources.php'                 => 'resources.json',
		'templates/template-resource-search.php'           => 'resource-search.json',
		'templates/template-landing.php'                   => 'landing.json',
		'templates/template-video-hero.php'                => 'video-hero.json',
		'templates/template-video-portal.php'              => 'video-portal.json',
		'templates/template-interactive-product-launch.php' => 'interactive-product-launch.json',
		'templates/template-ai-product-brand.php'          => 'ai-product-brand.json',
		'templates/template-saas-pricing.php'              => 'saas-pricing.json',
		'templates/template-software-intro.php'            => 'software-intro.json',
		'templates/template-data-showcase.php'             => 'data-showcase.json',
		'templates/template-resume.php'                    => 'resume.json',
		'templates/template-beauty-salon.php'              => 'beauty-salon.json',
		'templates/template-restaurant.php'                => 'restaurant.json',
		'templates/template-dental-clinic.php'             => 'dental-clinic.json',
		'templates/template-law-firm.php'                  => 'law-firm.json',
		'templates/template-renovation-construction.php'   => 'renovation-construction.json',
		'templates/template-wedding-photography.php'       => 'wedding-photography.json',
		'templates/template-gym-fitness.php'               => 'gym-fitness.json',
		'templates/template-yoga-studio.php'               => 'yoga-studio.json',
		'templates/template-pet.php'                       => 'pet.json',
		'templates/template-homestay.php'                  => 'homestay.json',
		'templates/template-travel.php'                    => 'travel.json',
		'templates/template-medical-beauty.php'            => 'medical-beauty.json',
		'templates/template-auto-service.php'              => 'auto-service.json',
		'templates/template-wellness-center.php'           => 'wellness-center.json',
		'templates/template-health-supplements.php'         => 'health-supplements.json',
		'templates/template-intimate-wellness.php'          => 'intimate-wellness.json',
		'templates/template-fashion-brand.php'              => 'fashion-brand.json',
		'templates/template-chain-store-official.php'      => 'chain-store-official.json',
		'templates/template-course-enrollment.php'         => 'course-enrollment.json',
		'templates/template-ecommerce-promo.php'           => 'ecommerce-promo.json',
		'templates/template-app-download-landing.php'      => 'app-download-landing.json',
		'templates/template-personal-ip-home.php'          => 'personal-ip-home.json',
		'templates/template-marketing-pr-agency.php'       => 'marketing-pr-agency.json',
		'templates/template-manufacturing-factory.php'     => 'manufacturing-factory.json',
		'templates/template-foreign-trade-b2b.php'         => 'foreign-trade-b2b.json',
		'templates/template-finance-consulting.php'        => 'finance-consulting.json',
		'templates/template-accounting-tax-service.php'    => 'accounting-tax-service.json',
		'templates/template-intellectual-property-service.php' => 'intellectual-property-service.json',
		'templates/template-study-abroad-immigration.php'  => 'study-abroad-immigration.json',
		'templates/template-early-childhood-education.php' => 'early-childhood-education.json',
		'templates/template-vocational-training-school.php' => 'vocational-training-school.json',
		'templates/template-psychological-counseling.php' => 'psychological-counseling.json',
		'templates/template-senior-care-center.php'       => 'senior-care-center.json',
		'templates/template-postpartum-care-center.php'   => 'postpartum-care-center.json',
		'templates/template-architecture-design-studio.php' => 'architecture-design-studio.json',
		'templates/template-interior-soft-decoration.php' => 'interior-soft-decoration.json',
		'templates/template-landscape-garden-design.php' => 'landscape-garden-design.json',
		'templates/template-appliance-repair-service.php' => 'appliance-repair-service.json',
		'templates/template-franchise-investment.php'    => 'franchise-investment.json',
		'templates/template-mcn-live-commerce.php'       => 'mcn-live-commerce.json',
		'templates/template-conference-event-service.php' => 'conference-event-service.json',
		'templates/template-real-estate-service.php'       => 'real-estate-service.json',
		'templates/template-local-service-official.php'    => 'local-service-official.json',
		'templates/template-qiling-recycling-official.php' => 'qiling-recycling-official.json',
		'templates/template-qiling-housekeeping-official.php' => 'qiling-housekeeping-official.json',
		'templates/template-healthcare-clinic.php'         => 'healthcare-clinic.json',
		'templates/template-logistics-supply-chain.php'    => 'logistics-supply-chain.json',
		'templates/template-recruitment-hr-service.php'    => 'recruitment-hr-service.json',
		'templates/template-nonprofit-organization.php'    => 'nonprofit-organization.json',
		'templates/template-government-public-service.php' => 'government-public-service.json',
		'templates/template-agriculture-food.php'          => 'agriculture-food.json',
		'templates/template-energy-environment.php'        => 'energy-environment.json',
		'templates/template-event-exhibition.php'          => 'event-exhibition.json',
		'templates/template-industrial-park.php'           => 'industrial-park.json',
		'templates/template-property-management.php'       => 'property-management.json',
		'templates/template-semiconductor-electronics.php' => 'semiconductor-electronics.json',
		'templates/template-industrial-automation-robotics.php' => 'industrial-automation-robotics.json',
		'templates/template-medical-device.php'            => 'medical-device.json',
		'templates/template-lab-instrument.php'            => 'lab-instrument.json',
		'templates/template-solar-storage-equipment.php'   => 'solar-storage-equipment.json',
		'templates/template-water-treatment-environmental.php' => 'water-treatment-environmental.json',
		'templates/template-cross-border-ecommerce-service.php' => 'cross-border-ecommerce-service.json',
		'templates/template-overseas-warehouse-supply-chain.php' => 'overseas-warehouse-supply-chain.json',
		'templates/template-enterprise-software-integrator.php' => 'enterprise-software-integrator.json',
		'templates/template-ai-agent-enterprise.php'       => 'ai-agent-enterprise.json',
		'templates/template-ev-charging-station.php'       => 'ev-charging-station.json',
		'templates/template-software-home.php'             => 'software-home.json',
		'templates/template-saas-home.php'                 => 'saas-home.json',
		'templates/template-hosting-saas-home.php'         => 'hosting-saas-home.json',
		'templates/template-developer-platform.php'        => 'developer-platform.json',
		'templates/template-data-intelligence-bi.php'      => 'data-intelligence-bi.json',
		'templates/template-qiling-ai-writing-studio.php'  => 'qiling-ai-writing-studio.json',
		'templates/template-qiling-ai-multilingual-seo.php' => 'qiling-ai-multilingual-seo.json',
		'templates/template-qiling-doc-ocr-converter.php' => 'qiling-doc-ocr-converter.json',
		'templates/template-qiling-image-studio.php'      => 'qiling-image-studio.json',
		'templates/template-qiling-wallpaper-gallery.php' => 'qiling-wallpaper-gallery.json',
		'templates/template-qiling-cloud-storage-hosting.php' => 'qiling-cloud-storage-hosting.json',
		'templates/template-qiling-cloud-canvas.php'      => 'qiling-cloud-canvas.json',
		'templates/template-tech-company-integrated.php'  => 'tech-company-integrated.json',
		'templates/template-qiling-security-ops.php'      => 'qiling-security-ops.json',
		'templates/template-qiling-escrow-platform.php'   => 'qiling-escrow-platform.json',
		'templates/template-qiling-freetask-platform.php' => 'qiling-freetask-platform.json',
		'templates/template-qiling-friends-matchmaking.php' => 'qiling-friends-matchmaking.json',
		'templates/template-qiling-bbs-support-community.php' => 'qiling-bbs-support-community.json',
		'templates/template-open-source-devtools.php'      => 'open-source-devtools.json',
		'templates/template-cybersecurity-brand.php'       => 'cybersecurity-brand.json',
	);

	/**
	 * Get templates backed by official JSON packages.
	 *
	 * @return array<int,string>
	 */
	public function get_supported_templates() {
		return array_keys( $this->package_map );
	}

	/**
	 * Check whether a template has an official JSON package.
	 *
	 * @param mixed $template Template slug.
	 * @return bool
	 */
	public function has_package_for_template( $template ) {
		$template = $this->normalize_template_slug( $template );
		return '' !== $template && isset( $this->package_map[ $template ] ) && is_readable( $this->get_package_path( $template ) );
	}

	/**
	 * Load and parse an official JSON package.
	 *
	 * @param mixed $template Template slug.
	 * @return array<string,mixed>|\WP_Error
	 */
	public function load_package_for_template( $template ) {
		$template = $this->normalize_template_slug( $template );
		if ( '' === $template || ! isset( $this->package_map[ $template ] ) ) {
			return new \WP_Error( 'unsupported_template_package', __( '该模板没有官方 JSON 页面包。', 'developer-starter' ) );
		}

		$path = $this->get_package_path( $template );
		if ( ! is_readable( $path ) ) {
			return new \WP_Error( 'missing_template_package', __( '官方 JSON 页面包文件不存在或不可读。', 'developer-starter' ) );
		}

		$raw_json = file_get_contents( $path );
		if ( ! is_string( $raw_json ) || '' === trim( $raw_json ) ) {
			return new \WP_Error( 'empty_template_package', __( '官方 JSON 页面包内容为空。', 'developer-starter' ) );
		}

		$decoded = json_decode( $raw_json, true );
		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			return new \WP_Error( 'invalid_template_package', __( '官方 JSON 页面包格式错误。', 'developer-starter' ) );
		}

		$single_page_service = new Single_Page_Package_Service();
		$package = $single_page_service->parse_package( $raw_json );
		if ( is_wp_error( $package ) ) {
			return $package;
		}

		$metadata = isset( $decoded['metadata'] ) && is_array( $decoded['metadata'] ) ? $decoded['metadata'] : array();
		$package['package_id'] = $this->get_package_id( $template );
		$package['template']   = $template;
		$package['metadata']   = $this->sanitize_metadata( $metadata, $template, $package );
		$package['package_version'] = isset( $decoded['version'] ) && is_scalar( $decoded['version'] ) ? absint( $decoded['version'] ) : 1;
		$package['search_mode'] = isset( $decoded['search_mode'] ) && is_scalar( $decoded['search_mode'] ) ? sanitize_key( (string) $decoded['search_mode'] ) : '';
		$package['json_file']  = $this->package_map[ $template ];

		return $package;
	}

	/**
	 * Build catalog metadata for the admin Template Center.
	 *
	 * @param mixed $template Template slug.
	 * @return array<string,mixed>|\WP_Error
	 */
	public function get_catalog_meta_for_template( $template ) {
		$package = $this->load_package_for_template( $template );
		if ( is_wp_error( $package ) ) {
			return $package;
		}

		$metadata = isset( $package['metadata'] ) && is_array( $package['metadata'] ) ? $package['metadata'] : array();
		$category = ! empty( $metadata['category'] ) ? sanitize_key( (string) $metadata['category'] ) : 'industry';
		$industry = ! empty( $metadata['industry'] ) ? $this->normalize_industry_key( $metadata['industry'] ) : 'general';
		$badges = isset( $metadata['badges'] ) && is_array( $metadata['badges'] ) ? $metadata['badges'] : array();

		return array(
			'id'             => ! empty( $metadata['id'] ) ? sanitize_key( (string) $metadata['id'] ) : $this->get_package_id( $template ),
			'label'          => ! empty( $metadata['label'] ) ? sanitize_text_field( (string) $metadata['label'] ) : ( ! empty( $package['title'] ) ? sanitize_text_field( (string) $package['title'] ) : '' ),
			'source'         => 'official_json',
			'source_label'   => __( '启灵官方 JSON', 'developer-starter' ),
			'category'       => $category,
			'category_label' => ! empty( $metadata['category_label'] ) ? sanitize_text_field( (string) $metadata['category_label'] ) : $this->get_category_label( $category ),
			'industry'       => $industry,
			'industry_label' => $this->get_industry_label( $industry ),
			'scenario'       => ! empty( $metadata['scenario'] ) ? sanitize_text_field( (string) $metadata['scenario'] ) : __( '行业官网页面', 'developer-starter' ),
			'description'    => ! empty( $metadata['description'] ) ? sanitize_textarea_field( (string) $metadata['description'] ) : __( '由启灵官方 JSON 页面包提供模块、图片、页面设置和 SEO 初始内容。', 'developer-starter' ),
			'badges'         => $badges,
			'order'          => isset( $metadata['order'] ) ? absint( $metadata['order'] ) : 500,
			'thumbnail'      => ! empty( $metadata['thumbnail'] ) ? esc_url_raw( (string) $metadata['thumbnail'] ) : '',
			'package_id'     => ! empty( $metadata['id'] ) ? sanitize_key( (string) $metadata['id'] ) : $this->get_package_id( $template ),
			'json_file'      => isset( $package['json_file'] ) ? sanitize_file_name( (string) $package['json_file'] ) : '',
		);
	}

	/**
	 * Apply an official JSON package to a page.
	 *
	 * @param int   $post_id  Page ID.
	 * @param mixed $template         Template slug.
	 * @param bool  $replace_existing Whether to replace existing page modules.
	 * @return bool|\WP_Error
	 */
	public function apply_package_to_page( $post_id, $template, $replace_existing = false ) {
		$post_id = absint( $post_id );
		$post = get_post( $post_id );
		if ( ! $post instanceof \WP_Post || 'page' !== $post->post_type ) {
			return false;
		}

		$template = $this->normalize_template_slug( $template );
		if ( ! $this->has_package_for_template( $template ) ) {
			return false;
		}

		$modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
			? developer_starter_get_raw_page_modules_meta( $post_id )
			: get_post_meta( $post_id, '_developer_starter_modules', true );
		if ( ! $replace_existing && ! empty( $modules ) ) {
			return false;
		}

		$package = $this->load_package_for_template( $template );
		if ( is_wp_error( $package ) ) {
			return $package;
		}

		$package_modules = isset( $package['modules'] ) && is_array( $package['modules'] ) ? $package['modules'] : array();
		if ( empty( $package_modules ) ) {
			return new \WP_Error( 'empty_template_package_modules', __( '官方 JSON 页面包没有可写入的模块。', 'developer-starter' ) );
		}

		if ( function_exists( 'developer_starter_normalize_modules_meta_types' ) ) {
			$package_modules = developer_starter_normalize_modules_meta_types( $package_modules );
		}

		if ( function_exists( 'developer_starter_normalize_legacy_module_data_fields' ) ) {
			$package_modules = developer_starter_normalize_legacy_module_data_fields( $package_modules );
		}

		$package_modules = $this->resolve_category_matches( $package_modules );

		update_post_meta( $post_id, '_wp_page_template', $template );
		update_post_meta( $post_id, '_developer_starter_modules', $package_modules );
		$this->apply_page_settings( $post_id, $package );

		$package_id = isset( $package['package_id'] ) ? sanitize_key( (string) $package['package_id'] ) : $this->get_package_id( $template );
		update_post_meta( $post_id, '_qiling_template_center_source', 'official_json' );
		update_post_meta( $post_id, '_qiling_template_center_template', $template );
		update_post_meta( $post_id, '_qiling_template_center_package_id', $package_id );
		update_post_meta( $post_id, '_qiling_official_package_version', max( 1, absint( $package['package_version'] ) ) );

		return true;
	}

	/**
	 * Upgrade an existing official video portal without replacing edited modules.
	 *
	 * @param int $post_id Page ID.
	 * @return bool
	 */
	public function maybe_upgrade_video_portal_page( $post_id ) {
		$post_id = absint( $post_id );
		if ( $post_id <= 0 || 'page' !== get_post_type( $post_id ) ) {
			return false;
		}

		$template   = $this->normalize_template_slug( get_post_meta( $post_id, '_wp_page_template', true ) );
		$package_id = sanitize_key( (string) get_post_meta( $post_id, '_qiling_template_center_package_id', true ) );
		if ( 'templates/template-video-portal.php' !== $template || 'video-portal' !== $package_id ) {
			return false;
		}

		$package = $this->load_package_for_template( $template );
		if ( is_wp_error( $package ) ) {
			return false;
		}

		$target_version  = max( 1, absint( $package['package_version'] ) );
		$current_version = absint( get_post_meta( $post_id, '_qiling_official_package_version', true ) );
		if ( $current_version >= $target_version ) {
			return false;
		}

		$modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
			? developer_starter_get_raw_page_modules_meta( $post_id )
			: get_post_meta( $post_id, '_developer_starter_modules', true );
		$modules = is_array( $modules ) ? $modules : array();
		$has_footer_suite = false;
		$official_footer_module = null;
		if ( ! empty( $package['modules'] ) && is_array( $package['modules'] ) ) {
			foreach ( $package['modules'] as $package_module ) {
				$type = isset( $package_module['type'] ) ? sanitize_key( (string) $package_module['type'] ) : '';
				if ( 'footer_suite' === $type ) {
					$official_footer_module = $package_module;
					break;
				}
			}
		}
		foreach ( $modules as &$module ) {
			if ( ! is_array( $module ) ) {
				continue;
			}
			$type = isset( $module['type'] ) ? sanitize_key( (string) $module['type'] ) : '';
			if ( 'footer_suite' === $type ) {
				$has_footer_suite = true;
				if ( $current_version < 3 && is_array( $official_footer_module ) ) {
					$module = $official_footer_module;
				}
			}
			if ( 'qiling_video_portal_hero' === $type && isset( $module['data'] ) && is_array( $module['data'] ) ) {
				if ( $current_version < 3 ) {
					$module['data']['search_style'] = 'cinema';
				}
			}
		}
		unset( $module );

		if ( ! $has_footer_suite && is_array( $official_footer_module ) ) {
			$modules[] = $official_footer_module;
		}

		update_post_meta( $post_id, '_developer_starter_modules', $modules );
		$this->apply_page_settings( $post_id, $package );
		update_post_meta( $post_id, '_qiling_official_package_version', $target_version );

		return true;
	}

	/**
	 * Resolve explicit category match hints while a package is first applied.
	 *
	 * @param array<int,array<string,mixed>> $modules Package modules.
	 * @return array<int,array<string,mixed>>
	 */
	private function resolve_category_matches( $modules ) {
		$has_match_hints = false;
		foreach ( $modules as $module ) {
			$data = isset( $module['data'] ) && is_array( $module['data'] ) ? $module['data'] : array();
			if ( array_key_exists( 'category_match', $data ) || array_key_exists( 'vr_category_match', $data ) ) {
				$has_match_hints = true;
				break;
			}
			if ( ! empty( $data['vr_boards'] ) && is_array( $data['vr_boards'] ) ) {
				foreach ( $data['vr_boards'] as $board ) {
					if ( is_array( $board ) && array_key_exists( 'category_match', $board ) ) {
						$has_match_hints = true;
						break 2;
					}
				}
			}
		}
		if ( ! $has_match_hints ) {
			return $modules;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
			)
		);
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return $this->remove_category_match_hints( $modules );
		}

		$indexes = array(
			'slug' => array(),
			'name' => array(),
		);
		foreach ( $terms as $term ) {
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}

			$slug = $this->normalize_category_match_value( $term->slug );
			$name = $this->normalize_category_match_value( $term->name );
			if ( '' !== $slug && ! isset( $indexes['slug'][ $slug ] ) ) {
				$indexes['slug'][ $slug ] = (int) $term->term_id;
			}
			if ( '' !== $name && ! isset( $indexes['name'][ $name ] ) ) {
				$indexes['name'][ $name ] = (int) $term->term_id;
			}
		}

		foreach ( $modules as &$module ) {
			if ( ! is_array( $module ) || empty( $module['data'] ) || ! is_array( $module['data'] ) ) {
				continue;
			}

			$data = &$module['data'];
			if ( array_key_exists( 'category_match', $data ) ) {
				$this->resolve_category_field( $data, 'target_category', $data['category_match'], $indexes, '' );
			}
			unset( $data['category_match'] );

			if ( array_key_exists( 'vr_category_match', $data ) ) {
				$this->resolve_category_field( $data, 'vr_category', $data['vr_category_match'], $indexes, '0' );
			}
			unset( $data['vr_category_match'] );

			if ( ! empty( $data['vr_boards'] ) && is_array( $data['vr_boards'] ) ) {
				foreach ( $data['vr_boards'] as $board_index => &$board ) {
					if ( ! is_array( $board ) ) {
						continue;
					}
					if ( array_key_exists( 'category_match', $board ) ) {
						if ( ! $this->resolve_category_field( $board, 'category', $board['category_match'], $indexes, '0' ) ) {
							unset( $data['vr_boards'][ $board_index ] );
							continue;
						}
					}
					unset( $board['category_match'] );
				}
				unset( $board );
				$data['vr_boards'] = array_values( $data['vr_boards'] );
			}
			unset( $data );
		}
		unset( $module );

		return $modules;
	}

	/**
	 * Resolve one empty category field from a named match rule.
	 *
	 * @param array<string,mixed>              $data          Module or repeater data.
	 * @param string                           $field         Category field.
	 * @param mixed                            $match_key     Match rule key.
	 * @param array<string,array<string,int>>  $indexes       Category indexes.
	 * @param string                           $empty_value   Stored empty value.
	 * @return bool Whether the field already had or received a category.
	 */
	private function resolve_category_field( &$data, $field, $match_key, $indexes, $empty_value ) {
		if ( ! empty( $data[ $field ] ) ) {
			return true;
		}
		if ( ! is_scalar( $match_key ) ) {
			return false;
		}

		$aliases = $this->get_category_match_aliases( sanitize_key( (string) $match_key ) );
		foreach ( array( 'slug', 'name' ) as $index_type ) {
			foreach ( $aliases as $alias ) {
				$normalized = $this->normalize_category_match_value( $alias );
				if ( '' !== $normalized && isset( $indexes[ $index_type ][ $normalized ] ) ) {
					$data[ $field ] = (string) $indexes[ $index_type ][ $normalized ];
					return true;
				}
			}
		}

		$data[ $field ] = $empty_value;
		return false;
	}

	/**
	 * Remove internal package hints when no categories are available.
	 *
	 * @param array<int,array<string,mixed>> $modules Package modules.
	 * @return array<int,array<string,mixed>>
	 */
	private function remove_category_match_hints( $modules ) {
		foreach ( $modules as &$module ) {
			if ( ! isset( $module['data'] ) || ! is_array( $module['data'] ) ) {
				continue;
			}
			unset( $module['data']['category_match'], $module['data']['vr_category_match'] );
			if ( isset( $module['data']['vr_boards'] ) && is_array( $module['data']['vr_boards'] ) ) {
				foreach ( $module['data']['vr_boards'] as $board_index => &$board ) {
					if ( is_array( $board ) && array_key_exists( 'category_match', $board ) ) {
						unset( $module['data']['vr_boards'][ $board_index ] );
					}
				}
				unset( $board );
				$module['data']['vr_boards'] = array_values( $module['data']['vr_boards'] );
			}
		}
		unset( $module );

		return $modules;
	}

	/**
	 * Get strict aliases for a video portal category role.
	 *
	 * @param string $match_key Match rule key.
	 * @return array<int,string>
	 */
	private function get_category_match_aliases( $match_key ) {
		$rules = array(
			'movie'    => array( '电影', '影片', 'movie', 'movies', 'film' ),
			'tv'       => array( '电视剧', '剧集', '电视', 'tv', 'series', 'drama' ),
			'anime'    => array( '动漫', '动画', 'anime', 'animation' ),
			'variety'  => array( '综艺', 'variety', 'show' ),
			'trending' => array( '正在热播', '热播', '热门', 'hot', 'trending' ),
		);

		return isset( $rules[ $match_key ] ) ? $rules[ $match_key ] : array();
	}

	/**
	 * Normalize category slugs and names for exact comparisons.
	 *
	 * @param mixed $value Category slug, name, or alias.
	 * @return string
	 */
	private function normalize_category_match_value( $value ) {
		$value = is_scalar( $value ) ? sanitize_text_field( (string) $value ) : '';
		$value = remove_accents( rawurldecode( trim( $value ) ) );
		return function_exists( 'mb_strtolower' ) ? mb_strtolower( $value, 'UTF-8' ) : strtolower( $value );
	}

	/**
	 * Normalize a template slug.
	 *
	 * @param mixed $template Template slug.
	 * @return string
	 */
	private function normalize_template_slug( $template ) {
		$template = is_scalar( $template ) ? sanitize_text_field( (string) $template ) : '';
		$template = str_replace( '\\', '/', trim( $template ) );

		if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
			$template = developer_starter_normalize_page_template_slug( $template );
		}

		return $template;
	}

	/**
	 * Get package path by template.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private function get_package_path( $template ) {
		$file = isset( $this->package_map[ $template ] ) ? $this->package_map[ $template ] : '';
		return trailingslashit( DEVELOPER_STARTER_INC ) . trailingslashit( $this->package_dir ) . $file;
	}

	/**
	 * Get stable package ID.
	 *
	 * @param string $template Template slug.
	 * @return string
	 */
	private function get_package_id( $template ) {
		$file = isset( $this->package_map[ $template ] ) ? $this->package_map[ $template ] : basename( $template );
		return sanitize_key( basename( (string) $file, '.json' ) );
	}

	/**
	 * Sanitize package metadata.
	 *
	 * @param array<string,mixed> $metadata Raw metadata.
	 * @param string              $template Template slug.
	 * @param array<string,mixed> $package Parsed package.
	 * @return array<string,mixed>
	 */
	private function sanitize_metadata( $metadata, $template, $package ) {
		$badges = array();
		if ( isset( $metadata['badges'] ) && is_array( $metadata['badges'] ) ) {
			foreach ( $metadata['badges'] as $badge ) {
				if ( is_scalar( $badge ) && '' !== trim( (string) $badge ) ) {
					$badges[] = sanitize_text_field( (string) $badge );
				}
			}
		}

		$id = ! empty( $metadata['id'] ) && is_scalar( $metadata['id'] )
			? sanitize_key( (string) $metadata['id'] )
			: $this->get_package_id( $template );

		$label = '';
		if ( ! empty( $metadata['label'] ) && is_scalar( $metadata['label'] ) ) {
			$label = sanitize_text_field( (string) $metadata['label'] );
		} elseif ( ! empty( $package['title'] ) && is_scalar( $package['title'] ) ) {
			$label = sanitize_text_field( (string) $package['title'] );
		}

		$category = ! empty( $metadata['category'] ) && is_scalar( $metadata['category'] )
			? sanitize_key( (string) $metadata['category'] )
			: 'industry';
		$industry = ! empty( $metadata['industry'] ) && is_scalar( $metadata['industry'] )
			? $this->normalize_industry_key( $metadata['industry'] )
			: 'general';

		return array(
			'id'             => $id,
			'label'          => $label,
			'category'       => $category,
			'category_label' => ! empty( $metadata['category_label'] ) && is_scalar( $metadata['category_label'] ) ? sanitize_text_field( (string) $metadata['category_label'] ) : $this->get_category_label( $category ),
			'industry'       => $industry,
			'industry_label' => $this->get_industry_label( $industry ),
			'scenario'       => ! empty( $metadata['scenario'] ) && is_scalar( $metadata['scenario'] ) ? sanitize_text_field( (string) $metadata['scenario'] ) : __( '行业官网页面', 'developer-starter' ),
			'description'    => ! empty( $metadata['description'] ) && is_scalar( $metadata['description'] ) ? sanitize_textarea_field( (string) $metadata['description'] ) : '',
			'badges'         => $badges,
			'order'          => isset( $metadata['order'] ) && is_scalar( $metadata['order'] ) ? absint( $metadata['order'] ) : 500,
			'thumbnail'      => ! empty( $metadata['thumbnail'] ) && is_scalar( $metadata['thumbnail'] ) ? esc_url_raw( (string) $metadata['thumbnail'] ) : '',
		);
	}

	/**
	 * Apply page settings and SEO metadata from parsed package.
	 *
	 * @param int                 $post_id Page ID.
	 * @param array<string,mixed> $package Parsed package.
	 * @return void
	 */
	private function apply_page_settings( $post_id, $package ) {
		$search_mode = isset( $package['search_mode'] ) ? sanitize_key( (string) $package['search_mode'] ) : '';
		if ( '' !== $search_mode && class_exists( '\Developer_Starter\Core\Search_Mode_Manager' ) ) {
			$registered_modes = Search_Mode_Manager::get_instance()->get_modes();
			$search_mode = isset( $registered_modes[ $search_mode ] ) ? $search_mode : Search_Mode_Manager::FALLBACK_MODE;
			update_post_meta( $post_id, '_qiling_search_mode', $search_mode );
		}
		if ( ! empty( $package['hide_page_header_defined'] ) ) {
			update_post_meta( $post_id, '_qiling_hide_page_header', ! empty( $package['hide_page_header'] ) ? '1' : '0' );
		}

		update_post_meta( $post_id, '_qiling_transparent_header', ! empty( $package['transparent_header'] ) ? '1' : '0' );
		update_post_meta( $post_id, '_developer_starter_enable_scroll_reveal', ! empty( $package['enable_scroll_reveal'] ) ? '1' : '0' );
		if ( isset( $package['footer'] ) && is_array( $package['footer'] ) && function_exists( 'developer_starter_persist_post_footer_visual_settings' ) ) {
			developer_starter_persist_post_footer_visual_settings( $post_id, $package['footer'] );
		}
		if ( isset( $package['region_decoration'] ) && is_array( $package['region_decoration'] ) && function_exists( 'developer_starter_persist_post_page_region_decoration' ) ) {
			developer_starter_persist_post_page_region_decoration( $post_id, $package['region_decoration'] );
		}
		if ( isset( $package['visual_style'] ) && is_array( $package['visual_style'] ) && function_exists( 'developer_starter_persist_post_page_visual_style' ) ) {
			developer_starter_persist_post_page_visual_style( $post_id, $package['visual_style'] );
		}
		if ( class_exists( '\Developer_Starter\Core\Design_Tokens' ) ) {
			$page_design = isset( $package['page_design'] ) && is_array( $package['page_design'] ) ? $package['page_design'] : array();
			Design_Tokens::persist_page_design_overrides( $post_id, $page_design );
		}

		$seo = isset( $package['seo'] ) && is_array( $package['seo'] ) ? $package['seo'] : array();
		$seo_meta_map = array(
			'title'          => '_developer_starter_seo_title',
			'description'    => '_developer_starter_seo_description',
			'keywords'       => '_developer_starter_seo_keywords',
			'og_title'       => '_developer_starter_og_title',
			'og_description' => '_developer_starter_og_description',
		);

		foreach ( $seo_meta_map as $seo_key => $meta_key ) {
			$value = isset( $seo[ $seo_key ] ) && is_scalar( $seo[ $seo_key ] ) ? trim( (string) $seo[ $seo_key ] ) : '';
			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	/**
	 * Category label fallback.
	 *
	 * @param string $category Category key.
	 * @return string
	 */
	private function get_category_label( $category ) {
		$labels = array(
			'industry' => __( '行业官网', 'developer-starter' ),
			'resource' => __( '数字资源', 'developer-starter' ),
		);

		return isset( $labels[ $category ] ) ? $labels[ $category ] : __( '其他', 'developer-starter' );
	}

	/**
	 * Industry label fallback.
	 *
	 * @param string $industry Industry key.
	 * @return string
	 */
	private function get_industry_label( $industry ) {
		$industry = $this->normalize_industry_key( $industry );
		if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) ) {
			return \Developer_Starter\Modules\Module_Standards::get_industry_label( $industry );
		}

		return ucwords( str_replace( array( '-', '_' ), ' ', (string) $industry ) );
	}

	/**
	 * Normalize an industry key through the shared standard.
	 *
	 * @param mixed $industry Industry key.
	 * @return string
	 */
	private function normalize_industry_key( $industry ) {
		$key = is_scalar( $industry ) ? sanitize_key( str_replace( '-', '_', (string) $industry ) ) : '';
		if ( '' === $key ) {
			return 'general';
		}

		if ( class_exists( '\Developer_Starter\Modules\Module_Standards' ) ) {
			return \Developer_Starter\Modules\Module_Standards::normalize_industry_key( $key );
		}

		return $key;
	}
}
