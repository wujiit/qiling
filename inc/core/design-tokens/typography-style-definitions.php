<?php
/**
 * 排版样式定义数据配置。
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'body'   => array(
                'label'  => __( '正文 Body', 'qiling' ),
                'sample' => __( '启灵主题正在展示正文排版与阅读节奏。', 'qiling' ),
            ),
            'small'  => array(
                'label'  => __( '辅助 Small', 'qiling' ),
                'sample' => __( '小字号常用于辅助信息、时间、备注。', 'qiling' ),
            ),
            'lead'   => array(
                'label'  => __( '导语 Lead', 'qiling' ),
                'sample' => __( 'Lead 常用于 Banner 副标题和页面导语。', 'qiling' ),
            ),
            'menu'   => array(
                'label'  => __( '导航 Menu', 'qiling' ),
                'sample' => __( '导航菜单 / 顶部入口', 'qiling' ),
            ),
            'button' => array(
                'label'  => __( '按钮 Button', 'qiling' ),
                'sample' => __( '立即咨询', 'qiling' ),
            ),
            'input'  => array(
                'label'  => __( '表单 Input', 'qiling' ),
                'sample' => __( '请输入关键词', 'qiling' ),
            ),
            'h1'     => array(
                'label'  => 'H1',
                'sample' => __( '品牌级主标题层级', 'qiling' ),
            ),
            'h2'     => array(
                'label'  => 'H2',
                'sample' => __( '页面主区块标题层级', 'qiling' ),
            ),
            'h3'     => array(
                'label'  => 'H3',
                'sample' => __( '内容卡片标题层级', 'qiling' ),
            ),
            'h4'     => array(
                'label'  => 'H4',
                'sample' => __( '次级内容标题层级', 'qiling' ),
            ),
            'h5'     => array(
                'label'  => 'H5',
                'sample' => __( '细分标题层级', 'qiling' ),
            ),
            'h6'     => array(
                'label'  => 'H6',
                'sample' => __( '微标题与标签层级', 'qiling' ),
            ),
        );
