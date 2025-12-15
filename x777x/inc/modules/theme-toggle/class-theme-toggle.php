<?php
/**
 * Module: Ultimate Light Mode Manager
 * Description: Полное зеркалирование настроек цветов для Светлой темы.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class X777X_Theme_Toggle {

	public function __construct() {
		// 1. Скрипты и стили
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		
		// 2. Шорткоды
		add_shortcode( 'theme_toggle', array( $this, 'render_toggle' ) );
		add_shortcode( 'x777x_theme_toggle', array( $this, 'render_toggle' ) );
		
		// 3. Настройки в Кастомайзере
		add_action( 'customize_register', array( $this, 'register_comprehensive_settings' ) );
		
		// 4. Вывод CSS
		add_action( 'wp_head', array( $this, 'output_comprehensive_css' ), 100 );
	}

	public function register_comprehensive_settings( $wp_customize ) {
		
		// --- СЕКЦИЯ: СВЕТЛАЯ ТЕМА ---
		$wp_customize->add_section( 'x777x_light_mode_section', array(
			'title'       => __( '☀️ Настройки Светлой Темы (FULL)', 'x777x' ),
			'description' => __( 'Полная настройка цветов для светлого режима. Включите светлый режим кнопкой на сайте, чтобы видеть изменения.', 'x777x' ),
			'priority'    => 25,
		) );

		// 1. ГЛОБАЛЬНАЯ ПАЛИТРА
		for ( $i = 1; $i <= 8; $i++ ) {
			$this->add_color_control($wp_customize, 'x777x_light_p' . $i, "Палитра: Цвет $i", '#ffffff');
		}

		// 2. ФОН СТРАНИЦЫ
		$this->add_color_control($wp_customize, 'x777x_light_body_bg', '---- Фон Сайта (Body) ----', '#ffffff');
		
		// 3. КАРТОЧКИ
		$this->add_color_control($wp_customize, 'x777x_light_card_bg', '---- Фон Карточек ----', '#ffffff');
		$this->add_color_control($wp_customize, 'x777x_light_card_shadow', 'Цвет Тени Карточек', 'rgba(0,0,0,0.1)');

		// 4. ШАПКА (3 ряда)
		$this->add_color_control($wp_customize, 'x777x_light_header_top_bg', 'Шапка: Верхний ряд (Top)', 'transparent');
		$this->add_color_control($wp_customize, 'x777x_light_header_main_bg', 'Шапка: Основной ряд (Main)', '#ffffff');
		$this->add_color_control($wp_customize, 'x777x_light_header_bottom_bg', 'Шапка: Нижний ряд (Bottom)', 'transparent');

		// 5. ПОДВАЛ (3 ряда)
		$this->add_color_control($wp_customize, 'x777x_light_footer_top_bg', 'Подвал: Верхний ряд', '#f5f5f5');
		$this->add_color_control($wp_customize, 'x777x_light_footer_middle_bg', 'Подвал: Средний ряд', '#f5f5f5');
		$this->add_color_control($wp_customize, 'x777x_light_footer_bottom_bg', 'Подвал: Нижний ряд', '#ffffff');
	}

	// Вспомогательная функция для быстрого создания контролов
	private function add_color_control( $wp_customize, $id, $label, $default ) {
		$wp_customize->add_setting( $id, array(
			'default'   => $default,
			'transport' => 'refresh', // Используем refresh для надежности при смене фонов
			'sanitize_callback' => 'sanitize_text_field',
		) );

		$wp_customize->add_control( new WP_Customize_Color_Control(
			$wp_customize,
			$id,
			array(
				'label'    => $label,
				'section'  => 'x777x_light_mode_section',
				'settings' => $id,
			)
		) );
	}

	public function output_comprehensive_css() {
		// Получаем настройки
		?>
		<style id="x777x-light-mode-full-css">
			/* Повышаем вес селектора (html body...), чтобы перебить :root темы без !important */
			html body.x777x-light-mode {
				
				/* --- 1. Глобальная Палитра --- */
				<?php for ( $i = 1; $i <= 8; $i++ ) : ?>
					--theme-palette-color-<?php echo $i; ?>: <?php echo get_theme_mod( 'x777x_light_p' . $i, '#ffffff' ); ?>;
				<?php endfor; ?>

				/* Ссылки (привязка к палитре) */
				--theme-link-initial-color: var(--theme-palette-color-1);
				--theme-link-hover-color: var(--theme-palette-color-2);
				--theme-headings-color: var(--theme-palette-color-3);
				--theme-text-color: var(--theme-palette-color-4);
				--theme-border-color: var(--theme-palette-color-5);

				/* --- 2. Фон Сайта --- */
				--theme-body-background-color: <?php echo get_theme_mod('x777x_light_body_bg', '#ffffff'); ?>;
				background-color: var(--theme-body-background-color);
				color: var(--theme-text-color);

				/* --- 3. Карточки --- */
				--theme-card-background-color: <?php echo get_theme_mod('x777x_light_card_bg', '#ffffff'); ?>;
				--theme-box-shadow-color: <?php echo get_theme_mod('x777x_light_card_shadow', 'rgba(0,0,0,0.1)'); ?>;
			}

			/* Применение фона к карточкам Blocksy */
			html body.x777x-light-mode .entry-card,
			html body.x777x-light-mode article.post,
			html body.x777x-light-mode .woocommerce-product-gallery,
			html body.x777x-light-mode .summary {
				background-color: var(--theme-card-background-color);
				box-shadow: 0px 5px 20px var(--theme-box-shadow-color);
			}

			/* --- 4. Шапка (Header) --- */
			/* Top Row */
			html body.x777x-light-mode .site-header [data-row*="top"] {
				background-color: <?php echo get_theme_mod('x777x_light_header_top_bg', 'transparent'); ?>;
			}
			/* Main Row */
			html body.x777x-light-mode .site-header [data-row*="middle"] {
				background-color: <?php echo get_theme_mod('x777x_light_header_main_bg', '#ffffff'); ?>;
			}
			/* Bottom Row */
			html body.x777x-light-mode .site-header [data-row*="bottom"] {
				background-color: <?php echo get_theme_mod('x777x_light_header_bottom_bg', 'transparent'); ?>;
			}

			/* --- 5. Подвал (Footer) --- */
			/* Top */
			html body.x777x-light-mode .site-footer [data-row*="top"] {
				background-color: <?php echo get_theme_mod('x777x_light_footer_top_bg', '#f5f5f5'); ?>;
			}
			/* Middle */
			html body.x777x-light-mode .site-footer [data-row*="middle"] {
				background-color: <?php echo get_theme_mod('x777x_light_footer_middle_bg', '#f5f5f5'); ?>;
			}
			/* Bottom */
			html body.x777x-light-mode .site-footer [data-row*="bottom"] {
				background-color: <?php echo get_theme_mod('x777x_light_footer_bottom_bg', '#ffffff'); ?>;
			}
		</style>
		<?php
	}

	public function enqueue_assets() {
		wp_enqueue_style( 'x777x-toggle', get_stylesheet_directory_uri() . '/inc/modules/theme-toggle/assets/css/style.css', array(), '8.0.0' );
		wp_enqueue_script( 'x777x-toggle', get_stylesheet_directory_uri() . '/inc/modules/theme-toggle/assets/js/script.js', array(), '8.0.0', true );
	}

	public function render_toggle() {
		ob_start();
		?>
		<button id="x777x-theme-toggle-btn" class="x777x-toggle-btn" aria-label="Смена темы">
			<span class="x777x-icon-sun"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg></span>
			<span class="x777x-icon-moon"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg></span>
		</button>
		<?php
		return ob_get_clean();
	}
}

new X777X_Theme_Toggle();