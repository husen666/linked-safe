<?php
/**
 * The framework shortcode fields file.
 *
 * @package Smart_Post_Show
 * @subpackage Smart_Post_Show/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
} // Cannot access directly.

if ( ! class_exists( 'SP_PC_Field_shortcode' ) ) {
	/**
	 * SP_PC_Field_shortcode
	 */
	class SP_PC_Field_shortcode extends SP_PC_Fields {
		/**
		 * Field constructor.
		 *
		 * @param array  $field The field type.
		 * @param string $value The values of the field.
		 * @param string $unique The unique ID for the field.
		 * @param string $where To where show the output CSS.
		 * @param string $parent The parent args.
		 */
		public function __construct( $field, $value = '', $unique = '', $where = '', $parent = '' ) {
			parent::__construct( $field, $value, $unique, $where, $parent );
		}
		/**
		 * Render method.
		 *
		 * @return void
		 */
		public function render() {
			// Get the Post ID.
			$post_id = get_the_ID();
			if ( ! empty( $this->field['shortcode'] ) && 'manage_view' === $this->field['shortcode'] ) {
				echo ( ! empty( $post_id ) ) ? '<div class="pcp-scode-wrap-side"><p>To display your show or view, add the following shortcode into your post, custom post types, page, widget or block editor. If adding the show to your theme files, additionally include the surrounding PHP code, <a href="https://docs.shapedplugin.com/docs/post-carousel/create-your-first-post-show/add-new-post-show/#faq" target="_blank">see how</a>.</p><span class="pcp-shortcode-selectable">[smart_post_show id="' . esc_attr( $post_id ) . '"]</span></div><div class="pcp-after-copy-text"><i class="fa fa-check-circle"></i> Shortcode Copied to Clipboard! </div>' : '';
			} elseif ( ! empty( $this->field['shortcode'] ) && 'pro_notice' === $this->field['shortcode'] ) {
				if ( ! empty( $post_id ) ) {
					echo '<div class="sp_wpcp_shortcode-area pcp-notice-wrapper">';
					echo '<div class="pcp-notice-heading">' . sprintf(
						/* translators: 1: start span tag, 2: close tag. */
						esc_html__( 'Additional Features in %1$sPRO%2$s', 'post-carousel' ),
						'<span>',
						'</span>'
					) . '</div>';

					echo '<p class="pcp-notice-desc">' . sprintf(
						/* translators: 1: start bold tag, 2: close tag. */
						esc_html__( 'Here are some additional features available in Pro!', 'post-carousel' ),
						'<b>',
						'</b>'
					) . '</p>';

					echo '<ul>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( '30+ Beautiful Layouts', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Advanced Query Builder', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Custom Post Type & Media', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Ajax Live Filtering & Search', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Control Detail Page Fields', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Ajax Load More & Infinite Scroll', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Redesign Blog & Archive Pages', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( 'Show Ads Between Posts', 'post-carousel' ) . '</li>';
					echo '<li><i class="sps-icon-check-icon"></i> ' . esc_html__( '200+ Customizations & More', 'post-carousel' ) . '</li>';
					echo '</ul>';

					echo '<div class="pcp-notice-button">';
					echo '<a class="pcp-open-live-demo" href="https://wpsmartpost.com/pricing/?ref=1" target="_blank">';
					echo esc_html__( 'Upgrade to Pro Now', 'post-carousel' ) . ' <i class="sps-icon-shuttle_2285485-1"></i>';
					echo '</a>';
					echo '</div>';
					echo '</div>';
				}
			} else {
				echo ( ! empty( $post_id ) ) ? '<div class="pcp-scode-wrap-side"><p>Smart Post Show has seamless integration with Gutenberg, Classic Editor, <strong>Elementor,</strong> Divi, Bricks, Beaver, Oxygen, WPBakery Builder, etc.</p></div>' : '';
			}
		}
	}
}
