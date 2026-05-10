<?php
/**
 * The layout Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die; } // Cannot access pages directly.

/**
 * The Layout building class.
 */
class SPS_Layout {

	/**
	 * Layout metabox section.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'fields' => array(
					array(
						'type'    => 'heading',
						'image'   => SP_PC_URL . 'admin/assets/img/logo.svg',
						'after'   => '<i class="fa fa-life-ring"></i> Support',
						'link'    => 'https://shapedplugin.com/support/?user=lite',
						'class'   => 'pcp-admin-header',
						'version' => SP_PC_VERSION,
					),
					array(
						'id'      => 'pcp_layout_preset',
						'type'    => 'layout_preset',
						'title'   => __( 'Layout Preset', 'post-carousel' ),
						'class'   => 'pcp-layout-preset',
						'options' => array(
							'carousel_layout'   => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/carousel.svg',
								'text'            => __( 'Carousel', 'post-carousel' ),
								'option_demo_url' => 'https://wpsmartpost.com/blocks/',
							),
							'slider_layout'     => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/slider.svg',
								'text'            => __( 'Slider', 'post-carousel' ),
								'option_demo_url' => 'https://wpsmartpost.com/blocks/#demoId3490',
							),
							'grid_layout'       => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/grid.svg',
								'text'            => __( 'Grid', 'post-carousel' ),
								'option_demo_url' => 'https://wpsmartpost.com/blocks/#demoId3497',
							),
							'list_layout'       => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/list.svg',
								'text'            => __( 'List', 'post-carousel' ),
								'option_demo_url' => 'https://wpsmartpost.com/blocks/#demoId3509',
							),
							'thumbnails_slider' => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/thumb_slider.svg',
								'text'            => __( 'Thumbs Slider', 'post-carousel' ),
								'pro_only'        => true,
								'option_demo_url' => 'https://wpsmartpost.com/blocks/#demoId3570',
							),
							'masonry_layout'    => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/masonry.svg',
								'text'            => __( 'Masonry', 'post-carousel' ),
								'pro_only'        => true,
								// 'option_demo_url' => 'https://smartpostshow.com/demo/masonry/',
							),
							'filter_layout'     => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/isotope.svg',
								'text'            => __( 'Isotope', 'post-carousel' ),
								'pro_only'        => true,
								// 'option_demo_url' => 'https://smartpostshow.com/demo/isotope-filter/',
							),
							'glossary_layout'   => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/glossary.svg',
								'text'            => __( 'Glossary', 'post-carousel' ),
								'pro_only'        => true,
								// 'option_demo_url' => 'https://smartpostshow.com/demo/glossary/',
							),
							'large_with_small'  => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/hierarchical_grid.svg',
								'text'            => __( 'Hierarchical Grid', 'post-carousel' ),
								'pro_only'        => true,
								'option_demo_url' => 'https://wpsmartpost.com/blocks/#demoId3506',
							),
							'timeline_layout'   => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/timeline.svg',
								'text'            => __( 'Timeline', 'post-carousel' ),
								'pro_only'        => true,
								'option_demo_url' => 'https://wpsmartpost.com/blocks/#demoId3522',
							),
							'zigzag_layout'     => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/zigzag.svg',
								'text'            => __( 'Zigzag', 'post-carousel' ),
								'pro_only'        => true,
								// 'option_demo_url' => 'https://smartpostshow.com/demo/zigzag/',
							),
							'table_layout'      => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/table.svg',
								'text'            => __( 'Table', 'post-carousel' ),
								'pro_only'        => true,
								// 'option_demo_url' => 'https://smartpostshow.com/demo/posts-table/',
							),
							'accordion_layout'  => array(
								'image'           => SP_PC_URL . 'admin/img/layout-preset/accordion.svg',
								'text'            => __( 'Accordion', 'post-carousel' ),
								'pro_only'        => true,
								// 'option_demo_url' => 'https://smartpostshow.com/demo/accordion/',
							),
						),
						'default' => 'carousel_layout',
					),
					array(
						'id'         => 'pcp_thumb_style',
						'type'       => 'layout_preset',
						'class'      => 'img_custom_width-3 hide-active-sign pcp_thumb_style padding_0',
						'title'      => __( 'Thumbnails Slider Style', 'post-carousel' ),
						'options'    => array(
							'bottom' => array(
								'image'    => SP_PC_URL . 'admin/img/thumb-style/bottom.svg',
								'text'     => __( 'Bottom', 'post-carousel' ),
								'pro_only' => true,
							),
							'top'    => array(
								'image'    => SP_PC_URL . 'admin/img/thumb-style/top.svg',
								'text'     => __( 'Top', 'post-carousel' ),
								'pro_only' => true,
							),
							'left'   => array(
								'image'    => SP_PC_URL . 'admin/img/thumb-style/left.svg',
								'text'     => __( 'Left', 'post-carousel' ),
								'pro_only' => true,
							),
							'right'  => array(
								'image'    => SP_PC_URL . 'admin/img/thumb-style/right.svg',
								'text'     => __( 'Right', 'post-carousel' ),
								'pro_only' => true,
							),
						),
						'default'    => 'bottom',
						'dependency' => array( 'pcp_layout_preset', '==', 'thumbnails_slider' ),
					),
					array(
						'id'         => 'pcp_grid_style',
						'type'       => 'layout_preset',
						'class'      => 'img_custom_width-2 hide-active-sign padding_0',
						'title'      => __( 'Isotope Style', 'post-carousel' ),
						// 'subtitle'   => __( 'Choose a style for the isotope.', 'post-carousel' ),
						'inline'     => true,
						'options'    => array(
							'even'    => array(
								'image'    => SP_PC_URL . 'admin/img/isotope-style/even.svg',
								'text'     => __( 'Even', 'post-carousel' ),
								'pro_only' => true,
							),
							'masonry' => array(
								'image'    => SP_PC_URL . 'admin/img/isotope-style/masonry.svg',
								'text'     => __( 'Masonry', 'post-carousel' ),
								'pro_only' => true,
							),
						),
						'default'    => 'even',
						'dependency' => array( 'pcp_layout_preset', '==', 'filter_layout', true ),
					),
					array(
						'id'         => '_one_other_style',
						'type'       => 'layout_preset',
						'class'      => 'img_custom_width-3 hide-active-sign padding_0',
						'title'      => __( 'Hierarchical Style', 'post-carousel' ),
						// 'subtitle'   => __( 'Choose a large item position for the large with small layout.', 'post-carousel' ),
						'options'    => array(
							'style_1' => array(
								'image'    => SP_PC_URL . 'admin/img/hierarchial-style/hierarchical_1.svg',
								'text'     => __( 'Hierarchical 1', 'post-carousel' ),
								'pro_only' => true,
							),
							'style_2' => array(
								'image'    => SP_PC_URL . 'admin/img/hierarchial-style/hierarchical_2.svg',
								'text'     => __( 'Hierarchical 2', 'post-carousel' ),
								'pro_only' => true,
							),
							'style_3' => array(
								'image'    => SP_PC_URL . 'admin/img/hierarchial-style/hierarchical_3.svg',
								'text'     => __( 'Hierarchical 3', 'post-carousel' ),
								'pro_only' => true,
							),
							'style_4' => array(
								'image'    => SP_PC_URL . 'admin/img/hierarchial-style/hierarchical_4.svg',
								'text'     => __( 'Hierarchical 4', 'post-carousel' ),
								'pro_only' => true,
							),
							'style_5' => array(
								'image'    => SP_PC_URL . 'admin/img/hierarchial-style/hierarchical_5.svg',
								'text'     => __( 'Hierarchical 5', 'post-carousel' ),
								'pro_only' => true,
							),
						),
						'default'    => 'style_1',
						'dependency' => array( 'pcp_layout_preset', '==', 'large_with_small', true ),
					),
					array(
						'id'         => 'timeline_other_style',
						'type'       => 'layout_preset',
						'class'      => 'timeline_other_style img_custom_width-3 hide-active-sign padding_0',
						'title'      => __( 'Timeline Style', 'post-carousel' ),
						// 'subtitle'   => __( 'Choose a large item position for the large with small layout.', 'post-carousel' ),
						'options'    => array(
							'timeline_style_1' => array(
								'image'    => SP_PC_URL . 'admin/img/timeline-style/style_01.svg',
								'text'     => __( 'Vertical', 'post-carousel' ),
								'pro_only' => true,
							),
							'timeline_style_2' => array(
								'image'    => SP_PC_URL . 'admin/img/timeline-style/style_02.svg',
								'text'     => __( 'Horizontal', 'post-carousel' ),
								'pro_only' => true,
							),
							'timeline_style_3' => array(
								'image'    => SP_PC_URL . 'admin/img/timeline-style/style_03.svg',
								'text'     => __( 'Right Side', 'post-carousel' ),
								'pro_only' => true,
							),
							'timeline_style_4' => array(
								'image'    => SP_PC_URL . 'admin/img/timeline-style/style_04.svg',
								'text'     => __( 'Left Side', 'post-carousel' ),
								'pro_only' => true,
							),
						),
						'default'    => 'timeline_style_1',
						'dependency' => array( 'pcp_layout_preset', '==', 'timeline_layout', true ),
					),
				), // End of fields array.
			)
		);
	}
}
