<?php
/**
 * ASSET LOADER MODULE
 * Условная (точечная) загрузка CSS/JS плагинов ProfileGrid и MyCred через буферизацию.
 * ТЕПЕРЬ ВКЛЮЧАЕТ ФИНАЛЬНУЮ ОБРАБОТКУ (МАСКИРОВКУ).
 */

// Шаг 1: Запускаем буферизацию
add_action( 'template_redirect', 'bigemot_buffer_start', 1 );
function bigemot_buffer_start() {
    if ( !is_admin() ) {
        // Указываем функцию-фильтр
        ob_start( 'bigemot_asset_buffer_callback' );
    }
}

// Шаг 2: Эта функция "фильтрует" HTML (Удаляет активы и вызывает маскировку)
function bigemot_asset_buffer_callback( $html ) {
    
    // ... (Логика ProfileGrid и MyCred остается без изменений) ...

    $load_profilegrid = false;
    $profilegrid_pages = array( 28, 25, 30 ); // ID страниц: login, registration, forgot-password
    
    if ( is_page( $profilegrid_pages ) || is_user_logged_in() ) {
        $load_profilegrid = true;
    }

    $load_mycred = is_user_logged_in(); 

    // Если ProfileGrid НЕ нужен, удаляем его ассеты
    if ( ! $load_profilegrid ) {
        $handles_to_remove = array(
            'pm-font-awesome-css', 'merged-css-footer-css',
            'pg-profile-menu.js-js', 'profilegrid-user-profiles-groups-and-communities-js',
            'modernizr-custom.min.js-js', 'profile-magic-footer.js-js',
            'pg-password-checker.js-js', 'profile-magic-admin-power.js-js',
            'jquery-ui-core-js', 'jquery-ui-datepicker-js', 'jquery-ui-accordion-js',
            'jquery-ui-mouse-js', 'jquery-ui-resizable-js', 'jquery-ui-draggable-js',
            'jquery-ui-controlgroup-js', 'jquery-ui-checkboxradio-js',
            'jquery-ui-button-js', 'jquery-ui-dialog-js', 'jquery-ui-menu-js',
            'jquery-ui-autocomplete-js'
        );
        $html = bigemot_remove_assets_by_id( $html, $handles_to_remove );
    }

    // Если myCred НЕ нужен, удаляем его ассеты
    if ( ! $load_mycred ) {
        $handles_to_remove = array(
            'mycred-front-css', 'mycred-social-share-icons-css', 'mycred-social-share-style-css'
        );
        $html = bigemot_remove_assets_by_id( $html, $handles_to_remove );
    }

    // !!! НОВОЕ: ВЫЗЫВАЕМ ФИНАЛЬНУЮ МАСКИРОВКУ
    if ( function_exists('bigemot_final_masking_callback') ) {
        $html = bigemot_final_masking_callback( $html );
    }

    return $html;
}

/**
 * Вспомогательная функция для очистки HTML по ID (используется и в security-masking.php)
 */
if ( ! function_exists( 'bigemot_remove_assets_by_id' ) ) {
    function bigemot_remove_assets_by_id( $html, $handles ) {
        foreach ( $handles as $handle ) {
            $safe_handle = preg_quote($handle, '/');
            
            // Регулярное выражение для <script>
            $script_regex = '/<script[^>]+id=[\'"]' . $safe_handle . '[\'"][^>]*>.*?<\/script>/is';
            
            // Регулярное выражение для <link> (CSS)
            $link_regex = '/<link[^>]+id=[\'"]' . $safe_handle . '[\'"][^>]*>/i';

            $html = preg_replace( $script_regex, '', $html );
            $html = preg_replace( $link_regex, '', $html );
        }
        return $html;
    }
}