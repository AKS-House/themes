<?php
/**
 * SECURITY MASKING MODULE
 * Маскировка путей ядра WP (wp-content, wp-includes, wp-json) и стандартных ID.
 * Версия 5.1: Устранено дублирование Canonical ссылки с помощью фильтрации готового HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// !!! ВАЖНОЕ ИСПРАВЛЕНИЕ: ЗАПУСК БУФЕРИЗАЦИИ !!!
// Так как мы убрали запуск из asset-loader.php, он должен быть здесь.
add_action( 'template_redirect', function() {
    // Запускаем буфер только на фронтенде и если функция маскировки существует
    if ( ! is_admin() && function_exists('bigemot_final_masking_callback') ) {
        ob_start( 'bigemot_final_masking_callback' );
    }
}, 1 );

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
                
                // Собираем HTML обратно: часть до первого тега + первый тег + очищенная часть
                $html = substr($html, 0, $start_search_pos) . $after_first_cleaned;
            }
        }
    }
    
    return $html;
}

// ФУНКЦИЯ ОЧИСТКИ META-ТЕГОВ
function bigemot_strip_meta_tags( $html ) {
    // Удаляем meta generator (версия WP и т.д.)
    $html = preg_replace( '/<meta name="generator" content=".*?" \/>/i', '', $html );
    
    // Удаляем wlwmanifest
    $html = preg_replace( '/<link rel="wlwmanifest" type="application\/wlwmanifest\+xml" href=".*?" \/>/i', '', $html );
    
    // Удаляем EditURI (RSD)
    $html = preg_replace( '/<link rel="EditURI" type="application\/rsd\+xml" title="RSD" href=".*?" \/>/i', '', $html );
    
    // Удаляем shortlink
    $html = preg_replace( '/<link rel="shortlink" href=".*?" \/>/i', '', $html );

    // Удаляем dns-prefetch s.w.org (стандартный WP)
    $html = preg_replace( '/<link rel="dns-prefetch" href="\/\/s\.w\.org" \/>/i', '', $html );
    
    // Очищаем множественные переносы строк для чистоты кода (опционально)
    $html = preg_replace('/[\r\n]{3,}/', "\n\n", $html); 
    
    return $html;
}


// ФУНКЦИЯ ФИНАЛЬНОЙ МАСКИРОВКИ
// Эта функция теперь вызывается через ob_start() в начале этого файла
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
        
        // 3. Маскировка стандартных ID и классов WP
        'wp-block-library-css' => 'bglib-css',
        'wp-dom-ready-js'      => 'bg-dom-js',
        'wp-hooks-js'          => 'bg-hooks-js',
        'wp-i11n-js'           => 'bg-i11n-js', 
        'wp-a11y-js'           => 'bg-a11y-js',
        'heartbeat-js'         => 'hb-js',
        'wp-auth-check-js'     => 'auth-chk-js',
        'wp-playlist-js'       => 'pl-list-js',
        'wp-embed-js'          => 'em-js',
        'wp-emoji-release.min.js' => 'emj.js',
    );

    // Выполняем замену всех ключей на значения в массиве
    $html = str_replace( array_keys( $replacements ), array_values( $replacements ), $html );

    return $html;
}