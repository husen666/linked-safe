<?php
/**
 * The Popup Settings Meta-box configurations.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access pages directly.

/**
 * The Popup settings class.
 */
class SPS_DetailSettings {

	/**
	 * Popup settings section metabox.
	 *
	 * @param string $prefix The metabox key.
	 * @return void
	 */
	public static function section( $prefix ) {
		SP_PC::createSection(
			$prefix,
			array(
				'title'  => __( 'Detail page Settings', 'post-carousel' ),
				'icon'   => 'sps-icon-detail-page',
				'fields' => array(
					array(
						'id'       => 'pcp_page_link_type',
						'class'    => 'pcp_page_link_type',
						'type'     => 'radio',
						'title'    => __( 'Detail Page Link Type', 'post-carousel' ),
						'subtitle' => __( 'Choose a link type for the (item) detail page.', 'post-carousel' ),
						'options'  => array(
							'single_page' => __( 'Single Page', 'post-carousel' ),
							'none'        => __( 'None (no link action)', 'post-carousel' ),
							'popup'       => __( 'Popup (Pro)', 'post-carousel' ),
						),
						'default'  => 'single_page',
					),
					array(
						'id'         => 'pcp_link_target',
						'type'       => 'select',
						'title'      => __( 'Target', 'post-carousel' ),
						'subtitle'   => __( 'Set a target for the item link.', 'post-carousel' ),
						'options'    => array(
							'_self'   => __( 'Current Tab', 'post-carousel' ),
							'_blank'  => __( 'New Tab', 'post-carousel' ),
							'_parent' => __( 'Parent', 'post-carousel' ),
							'_top'    => __( 'Top', 'post-carousel' ),
						),
						'default'    => '_self',
						'dependency' => array( 'pcp_page_link_type', '==', 'single_page' ),
					),
					array(
						'id'      => 'pcp_link_rel',
						'type'    => 'checkbox',
						'title'   => __( 'Add rel="nofollow" to item links', 'post-carousel' ),
						'default' => false,
					),
					array(
						'type'    => 'notice',
						'content' => sprintf(
							/* translators: 1: start link tag, 2: close tag. */
							__( 'To unlock additional amazing Popup Settings (Single, Multi, Show/Hide Popup Fields, Custom height-width and more customization options), %1$sUpgrade To Pro!%2$s', 'post-carousel' ),
							'<a href="https://wpsmartpost.com/pricing/?ref=1" target="_blank"><b>',
							'</b></a>'
						),
						'class'   => 'taxonomy-ajax-filter-notice',
					),
				), // End of fields array.
			)
		); // Display settings section end.
	}
}
