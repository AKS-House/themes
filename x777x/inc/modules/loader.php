<?php
/**
 * Module Loader
 * Централизованное подключение изолированных модулей.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$modules_path = get_stylesheet_directory() . '/inc/modules/';

// Список активных модулей
$active_modules = [
    'theme-toggle',     // Переключатель темы
    'header-expansion', // Дополнительные виджеты и HTML элементы
];

foreach ( $active_modules as $module ) {
    $file = $modules_path . $module . '/class-' . $module . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}