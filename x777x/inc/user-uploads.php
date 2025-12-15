<?php
/**
 * USER UPLOADS MODULE
 * Управление личными папками пользователей (для ProfileGrid)
 */

/**
 * ЗАДАЧА 1: Перенаправление загрузок в личную папку пользователя.
 */
function user_specific_upload_dir( $dirs ) {
    $user_id = get_current_user_id();
    if ( $user_id > 0 ) {
        $custom_dir = '/user-files/' . $user_id;
        $dirs['subdir'] = $custom_dir;
        $dirs['path']   = $dirs['basedir'] . $custom_dir;
        $dirs['url']    = $dirs['baseurl'] . $custom_dir;
    }
    return $dirs;
}
add_filter( 'upload_dir', 'user_specific_upload_dir' );

/**
 * ЗАДАЧА 2: Удаление личной папки пользователя при удалении аккаунта.
 */
function delete_user_upload_folder( $user_id ) {
    $upload_dir = wp_upload_dir();
    $user_folder_path = $upload_dir['basedir'] . '/user-files/' . $user_id;

    if ( is_dir( $user_folder_path ) ) {
        pm_delete_files_recursive( $user_folder_path );
    }
}
add_action( 'delete_user', 'delete_user_upload_folder' );

/**
 * ЗАДАЧА 3: Удаление старого файла при замене аватара/обложки ProfileGrid.
 */
function pm_delete_replaced_file_v2( $check, $user_id, $meta_key, $new_value, $old_value_from_hook ) {
    
    $meta_keys_to_watch = array(
        'pm_user_avatar',       
        'pm_cover_image'        
    );

    if ( ! in_array( $meta_key, $meta_keys_to_watch ) ) {
        return $check; 
    }

    $real_old_value = get_user_meta( $user_id, $meta_key, true );

    if ( !empty($real_old_value) && is_numeric($real_old_value) && is_numeric($new_value) && $real_old_value != $new_value ) {
        // Удаляем старое вложение (и файл) из медиатеки.
        wp_delete_attachment( $real_old_value, true );
    }

    return $check; 
}
add_filter( 'update_user_metadata', 'pm_delete_replaced_file_v2', 10, 5 );

/**
 * Вспомогательная функция для рекурсивного удаления папки.
 */
function pm_delete_files_recursive( $dir ) {
    if ( !is_dir( $dir ) || empty( $dir ) || strlen( $dir ) < 10 ) {
        return;
    }
    
    $files = array_diff( scandir( $dir ), array( '.', '..' ) );

    foreach ( $files as $file ) {
        $path = "$dir/$file";
        if ( is_dir( $path ) ) {
            pm_delete_files_recursive( $path );
        } else {
            unlink( $path );
        }
    }
    // Удаляем саму папку
    @rmdir( $dir );
}