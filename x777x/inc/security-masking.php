<?php
/**
 * SECURITY MASKING MODULE
 * Маскировка путей ядра WP (wp-content, wp-includes, wp-json) и стандартных ID.
 * Версия 5.1: Устранено дублирование Canonical ссылки с помощью фильтрации готового HTML.
 */

// ФУНКЦИЯ ДЛЯ УДАЛЕНИЯ ДУБЛИРУЮЩИХСЯ CANONICAL ССЫЛОК (оставляет только первую)
function bigemot_remove_duplicate_canonical($html) {
    // Регулярное выражение для поиска <link rel="canonical" ... >
    $canonical_regex = '/(<link\s+rel=["\']canonical["\'][^>]*>)/i';

    // Находим все Canonical ссылки
    if (preg_match_all($canonical_regex, $html, $matches)) {
        // Если найдено более одного совпадения
        if (count($matches[0]) > 1) {
            $first_canonical = $matches[0][0];
            
            // Находим позицию первого (корректного) тега
            $first_pos = strpos($html, $first_canonical);
            
            if ($first_pos !== false) {
                // Определяем позицию, с которой нужно начать поиск и удаление дубликатов
                $start_search_pos = $first_pos + strlen($first_canonical);
                
                // Извлекаем HTML после первого тега
                $after_first = substr($html, $start_search_pos);
                
                // Удаляем все остальные вхождения canonical тега из этой части
                $after_first_cleaned = preg_replace($canonical_regex, '', $after_first);
                
                // Собираем HTML обратно: (HTML до первого тега) + (Первый тег) + (Очищенный HTML после)
                $html = substr($html, 0, $start_search_pos) . $after_first_cleaned;
            }
        }
    }
    
    return $html;
}


// ФУНКЦИЯ ДЛЯ УДАЛЕНИЯ ДУБЛИРУЮЩИХСЯ META-ТЕГОВ И ОЧИСТКИ КОДА
function bigemot_strip_meta_tags($html) {
    // 1. Список property/name атрибутов мета-тегов, которые нужно удалить.
    $meta_properties_to_remove = [
        // Дубликаты OG
        'og:locale', 'og:type', 'og:title', 'og:description', 'og:url', 
        'og:site_name', 'og:image',
        // Дубликаты Twitter
        'twitter:card', 'twitter:title', 'twitter:description', 
        'twitter:site', 'twitter:image',
        // Другие теги
        'og:image:width', 'og:image:height', 'article:published_time',
        'article:modified_time', 'og:updated_time', 'article:tag', 
        'article:section',
    ];

    foreach ($meta_properties_to_remove as $prop) {
        $safe_prop = preg_quote($prop, '/');
        
        // Регулярное выражение для мета-тегов с property="..."
        $regex_prop = '/<meta\s+[^>]*property=["\']' . $safe_prop . '["\'][^>]*>/i';
        $html = preg_replace($regex_prop, '', $html);

        // Регулярное выражение для мета-тегов с name="..."
        $regex_name = '/<meta\s+[^>]*name=["\']' . $safe_prop . '["\'][^>]*>/i';
        $html = preg_replace($regex_name, '', $html);
    }
    
    // 2. Очистка лишних пустых строк, оставшихся после удаления мета-тегов
    // Заменяем 3 и более новых строк (включая CR/LF) на 2 новые строки.
    $html = preg_replace('/(\R){3,}/', "\n\n", $html); 
    
    return $html;
}


// ФУНКЦИЯ ФИНАЛЬНОЙ МАСКИРОВКИ
// Эта функция вызывается из asset-loader.php
function bigemot_final_masking_callback( $html ) {
    
    // !!! ШАГ 1: УДАЛЕНИЕ ДУБЛИРУЮЩИХСЯ CANONICAL ССЫЛОК
    $html = bigemot_remove_duplicate_canonical($html);

    // ШАГ 2: Удаление дублирующихся мета-тегов и очистка кода
    $html = bigemot_strip_meta_tags($html);
    
    // --- ШАГ 3: МАСКИРОВКА ПУТЕЙ И ID ---
    $replacements = array(
        // 1. Замена путей ядра WP
        'wp-content/' => 'smartx/', 
        'wp-includes/' => 'police/',
        // 2. Маскировка REST API
        'wp-json/' => 'x-api-json/',
        // 3. Маскировка стандартных ID
        'wp-block-library-css' => 'bglib-css',
        'wp-dom-ready-js' => 'bg-dom-js',
        'wp-hooks-js' => 'bg-hooks-js',
        'wp-i11n-js' => 'bg-i11n-js', 
        'wp-a11y-js' => 'bg-a11y-js',
        'heartbeat-js' => 'bg-hb-js', 
        'jquery-core-js' => 'bg-jq-js',
    );

    $html = str_replace(array_keys($replacements), array_values($replacements), $html);

    // Возвращаем очищенный HTML
    return $html;
}

/**
 * Очистка классов в теге <body>, чтобы скрыть следы WP.
 */
add_filter( 'body_class', function ( $classes ) {
    $forbidden_classes = array(
        'wp-embed-responsive',
        'wp-theme-blocksy',
        'wp-child-theme-x777x',
        'wp-custom-logo',
    );

    $classes = array_diff( $classes, $forbidden_classes );

    return $classes;
}, 999 );