<?php
/**
 * OPTIMIZATION ASSETS MODULE
 * Оптимизация LCP, шрифтов и отключение Google Fonts.
 */

// --- 🖼️ ОПТИМИЗАЦИЯ LCP ИЗОБРАЖЕНИЙ ---

// 1. Предзагрузка LCP изображения в <head>
add_action('wp_head', function() {
    if (is_admin()) {
        return;
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return;
    }
    
    $featured_id = false;

    // Поддержка WooCommerce, если это страница продукта
    if (function_exists('is_product') && is_product()) {
        $product = wc_get_product($post_id);
        if ($product) {
            $featured_id = $product->get_image_id();
        }
    }

    // Для обычных записей (post)
    if (!$featured_id && is_single()) {
        $featured_id = get_post_thumbnail_id($post_id);
    }

    if ($featured_id) {
        // Получаем URL изображения
        $src = wp_get_attachment_image_src($featured_id, 'full');
        if ($src) {
            // Использование fetchpriority="high" для LCP
            echo '<link rel="preload" as="image" href="' . esc_url($src[0]) . '" fetchpriority="high">' . "\n";
        }
    }
});

// 2. Добавление атрибутов fetchpriority="high", loading="eager" к LCP-изображению
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    if (is_admin()) {
        return $attr;
    }

    $post_id = get_the_ID();
    if (!$post_id) {
        return $attr;
    }

    // Используем статические переменные для кэширования ID LCP-изображения на запрос
    static $current_post_id = null;
    static $featured_id = null;
    
    if ($current_post_id !== $post_id) {
        $current_post_id = $post_id;
        $featured_id = false;

        if (function_exists('is_product') && is_product()) {
            $product = wc_get_product($post_id);
            if ($product) {
                $featured_id = $product->get_image_id();
            }
        }

        if (!$featured_id && is_single()) {
            $featured_id = get_post_thumbnail_id($post_id);
        }
    }
    
    if ($featured_id && $attachment->ID === $featured_id) {
        $attr['fetchpriority'] = 'high';
        $attr['loading'] = 'eager';
        $attr['decoding'] = 'async';
    }

    return $attr;
}, 10, 3);