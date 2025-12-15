<?php
/**
 * SITEMAP GENERATOR MODULE
 * Генерирует sitemap.xml только для постов и главной страницы.
 */

// ПУТЬ К КЭШИРОВАННОМУ ФАЙЛУ SITEMAP
define('BIGEMOT_SITEMAP_PATH', ABSPATH . 'sitemap.xml');

// 1. АВТОМАТИЧЕСКАЯ ГЕНЕРАЦИЯ SITEMAP
function bigemot_generate_sitemap() {
    $sitemap_content = '';

    // Заголовок XML
    $sitemap_content .= '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $sitemap_content .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // A. ГЛАВНАЯ СТРАНИЦА
    $sitemap_content .= "\t<url>\n";
    $sitemap_content .= "\t\t<loc>" . esc_url(home_url('/')) . "</loc>\n";
    $sitemap_content .= "\t\t<lastmod>" . date('c', time()) . "</lastmod>\n"; // Всегда свежая дата для главной
    $sitemap_content .= "\t\t<changefreq>daily</changefreq>\n";
    $sitemap_content .= "\t\t<priority>1.0</priority>\n";
    $sitemap_content .= "\t</url>\n";

    // B. ВСЕ ОПУБЛИКОВАННЫЕ ПОСТЫ
    $posts_args = array(
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'modified', // Сортировка по дате изменения
        'order'          => 'DESC',
        'no_found_rows'  => true
    );

    $posts = new WP_Query($posts_args);

    if ($posts->have_posts()) {
        while ($posts->have_posts()) {
            $posts->the_post();
            $post_url = get_permalink();
            $post_modified = get_the_modified_date('c'); // Дата последнего изменения
            
            // Пропускаем посты, которые должны быть noindex
            if (get_post_meta(get_the_ID(), '_bigemot_noindex', true) === '1') {
                continue;
            }

            $sitemap_content .= "\t<url>\n";
            $sitemap_content .= "\t\t<loc>" . esc_url($post_url) . "</loc>\n";
            $sitemap_content .= "\t\t<lastmod>" . $post_modified . "</lastmod>\n";
            $sitemap_content .= "\t\t<changefreq>weekly</changefreq>\n";
            $sitemap_content .= "\t\t<priority>0.8</priority>\n";
            $sitemap_content .= "\t</url>\n";
        }
        wp_reset_postdata();
    }

    $sitemap_content .= '</urlset>';
    
    // Сохраняем в файл sitemap.xml в корне сайта для максимальной производительности
    file_put_contents(BIGEMOT_SITEMAP_PATH, $sitemap_content);
}

// Запускаем генерацию карты сайта при публикации или изменении поста/страницы
add_action('save_post', 'bigemot_generate_sitemap');
add_action('wp_loaded', 'bigemot_generate_sitemap'); // Также генерируем при загрузке WP (для первого запуска)

// 2. ОБРАБОТЧИК ЗАПРОСА SITEMAP.XML
function bigemot_sitemap_rewrite_rules() {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?bigemot_sitemap=1', 'top');
}
add_action('init', 'bigemot_sitemap_rewrite_rules');

function bigemot_sitemap_query_vars($vars) {
    $vars[] = 'bigemot_sitemap';
    return $vars;
}
add_filter('query_vars', 'bigemot_sitemap_query_vars');

function bigemot_sitemap_template() {
    if (get_query_var('bigemot_sitemap')) {
        if (!file_exists(BIGEMOT_SITEMAP_PATH)) {
            // Если файл не существует, генерируем его немедленно
            bigemot_generate_sitemap(); 
        }

        header('Content-Type: application/xml; charset=' . get_bloginfo('charset'), true);
        readfile(BIGEMOT_SITEMAP_PATH);
        exit;
    }
}
add_action('template_redirect', 'bigemot_sitemap_template');

// После добавления этого кода, нужно перейти в Настройки -> Постоянные ссылки и просто сохранить их, чтобы обновить правила перезаписи.