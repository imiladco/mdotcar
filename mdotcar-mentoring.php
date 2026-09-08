<?php
/**
 * Plugin Name:       MDotCar Mentoring
 * Plugin URI:        https://mdotcar.com/
 * Description:       Mentoring widget for mdotcar.com — classic widget, shortcode and block-editor friendly.
 * Version:           0.1.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            claude
 * Author URI:        https://mdotcar.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mdotcar-mentoring
 * Domain Path:       /languages
 *
 * @package MDotCar\Mentoring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Single source of truth for the plugin version (semver).
 * Bump this on every release and mirror it in readme.txt + CHANGELOG.md.
 */
define( 'MDOTCAR_MENTORING_VERSION', '0.1.1' );

/** Minimum environment the plugin supports. */
define( 'MDOTCAR_MENTORING_MIN_PHP', '7.4' );
define( 'MDOTCAR_MENTORING_MIN_WP', '6.0' );

define( 'MDOTCAR_MENTORING_FILE', __FILE__ );
define( 'MDOTCAR_MENTORING_DIR', plugin_dir_path( __FILE__ ) );
define( 'MDOTCAR_MENTORING_URL', plugin_dir_url( __FILE__ ) );
define( 'MDOTCAR_MENTORING_BASENAME', plugin_basename( __FILE__ ) );

require_once MDOTCAR_MENTORING_DIR . 'includes/class-requirements.php';

/**
 * Boot the plugin once the environment has been validated.
 */
function mdotcar_mentoring_bootstrap() {
	$requirements = new MDotCar_Mentoring_Requirements(
		MDOTCAR_MENTORING_MIN_PHP,
		MDOTCAR_MENTORING_MIN_WP
	);

	if ( ! $requirements->is_satisfied() ) {
		add_action( 'admin_notices', array( $requirements, 'render_notice' ) );
		return;
	}

	require_once MDOTCAR_MENTORING_DIR . 'includes/class-autoloader.php';
	MDotCar_Mentoring_Autoloader::register();

	MDotCar_Mentoring_Plugin::instance()->run();
}
add_action( 'plugins_loaded', 'mdotcar_mentoring_bootstrap' );

register_activation_hook( __FILE__, array( 'MDotCar_Mentoring_Installer', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'MDotCar_Mentoring_Installer', 'deactivate' ) );

/**
 * Activation/deactivation run before `plugins_loaded`, so load the installer eagerly.
 */
require_once MDOTCAR_MENTORING_DIR . 'includes/class-installer.php';
