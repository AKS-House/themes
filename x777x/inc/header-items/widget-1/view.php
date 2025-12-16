<?php
// themes/x777x/inc/header-items/widget-1/view.php

$sidebar_id = 'x777x-header-widget-1';

// Если сайдбар пуст и мы не в режиме настройки - ничего не выводим
if ( ! is_active_sidebar( $sidebar_id ) && ! is_customize_preview() ) {
	return;
}

// $attr - это массив, который Blocksy передает в этот файл.
// Если вдруг он не определен (защита), создаем пустой массив.
if ( ! isset( $attr ) ) {
	$attr = [];
}

// 1. Добавляем наши базовые классы
$my_classes = 'ct-header-element x777x-header-widget ';

// 2. Добавляем классы видимости (Desktop/Tablet/Mobile)
$visibility = blocksy_default_akg( 'visibility', $atts, [
	'desktop' => true,
	'tablet' => true,
	'mobile' => true,
]);
$my_classes .= blocksy_visibility_classes( $visibility );

// Сливаем с классами от Blocksy
if ( isset( $attr['class'] ) ) {
	$attr['class'] .= ' ' . $my_classes;
} else {
	$attr['class'] = $my_classes;
}

// 3. Обработка выравнивания (Horizontal Alignment)
// Blocksy использует атрибут data-alignment для управления Flexbox-выравниванием
$alignment = blocksy_default_akg( 'horizontal_alignment', $atts, 'left' );
$attr['data-alignment'] = $alignment;

// 4. Обработка Максимальной ширины (Max Width)
$max_width = blocksy_default_akg( 'max_width', $atts, 'CT_CSS_SKIP_RULE' );
if ( $max_width !== 'CT_CSS_SKIP_RULE' ) {
	// Если значение адаптивное (массив) или простое число
	$width_val = ( is_array( $max_width ) && isset( $max_width['desktop'] ) ) ? $max_width['desktop'] : $max_width;
	
	// Добавляем инлайн-стиль
	$style_string = 'max-width: ' . intval( $width_val ) . 'px; width: 100%;';
	
	if ( isset( $attr['style'] ) ) {
		$attr['style'] .= ' ' . $style_string;
	} else {
		$attr['style'] = $style_string;
	}
}

?>

<div <?php echo blocksy_attr_to_html( $attr ); ?>>
	<?php 
	if ( is_active_sidebar( $sidebar_id ) ) {
		// Обертка ct-widget-area помогает наследовать стили виджетов темы
		echo '<div class="ct-widget-area">';
		dynamic_sidebar( $sidebar_id );
		echo '</div>';
	} elseif ( is_customize_preview() ) {
		// Заглушка для режима конструктора
		echo '<div class="ct-placeholder" style="border: 1px dashed rgba(0,0,0,0.3); padding: 10px; min-width: 100px; text-align: center; font-size: 11px;">';
		echo '<strong>Widget 1</strong><br>Empty';
		echo '</div>';
	}
	?>
</div>