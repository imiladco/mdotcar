<?php
/**
 * Environment checks.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

/**
 * Verifies the PHP, WordPress and Elementor versions the plugin needs.
 */
class MDotCar_Elementor_Requirements {

	/**
	 * Minimum PHP version.
	 *
	 * @var string
	 */
	private $min_php;

	/**
	 * Minimum WordPress version.
	 *
	 * @var string
	 */
	private $min_wp;

	/**
	 * Minimum Elementor version.
	 *
	 * @var string
	 */
	private $min_elementor;

	/**
	 * Messages describing the unmet requirements.
	 *
	 * @var string[]
	 */
	private $errors = array();

	/**
	 * @param string $min_php       Minimum PHP version.
	 * @param string $min_wp        Minimum WordPress version.
	 * @param string $min_elementor Minimum Elementor version.
	 */
	public function __construct( $min_php, $min_wp, $min_elementor ) {
		$this->min_php       = $min_php;
		$this->min_wp        = $min_wp;
		$this->min_elementor = $min_elementor;
	}

	/**
	 * Collects the unmet requirements, if any.
	 *
	 * @return bool True when the environment can run the plugin.
	 */
	public function is_satisfied() {
		$this->errors = array();

		if ( version_compare( PHP_VERSION, $this->min_php, '<' ) ) {
			$this->errors[] = sprintf(
				/* translators: 1: required PHP version, 2: current PHP version. */
				__( 'MDotCar Elementor Widgets requires PHP %1$s or newer. You are running %2$s.', 'mdotcar-elementor' ),
				$this->min_php,
				PHP_VERSION
			);
		}

		if ( version_compare( get_bloginfo( 'version' ), $this->min_wp, '<' ) ) {
			$this->errors[] = sprintf(
				/* translators: 1: required WordPress version, 2: current WordPress version. */
				__( 'MDotCar Elementor Widgets requires WordPress %1$s or newer. You are running %2$s.', 'mdotcar-elementor' ),
				$this->min_wp,
				get_bloginfo( 'version' )
			);
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			$this->errors[] = __( 'MDotCar Elementor Widgets requires the Elementor plugin to be installed and active.', 'mdotcar-elementor' );
		} elseif ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, $this->min_elementor, '<' ) ) {
			$this->errors[] = sprintf(
				/* translators: 1: required Elementor version, 2: current Elementor version. */
				__( 'MDotCar Elementor Widgets requires Elementor %1$s or newer. You are running %2$s.', 'mdotcar-elementor' ),
				$this->min_elementor,
				ELEMENTOR_VERSION
			);
		}

		return empty( $this->errors );
	}

	/**
	 * Prints the admin notice describing why the plugin stayed inactive.
	 */
	public function render_notice() {
		if ( empty( $this->errors ) || ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p>' .
			esc_html( implode( ' ', $this->errors ) ) .
			'</p></div>';
	}
}
