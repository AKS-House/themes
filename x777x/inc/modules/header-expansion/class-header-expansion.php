<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class X777X_Header_Expansion {

	public function __construct() {
		// 1. Регистрация зон виджетов (сайдбаров)
		add_action( 'widgets_init', array( $this, 'register_custom_sidebars' ) );

		// 2. Указываем Blocksy путь к папке с нашими элементами
		add_filter( 'blocksy:header:items-paths', array( $this, 'add_header_items_path' ) );
	}

	public function register_custom_sidebars() {
		// Регистрируем 5 зон
		for ( $i = 1; $i <= 5; $i++ ) {
			register_sidebar( array(
				'name'          => 'Header Widget ' . $i,
				'id'            => 'x777x-header-widget-' . $i,
				'description'   => 'Зона для элемента Widget ' . $i . ' в шапке.',
				'before_widget' => '<div id="%1$s" class="widget x777x-header-widget-content %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widget-title">',
				'after_title'   => '</h4>',
			) );
		}
	}

	public function add_header_items_path( $paths ) {
		// Сообщаем теме, что в этой папке лежат элементы конструктора
		// get_stylesheet_directory() указывает на корень дочерней темы (x777x)
		$paths[] = get_stylesheet_directory() . '/inc/header-items';
		return $paths;
	}
}

new X777X_Header_Expansion();