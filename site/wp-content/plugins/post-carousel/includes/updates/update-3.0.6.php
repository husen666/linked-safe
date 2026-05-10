<?php
/**
 * This file is used to update the database version of the plugin.
 *
 * @link        https://wpsmartpost.com/
 * @since      3.0.6
 *
 * @package    Smart_Post_Show
 * @subpackage Smart_Post_Show/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

update_option( 'smart_post_show_version', '3.0.6' );
update_option( 'smart_post_show_db_version', '3.0.6' );


// If the transient exists, delete it.
if ( get_transient( 'spspc_plugins' ) ) {
	delete_transient( 'spspc_plugins' );
}
