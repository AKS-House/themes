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

		// 4. Отрисовка элемента в Хедере
		add_action( 'blocksy:header:render:item', array( $this, 'render_builder_item' ), 10, 2 );
	}

	/**
	 * 1. Создаем зоны виджетов в Админке -> Внешний вид -> Виджеты
	 */
	public function register_custom_sidebars() {
		for ( $i = 1; $i <= $this->widgets_count; $i++ ) {
			register_sidebar( array(
				'name'          => 'Кастомный Виджет ' . $i,
				'id'            => 'x777x-custom-widget-' . $i,
				'description'   => 'Эта зона используется в конструкторе шапки (Custom Widget ' . $i . ')',
				'before_widget' => '<div id="%1$s" class="widget x777x-header-widget %2$s">',
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
				'title' => 'Виджет ' . $i,
				// 'group' важен для группировки в палитре (например, рядом с HTML элементами)
				'group' => 'elements', 
				// Конфигурация устройств, где элемент доступен
				'config' => array(
					'devices' => ['desktop', 'tablet', 'mobile'],
				),
				// Указываем, что клонирование отключено, это уникальные элементы
				'clone' => false, 
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
				'title' => 'Виджет ' . $i,
				'options' => array(
					// Вкладка Общие
					'general' => array(
						'type' => 'tab',
						'title' => __( 'General', 'blocksy' ),
						'options' => array(
							// Информационное сообщение
							'info_text' => array(
								'type' => 'ct-message',
								'text' => sprintf( 'Контент этого элемента управляется в разделе <a href="%s" target="_blank">Внешний вид -> Виджеты</a> (Зона: Кастомный Виджет %d).', admin_url('widgets.php'), $i ),
							),
							// Разделитель
							blocksy_rand_md5() => array( 'type' => 'ct-divider' ),
							
							// Настройка выравнивания (полезно для виджетов)
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
					// Вкладка Дизайн (стандартные отступы и видимость)
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
		$sidebar_id = 'x777x-custom-widget-' . $widget_number;

		// Проверяем, активен ли сайдбар в админке
		if ( ! is_active_sidebar( $sidebar_id ) ) {
			// Можно вывести заглушку для админа, если виджет пустой
			if ( is_customize_preview() ) {
				echo '<div class="ct-header-text">Виджет ' . $widget_number . ' (пусто)</div>';
			}
			return;
		}

		// Обработка видимости (Visibility options)
		// Blocksy использует вспомогательную функцию для генерации классов видимости
		$visibility = blocksy_default_akg( 'visibility', $atts, array(
			'desktop' => true,
			'tablet' => true,
			'mobile' => true,
		) );
		
		$classes = 'ct-header-element x777x-widget-area ';
		$classes .= blocksy_visibility_classes( $visibility );

		// Вывод HTML
		echo '<div class="' . esc_attr( $classes ) . '" data-id="' . esc_attr( $id ) . '">';
		dynamic_sidebar( $sidebar_id );
		echo '</div>';
	}
}

new X777X_Header_Expansion();