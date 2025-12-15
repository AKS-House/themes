<?php
/**
 * Module: Header & Footer Expansion
 * Description: Добавляет кастомные зоны виджетов и дополнительные HTML элементы в конструктор Blocksy.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class X777X_Header_Expansion {

	public function __construct() {
		// 1. Регистрация зон виджетов (Сайдбаров)
		add_action( 'widgets_init', array( $this, 'register_custom_sidebars' ) );

		// 2. Шорткод для вывода виджетов
		add_shortcode( 'x777x_widget', array( $this, 'render_widget_area' ) );

		// 3. Добавление элементов в конструктор (Header & Footer)
		add_filter( 'blocksy:header:builder:elements', array( $this, 'add_builder_elements' ), 20 );
		add_filter( 'blocksy:footer:builder:elements', array( $this, 'add_builder_elements' ), 20 );
	}

	/**
	 * 1. Создаем 10 дополнительных зон для виджетов
	 * (Можно увеличить число $count, если нужно больше)
	 */
	public function register_custom_sidebars() {
		$count = 10; 

		for ( $i = 1; $i <= $count; $i++ ) {
			register_sidebar( array(
				'name'          => 'Кастомный Виджет ' . $i,
				'id'            => 'x777x-custom-widget-' . $i,
				'description'   => 'Зона для вставки в Header или Footer через конструктор.',
				'before_widget' => '<div id="%1$s" class="widget %2$s x777x-widget-content">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			) );
		}
	}

	/**
	 * 2. Логика шорткода [x777x_widget id="1"]
	 */
	public function render_widget_area( $atts ) {
		$atts = shortcode_atts( array(
			'id' => '1',
		), $atts );

		$sidebar_id = 'x777x-custom-widget-' . $atts['id'];

		ob_start();
		if ( is_active_sidebar( $sidebar_id ) ) {
			dynamic_sidebar( $sidebar_id );
		} else {
			// Видно только админам для отладки
			if ( current_user_can( 'administrator' ) ) {
				echo '<div style="opacity: 0.5; border: 1px dashed currentColor; padding: 5px; font-size: 10px;">Виджет ' . esc_html( $atts['id'] ) . ' пуст</div>';
			}
		}
		return ob_get_clean();
	}

	/**
	 * 3. Добавляем клоны HTML-элемента в Конструктор Blocksy
	 */
	public function add_builder_elements( $elements ) {
		// Количество дополнительных элементов в конструкторе
		$count = 5; 

		// Берем настройки стандартного HTML элемента Blocksy как базу
		// В бесплатной версии он обычно называется 'text' или 'html'
		$base_config = isset( $elements['text'] ) ? $elements['text'] : [];

		if ( empty( $base_config ) ) {
			// Если вдруг ключ изменился, пробуем html
			$base_config = isset( $elements['html'] ) ? $elements['html'] : [];
		}

		if ( empty( $base_config ) ) {
			return $elements; // Если не нашли базу, ничего не делаем, чтобы не сломать
		}

		for ( $i = 1; $i <= $count; $i++ ) {
			// Создаем копию конфигурации
			$new_element = $base_config;
			
			// Меняем название, чтобы вы видели их в списке
			$new_element['title'] = 'HTML / Виджет ' . $i;
			$new_element['description'] = 'Доп. блок. Вставьте шорткод [x777x_widget id="' . $i . '"]';
			
			// Добавляем уникальный элемент в массив
			// Ключ должен быть уникальным, например text_custom_1
			$elements['text_x777x_' . $i] = $new_element;
		}

		return $elements;
	}
}

new X777X_Header_Expansion();