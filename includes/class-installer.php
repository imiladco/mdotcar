<?php
/**
 * Activation, deactivation and version upgrades.
 *
 * @package MDotCar\Mentoring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Owns the stored plugin version so upgrade routines run exactly once per release.
 */
class MDotCar_Mentoring_Installer {

	/** Option holding the installed (last booted) plugin version. */
	const VERSION_OPTION = 'mdotcar_mentoring_version';

	/** Option holding the version present at first install; never overwritten. */
	const INSTALLED_AT_OPTION = 'mdotcar_mentoring_installed_version';

	/**
	 * Runs on activation.
	 */
	public static function activate() {
		if ( ! get_option( self::INSTALLED_AT_OPTION ) ) {
			add_option( self::INSTALLED_AT_OPTION, MDOTCAR_MENTORING_VERSION );
		}

		self::maybe_upgrade();

		flush_rewrite_rules();
	}

	/**
	 * Runs on deactivation. Data is intentionally kept; uninstall.php removes it.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Compares the stored version with the shipped one and runs the upgrade steps
	 * in between, then records the new version.
	 */
	public static function maybe_upgrade() {
		$stored  = (string) get_option( self::VERSION_OPTION, '' );
		$current = MDOTCAR_MENTORING_VERSION;

		if ( $stored === $current ) {
			return;
		}

		/**
		 * Fires when the plugin boots at a version other than the stored one.
		 *
		 * @param string $stored  Previously stored version ('' on a fresh install).
		 * @param string $current Version being installed.
		 */
		do_action( 'mdotcar_mentoring_upgrade', $stored, $current );

		update_option( self::VERSION_OPTION, $current );
	}
}
