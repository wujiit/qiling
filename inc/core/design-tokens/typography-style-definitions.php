<?php
/**
 * Data config for typography style definitions.
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
            'body'   => array(
                'label'  => __( '正文 Body', 'developer-starter' ),
                'sample' => __( '启灵主题正在展示正文排版与阅读节奏。', 'developer-starter' ),
            ),
            'small'  => array(
                'label'  => __( '辅助 Small', 'developer-starter' ),
                'sample' => __( '小字号常用于辅助信息、时间、备注。', 'developer-starter' ),
            ),
            'lead'   => array(
                'label'  => __( '导语 Lead', 'developer-starter' ),
                'sample' => __( 'Lead 常用于 Banner 副标题和页面导语。', 'developer-starter' ),
            ),
            'menu'   => array(
                'label'  => __( '导航 Menu', 'developer-starter' ),
                'sample' => __( '导航菜单 / 顶部入口', 'developer-starter' ),
            ),
            'button' => array(
                'label'  => __( '按钮 Button', 'developer-starter' ),
                'sample' => __( '立即咨询', 'developer-starter' ),
            ),
            'input'  => array(
                'label'  => __( '表单 Input', 'developer-starter' ),
                'sample' => __( '请输入关键词', 'developer-starter' ),
            ),
            'h1'     => array(
                'label'  => 'H1',
                'sample' => __( '品牌级主标题层级', 'developer-starter' ),
            ),
            'h2'     => array(
                'label'  => 'H2',
                'sample' => __( '页面主区块标题层级', 'developer-starter' ),
            ),
            'h3'     => array(
                'label'  => 'H3',
                'sample' => __( '内容卡片标题层级', 'developer-starter' ),
            ),
            'h4'     => array(
                'label'  => 'H4',
                'sample' => __( '次级内容标题层级', 'developer-starter' ),
            ),
            'h5'     => array(
                'label'  => 'H5',
                'sample' => __( '细分标题层级', 'developer-starter' ),
            ),
            'h6'     => array(
                'label'  => 'H6',
                'sample' => __( '微标题与标签层级', 'developer-starter' ),
            ),
        );
