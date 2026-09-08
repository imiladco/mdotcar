<?php
/**
 * Class autoloading for the plugin's `MDotCar_Mentoring_*` classes.
 *
 * @package MDotCar\Mentoring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maps class names to WordPress-style file names under includes/ and widgets/.
 */
class MDotCar_Mentoring_Autoloader {

	const PREFIX = 'MDotCar_Mentoring_';

	/** @var string[] Directories searched, in order. */
	private static $paths = array( 'includes/', 'widgets/' );

	/**
	 * Registers the autoloader with SPL.
	 */
	public static function register() {
		spl_autoload_register( array( __CLASS__, 'load' ) );
	}

	/**
	 * @param string $class Fully qualified class name being loaded.
	 */
	public static function load( $class ) {
		if ( 0 !== strpos( $class, self::PREFIX ) ) {
			return;
		}

		$slug = strtolower( str_replace( '_', '-', substr( $class, strlen( self::PREFIX ) ) ) );
		$file = 'class-' . $slug . '.php';

		foreach ( self::$paths as $path ) {
			$candidate = MDOTCAR_MENTORING_DIR . $path . $file;

			if ( is_readable( $candidate ) ) {
				require_once $candidate;
				return;
			}
		}
	}
}
