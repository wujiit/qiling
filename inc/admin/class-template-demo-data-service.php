<?php
/**
 * 启灵行业模版演示数据服务类 (Template Demo Data Service)
 *
 * 负责在导入官方行业模版时生成标准、可编辑、可删除的示例内容，
 * 并提供全站演示数据的一键彻底清理能力。
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Template_Demo_Data_Service {

    const META_KEY_DEMO_DATA = '_qiling_is_demo_data';
    const META_KEY_TEMPLATE  = '_qiling_demo_template';
    const META_KEY_PAGE_ID   = '_qiling_demo_page_id';

    /**
     * 获取全站当前已生成的演示数据总数
     *
     * @return int
     */
    public static function get_demo_data_count() {
        $query = new \WP_Query( array(
            'post_type'      => array( 'post', 'page', 'product' ),
            'post_status'    => array( 'publish', 'draft', 'pending' ),
            'meta_key'       => self::META_KEY_DEMO_DATA,
            'meta_value'     => '1',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => false,
        ) );

        return (int) $query->found_posts;
    }

    /**
     * 一键彻底清理全站所有演示数据（连带媒体占位，不移入回收站直接永久清除）
     *
     * @return int 清理的演示文章数量
     */
    public static function clear_all_demo_data() {
        $query = new \WP_Query( array(
            'post_type'      => array( 'post', 'page', 'product' ),
            'post_status'    => 'any',
            'meta_key'       => self::META_KEY_DEMO_DATA,
            'meta_value'     => '1',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
        ) );

        $count = 0;
        if ( ! empty( $query->posts ) ) {
            foreach ( $query->posts as $post_id ) {
                $deleted = wp_delete_post( $post_id, true );
                if ( $deleted ) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * 根据模版行业类型自动生成 3~4 篇精选示例文章
     *
     * @param string $template_slug 模版文件名或标识
     * @param int    $page_id       当前创建的页面 ID
     * @param string $page_title    当前页面标题
     * @return int 生成的示例内容数量
     */
    public static function generate_demo_data_for_template( $template_slug, $page_id = 0, $page_title = '' ) {
        $slug_lower = strtolower( (string) $template_slug );
        $recipes    = self::resolve_demo_recipes_by_template( $slug_lower, $page_title );

        $created_count = 0;
        $current_user_id = get_current_user_id() ?: 1;

        foreach ( $recipes as $recipe ) {
            $cat_name = ! empty( $recipe['category'] ) ? $recipe['category'] : __( '行业精选案例', 'qiling' );
            $cat_id   = self::ensure_category_exists( $cat_name );

            $post_data = array(
                'post_type'    => 'post',
                'post_status'  => 'publish',
                'post_title'   => sanitize_text_field( $recipe['title'] ),
                'post_content' => $recipe['content'],
                'post_excerpt' => isset( $recipe['excerpt'] ) ? sanitize_text_field( $recipe['excerpt'] ) : '',
                'post_author'  => $current_user_id,
            );

            if ( $cat_id > 0 ) {
                $post_data['post_category'] = array( $cat_id );
            }

            if ( ! empty( $recipe['tags'] ) && is_array( $recipe['tags'] ) ) {
                $post_data['tags_input'] = $recipe['tags'];
            }

            $new_post_id = wp_insert_post( $post_data );
            if ( ! is_wp_error( $new_post_id ) && $new_post_id > 0 ) {
                update_post_meta( $new_post_id, self::META_KEY_DEMO_DATA, '1' );
                update_post_meta( $new_post_id, self::META_KEY_TEMPLATE, $template_slug );
                if ( $page_id > 0 ) {
                    update_post_meta( $new_post_id, self::META_KEY_PAGE_ID, $page_id );
                }
                $created_count++;
            }
        }

        return $created_count;
    }

    /**
     * 确保分类存在，若不存在则安全新建
     *
     * @param string $category_name 分类名称
     * @return int 分类 ID
     */
    private static function ensure_category_exists( $category_name ) {
        $category = get_term_by( 'name', $category_name, 'category' );
        if ( $category instanceof \WP_Term ) {
            return (int) $category->term_id;
        }

        $res = wp_insert_term( $category_name, 'category' );
        if ( ! is_wp_error( $res ) && isset( $res['term_id'] ) ) {
            return (int) $res['term_id'];
        }

        return 0;
    }

    /**
     * 根据模版名称与行业特性匹配精细化的行业文章文案
     *
     * @param string $slug 模版 slug
     * @param string $page_title 页面标题
     * @return array<int, array<string, mixed>>
     */
    private static function resolve_demo_recipes_by_template( $slug, $page_title ) {
        // 1. 法律、律所、法务合规
        if ( false !== strpos( $slug, 'law' ) || false !== strpos( $slug, 'legal' ) ) {
            return array(
                array(
                    'title'    => '涉案标的超五千万元重大建设工程施工合同纠纷胜诉纪实',
                    'category' => '经典诉讼判例',
                    'tags'     => array( '建设工程', '合同纠纷', '胜诉判决' ),
                    'excerpt'  => '本案涉及多层转包、工程量争议及违约金核算等复杂法律关系，律师团队通过穿透式质证为客户挽回全部预期利益。',
                    'content'  => '<p class="lead">在当前工程建设与商业地产深度调整期，重大施工合同纠纷往往涉及复杂的转包、挂靠与签证认定等核心争议。本案涉案标的逾5,000万元，历经一审、重审，团队最终实现全案胜诉。</p><h3>核心办案焦点与策略</h3><ul><li><strong>工程量穿透审查：</strong>针对发包方提出的超期索赔与隐蔽工程量异议，团队调取近三年全套往来签证原始底单并申请司法审计。</li><li><strong>阻断违约责任归咎：</strong>精准论证发包方顺延工期事件成立，阻断对方关于工期延误违约金的主张。</li></ul><p>该案的圆满判决，不仅为当事人挽回了近五千万元巨额工程款，更树立了类似疑难建设工程争议解决的示范判例。</p>',
                ),
                array(
                    'title'    => '企业出海跨国经营：数据合规与知识产权壁垒化解实务指引',
                    'category' => '跨境合规观察',
                    'tags'     => array( '企业出海', '数据合规', '知识产权' ),
                    'excerpt'  => '梳理中资企业在东南亚、北美与欧盟展业过程中的隐私合规审查、反海外腐败及专利防御性布局。',
                    'content'  => '<p class="lead">全球经济格局重塑背景下，中国企业的出海进程正从单纯的“产品输出”迈入“产能与品牌全球化”阶段。面对欧美严苛的监管要求，前置性合规审查至关重要。</p><h3>企业跨国架构搭建的三大防线</h3><ol><li><strong>数据跨境流动合规：</strong>符合我国《数据出境安全评估办法》及欧盟 GDPR 双向监管标准；</li><li><strong>专利池与商标预先抢位：</strong>在目标国提早完成防御性申请，防范恶意抢注与 NPE 专利流氓恶意诉讼。</li></ol>',
                ),
                array(
                    'title'    => '陈明远 资深高级合伙人 / 国际商事仲裁委员会仲裁员',
                    'category' => '合伙人专家团队',
                    'tags'     => array( '合伙人律师', '商事仲裁', '公司法务' ),
                    'excerpt'  => '执业逾 18 年，擅长大型央国企重大资产重组、破产重整以及跨国投资争议解决，曾代理多起标的数十亿元标杆案件。',
                    'content'  => '<p>陈明远律师毕业于中国政法大学，获法学硕士学位，拥有中华人民共和国律师执业资格及证券从业资格。长期受聘担任多家大型上市公司和金融机构的常年法律顾问。</p><p><strong>专业领域：</strong>企业并购重组、疑难民商事诉讼、知识产权防御、破产重组清算。</p>',
                ),
            );
        }

        // 2. 医疗健康、口腔门诊、医美诊所
        if ( false !== strpos( $slug, 'dental' ) || false !== strpos( $slug, 'clinic' ) || false !== strpos( $slug, 'medical' ) || false !== strpos( $slug, 'healthcare' ) || false !== strpos( $slug, 'beauty' ) ) {
            return array(
                array(
                    'title'    => '全数字化微创精准种植牙技术体系临床应用与长期骨结合分析',
                    'category' => '前沿诊疗项目',
                    'tags'     => array( '微创种植', '数字化导板', '口腔健康' ),
                    'excerpt'  => '基于德国高精度 CBCT 与 3D 打印导板引导，实现种植体轴向角度与咬合力的精密控制，术后创口小、愈合周期大幅缩短。',
                    'content'  => '<p class="lead">数字化口腔种植彻底颠覆了传统“盲种”和切口翻瓣方式。通过三维颌骨建模，种植体植入误差控制在亚毫米级，显著降低了术后疼痛与神经损伤概率。</p><h3>核心技术优势</h3><ul><li><strong>3D 导航微创切口：</strong>告别传统大切口，仅需 3~4mm 环形切口，出血量减少 80%；</li><li><strong>即刻负重与早期愈合：</strong>结合亲水性种植体表面处理，骨结合效率提升近一倍。</li></ul>',
                ),
                array(
                    'title'    => '赵立新 主任医师 / 口腔种植与颌面美学修复学科带头人',
                    'category' => '专家医疗团队',
                    'tags'     => array( '主任医师', '种植专科', '学科带头人' ),
                    'excerpt'  => '原三甲口腔医院种植科主任，从医 22 年，累计完成复杂牙槽骨增量及疑难全口种植逾 8,000 例。',
                    'content'  => '<p>赵立新主任系中华口腔医学会种植专委会委员，国际口腔种植学会（ITI）学者。擅长上颌窦内/外提升、即拔即种即刻修复以及颌骨缺损修复重建。</p><p><strong>诊疗理念：</strong>医者仁心，以微创技术守护每一颗牙齿的健康与自然咀嚼质感。</p>',
                ),
                array(
                    'title'    => '成人牙齿隐形矫正全周期管理：微笑美学线与咬合功能重建',
                    'category' => '美学正畸科普',
                    'tags'     => array( '隐形矫正', '微笑美学', '正畸科普' ),
                    'excerpt'  => '正畸不仅仅是排齐前牙，更关乎颞下颌关节的健康与面部侧貌下三分之一的美学协调。',
                    'content'  => '<p>很多成年人认为矫正只是青少年时期的事，实际上牙齿移动依赖的是牙周膜的生物学改建，无论任何年龄只要牙周健康均可进行矫治。</p>',
                ),
            );
        }

        // 3. 人工智能、科技、SaaS、软件与机器人
        if ( false !== strpos( $slug, 'ai' ) || false !== strpos( $slug, 'saas' ) || false !== strpos( $slug, 'software' ) || false !== strpos( $slug, 'robot' ) || false !== strpos( $slug, 'tech' ) || false !== strpos( $slug, 'data' ) ) {
            return array(
                array(
                    'title'    => '面向大语言模型的高性能向量检索与企业级私有化知识库构建方案',
                    'category' => '前沿技术架构',
                    'tags'     => array( 'LLM', '向量数据库', '企业私有化' ),
                    'excerpt'  => '解决企业专有数据安全顾虑，通过混合检索策略（BM25 + Dense Retrieval）与重排序模型大幅削减大模型幻觉。',
                    'content'  => '<p class="lead">在大模型落地产业应用的过程中，数据主权与回答精准度是企业客户首要关切。本方案通过全栈自研私有化部署架构，为数十家行业客户筑牢数据安全防火墙。</p><h3>技术架构三大支柱</h3><ul><li><strong>混合检索链路：</strong>倒排索引与高维向量相似度联合打分，命中率提升至 94.6%；</li><li><strong>动态分块与上下文增强：</strong>自适应语义切分杜绝割裂关键信息。</li></ul>',
                ),
                array(
                    'title'    => '某大型高端装备制造集团智能产线数字孪生系统实战交付',
                    'category' => '标杆落地案例',
                    'tags'     => array( '数字孪生', '智能制造', '工业物联网' ),
                    'excerpt'  => '联动 1,200 余个工业传感器点位，毫秒级模拟整线运转节拍，预测性维护使设备非计划停机率下降 42%。',
                    'content'  => '<p class="lead">工业4.0时代，实时感知与闭环调度是智能工厂的核心。通过构建 1:1 三维数字孪生底座，企业管理者可实现全厂能耗、工单进度与故障预警的透明掌控。</p>',
                ),
                array(
                    'title'    => '企业级智能协同平台 2026 年度白皮书：AI Agent 驱动的流程重构',
                    'category' => '行业白皮书',
                    'tags'     => array( 'AI Agent', '数字化转型', '效率革命' ),
                    'excerpt'  => '深度剖析多智能体（Multi-Agent）在财务审核、合规风控、客群分析与敏捷开发中的范式转移。',
                    'content'  => '<p>随着自主规划智能体的成熟，传统的规则型 RPA 流程正加速向目标导向的自治协作演进，企业人均产出效能将迎来质的飞跃。</p>',
                ),
            );
        }

        // 4. 跨境电商、物流、供应链、外贸
        if ( false !== strpos( $slug, 'ecommerce' ) || false !== strpos( $slug, 'logistics' ) || false !== strpos( $slug, 'trade' ) || false !== strpos( $slug, 'warehouse' ) ) {
            return array(
                array(
                    'title'    => '欧美主要港口头程海运与自营海外仓极速一件代发网络布局',
                    'category' => '全球物流方案',
                    'tags'     => array( '海外仓', '一件代发', '头程专线' ),
                    'excerpt'  => '洛杉矶、鹿特丹核心枢纽仓配联动，本地派送订单 24 小时出库率超 99.8%，助力独立站与平台大卖降本增效。',
                    'content'  => '<p class="lead">全球电商时效竞逐进入深水区。通过头程集运与海外本地仓配一体化，跨境卖家不仅能压缩干线运费，更能大幅提升买家五星好评率与复购率。</p>',
                ),
                array(
                    'title'    => '年出货量超 300 万件头部服饰品牌柔性跨境供应链整合实战',
                    'category' => '大客户案例',
                    'tags'     => array( '跨境出海', '供应链整合', '物流优化' ),
                    'excerpt'  => '针对快时尚品类高频小单、多频次补货特征，量身定制动态分仓与智能补货算法，综合物流成本降低 18.5%。',
                    'content'  => '<p>快时尚跨境出海的核心生命线在于库存周转。我们通过自研 WMS/TMS 数字化中台，将客户海外端安全库存天数从 45 天稳健缩短至 24 天。</p>',
                ),
                array(
                    'title'    => '2026 全球主要口岸绿色清关合规指南与关税筹划策略',
                    'category' => '外贸干货百科',
                    'tags'     => array( '目的港清关', '关税筹划', '外贸合规' ),
                    'excerpt'  => '解析最新原产地规则、反倾销税目避险以及欧美碳关税（CBAM）应对要点，保障合规快速通关。',
                    'content'  => '<p>随着全球各口岸查验力度的趋严，合规申报与规范单证已成为企业出海防范扣关、罚没的核心保障。</p>',
                ),
            );
        }

        // 5. 通用企业 / 品牌官网精选预设
        $display_title = ! empty( $page_title ) ? $page_title : __( '企业核心业务', 'qiling' );
        return array(
            array(
                'title'    => sprintf( '守正笃行，精工细作：深入践行%s领域高品质交付承诺', $display_title ),
                'category' => '企业核心动态',
                'tags'     => array( '品质交付', '企业精神', '核心实力' ),
                'excerpt'  => '秉承匠心品质与长期主义价值观，公司构建了从前期需求调研、方案设计到全生命周期维护的严密标准。',
                'content'  => '<p class="lead">面对瞬息万变的市场竞争，唯有对专业与品质的坚守能够跨越周期。公司始终以客户成功为导向，依托深厚的行业积淀与成熟的组织能力，持续交付超出客户预期的专业解决方案。</p><h3>我们的品质管控铁律</h3><ul><li><strong>全程闭环品控：</strong>每个项目均配备专职质检与交付专家，严格执行阶段性评审；</li><li><strong>响应速度领先：</strong>建立 7×24 小时应急响应机制，为客户业务保驾护航。</li></ul>',
            ),
            array(
                'title'    => sprintf( '赋能标杆客户：某行业百强客户定制化项目成功落地纪实', $display_title ),
                'category' => '行业标杆案例',
                'tags'     => array( '标杆项目', '客户口碑', '交付成果' ),
                'excerpt'  => '深度对接客户复杂痛点，历经 6 个月攻坚交付，帮助客户实现效率提升 35%、综合运营成本下降 20%。',
                'content'  => '<p class="lead">任何成功的合作都始于对业务本质的深刻理解。在本次深度合作中，团队深入客户一线调研，量身定制贴合实际场景的整体落地规划，赢得了客户高管的高度赞誉。</p>',
            ),
            array(
                'title'    => '面向未来的可持续发展战略：公司发布 2026 全新业务规划布局',
                'category' => '战略与发展',
                'tags'     => array( '战略布局', '创新赋能', '行业趋势' ),
                'excerpt'  => '加大自主创新研发投入，携手生态合作伙伴共同开拓更广阔的产业协同空间。',
                'content'  => '<p>未来，公司将继续坚定不移地以技术创新为第一驱动力，以绿色低碳为发展底色，为各领域客户创造更加持久、深度的商业与社会价值。</p>',
            ),
        );
    }
}
