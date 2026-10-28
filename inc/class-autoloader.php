<?php
/**
 * 主题类自动加载器（支持 Qiling 与 Developer_Starter 双命名空间）
 *
 * @package Qiling
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Qiling 与 Developer_Starter 命名空间类的自动加载函数
 *
 * @param string $class_name 完整的类名。
 * @return void
 */
function qiling_autoloader( $class_name ) {
    $is_qiling = strpos( $class_name, 'Qiling\\' ) === 0;
    $is_dev_starter = strpos( $class_name, 'Developer_Starter\\' ) === 0;

    // 仅处理属于当前主题的类
    if ( ! $is_qiling && ! $is_dev_starter ) {
        return;
    }

    // 移除命名空间前缀，获取相对类路径
    $relative_class = $is_qiling ? substr( $class_name, 7 ) : substr( $class_name, 18 );

    // 拆分命名空间层级
    $parts = explode( '\\', $relative_class );
    
    // 获取类名（最后一部分）
    $final_class_name = array_pop( $parts );
    
    // 将目录层级转换为小写
    $path_parts = array_map( 'strtolower', $parts );
    
    // 根据类名构建对应的文件名：
    // 1. 转为小写
    // 2. 下划线替换为连字符
    // 3. 添加 class- 前缀
    $filename = 'class-' . str_replace( '_', '-', strtolower( $final_class_name ) ) . '.php';
    
    // 拼接完整文件路径（QILING_INC 在 functions.php 中定义）
    $path = QILING_INC . '/' . implode( '/', $path_parts ) . '/' . $filename;
    
    // 检查文件是否存在并引入
    if ( file_exists( $path ) ) {
        require_once $path;

        // 双向别名自动绑定：
        // 1. 如果调用方使用了 Qiling 命名空间，但文件内部声明的是 Developer_Starter，自动建立别名
        if ( $is_qiling && ! class_exists( $class_name, false ) && ! interface_exists( $class_name, false ) && ! trait_exists( $class_name, false ) ) {
            $legacy_target = 'Developer_Starter\\' . $relative_class;
            if ( class_exists( $legacy_target, false ) || interface_exists( $legacy_target, false ) || trait_exists( $legacy_target, false ) ) {
                class_alias( $legacy_target, $class_name );
            }
        }
        // 2. 如果调用方使用了 Developer_Starter 命名空间，但文件内部声明的是 Qiling，反向建立别名
        elseif ( $is_dev_starter && ! class_exists( $class_name, false ) && ! interface_exists( $class_name, false ) && ! trait_exists( $class_name, false ) ) {
            $modern_target = 'Qiling\\' . $relative_class;
            if ( class_exists( $modern_target, false ) || interface_exists( $modern_target, false ) || trait_exists( $modern_target, false ) ) {
                class_alias( $modern_target, $class_name );
            }
        }
    }
}

// 注册新版全能自动加载函数
spl_autoload_register( 'qiling_autoloader' );

/**
 * 历史加载函数兼容代理
 *
 * @param string $class_name 完整的类名。
 * @return void
 */
if ( ! function_exists( 'developer_starter_autoloader' ) ) {
    function developer_starter_autoloader( $class_name ) {
        qiling_autoloader( $class_name );
    }
}
