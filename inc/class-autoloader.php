<?php
/**
 * 主题类自动加载器
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Developer_Starter 命名空间类的自动加载函数
 *
 * @param string $class_name 完整的类名。
 * @return void
 */
function developer_starter_autoloader( $class_name ) {
    // 检查类是否属于当前主题命名空间
    if ( strpos( $class_name, 'Developer_Starter\\' ) !== 0 ) {
        return;
    }

    // 移除命名空间前缀
    $relative_class = substr( $class_name, 18 ); // Length of 'Developer_Starter\'

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
    
    // 拼接完整文件路径
    // DEVELOPER_STARTER_INC 在 functions.php 中定义
    $path = DEVELOPER_STARTER_INC . '/' . implode( '/', $path_parts ) . '/' . $filename;
    
    // 检查文件是否存在并引入
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

// 注册自动加载函数
spl_autoload_register( 'developer_starter_autoloader' );
