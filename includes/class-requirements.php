<?php
/**
 * Environment checks.
 *
 * @package MDotCar\Mentoring
 */

defined( 'ABSPATH' ) || exit;

/**
 * Verifies the PHP/WordPress versions the plugin needs.
 */
class MDotCar_Mentoring_Requirements {

	/** @var string */
	private $min_php;

	/** @var string */
	private $min_wp;

	/** @var string[] */
	private $errors = array();

	/**
	 * @param string $min_php Minimum PHP version.
	 * @param string $min_wp  Minimum WordPress version.
	 */
	public function __construct( $min_php, $min_wp ) {
		$this->min_php = $min_php;
		$this->min_wp  = $min_wp;
	}

	/**
	 * @return bool True when the environment can run the plugin.
	 */
	public function is_satisfied() {
		$this->errors = array();

		if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
			$this->errors[] = sprintf(
				/* translators: 1: required PHP version, 2: current PHP version. */
				__( 'MDotCar Mentoring requires PHP %1$s or newer. You are running %2$s.', 'mdotcar-mentoring' ),
				$this->min_php,
				PHP_VERSION
			);
		}

		if ( version_compare( get_bloginfo( 'version' ), $this->min_wp, '<' ) ) {
			$this->errors[] = sprintf(
				/* translators: 1: required WordPress version, 2: current WordPress version. */
				__( 'MDotCar Mentoring requires WordPress %1$s or newer. You are running %2$s.', 'mdotcar-mentoring' ),
				$this->min_wp,
				get_bloginfo( 'version' )
			);
		}

		return empty( $this->errors );
	}

	/**
	 * Prints the admin notice describing why the plugin stayed inactive.
	 */
	public function render_notice() {
		if ( empty( $this->errors ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>' .
			esc_html( implode( ' ', $this->errors ) ) .
			'</p></div>';
	}
}
