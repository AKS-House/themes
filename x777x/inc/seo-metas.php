<?php
/**
 * SEO METAS MODULE
 * Генерирует Title, Description, Canonical, Open Graph и Twitter Cards.
 */

// УДАЛЯЕМ ЛИШНИЕ ССЫЛКИ И ЗАГОЛОВКИ (для чистоты)
function bigemot_clean_header_seo() {
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'wlwmanifest_link'); 
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wp_shortlink_wp_head');
    remove_action('wp_head', 'rest_output_link_wp_head');
    remove_action('wp_head', 'feed_links_extra', 3);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
}
add_action('after_setup_theme', 'bigemot_clean_header_seo');


// ГЕНЕРАЦИЯ ОСНОВНЫХ МЕТА-ТЕГОВ И CANONICAL
function bigemot_seo_metas() {
    global $post;

    if (is_singular()) {
        $post_id = $post->ID;
    } elseif (is_front_page() || is_home()) {
        $post_id = get_option('page_on_front'); // Для статической главной страницы
    } else {
        $post_id = 0;
    }
    
    // 1. Canonical URL
    $canonical_url = home_url(add_query_arg(null, null));
    echo '<link rel="canonical" href="' . esc_url($canonical_url) . '">' . "\n";

    // 2. Description
    $description = '';
    if ($post_id) {
        $description = get_post_meta($post_id, '_bigemot_seo_description', true);
    }
    
    if (empty($description) && is_singular()) {
        $description = wp_trim_words($post->post_content, 25, '...');
    } elseif (empty($description) && (is_front_page() || is_home())) {
        $description = get_bloginfo('description');
    }

    if (!empty($description)) {
        echo '<meta name="description" content="' . esc_attr(strip_tags($description)) . '">' . "\n";
    }

    // 3. Title (Перезаписываем стандартный WP Title)
    if (is_singular()) {
        $title = get_the_title($post_id) . ' | ' . get_bloginfo('name');
    } elseif (is_front_page() || is_home()) {
        $title = get_bloginfo('name') . ' | ' . get_bloginfo('description');
    } else {
        $title = wp_get_document_title(); // Позволяем WP генерировать для архивов/других страниц
    }
    add_filter('pre_get_document_title', function() use ($title) { return $title; });
}
add_action('wp_head', 'bigemot_seo_metas', 1);

// ГЕНЕРАЦИЯ OPEN GRAPH И TWITTER CARDS
function bigemot_social_metas() {
    global $post;
    if (!is_singular()) return;

    $url = get_permalink($post->ID);
    $title = get_the_title($post->ID);
    $description = get_post_meta($post->ID, '_bigemot_seo_description', true);
    if (empty($description)) {
        $description = wp_trim_words($post->post_content, 25, '...');
    }
    
    // Получение изображения (Вам нужно убедиться, что эта функция возвращает WEBP-ссылку из Cloudflare R2)
    $image = get_the_post_thumbnail_url($post->ID, 'full'); 
    
    // --- OPEN GRAPH ---
    echo '<meta property="og:locale" content="' . esc_attr(get_locale()) . '" />' . "\n";
    echo '<meta property="og:type" content="article" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '" />' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    if ($image) {
        echo '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
    }

    // --- TWITTER CARD ---
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta name="twitter:site" content="@Bigemot" />' . "\n"; 
    if ($image) {
        echo '<meta name="twitter:image" content="' . esc_url($image) . '" />' . "\n";
    }

    // --- JSON-LD (Schema Markup) ---
    bigemot_generate_article_schema($post, $description, $image);
}
add_action('wp_head', 'bigemot_social_metas', 5);

// ФУНКЦИЯ ГЕНЕРАЦИИ JSON-LD SCHEMA (для E-E-A-T)
function bigemot_generate_article_schema($post, $description, $image) {
    if (empty($post) || $post->post_type !== 'post') return;

    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BlogPosting',
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => get_permalink($post->ID),
        ],
        'headline' => get_the_title($post->ID),
        'description' => $description,
        'datePublished' => get_the_date('c', $post->ID),
        'dateModified' => get_the_modified_date('c', $post->ID),
        'author' => [
            '@type' => 'Person',
            'name' => get_the_author_meta('display_name', $post->post_author),
            'url' => get_author_posts_url($post->post_author),
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => get_bloginfo('name'),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => esc_url(get_site_icon_url()), // Используем иконку сайта как лого
            ],
        ],
    ];

    if ($image) {
        $schema['image'] = [
            '@type' => 'ImageObject',
            'url' => $image,
            // Желательно добавить 'width' и 'height' здесь
        ];
    }

    echo '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}


// МЕХАНИЗМ СОХРАНЕНИЯ ОПИСАНИЯ В КАСТОМНОМ ПОЛЕ (как было, но внутри модуля)
function bigemot_add_seo_meta_box() {
    add_meta_box(
        'bigemot_seo_meta_box',
        'SEO Настройки (Bigemot)',
        'bigemot_display_seo_meta_box',
        'post', 
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'bigemot_add_seo_meta_box');

function bigemot_display_seo_meta_box($post) {
    wp_nonce_field('bigemot_seo_nonce', 'bigemot_seo_nonce_field');
    $description = get_post_meta($post->ID, '_bigemot_seo_description', true);
    ?>
    <p>
        <label for="bigemot_seo_description">Meta Description (150-160 символов):</label><br>
        <textarea id="bigemot_seo_description" name="bigemot_seo_description" style="width:100%;" rows="3"><?php echo esc_textarea($description); ?></textarea>
    </p>
    <?php
}

function bigemot_save_seo_meta_box($post_id) {
    if (!isset($_POST['bigemot_seo_nonce_field']) || !wp_verify_nonce($_POST['bigemot_seo_nonce_field'], 'bigemot_seo_nonce')) {
        return $post_id;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    if (isset($_POST['bigemot_seo_description'])) {
        $description = sanitize_textarea_field($_POST['bigemot_seo_description']);
        update_post_meta($post_id, '_bigemot_seo_description', $description);
    }
}
add_action('save_post', 'bigemot_save_seo_meta_box');