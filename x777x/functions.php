<?php

if (! defined('ABSPATH')) {
	die( 'Direct access forbidden.' );
}

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
});

// Проверяем наличие папки inc и подключаем модули
$modules_dir = get_stylesheet_directory() . '/inc/';

if (is_dir($modules_dir)) {
    // Оптимизация шрифтов и LCP (самые ранние хуки)
    require_once $modules_dir . 'optimization-assets.php';

    // Условная загрузка ассетов (ProfileGrid, MyCred)
    require_once $modules_dir . 'asset-loader.php';

    // Маскировка ядра WP
    require_once $modules_dir . 'security-masking.php';

    // Персональные папки пользователей (ProfileGrid/uploads)
    require_once $modules_dir . 'user-uploads.php';

    // Модуль базовых мета-тегов и Schema
    require_once $modules_dir . 'seo-metas.php';

    // Модуль генерации карты сайта (только Посты и Главная)
    require_once $modules_dir . 'sitemap-generator.php';

    // Модуль модификации ссылок (nofollow, target="_blank" для внешних)
    require_once $modules_dir . 'link-modifier.php';

    // Подключение модульной системы (новые фичи)
    require_once get_stylesheet_directory() . '/inc/modules/loader.php';
}