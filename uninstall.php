<?php
/**
 * Removes plugin data when it is deleted from the WordPress admin.
 *
 * @package MDotCar\Elementor
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'mdotcar_elementor_version' );
delete_option( 'mdotcar_elementor_installed_version' );
