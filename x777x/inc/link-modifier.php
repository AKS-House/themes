<?php
/**
 * LINK MODIFIER MODULE
 * Автоматически добавляет nofollow, noopener, noreferrer и target="_blank" ко всем внешним ссылкам.
 * Внутренние ссылки остаются без изменений.
 */

function bigemot_process_external_links($content) {
    if (is_admin() || empty($content)) {
        return $content;
    }

    $site_url = preg_quote(home_url(), '/');

    // Регулярное выражение для поиска всех ссылок
    // (Используем DOMDocument для более надежного парсинга, чем регулярки)
    
    // Если DOMDocument недоступен, используем preg_replace (менее надежно, но более универсально)
    if (!class_exists('DOMDocument')) {
        // Запасной вариант с preg_replace для внешних ссылок
        $pattern = '/<a\s+(.*?)href=[\'"](?!' . $site_url . ')([^"\']+)[\'"](.*?)>(.*?)<\/a>/i';
        
        $replacement = '<a $1href="$2" target="_blank" rel="nofollow noopener noreferrer" $3>$4</a>';
        
        // Предотвращаем дублирование атрибутов, если они уже есть (сложно с preg_replace, но допустимо для начала)
        
        return preg_replace($pattern, $replacement, $content);
    }

    // Предпочтительный и надежный метод: DOMDocument
    $dom = new DOMDocument();

    // Загружаем HTML. '@' подавляет ошибки, связанные с невалидным HTML5
    @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $content); 

    $anchors = $dom->getElementsByTagName('a');
    $home_host = parse_url(home_url(), PHP_URL_HOST);

    foreach ($anchors as $anchor) {
        $href = $anchor->getAttribute('href');
        
        if (empty($href) || strpos($href, '#') === 0 || strpos($href, 'mailto:') === 0) {
            continue; // Пропускаем якоря и mailto
        }

        $link_host = parse_url($href, PHP_URL_HOST);

        // Если хост ссылки отличается от хоста сайта (Внешняя ссылка)
        if ($link_host && strcasecmp($link_host, $home_host) !== 0) {
            // Добавляем nofollow
            $rel = $anchor->getAttribute('rel');
            if (stripos($rel, 'nofollow') === false) {
                $rel = trim($rel . ' nofollow');
            }
            
            // Добавляем безопасность
            if (stripos($rel, 'noopener') === false) {
                $rel = trim($rel . ' noopener');
            }
            if (stripos($rel, 'noreferrer') === false) {
                $rel = trim($rel . ' noreferrer');
            }

            $anchor->setAttribute('rel', $rel);

            // Добавляем target="_blank"
            if ($anchor->getAttribute('target') !== '_blank') {
                $anchor->setAttribute('target', '_blank');
            }
        }
    }

    // Сохраняем и очищаем HTML (убираем добавленный <?xml...> и <body>)
    $content = $dom->saveHTML();
    $content = str_ireplace(['<!DOCTYPE html>', '<html>', '</html>', '<body>', '</body>'], '', $content);
    $content = preg_replace('/<\?xml[^>]*\?>/', '', $content);
    
    return $content;
}

// Буферизация вывода для модификации ссылок по всему сайту
function bigemot_start_output_buffer() {
    ob_start('bigemot_process_external_links');
}
add_action('template_redirect', 'bigemot_start_output_buffer');