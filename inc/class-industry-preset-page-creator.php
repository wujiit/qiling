<?php
/**
 * 行业预设页面创建器类
 *
 * 为餐饮/宠物/旅行/民宿/医美页面模板自动填充可编辑模块。
 *
 * @package Developer_Starter
 * @since 1.0.4
 */

namespace Developer_Starter\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Industry_Preset_Page_Creator {

    /**
     * 模板与预设映射。
     *
     * @return array<string,array<string,string>>
     */
    private function get_template_map() {
        return array(
            'templates/template-restaurant.php' => array(
                'preset' => 'restaurant',
                'flag'   => '_industry_restaurant_modules_filled',
            ),
            'templates/template-pet.php' => array(
                'preset' => 'pet',
                'flag'   => '_industry_pet_modules_filled',
            ),
            'templates/template-travel.php' => array(
                'preset' => 'travel',
                'flag'   => '_industry_travel_modules_filled',
            ),
            'templates/template-homestay.php' => array(
                'preset' => 'homestay',
                'flag'   => '_industry_homestay_modules_filled',
            ),
            'templates/template-medical-beauty.php' => array(
                'preset' => 'medical_beauty',
                'flag'   => '_industry_medical_beauty_modules_filled',
            ),
        );
    }

    /**
     * 构造函数
     */
    public function __construct() {
        add_action( 'save_post', array( $this, 'on_page_save' ), 99, 2 );
    }

    /**
     * 页面保存时自动填充行业模块。
     *
     * @param int     $post_id 页面ID
     * @param WP_Post $post    页面对象
     */
    public function on_page_save( $post_id, $post ) {
        if ( $post->post_type !== 'page' ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $template = get_post_meta( $post_id, '_wp_page_template', true );
        if ( function_exists( 'developer_starter_normalize_page_template_slug' ) ) {
            $template = developer_starter_normalize_page_template_slug( $template );
        }
        $map      = $this->get_template_map();

        if ( ! isset( $map[ $template ] ) ) {
            return;
        }

        $modules = function_exists( 'developer_starter_get_raw_page_modules_meta' )
            ? developer_starter_get_raw_page_modules_meta( $post_id )
            : get_post_meta( $post_id, '_developer_starter_modules', true );
        if ( empty( $modules ) || ! is_array( $modules ) || count( $modules ) === 0 ) {
            Page_Creator_Base::persist_default_modules_for_creator(
                $post_id,
                $this->get_preset_modules( $map[ $template ]['preset'] ),
                __CLASS__,
                $template
            );
            update_post_meta( $post_id, $map[ $template ]['flag'], '1' );
        }
    }

    /**
     * 获取指定行业预设模块。
     *
     * @param string $preset 预设标识
     * @return array
     */
    private function get_preset_modules( $preset ) {
        switch ( $preset ) {
            case 'restaurant':
                return $this->get_restaurant_modules();
            case 'pet':
                return $this->get_pet_modules();
            case 'travel':
                return $this->get_travel_modules();
            case 'homestay':
                return $this->get_homestay_modules();
            case 'medical_beauty':
                return $this->get_medical_beauty_modules();
            default:
                return array();
        }
    }

    /**
     * 餐饮官网预设模块
     *
     * @return array
     */
    private function get_restaurant_modules() {
        return array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'large',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image'      => '',
                            'title'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '城市里的 <strong>烟火好味道</strong>', 'Signature <strong>Flavors</strong> in the City' ) : __( '城市里的 <strong>烟火好味道</strong>', 'developer-starter' ),
                            'subtitle'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '精选食材 · 现点现做 · 支持在线预约到店', 'Selected ingredients, made to order, with easy table reservations.' ) : __( '精选食材 · 现点现做 · 支持在线预约到店', 'developer-starter' ),
                            'btn_text'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即预约', 'Book a Table' ) : __( '立即预约', 'developer-starter' ),
                            'btn_url'    => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'menu',
                'data' => array(
                    'menu_title'        => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '招牌<span style="color:#f59e0b">菜单</span>', 'Signature <span style="color:#f59e0b">Menu</span>' ) : __( '招牌<span style="color:#f59e0b">菜单</span>', 'developer-starter' ),
                    'menu_subtitle'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '人气菜品与套餐推荐，可按门店口味自行调整', 'Popular dishes and set menus that you can tailor to your venue.' ) : __( '人气菜品与套餐推荐，可按门店口味自行调整', 'developer-starter' ),
                    'menu_layout'       => 'grid',
                    'menu_accent_color' => '#f59e0b',
                    'menu_items'        => array(
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '招牌牛排套餐', 'Signature Steak Set' ) : __( '招牌牛排套餐', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '主厨特制牛排 + 时蔬 + 汤品', 'Chef-cut steak with seasonal vegetables and soup.' ) : __( '主厨特制牛排 + 时蔬 + 汤品', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 128 ) : '¥128',
                            'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热卖', 'Hot' ) : __( '热卖', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '双人晚餐套餐', 'Dinner Set for Two' ) : __( '双人晚餐套餐', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前菜、主菜、甜品组合，适合约会场景', 'Starter, main course, and dessert pairing for date nights.' ) : __( '前菜、主菜、甜品组合，适合约会场景', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 258 ) : '¥258',
                            'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Featured' ) : __( '推荐', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '午市商务简餐', 'Business Lunch Special' ) : __( '午市商务简餐', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '快节奏工作日高效用餐方案', 'A quick weekday option for business lunches.' ) : __( '快节奏工作日高效用餐方案', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 58 ) : '¥58',
                            'badge' => '',
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '家庭聚会套餐', 'Family Sharing Set' ) : __( '家庭聚会套餐', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '6-8人分享，含冷热菜与饮品', 'Sharing menu for 6-8 guests with hot dishes, cold plates, and drinks.' ) : __( '6-8人分享，含冷热菜与饮品', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 688 ) : '¥688',
                            'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '新品', 'New' ) : __( '新品', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '门店服务', 'Dining Services' ) : __( '门店服务', 'developer-starter' ),
                    'services_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '不仅有好吃的，更有舒适体验', 'More than great food, with a polished dining experience.' ) : __( '不仅有好吃的，更有舒适体验', 'developer-starter' ),
                    'services_columns'  => '4',
                    'services_items'    => array(
                        array(
                            'icon'  => '🍽️',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '到店堂食', 'Dine-In' ) : __( '到店堂食', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合约会、家庭聚餐和商务接待', 'Ideal for dates, family meals, and business hosting.' ) : __( '适合约会、家庭聚餐和商务接待', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🎉',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '包场活动', 'Private Events' ) : __( '包场活动', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '生日会、团建和小型发布会场地预订', 'Venue booking for birthdays, team gatherings, and small launches.' ) : __( '生日会、团建和小型发布会场地预订', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🥗',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '轻食定制', 'Healthy Menu Options' ) : __( '轻食定制', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '低卡、健身与过敏原提示菜品支持', 'Low-calorie, fitness-friendly, and allergen-aware options available.' ) : __( '低卡、健身与过敏原提示菜品支持', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🚗',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '外带打包', 'Takeaway Pickup' ) : __( '外带打包', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '支持电话/线上预约取餐', 'Phone and online pickup reservations supported.' ) : __( '支持电话/线上预约取餐', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '顾客评价', 'Guest Reviews' ) : __( '顾客评价', 'developer-starter' ),
                    'testimonials_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '真实反馈，帮助新顾客快速决策', 'Real feedback that helps new guests decide faster.' ) : __( '真实反馈，帮助新顾客快速决策', 'developer-starter' ),
                    'testimonials_columns'  => '3',
                    'testimonials_items'    => array(
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '陈女士', 'Emma C.' ) : __( '陈女士', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '周末常客', 'Weekend Guest' ) : __( '周末常客', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '菜品稳定、上菜速度快，服务细节很到位。', 'Consistently good dishes, quick service, and thoughtful attention to detail.' ) : __( '菜品稳定、上菜速度快，服务细节很到位。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'meituan',
                            'verified' => 'verified',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '李先生', 'Daniel L.' ) : __( '李先生', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '公司行政', 'Office Manager' ) : __( '公司行政', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '商务接待体验很好，提前预约流程非常顺畅。', 'Excellent for business hosting, and the reservation process was seamless.' ) : __( '商务接待体验很好，提前预约流程非常顺畅。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'dianping',
                            'verified' => 'verified',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '王小姐', 'Sophia W.' ) : __( '王小姐', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '家庭用户', 'Family Guest' ) : __( '家庭用户', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '套餐搭配合理，老人小孩都能找到合适口味。', 'Great set combinations, with options that worked well for every age group.' ) : __( '套餐搭配合理，老人小孩都能找到合适口味。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'xiaohongshu',
                            'verified' => 'guest',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '在线订位', 'Table Booking' ) : __( '在线订位', 'developer-starter' ),
                    'booking_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '目前仅提供预约登记，提交后由门店人工确认', 'Reservation requests are reviewed and confirmed manually by the venue.' ) : __( '目前仅提供预约登记，提交后由门店人工确认', 'developer-starter' ),
                    'form_id'          => '',
                    'booking_layout'   => 'sidebar',
                    'sidebar_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '订位说明', 'Booking Notes' ) : __( '订位说明', 'developer-starter' ),
                    'sidebar_content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "可预约时间：11:00-21:30\n建议提前 2 小时预约\n团体用餐请备注人数与需求\n到店后报手机号即可", "Reservation hours: 11:00-21:30\nBook at least 2 hours in advance\nFor group dining, please note guest count and requests\nCheck in with your phone number on arrival" ) : __( "可预约时间：11:00-21:30\n建议提前 2 小时预约\n团体用餐请备注人数与需求\n到店后报手机号即可", 'developer-starter' ),
                    'contact_phone'    => '400-888-1234',
                    'contact_hours'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '营业时间 10:30-22:00', 'Open daily 10:30-22:00' ) : __( '营业时间 10:30-22:00', 'developer-starter' ),
                    'module_bg_color'  => '#f8fafc',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '订位常见问题', 'Booking FAQ' ) : __( '订位常见问题', 'developer-starter' ),
                    'faq_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '减少客服沟通成本，提升转化效率', 'Answer common booking questions up front and reduce friction.' ) : __( '减少客服沟通成本，提升转化效率', 'developer-starter' ),
                    'faq_items'    => array(
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可以临时加位吗？', 'Can we add more guests later?' ) : __( '可以临时加位吗？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可在到店前电话联系门店，视当日客流协调安排。', 'Yes. Please contact the venue before arrival and seating will be adjusted based on availability.' ) : __( '可在到店前电话联系门店，视当日客流协调安排。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '是否支持包厢预订？', 'Are private rooms available?' ) : __( '是否支持包厢预订？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '支持，请在预约备注中说明人数和用餐时段。', 'Yes. Please include your guest count and preferred dining time in the booking notes.' ) : __( '支持，请在预约备注中说明人数和用餐时段。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约后多久确认？', 'How soon is a booking confirmed?' ) : __( '预约后多久确认？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '通常 15 分钟内由门店电话或短信确认。', 'Most bookings are confirmed by phone or message within about 15 minutes.' ) : __( '通常 15 分钟内由门店电话或短信确认。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * 宠物官网预设模块
     *
     * @return array
     */
    private function get_pet_modules() {
        return array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'large',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image'      => '',
                            'title'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '给毛孩子一个 <strong>安心的家</strong>', 'A <strong>Safe Place</strong> for Every Pet' ) : __( '给毛孩子一个 <strong>安心的家</strong>', 'developer-starter' ),
                            'subtitle'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '宠物洗护、寄养、领养信息与到店预约一站式展示', 'Show grooming, boarding, adoption info, and store visits in one place.' ) : __( '宠物洗护、寄养、领养信息与到店预约一站式展示', 'developer-starter' ),
                            'btn_text'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约到店', 'Book a Visit' ) : __( '预约到店', 'developer-starter' ),
                            'btn_url'    => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'pet_profile',
                'data' => array(
                    'pet_title'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '在店<span style="color:#ec4899">萌宠档案</span>', 'Featured <span style="color:#ec4899">Pet Profiles</span>' ) : __( '在店<span style="color:#ec4899">萌宠档案</span>', 'developer-starter' ),
                    'pet_subtitle'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '支持展示领养信息、在售幼宠或门店明星宠物', 'Perfect for adoption listings, available pets, or your in-store favorites.' ) : __( '支持展示领养信息、在售幼宠或门店明星宠物', 'developer-starter' ),
                    'pet_columns'       => '4',
                    'pet_primary_color' => '#ec4899',
                    'pet_items'         => array(
                        array(
                            'image'  => '',
                            'name'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '奶糖', 'Mochi' ) : __( '奶糖', 'developer-starter' ),
                            'breed'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '布偶猫', 'Ragdoll Cat' ) : __( '布偶猫', 'developer-starter' ),
                            'gender' => 'female',
                            'age'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '5个月', '5 months' ) : __( '5个月', 'developer-starter' ),
                            'status' => 'available',
                            'tags'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '已体检, 性格温顺, 可亲近', 'Health checked, gentle temperament, friendly' ) : __( '已体检, 性格温顺, 可亲近', 'developer-starter' ),
                            'price'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '待咨询', 'Ask for details' ) : __( '待咨询', 'developer-starter' ),
                            'link'   => '#',
                        ),
                        array(
                            'image'  => '',
                            'name'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可乐', 'Coco' ) : __( '可乐', 'developer-starter' ),
                            'breed'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '柯基犬', 'Corgi' ) : __( '柯基犬', 'developer-starter' ),
                            'gender' => 'male',
                            'age'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '8个月', '8 months' ) : __( '8个月', 'developer-starter' ),
                            'status' => 'reserved',
                            'tags'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '活泼, 会坐下握手, 疫苗齐全', 'Playful, knows basic tricks, fully vaccinated' ) : __( '活泼, 会坐下握手, 疫苗齐全', 'developer-starter' ),
                            'price'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '待咨询', 'Ask for details' ) : __( '待咨询', 'developer-starter' ),
                            'link'   => '#',
                        ),
                        array(
                            'image'  => '',
                            'name'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '元宝', 'Lucky' ) : __( '元宝', 'developer-starter' ),
                            'breed'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '中华田园猫', 'Domestic Shorthair' ) : __( '中华田园猫', 'developer-starter' ),
                            'gender' => 'male',
                            'age'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '1岁', '1 year' ) : __( '1岁', 'developer-starter' ),
                            'status' => 'available',
                            'tags'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '已绝育, 可领养, 亲人', 'Neutered, ready for adoption, affectionate' ) : __( '已绝育, 可领养, 亲人', 'developer-starter' ),
                            'price'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '免费领养', 'Free adoption' ) : __( '免费领养', 'developer-starter' ),
                            'link'   => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '门店服务', 'Pet Services' ) : __( '门店服务', 'developer-starter' ),
                    'services_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '围绕日常护理与健康管理的标准化服务', 'Standardized care built around grooming, boarding, and wellness support.' ) : __( '围绕日常护理与健康管理的标准化服务', 'developer-starter' ),
                    'services_columns'  => '4',
                    'services_items'    => array(
                        array(
                            'icon'  => '🛁',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '洗护美容', 'Grooming' ) : __( '洗护美容', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础洗护、造型修剪、皮毛护理', 'Bathing, styling trims, and coat care.' ) : __( '基础洗护、造型修剪、皮毛护理', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🏠',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '安心寄养', 'Boarding Care' ) : __( '安心寄养', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '分区看护、每日反馈、按时喂养', 'Separated care areas, daily updates, and scheduled feeding.' ) : __( '分区看护、每日反馈、按时喂养', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🩺',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '健康咨询', 'Wellness Guidance' ) : __( '健康咨询', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础健康评估与日常喂养建议', 'Basic wellness assessments and feeding advice.' ) : __( '基础健康评估与日常喂养建议', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🎓',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '新手养宠指导', 'New Owner Support' ) : __( '新手养宠指导', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '从饮食到行为训练的入门陪跑', 'Starter guidance from feeding routines to behavior training.' ) : __( '从饮食到行为训练的入门陪跑', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '宠主反馈', 'Pet Owner Reviews' ) : __( '宠主反馈', 'developer-starter' ),
                    'testimonials_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '提升信任感，降低首次到店决策门槛', 'Build trust and make first visits easier to decide on.' ) : __( '提升信任感，降低首次到店决策门槛', 'developer-starter' ),
                    'testimonials_columns'  => '3',
                    'testimonials_items'    => array(
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '赵女士', 'Zoe Z.' ) : __( '赵女士', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '猫咪主人', 'Cat Owner' ) : __( '猫咪主人', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '洗护前后状态变化很明显，店员沟通专业。', 'The grooming results were obvious, and the staff communication felt very professional.' ) : __( '洗护前后状态变化很明显，店员沟通专业。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'dianping',
                            'verified' => 'verified',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '孙先生', 'Sam S.' ) : __( '孙先生', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '狗狗主人', 'Dog Owner' ) : __( '狗狗主人', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '寄养期间每天都有反馈，出差更放心。', 'We received updates every day during boarding, which made traveling much easier.' ) : __( '寄养期间每天都有反馈，出差更放心。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'meituan',
                            'verified' => 'guest',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '周小姐', 'Chloe Z.' ) : __( '周小姐', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '领养家庭', 'Adoption Family' ) : __( '领养家庭', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '领养流程清晰，后续回访也很认真。', 'The adoption steps were clear, and the follow-up after adoption was thoughtful.' ) : __( '领养流程清晰，后续回访也很认真。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'xiaohongshu',
                            'verified' => 'verified',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约到店', 'Book a Visit' ) : __( '预约到店', 'developer-starter' ),
                    'booking_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '支持洗护/寄养/咨询预约，提交后门店人工确认', 'Grooming, boarding, and consultation requests are manually confirmed by the store.' ) : __( '支持洗护/寄养/咨询预约，提交后门店人工确认', 'developer-starter' ),
                    'form_id'          => '',
                    'booking_layout'   => 'sidebar',
                    'sidebar_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约须知', 'Booking Notes' ) : __( '预约须知', 'developer-starter' ),
                    'sidebar_content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "请提前填写宠物年龄与品种\n如有疫苗记录可一并备注\n首次到店建议提前 10 分钟\n暂不涉及在线支付", "Please include your pet's age and breed\nYou can also note vaccination records\nFor first visits, please arrive 10 minutes early\nOnline payment is not included in this preset" ) : __( "请提前填写宠物年龄与品种\n如有疫苗记录可一并备注\n首次到店建议提前 10 分钟\n暂不涉及在线支付", 'developer-starter' ),
                    'contact_phone'    => '400-666-8899',
                    'contact_hours'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '每日 09:00-21:00', 'Open daily 09:00-21:00' ) : __( '每日 09:00-21:00', 'developer-starter' ),
                    'module_bg_color'  => '#fff7fb',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约常见问题', 'Visit FAQ' ) : __( '预约常见问题', 'developer-starter' ),
                    'faq_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '提前解答高频问题，减少重复咨询', 'Answer common visit questions ahead of time and reduce repeated inquiries.' ) : __( '提前解答高频问题，减少重复咨询', 'developer-starter' ),
                    'faq_items'    => array(
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '首次到店需要准备什么？', 'What should I prepare for a first visit?' ) : __( '首次到店需要准备什么？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '建议携带宠物基本信息与疫苗记录，便于快速建档。', 'Bring your pet’s basic information and vaccination records for faster check-in.' ) : __( '建议携带宠物基本信息与疫苗记录，便于快速建档。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '寄养是否支持视频查看？', 'Are boarding updates available by video?' ) : __( '寄养是否支持视频查看？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可按门店安排定时发送照片或短视频反馈。', 'Yes. The store can share scheduled photo or short video updates.' ) : __( '可按门店安排定时发送照片或短视频反馈。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可以当天临时预约吗？', 'Can I book on the same day?' ) : __( '可以当天临时预约吗？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可提交预约，门店会根据时段空位尽快确认。', 'Yes. Submit a request and the store will confirm as soon as possible based on availability.' ) : __( '可提交预约，门店会根据时段空位尽快确认。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * 旅行官网预设模块
     *
     * @return array
     */
    private function get_travel_modules() {
        return array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'large',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image'      => '',
                            'title'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '下一站，去看更大的世界', 'Your Next Journey Starts Here' ) : __( '下一站，去看更大的世界', 'developer-starter' ),
                            'subtitle'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门线路、行程说明、景点门票与预约咨询一页搞定', 'Packages, itineraries, attraction tickets, and trip inquiries in one place.' ) : __( '热门线路、行程说明、景点门票与预约咨询一页搞定', 'developer-starter' ),
                            'btn_text'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '咨询行程', 'Plan My Trip' ) : __( '咨询行程', 'developer-starter' ),
                            'btn_url'    => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'tour-package',
                'data' => array(
                    'tour_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '热门旅游线路', 'Popular Tour Packages' ) : __( '热门旅游线路', 'developer-starter' ),
                    'tour_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '按客群和预算快速筛选适合方案', 'Find the right itinerary by traveler type and budget.' ) : __( '按客群和预算快速筛选适合方案', 'developer-starter' ),
                    'tour_columns'  => '3',
                    'tour_items'    => array(
                        array(
                            'image'          => '',
                            'title'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '海南轻奢海岛 5 日', 'Luxury Island Escape 5 Days' ) : __( '海南轻奢海岛 5 日', 'developer-starter' ),
                            'destination'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '三亚', 'Sanya' ) : __( '三亚', 'developer-starter' ),
                            'departure'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全国多地可出发', 'Departures from multiple cities' ) : __( '全国多地可出发', 'developer-starter' ),
                            'days'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '5天4晚', '5 days / 4 nights' ) : __( '5天4晚', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 3299 ) : '¥3299',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 3999 ) : '¥3999',
                            'badge'          => 'hot',
                            'highlights'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "海景酒店\n蜈支洲岛\n亚龙湾自由活动", "Ocean-view resort\nIsland excursion\nFree time by the bay" ) : __( "海景酒店\n蜈支洲岛\n亚龙湾自由活动", 'developer-starter' ),
                            'link'           => '#',
                        ),
                        array(
                            'image'          => '',
                            'title'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '云南人文风光 6 日', 'Culture and Scenery Tour 6 Days' ) : __( '云南人文风光 6 日', 'developer-starter' ),
                            'destination'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '昆明-大理-丽江', 'Kunming - Dali - Lijiang' ) : __( '昆明-大理-丽江', 'developer-starter' ),
                            'departure'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '上海/杭州/南京', 'Departures from Shanghai / Hangzhou / Nanjing' ) : __( '上海/杭州/南京', 'developer-starter' ),
                            'days'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '6天5晚', '6 days / 5 nights' ) : __( '6天5晚', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 3699 ) : '¥3699',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 4299 ) : '¥4299',
                            'badge'          => 'recommend',
                            'highlights'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "洱海环线\n古城夜游\n特色民俗体验", "Lakeside route\nOld town evening walk\nCultural experiences" ) : __( "洱海环线\n古城夜游\n特色民俗体验", 'developer-starter' ),
                            'link'           => '#',
                        ),
                        array(
                            'image'          => '',
                            'title'          => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '亲子自然探索 4 日', 'Family Nature Escape 4 Days' ) : __( '亲子自然探索 4 日', 'developer-starter' ),
                            'destination'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '张家界', 'Zhangjiajie' ) : __( '张家界', 'developer-starter' ),
                            'departure'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '华中地区优先', 'Best for central China departures' ) : __( '华中地区优先', 'developer-starter' ),
                            'days'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '4天3晚', '4 days / 3 nights' ) : __( '4天3晚', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 2599 ) : '¥2599',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 2999 ) : '¥2999',
                            'badge'          => 'new',
                            'highlights'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "亲子友好酒店\n轻徒步线路\n研学讲解", "Family-friendly hotel\nEasy hiking route\nEducational guide service" ) : __( "亲子友好酒店\n轻徒步线路\n研学讲解", 'developer-starter' ),
                            'link'           => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'itinerary',
                'data' => array(
                    'itinerary_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '参考行程安排', 'Sample Itinerary' ) : __( '参考行程安排', 'developer-starter' ),
                    'itinerary_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可根据团队需求自定义微调', 'Tailor this sample schedule to your group needs.' ) : __( '可根据团队需求自定义微调', 'developer-starter' ),
                    'itinerary_layout'   => 'left',
                    'itinerary_days'     => array(
                        array(
                            'day_number'    => 'Day 1',
                            'day_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '抵达与入住', 'Arrival and Check-In' ) : __( '抵达与入住', 'developer-starter' ),
                            'day_image'     => '',
                            'morning'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '抵达目的地，专车接机/接站。', 'Arrive at the destination with airport or station pickup.' ) : __( '抵达目的地，专车接机/接站。', 'developer-starter' ),
                            'afternoon'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '办理入住，熟悉周边环境，自由活动。', 'Check in, get familiar with the area, and enjoy free time.' ) : __( '办理入住，熟悉周边环境，自由活动。', 'developer-starter' ),
                            'evening'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '欢迎晚餐与行程说明会。', 'Welcome dinner and trip briefing.' ) : __( '欢迎晚餐与行程说明会。', 'developer-starter' ),
                            'meals'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '晚餐', 'Dinner' ) : __( '晚餐', 'developer-starter' ),
                            'accommodation' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当地精选酒店', 'Selected local hotel' ) : __( '当地精选酒店', 'developer-starter' ),
                            'attractions'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "城市地标\n夜景步行街", "City landmark\nNight promenade" ) : __( "城市地标\n夜景步行街", 'developer-starter' ),
                        ),
                        array(
                            'day_number'    => 'Day 2',
                            'day_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '核心景点深度游', 'Flagship Sightseeing Day' ) : __( '核心景点深度游', 'developer-starter' ),
                            'day_image'     => '',
                            'morning'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '前往核心景区，安排导览与讲解。', 'Visit the signature attraction with guided commentary.' ) : __( '前往核心景区，安排导览与讲解。', 'developer-starter' ),
                            'afternoon'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '体验特色活动，预留拍照与自由时段。', 'Join featured activities with time for photos and free exploration.' ) : __( '体验特色活动，预留拍照与自由时段。', 'developer-starter' ),
                            'evening'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '返回酒店，晚间自由活动。', 'Return to the hotel and enjoy the evening at leisure.' ) : __( '返回酒店，晚间自由活动。', 'developer-starter' ),
                            'meals'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '早餐/午餐', 'Breakfast/Lunch' ) : __( '早餐/午餐', 'developer-starter' ),
                            'accommodation' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当地精选酒店', 'Selected local hotel' ) : __( '当地精选酒店', 'developer-starter' ),
                            'attractions'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "核心景区\n文化街区", "Main attraction\nCultural district" ) : __( "核心景区\n文化街区", 'developer-starter' ),
                        ),
                        array(
                            'day_number'    => 'Day 3',
                            'day_title'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '返程', 'Departure' ) : __( '返程', 'developer-starter' ),
                            'day_image'     => '',
                            'morning'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '酒店早餐后办理退房。', 'Breakfast at the hotel followed by check-out.' ) : __( '酒店早餐后办理退房。', 'developer-starter' ),
                            'afternoon'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '根据航班/车次安排送机送站。', 'Transfer to the airport or station based on your departure schedule.' ) : __( '根据航班/车次安排送机送站。', 'developer-starter' ),
                            'evening'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '行程结束。', 'End of the itinerary.' ) : __( '行程结束。', 'developer-starter' ),
                            'meals'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '早餐', 'Breakfast' ) : __( '早餐', 'developer-starter' ),
                            'accommodation' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '无', 'None' ) : __( '无', 'developer-starter' ),
                            'attractions'   => '',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'ticket-showcase',
                'data' => array(
                    'ticket_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '景点门票与增值项目', 'Tickets and Add-On Experiences' ) : __( '景点门票与增值项目', 'developer-starter' ),
                    'ticket_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '支持自由行单订或线路打包', 'Available for standalone booking or package upgrades.' ) : __( '支持自由行单订或线路打包', 'developer-starter' ),
                    'ticket_columns'  => '3',
                    'ticket_items'    => array(
                        array(
                            'image'          => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '核心景区成人票', 'Adult Ticket' ) : __( '核心景区成人票', 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '电子票，含景区主入口权益', 'Digital admission ticket with main gate access.' ) : __( '电子票，含景区主入口权益', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 168 ) : '¥168',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 198 ) : '¥198',
                            'validity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预订日起 30 天有效', 'Valid for 30 days from booking' ) : __( '预订日起 30 天有效', 'developer-starter' ),
                            'ticket_type'    => 'e-ticket',
                            'audience'       => 'adult',
                            'features'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "快速入园\n客服协助改期", "Fast entry\nReschedule support" ) : __( "快速入园\n客服协助改期", 'developer-starter' ),
                            'badge'          => 'recommend',
                            'btn_text'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约咨询', 'Request Booking' ) : __( '预约咨询', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                        array(
                            'image'          => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '亲子套票', 'Family Ticket Package' ) : __( '亲子套票', 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '1 大 1 小，适合家庭游客', '1 adult + 1 child package for families.' ) : __( '1 大 1 小，适合家庭游客', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 238 ) : '¥238',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 298 ) : '¥298',
                            'validity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '指定日期有效', 'Valid on selected dates' ) : __( '指定日期有效', 'developer-starter' ),
                            'ticket_type'    => 'e-ticket',
                            'audience'       => 'family',
                            'features'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "亲子友好路线\n专属客服", "Family-friendly route\nDedicated support" ) : __( "亲子友好路线\n专属客服", 'developer-starter' ),
                            'badge'          => 'hot',
                            'btn_text'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约咨询', 'Request Booking' ) : __( '预约咨询', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '行程预约咨询', 'Trip Consultation' ) : __( '行程预约咨询', 'developer-starter' ),
                    'booking_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '提交需求后由顾问 1 对 1 回访，暂不在线支付', 'After you submit your request, a travel consultant will follow up one-on-one. Online payment is not included.' ) : __( '提交需求后由顾问 1 对 1 回访，暂不在线支付', 'developer-starter' ),
                    'form_id'          => '',
                    'booking_layout'   => 'sidebar',
                    'sidebar_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '咨询说明', 'Consultation Notes' ) : __( '咨询说明', 'developer-starter' ),
                    'sidebar_content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "请填写出发城市和时间\n可备注人数与预算范围\n支持企业团建与家庭定制\n顾问一般 30 分钟内回复", "Please include your departure city and preferred dates\nYou can also note guest count and budget range\nPrivate groups and company retreats are supported\nA consultant usually replies within 30 minutes" ) : __( "请填写出发城市和时间\n可备注人数与预算范围\n支持企业团建与家庭定制\n顾问一般 30 分钟内回复", 'developer-starter' ),
                    'contact_phone'    => '400-700-2288',
                    'contact_hours'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '09:00-21:00 在线服务', 'Online support 09:00-21:00' ) : __( '09:00-21:00 在线服务', 'developer-starter' ),
                    'module_bg_color'  => '#f8fafc',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '出行问答', 'Travel FAQ' ) : __( '出行问答', 'developer-starter' ),
                    'faq_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '先解答，再转化', 'Address key questions first and shorten the decision path.' ) : __( '先解答，再转化', 'developer-starter' ),
                    'faq_items'    => array(
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '线路可以私家团定制吗？', 'Can the itinerary be customized for a private group?' ) : __( '线路可以私家团定制吗？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可以，支持 2-20 人私家团与企业团建行程定制。', 'Yes. Private groups of 2-20 guests and company retreats can be customized.' ) : __( '可以，支持 2-20 人私家团与企业团建行程定制。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '机酒是否包含在报价里？', 'Are flights and hotels included in the quoted price?' ) : __( '机酒是否包含在报价里？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '按线路不同可含机酒，也可仅提供地接服务。', 'Depending on the package, flights and hotels may be included, or local land services can be arranged separately.' ) : __( '按线路不同可含机酒，也可仅提供地接服务。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约后如何确认？', 'How is the request confirmed after booking?' ) : __( '预约后如何确认？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '顾问会通过电话或微信与您确认需求并出方案。', 'A consultant will confirm your needs by phone or message and prepare a proposal.' ) : __( '顾问会通过电话或微信与您确认需求并出方案。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * 民宿官网预设模块
     *
     * @return array
     */
    private function get_homestay_modules() {
        return array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'large',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image'      => '',
                            'title'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '住进风景里，慢下来', 'Stay Closer to the View' ) : __( '住进风景里，慢下来', 'developer-starter' ),
                            'subtitle'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '房型展示、配套设施、住客评价与预约入口完整联动', 'Rooms, amenities, guest reviews, and booking access all working together.' ) : __( '房型展示、配套设施、住客评价与预约入口完整联动', 'developer-starter' ),
                            'btn_text'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '立即预约', 'Book Your Stay' ) : __( '立即预约', 'developer-starter' ),
                            'btn_url'    => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'room-showcase',
                'data' => array(
                    'room_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '房型选择', 'Room Options' ) : __( '房型选择', 'developer-starter' ),
                    'room_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '不同人群与预算都能找到舒适空间', 'Comfortable spaces for different guests and budgets.' ) : __( '不同人群与预算都能找到舒适空间', 'developer-starter' ),
                    'room_columns'  => '3',
                    'room_items'    => array(
                        array(
                            'image'          => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '山景大床房', 'Mountain View King Room' ) : __( '山景大床房', 'developer-starter' ),
                            'area'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '32㎡', '32 sqm' ) : '32㎡',
                            'bed_type'       => 'king',
                            'capacity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '2人', '2 guests' ) : __( '2人', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 468, function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : '/晚' ) : '¥468/晚',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 568, function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : '/晚' ) : '¥568/晚',
                            'badge'          => 'recommend',
                            'amenities'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "icon-wifi|免费WiFi\nicon-breakfast|含双早\nicon-park|停车便利", "icon-wifi|Free Wi-Fi\nicon-breakfast|Breakfast for two\nicon-park|Easy parking" ) : __( "icon-wifi|免费WiFi\nicon-breakfast|含双早\nicon-park|停车便利", 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '采光充足，适合情侣与周末小住。', 'Bright and comfortable, ideal for couples and short weekend stays.' ) : __( '采光充足，适合情侣与周末小住。', 'developer-starter' ),
                            'btn_text'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约入住', 'Reserve Now' ) : __( '预约入住', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                        array(
                            'image'          => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '家庭亲子房', 'Family Room' ) : __( '家庭亲子房', 'developer-starter' ),
                            'area'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '46㎡', '46 sqm' ) : '46㎡',
                            'bed_type'       => 'family',
                            'capacity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '3-4人', '3-4 guests' ) : __( '3-4人', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 688, function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : '/晚' ) : '¥688/晚',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 788, function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : '/晚' ) : '¥788/晚',
                            'badge'          => 'garden',
                            'amenities'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "icon-bath|独立卫浴\nicon-tv|投影设备\nicon-sofa|会客区", "icon-bath|Private bathroom\nicon-tv|Projector setup\nicon-sofa|Lounge area" ) : __( "icon-bath|独立卫浴\nicon-tv|投影设备\nicon-sofa|会客区", 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '空间宽敞，适合亲子与家庭出行。', 'Spacious layout designed for family trips and longer stays.' ) : __( '空间宽敞，适合亲子与家庭出行。', 'developer-starter' ),
                            'btn_text'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约入住', 'Reserve Now' ) : __( '预约入住', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                        array(
                            'image'          => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '露台景观套房', 'Terrace View Suite' ) : __( '露台景观套房', 'developer-starter' ),
                            'area'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '58㎡', '58 sqm' ) : '58㎡',
                            'bed_type'       => 'queen',
                            'capacity'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '2人', '2 guests' ) : __( '2人', 'developer-starter' ),
                            'price'          => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 888, function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : '/晚' ) : '¥888/晚',
                            'original_price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1088, function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '/晚', '/night' ) : '/晚' ) : '¥1088/晚',
                            'badge'          => 'luxury',
                            'amenities'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "icon-cafe|迷你吧\nicon-camera|景观露台\nicon-service|管家服务", "icon-cafe|Minibar\nicon-camera|View terrace\nicon-service|Concierge service" ) : __( "icon-cafe|迷你吧\nicon-camera|景观露台\nicon-service|管家服务", 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合纪念日与高品质度假场景。', 'Ideal for special occasions and premium leisure stays.' ) : __( '适合纪念日与高品质度假场景。', 'developer-starter' ),
                            'btn_text'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约入住', 'Reserve Now' ) : __( '预约入住', 'developer-starter' ),
                            'btn_link'       => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'hotel-amenities',
                'data' => array(
                    'amenity_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '民宿配套', 'Guest Amenities' ) : __( '民宿配套', 'developer-starter' ),
                    'amenity_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '让入住体验更完整的公共服务', 'Shared services that make each stay more complete.' ) : __( '让入住体验更完整的公共服务', 'developer-starter' ),
                    'amenity_columns'  => '4',
                    'amenity_layout'   => 'card',
                    'amenity_items'    => array(
                        array(
                            'icon'           => 'icon-cafe',
                            'fallback_image' => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '早餐供应', 'Breakfast Service' ) : __( '早餐供应', 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '本地食材早餐，支持素食备注', 'Breakfast with local ingredients and vegetarian options on request.' ) : __( '本地食材早餐，支持素食备注', 'developer-starter' ),
                            'time'           => '07:30-10:00',
                        ),
                        array(
                            'icon'           => 'icon-wifi',
                            'fallback_image' => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '公共 WiFi', 'Wi-Fi Access' ) : __( '公共 WiFi', 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全区域覆盖，适合远程办公', 'Available across the property and suitable for remote work.' ) : __( '全区域覆盖，适合远程办公', 'developer-starter' ),
                            'time'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全天', 'All day' ) : __( '全天', 'developer-starter' ),
                        ),
                        array(
                            'icon'           => 'icon-car',
                            'fallback_image' => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '停车服务', 'Parking' ) : __( '停车服务', 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '提供免费停车位（数量有限）', 'Complimentary parking is available in limited numbers.' ) : __( '提供免费停车位（数量有限）', 'developer-starter' ),
                            'time'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '全天', 'All day' ) : __( '全天', 'developer-starter' ),
                        ),
                        array(
                            'icon'           => 'icon-map',
                            'fallback_image' => '',
                            'name'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '出游建议', 'Local Tips' ) : __( '出游建议', 'developer-starter' ),
                            'desc'           => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '管家推荐周边路线和餐饮清单', 'Local route suggestions and dining recommendations from the host.' ) : __( '管家推荐周边路线和餐饮清单', 'developer-starter' ),
                            'time'           => '09:00-20:00',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'gallery',
                'data' => array(
                    'gallery_title'       => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '空间展示', 'Property Gallery' ) : __( '空间展示', 'developer-starter' ),
                    'gallery_subtitle'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '你可直接替换为真实环境图', 'Replace these placeholders with your real property photos.' ) : __( '你可直接替换为真实环境图', 'developer-starter' ),
                    'gallery_columns'     => '4',
                    'gallery_style'       => 'grid',
                    'enable_filter'       => 'yes',
                    'filter_categories'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '外观,客房,餐厅,公共区域', 'Exterior,Rooms,Dining,Shared Areas' ) : __( '外观,客房,餐厅,公共区域', 'developer-starter' ),
                    'gallery_lightbox'    => '1',
                    'gallery_images'      => array(
                        array(
                            'image'    => '',
                            'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '民宿外观', 'Exterior View' ) : __( '民宿外观', 'developer-starter' ),
                            'desc'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '门头与入口环境', 'Front entrance and arrival experience.' ) : __( '门头与入口环境', 'developer-starter' ),
                            'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '外观', 'Exterior' ) : __( '外观', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '客房细节', 'Room Details' ) : __( '客房细节', 'developer-starter' ),
                            'desc'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '床品与光线效果', 'Bedding, textures, and natural light.' ) : __( '床品与光线效果', 'developer-starter' ),
                            'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '客房', 'Rooms' ) : __( '客房', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '早餐区', 'Breakfast Area' ) : __( '早餐区', 'developer-starter' ),
                            'desc'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '用餐空间', 'Dining and shared breakfast space.' ) : __( '用餐空间', 'developer-starter' ),
                            'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '餐厅', 'Dining' ) : __( '餐厅', 'developer-starter' ),
                        ),
                        array(
                            'image'    => '',
                            'title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '公共客厅', 'Shared Lounge' ) : __( '公共客厅', 'developer-starter' ),
                            'desc'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '休闲阅读区域', 'Reading and relaxation area.' ) : __( '休闲阅读区域', 'developer-starter' ),
                            'category' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '公共区域', 'Shared Areas' ) : __( '公共区域', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '住客评价', 'Guest Reviews' ) : __( '住客评价', 'developer-starter' ),
                    'testimonials_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '展示真实口碑，提升预订转化', 'Share real guest impressions to improve booking confidence.' ) : __( '展示真实口碑，提升预订转化', 'developer-starter' ),
                    'testimonials_columns'  => '3',
                    'testimonials_items'    => array(
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '杨女士', 'Amy Y.' ) : __( '杨女士', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '周末度假客人', 'Weekend Guest' ) : __( '周末度假客人', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '房间干净安静，老板推荐的周边线路很实用。', 'The room was clean and quiet, and the host’s local recommendations were very useful.' ) : __( '房间干净安静，老板推荐的周边线路很实用。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'ctrip',
                            'verified' => 'guest',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '蒋先生', 'James J.' ) : __( '蒋先生', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '家庭出行', 'Family Stay' ) : __( '家庭出行', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '亲子房空间大，早餐味道也不错。', 'The family room was spacious and the breakfast was a pleasant surprise.' ) : __( '亲子房空间大，早餐味道也不错。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'fliggy',
                            'verified' => 'verified',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '何小姐', 'Holly H.' ) : __( '何小姐', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '自由职业者', 'Remote Worker' ) : __( '自由职业者', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'WiFi 稳定，适合边住边办公。', 'The Wi-Fi was stable enough to stay and work comfortably.' ) : __( 'WiFi 稳定，适合边住边办公。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'booking',
                            'verified' => 'verified',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '在线预约入住', 'Stay Reservation' ) : __( '在线预约入住', 'developer-starter' ),
                    'booking_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当前仅做预约登记，确认后由人工联系', 'This preset collects reservation requests first and confirms them manually.' ) : __( '当前仅做预约登记，确认后由人工联系', 'developer-starter' ),
                    'form_id'          => '',
                    'booking_layout'   => 'sidebar',
                    'sidebar_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '入住说明', 'Stay Notes' ) : __( '入住说明', 'developer-starter' ),
                    'sidebar_content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "请填写入住与离店日期\n可备注出行人数和需求\n节假日请尽量提前预约\n暂不支持在线付款", "Please include your check-in and check-out dates\nYou can note guest count and special requests\nFor holidays, please book as early as possible\nOnline payment is not included in this preset" ) : __( "请填写入住与离店日期\n可备注出行人数和需求\n节假日请尽量提前预约\n暂不支持在线付款", 'developer-starter' ),
                    'contact_phone'    => '400-777-9090',
                    'contact_hours'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '08:00-22:00 客服在线', 'Guest support 08:00-22:00' ) : __( '08:00-22:00 客服在线', 'developer-starter' ),
                    'module_bg_color'  => '#f8fafc',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '入住问答', 'Stay FAQ' ) : __( '入住问答', 'developer-starter' ),
                    'faq_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '降低咨询成本，提高下单效率', 'Reduce repeated questions and make booking decisions easier.' ) : __( '降低咨询成本，提高下单效率', 'developer-starter' ),
                    'faq_items'    => array(
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '宠物可以入住吗？', 'Are pets allowed to stay?' ) : __( '宠物可以入住吗？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可根据房型和时段申请，建议预约时提前说明。', 'Pets may be allowed depending on room type and dates. Please mention this before booking.' ) : __( '可根据房型和时段申请，建议预约时提前说明。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '是否提供发票？', 'Can invoices be provided?' ) : __( '是否提供发票？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '支持，确认入住后可开具对应票据。', 'Yes. Invoices can be issued after the reservation is confirmed.' ) : __( '支持，确认入住后可开具对应票据。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '最晚几点可办理入住？', 'How late can guests check in?' ) : __( '最晚几点可办理入住？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '通常支持至 22:00，超时请提前联系管家。', 'Check-in is usually available until 22:00. Please contact the host in advance for later arrivals.' ) : __( '通常支持至 22:00，超时请提前联系管家。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * 医美官网预设模块
     *
     * @return array
     */
    private function get_medical_beauty_modules() {
        return array(
            array(
                'type' => 'banner',
                'data' => array(
                    'banner_layout' => 'slider',
                    'banner_height' => 'large',
                    'banner_slides' => array(
                        array(
                            'media_type' => 'image',
                            'image'      => '',
                            'title'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '科学审美 · 合规医美', 'Evidence-Based Aesthetics' ) : __( '科学审美 · 合规医美', 'developer-starter' ),
                            'subtitle'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '项目介绍、价格参考、流程说明与预约咨询一体化官网', 'Service overviews, pricing guidance, process details, and consultation booking in one site.' ) : __( '项目介绍、价格参考、流程说明与预约咨询一体化官网', 'developer-starter' ),
                            'btn_text'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约面诊', 'Book a Consultation' ) : __( '预约面诊', 'developer-starter' ),
                            'btn_url'    => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'services',
                'data' => array(
                    'services_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '项目分类', 'Treatment Categories' ) : __( '项目分类', 'developer-starter' ),
                    'services_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '根据诉求快速匹配适合方案', 'Quickly match services to your care goals.' ) : __( '根据诉求快速匹配适合方案', 'developer-starter' ),
                    'services_columns'  => '4',
                    'services_items'    => array(
                        array(
                            'icon'  => '💧',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '皮肤管理', 'Skin Care' ) : __( '皮肤管理', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '补水焕肤、控油修护、敏感肌管理', 'Hydration, texture renewal, oil balance, and sensitive skin care.' ) : __( '补水焕肤、控油修护、敏感肌管理', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '✨',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '光电紧致', 'Energy-Based Tightening' ) : __( '光电紧致', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '面部提升、轮廓紧致、肤质改善', 'Focused on lifting, contour definition, and skin quality.' ) : __( '面部提升、轮廓紧致、肤质改善', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🧬',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '注射微整', 'Injectable Treatments' ) : __( '注射微整', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '个性化面部精修与衰老管理方案', 'Personalized facial refinement and aging-care plans.' ) : __( '个性化面部精修与衰老管理方案', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'icon'  => '🌿',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '术后修复', 'Recovery Support' ) : __( '术后修复', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '术后护理与康复追踪指导', 'Post-treatment care and guided recovery follow-up.' ) : __( '术后护理与康复追踪指导', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'menu',
                'data' => array(
                    'menu_title'        => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '项目<span style="color:#ec4899">价目参考</span>', 'Treatment <span style="color:#ec4899">Pricing</span>' ) : __( '项目<span style="color:#ec4899">价目参考</span>', 'developer-starter' ),
                    'menu_subtitle'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '可按门店实际项目与活动期价格自行调整', 'Adjust these sample prices based on your clinic services and campaigns.' ) : __( '可按门店实际项目与活动期价格自行调整', 'developer-starter' ),
                    'menu_layout'       => 'list',
                    'menu_accent_color' => '#ec4899',
                    'menu_items'        => array(
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '基础补水管理', 'Hydration Essentials' ) : __( '基础补水管理', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '适合日常维养与肤质稳定', 'A gentle option for routine care and skin balance.' ) : __( '适合日常维养与肤质稳定', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 398 ) : '¥398',
                            'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '入门', 'Starter' ) : __( '入门', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '进阶紧致管理', 'Firming Upgrade' ) : __( '进阶紧致管理', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '关注轮廓与紧致提升需求', 'Designed for contour refinement and skin tightening goals.' ) : __( '关注轮廓与紧致提升需求', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_demo_price_text' ) ? developer_starter_get_demo_price_text( 1280 ) : '¥1280',
                            'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '推荐', 'Featured' ) : __( '推荐', 'developer-starter' ),
                            'link'  => '#',
                        ),
                        array(
                            'image' => '',
                            'title' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '定制联合方案', 'Custom Treatment Plan' ) : __( '定制联合方案', 'developer-starter' ),
                            'desc'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '根据面诊结果制定个性化周期计划', 'Tailored in phases after consultation and assessment.' ) : __( '根据面诊结果制定个性化周期计划', 'developer-starter' ),
                            'price' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '面诊后报价', 'Quoted after consultation' ) : __( '面诊后报价', 'developer-starter' ),
                            'badge' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '定制', 'Custom' ) : __( '定制', 'developer-starter' ),
                            'link'  => '#',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'curriculum',
                'data' => array(
                    'curriculum_title'         => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '服务流程', 'Treatment Journey' ) : __( '服务流程', 'developer-starter' ),
                    'curriculum_subtitle'      => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '流程公开透明，便于用户建立信任', 'A transparent process designed to build confidence and trust.' ) : __( '流程公开透明，便于用户建立信任', 'developer-starter' ),
                    'curriculum_primary_color' => '#ec4899',
                    'curriculum_items'         => array(
                        array(
                            'title'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'Step 1：预约建档', 'Step 1: Consultation Request' ) : __( 'Step 1：预约建档', 'developer-starter' ),
                            'meta'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '线上提交信息', 'Submit your details online' ) : __( '线上提交信息', 'developer-starter' ),
                            'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '<p>填写基础资料与期望诉求，客服确认到院时间。</p>', '<p>Share your basic details and goals, and the team will confirm your appointment time.</p>' ) : __( '<p>填写基础资料与期望诉求，客服确认到院时间。</p>', 'developer-starter' ),
                            'open'    => 'yes',
                        ),
                        array(
                            'title'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'Step 2：专业面诊', 'Step 2: Professional Assessment' ) : __( 'Step 2：专业面诊', 'developer-starter' ),
                            'meta'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '医生评估与沟通', 'Doctor evaluation and discussion' ) : __( '医生评估与沟通', 'developer-starter' ),
                            'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '<p>医生进行评估，沟通适配方案与注意事项。</p>', '<p>The doctor evaluates your needs and walks through suitable options and care notes.</p>' ) : __( '<p>医生进行评估，沟通适配方案与注意事项。</p>', 'developer-starter' ),
                            'open'    => 'no',
                        ),
                        array(
                            'title'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( 'Step 3：项目执行与复诊', 'Step 3: Treatment and Follow-Up' ) : __( 'Step 3：项目执行与复诊', 'developer-starter' ),
                            'meta'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '术后跟踪', 'Aftercare follow-up' ) : __( '术后跟踪', 'developer-starter' ),
                            'content' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '<p>执行后安排护理建议与复诊跟踪，确保恢复效果。</p>', '<p>After treatment, you receive care guidance and follow-up support for recovery.</p>' ) : __( '<p>执行后安排护理建议与复诊跟踪，确保恢复效果。</p>', 'developer-starter' ),
                            'open'    => 'no',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'testimonials',
                'data' => array(
                    'testimonials_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '用户反馈', 'Client Feedback' ) : __( '用户反馈', 'developer-starter' ),
                    'testimonials_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '展示真实体验，帮助访客形成预期', 'Real experiences that help visitors understand what to expect.' ) : __( '展示真实体验，帮助访客形成预期', 'developer-starter' ),
                    'testimonials_columns'  => '3',
                    'testimonials_items'    => array(
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '刘小姐', 'Olivia L.' ) : __( '刘小姐', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '皮肤管理用户', 'Skin care client' ) : __( '皮肤管理用户', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '面诊很细致，方案解释清楚，体验感很好。', 'The consultation was thorough, the plan was explained clearly, and the overall experience felt reassuring.' ) : __( '面诊很细致，方案解释清楚，体验感很好。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'xiaohongshu',
                            'verified' => 'verified',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '高女士', 'Grace G.' ) : __( '高女士', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '术后复诊用户', 'Aftercare client' ) : __( '术后复诊用户', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '术后护理提醒及时，恢复期管理很专业。', 'Follow-up reminders were timely, and the recovery guidance felt very professional.' ) : __( '术后护理提醒及时，恢复期管理很专业。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'weibo',
                            'verified' => 'vip',
                        ),
                        array(
                            'avatar'   => '',
                            'name'     => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '韩女士', 'Hannah H.' ) : __( '韩女士', 'developer-starter' ),
                            'position' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '新客', 'New client' ) : __( '新客', 'developer-starter' ),
                            'content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '咨询回复快，流程透明，没有被强行推销。', 'The team replied quickly, the process was transparent, and I never felt pressured.' ) : __( '咨询回复快，流程透明，没有被强行推销。', 'developer-starter' ),
                            'rating'   => '5',
                            'source'   => 'dianping',
                            'verified' => 'verified',
                        ),
                    ),
                ),
            ),
            array(
                'type' => 'booking-entry',
                'data' => array(
                    'booking_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约咨询入口', 'Consultation Booking' ) : __( '预约咨询入口', 'developer-starter' ),
                    'booking_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '先登记后确认，当前不接入在线支付', 'Submit your request first and confirm later. Online payment is not included in this preset.' ) : __( '先登记后确认，当前不接入在线支付', 'developer-starter' ),
                    'form_id'          => '',
                    'booking_layout'   => 'sidebar',
                    'sidebar_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约须知', 'Booking Notes' ) : __( '预约须知', 'developer-starter' ),
                    'sidebar_content'  => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( "请如实填写基础信息\n建议备注关注部位与目标\n客服会安排对应顾问回访\n面诊后再确定具体方案", "Please provide accurate basic information\nYou can note the treatment area and goals\nA suitable consultant will follow up with you\nSpecific plans are confirmed after consultation" ) : __( "请如实填写基础信息\n建议备注关注部位与目标\n客服会安排对应顾问回访\n面诊后再确定具体方案", 'developer-starter' ),
                    'contact_phone'    => '400-900-5566',
                    'contact_hours'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '10:00-20:00 专属顾问在线', 'Consultants online 10:00-20:00' ) : __( '10:00-20:00 专属顾问在线', 'developer-starter' ),
                    'module_bg_color'  => '#fff5f8',
                ),
            ),
            array(
                'type' => 'faq',
                'data' => array(
                    'faq_title'    => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '医美咨询常见问题', 'Aesthetic Consultation FAQ' ) : __( '医美咨询常见问题', 'developer-starter' ),
                    'faq_subtitle' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '把用户最关心的问题提前讲清楚', 'Answer the most common questions before visitors ask.' ) : __( '把用户最关心的问题提前讲清楚', 'developer-starter' ),
                    'faq_items'    => array(
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '线上可以直接确定项目吗？', 'Can treatments be finalized online?' ) : __( '线上可以直接确定项目吗？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '建议以面诊评估结果为准，线上先做初步咨询与预约。', 'It is best to confirm treatments after an in-person assessment. Online requests are used for initial consultation and booking.' ) : __( '建议以面诊评估结果为准，线上先做初步咨询与预约。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '预约后多久会联系？', 'How soon will someone follow up after booking?' ) : __( '预约后多久会联系？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '通常 30 分钟内会有顾问电话或微信回访。', 'A consultant usually follows up within about 30 minutes by phone or message.' ) : __( '通常 30 分钟内会有顾问电话或微信回访。', 'developer-starter' ),
                        ),
                        array(
                            'question' => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当前是否支持线上付款？', 'Is online payment supported?' ) : __( '当前是否支持线上付款？', 'developer-starter' ),
                            'answer'   => function_exists( 'developer_starter_get_locale_text' ) ? developer_starter_get_locale_text( '当前预设仅展示预约，不包含在线支付流程。', 'This preset only covers booking requests and does not include an online payment flow.' ) : __( '当前预设仅展示预约，不包含在线支付流程。', 'developer-starter' ),
                        ),
                    ),
                ),
            ),
        );
    }
}
