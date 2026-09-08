<?php
/**
 * Removes plugin data when it is deleted from the WordPress admin.
 *
 * @package MDotCar\Mentoring
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

delete_option( 'mdotcar_mentoring_version' );
delete_option( 'mdotcar_mentoring_installed_version' );
