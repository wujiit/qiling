<?php
/**
 * GitHub Activity Module.
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Modules\Modules;

use Developer_Starter\Core\GitHub_Repository_Activity_Service;
use Developer_Starter\Modules\Module_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class GitHub_Activity_Module extends Module_Base {

    public function __construct() {
        $this->category    = 'software';
        $this->icon        = 'dashicons-share-alt2';
        $this->description = __( '展示公开 GitHub 仓库的 Stars、Forks、最新 Release 和 Commit 动态', 'developer-starter' );
    }

    public function get_id() {
        return 'github_activity';
    }

    public function get_name() {
        return __( 'GitHub 项目动态', 'developer-starter' );
    }

    public function get_fields() {
        return array(
            array( 'id' => 'gha_title', 'label' => __( '模块标题', 'developer-starter' ), 'type' => 'text', 'default' => __( 'GitHub 项目动态', 'developer-starter' ) ),
            array( 'id' => 'gha_subtitle', 'label' => __( '模块副标题', 'developer-starter' ), 'type' => 'text', 'default' => __( '实时展示公开仓库的社区数据、版本发布和最近提交。', 'developer-starter' ) ),
            array(
                'id'      => 'gha_enable',
                'label'   => __( '启用 GitHub 项目展示', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'no'  => __( '关闭', 'developer-starter' ),
                    'yes' => __( '开启', 'developer-starter' ),
                ),
                'default' => 'no',
                'description' => __( '关闭时不会访问 GitHub API，也不会生成本地缓存文件。', 'developer-starter' ),
            ),
            array( 'id' => 'gha_repository_url', 'label' => __( 'GitHub 仓库地址', 'developer-starter' ), 'type' => 'text', 'default' => '', 'description' => __( '填写公开仓库地址，如 https://github.com/owner/repo。当前版本不需要 Token。', 'developer-starter' ) ),
            array(
                'id'      => 'gha_show_stats',
                'label'   => __( '显示 Star / Fork 数据', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'gha_show_release',
                'label'   => __( '显示最新 Release', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array(
                'id'      => 'gha_show_commits',
                'label'   => __( '显示 Commit 动态', 'developer-starter' ),
                'type'    => 'select',
                'options' => array(
                    'yes' => __( '显示', 'developer-starter' ),
                    'no'  => __( '隐藏', 'developer-starter' ),
                ),
                'default' => 'yes',
            ),
            array( 'id' => 'gha_commit_count', 'label' => __( 'Commit 数量', 'developer-starter' ), 'type' => 'number', 'default' => '5', 'description' => __( '建议 3-6 条，最多 10 条。', 'developer-starter' ) ),
            array( 'id' => 'gha_cache_hours', 'label' => __( '缓存时间（小时）', 'developer-starter' ), 'type' => 'number', 'default' => '6', 'description' => __( '缓存写入服务器 uploads/qiling/github-activity 本地 JSON 文件，不写入数据库。', 'developer-starter' ) ),
            array( 'id' => 'gha_bg_color', 'label' => __( '背景颜色', 'developer-starter' ), 'type' => 'color', 'default' => '#f8fafc', 'description' => __( '支持 CSS 颜色或渐变。', 'developer-starter' ) ),
            array( 'id' => 'gha_padding_top', 'label' => __( '上边距', 'developer-starter' ), 'type' => 'text', 'default' => '80px' ),
            array( 'id' => 'gha_padding_bottom', 'label' => __( '下边距', 'developer-starter' ), 'type' => 'text', 'default' => '80px' ),
        );
    }

    public function render( $data = array() ) {
        $enabled = isset( $data['gha_enable'] ) && 'yes' === (string) $data['gha_enable'];
        if ( ! $enabled ) {
            if ( current_user_can( 'edit_pages' ) ) {
                $this->render_disabled_state();
            }
            return;
        }

        $repository_url = isset( $data['gha_repository_url'] ) ? trim( (string) $data['gha_repository_url'] ) : '';
        if ( '' === $repository_url ) {
            if ( current_user_can( 'edit_pages' ) ) {
                $this->render_empty_state();
            }
            return;
        }

        $title         = isset( $data['gha_title'] ) ? (string) $data['gha_title'] : __( 'GitHub 项目动态', 'developer-starter' );
        $subtitle      = isset( $data['gha_subtitle'] ) ? (string) $data['gha_subtitle'] : '';
        $show_stats    = ! isset( $data['gha_show_stats'] ) || 'no' !== (string) $data['gha_show_stats'];
        $show_release  = ! isset( $data['gha_show_release'] ) || 'no' !== (string) $data['gha_show_release'];
        $show_commits  = ! isset( $data['gha_show_commits'] ) || 'no' !== (string) $data['gha_show_commits'];
        $commit_count  = isset( $data['gha_commit_count'] ) ? absint( $data['gha_commit_count'] ) : 5;
        $cache_hours   = isset( $data['gha_cache_hours'] ) ? absint( $data['gha_cache_hours'] ) : 6;
        $bg_color      = isset( $data['gha_bg_color'] ) ? $this->clean_css_value( $data['gha_bg_color'] ) : '#f8fafc';
        $padding_top   = isset( $data['gha_padding_top'] ) && '' !== $data['gha_padding_top'] ? $this->clean_css_value( $data['gha_padding_top'] ) : '80px';
        $padding_bottom = isset( $data['gha_padding_bottom'] ) && '' !== $data['gha_padding_bottom'] ? $this->clean_css_value( $data['gha_padding_bottom'] ) : '80px';

        $service = new GitHub_Repository_Activity_Service();
        $activity = $service->get_activity(
            $repository_url,
            array(
                'ttl'          => max( 1, min( 168, $cache_hours ) ) * HOUR_IN_SECONDS,
                'commit_count' => $commit_count,
            )
        );

        $repository = isset( $activity['repository'] ) && is_array( $activity['repository'] ) ? $activity['repository'] : array();
        if ( empty( $repository ) ) {
            $this->render_error_state( isset( $activity['error'] ) ? $activity['error'] : '' );
            return;
        }

        $section_style = 'padding-top:' . $padding_top . ';padding-bottom:' . $padding_bottom . ';';
        if ( '' !== $bg_color ) {
            $section_style .= false !== strpos( $bg_color, 'gradient' ) ? 'background:' . $bg_color . ';' : 'background-color:' . $bg_color . ';';
        }

        $module_id = 'github-activity-' . uniqid();
        $release   = isset( $activity['latest_release'] ) && is_array( $activity['latest_release'] ) ? $activity['latest_release'] : null;
        $commits   = isset( $activity['commits'] ) && is_array( $activity['commits'] ) ? $activity['commits'] : array();
        ?>
        <section class="module module-github-activity qiling-github-activity" id="<?php echo esc_attr( $module_id ); ?>" style="<?php echo esc_attr( $section_style ); ?>">
            <div class="container">
                <div class="qga-header">
                    <div>
                        <span class="qga-kicker"><?php esc_html_e( 'GitHub', 'developer-starter' ); ?></span>
                        <?php if ( $title ) : ?>
                            <h2 class="qga-title"><?php echo esc_html( $title ); ?></h2>
                        <?php endif; ?>
                        <?php if ( $subtitle ) : ?>
                            <p class="qga-subtitle"><?php echo esc_html( $subtitle ); ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ( ! empty( $repository['html_url'] ) ) : ?>
                        <a class="qga-repo-link" href="<?php echo esc_url( $repository['html_url'] ); ?>" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( isset( $repository['full_name'] ) ? $repository['full_name'] : '' ); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <div class="qga-layout">
                    <div class="qga-main">
                        <div class="qga-repo-card">
                            <div class="qga-repo-name"><?php echo esc_html( isset( $repository['full_name'] ) ? $repository['full_name'] : '' ); ?></div>
                            <?php if ( ! empty( $repository['description'] ) ) : ?>
                                <p class="qga-repo-desc"><?php echo esc_html( $repository['description'] ); ?></p>
                            <?php endif; ?>
                            <div class="qga-pills">
                                <?php if ( ! empty( $repository['language'] ) ) : ?>
                                    <span><?php echo esc_html( $repository['language'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $repository['license'] ) ) : ?>
                                    <span><?php echo esc_html( $repository['license'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $repository['default_branch'] ) ) : ?>
                                    <span><?php echo esc_html( $repository['default_branch'] ); ?></span>
                                <?php endif; ?>
                                <?php if ( ! empty( $activity['fetched_at'] ) ) : ?>
                                    <span><?php echo esc_html( sprintf( __( '更新于 %s', 'developer-starter' ), date_i18n( 'Y-m-d H:i', (int) $activity['fetched_at'] ) ) ); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ( $show_stats ) : ?>
                            <div class="qga-stats">
                                <?php $this->render_stat( __( 'Stars', 'developer-starter' ), isset( $repository['stars'] ) ? $repository['stars'] : 0 ); ?>
                                <?php $this->render_stat( __( 'Forks', 'developer-starter' ), isset( $repository['forks'] ) ? $repository['forks'] : 0 ); ?>
                                <?php $this->render_stat( __( 'Issues', 'developer-starter' ), isset( $repository['open_issues'] ) ? $repository['open_issues'] : 0 ); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ( $show_release ) : ?>
                        <div class="qga-release">
                            <div class="qga-card-label"><?php esc_html_e( 'Latest Release', 'developer-starter' ); ?></div>
                            <?php if ( $release ) : ?>
                                <a class="qga-release-version" href="<?php echo esc_url( $release['html_url'] ); ?>" target="_blank" rel="noopener noreferrer">
                                    <?php echo esc_html( $release['tag_name'] ? $release['tag_name'] : $release['name'] ); ?>
                                </a>
                                <?php if ( ! empty( $release['name'] ) && $release['name'] !== $release['tag_name'] ) : ?>
                                    <div class="qga-release-name"><?php echo esc_html( $release['name'] ); ?></div>
                                <?php endif; ?>
                                <?php if ( ! empty( $release['published_at'] ) ) : ?>
                                    <div class="qga-muted"><?php echo esc_html( date_i18n( 'Y-m-d', strtotime( $release['published_at'] ) ) ); ?></div>
                                <?php endif; ?>
                            <?php else : ?>
                                <div class="qga-muted"><?php esc_html_e( '暂无公开 Release', 'developer-starter' ); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ( $show_commits ) : ?>
                    <div class="qga-commits">
                        <div class="qga-card-label"><?php esc_html_e( 'Recent Commits', 'developer-starter' ); ?></div>
                        <?php if ( ! empty( $commits ) ) : ?>
                            <?php foreach ( $commits as $commit ) : ?>
                                <a class="qga-commit" href="<?php echo esc_url( isset( $commit['html_url'] ) ? $commit['html_url'] : '' ); ?>" target="_blank" rel="noopener noreferrer">
                                    <span class="qga-commit-sha"><?php echo esc_html( isset( $commit['sha'] ) ? $commit['sha'] : '' ); ?></span>
                                    <span class="qga-commit-message"><?php echo esc_html( isset( $commit['message'] ) ? $commit['message'] : '' ); ?></span>
                                    <span class="qga-commit-meta">
                                        <?php
                                        $meta = array();
                                        if ( ! empty( $commit['author'] ) ) {
                                            $meta[] = $commit['author'];
                                        }
                                        if ( ! empty( $commit['date'] ) ) {
                                            $meta[] = date_i18n( 'Y-m-d', strtotime( $commit['date'] ) );
                                        }
                                        echo esc_html( implode( ' · ', $meta ) );
                                        ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="qga-muted"><?php esc_html_e( '暂无可展示 Commit。', 'developer-starter' ); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <?php if ( current_user_can( 'edit_pages' ) && ! empty( $activity['error'] ) ) : ?>
                    <div class="qga-admin-note"><?php echo esc_html( $activity['error'] ); ?></div>
                <?php endif; ?>
            </div>
        </section>
        <?php
    }

    /**
     * @param string $label Stat label.
     * @param int    $value Stat value.
     * @return void
     */
    private function render_stat( $label, $value ) {
        ?>
        <div class="qga-stat">
            <div class="qga-stat-value"><?php echo esc_html( number_format_i18n( absint( $value ) ) ); ?></div>
            <div class="qga-stat-label"><?php echo esc_html( $label ); ?></div>
        </div>
        <?php
    }

    /**
     * @return void
     */
    private function render_empty_state() {
        ?>
        <section class="module module-github-activity qiling-github-activity" style="padding:60px 0;background:#f8fafc;">
            <div class="container">
                <div class="qga-empty"><?php esc_html_e( '请在模块设置中填写 GitHub 仓库地址。', 'developer-starter' ); ?></div>
            </div>
        </section>
        <?php
    }

    /**
     * @return void
     */
    private function render_disabled_state() {
        ?>
        <section class="module module-github-activity qiling-github-activity" style="padding:60px 0;background:#f8fafc;">
            <div class="container">
                <div class="qga-empty"><?php esc_html_e( 'GitHub 项目展示未启用；开启后才会访问 GitHub API 并生成本地缓存。', 'developer-starter' ); ?></div>
            </div>
        </section>
        <?php
    }

    /**
     * @param string $message Error message.
     * @return void
     */
    private function render_error_state( $message ) {
        if ( ! current_user_can( 'edit_pages' ) ) {
            return;
        }
        ?>
        <section class="module module-github-activity qiling-github-activity" style="padding:60px 0;background:#f8fafc;">
            <div class="container">
                <div class="qga-empty"><?php echo esc_html( $message ? $message : __( 'GitHub 数据暂时无法加载。', 'developer-starter' ) ); ?></div>
            </div>
        </section>
        <?php
    }

    /**
     * @param string $value CSS value.
     * @return string
     */
    private function clean_css_value( $value ) {
        $value = trim( wp_strip_all_tags( (string) $value ) );
        return str_replace( array( ';', '{', '}' ), '', $value );
    }

}
