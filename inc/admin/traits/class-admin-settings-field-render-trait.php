<?php
/**
 * 主题后台设置字段渲染 Trait。
 *
 * 聚合各业务模块的字段渲染 Trait，保持设置渲染器结构清晰易维护。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin\Traits;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/field-render/class-admin-settings-field-render-international-center-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-ai-connections-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-governance-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-international-diagnostics-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-design-tokens-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-design-presets-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-tools-trait.php';
require_once __DIR__ . '/field-render/class-admin-settings-field-render-seo-health-trait.php';

trait Admin_Settings_Field_Render_Trait {

    use Admin_Settings_Field_Render_International_Center_Trait;
    use Admin_Settings_Field_Render_AI_Connections_Trait;
    use Admin_Settings_Field_Render_Governance_Trait;
    use Admin_Settings_Field_Render_International_Diagnostics_Trait;
    use Admin_Settings_Field_Render_Design_Tokens_Trait;
    use Admin_Settings_Field_Render_Design_Presets_Trait;
    use Admin_Settings_Field_Render_Tools_Trait;
    use Admin_Settings_Field_Render_SEO_Health_Trait;
}
