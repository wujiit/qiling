<?php
/**
 * Template Name: 招聘/职业机会
 * Template Post Type: page
 *
 * 招聘页面模板 - 用于展示公司招聘信息、职位列表和在线申请
 *
 * @package Developer_Starter
 */

get_header();

// 获取招聘设置
$careers_options = Developer_Starter\Core\Careers_Manager::get_option();
$hero_title = $careers_options['hero_title'] ?? get_the_title();
$hero_subtitle = $careers_options['hero_subtitle'] ?? '';
$stat_1_number = $careers_options['stat_1_number'] ?? '50+';
$stat_1_label = ! empty( $careers_options['stat_1_label'] ) ? sanitize_text_field( (string) $careers_options['stat_1_label'] ) : __( '团队成员', 'developer-starter' );
$stat_2_number = $careers_options['stat_2_number'] ?? '10+';
$stat_2_label = ! empty( $careers_options['stat_2_label'] ) ? sanitize_text_field( (string) $careers_options['stat_2_label'] ) : __( '开放职位', 'developer-starter' );
$stat_3_number = $careers_options['stat_3_number'] ?? '5个';
$stat_3_label = ! empty( $careers_options['stat_3_label'] ) ? sanitize_text_field( (string) $careers_options['stat_3_label'] ) : __( '办公城市', 'developer-starter' );
$benefits = $careers_options['benefits'] ?? array();
$enable_application = $careers_options['enable_application'] ?? '1';
$hero_bg_color = $careers_options['hero_bg_color'] ?? '';

// HR联系方式 - 优先使用招聘设置，否则使用主题设置
$hr_phone = ! empty( $careers_options['hr_phone'] ) ? $careers_options['hr_phone'] : developer_starter_get_option( 'company_phone', '' );
$hr_email = ! empty( $careers_options['hr_email'] ) ? $careers_options['hr_email'] : developer_starter_get_option( 'company_email', '' );

// 获取公司信息
$company_name = developer_starter_get_option( 'company_name', '' );
$address = developer_starter_get_option( 'company_address', '' );

// 获取职位列表
$positions = Developer_Starter\Core\Careers_Manager::get_positions();

// 福利图标映射
$benefit_icons = array(
    'money' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
    'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
    'book' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>',
    'calendar' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    'users' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
    'trending' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'heart' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>',
    'star' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
);

// 福利背景色
$benefit_colors = array(
    'money' => 'linear-gradient(135deg, var(--color-primary), var(--color-violet-600))',
    'shield' => 'linear-gradient(135deg, var(--color-success), var(--qiling-color-059669))',
    'book' => 'linear-gradient(135deg, var(--color-warning), var(--color-warning-dark))',
    'calendar' => 'linear-gradient(135deg, var(--qiling-color-ec4899), var(--qiling-color-be185d))',
    'users' => 'linear-gradient(135deg, var(--qiling-color-8b5cf6), var(--qiling-color-6d28d9))',
    'trending' => 'linear-gradient(135deg, var(--qiling-color-06b6d4), var(--qiling-color-0891b2))',
    'heart' => 'linear-gradient(135deg, var(--color-error-light), var(--color-error))',
    'star' => 'linear-gradient(135deg, var(--qiling-color-eab308), var(--qiling-color-ca8a04))',
);

// 职位类型和分类映射
$job_types = array( 'fulltime' => __( '全职', 'developer-starter' ), 'parttime' => __( '兼职', 'developer-starter' ), 'intern' => __( '实习', 'developer-starter' ) );
$categories = array( 'tech' => __( '技术研发', 'developer-starter' ), 'product' => __( '产品设计', 'developer-starter' ), 'market' => __( '市场运营', 'developer-starter' ), 'admin' => __( '职能管理', 'developer-starter' ) );
?>

<!-- Hero Banner -->
<?php 
$hero_style = '';
$hero_bg_color_normalized = strtolower( preg_replace( '/\s+/', '', (string) $hero_bg_color ) );
$empty_light_hero_colors = array(
    '#' . 'fff',
    '#' . 'ffffff',
    'white',
    sprintf( 'r' . 'gb(%1$d,%1$d,%1$d)', 255 ),
    sprintf( 'r' . 'gba(%1$d,%1$d,%1$d,1)', 255 ),
    'var(--color-neutral-0)',
    'var(--qiling-color-ffffff)',
    'transparent',
);
if ( ! empty( $hero_bg_color ) && ! in_array( $hero_bg_color_normalized, $empty_light_hero_colors, true ) ) {
    $hero_style = 'background: ' . esc_attr( $hero_bg_color ) . ';';
}
?>
<div class="careers-hero"<?php echo $hero_style ? ' style="' . $hero_style . '"' : ''; ?>>
    <div class="careers-hero-bg"></div>
    <div class="careers-hero-particles"></div>
    <div class="container">
        <div class="careers-hero-content">
            <span class="careers-badge">🚀 <?php esc_html_e( '加入我们', 'developer-starter' ); ?></span>
            <h1 class="careers-hero-title"><?php echo esc_html( $hero_title ); ?></h1>
            <?php if ( $hero_subtitle ) : ?>
                <p class="careers-hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
            <?php endif; ?>
            <div class="careers-hero-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html( $stat_1_number ); ?></span>
                    <span class="stat-label"><?php echo esc_html( $stat_1_label ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html( $stat_2_number ); ?></span>
                    <span class="stat-label"><?php echo esc_html( $stat_2_label ); ?></span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html( $stat_3_number ); ?></span>
                    <span class="stat-label"><?php echo esc_html( $stat_3_label ); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 公司福利 -->
<?php if ( ! empty( $benefits ) ) : ?>
<section class="careers-benefits section-padding">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title"><?php esc_html_e( '为什么选择我们？', 'developer-starter' ); ?></h2>
            <p class="section-subtitle"><?php esc_html_e( '我们提供具有竞争力的薪酬福利和广阔的发展空间', 'developer-starter' ); ?></p>
        </div>
        
        <div class="benefits-grid">
            <?php foreach ( $benefits as $idx => $benefit ) : 
                $icon_key = $benefit['icon'] ?? 'star';
                $icon_svg = $benefit_icons[ $icon_key ] ?? $benefit_icons['star'];
                $icon_bg = $benefit_colors[ $icon_key ] ?? $benefit_colors['star'];
            ?>
                <div class="benefit-card">
                    <div class="benefit-icon" style="background: <?php echo $icon_bg; ?>;">
                        <?php echo $icon_svg; ?>
                    </div>
                    <h3 class="benefit-title"><?php echo esc_html( $benefit['title'] ?? '' ); ?></h3>
                    <p class="benefit-desc"><?php echo esc_html( $benefit['desc'] ?? '' ); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 招聘职位 -->
<section class="careers-positions section-padding">
    <div class="container">
        <div class="section-header text-center">
            <h2 class="section-title"><?php esc_html_e( '开放职位', 'developer-starter' ); ?></h2>
            <p class="section-subtitle"><?php esc_html_e( '寻找你的理想职位，开启精彩职业旅程', 'developer-starter' ); ?></p>
        </div>
        
        <div class="positions-filter">
            <button class="filter-btn active" data-filter="all"><?php esc_html_e( '全部职位', 'developer-starter' ); ?></button>
            <?php foreach ( $categories as $cat_key => $cat_label ) : ?>
                <button class="filter-btn" data-filter="<?php echo esc_attr( $cat_key ); ?>"><?php echo esc_html( $cat_label ); ?></button>
            <?php endforeach; ?>
        </div>
        
        <div class="positions-list">
            <?php if ( empty( $positions ) ) : ?>
                <div class="no-positions" style="text-align: center; padding: var(--qiling-space-60) var(--qiling-space-20); background: var(--color-neutral-0); border-radius: var(--qiling-space-16);">
                    <p style="color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 1.1);"><?php esc_html_e( '暂无开放职位，请稍后再来查看', 'developer-starter' ); ?></p>
                </div>
            <?php else : ?>
                <?php foreach ( $positions as $pos ) : ?>
                    <div class="position-card" data-category="<?php echo esc_attr( $pos->category ); ?>">
                        <div class="position-header">
                            <div class="position-info">
                                <h3 class="position-title"><?php echo esc_html( $pos->title ); ?></h3>
                                <div class="position-meta">
                                    <span class="meta-item">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                        <?php echo esc_html( $pos->location ); ?>
                                    </span>
                                    <span class="meta-item">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
                                        <?php echo esc_html( $pos->department ); ?>
                                    </span>
                                    <span class="meta-item">
                                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                        <?php echo esc_html( $job_types[ $pos->job_type ] ?? __( '全职', 'developer-starter' ) ); ?>
                                    </span>
                                </div>
                            </div>
                            <?php if ( $pos->salary ) : ?>
                                <div class="position-salary"><?php echo esc_html( $pos->salary ); ?></div>
                            <?php endif; ?>
                            <button class="position-toggle">
                                <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </div>
                        <div class="position-details">
                            <?php if ( $pos->description ) : ?>
                                <div class="detail-section">
                                    <h4><?php esc_html_e( '职位描述', 'developer-starter' ); ?></h4>
                                    <ul>
                                        <?php foreach ( explode( "\n", $pos->description ) as $line ) : ?>
                                            <?php if ( trim( $line ) ) : ?>
                                                <li><?php echo esc_html( trim( $line ) ); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ( $pos->requirements ) : ?>
                                <div class="detail-section">
                                    <h4><?php esc_html_e( '任职要求', 'developer-starter' ); ?></h4>
                                    <ul>
                                        <?php foreach ( explode( "\n", $pos->requirements ) as $line ) : ?>
                                            <?php if ( trim( $line ) ) : ?>
                                                <li><?php echo esc_html( trim( $line ) ); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if ( $enable_application ) : ?>
                                <a href="#apply-form" class="btn btn-primary" data-position-id="<?php echo esc_attr( $pos->id ); ?>" data-position-title="<?php echo esc_attr( $pos->title ); ?>"><?php esc_html_e( '立即申请', 'developer-starter' ); ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 在线申请表单 -->
<?php if ( $enable_application ) : ?>
<section id="apply-form" class="careers-apply section-padding">
    <div class="container">
        <div class="apply-wrapper">
            <div class="apply-info">
                <h2><?php esc_html_e( '投递简历', 'developer-starter' ); ?></h2>
                <p><?php esc_html_e( '填写以下信息，我们会尽快与您联系', 'developer-starter' ); ?></p>
                
                <div class="apply-tips">
                    <h4><?php esc_html_e( '投递须知', 'developer-starter' ); ?></h4>
                    <ul>
                        <li><?php esc_html_e( '请确保联系方式真实有效', 'developer-starter' ); ?></li>
                        <li><?php esc_html_e( '简历投递后3-5个工作日内回复', 'developer-starter' ); ?></li>
                        <li><?php esc_html_e( '面试通过后签订正式劳动合同', 'developer-starter' ); ?></li>
                    </ul>
                </div>
                
                <?php if ( $hr_phone || $hr_email ) : ?>
                <div class="hr-contact">
                    <h4><?php esc_html_e( 'HR联系方式', 'developer-starter' ); ?></h4>
                    <?php if ( $hr_phone ) : ?>
                        <p><strong><?php esc_html_e( '电话：', 'developer-starter' ); ?></strong><?php echo esc_html( $hr_phone ); ?></p>
                    <?php endif; ?>
                    <?php if ( $hr_email ) : ?>
                        <p><strong><?php esc_html_e( '邮箱：', 'developer-starter' ); ?></strong><?php echo esc_html( $hr_email ); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="apply-form-container">
                <!-- 成功/失败提示弹窗 -->
                <div id="apply-toast" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; padding: var(--qiling-space-40) var(--qiling-space-60); border-radius: var(--qiling-space-16); text-align: center; box-shadow: 0 var(--qiling-space-25) var(--qiling-space-80) rgba(var(--qiling-rgb-0-0-0), 0.25);">
                    <div id="apply-toast-icon" style="font-size: calc(var(--qiling-font-size-base) * 4); margin-bottom: var(--qiling-space-15);"></div>
                    <div id="apply-toast-text" style="font-size: calc(var(--qiling-font-size-base) * 1.25); font-weight: 600;"></div>
                </div>
                <div id="apply-overlay" style="display: none; position: fixed; inset: 0; background: rgba(var(--qiling-rgb-0-0-0), 0.5); z-index: 9998;"></div>
                
                <form id="careers-apply-form" class="apply-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label><?php esc_html_e( '姓名', 'developer-starter' ); ?> <span class="required">*</span></label>
                            <input type="text" name="name" required placeholder="<?php esc_attr_e( '请输入您的姓名', 'developer-starter' ); ?>" />
                        </div>
                        <div class="form-group">
                            <label><?php esc_html_e( '电话', 'developer-starter' ); ?> <span class="required">*</span></label>
                            <input type="tel" name="phone" required placeholder="<?php esc_attr_e( '请输入联系电话', 'developer-starter' ); ?>" />
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label><?php esc_html_e( '邮箱', 'developer-starter' ); ?> <span class="required">*</span></label>
                            <input type="email" name="email" required placeholder="<?php esc_attr_e( '请输入电子邮箱', 'developer-starter' ); ?>" />
                        </div>
                        <div class="form-group">
                            <label><?php esc_html_e( '应聘职位', 'developer-starter' ); ?> <span class="required">*</span></label>
                            <select name="position_title" id="position-select" required>
                                <option value=""><?php esc_html_e( '请选择职位', 'developer-starter' ); ?></option>
                                <?php foreach ( $positions as $pos ) : ?>
                                    <option value="<?php echo esc_attr( $pos->title ); ?>" data-id="<?php echo esc_attr( $pos->id ); ?>">
                                        <?php echo esc_html( $pos->title ); ?>
                                    </option>
                                <?php endforeach; ?>
                                <option value="其他职位"><?php esc_html_e( '其他职位', 'developer-starter' ); ?></option>
                            </select>
                            <input type="hidden" name="position_id" id="position-id" value="0" />
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php esc_html_e( '自我介绍', 'developer-starter' ); ?></label>
                        <textarea name="message" rows="5" placeholder="<?php esc_attr_e( '请简要介绍您的教育背景、工作经验和核心技能...', 'developer-starter' ); ?>"></textarea>
                    </div>
                    
                    <input type="hidden" name="action" value="ds_submit_careers_application" />
                    <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'ds_careers_application_nonce' ) ); ?>" />
                    
                    <button type="submit" class="btn btn-primary btn-lg btn-submit">
                        <span class="btn-text"><?php esc_html_e( '提交申请', 'developer-starter' ); ?></span>
                        <span class="btn-loading" style="display: none;">
                            <svg width="20" height="20" viewBox="0 0 24 24" style="animation: spin 1s linear infinite;">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"/>
                            </svg>
                            <?php esc_html_e( '提交中...', 'developer-starter' ); ?>
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 公司地址 -->
<?php if ( $address || $company_name ) : ?>
<section class="careers-location" style="background: linear-gradient(135deg, var(--color-neutral-800) 0%, var(--color-neutral-900) 100%);">
    <div class="container">
        <div class="location-content">
            <div class="location-text">
                <h3><?php esc_html_e( '工作地点', 'developer-starter' ); ?></h3>
                <?php if ( $company_name ) : ?>
                    <p class="company-name"><?php echo esc_html( $company_name ); ?></p>
                <?php endif; ?>
                <?php if ( $address ) : ?>
                    <p class="company-address"><?php echo esc_html( $address ); ?></p>
                <?php endif; ?>
            </div>
            <div class="location-cta">
                <p><?php esc_html_e( '期待与你相见！', 'developer-starter' ); ?></p>
                <?php if ( $enable_application ) : ?>
                    <a href="#apply-form" class="btn btn-light btn-lg"><?php esc_html_e( '立即加入', 'developer-starter' ); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<style>
/* ===== Careers Hero ===== */
.careers-hero {
    position: relative;
    background:
        radial-gradient(circle at 12% 18%, rgba(var(--qiling-rgb-255-255-255), 0.22) 0%, rgba(var(--qiling-rgb-255-255-255), 0) 32%),
        radial-gradient(circle at 86% 16%, rgba(var(--color-success-rgb), 0.28) 0%, rgba(var(--color-success-rgb), 0) 34%),
        linear-gradient(135deg, var(--color-primary) 0%, var(--qiling-color-1e40af) 52%, var(--qiling-color-0f172a) 100%);
    padding: clamp(var(--qiling-space-100), var(--qiling-space-vw-10), var(--qiling-space-150)) 0 clamp(var(--qiling-space-72), var(--qiling-space-vw-8), var(--qiling-space-100));
    overflow: hidden;
    color: var(--color-text-inverse);
    isolation: isolate;
}

.careers-hero .container {
    position: relative;
    z-index: 2;
}

.careers-hero-bg {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 20% 20%, rgba(var(--qiling-rgb-255-255-255), 0.1) 0 2px, transparent 3px),
        radial-gradient(circle at 80% 40%, rgba(var(--qiling-rgb-255-255-255), 0.08) 0 3px, transparent 4px),
        radial-gradient(circle at 40% 80%, rgba(var(--qiling-rgb-255-255-255), 0.1) 0 2px, transparent 3px),
        radial-gradient(circle at 90% 90%, rgba(var(--qiling-rgb-255-255-255), 0.15) 0 1px, transparent 2px);
    animation: float 20s linear infinite;
}

.careers-hero-particles {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 30% 70%, rgba(var(--qiling-rgb-255-255-255), 0.1) 0%, transparent 50%),
                radial-gradient(circle at 70% 30%, rgba(var(--qiling-rgb-255-255-255), 0.08) 0%, transparent 40%);
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(1deg); }
}

.careers-hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
    color: var(--color-text-inverse);
}

.careers-badge {
    display: inline-block;
    background: rgba(var(--qiling-rgb-255-255-255), 0.16);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.24);
    padding: var(--qiling-space-8) var(--qiling-space-20);
    border-radius: 30px;
    font-size: calc(var(--qiling-font-size-base) * 0.9);
    font-weight: 500;
    margin-bottom: var(--qiling-space-20);
    animation: fadeInUp 0.6s ease;
    color: var(--color-text-inverse);
}

.careers-hero-title {
    font-size: calc(var(--qiling-font-size-base) * 3.5);
    font-weight: 800;
    max-width: var(--qiling-measure-760);
    margin: 0 auto var(--qiling-space-20);
    color: var(--color-text-inverse);
    text-shadow: 0 4px 20px rgba(var(--qiling-rgb-0-0-0), 0.2);
    animation: fadeInUp 0.6s ease 0.1s both;
}

.careers-hero-subtitle {
    font-size: calc(var(--qiling-font-size-base) * 1.25);
    color: var(--qiling-color-rgba-255-255-255-082);
    max-width: var(--qiling-measure-600);
    margin: 0 auto var(--qiling-space-40);
    animation: fadeInUp 0.6s ease 0.2s both;
}

.careers-hero-stats {
    display: flex;
    justify-content: center;
    gap: var(--qiling-space-60);
    animation: fadeInUp 0.6s ease 0.3s both;
}

.stat-item {
    text-align: center;
    min-width: var(--qiling-measure-140);
    padding: var(--qiling-space-18) var(--qiling-space-22);
    border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.18);
    border-radius: 18px;
    background: rgba(var(--qiling-rgb-255-255-255), 0.1);
    backdrop-filter: blur(14px);
    box-shadow: 0 18px 42px rgba(var(--qiling-rgb-15-23-42), 0.18);
}

.stat-number {
    display: block;
    font-size: calc(var(--qiling-font-size-base) * 2.5);
    font-weight: 800;
    color: var(--color-text-inverse);
}

.stat-label {
    font-size: calc(var(--qiling-font-size-base) * 0.9);
    color: var(--qiling-color-rgba-255-255-255-072);
}

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* ===== Benefits Section ===== */
.benefits-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: var(--qiling-space-30);
}

.benefit-card {
    background: var(--color-neutral-0);
    border-radius: 20px;
    padding: var(--qiling-space-40) var(--qiling-space-30);
    text-align: center;
    box-shadow: 0 10px 40px rgba(var(--qiling-rgb-0-0-0), 0.06);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.benefit-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 60px rgba(var(--qiling-rgb-0-0-0), 0.12);
}

.benefit-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, var(--color-primary), var(--color-violet-600));
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto var(--qiling-space-20);
    color: var(--color-neutral-0);
}

.benefit-icon svg {
    width: 32px;
    height: 32px;
}

.benefit-title {
    font-size: calc(var(--qiling-font-size-base) * 1.25);
    margin: 0 0 var(--qiling-space-10);
    color: var(--color-neutral-800);
}

.benefit-desc {
    color: var(--color-neutral-500);
    font-size: calc(var(--qiling-font-size-base) * 0.95);
    margin: 0;
    line-height: 1.6;
}

/* ===== Positions Section ===== */
.careers-positions {
    background: var(--color-neutral-50);
}

.positions-filter {
    display: flex;
    justify-content: center;
    gap: var(--qiling-space-12);
    margin-bottom: var(--qiling-space-40);
    flex-wrap: wrap;
}

.filter-btn {
    padding: var(--qiling-space-10) var(--qiling-space-24);
    border: 2px solid var(--color-neutral-200);
    background: var(--color-neutral-0);
    border-radius: 30px;
    font-size: calc(var(--qiling-font-size-base) * 0.95);
    font-weight: 500;
    color: var(--color-neutral-500);
    cursor: pointer;
    transition: all 0.3s;
}

.filter-btn:hover,
.filter-btn.active {
    background: var(--color-primary);
    border-color: var(--color-primary);
    color: var(--color-neutral-0);
}

.positions-list {
    max-width: var(--qiling-measure-900);
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: var(--qiling-space-20);
}

.position-card {
    background: var(--color-neutral-0);
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(var(--qiling-rgb-0-0-0), 0.06);
    transition: all 0.3s;
}

.position-card:hover {
    box-shadow: 0 10px 40px rgba(var(--qiling-rgb-0-0-0), 0.1);
}

.position-header {
    display: flex;
    align-items: center;
    padding: var(--qiling-space-25) var(--qiling-space-30);
    gap: var(--qiling-space-20);
    cursor: pointer;
}

.position-info {
    flex: 1;
}

.position-title {
    font-size: calc(var(--qiling-font-size-base) * 1.25);
    margin: 0 0 var(--qiling-space-10);
    color: var(--color-neutral-800);
}

.position-meta {
    display: flex;
    gap: var(--qiling-space-20);
    flex-wrap: wrap;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: var(--qiling-space-6);
    color: var(--color-neutral-500);
    font-size: calc(var(--qiling-font-size-base) * 0.9);
}

.meta-item svg {
    opacity: 0.7;
}

.position-salary {
    background: linear-gradient(135deg, var(--qiling-color-fef3c7), var(--qiling-color-fde68a));
    color: var(--qiling-color-92400e);
    padding: var(--qiling-space-8) var(--qiling-space-16);
    border-radius: 8px;
    font-weight: 600;
    font-size: calc(var(--qiling-font-size-base) * 1.1);
}

.position-toggle {
    width: 40px;
    height: 40px;
    border: none;
    background: var(--color-neutral-100);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s;
    color: var(--color-neutral-500);
}

.position-toggle:hover {
    background: var(--color-primary);
    color: var(--color-neutral-0);
}

.position-card.expanded .position-toggle {
    transform: rotate(180deg);
}

.position-details {
    display: none;
    padding: 0 var(--qiling-space-30) var(--qiling-space-30);
    border-top: 1px solid var(--color-neutral-100);
    animation: slideDown 0.3s ease;
}

.position-card.expanded .position-details {
    display: block;
}

@keyframes slideDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.detail-section {
    margin-bottom: var(--qiling-space-25);
}

.detail-section h4 {
    font-size: var(--qiling-font-size-base);
    color: var(--color-neutral-800);
    margin: var(--qiling-space-20) 0 var(--qiling-space-15);
}

.detail-section ul {
    margin: 0;
    padding-left: var(--qiling-space-20);
    color: var(--color-neutral-500);
}

.detail-section li {
    margin-bottom: var(--qiling-space-8);
    line-height: 1.6;
}

/* ===== Apply Section ===== */
.careers-apply {
    background: linear-gradient(180deg, var(--color-neutral-50) 0%, var(--color-neutral-0) 100%);
}

.apply-wrapper {
    display: grid;
    grid-template-columns: 1fr 1.5fr;
    gap: var(--qiling-space-60);
    max-width: var(--qiling-measure-1000);
    margin: 0 auto;
}

.apply-info h2 {
    font-size: calc(var(--qiling-font-size-base) * 2);
    margin: 0 0 var(--qiling-space-10);
    color: var(--color-neutral-800);
}

.apply-info > p {
    color: var(--color-neutral-500);
    margin: 0 0 var(--qiling-space-30);
}

.apply-tips {
    background: linear-gradient(135deg, var(--qiling-color-eff6ff), var(--qiling-color-dbeafe));
    border-radius: 16px;
    padding: var(--qiling-space-25);
    margin-bottom: var(--qiling-space-25);
}

.apply-tips h4 {
    margin: 0 0 var(--qiling-space-15);
    color: var(--color-primary);
    font-size: var(--qiling-font-size-base);
}

.apply-tips ul {
    margin: 0;
    padding-left: var(--qiling-space-20);
    color: var(--qiling-color-1e40af);
}

.apply-tips li {
    margin-bottom: var(--qiling-space-8);
}

.hr-contact {
    background: var(--color-neutral-50);
    border-radius: 16px;
    padding: var(--qiling-space-25);
}

.hr-contact h4 {
    margin: 0 0 var(--qiling-space-15);
    color: var(--color-neutral-800);
    font-size: var(--qiling-font-size-base);
}

.hr-contact p {
    margin: 0 0 var(--qiling-space-8);
    color: var(--color-neutral-500);
}

.apply-form-container {
    background: var(--color-neutral-0);
    border-radius: 24px;
    padding: var(--qiling-space-40);
    box-shadow: 0 20px 60px rgba(var(--qiling-rgb-0-0-0), 0.08);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--qiling-space-20);
}

.form-group {
    margin-bottom: var(--qiling-space-20);
}

.form-group label {
    display: block;
    margin-bottom: var(--qiling-space-8);
    font-weight: 500;
    color: var(--color-neutral-700);
}

.form-group .required {
    color: var(--color-error-light);
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: var(--qiling-space-14) var(--qiling-space-18);
    border: 2px solid var(--color-neutral-200);
    border-radius: 12px;
    font-size: var(--qiling-font-size-base);
    transition: all 0.3s;
    font-family: inherit;
    box-sizing: border-box;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 4px rgba(var(--color-primary-rgb), 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 120px;
}

.btn-submit {
    width: 100%;
    padding: var(--qiling-space-16) var(--qiling-space-32);
    font-size: calc(var(--qiling-font-size-base) * 1.1);
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--qiling-space-10);
}

.btn-submit:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(var(--color-primary-rgb), 0.3);
}

/* ===== Location Section ===== */
.careers-location {
    padding: var(--qiling-space-60) 0;
    color: var(--color-neutral-0);
    background:
        radial-gradient(circle at 10% 10%, rgba(var(--color-primary-rgb), 0.22) 0%, rgba(var(--color-primary-rgb), 0) 34%),
        linear-gradient(135deg, var(--color-neutral-800) 0%, var(--color-neutral-900) 100%) !important;
}

.location-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--qiling-space-40);
    padding: var(--qiling-space-36);
    border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.1);
    border-radius: 24px;
    background: rgba(var(--qiling-rgb-255-255-255), 0.04);
    box-shadow: 0 24px 56px rgba(var(--qiling-rgb-15-23-42), 0.28);
}

.location-text h3 {
    font-size: calc(var(--qiling-font-size-base) * 1.5);
    margin: 0 0 var(--qiling-space-15);
    color: var(--color-text-inverse);
}

.company-name {
    font-size: calc(var(--qiling-font-size-base) * 1.25);
    margin: 0 0 var(--qiling-space-8);
    color: var(--color-text-inverse);
}

.company-address {
    color: var(--qiling-color-rgba-255-255-255-072);
    margin: 0;
}

.location-cta {
    text-align: center;
}

.location-cta p {
    margin: 0 0 var(--qiling-space-15);
    font-size: calc(var(--qiling-font-size-base) * 1.1);
    color: var(--color-text-inverse);
}

.careers-location .btn-light {
    background: var(--color-text-inverse);
    color: var(--color-primary);
    border-color: var(--color-text-inverse);
    box-shadow: 0 12px 28px rgba(var(--qiling-rgb-15-23-42), 0.22);
}

.careers-location .btn-light:hover {
    background: var(--qiling-color-rgba-255-255-255-082);
    border-color: var(--qiling-color-rgba-255-255-255-082);
    color: var(--color-primary);
}

/* ===== Responsive ===== */
@media (max-width: 992px) {
    .benefits-grid {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .apply-wrapper {
        grid-template-columns: 1fr;
        gap: var(--qiling-space-40);
    }
    
    .careers-hero-stats {
        gap: var(--qiling-space-40);
    }
}

@media (max-width: 768px) {
    .careers-hero {
        padding: var(--qiling-space-100) 0 var(--qiling-space-60);
    }
    
    .careers-hero-title {
        font-size: calc(var(--qiling-font-size-base) * 2);
    }
    
    .careers-hero-subtitle {
        font-size: var(--qiling-font-size-base);
    }
    
    .careers-hero-stats {
        flex-wrap: wrap;
        gap: var(--qiling-space-30);
    }
    
    .stat-number {
        font-size: calc(var(--qiling-font-size-base) * 2);
    }
    
    .benefits-grid {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .position-header {
        flex-wrap: wrap;
        gap: var(--qiling-space-15);
    }
    
    .position-salary {
        order: -1;
    }
    
    .location-content {
        flex-direction: column;
        text-align: center;
    }
    
    .apply-form-container {
        padding: var(--qiling-space-25);
    }
    
    #apply-toast {
        padding: var(--qiling-space-30) var(--qiling-space-40) !important;
        width: 85% !important;
    }
}

@keyframes spin {
    100% { transform: rotate(360deg); }
}

/* ========================================
   Dark Mode Support
   ======================================== */
html.dark-mode .careers-positions {
    background: var(--color-neutral-900);
}

html.dark-mode .careers-apply {
    background: linear-gradient(180deg, var(--color-neutral-900) 0%, var(--color-neutral-800) 100%);
}

html.dark-mode .benefit-card,
html.dark-mode .position-card,
html.dark-mode .apply-form-container {
    background: var(--color-neutral-800);
    box-shadow: none;
    border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.05);
}

html.dark-mode .benefit-card:hover,
html.dark-mode .position-card:hover {
    background: var(--color-neutral-700);
}

html.dark-mode .benefit-title,
html.dark-mode .position-title,
html.dark-mode .detail-section h4,
html.dark-mode .apply-info h2,
html.dark-mode .hr-contact h4,
html.dark-mode .form-group label {
    color: var(--color-neutral-100);
}

html.dark-mode .benefit-desc,
html.dark-mode .meta-item,
html.dark-mode .detail-section ul,
html.dark-mode .apply-info > p,
html.dark-mode .hr-contact p {
    color: var(--color-neutral-300);
}

html.dark-mode .no-positions {
    background: var(--color-neutral-800) !important;
}

html.dark-mode .filter-btn {
    background: var(--color-neutral-800);
    border-color: var(--color-neutral-700);
    color: var(--color-neutral-400);
}

html.dark-mode .filter-btn:hover,
html.dark-mode .filter-btn.active {
    border-color: var(--color-primary);
    background: var(--color-primary);
    color: var(--color-neutral-0);
}

html.dark-mode .position-toggle {
    background: rgba(var(--qiling-rgb-255-255-255), 0.1);
    color: var(--color-neutral-300);
}

html.dark-mode .position-toggle:hover {
    background: var(--color-primary);
    color: var(--color-neutral-0);
}

html.dark-mode .position-details {
    border-top-color: rgba(var(--qiling-rgb-255-255-255), 0.1);
}

html.dark-mode .apply-tips {
    background: rgba(var(--color-primary-rgb), 0.1);
}

html.dark-mode .apply-tips h4,
html.dark-mode .apply-tips ul {
    color: var(--qiling-color-60a5fa);
}

html.dark-mode .hr-contact {
    background: var(--color-neutral-800);
    border: 1px solid rgba(var(--qiling-rgb-255-255-255), 0.05);
}

html.dark-mode .form-group input,
html.dark-mode .form-group select,
html.dark-mode .form-group textarea {
    background: var(--color-neutral-900);
    border-color: var(--color-neutral-700);
    color: var(--color-neutral-100);
}

html.dark-mode .form-group input:focus,
html.dark-mode .form-group select:focus,
html.dark-mode .form-group textarea:focus {
    border-color: var(--color-primary);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 职位筛选
    var filterBtns = document.querySelectorAll('.filter-btn');
    var positionCards = document.querySelectorAll('.position-card');
    
    filterBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var filter = this.getAttribute('data-filter');
            
            filterBtns.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            
            positionCards.forEach(function(card) {
                if (filter === 'all' || card.getAttribute('data-category') === filter) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
    
    // 职位展开/收起
    var positionHeaders = document.querySelectorAll('.position-header');
    positionHeaders.forEach(function(header) {
        header.addEventListener('click', function() {
            var card = this.closest('.position-card');
            card.classList.toggle('expanded');
        });
    });
    
    // 职位选择同步
    var positionSelect = document.getElementById('position-select');
    var positionIdInput = document.getElementById('position-id');
    if (positionSelect && positionIdInput) {
        positionSelect.addEventListener('change', function() {
            var selected = this.options[this.selectedIndex];
            positionIdInput.value = selected.getAttribute('data-id') || 0;
        });
    }
    
    // 点击申请按钮时选中对应职位
    document.querySelectorAll('.position-details .btn-primary').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            var positionTitle = this.getAttribute('data-position-title');
            var positionId = this.getAttribute('data-position-id');
            if (positionSelect && positionTitle) {
                for (var i = 0; i < positionSelect.options.length; i++) {
                    if (positionSelect.options[i].value === positionTitle) {
                        positionSelect.selectedIndex = i;
                        if (positionIdInput) {
                            positionIdInput.value = positionId || 0;
                        }
                        break;
                    }
                }
            }
        });
    });
    
    // 表单提交
    var applyForm = document.getElementById('careers-apply-form');
    if (applyForm) {
        applyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var form = this;
            var btnText = form.querySelector('.btn-text');
            var btnLoading = form.querySelector('.btn-loading');
            var submitBtn = form.querySelector('.btn-submit');
            var toast = document.getElementById('apply-toast');
            var overlay = document.getElementById('apply-overlay');
            var toastIcon = document.getElementById('apply-toast-icon');
            var toastText = document.getElementById('apply-toast-text');
            
            // 显示加载状态
            submitBtn.disabled = true;
            btnText.style.display = 'none';
            btnLoading.style.display = 'inline-flex';
            
            var formData = new FormData(form);
            
            fetch(<?php echo wp_json_encode( esc_url_raw( admin_url( 'admin-ajax.php' ) ) ); ?>, {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                overlay.style.display = 'block';
                toast.style.display = 'block';
                
                if (data.success) {
                    toast.style.background = 'var(--qiling-color-dcfce7)';
                    toastIcon.innerHTML = '✅';
                    toastText.innerHTML = '<span style="color:var(--qiling-color-166534);">' + data.data.message + '</span>';
                    form.reset();
                } else {
                    toast.style.background = 'var(--qiling-color-fee2e2)';
                    toastIcon.innerHTML = '❌';
                    toastText.innerHTML = '<span style="color:var(--qiling-color-991b1b);">' + (data.data ? data.data.message : '<?php echo esc_js( __( '提交失败，请稍后重试', 'developer-starter' ) ); ?>') + '</span>';
                }
                
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                
                setTimeout(function() {
                    toast.style.display = 'none';
                    overlay.style.display = 'none';
                }, 2500);
            })
            .catch(function(err) {
                console.error('Form error:', err);
                overlay.style.display = 'block';
                toast.style.display = 'block';
                toast.style.background = 'var(--qiling-color-fee2e2)';
                toastIcon.innerHTML = '❌';
                toastText.innerHTML = '<span style="color:var(--qiling-color-991b1b);"><?php echo esc_js( __( '网络错误，请稍后重试', 'developer-starter' ) ); ?></span>';
                
                submitBtn.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
                
                setTimeout(function() {
                    toast.style.display = 'none';
                    overlay.style.display = 'none';
                }, 2500);
            });
        });
    }
    
    // 点击遮罩关闭弹窗
    var applyOverlay = document.getElementById('apply-overlay');
    if (applyOverlay) {
        applyOverlay.addEventListener('click', function() {
            this.style.display = 'none';
            document.getElementById('apply-toast').style.display = 'none';
        });
    }
});
</script>

<?php get_footer(); ?>
