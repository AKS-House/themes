<?php

$options = [
	'general' => [
		'type' => 'tab',
		'title' => __( 'General', 'blocksy' ),
		'options' => [
			
			// Выравнивание элемента в ячейке шапки
			'horizontal_alignment' => [
				'type' => 'ct-radio',
				'label' => __( 'Horizontal Alignment', 'blocksy' ),
				'view' => 'text',
				'design' => 'block',
				'responsive' => true,
				'attr' => [ 'data-type' => 'alignment' ],
				'value' => 'left',
				'choices' => [
					'left' => '',
					'center' => '',
					'right' => '',
				],
			],

			// Ограничение ширины
			'max_width' => [
				'label' => __( 'Max Width (px)', 'blocksy' ),
				'type' => 'ct-slider',
				'min' => 50,
				'max' => 1000,
				'value' => 'CT_CSS_SKIP_RULE',
				'responsive' => true,
				'divider' => 'top',
			],
		],
	],

	'design' => [
		'type' => 'tab',
		'title' => __( 'Design', 'blocksy' ),
		'options' => [
			'visibility' => [
				'type' => 'ct-visibility',
				'label' => __( 'Visibility', 'blocksy' ),
				'design' => 'block',
				'allow_empty' => true,
				'value' => [
					'desktop' => true,
					'tablet' => true,
					'mobile' => true,
				],
			],
			
			'margin' => [
				'type' => 'ct-spacing',
				'label' => __( 'Margin', 'blocksy' ),
				'responsive' => true,
				'divider' => 'top',
				'value' => blocksy_spacing_value(),
			],
		],
	],
];