import { readdirSync } from 'node:fs';

import {
  assertContains,
  assertFileExists,
  readThemeFile,
  themePath,
} from './_helpers.mjs';

export const name = 'Official template center contracts';

export async function run() {
  [
    'inc/admin/class-template-center-admin.php',
    'inc/core/class-official-template-package-service.php',
    'inc/core/helpers/helpers-content-modules.php',
    'inc/core/helpers/helpers-page-skins.php',
    'functions.php',
    'inc/core/bootstrap-services.php',
  ].forEach((file) => {
    assertFileExists(file, `Template center chain missing required file: ${file}`);
  });

  const servicePhp = readThemeFile('inc/core/class-official-template-package-service.php');
  const officialJsonFiles = Array.from(
    servicePhp.matchAll(/=>\s*'([^']+\.json)'/g),
    (match) => match[1]
  ).sort();

  if (officialJsonFiles.length < 90) {
    throw new Error(`Official JSON template package map is unexpectedly small: ${officialJsonFiles.length}`);
  }

  officialJsonFiles.forEach((file) => {
    const relativePath = `inc/template-center/official/${file}`;
    assertFileExists(relativePath, `Official JSON template package missing: ${relativePath}`);
    const payload = JSON.parse(readThemeFile(relativePath));
    if (!payload.metadata?.thumbnail || !Array.isArray(payload.modules) || payload.modules.length < 1) {
      throw new Error(`Official JSON template package is incomplete: ${relativePath}`);
    }
  });

  const adminPhp = readThemeFile('inc/admin/class-template-center-admin.php');
  [
    'class Template_Center_Admin',
    "private $page_slug = 'developer-starter-template-center';",
    'register_submenu_page(',
    'get_template_catalog(',
    'filter_catalog(',
    'render_template_card(',
    'data-qtc-src',
    'IntersectionObserver',
    'render_industry_coverage_panel(',
    'get_industry_coverage_data(',
    'get_standard_industry_choices(',
    'handle_create_request(',
    'normalize_industry_key(',
    'developer_starter_maybe_fill_default_modules_for_page_template',
    'get_official_template_package_service',
    'qtc-card-thumb',
    'qtc-coverage-panel',
    '待补行业',
    "update_post_meta( $post_id, '_qiling_template_center_source', 'official' );",
    "update_post_meta( $post_id, '_qiling_template_center_target_language', $target_language );",
    "update_post_meta( $post_id, '_qiling_template_center_target_market', $target_market );",
    'template_target_language',
    'template_target_market',
    'template_localization_enable',
    'localize_created_template_page(',
    'get_template_localization_language_choices(',
    "'fr' => __( '法文'",
    "'de' => __( '德文'",
    "'es' => __( '西班牙文'",
    'create_language_page',
    "developer_starter_template_center_catalog",
    '启灵官方',
    '这里展示启灵官方模板',
  ].forEach((needle) => {
    assertContains(adminPhp, needle, `Template center admin contract changed unexpectedly: ${needle}`);
  });

  const helpersPhp = readThemeFile('inc/core/helpers/helpers-content-modules.php');
  const pageSkinsPhp = readThemeFile('inc/core/helpers/helpers-page-skins.php');
  const assetsPhp = readThemeFile('inc/core/class-assets.php');
  const cloudCanvasPackage = JSON.parse(readThemeFile('inc/template-center/official/qiling-cloud-canvas.json'));
  const ctaModulePhp = readThemeFile('inc/modules/modules/class-cta-module.php');
  const dynamicBannerModulePhp = readThemeFile('inc/modules/modules/class-dynamic-banner-module.php');
  const heroSearchModulePhp = readThemeFile('inc/modules/modules/class-hero-search-module.php');
  const cloudCanvasCss = readThemeFile('assets/css/cloud-canvas-skin.css');
  const headerPhp = readThemeFile('header.php');
  const footerVisualPhp = readThemeFile('inc/core/helpers/helpers-footer-visual.php');
  const pageSettingsPhp = readThemeFile('inc/admin/class-meta-boxes-post-settings-service.php');

  [
    'developer_starter_get_page_visual_skins',
    'developer_starter_get_page_visual_skin_for_template',
    'developer_starter_get_current_page_visual_skin',
    'developer_starter_get_page_visual_skin_wrapper_classes',
    'developer_starter_enqueue_current_page_visual_skin_styles',
    'developer_starter_page_visual_skins',
    'templates/template-qiling-cloud-canvas.php',
    'assets/css/cloud-canvas-skin.css',
    'qiling-page-skin-cloud-canvas',
    "'footer'          => array(",
    "'site-footer--integrated-canvas'",
  ].forEach((needle) => {
    assertContains(pageSkinsPhp, needle, `Page visual skin registry contract changed unexpectedly: ${needle}`);
  });

  if (
    !cloudCanvasPackage.footer
    || cloudCanvasPackage.footer.mode !== 'page_skin'
    || cloudCanvasPackage.footer.wave !== 'on'
    || cloudCanvasPackage.footer.preset !== 'cloud_canvas'
    || cloudCanvasPackage.footer.inherit_skin_colors !== true
  ) {
    throw new Error('Cloud canvas official package is missing its page-level footer skin strategy.');
  }

  assertContains(
    readThemeFile('inc/core/helpers/bootstrap.php'),
    "helpers-page-skins.php",
    'Page visual skin helpers are not loaded by helper bootstrap.'
  );
  assertContains(
    assetsPhp,
    'developer_starter_enqueue_current_page_visual_skin_styles( $version );',
    'Page visual skin CSS is not loaded through the shared asset registry.'
  );
  assertContains(ctaModulePhp, "array( 'color', 'gradient' )", 'CTA renderer must support official gradient background packages.');
  assertContains(ctaModulePhp, "data['cta_bg_gradient']", 'CTA renderer must retain the official gradient field contract.');
  assertContains(dynamicBannerModulePhp, "data['db_bg_overlay']", 'Dynamic banner renderer must retain official image overlay settings.');
  assertContains(heroSearchModulePhp, "data['hs_overlay_opacity']", 'Hero search renderer must retain official overlay opacity settings.');
  assertContains(headerPhp, '$transparent_header_tone', 'Transparent headers must resolve foreground tone from the first hero.');
  assertContains(headerPhp, "header-tone-' . $transparent_header_tone", 'Transparent header tone must be exposed as a body state.');
  assertContains(headerPhp, "['header']['transparent_text']", 'Explicit page transparent-header text must beat automatic tone selection.');
  assertContains(headerPhp, "custom_header_values['search_bg']", 'Explicit transparent search colors must beat automatic header tone values.');
  assertContains(headerPhp, "custom_header_values['phone_text']", 'Explicit transparent phone colors must beat automatic header tone values.');
  assertContains(footerVisualPhp, "developer_starter_get_page_visual_style_custom_vars_array( $page_visual_settings, 'footer' )", 'Footer runtime must merge only explicit page footer overrides.');
  assertContains(footerVisualPhp, 'developer_starter_get_page_visual_custom_preset_skin', 'Footer-only presets must support user visual presets.');
  assertContains(pageSettingsPhp, 'data-qiling-page-footer-settings', 'Backend page visual settings must expose the footer source strategy.');
  assertContains(pageSettingsPhp, 'save_page_footer_meta_box(', 'Backend footer strategy must persist through the shared footer contract.');
  if (/\.qiling-page-skin--cloud-canvas \.module,\s*\.template-qiling-cloud-canvas \.module\s*\{[^}]*background:\s*transparent\s*!important/.test(cloudCanvasCss)) {
    throw new Error('Cloud canvas skin must not erase explicit module backgrounds with !important.');
  }
  assertContains(cloudCanvasCss, 'body.qiling-page-skin-integrated :is(', 'Integrated pages must expose one continuous page canvas.');
  assertContains(cloudCanvasCss, ':not(.has-bg-image):not(.bg-type-image)', 'Integrated canvas must preserve explicit module image backgrounds.');
  assertContains(cloudCanvasCss, '.module-cta :is(.cta-title, .cta-subtitle)', 'Integrated CTA text must retain foreground contrast.');
  assertContains(cloudCanvasCss, '.qiling-page-skin--tech-canvas {', 'Technology canvas must define its own blue/green page palette.');
  assertContains(
    readThemeFile('templates/template-hosting-saas-home.php'),
    'developer_starter_get_page_visual_skin_wrapper_classes(',
    'Hosting integrated template must attach its page skin wrapper classes.'
  );

  [
    'developer_starter_get_page_template_default_modules_map',
    'developer_starter_maybe_fill_default_modules_for_page_template',
    'developer_starter_maybe_fill_official_template_package_for_page_template',
    'developer_starter_maybe_fill_official_template_package_after_save',
    'templates/template-home.php',
    'templates/template-ai-product-brand.php',
    'templates/template-saas-home.php',
    'templates/template-manufacturing-factory.php',
    'templates/template-local-service-official.php',
    'templates/template-qiling-recycling-official.php',
    'templates/template-qiling-housekeeping-official.php',
    'templates/template-qiling-ai-writing-studio.php',
    'templates/template-qiling-ai-multilingual-seo.php',
    'templates/template-qiling-doc-ocr-converter.php',
    'templates/template-qiling-image-studio.php',
    'templates/template-qiling-cloud-storage-hosting.php',
    'templates/template-qiling-cloud-canvas.php',
    'templates/template-qiling-security-ops.php',
    'templates/template-qiling-escrow-platform.php',
    'templates/template-qiling-freetask-platform.php',
    'templates/template-qiling-friends-matchmaking.php',
    'templates/template-qiling-bbs-support-community.php',
    'templates/template-healthcare-clinic.php',
    'templates/template-government-public-service.php',
    'templates/template-agriculture-food.php',
    'templates/template-property-management.php',
    'templates/template-semiconductor-electronics.php',
    'templates/template-industrial-automation-robotics.php',
    'templates/template-medical-device.php',
    'templates/template-lab-instrument.php',
    'templates/template-solar-storage-equipment.php',
    'templates/template-water-treatment-environmental.php',
    'templates/template-cross-border-ecommerce-service.php',
    'templates/template-overseas-warehouse-supply-chain.php',
    'templates/template-enterprise-software-integrator.php',
    'templates/template-ai-agent-enterprise.php',
    'templates/template-ev-charging-station.php',
  ].forEach((needle) => {
    assertContains(helpersPhp, needle, `Template center depends on missing template fill map: ${needle}`);
  });

  const allTemplateFiles = readdirSync(themePath('templates'))
    .filter((file) => file.endsWith('.php'))
    .map((file) => `templates/${file}`)
    .sort();
  const defaultFillTemplates = Array.from(
    helpersPhp.matchAll(/'templates\/([^']+\.php)'\s*=>\s*array\(/g),
    (match) => `templates/${match[1]}`
  ).sort();
  const expectedTemplatesWithoutDefaultFill = [
    'templates/template-about.php',
    'templates/template-account.php',
    'templates/template-careers.php',
    'templates/template-changelog.php',
    'templates/template-contact.php',
    'templates/template-faq.php',
    'templates/template-forgot-password.php',
    'templates/template-fullscreen.php',
    'templates/template-fullwidth.php',
    'templates/template-latest-posts.php',
    'templates/template-login.php',
    'templates/template-register.php',
    'templates/template-submit-post.php',
  ].sort();
  const templatesWithoutDefaultFill = allTemplateFiles
    .filter((file) => !defaultFillTemplates.includes(file))
    .sort();

  if (JSON.stringify(templatesWithoutDefaultFill) !== JSON.stringify(expectedTemplatesWithoutDefaultFill)) {
    throw new Error(
      `Unexpected page templates without default fill mapping: ${JSON.stringify(templatesWithoutDefaultFill)}`
    );
  }

  [
    'class Official_Template_Package_Service',
    'get_supported_templates',
    'apply_package_to_page',
    'get_catalog_meta_for_template',
    'normalize_industry_key',
    'templates/template-home.php',
    'templates/template-blog.php',
    'templates/template-products.php',
    'templates/template-solutions.php',
    'templates/template-cases.php',
    'templates/template-news.php',
    'templates/template-topic.php',
    'templates/template-features-showcase.php',
    'templates/template-resources.php',
    'templates/template-resource-search.php',
    'templates/template-landing.php',
    'templates/template-video-hero.php',
    'templates/template-interactive-product-launch.php',
    'templates/template-ai-product-brand.php',
    'templates/template-saas-pricing.php',
    'templates/template-software-intro.php',
    'templates/template-data-showcase.php',
    'templates/template-resume.php',
    'templates/template-beauty-salon.php',
    'templates/template-homestay.php',
    'templates/template-auto-service.php',
    'templates/template-chain-store-official.php',
    'templates/template-course-enrollment.php',
    'templates/template-ecommerce-promo.php',
    'templates/template-software-home.php',
    'templates/template-saas-home.php',
    'templates/template-cybersecurity-brand.php',
    'templates/template-manufacturing-factory.php',
    'templates/template-foreign-trade-b2b.php',
    'templates/template-finance-consulting.php',
    'templates/template-accounting-tax-service.php',
    'templates/template-intellectual-property-service.php',
    'templates/template-study-abroad-immigration.php',
    'templates/template-early-childhood-education.php',
    'templates/template-vocational-training-school.php',
    'templates/template-psychological-counseling.php',
    'templates/template-senior-care-center.php',
    'templates/template-postpartum-care-center.php',
    'templates/template-architecture-design-studio.php',
    'templates/template-interior-soft-decoration.php',
    'templates/template-landscape-garden-design.php',
    'templates/template-appliance-repair-service.php',
    'templates/template-franchise-investment.php',
    'templates/template-mcn-live-commerce.php',
    'templates/template-conference-event-service.php',
    'templates/template-real-estate-service.php',
    'templates/template-local-service-official.php',
    'templates/template-qiling-recycling-official.php',
    'templates/template-qiling-housekeeping-official.php',
    'templates/template-qiling-ai-writing-studio.php',
    'templates/template-qiling-ai-multilingual-seo.php',
    'templates/template-qiling-doc-ocr-converter.php',
    'templates/template-qiling-image-studio.php',
    'templates/template-qiling-cloud-storage-hosting.php',
    'templates/template-qiling-cloud-canvas.php',
    'templates/template-qiling-security-ops.php',
    'templates/template-qiling-escrow-platform.php',
    'templates/template-qiling-freetask-platform.php',
    'templates/template-qiling-friends-matchmaking.php',
    'templates/template-qiling-bbs-support-community.php',
    'templates/template-healthcare-clinic.php',
    'templates/template-logistics-supply-chain.php',
    'templates/template-recruitment-hr-service.php',
    'templates/template-nonprofit-organization.php',
    'templates/template-government-public-service.php',
    'templates/template-agriculture-food.php',
    'templates/template-energy-environment.php',
    'templates/template-event-exhibition.php',
    'templates/template-industrial-park.php',
    'templates/template-property-management.php',
    'templates/template-semiconductor-electronics.php',
    'templates/template-industrial-automation-robotics.php',
    'templates/template-medical-device.php',
    'templates/template-lab-instrument.php',
    'templates/template-solar-storage-equipment.php',
    'templates/template-water-treatment-environmental.php',
    'templates/template-cross-border-ecommerce-service.php',
    'templates/template-overseas-warehouse-supply-chain.php',
    'templates/template-enterprise-software-integrator.php',
    'templates/template-ai-agent-enterprise.php',
    'templates/template-ev-charging-station.php',
    'official_json',
  ].forEach((needle) => {
    assertContains(servicePhp, needle, `Official JSON template service contract changed unexpectedly: ${needle}`);
  });

  const bootstrapServicesPhp = readThemeFile('inc/core/bootstrap-services.php');
  assertContains(
    bootstrapServicesPhp,
    'new Developer_Starter\\Admin\\Template_Center_Admin();',
    'Template center admin is not initialized.'
  );

}
