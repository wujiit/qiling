<?php
/**
 * Careers Manager Class - 招聘管理系统
 *
 * @package Developer_Starter
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Careers_Manager {

    const TABLE_VERSION = '1.1.0';
    const TABLE_VERSION_OPTION = 'developer_starter_careers_table_version';
    const TABLE_MIGRATION_LOCK = 'developer_starter_careers_table_migration_lock';

    private $positions_table;
    private $applications_table;
    private $option_name = 'developer_starter_careers_options';

    /**
     * 检查招聘模块是否启用。
     *
     * @return bool
     */
    public static function is_enabled() {
        $options = get_option( 'developer_starter_careers_options', array() );
        if ( ! is_array( $options ) ) {
            return true;
        }

        if ( ! array_key_exists( 'module_enabled', $options ) ) {
            return true;
        }

        return (string) $options['module_enabled'] === '1';
    }

    public function __construct() {
        global $wpdb;
        $this->positions_table = $wpdb->prefix . 'ds_careers_positions';
        $this->applications_table = $wpdb->prefix . 'ds_careers_applications';
        
        // 数据表创建
        add_action( 'after_switch_theme', array( $this, 'install_tables' ), 10, 0 );
        add_action( 'admin_init', array( $this, 'maybe_create_tables' ) );
        
        $admin_menu_enabled = ! function_exists( 'developer_starter_is_careers_admin_menu_enabled' )
            || developer_starter_is_careers_admin_menu_enabled();

        if ( $admin_menu_enabled ) {
            // 后台菜单
            add_action( 'admin_menu', array( $this, 'add_admin_menus' ), 25 );
            
            // 注册设置
            add_action( 'admin_init', array( $this, 'register_settings' ) );
        }
        
        // AJAX处理
        add_action( 'wp_ajax_ds_submit_careers_application', array( $this, 'handle_application_submit' ) );
        add_action( 'wp_ajax_nopriv_ds_submit_careers_application', array( $this, 'handle_application_submit' ) );
        
        if ( $admin_menu_enabled ) {
            // 加载后台脚本
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        }
    }

    /**
     * 主题启用时创建/升级数据表
     */
    public function install_tables() {
        $this->run_tables_migration( true );
    }

    /**
     * 检查并创建数据表
     */
    public function maybe_create_tables() {
        if ( ! Database_Schema_Migration_Service::can_run_admin_migration() ) {
            return;
        }

        $this->run_tables_migration();
    }

    private function run_tables_migration( $force = false ) {
        Database_Schema_Migration_Service::run(
            array(
                'version_option'     => self::TABLE_VERSION_OPTION,
                'target_version'     => self::TABLE_VERSION,
                'lock_option'        => self::TABLE_MIGRATION_LOCK,
                'force'              => $force,
                'migration_callback' => array( $this, 'create_tables' ),
            )
        );
    }

    /**
     * 创建数据表
     */
    public function create_tables() {
        Database_Schema_Migration_Service::apply_schema( $this->get_table_schemas() );

        // 插入默认职位数据
        $this->insert_default_positions();
    }

    private function get_table_schemas() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // 职位表
        $sql_positions = "CREATE TABLE IF NOT EXISTS {$this->positions_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            title VARCHAR(200) NOT NULL,
            department VARCHAR(100) DEFAULT '',
            location VARCHAR(100) DEFAULT '',
            job_type VARCHAR(50) DEFAULT 'fulltime',
            salary VARCHAR(50) DEFAULT '',
            category VARCHAR(50) DEFAULT '',
            description TEXT,
            requirements TEXT,
            sort_order INT DEFAULT 0,
            status TINYINT(1) DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY status (status),
            KEY category (category),
            KEY sort_order (sort_order)
        ) $charset_collate;";
        
        // 求职申请表
        $sql_applications = "CREATE TABLE IF NOT EXISTS {$this->applications_table} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(50) DEFAULT '',
            email VARCHAR(100) DEFAULT '',
            position_id BIGINT(20) UNSIGNED DEFAULT 0,
            position_title VARCHAR(200) DEFAULT '',
            message TEXT,
            ip_address VARCHAR(45) DEFAULT '',
            user_agent VARCHAR(255) DEFAULT '',
            is_read TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY position_id (position_id),
            KEY is_read (is_read),
            KEY created_at (created_at),
            KEY ip_created (ip_address, created_at)
        ) $charset_collate;";

        return array( $sql_positions, $sql_applications );
    }

    /**
     * 插入默认职位数据
     */
    private function insert_default_positions() {
        global $wpdb;
        
        // 检查是否已有数据
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$this->positions_table}" );
        if ( $count > 0 ) {
            return;
        }
        
        $default_positions = array(
            array(
                'title' => __( '高级PHP开发工程师', 'developer-starter' ),
                'department' => __( '技术部', 'developer-starter' ),
                'location' => __( '北京', 'developer-starter' ),
                'job_type' => 'fulltime',
                'salary' => '15-25K',
                'category' => 'tech',
                'description' => __( "负责公司核心业务系统的设计和开发\n参与技术架构设计，保证系统高可用性和扩展性\n编写技术文档，进行代码审查\n指导初级开发人员，参与技术分享", 'developer-starter' ),
                'requirements' => __( "本科及以上学历，计算机相关专业\n5年以上PHP开发经验，熟悉Laravel/ThinkPHP框架\n熟悉MySQL、Redis等数据库，具备性能优化经验\n良好的沟通能力和团队协作精神", 'developer-starter' ),
                'sort_order' => 1,
            ),
            array(
                'title' => __( 'UI/UX设计师', 'developer-starter' ),
                'department' => __( '产品部', 'developer-starter' ),
                'location' => __( '上海', 'developer-starter' ),
                'job_type' => 'fulltime',
                'salary' => '12-20K',
                'category' => 'product',
                'description' => __( "负责公司产品的UI/UX设计工作\n制定设计规范，维护设计系统\n与产品、开发团队紧密协作\n跟踪国际设计趋势，持续优化用户体验", 'developer-starter' ),
                'requirements' => __( "设计相关专业本科及以上学历\n3年以上UI/UX设计经验，有B端产品设计经验优先\n精通Figma、Sketch等设计工具\n具备良好的审美和设计感", 'developer-starter' ),
                'sort_order' => 2,
            ),
            array(
                'title' => __( '新媒体运营专员', 'developer-starter' ),
                'department' => __( '市场部', 'developer-starter' ),
                'location' => __( '深圳', 'developer-starter' ),
                'job_type' => 'fulltime',
                'salary' => '8-15K',
                'category' => 'market',
                'description' => __( "负责公司新媒体账号的日常运营\n策划并执行内容营销活动\n分析运营数据，优化运营策略\n关注行业动态，挖掘热点话题", 'developer-starter' ),
                'requirements' => __( "本科及以上学历，市场营销、新闻传播相关专业\n2年以上新媒体运营经验\n优秀的文案撰写能力和创意策划能力\n熟悉微信、微博、抖音等主流平台规则", 'developer-starter' ),
                'sort_order' => 3,
            ),
            array(
                'title' => __( '人力资源主管', 'developer-starter' ),
                'department' => __( '行政部', 'developer-starter' ),
                'location' => __( '北京', 'developer-starter' ),
                'job_type' => 'fulltime',
                'salary' => '12-18K',
                'category' => 'admin',
                'description' => __( "负责公司招聘工作的全流程管理\n维护和拓展招聘渠道\n参与人力资源政策制定和执行\n负责员工关系管理和企业文化建设", 'developer-starter' ),
                'requirements' => __( "本科及以上学历，人力资源管理相关专业\n3年以上人力资源工作经验\n熟悉劳动法律法规\n优秀的沟通协调能力和抗压能力", 'developer-starter' ),
                'sort_order' => 4,
            ),
        );
        
        foreach ( $default_positions as $position ) {
            $wpdb->insert( $this->positions_table, $position );
        }
        
        // 设置默认选项
        $default_options = array(
            'hero_title' => __( '加入我们', 'developer-starter' ),
            'hero_subtitle' => __( '与优秀的团队一起，创造无限可能。我们期待有才华的你加入！', 'developer-starter' ),
            'stat_1_number' => '50+',
            'stat_1_label' => __( '团队成员', 'developer-starter' ),
            'stat_2_number' => '10+',
            'stat_2_label' => __( '开放职位', 'developer-starter' ),
            'stat_3_number' => '5个',
            'stat_3_label' => __( '办公城市', 'developer-starter' ),
            'benefits' => array(
                array( 'icon' => 'money', 'title' => __( '有竞争力的薪资', 'developer-starter' ), 'desc' => __( '行业领先的薪酬体系，绩效奖金、年终奖金、项目分红', 'developer-starter' ) ),
                array( 'icon' => 'shield', 'title' => __( '五险一金', 'developer-starter' ), 'desc' => __( '足额缴纳五险一金，额外补充商业医疗保险', 'developer-starter' ) ),
                array( 'icon' => 'book', 'title' => __( '培训发展', 'developer-starter' ), 'desc' => __( '完善的培训体系，行业大会、技术分享、读书基金', 'developer-starter' ) ),
                array( 'icon' => 'calendar', 'title' => __( '带薪年假', 'developer-starter' ), 'desc' => __( '入职即享带薪年假，额外享有生日假、婚假等福利假期', 'developer-starter' ) ),
                array( 'icon' => 'users', 'title' => __( '团队活动', 'developer-starter' ), 'desc' => __( '定期团建活动，下午茶、生日会、年度旅游', 'developer-starter' ) ),
                array( 'icon' => 'trending', 'title' => __( '晋升通道', 'developer-starter' ), 'desc' => __( '透明的晋升机制，技术线与管理线双通道发展', 'developer-starter' ) ),
            ),
            'hr_phone' => '',
            'hr_email' => '',
            'module_enabled' => '1',
            'enable_application' => '1',
        );
        
        update_option( $this->option_name, $default_options );
    }

    /**
     * 注册设置
     */
    public function register_settings() {
        register_setting( 'developer_starter_careers_settings', $this->option_name, array(
            'sanitize_callback' => array( $this, 'sanitize_options' ),
        ) );
    }

    /**
     * 清理选项
     */
    public function sanitize_options( $input ) {
        if ( ! is_array( $input ) ) {
            return array();
        }
        
        $sanitized = array();
        
        // 文本字段
        $text_fields = array( 'hero_title', 'hero_subtitle', 'hero_bg_color', 'stat_1_number', 'stat_1_label', 
                             'stat_2_number', 'stat_2_label', 'stat_3_number', 'stat_3_label',
                             'hr_phone', 'hr_email' );
        foreach ( $text_fields as $field ) {
            $sanitized[ $field ] = isset( $input[ $field ] ) ? sanitize_text_field( $input[ $field ] ) : '';
        }
        
        // 复选框
        $sanitized['module_enabled'] = isset( $input['module_enabled'] ) ? '1' : '';
        $sanitized['enable_application'] = isset( $input['enable_application'] ) ? '1' : '';
        
        // 福利数组
        if ( isset( $input['benefits'] ) && is_array( $input['benefits'] ) ) {
            $sanitized['benefits'] = array();
            foreach ( $input['benefits'] as $benefit ) {
                if ( ! empty( $benefit['title'] ) ) {
                    $sanitized['benefits'][] = array(
                        'icon' => sanitize_text_field( $benefit['icon'] ?? '' ),
                        'title' => sanitize_text_field( $benefit['title'] ?? '' ),
                        'desc' => sanitize_text_field( $benefit['desc'] ?? '' ),
                    );
                }
            }
        }
        
        return $sanitized;
    }

    /**
     * 加载后台脚本
     */
    public function enqueue_admin_scripts( $hook ) {
        if ( strpos( $hook, 'careers' ) === false ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    /**
     * 添加后台菜单
     */
    public function add_admin_menus() {
        // 招聘设置
        add_submenu_page(
            'developer-starter-settings',
            __( '招聘设置', 'developer-starter' ),
            __( '招聘设置', 'developer-starter' ),
            'manage_options',
            'developer-starter-careers-settings',
            array( $this, 'render_settings_page' )
        );
        
        // 职位管理
        add_submenu_page(
            'developer-starter-settings',
            __( '职位管理', 'developer-starter' ),
            __( '职位管理', 'developer-starter' ),
            'manage_options',
            'developer-starter-careers-positions',
            array( $this, 'render_positions_page' )
        );
        
        // 求职申请
        $unread_count = $this->get_unread_applications_count();
        $menu_title = __( '求职申请', 'developer-starter' );
        if ( $unread_count > 0 ) {
            $menu_title .= ' <span class="awaiting-mod count-' . $unread_count . '"><span class="pending-count">' . $unread_count . '</span></span>';
        }
        
        add_submenu_page(
            'developer-starter-settings',
            __( '求职申请', 'developer-starter' ),
            $menu_title,
            'manage_options',
            'developer-starter-careers-applications',
            array( $this, 'render_applications_page' )
        );
    }

    /**
     * 获取未读申请数量
     */
    private function get_unread_applications_count() {
        global $wpdb;
        return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->applications_table} WHERE is_read = 0" );
    }

    /**
     * 获取招聘设置
     */
    public static function get_option( $key = null, $default = '' ) {
        $options = get_option( 'developer_starter_careers_options', array() );
        if ( $key === null ) {
            return $options;
        }
        return isset( $options[ $key ] ) ? $options[ $key ] : $default;
    }

    /**
     * 获取所有启用的职位
     */
    public static function get_positions( $category = '' ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ds_careers_positions';

        if ( ! self::is_enabled() && ! is_admin() ) {
            return array();
        }
        
        $sql = "SELECT * FROM {$table} WHERE status = 1";
        if ( ! empty( $category ) && $category !== 'all' ) {
            $sql .= $wpdb->prepare( " AND category = %s", $category );
        }
        $sql .= " ORDER BY sort_order ASC, id DESC";
        
        return $wpdb->get_results( $sql );
    }

    /**
     * 获取单个职位
     */
    public static function get_position( $id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'ds_careers_positions';
        return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ) );
    }

    /**
     * 按标题获取启用中的职位。
     *
     * @param string $title 职位标题。
     * @return object|null
     */
    private function get_active_position_by_title( $title ) {
        global $wpdb;

        $title = trim( (string) $title );
        if ( '' === $title ) {
            return null;
        }

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->positions_table} WHERE title = %s AND status = 1 ORDER BY id DESC LIMIT 1",
                $title
            )
        );
    }

    // ==================== 渲染页面 ====================

    /**
     * 渲染招聘设置页面
     */
    public function render_settings_page() {
        $options = get_option( $this->option_name, array() );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '招聘页面设置', 'developer-starter' ); ?></h1>
            
            <form method="post" action="options.php">
                <?php settings_fields( 'developer_starter_careers_settings' ); ?>
                
                <h2 class="title"><?php esc_html_e( '页面头部设置', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="hero_title"><?php esc_html_e( 'Hero 标题', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="text" id="hero_title" name="<?php echo $this->option_name; ?>[hero_title]" 
                                   value="<?php echo esc_attr( __( $options['hero_title'] ?? '加入我们', 'developer-starter' ) ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( '招聘页面的主标题（如：招聘精英/加入我们）', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="hero_subtitle"><?php esc_html_e( 'Hero 副标题', 'developer-starter' ); ?></label></th>
                        <td>
                            <textarea id="hero_subtitle" name="<?php echo $this->option_name; ?>[hero_subtitle]" 
                                      rows="2" class="large-text"><?php echo esc_textarea( $options['hero_subtitle'] ?? '' ); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="hero_bg_color"><?php esc_html_e( 'Hero 背景颜色', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="text" id="hero_bg_color" name="<?php echo $this->option_name; ?>[hero_bg_color]" 
                                   value="<?php echo esc_attr( $options['hero_bg_color'] ?? '' ); ?>" class="regular-text" 
                                   placeholder="<?php esc_attr_e( '如: linear-gradient(135deg, #2563eb 0%, #0891b2 50%, #10b981 100%)', 'developer-starter' ); ?>" />
                            <p class="description"><?php esc_html_e( '支持渐变色，留空使用默认渐变（蓝→青→绿）', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php esc_html_e( '统计数据', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( '统计项 1', 'developer-starter' ); ?></th>
                        <td>
                            <input type="text" name="<?php echo $this->option_name; ?>[stat_1_number]" 
                                   value="<?php echo esc_attr( $options['stat_1_number'] ?? '50+' ); ?>" 
                                   placeholder="<?php esc_attr_e( '数字', 'developer-starter' ); ?>" style="width: 100px;" />
                            <input type="text" name="<?php echo $this->option_name; ?>[stat_1_label]" 
                                   value="<?php echo esc_attr( __( $options['stat_1_label'] ?? '团队成员', 'developer-starter' ) ); ?>" 
                                   placeholder="<?php esc_attr_e( '标签', 'developer-starter' ); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( '统计项 2', 'developer-starter' ); ?></th>
                        <td>
                            <input type="text" name="<?php echo $this->option_name; ?>[stat_2_number]" 
                                   value="<?php echo esc_attr( $options['stat_2_number'] ?? '10+' ); ?>" 
                                   placeholder="<?php esc_attr_e( '数字', 'developer-starter' ); ?>" style="width: 100px;" />
                            <input type="text" name="<?php echo $this->option_name; ?>[stat_2_label]" 
                                   value="<?php echo esc_attr( __( $options['stat_2_label'] ?? '开放职位', 'developer-starter' ) ); ?>" 
                                   placeholder="<?php esc_attr_e( '标签', 'developer-starter' ); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( '统计项 3', 'developer-starter' ); ?></th>
                        <td>
                            <input type="text" name="<?php echo $this->option_name; ?>[stat_3_number]" 
                                   value="<?php echo esc_attr( $options['stat_3_number'] ?? '5个' ); ?>" 
                                   placeholder="<?php esc_attr_e( '数字', 'developer-starter' ); ?>" style="width: 100px;" />
                            <input type="text" name="<?php echo $this->option_name; ?>[stat_3_label]" 
                                   value="<?php echo esc_attr( __( $options['stat_3_label'] ?? '办公城市', 'developer-starter' ) ); ?>" 
                                   placeholder="<?php esc_attr_e( '标签', 'developer-starter' ); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php esc_html_e( '公司福利', 'developer-starter' ); ?></h2>
                <div id="benefits-container" style="margin-bottom: 20px;">
                    <?php 
                    $benefits = isset( $options['benefits'] ) && is_array( $options['benefits'] ) ? $options['benefits'] : array();
                    $icon_options = array(
                        'money' => __( '💰 薪资', 'developer-starter' ),
                        'shield' => __( '🛡️ 保险', 'developer-starter' ),
                        'book' => __( '📚 培训', 'developer-starter' ),
                        'calendar' => __( '📅 假期', 'developer-starter' ),
                        'users' => __( '👥 团队', 'developer-starter' ),
                        'trending' => __( '📈 晋升', 'developer-starter' ),
                        'heart' => __( '❤️ 关怀', 'developer-starter' ),
                        'star' => __( '⭐ 福利', 'developer-starter' ),
                    );
                    $icon_options_markup = '';
                    foreach ( $icon_options as $val => $label ) {
                        $icon_options_markup .= sprintf(
                            '<option value="%s">%s</option>',
                            esc_attr( $val ),
                            esc_html( $label )
                        );
                    }
                    foreach ( $benefits as $idx => $benefit ) : ?>
                        <div class="benefit-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ddd; position: relative;">
                            <a href="#" class="remove-benefit" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;"><?php esc_html_e( '删除', 'developer-starter' ); ?></a>
                            <p style="margin: 0 0 10px;">
                                <label><strong><?php esc_html_e( '图标', 'developer-starter' ); ?></strong></label><br>
                                <select name="<?php echo esc_attr( $this->option_name ); ?>[benefits][<?php echo absint( $idx ); ?>][icon]" style="width: 150px;">
                                    <?php foreach ( $icon_options as $val => $label ) : ?>
                                        <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $benefit['icon'] ?? '', $val ); ?>><?php echo esc_html( $label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </p>
                            <p style="margin: 0 0 10px;">
                                <label><strong><?php esc_html_e( '标题', 'developer-starter' ); ?></strong></label><br>
                                <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[benefits][<?php echo absint( $idx ); ?>][title]" 
                                       value="<?php echo esc_attr( $benefit['title'] ?? '' ); ?>" style="width: 100%;" />
                            </p>
                            <p style="margin: 0;">
                                <label><strong><?php esc_html_e( '描述', 'developer-starter' ); ?></strong></label><br>
                                <input type="text" name="<?php echo esc_attr( $this->option_name ); ?>[benefits][<?php echo absint( $idx ); ?>][desc]" 
                                       value="<?php echo esc_attr( $benefit['desc'] ?? '' ); ?>" style="width: 100%;" />
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="add-benefit" class="button"><?php esc_html_e( '+ 添加福利项', 'developer-starter' ); ?></button>
                
                <h2 class="title" style="margin-top: 30px;"><?php esc_html_e( 'HR 联系方式', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><label for="hr_phone"><?php esc_html_e( 'HR 电话', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="text" id="hr_phone" name="<?php echo $this->option_name; ?>[hr_phone]" 
                                   value="<?php echo esc_attr( $options['hr_phone'] ?? '' ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( '留空则使用主题设置中的公司电话', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="hr_email"><?php esc_html_e( 'HR 邮箱', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="email" id="hr_email" name="<?php echo $this->option_name; ?>[hr_email]" 
                                   value="<?php echo esc_attr( $options['hr_email'] ?? '' ); ?>" class="regular-text" />
                            <p class="description"><?php esc_html_e( '留空则使用主题设置中的公司邮箱', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <h2 class="title"><?php esc_html_e( '功能开关', 'developer-starter' ); ?></h2>
                <table class="form-table">
                    <tr>
                        <th><?php esc_html_e( '招聘模块', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $this->option_name; ?>[module_enabled]" value="1"
                                       <?php checked( $options['module_enabled'] ?? '1', '1' ); ?> />
                                <?php esc_html_e( '启用招聘模块', 'developer-starter' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( '关闭后，前台普通请求不会初始化招聘管理器，站点其他页面也不再额外加载招聘相关钩子。', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( '在线申请', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="<?php echo $this->option_name; ?>[enable_application]" value="1" 
                                       <?php checked( $options['enable_application'] ?? '1', '1' ); ?> />
                                <?php esc_html_e( '启用在线申请功能', 'developer-starter' ); ?>
                            </label>
                            <p class="description"><?php esc_html_e( '关闭后，招聘页面将不显示申请表单', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button( __( '保存设置', 'developer-starter' ) ); ?>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var benefitIndex = <?php echo absint( count( $benefits ) ); ?>;
            
            $('#add-benefit').on('click', function() {
                var html = '<div class="benefit-item" style="background: #f9f9f9; padding: 15px; margin-bottom: 10px; border-radius: 5px; border: 1px solid #ddd; position: relative;">' +
                    '<a href="#" class="remove-benefit" style="position: absolute; top: 5px; right: 10px; color: #a00; text-decoration: none;"><?php echo esc_js( __( '删除', 'developer-starter' ) ); ?></a>' +
                    '<p style="margin: 0 0 10px;"><label><strong><?php echo esc_js( __( '图标', 'developer-starter' ) ); ?></strong></label><br>' +
                    '<select name="<?php echo esc_js( $this->option_name ); ?>[benefits][' + benefitIndex + '][icon]" style="width: 150px;">' +
                    <?php echo wp_json_encode( $icon_options_markup ); ?> +
                    '</select></p>' +
                    '<p style="margin: 0 0 10px;"><label><strong><?php echo esc_js( __( '标题', 'developer-starter' ) ); ?></strong></label><br>' +
                    '<input type="text" name="<?php echo esc_js( $this->option_name ); ?>[benefits][' + benefitIndex + '][title]" style="width: 100%;" /></p>' +
                    '<p style="margin: 0;"><label><strong><?php echo esc_js( __( '描述', 'developer-starter' ) ); ?></strong></label><br>' +
                    '<input type="text" name="<?php echo esc_js( $this->option_name ); ?>[benefits][' + benefitIndex + '][desc]" style="width: 100%;" /></p>' +
                    '</div>';
                $('#benefits-container').append(html);
                benefitIndex++;
            });
            
            $(document).on('click', '.remove-benefit', function(e) {
                e.preventDefault();
                $(this).closest('.benefit-item').remove();
            });
        });
        </script>
        <?php
    }

    /**
     * 渲染职位管理页面
     */
    public function render_positions_page() {
        global $wpdb;
        
        // 处理操作
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        $id = isset( $_GET['id'] ) ? absint( wp_unslash( $_GET['id'] ) ) : 0;
        
        // 删除操作
        if ( $action === 'delete' && $id && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'delete_position_' . $id ) ) {
            // 权限检查
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( '您没有权限执行此操作', 'developer-starter' ) );
            }
            $wpdb->delete( $this->positions_table, array( 'id' => $id ), array( '%d' ) );
            echo '<div class="notice notice-success"><p>' . __( '职位已删除', 'developer-starter' ) . '</p></div>';
        }
        
        // 保存操作
        if ( isset( $_POST['save_position'] ) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'save_position' ) ) {
            // 权限检查
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( '您没有权限执行此操作', 'developer-starter' ) );
            }
            $data = array(
                'title' => sanitize_text_field( wp_unslash( $_POST['title'] ?? '' ) ),
                'department' => sanitize_text_field( wp_unslash( $_POST['department'] ?? '' ) ),
                'location' => sanitize_text_field( wp_unslash( $_POST['location'] ?? '' ) ),
                'job_type' => sanitize_text_field( wp_unslash( $_POST['job_type'] ?? 'fulltime' ) ),
                'salary' => sanitize_text_field( wp_unslash( $_POST['salary'] ?? '' ) ),
                'category' => sanitize_text_field( wp_unslash( $_POST['category'] ?? '' ) ),
                'description' => sanitize_textarea_field( wp_unslash( $_POST['description'] ?? '' ) ),
                'requirements' => sanitize_textarea_field( wp_unslash( $_POST['requirements'] ?? '' ) ),
                'sort_order' => isset( $_POST['sort_order'] ) ? absint( wp_unslash( $_POST['sort_order'] ) ) : 0,
                'status' => isset( $_POST['status'] ) ? 1 : 0,
            );
            
            $edit_id = isset( $_POST['position_id'] ) ? absint( wp_unslash( $_POST['position_id'] ) ) : 0;
            
            if ( $edit_id > 0 ) {
                $wpdb->update( $this->positions_table, $data, array( 'id' => $edit_id ) );
                echo '<div class="notice notice-success"><p>' . __( '职位已更新', 'developer-starter' ) . '</p></div>';
            } else {
                $wpdb->insert( $this->positions_table, $data );
                echo '<div class="notice notice-success"><p>' . __( '职位已添加', 'developer-starter' ) . '</p></div>';
            }
            $action = ''; // 重置为列表视图
        }
        
        // 编辑模式
        if ( $action === 'edit' || $action === 'add' ) {
            $position = $action === 'edit' && $id ? $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$this->positions_table} WHERE id = %d", $id ) ) : null;
            $this->render_position_form( $position );
            return;
        }
        
        // 列表视图
        $positions = $wpdb->get_results( "SELECT * FROM {$this->positions_table} ORDER BY sort_order ASC, id DESC" );
        ?>
        <div class="wrap">
            <h1>
                <?php esc_html_e( '职位管理', 'developer-starter' ); ?>
                <a href="<?php echo esc_url( admin_url( 'admin.php?page=developer-starter-careers-positions&action=add' ) ); ?>" class="page-title-action"><?php esc_html_e( '添加新职位', 'developer-starter' ); ?></a>
            </h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e( 'ID', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '职位名称', 'developer-starter' ); ?></th>
                        <th style="width: 100px;"><?php esc_html_e( '部门', 'developer-starter' ); ?></th>
                        <th style="width: 80px;"><?php esc_html_e( '地点', 'developer-starter' ); ?></th>
                        <th style="width: 80px;"><?php esc_html_e( '类型', 'developer-starter' ); ?></th>
                        <th style="width: 100px;"><?php esc_html_e( '薪资', 'developer-starter' ); ?></th>
                        <th style="width: 80px;"><?php esc_html_e( '分类', 'developer-starter' ); ?></th>
                        <th style="width: 60px;"><?php esc_html_e( '排序', 'developer-starter' ); ?></th>
                        <th style="width: 60px;"><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                        <th style="width: 120px;"><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $positions ) ) : ?>
                        <tr><td colspan="10" style="text-align: center; padding: 40px;"><?php esc_html_e( '暂无职位，请添加', 'developer-starter' ); ?></td></tr>
                    <?php else : ?>
                        <?php 
                        $job_types = array( 
                            'fulltime' => __( '全职', 'developer-starter' ), 
                            'parttime' => __( '兼职', 'developer-starter' ), 
                            'intern'   => __( '实习', 'developer-starter' ) 
                        );
                        $categories = array( 
                            'tech'    => __( '技术', 'developer-starter' ), 
                            'product' => __( '产品', 'developer-starter' ), 
                            'market'  => __( '市场', 'developer-starter' ), 
                            'admin'   => __( '职能', 'developer-starter' ) 
                        );
                        foreach ( $positions as $pos ) : ?>
                            <tr>
                                <td><?php echo esc_html( $pos->id ); ?></td>
                                <td><strong><?php echo esc_html( $pos->title ); ?></strong></td>
                                <td><?php echo esc_html( $pos->department ); ?></td>
                                <td><?php echo esc_html( $pos->location ); ?></td>
                                <td><?php echo esc_html( $job_types[ $pos->job_type ] ?? $pos->job_type ); ?></td>
                                <td><?php echo esc_html( $pos->salary ); ?></td>
                                <td><?php echo esc_html( $categories[ $pos->category ] ?? $pos->category ); ?></td>
                                <td><?php echo esc_html( $pos->sort_order ); ?></td>
                                <td>
                                    <?php if ( $pos->status ) : ?>
                                        <span style="color: #22c55e;"><?php esc_html_e( '启用', 'developer-starter' ); ?></span>
                                    <?php else : ?>
                                        <span style="color: #94a3b8;"><?php esc_html_e( '禁用', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=developer-starter-careers-positions&action=edit&id=' . absint( $pos->id ) ) ); ?>"><?php esc_html_e( '编辑', 'developer-starter' ); ?></a> |
                                    <a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=developer-starter-careers-positions&action=delete&id=' . absint( $pos->id ) ), 'delete_position_' . absint( $pos->id ) ) ); ?>" 
                                       onclick="return confirm('<?php echo esc_js( __( '确定删除此职位？', 'developer-starter' ) ); ?>');" style="color: #dc2626;"><?php esc_html_e( '删除', 'developer-starter' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }

    /**
     * 渲染职位编辑表单
     */
    private function render_position_form( $position = null ) {
        $is_edit = $position !== null;
        ?>
        <div class="wrap">
            <h1><?php echo $is_edit ? __( '编辑职位', 'developer-starter' ) : __( '添加新职位', 'developer-starter' ); ?></h1>
            
            <form method="post">
                <?php wp_nonce_field( 'save_position' ); ?>
                <?php if ( $is_edit ) : ?>
                    <input type="hidden" name="position_id" value="<?php echo esc_attr( $position->id ); ?>" />
                <?php endif; ?>
                
                <table class="form-table">
                    <tr>
                        <th><label for="title"><?php esc_html_e( '职位名称', 'developer-starter' ); ?> <span style="color: red;">*</span></label></th>
                        <td>
                            <input type="text" id="title" name="title" 
                                   value="<?php echo esc_attr( $position->title ?? '' ); ?>" class="regular-text" required />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="department"><?php esc_html_e( '部门', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="text" id="department" name="department" 
                                   value="<?php echo esc_attr( $position->department ?? '' ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="location"><?php esc_html_e( '工作地点', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="text" id="location" name="location" 
                                   value="<?php echo esc_attr( $position->location ?? '' ); ?>" class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="job_type"><?php esc_html_e( '工作类型', 'developer-starter' ); ?></label></th>
                        <td>
                            <select id="job_type" name="job_type">
                                <option value="fulltime" <?php selected( $position->job_type ?? '', 'fulltime' ); ?>><?php esc_html_e( '全职', 'developer-starter' ); ?></option>
                                <option value="parttime" <?php selected( $position->job_type ?? '', 'parttime' ); ?>><?php esc_html_e( '兼职', 'developer-starter' ); ?></option>
                                <option value="intern" <?php selected( $position->job_type ?? '', 'intern' ); ?>><?php esc_html_e( '实习', 'developer-starter' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="salary"><?php esc_html_e( '薪资范围', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="text" id="salary" name="salary" 
                                   value="<?php echo esc_attr( $position->salary ?? '' ); ?>" placeholder="<?php esc_attr_e( '如：15-25K', 'developer-starter' ); ?>" style="width: 150px;" />
                        </td>
                    </tr>
                    <tr>
                        <th><label for="category"><?php esc_html_e( '分类标签', 'developer-starter' ); ?></label></th>
                        <td>
                            <select id="category" name="category">
                                <option value="tech" <?php selected( $position->category ?? '', 'tech' ); ?>><?php esc_html_e( '技术研发', 'developer-starter' ); ?></option>
                                <option value="product" <?php selected( $position->category ?? '', 'product' ) ;?>><?php esc_html_e( '产品设计', 'developer-starter' ); ?></option>
                                <option value="market" <?php selected( $position->category ?? '', 'market' ); ?>><?php esc_html_e( '市场运营', 'developer-starter' ); ?></option>
                                <option value="admin" <?php selected( $position->category ?? '', 'admin' ); ?>><?php esc_html_e( '职能管理', 'developer-starter' ); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description"><?php esc_html_e( '职位描述', 'developer-starter' ); ?></label></th>
                        <td>
                            <textarea id="description" name="description" rows="6" class="large-text"><?php echo esc_textarea( $position->description ?? '' ); ?></textarea>
                            <p class="description"><?php esc_html_e( '每行一条，将显示为列表', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="requirements"><?php esc_html_e( '任职要求', 'developer-starter' ); ?></label></th>
                        <td>
                            <textarea id="requirements" name="requirements" rows="6" class="large-text"><?php echo esc_textarea( $position->requirements ?? '' ); ?></textarea>
                            <p class="description"><?php esc_html_e( '每行一条，将显示为列表', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="sort_order"><?php esc_html_e( '排序', 'developer-starter' ); ?></label></th>
                        <td>
                            <input type="number" id="sort_order" name="sort_order" 
                                   value="<?php echo esc_attr( $position->sort_order ?? 0 ); ?>" style="width: 80px;" />
                            <p class="description"><?php esc_html_e( '数字越小越靠前', 'developer-starter' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="status" value="1" <?php checked( $position->status ?? 1, 1 ); ?> />
                                <?php esc_html_e( '启用此职位', 'developer-starter' ); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="save_position" class="button button-primary" value="<?php esc_attr_e( '保存职位', 'developer-starter' ); ?>" />
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=developer-starter-careers-positions' ) ); ?>" class="button"><?php esc_html_e( '返回列表', 'developer-starter' ); ?></a>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * 渲染求职申请页面
     */
    public function render_applications_page() {
        global $wpdb;
        $paged = isset( $_GET['paged'] ) ? max( 1, absint( wp_unslash( $_GET['paged'] ) ) ) : 1;
        $per_page = 50;
        $base_page_url = admin_url( 'admin.php?page=developer-starter-careers-applications' );
        
        // 处理操作
        if ( isset( $_GET['action'] ) && isset( $_GET['id'] ) && isset( $_GET['_wpnonce'] ) ) {
            // 权限检查
            if ( ! current_user_can( 'manage_options' ) ) {
                wp_die( __( '您没有权限执行此操作', 'developer-starter' ) );
            }
            $id = absint( wp_unslash( $_GET['id'] ) );
            $action = sanitize_key( wp_unslash( $_GET['action'] ) );
            $nonce  = sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) );
            if ( wp_verify_nonce( $nonce, 'application_action' ) ) {
                if ( $action === 'mark_read' ) {
                    $wpdb->update( $this->applications_table, array( 'is_read' => 1 ), array( 'id' => $id ), array( '%d' ), array( '%d' ) );
                } elseif ( $action === 'delete' ) {
                    $wpdb->delete( $this->applications_table, array( 'id' => $id ), array( '%d' ) );
                }
            }
        }
        
        // 获取申请列表（真实分页）
        $total_records = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$this->applications_table}" );
        $total_pages = max( 1, (int) ceil( $total_records / $per_page ) );
        if ( $paged > $total_pages ) {
            $paged = $total_pages;
        }
        $offset = ( $paged - 1 ) * $per_page;
        $applications = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->applications_table} ORDER BY created_at DESC LIMIT %d OFFSET %d",
                $per_page,
                $offset
            )
        );
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( '求职申请管理', 'developer-starter' ); ?></h1>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 50px;"><?php esc_html_e( 'ID', 'developer-starter' ); ?></th>
                        <th style="width: 80px;"><?php esc_html_e( '姓名', 'developer-starter' ); ?></th>
                        <th style="width: 120px;"><?php esc_html_e( '电话', 'developer-starter' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( '邮箱', 'developer-starter' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( '应聘职位', 'developer-starter' ); ?></th>
                        <th><?php esc_html_e( '自我介绍', 'developer-starter' ); ?></th>
                        <th style="width: 150px;"><?php esc_html_e( '申请时间', 'developer-starter' ); ?></th>
                        <th style="width: 60px;"><?php esc_html_e( '状态', 'developer-starter' ); ?></th>
                        <th style="width: 120px;"><?php esc_html_e( '操作', 'developer-starter' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ( empty( $applications ) ) : ?>
                        <tr><td colspan="9" style="text-align: center; padding: 40px;"><?php esc_html_e( '暂无求职申请', 'developer-starter' ); ?></td></tr>
                    <?php else : ?>
                        <?php foreach ( $applications as $app ) : ?>
                            <tr style="<?php echo $app->is_read ? '' : 'background: #fff9e6;'; ?>">
                                <td><?php echo esc_html( $app->id ); ?></td>
                                <td><strong><?php echo esc_html( $app->name ); ?></strong></td>
                                <td><?php echo esc_html( $app->phone ); ?></td>
                                <td><?php echo esc_html( $app->email ); ?></td>
                                <td><?php echo esc_html( $app->position_title ); ?></td>
                                <td><?php echo esc_html( wp_trim_words( $app->message, 20 ) ); ?></td>
                                <td><?php echo esc_html( $app->created_at ); ?></td>
                                <td>
                                    <?php if ( $app->is_read ) : ?>
                                        <span style="color: #22c55e;"><?php esc_html_e( '已读', 'developer-starter' ); ?></span>
                                    <?php else : ?>
                                        <span style="color: #f59e0b; font-weight: bold;"><?php esc_html_e( '未读', 'developer-starter' ); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ( ! $app->is_read ) : ?>
                                        <?php
                                        $mark_read_url = wp_nonce_url(
                                            add_query_arg(
                                                array(
                                                    'action' => 'mark_read',
                                                    'id'     => (int) $app->id,
                                                    'paged'  => $paged,
                                                ),
                                                $base_page_url
                                            ),
                                            'application_action'
                                        );
                                        ?>
                                        <a href="<?php echo esc_url( $mark_read_url ); ?>"><?php esc_html_e( '标记已读', 'developer-starter' ); ?></a> |
                                    <?php endif; ?>
                                    <?php
                                    $delete_url = wp_nonce_url(
                                        add_query_arg(
                                            array(
                                                'action' => 'delete',
                                                'id'     => (int) $app->id,
                                                'paged'  => $paged,
                                            ),
                                            $base_page_url
                                        ),
                                        'application_action'
                                    );
                                    ?>
                                    <a href="<?php echo esc_url( $delete_url ); ?>" 
                                       onclick="return confirm('<?php echo esc_js( __( '确定删除此申请？', 'developer-starter' ) ); ?>');" style="color: #dc2626;"><?php esc_html_e( '删除', 'developer-starter' ); ?></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if ( $total_pages > 1 ) : ?>
                <div class="tablenav">
                    <div class="tablenav-pages">
                        <?php
                        echo wp_kses_post(
                            paginate_links(
                                array(
                                    'base'    => add_query_arg( 'paged', '%#%', $base_page_url ),
                                    'format'  => '',
                                    'current' => $paged,
                                    'total'   => $total_pages,
                                )
                            )
                        );
                        ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    // ==================== AJAX 处理 ====================

    /**
     * 处理求职申请提交
     */
    public function handle_application_submit() {
        // 验证 nonce
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
        if ( '' === $nonce || ! wp_verify_nonce( $nonce, 'ds_careers_application_nonce' ) ) {
            wp_send_json_error( array( 'message' => __( '安全验证失败', 'developer-starter' ) ) );
        }
        
        // 检查是否启用申请
        $options = get_option( $this->option_name, array() );
        if ( empty( $options['enable_application'] ) ) {
            wp_send_json_error( array( 'message' => __( '在线申请已关闭', 'developer-starter' ) ) );
        }
        if ( ! self::is_enabled() ) {
            wp_send_json_error( array( 'message' => __( '招聘模块已关闭', 'developer-starter' ) ) );
        }
        
        // 频率限制
        $ip = developer_starter_get_client_ip();
        if ( $this->is_rate_limited( $ip ) ) {
            wp_send_json_error( array( 'message' => __( '提交过于频繁，请稍后再试', 'developer-starter' ) ) );
        }
        
        // 清理输入
        $name = sanitize_text_field( wp_unslash( $_POST['name'] ?? '' ) );
        $phone = sanitize_text_field( wp_unslash( $_POST['phone'] ?? '' ) );
        $email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
        $position_id = isset( $_POST['position_id'] ) ? absint( wp_unslash( $_POST['position_id'] ) ) : 0;
        $raw_position_title = sanitize_text_field( wp_unslash( $_POST['position_title'] ?? '' ) );
        $message = sanitize_textarea_field( wp_unslash( $_POST['message'] ?? '' ) );
        
        // 验证必填
        if ( empty( $name ) ) {
            wp_send_json_error( array( 'message' => __( '请填写姓名', 'developer-starter' ) ) );
        }
        if ( empty( $phone ) && empty( $email ) ) {
            wp_send_json_error( array( 'message' => __( '请填写联系电话或邮箱', 'developer-starter' ) ) );
        }
        $position_title = '';
        $selected_position = $position_id > 0 ? self::get_position( $position_id ) : null;
        if ( $selected_position && ! empty( $selected_position->title ) && (int) ( $selected_position->status ?? 0 ) === 1 ) {
            $position_id = (int) $selected_position->id;
            $position_title = sanitize_text_field( (string) $selected_position->title );
        } else {
            $matched_position = $this->get_active_position_by_title( $raw_position_title );
            $other_position_label = __( '其他职位', 'developer-starter' );
            if ( $matched_position && ! empty( $matched_position->title ) ) {
                $position_id = (int) $matched_position->id;
                $position_title = sanitize_text_field( (string) $matched_position->title );
            } elseif ( $raw_position_title === $other_position_label || $raw_position_title === '其他职位' ) {
                $position_id = 0;
                $position_title = $other_position_label;
            }
        }

        if ( '' === $position_title ) {
            wp_send_json_error( array( 'message' => __( '请选择有效的应聘职位', 'developer-starter' ) ) );
        }
        
        // 插入数据库
        global $wpdb;
        $result = $wpdb->insert(
            $this->applications_table,
            array(
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'position_id' => $position_id,
                'position_title' => $position_title,
                'message' => $message,
                'ip_address' => $ip,
                'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '',
                'is_read' => 0,
                'created_at' => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
        );
        
        if ( $result === false ) {
            wp_send_json_error( array( 'message' => __( '提交失败，请稍后重试', 'developer-starter' ) ) );
        }

        $this->send_application_notification(
            array(
                'name'           => $name,
                'phone'          => $phone,
                'email'          => $email,
                'position_id'    => $position_id,
                'position_title' => $position_title,
                'message'        => $message,
                'ip_address'     => $ip,
            )
        );
        
        wp_send_json_success( array( 'message' => __( '申请已提交！我们会尽快与您联系', 'developer-starter' ) ) );
    }

    /**
     * 发送求职申请通知（邮件/推送）
     *
     * @param array $application 申请数据。
     * @return void
     */
    private function send_application_notification( $application ) {
        $mode = function_exists( 'developer_starter_get_notify_method' )
            ? developer_starter_get_notify_method( 'careers', 'none' )
            : 'none';

        $should_send_email = function_exists( 'developer_starter_notify_method_has_email' )
            ? developer_starter_notify_method_has_email( $mode )
            : false;
        $should_send_push = function_exists( 'developer_starter_notify_method_has_push' )
            ? developer_starter_notify_method_has_push( $mode )
            : false;

        if ( ! $should_send_email && ! $should_send_push ) {
            return;
        }

        $site_name = get_bloginfo( 'name' );
        $subject = sprintf(
            __( '[%1$s] 新求职申请：%2$s', 'developer-starter' ),
            $site_name,
            $application['position_title']
        );

        if ( $should_send_email ) {
            $options = get_option( $this->option_name, array() );
            $to = ! empty( $options['hr_email'] ) ? sanitize_email( $options['hr_email'] ) : get_option( 'admin_email' );
            if ( ! $to ) {
                $to = get_option( 'admin_email' );
            }

            $mail_message  = __( "您收到一条新的求职申请：\n\n", 'developer-starter' );
            $mail_message .= __( '姓名', 'developer-starter' ) . ': ' . $application['name'] . "\n";
            $mail_message .= __( '电话', 'developer-starter' ) . ': ' . $application['phone'] . "\n";
            $mail_message .= __( '邮箱', 'developer-starter' ) . ': ' . $application['email'] . "\n";
            $mail_message .= __( '职位', 'developer-starter' ) . ': ' . $application['position_title'] . "\n";
            $mail_message .= __( '自我介绍', 'developer-starter' ) . ': ' . $application['message'] . "\n";
            $mail_message .= __( '提交时间', 'developer-starter' ) . ': ' . current_time( 'Y-m-d H:i:s' ) . "\n";
            $mail_message .= __( 'IP 地址', 'developer-starter' ) . ': ' . $application['ip_address'] . "\n";

            wp_mail( $to, $subject, $mail_message, array( 'Content-Type: text/plain; charset=UTF-8' ) );
        }

        if ( $should_send_push && function_exists( 'developer_starter_send_push_message' ) ) {
            $push_lines = array(
                __( '姓名', 'developer-starter' ) => $application['name'],
                __( '电话', 'developer-starter' ) => $application['phone'],
                __( '邮箱', 'developer-starter' ) => $application['email'],
                __( '职位', 'developer-starter' ) => $application['position_title'],
                __( '自我介绍', 'developer-starter' ) => $application['message'],
                __( '提交时间', 'developer-starter' ) => current_time( 'Y-m-d H:i:s' ),
                __( 'IP 地址', 'developer-starter' ) => $application['ip_address'],
            );
            developer_starter_send_push_message(
                'careers',
                __( '新求职申请通知', 'developer-starter' ),
                $push_lines,
                array(
                    'args' => array(
                        'source' => 'qiling_theme_careers',
                    ),
                )
            );
        }
    }

    /**
     * 检查频率限制
     */
    private function is_rate_limited( $ip ) {
        global $wpdb;
        $threshold = function_exists( 'wp_date' )
            ? wp_date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( 5 * MINUTE_IN_SECONDS ), wp_timezone() )
            : date_i18n( 'Y-m-d H:i:s', current_time( 'timestamp' ) - ( 5 * MINUTE_IN_SECONDS ) );
        $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$this->applications_table} WHERE ip_address = %s AND created_at > %s",
            $ip,
            $threshold
        ) );
        return $count >= 3; // 5分钟内最多3次
    }
}
