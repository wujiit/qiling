<?php
/**
 * Template Name: 关于我们
 * Template Post Type: page
 *
 * @package Developer_Starter
 */

// 加载关于我们页面专用样式
add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'developer-starter-about',
        DEVELOPER_STARTER_ASSETS . '/css/about.css',
        array( 'developer-starter-main' ),
        developer_starter_get_assets_version()
    );
}, 20 );

get_header();

// Get settings
$show_timeline = developer_starter_get_option( 'about_show_timeline', '' );
$show_team = developer_starter_get_option( 'about_show_team', '' );
$show_certificates = developer_starter_get_option( 'about_show_certificates', '' );
$show_environment = developer_starter_get_option( 'about_show_environment', '' );
$show_culture = developer_starter_get_option( 'about_show_culture', '' );

$timeline_items = developer_starter_get_option( 'timeline_items', array() );
$team_members = developer_starter_get_option( 'team_members', array() );
$certificates = developer_starter_get_option( 'about_certificates', array() );
$environment = developer_starter_get_option( 'about_environment', array() );
$culture = developer_starter_get_option( 'about_culture', array() );

// 构建Tab数据
$tabs = array();
$tabs['intro'] = __( '公司简介', 'developer-starter' );
if ( $show_timeline && ! empty( $timeline_items ) ) $tabs['timeline'] = __( '发展历程', 'developer-starter' );
if ( $show_team && ! empty( $team_members ) ) $tabs['team'] = __( '团队成员', 'developer-starter' );
if ( $show_certificates && ! empty( $certificates ) ) $tabs['certificates'] = __( '资质荣誉', 'developer-starter' );
if ( $show_environment && ! empty( $environment ) ) $tabs['environment'] = __( '公司环境', 'developer-starter' );
if ( $show_culture && ! empty( $culture ) ) $tabs['culture'] = __( '企业文化', 'developer-starter' );

$has_tabs = count( $tabs ) > 1;
?>

<?php \Developer_Starter\Core\Page_Header::render( 'default' ); ?>

<div class="page-content section-padding">
    <div class="container">
        
        <?php if ( $has_tabs ) : ?>
        <!-- Tab 导航 -->
        <div class="about-tabs-wrapper" data-aos="fade-up">
            <div class="about-tabs">
                <?php $first = true; foreach ( $tabs as $key => $label ) : ?>
                    <button class="about-tab-btn <?php echo $first ? 'active' : ''; ?>" data-tab="<?php echo $key; ?>">
                        <?php echo esc_html( $label ); ?>
                    </button>
                <?php $first = false; endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Tab 内容区域 -->
        <div class="about-tab-panels">
            
            <!-- 公司简介 -->
            <div class="about-tab-content <?php echo isset( $tabs['intro'] ) ? 'active' : ''; ?>" data-panel="intro">
                <?php while ( have_posts() ) : the_post(); ?>
                    <?php 
                    $modules = function_exists( 'developer_starter_get_page_modules_data' )
                        ? developer_starter_get_page_modules_data( get_the_ID() )
                        : get_post_meta( get_the_ID(), '_developer_starter_modules', true );
                    if ( ! empty( $modules ) && is_array( $modules ) ) :
                        developer_starter_render_page_modules(); 
                    else :
                    ?>
                        <div class="about-intro" data-aos="fade-up">
                            <div class="entry-content">
                                <?php the_content(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endwhile; ?>
            </div>
            
            <?php if ( isset( $tabs['timeline'] ) ) : ?>
            <!-- 发展历程 -->
            <div class="about-tab-content" data-panel="timeline">
                <div class="timeline" style="max-width: var(--qiling-measure-800); margin: 0 auto; position: relative;" data-aos="fade-up">
                    <div class="timeline-line" style="position: absolute; left: 50%; top: 0; bottom: 0; width: 2px; background: linear-gradient(to bottom, var(--color-primary), var(--color-violet-600));"></div>
                    
                    <?php foreach ( $timeline_items as $idx => $item ) : 
                        $year = isset( $item['year'] ) ? $item['year'] : '';
                        $title = isset( $item['title'] ) ? $item['title'] : '';
                        $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                        $is_left = $idx % 2 === 0;
                    ?>
                        <div class="timeline-item" style="display: flex; align-items: center; margin-bottom: var(--qiling-space-40); <?php echo $is_left ? '' : 'flex-direction: row-reverse;'; ?>" data-aos="fade-<?php echo $is_left ? 'right' : 'left'; ?>" data-aos-delay="<?php echo $idx * 100; ?>">
                            <div class="timeline-content" style="flex: 1; padding: var(--qiling-space-30); background: var(--color-neutral-0); border-radius: 16px; box-shadow: 0 10px 40px rgba(var(--qiling-rgb-0-0-0), 0.08); <?php echo $is_left ? 'margin-right: var(--qiling-space-50); text-align: right;' : 'margin-left: var(--qiling-space-50);'; ?>">
                                <span style="display: inline-block; background: linear-gradient(135deg, var(--color-primary), var(--color-violet-600)); color: var(--color-neutral-0); padding: var(--qiling-space-5) var(--qiling-space-15); border-radius: 20px; font-weight: 600; margin-bottom: var(--qiling-space-10);"><?php echo esc_html( $year ); ?></span>
                                <h3 style="font-size: calc(var(--qiling-font-size-base) * 1.25); margin-bottom: var(--qiling-space-10);"><?php echo esc_html( $title ); ?></h3>
                                <p style="color: var(--color-neutral-500); margin: 0;"><?php echo esc_html( $desc ); ?></p>
                            </div>
                            <div class="timeline-dot" style="width: 20px; height: 20px; background: var(--color-primary); border-radius: 50%; border: 4px solid var(--color-neutral-0); box-shadow: 0 0 0 4px var(--color-primary); position: relative; z-index: 1;"></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ( isset( $tabs['team'] ) ) : ?>
            <!-- 团队成员 -->
            <div class="about-tab-content" data-panel="team">
                <div class="team-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: var(--qiling-space-30);">
                    <?php foreach ( $team_members as $idx => $member ) : 
                        $name = isset( $member['name'] ) ? $member['name'] : '';
                        $position = isset( $member['position'] ) ? $member['position'] : '';
                        $avatar = isset( $member['avatar'] ) ? $member['avatar'] : '';
                        $desc = isset( $member['desc'] ) ? $member['desc'] : '';
                    ?>
                        <div class="team-member" style="background: var(--color-neutral-0); border-radius: 20px; overflow: hidden; box-shadow: 0 15px 50px rgba(var(--qiling-rgb-0-0-0), 0.1); text-align: center;" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                            <div class="member-avatar" style="padding: var(--qiling-space-30) var(--qiling-space-30) 0;">
                                <?php if ( $avatar ) : ?>
                                    <img src="<?php echo esc_url( $avatar ); ?>" alt="<?php echo esc_attr( $name ); ?>" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid var(--color-primary);" />
                                <?php else : ?>
                                    <div style="width: 120px; height: 120px; border-radius: 50%; background: linear-gradient(135deg, var(--color-primary), var(--color-violet-600)); margin: 0 auto; display: flex; align-items: center; justify-content: center; color: var(--color-neutral-0); font-size: calc(var(--qiling-font-size-base) * 2.5); font-weight: 600;">
                                        <?php echo mb_substr( $name, 0, 1 ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="member-info" style="padding: var(--qiling-space-20) var(--qiling-space-30) var(--qiling-space-30);">
                                <h3 style="font-size: calc(var(--qiling-font-size-base) * 1.25); margin-bottom: var(--qiling-space-5);"><?php echo esc_html( $name ); ?></h3>
                                <p style="color: var(--color-primary); font-weight: 500; margin-bottom: var(--qiling-space-15);"><?php echo esc_html( $position ); ?></p>
                                <p style="color: var(--color-neutral-500); font-size: calc(var(--qiling-font-size-base) * 0.9); line-height: 1.6;"><?php echo esc_html( $desc ); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ( isset( $tabs['certificates'] ) ) : ?>
            <!-- 资质荣誉 -->
            <div class="about-tab-content" data-panel="certificates">
                <div class="about-gallery">
                    <?php foreach ( $certificates as $idx => $cert ) : 
                        $image = isset( $cert['image'] ) ? $cert['image'] : '';
                        $title = isset( $cert['title'] ) ? $cert['title'] : '';
                    ?>
                        <div class="about-gallery-item" data-aos="fade-up" data-aos-delay="<?php echo $idx * 50; ?>" onclick="openAboutLightbox('<?php echo esc_url( $image ); ?>', '<?php echo esc_attr( $title ); ?>')">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                            <?php else : ?>
                                <div class="about-gallery-placeholder">📜</div>
                            <?php endif; ?>
                            <?php if ( $title ) : ?>
                                <div class="gallery-title"><?php echo esc_html( $title ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ( isset( $tabs['environment'] ) ) : ?>
            <!-- 公司环境 -->
            <div class="about-tab-content" data-panel="environment">
                <div class="about-gallery">
                    <?php foreach ( $environment as $idx => $env ) : 
                        $image = isset( $env['image'] ) ? $env['image'] : '';
                        $title = isset( $env['title'] ) ? $env['title'] : '';
                    ?>
                        <div class="about-gallery-item" data-aos="fade-up" data-aos-delay="<?php echo $idx * 50; ?>" onclick="openAboutLightbox('<?php echo esc_url( $image ); ?>', '<?php echo esc_attr( $title ); ?>')">
                            <?php if ( $image ) : ?>
                                <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $title ); ?>" />
                            <?php else : ?>
                                <div class="about-gallery-placeholder">🏢</div>
                            <?php endif; ?>
                            <?php if ( $title ) : ?>
                                <div class="gallery-title"><?php echo esc_html( $title ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ( isset( $tabs['culture'] ) ) : ?>
            <!-- 企业文化 -->
            <div class="about-tab-content" data-panel="culture">
                <div class="about-culture-grid">
                    <?php foreach ( $culture as $idx => $item ) : 
                        $icon_raw = isset( $item['icon'] ) ? trim( $item['icon'] ) : '💡';
                        $title = isset( $item['title'] ) ? $item['title'] : '';
                        $desc = isset( $item['desc'] ) ? $item['desc'] : '';
                        
                        // 图标仅走当前主题统一图标 helper
                        $icon = trim( $icon_raw );
                    ?>
                        <div class="about-culture-card" data-aos="fade-up" data-aos-delay="<?php echo $idx * 100; ?>">
                            <div class="culture-icon">
                                <?php echo developer_starter_get_icon_html( $icon ); ?>
                            </div>
                            <h3 class="culture-title"><?php echo esc_html( $title ); ?></h3>
                            <p class="culture-desc"><?php echo nl2br( esc_html( $desc ) ); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</div>

<?php
// Contact Info Section
$company_name = developer_starter_get_option( 'company_name', '' );
$phone = developer_starter_get_option( 'company_phone', '' );
$qq = developer_starter_get_option( 'company_qq', '' );
$qq_link = function_exists( 'developer_starter_get_qq_contact_link' ) ? developer_starter_get_qq_contact_link( $qq ) : '';
$wechat_qrcode = developer_starter_get_option( 'company_wechat_qrcode', '' );
$email = developer_starter_get_option( 'company_email', '' );
$address = developer_starter_get_option( 'company_address', '' );
$working_hours = developer_starter_get_option( 'company_working_hours', '' );
$phone_link = $phone ? preg_replace( '/[^\d+]/', '', (string) $phone ) : '';

if ( $company_name || $phone || $qq || $wechat_qrcode || $email || $address || $working_hours ) :
?>
<section class="about-info section-padding">
    <div class="container">
        <div class="about-contact-shell">
            <div class="section-header about-contact-header text-center">
                <h2 class="section-title"><?php esc_html_e( '联系方式', 'developer-starter' ); ?></h2>
            </div>

            <div class="about-contact-list">
                <?php if ( $company_name ) : ?>
                    <div class="about-contact-item">
                        <span class="about-contact-icon about-contact-icon-company" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( '公司名称', 'developer-starter' ); ?></span>
                            <strong class="about-contact-value"><?php echo esc_html( $company_name ); ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $phone ) : ?>
                    <div class="about-contact-item">
                        <span class="about-contact-icon about-contact-icon-phone" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.78 19.78 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.78 19.78 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.35 1.9.65 2.81a2 2 0 0 1-.45 2.11L8.04 9.91a16 16 0 0 0 6.05 6.05l1.27-1.27a2 2 0 0 1 2.11-.45c.91.3 1.85.52 2.81.65A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( '联系电话', 'developer-starter' ); ?></span>
                            <?php if ( $phone_link ) : ?>
                                <a class="about-contact-value" href="tel:<?php echo esc_attr( $phone_link ); ?>"><?php echo esc_html( $phone ); ?></a>
                            <?php else : ?>
                                <strong class="about-contact-value"><?php echo esc_html( $phone ); ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $qq ) : ?>
                    <div class="about-contact-item">
                        <span class="about-contact-icon about-contact-icon-qq" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 10V6a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v6a2 2 0 0 1-2 2h-1.5L13 17v-3h-3a2 2 0 0 1-2-2Z"/><path d="M8 8H6a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h1.5L11 21v-3"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( 'QQ', 'developer-starter' ); ?></span>
                            <?php if ( $qq_link ) : ?>
                                <a class="about-contact-value" href="<?php echo esc_url( $qq_link ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( $qq ); ?></a>
                            <?php else : ?>
                                <strong class="about-contact-value"><?php echo esc_html( $qq ); ?></strong>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $email ) : ?>
                    <div class="about-contact-item">
                        <span class="about-contact-icon about-contact-icon-email" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( '电子邮箱', 'developer-starter' ); ?></span>
                            <a class="about-contact-value" href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $address ) : ?>
                    <div class="about-contact-item">
                        <span class="about-contact-icon about-contact-icon-address" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 5.5-8 12-8 12S4 15.5 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( '公司地址', 'developer-starter' ); ?></span>
                            <strong class="about-contact-value"><?php echo esc_html( $address ); ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $working_hours ) : ?>
                    <div class="about-contact-item">
                        <span class="about-contact-icon about-contact-icon-hours" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( '工作时间', 'developer-starter' ); ?></span>
                            <strong class="about-contact-value"><?php echo esc_html( $working_hours ); ?></strong>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ( $wechat_qrcode ) : ?>
                    <div class="about-contact-item about-contact-item-wechat">
                        <span class="about-contact-icon about-contact-icon-wechat" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8.69 2.19C3.89 2.19 0 5.48 0 9.53c0 2.21 1.17 4.2 3 5.55a.59.59 0 0 1 .21.66l-.39 1.48c-.02.07-.05.14-.05.22 0 .16.13.29.29.29a.33.33 0 0 0 .17-.05l1.91-1.11a.86.86 0 0 1 .71-.1 10.16 10.16 0 0 0 2.84.4c.28 0 .55-.03.81-.05-.86-2.58.16-4.97 1.93-6.45 1.7-1.41 3.88-1.98 5.85-1.84-.57-3.58-4.19-6.34-8.59-6.34Zm-2.9 5.98a1.17 1.17 0 1 0 0-2.35 1.17 1.17 0 0 0 0 2.35Zm5.81 0a1.17 1.17 0 1 0 0-2.35 1.17 1.17 0 0 0 0 2.35Zm5.34.72c-1.8-.05-3.75.51-5.28 1.79-1.72 1.43-2.69 3.72-1.78 6.22.94 2.45 3.67 4.23 6.88 4.23.83 0 1.62-.12 2.36-.34a.72.72 0 0 1 .6.08l1.58.93a.27.27 0 0 0 .14.04c.13 0 .24-.11.24-.25 0-.06-.02-.12-.04-.18l-.33-1.23a.58.58 0 0 1-.02-.16.49.49 0 0 1 .2-.4C23.02 18.48 24 16.82 24 14.98c0-3.21-2.93-5.84-7.06-6.09Zm-2.04 3.81a.98.98 0 1 1 0-1.96.98.98 0 0 1 0 1.96Zm4.85 0a.98.98 0 1 1 0-1.96.98.98 0 0 1 0 1.96Z"/></svg>
                        </span>
                        <div class="about-contact-body">
                            <span class="about-contact-label"><?php esc_html_e( '微信', 'developer-starter' ); ?></span>
                            <div class="about-contact-qr">
                                <img src="<?php echo esc_url( $wechat_qrcode ); ?>" alt="<?php esc_attr_e( '微信二维码', 'developer-starter' ); ?>" loading="lazy" decoding="async" />
                                <span><?php esc_html_e( '扫码添加微信', 'developer-starter' ); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 灯箱 -->
<div class="about-lightbox" id="aboutLightbox" onclick="closeAboutLightbox()">
    <button class="lightbox-close" onclick="closeAboutLightbox()">&times;</button>
    <img src="" alt="" id="lightboxImage" onclick="event.stopPropagation()" />
    <div class="lightbox-title" id="lightboxTitle"></div>
</div>

<script>
(function() {
    // Tab 切换
    var tabs = document.querySelectorAll('.about-tab-btn');
    var panels = document.querySelectorAll('.about-tab-content');
    
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.getAttribute('data-tab');
            
            // 更新Tab状态
            tabs.forEach(function(t) { t.classList.remove('active'); });
            this.classList.add('active');
            
            // 更新内容面板
            panels.forEach(function(p) {
                if (p.getAttribute('data-panel') === target) {
                    p.classList.add('active');
                } else {
                    p.classList.remove('active');
                }
            });
            
            // 重新触发AOS动画
            if (typeof AOS !== 'undefined') {
                AOS.refresh();
            }
        });
    });
})();

// 灯箱功能
function openAboutLightbox(src, title) {
    if (!src) return;
    document.getElementById('lightboxImage').src = src;
    document.getElementById('lightboxTitle').textContent = title || '';
    document.getElementById('aboutLightbox').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeAboutLightbox() {
    document.getElementById('aboutLightbox').classList.remove('active');
    document.body.style.overflow = '';
}

// ESC 键关闭灯箱
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAboutLightbox();
});
</script>

<?php get_footer(); ?>
