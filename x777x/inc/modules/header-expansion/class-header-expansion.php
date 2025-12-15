<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class X777X_Header_Expansion {

	// Количество добавляемых виджетов
	private $widgets_count = 5;

	public function __construct() {
		// 1. Регистрация зон виджетов (сайдбаров) в WP
		add_action( 'widgets_init', array( $this, 'register_custom_sidebars' ) );

		// 2. Регистрация элементов в Конструкторе Хедера
		add_filter( 'blocksy:header:builder:elements', array( $this, 'register_builder_elements' ) );

		// 3. Регистрация опций для этих элементов (при клике на шестеренку)
		add_filter( 'blocksy:options:builder:elements', array( $this, 'register_builder_options' ) );

		// 4. Отрисовка элемента в Хедере (перехват рендеринга)
		add_action( 'blocksy:header:render:item', array( $this, 'render_builder_item' ), 10, 2 );
	}

	/**
	 * 1. Создаем зоны виджетов в Админке -> Внешний вид -> Виджеты
	 */
	public function register_custom_sidebars() {
		for ( $i = 1; $i <= $this->widgets_count; $i++ ) {
			register_sidebar( array(
				'name'          => 'Header Widget ' . $i,
				'id'            => 'x777x-header-widget-' . $i,
				'description'   => 'Зона для элемента "Виджет ' . $i . '" в конструкторе шапки.',
				'before_widget' => '<div id="%1$s" class="widget x777x-header-widget-content %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			) );
		}
	}

	/**
	 * 2. Добавляем иконки ("кубики") в палитру Конструктора Хедера
	 */
	public function register_builder_elements( $elements ) {
		for ( $i = 1; $i <= $this->widgets_count; $i++ ) {
			$id = 'x777x_widget_' . $i;
			
			$elements[ $id ] = array(
				'title' => __( 'Widget', 'blocksy' ) . ' ' . $i,
				'description' => __( 'Custom widget area', 'blocksy' ),
				// Важно: группа определяет, где в палитре появится элемент.
				'group' => 'elements', 
				// Конфигурация устройств, где элемент доступен
				'config' => array(
					'devices' => ['desktop', 'tablet', 'mobile'],
				),
				// Указываем, что клонирование отключено, это уникальные элементы (как Widget Area 1 в футере)
				'clone' => false, 
				// Иконка (можно использовать svg код)
				'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M19,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5C21,3.9,20.1,3,19,3z M19,19H5V5h14V19z"/><rect x="7" y="7" width="10" height="2"/><rect x="7" y="11" width="10" height="2"/><rect x="7" y="15" width="7" height="2"/></svg>',
			);
		}
		return $elements;
	}

	/**
	 * 3. Настройки элемента (всплывающее окно при клике в конструкторе)
	 */
	public function register_builder_options( $options ) {
		for ( $i = 1; $i <= $this->widgets_count; $i++ ) {
			$id = 'x777x_widget_' . $i;

			$options[ $id ] = array(
				'title' => __( 'Widget', 'blocksy' ) . ' ' . $i,
				'options' => array(
					// Вкладка Общие
					'general' => array(
						'type' => 'tab',
						'title' => __( 'General', 'blocksy' ),
						'options' => array(
							'info_text' => array(
								'type' => 'ct-message',
								'text' => sprintf( 
									'Контент управляется в <a href="%s" target="_blank">Виджеты -> Header Widget %d</a>.', 
									admin_url('widgets.php'), 
									$i 
								),
							),
							
							blocksy_rand_md5() => array( 'type' => 'ct-divider' ),

							// Настройка выравнивания (полезно для виджетов в хедере)
							'horizontal_alignment' => array(
								'type' => 'ct-radio',
								'label' => __( 'Horizontal Alignment', 'blocksy' ),
								'view' => 'text',
								'design' => 'block',
								'responsive' => true,
								'attr' => array( 'data-type' => 'alignment' ),
								'value' => 'CT_CSS_SKIP_RULE',
								'choices' => array(
									'left' => '',
									'center' => '',
									'right' => '',
								),
							),
						),
					),

					// Вкладка Дизайн (стандартные настройки Blocksy)
					'design' => array(
						'type' => 'tab',
						'title' => __( 'Design', 'blocksy' ),
						'options' => array(
							'visibility' => array(
								'type' => 'ct-visibility',
								'label' => __( 'Visibility', 'blocksy' ),
								'design' => 'block',
								'allow_empty' => true,
							),
							
							'font_color' => [
								'label' => __( 'Font Color', 'blocksy' ),
								'type'  => 'ct-color-picker',
								'design' => 'block:right',
								'responsive' => true,
								'value' => [
									'default' => [
										'color' => 'CT_CSS_SKIP_RULE',
									],
									'link_initial' => [
										'color' => 'CT_CSS_SKIP_RULE',
									],
									'link_hover' => [
										'color' => 'CT_CSS_SKIP_RULE',
									],
								],
								'pickers' => [
									[
										'title' => __( 'Initial', 'blocksy' ),
										'id' => 'default',
									],
									[
										'title' => __( 'Link Initial', 'blocksy' ),
										'id' => 'link_initial',
									],
									[
										'title' => __( 'Link Hover', 'blocksy' ),
										'id' => 'link_hover',
									],
								],
							],

							'margin' => array(
								'type' => 'ct-spacing',
								'label' => __( 'Margin', 'blocksy' ),
								'responsive' => true,
								'divider' => 'top',
								'value' => blocksy_spacing_value(),
							),
						),
					),
				),
			);
		}
		return $options;
	}

	/**
	 * 4. Логика вывода (HTML) в шапке
	 * * Blocksy вызывает этот хук, если не находит файл view.php для элемента.
	 * * @param string $id ID элемента (напр. x777x_widget_1)
	 * @param array $atts Настройки элемента, полученные из кастомайзера
	 */
	public function render_builder_item( $id, $atts ) {
		// Проверяем, наш ли это виджет
		if ( strpos( $id, 'x777x_widget_' ) === false ) {
			return;
		}

		// Извлекаем номер виджета из ID
		$widget_number = str_replace( 'x777x_widget_', '', $id );
		$sidebar_id = 'x777x-header-widget-' . $widget_number;

		// Если мы в кастомайзере, всегда показываем плейсхолдер, чтобы элемент было видно
		if ( is_customize_preview() && ! is_active_sidebar( $sidebar_id ) ) {
			echo '<div class="ct-header-element" style="border: 1px dashed #ccc; padding: 10px;">Header Widget ' . $widget_number . ' (Empty)</div>';
			return;
		}

		if ( ! is_active_sidebar( $sidebar_id ) ) {
			return;
		}

		// Генерация классов видимости
		$visibility = blocksy_default_akg( 'visibility', $atts, array(
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		) );
		
		$classes = 'ct-header-element x777x-header-widget ';
		$classes .= blocksy_visibility_classes( $visibility );

		// Атрибуты для выравнивания (если используется flex в CSS темы)
		$alignment = blocksy_default_akg( 'horizontal_alignment', $atts, 'left' );
		$attr_string = 'class="' . esc_attr( $classes ) . '" data-id="' . esc_attr( $id ) . '" data-alignment="' . esc_attr( $alignment ) . '"';

		echo '<div ' . $attr_string . '>';
		dynamic_sidebar( $sidebar_id );
		echo '</div>';
	}
}

new X777X_Header_Expansion();