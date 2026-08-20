<?php
/**
 * Request message builder.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core\AI;

use Developer_Starter\Core\AI_Decorator;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Prompt_Builder {

    /**
     * @var object
     */
    private $decorator;

    /**
     * @param object $decorator 装修服务门面。
     */
    public function __construct( AI_Decorator $decorator ) {
        $this->decorator = $decorator;
    }

    /**
     * 构建页面规划消息体。
     *
     * @param int                               $post_id 页面 ID。
     * @param string                            $prompt 用户需求。
     * @param array<string,array<string,mixed>> $selected_modules 候选模块 schema。
     * @return array<int,array<string,string>>
     */
    public function build_page_plan_messages( $post_id, $prompt, $selected_modules ) {
        $payload = array(
            'task' => '为启灵主题规划页面模块结构',
            'page_context' => $this->build_page_context( $post_id ),
            'user_requirements' => $prompt,
            'output_rules' => array(
                '只能从 candidate_modules 中选择要使用的模块 type，可以少选，不要为了凑数把所有候选模块都用上',
                '输出必须是单个 json 对象，不要输出解释、Markdown 或代码块',
                '顶层必须返回 title、page_template、hide_page_header、transparent_header、enable_scroll_reveal、seo、modules',
                'seo 必须是当前单页的 SEO 建议对象，包含 title、description、keywords、og_title、og_description',
                'modules 必须是数组，每项只返回 type 和 goal',
                'goal 用一句中文说明该模块在页面中的作用，简洁明确即可',
                '模块顺序应符合正常页面阅读逻辑，例如首屏 -> 能力/服务 -> 案例/信任 -> CTA',
                '不要规划整站、多页面、站点包或页面包市场内容',
            ),
            'candidate_modules' => $this->build_plan_candidate_modules( $selected_modules ),
        );

        return array(
            array(
                'role'    => 'system',
                'content' => $this->decorator->get_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => "请根据下面的 json 输入先规划页面结构，并严格只返回 json 对象：\n" . wp_json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
            ),
        );
    }

    /**
     * 构建单模块生成消息体。
     *
     * @param int                 $post_id 页面 ID。
     * @param string              $prompt 用户需求。
     * @param array<string,mixed> $plan 页面规划。
     * @param array<string,mixed> $current_module 当前模块规划。
     * @param array<string,mixed> $current_module_schema 当前模块 schema。
     * @param array<int,array<string,string>> $completed_modules 已完成模块摘要。
     * @param array<string,mixed>             $current_module_data 当前模块已有数据。
     * @return array<int,array<string,string>>
     */
    public function build_page_module_messages( $post_id, $prompt, $plan, $current_module, $current_module_schema, $completed_modules, $current_module_data = array() ) {
        $payload = array(
            'task' => '为启灵主题生成或优化单个模块的 json 数据',
            'page_context' => $this->build_page_context( $post_id ),
            'user_requirements' => $prompt,
            'page_plan' => array(
                'title'                => isset( $plan['title'] ) ? (string) $plan['title'] : '',
                'page_template'        => isset( $plan['page_template'] ) ? (string) $plan['page_template'] : $this->decorator->get_default_page_template(),
                'hide_page_header'     => ! empty( $plan['hide_page_header'] ),
                'transparent_header'   => ! empty( $plan['transparent_header'] ),
                'enable_scroll_reveal' => ! empty( $plan['enable_scroll_reveal'] ),
                'seo'                  => isset( $plan['seo'] ) && is_array( $plan['seo'] ) ? $plan['seo'] : array(),
                'modules'              => isset( $plan['modules'] ) && is_array( $plan['modules'] ) ? array_values( $plan['modules'] ) : array(),
            ),
            'completed_modules' => array_values( $completed_modules ),
            'current_module' => array(
                'type'        => isset( $current_module['type'] ) ? (string) $current_module['type'] : '',
                'goal'        => isset( $current_module['goal'] ) ? (string) $current_module['goal'] : '',
                'name'        => isset( $current_module_schema['name'] ) ? (string) $current_module_schema['name'] : '',
                'fields'      => isset( $current_module_schema['fields'] ) && is_array( $current_module_schema['fields'] ) ? $current_module_schema['fields'] : array(),
                'defaultData' => isset( $current_module_schema['defaultData'] ) && is_array( $current_module_schema['defaultData'] ) ? $current_module_schema['defaultData'] : array(),
                'existingData'=> is_array( $current_module_data ) ? $current_module_data : array(),
                'metadata'    => isset( $current_module_schema['metadata'] ) && is_array( $current_module_schema['metadata'] ) ? $current_module_schema['metadata'] : array(),
            ),
            'output_rules' => array(
                '只生成 current_module.type 对应的模块，不要生成其他模块',
                '输出必须是单个 json 对象，不要输出解释、Markdown 或代码块',
                '格式必须固定为 {"type":"当前模块ID","data":{...}}',
                'type 必须等于 current_module.type',
                '只能使用 current_module.fields 中出现过的字段，不要发明字段',
                '如字段无法确定，优先留空或沿用 defaultData，不要胡乱补全',
                '如果 existingData 中已有可用内容，请在保留事实信息的基础上优化结构、文案、CTA 和视觉字段',
                '内容要与 page_plan 和 completed_modules 保持风格、卖点、CTA 一致',
                '不要输出整站、多页面、站点包或额外模块',
            ),
        );

        return array(
            array(
                'role'    => 'system',
                'content' => $this->decorator->get_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => "请根据下面的 json 输入生成当前模块的数据，并严格只返回 json 对象：\n" . wp_json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
            ),
        );
    }

    /**
     * 构建单模块本地化消息体。
     *
     * @param int                              $post_id 页面 ID。
     * @param string                           $prompt 补充要求。
     * @param string                           $module_type 模块类型。
     * @param array<string,mixed>              $current_module_schema 当前模块 schema。
     * @param array<string,mixed>              $current_module_data 当前模块数据。
     * @param array<string,mixed>              $localization 本地化参数。
     * @param array<string,array<string,mixed>> $text_only_schema 文案字段白名单。
     * @return array<int,array<string,string>>
     */
    public function build_module_localization_messages( $post_id, $prompt, $module_type, $current_module_schema, $current_module_data, $localization, $text_only_schema ) {
        $payload = array(
            'task' => '本地化启灵主题单个模块的文案字段',
            'page_context' => $this->build_page_context( $post_id ),
            'localization' => array(
                'target_language' => isset( $localization['target_language'] ) ? (string) $localization['target_language'] : 'en',
                'target_language_label' => isset( $localization['target_language_label'] ) ? (string) $localization['target_language_label'] : '',
                'target_market' => isset( $localization['target_market'] ) ? (string) $localization['target_market'] : '',
                'tone' => isset( $localization['tone'] ) ? (string) $localization['tone'] : 'professional',
                'tone_label' => isset( $localization['tone_label'] ) ? (string) $localization['tone_label'] : '',
                'currency' => isset( $localization['currency'] ) ? (string) $localization['currency'] : 'USD',
                'industry' => isset( $localization['industry'] ) ? (string) $localization['industry'] : '',
                'preserve_layout' => true,
            ),
            'user_notes' => $prompt,
            'current_module' => array(
                'type' => $module_type,
                'name' => isset( $current_module_schema['name'] ) ? (string) $current_module_schema['name'] : $module_type,
                'data' => is_array( $current_module_data ) ? $current_module_data : array(),
                'text_only_field_whitelist' => $text_only_schema,
                'metadata' => isset( $current_module_schema['metadata'] ) && is_array( $current_module_schema['metadata'] ) ? $current_module_schema['metadata'] : array(),
            ),
            'output_rules' => array(
                '输出必须是单个 json 对象，不要输出解释、Markdown 或代码块',
                '格式必须固定为 {"type":"当前模块ID","data":{...}}，type 必须等于 current_module.type',
                '只能返回 current_module.text_only_field_whitelist 中列出的字段，可以只返回被本地化的字段',
                '只改文案字段：标题、副标题、描述、按钮文字、列表项名称、FAQ 问答、价格展示等可读文本',
                '禁止修改布局、样式、颜色、间距、图片、图标、链接 URL、文章 ID、分类、taxonomy、数据源、显示开关、数量、排序字段',
                '保留 repeater 列表数量和顺序，不要新增、删除或重排列表项',
                '默认强制保留原布局，preserve_layout 始终为 true',
                '如果某个字段不是自然语言文案，原样省略，不要翻译',
                '目标语言必须与 localization.target_language_label / localization.target_language 一致；输出文案必须适配目标市场、语气、币种和行业参数',
            ),
        );

        return array(
            array(
                'role'    => 'system',
                'content' => $this->decorator->get_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => "请根据下面的 json 输入进行单模块本地化，并严格只返回 json 对象：\n" . wp_json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
            ),
        );
    }

    /**
     * Build full-page localization messages.
     *
     * @param int                              $post_id Page ID.
     * @param string                           $prompt User notes.
     * @param array<string,mixed>              $current_package Current page package.
     * @param array<string,mixed>              $localization Localization args.
     * @param array<int,array<string,mixed>>   $text_schema_map Text-only whitelist per module index.
     * @return array<int,array<string,string>>
     */
    public function build_page_localization_messages( $post_id, $prompt, $current_package, $localization, $text_schema_map ) {
        $payload = array(
            'task' => '本地化启灵主题整页页面包的文案字段',
            'page_context' => $this->build_page_context( $post_id ),
            'localization' => array(
                'target_language' => isset( $localization['target_language'] ) ? (string) $localization['target_language'] : 'en',
                'target_language_label' => isset( $localization['target_language_label'] ) ? (string) $localization['target_language_label'] : '',
                'target_market' => isset( $localization['target_market'] ) ? (string) $localization['target_market'] : '',
                'tone' => isset( $localization['tone'] ) ? (string) $localization['tone'] : 'professional',
                'tone_label' => isset( $localization['tone_label'] ) ? (string) $localization['tone_label'] : '',
                'currency' => isset( $localization['currency'] ) ? (string) $localization['currency'] : 'USD',
                'industry' => isset( $localization['industry'] ) ? (string) $localization['industry'] : '',
                'industry_tone_pack' => isset( $localization['industry_tone_pack'] ) ? (string) $localization['industry_tone_pack'] : '',
                'industry_tone_pack_label' => isset( $localization['industry_tone_pack_label'] ) ? (string) $localization['industry_tone_pack_label'] : '',
                'industry_tone_pack_guidance' => isset( $localization['industry_tone_pack_guidance'] ) ? (string) $localization['industry_tone_pack_guidance'] : '',
                'preserve_layout' => true,
            ),
            'glossary' => array(
                'fixed_translations' => isset( $localization['fixed_translations'] ) && is_array( $localization['fixed_translations'] ) ? $localization['fixed_translations'] : array(),
                'forbidden_words' => isset( $localization['forbidden_words'] ) && is_array( $localization['forbidden_words'] ) ? $localization['forbidden_words'] : array(),
                'product_terms' => isset( $localization['product_terms'] ) && is_array( $localization['product_terms'] ) ? $localization['product_terms'] : array(),
            ),
            'user_notes' => $prompt,
            'current_package' => is_array( $current_package ) ? $current_package : array(),
            'module_text_only_field_whitelist' => is_array( $text_schema_map ) ? array_values( $text_schema_map ) : array(),
            'output_rules' => array(
                '输出必须是单个 json 对象，不要输出解释、Markdown 或代码块',
                '格式必须固定为 {"title":"...","seo":{...},"modules":[{"type":"原模块ID","data":{...}}],"localization_review":{...}}',
                'modules 数量、顺序和每个模块 type 必须与 current_package.modules 完全一致',
                '只能返回 module_text_only_field_whitelist 中列出的文案字段，可以只返回被本地化的字段',
                '只改文案字段：标题、副标题、描述、按钮文字、列表项名称、FAQ 问答、价格展示等可读文本',
                '禁止修改布局、样式、颜色、间距、图片、图标、链接 URL、文章 ID、分类、taxonomy、数据源、显示开关、数量、排序字段',
                '保留 repeater 列表数量和顺序，不要新增、删除或重排列表项',
                'title、seo.title、seo.description、seo.keywords、seo.og_title、seo.og_description 需要适配目标语言和目标市场',
                '固定翻译必须严格遵守；产品名词库必须保留或按固定译名处理；禁用词不能出现在输出里',
                '目标语言必须与 localization.target_language_label / localization.target_language 一致；输出文案必须适配目标市场、语气、币种、行业参数和行业语气包',
                'localization_review 必须包含 literalness、cta_market_fit、seo_title_length、warnings、recommendations，用于评分',
            ),
            'review_requirements' => array(
                'literalness' => '检查是否太直译，取值 good / risk / bad',
                'cta_market_fit' => '检查 CTA 是否适合目标市场，取值 good / risk / bad',
                'seo_title_length' => '检查 SEO 标题长度是否过长，取值 good / risk / bad',
                'warnings' => '最多 5 条问题提示',
                'recommendations' => '最多 5 条优化建议',
            ),
        );

        return array(
            array(
                'role'    => 'system',
                'content' => $this->decorator->get_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => "请根据下面的 json 输入进行整页本地化，并严格只返回 json 对象：\n" . wp_json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
            ),
        );
    }

    /**
     * Build post/article/FAQ content localization messages.
     *
     * @param int                 $post_id Post ID.
     * @param string              $prompt User notes.
     * @param array<string,mixed> $content_payload Current content payload.
     * @param array<string,mixed> $localization Localization args.
     * @return array<int,array<string,string>>
     */
    public function build_content_localization_messages( $post_id, $prompt, $content_payload, $localization ) {
        $payload = array(
            'task' => '本地化启灵主题文章或 FAQ 的标题、正文、摘要和 SEO 文案',
            'page_context' => $this->build_page_context( $post_id ),
            'localization' => array(
                'target_language' => isset( $localization['target_language'] ) ? (string) $localization['target_language'] : 'en',
                'target_language_label' => isset( $localization['target_language_label'] ) ? (string) $localization['target_language_label'] : '',
                'target_market' => isset( $localization['target_market'] ) ? (string) $localization['target_market'] : '',
                'tone' => isset( $localization['tone'] ) ? (string) $localization['tone'] : 'professional',
                'tone_label' => isset( $localization['tone_label'] ) ? (string) $localization['tone_label'] : '',
                'currency' => isset( $localization['currency'] ) ? (string) $localization['currency'] : 'USD',
                'industry' => isset( $localization['industry'] ) ? (string) $localization['industry'] : '',
                'industry_tone_pack' => isset( $localization['industry_tone_pack'] ) ? (string) $localization['industry_tone_pack'] : '',
                'industry_tone_pack_label' => isset( $localization['industry_tone_pack_label'] ) ? (string) $localization['industry_tone_pack_label'] : '',
                'industry_tone_pack_guidance' => isset( $localization['industry_tone_pack_guidance'] ) ? (string) $localization['industry_tone_pack_guidance'] : '',
            ),
            'glossary' => array(
                'fixed_translations' => isset( $localization['fixed_translations'] ) && is_array( $localization['fixed_translations'] ) ? $localization['fixed_translations'] : array(),
                'forbidden_words' => isset( $localization['forbidden_words'] ) && is_array( $localization['forbidden_words'] ) ? $localization['forbidden_words'] : array(),
                'product_terms' => isset( $localization['product_terms'] ) && is_array( $localization['product_terms'] ) ? $localization['product_terms'] : array(),
            ),
            'user_notes' => $prompt,
            'current_content' => is_array( $content_payload ) ? $content_payload : array(),
            'output_rules' => array(
                '输出必须是单个 json 对象，不要输出解释、Markdown 或代码块',
                '格式必须固定为 {"title":"...","excerpt":"...","content":"...","seo":{...},"localization_review":{...}}',
                '只改自然语言文案，保留正文 HTML 标签结构，不添加脚本、iframe、表单或高风险 HTML',
                'title、excerpt、content、seo.title、seo.description、seo.keywords、seo.og_title、seo.og_description 必须适配目标语言和目标市场',
                '固定翻译必须严格遵守；产品名词库必须保留或按固定译名处理；禁用词不能出现在输出里',
                'localization_review 必须包含 literalness、cta_market_fit、seo_title_length、warnings、recommendations，用于评分',
            ),
        );

        return array(
            array(
                'role'    => 'system',
                'content' => $this->decorator->get_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => "请根据下面的 json 输入进行内容本地化，并严格只返回 json 对象：\n" . wp_json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
            ),
        );
    }

    /**
     * 构建整页生成消息体。
     *
     * @param int                               $post_id 页面 ID。
     * @param string                            $prompt 用户需求。
     * @param array<string,array<string,mixed>> $selected_modules 允许模块。
     * @return array<int,array<string,string>>
     */
    public function build_request_messages( $post_id, $prompt, $selected_modules ) {
        $payload = array(
            'task' => '为启灵主题生成页面装修 json 草稿',
            'page_context' => $this->build_page_context( $post_id ),
            'user_requirements' => $prompt,
            'output_rules' => array(
                '只能使用 selected_modules 中给出的模块 type',
                '只能使用 schema 中出现过的字段，不要发明字段',
                'select 字段只能填写 options 中允许的值',
                'repeater 只能输出 fields 里定义的字段结构',
                '输出必须是单个 json 对象，不要输出解释、Markdown 或代码块',
                '顶层请返回 title、page_template、hide_page_header、transparent_header、enable_scroll_reveal、seo、modules',
                'seo 必须是当前单页的 SEO 建议对象，包含 title、description、keywords、og_title、og_description',
                'modules 必须是数组，每项形如 {"type":"模块ID","data":{...}}',
                '如无法确定字段内容，优先留空或使用 defaultData，不要猜不存在的字段',
                '默认使用中文文案，除非用户明确要求其他语言',
                '可以参考 page_context.existing_modules 优化已有内容，但结果仍然只服务当前单页',
                '不要生成整站、多页面、站点包、页面包市场内容或任何当前页面以外的页面清单',
            ),
            'selected_modules' => array_values( $selected_modules ),
        );

        return array(
            array(
                'role'    => 'system',
                'content' => $this->decorator->get_system_prompt(),
            ),
            array(
                'role'    => 'user',
                'content' => "请根据下面的 json 输入生成页面装修草稿，并严格只返回 json 对象：\n" . wp_json_encode(
                    $payload,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
                ),
            ),
        );
    }

    /**
     * 构建页面上下文。
     *
     * @param int $post_id 页面 ID。
     * @return array<string,mixed>
     */
    private function build_page_context( $post_id ) {
        return array(
            'post_id'        => $post_id,
            'current_page'   => $this->decorator->get_post_page_settings( $post_id ),
            'existing_modules' => $this->decorator->get_existing_modules_context( $post_id ),
            'builder_protocol' => $this->decorator->get_builder_protocol_context( 'prompt' ),
            'ai_scope_policy' => $this->decorator->get_ai_scope_policy( 'prompt' ),
            'ai_capabilities' => $this->decorator->get_ai_capabilities( 'prompt' ),
            'design_system' => $this->decorator->get_design_system_context( 'prompt' ),
            'content_models' => $this->decorator->get_content_model_context( 'prompt' ),
        );
    }

    /**
     * 构建规划阶段的候选模块描述。
     *
     * @param array<string,array<string,mixed>> $selected_modules 候选模块。
     * @return array<int,array<string,mixed>>
     */
    private function build_plan_candidate_modules( $selected_modules ) {
        $items = array();
        foreach ( $selected_modules as $module_id => $module_schema ) {
            $items[] = array(
                'id'        => $module_id,
                'name'      => isset( $module_schema['name'] ) ? (string) $module_schema['name'] : $module_id,
                'metadata'  => isset( $module_schema['metadata'] ) && is_array( $module_schema['metadata'] ) ? $module_schema['metadata'] : array(),
                'field_ids' => $this->extract_prompt_field_ids(
                    isset( $module_schema['fields'] ) && is_array( $module_schema['fields'] ) ? $module_schema['fields'] : array()
                ),
            );
        }

        return $items;
    }

    /**
     * 提取顶层字段 ID 列表用于规划上下文。
     *
     * @param array<int,array<string,mixed>> $fields 字段定义。
     * @return array<int,string>
     */
    private function extract_prompt_field_ids( $fields ) {
        $ids = array();
        foreach ( $fields as $field ) {
            if ( ! is_array( $field ) || empty( $field['id'] ) ) {
                continue;
            }

            $field_id = sanitize_key( (string) $field['id'] );
            if ( '' !== $field_id && ! in_array( $field_id, $ids, true ) ) {
                $ids[] = $field_id;
            }

            if ( count( $ids ) >= 12 ) {
                break;
            }
        }

        return $ids;
    }
}
