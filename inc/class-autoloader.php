<?php
/**
 * Theme Autoloader
 *
 * @package Developer_Starter
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Autoload function for Developer_Starter classes
 *
 * @param string $class_name The fully-qualified class name.
 * @return void
 */
function developer_starter_autoloader( $class_name ) {
    // Check if class is in our namespace
    if ( strpos( $class_name, 'Developer_Starter\\' ) !== 0 ) {
        return;
    }

    // Remove the namespace prefix
    $relative_class = substr( $class_name, 18 ); // Length of 'Developer_Starter\'

    // Split into parts
    $parts = explode( '\\', $relative_class );
    
    // Get the class name (last part)
    $final_class_name = array_pop( $parts );
    
    // Convert directory parts to lowercase
    $path_parts = array_map( 'strtolower', $parts );
    
    // Build the filename from the class name:
    // 1. Convert to lowercase
    // 2. Replace underscores with hyphens
    // 3. Prepend 'class-'
    $filename = 'class-' . str_replace( '_', '-', strtolower( $final_class_name ) ) . '.php';
    
    // Build full path
    // DEVELOPER_STARTER_INC is defined in functions.php
    $path = DEVELOPER_STARTER_INC . '/' . implode( '/', $path_parts ) . '/' . $filename;
    
    // Check if file exists and require it
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

// Register the autoloader
spl_autoload_register( 'developer_starter_autoloader' );
