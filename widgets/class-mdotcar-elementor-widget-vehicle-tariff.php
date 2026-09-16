<?php
/**
 * MDotCar Vehicle Tariff widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;

/**
 * Car-wash tariff cards: one card per vehicle, each with an image, a title, its
 * tariffs, a link the whole card follows, and a value stored in the browser for
 * the booking flow to pick up.
 */
class MDotCar_Elementor_Widget_Vehicle_Tariff extends Widget_Base {

	/** How many tariff columns a card offers. The design uses three. */
	const TARIFFS = 3;

	/** CSS selector for the grid holding the cards. */
	const GRID = '{{WRAPPER}} .mdotcar-vehicles';

	/** CSS selector for one card. */
	const CARD = '{{WRAPPER}} .mdotcar-vehicle';

	/**
	 * Widget key used in templates and the panel.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mdotcar-vehicle-tariff';
	}

	/**
	 * Label shown in the Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'MDotCar Vehicle Tariff', 'mdotcar-elementor' );
	}

	/**
	 * Icon shown next to the widget in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-price-table';
	}

	/**
	 * Panel categories the widget belongs to.
	 *
	 * @return string[]
	 */
	public function get_categories() {
		return array( MDotCar_Elementor_Plugin::CATEGORY );
	}

	/**
	 * Keywords matched by the panel's search box.
	 *
	 * @return string[]
	 */
	public function get_keywords() {
		return array( 'vehicle', 'car', 'price', 'tariff', 'carwash', 'mdotcar', 'تعرفه', 'خودرو', 'کارواش' );
	}

	/**
	 * Styles enqueued only when the widget is on the page.
	 *
	 * @return string[]
	 */
	public function get_style_depends() {
		return array( MDotCar_Elementor_Plugin::HANDLE );
	}

	/**
	 * Scripts enqueued only when the widget is on the page.
	 *
	 * @return string[]
	 */
	public function get_script_depends() {
		return array( MDotCar_Elementor_Plugin::HANDLE );
	}

	/**
	 * Registers panel controls.
	 */
	protected function register_controls() {
		$this->register_vehicles_controls();
		$this->register_card_controls();
		$this->register_storage_controls();
		$this->register_layout_style_controls();
		$this->register_card_style_controls();
		$this->register_media_style_controls();
		$this->register_backdrop_style_controls();
		$this->register_title_style_controls();
		$this->register_tariff_style_controls();
		$this->register_action_style_controls();
	}

	/**
	 * Content tab: the vehicles themselves.
	 */
	private function register_vehicles_controls() {
		$this->start_controls_section(
			'section_vehicles',
			array(
				'label' => __( 'Vehicles', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Vehicle Title', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Vehicle', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'image',
			array(
				'label'   => __( 'Vehicle Image', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
				'default' => array( 'url' => Utils::get_placeholder_image_src() ),
			)
		);

		$repeater->add_control(
			'badge_image',
			array(
				'label'       => __( 'Badge Image', 'mdotcar-elementor' ),
				'description' => __( 'Takes precedence over the badge icon. Useful for a vehicle-class illustration.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'backdrop_image',
			array(
				'label'       => __( 'Card Backdrop', 'mdotcar-elementor' ),
				'description' => __( 'An image or SVG layered over the card background and behind its content.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'badge_icon',
			array(
				'label'       => __( 'Badge Icon', 'mdotcar-elementor' ),
				'description' => __( 'Shown in the round badge over the image. Leave empty for the built-in car outline.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'mdotcar-elementor' ),
				'description' => __( 'The whole card follows this link, not only the button.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => 'https://mdotcar.com/',
			)
		);

		$repeater->add_control(
			'storage_value',
			array(
				'label'       => __( 'Stored Value', 'mdotcar-elementor' ),
				'description' => __( 'Written to localStorage or a cookie when the card is clicked, for the booking flow to read back. Typically the vehicle slug or id.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
			)
		);

		$defaults = $this->tariff_defaults();

		for ( $index = 1; $index <= self::TARIFFS; $index++ ) {
			$repeater->add_control(
				'tariff_' . $index . '_heading',
				array(
					/* translators: %d: tariff column number. */
					'label'     => sprintf( __( 'Tariff %d', 'mdotcar-elementor' ), $index ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
				)
			);

			$repeater->add_control(
				'tariff_' . $index . '_show',
				array(
					'label'        => __( 'Show', 'mdotcar-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$repeater->add_control(
				'tariff_' . $index . '_label',
				array(
					'label'     => __( 'Label', 'mdotcar-elementor' ),
					'type'      => Controls_Manager::TEXT,
					'dynamic'   => array( 'active' => true ),
					'default'   => $defaults[ $index ]['label'],
					'condition' => array( 'tariff_' . $index . '_show' => 'yes' ),
				)
			);

			$repeater->add_control(
				'tariff_' . $index . '_amount',
				array(
					'label'       => __( 'Amount', 'mdotcar-elementor' ),
					'description' => __( 'Digits only; separators and Persian numerals are applied on output.', 'mdotcar-elementor' ),
					'type'        => Controls_Manager::TEXT,
					'dynamic'     => array( 'active' => true ),
					'default'     => $defaults[ $index ]['amount'],
					'condition'   => array( 'tariff_' . $index . '_show' => 'yes' ),
				)
			);

			$repeater->add_control(
				'tariff_' . $index . '_featured',
				array(
					'label'        => __( 'Featured', 'mdotcar-elementor' ),
					'description'  => __( 'Gives the label the accent colour, as VIP has in the design.', 'mdotcar-elementor' ),
					'type'         => Controls_Manager::SWITCHER,
					'return_value' => 'yes',
					'default'      => $defaults[ $index ]['featured'],
					'condition'    => array( 'tariff_' . $index . '_show' => 'yes' ),
				)
			);
		}

		$this->add_control(
			'vehicles',
			array(
				'label'       => __( 'Vehicles', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ title }}}',
				'default'     => array(
					array(
						'title'           => __( 'Large Iranian sedan', 'mdotcar-elementor' ),
						'tariff_1_label'  => $defaults[1]['label'],
						'tariff_1_amount' => $defaults[1]['amount'],
						'tariff_2_label'  => $defaults[2]['label'],
						'tariff_2_amount' => $defaults[2]['amount'],
						'tariff_3_label'  => $defaults[3]['label'],
						'tariff_3_amount' => $defaults[3]['amount'],
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Default label/amount/featured per tariff column, matching the design.
	 *
	 * @return array[]
	 */
	private function tariff_defaults() {
		return array(
			1 => array(
				'label'    => __( 'VIP', 'mdotcar-elementor' ),
				'amount'   => '1180000',
				'featured' => 'yes',
			),
			2 => array(
				'label'    => __( 'Corporate', 'mdotcar-elementor' ),
				'amount'   => '630000',
				'featured' => '',
			),
			3 => array(
				'label'    => __( 'Personal', 'mdotcar-elementor' ),
				'amount'   => '660000',
				'featured' => '',
			),
		);
	}

	/**
	 * Content tab: the parts every card shares.
	 */
	private function register_card_controls() {
		$this->start_controls_section(
			'section_card',
			array(
				'label' => __( 'Card', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'currency',
			array(
				'label'       => __( 'Currency Label', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Toman', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'thousand_separator',
			array(
				'label'        => __( 'Thousand Separator', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'persian_digits',
			array(
				'label'        => __( 'Persian Digits', 'mdotcar-elementor' ),
				'description'  => __( 'Renders amounts with ۰-۹ instead of 0-9.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'action_text',
			array(
				'label'       => __( 'Action Text', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => __( 'Book now', 'mdotcar-elementor' ),
				'label_block' => true,
				'separator'   => 'before',
			)
		);

		$this->add_control(
			'action_icon',
			array(
				'label'       => __( 'Action Icon', 'mdotcar-elementor' ),
				'description' => __( 'Leave empty for the built-in chevron.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::ICONS,
				'skin'        => 'inline',
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'     => __( 'Title HTML Tag', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'h3',
				'options'   => array(
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'div'  => 'div',
					'span' => 'span',
				),
				'separator' => 'before',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: what a click leaves behind in the browser.
	 */
	private function register_storage_controls() {
		$this->start_controls_section(
			'section_storage',
			array(
				'label' => __( 'Stored Selection', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'storage_enabled',
			array(
				'label'        => __( 'Remember Selection', 'mdotcar-elementor' ),
				'description'  => __( 'Saves the clicked vehicle in the browser before the link is followed.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->add_control(
			'storage_type',
			array(
				'label'     => __( 'Store In', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'local',
				'options'   => array(
					'local'  => __( 'localStorage', 'mdotcar-elementor' ),
					'cookie' => __( 'Cookie', 'mdotcar-elementor' ),
					'both'   => __( 'Both', 'mdotcar-elementor' ),
				),
				'condition' => array( 'storage_enabled' => 'yes' ),
			)
		);

		$this->add_control(
			'storage_key',
			array(
				'label'       => __( 'Key Name', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'default'     => 'mdotcar_vehicle',
				'label_block' => true,
				'condition'   => array( 'storage_enabled' => 'yes' ),
			)
		);

		$this->add_control(
			'storage_days',
			array(
				'label'     => __( 'Cookie Lifetime (days)', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 365,
				'default'   => 30,
				'condition' => array(
					'storage_enabled' => 'yes',
					'storage_type'    => array( 'cookie', 'both' ),
				),
			)
		);

		$this->add_control(
			'storage_payload',
			array(
				'label'       => __( 'What to Store', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'value',
				'options'     => array(
					'value'   => __( 'Stored Value only', 'mdotcar-elementor' ),
					'details' => __( 'JSON: value, title and tariffs', 'mdotcar-elementor' ),
				),
				'description' => __( 'JSON also carries the card title and its tariff amounts, so a booking page can show them without another lookup.', 'mdotcar-elementor' ),
				'condition'   => array( 'storage_enabled' => 'yes' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: how the cards are laid out next to each other.
	 */
	private function register_layout_style_controls() {
		$this->start_controls_section(
			'section_layout_style',
			array(
				'label' => __( 'Layout', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'label'          => __( 'Columns', 'mdotcar-elementor' ),
				'type'           => Controls_Manager::SELECT,
				'default'        => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options'        => array(
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
				),
				'selectors'      => array(
					self::GRID => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr));',
				),
			)
		);

		$this->add_responsive_control(
			'grid_gap',
			array(
				'label'      => __( 'Gap', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 120,
					),
				),
				'selectors'  => array(
					self::GRID => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the card shell.
	 */
	private function register_card_style_controls() {
		$this->start_controls_section(
			'section_card_style',
			array(
				'label' => __( 'Card', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'card_background',
				'types'          => array( 'classic', 'gradient' ),
				'selector'       => self::CARD,
				'fields_options' => array(
					'background' => array( 'default' => 'classic' ),
					'color'      => array( 'default' => '#FFFFFF' ),
				),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => __( 'Border Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'default'    => array(
					'top'      => 16,
					'right'    => 16,
					'bottom'   => 16,
					'left'     => 16,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'size_units' => array( 'px', '%', 'rem' ),
				'selectors'  => array(
					self::CARD => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding_top',
			array(
				'label'      => __( 'Top Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 32,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 120,
					),
				),
				'selectors'  => array(
					self::CARD => 'padding-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding_bottom',
			array(
				'label'      => __( 'Bottom Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'unit' => 'px',
					'size' => 16,
				),
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					self::CARD => 'padding-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'accent_heading',
			array(
				'label'     => __( 'Accent Edge', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'accent_width',
			array(
				'label'      => __( 'Width', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'unit' => 'px',
					'size' => 1,
				),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 16,
					),
				),
				'selectors'  => array(
					// Inline-start so the edge stays on the reading side in RTL and LTR.
					self::CARD => 'border-inline-start-width: {{SIZE}}{{UNIT}}; border-inline-start-style: solid;',
				),
			)
		);

		$this->add_control(
			'accent_color',
			array(
				'label'     => __( 'Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0095FF',
				'selectors' => array(
					self::CARD => 'border-inline-start-color: {{VALUE}};',
				),
			)
		);

		$this->start_controls_tabs( 'card_state_tabs' );

		$this->start_controls_tab(
			'card_state_normal',
			array( 'label' => __( 'Normal', 'mdotcar-elementor' ) )
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'           => 'card_shadow',
				'selector'       => self::CARD,
				'fields_options' => array(
					'box_shadow_type' => array( 'default' => 'yes' ),
					'box_shadow'      => array(
						'default' => array(
							'horizontal' => 0,
							'vertical'   => 2,
							'blur'       => 10,
							'spread'     => 0,
							'color'      => 'rgba(24, 36, 101, 0.1)',
						),
					),
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'card_state_hover',
			array( 'label' => __( 'Hover', 'mdotcar-elementor' ) )
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow_hover',
				'selector' => self::CARD . ':hover',
			)
		);

		$this->add_control(
			'card_hover_lift',
			array(
				'label'      => __( 'Lift', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 24,
					),
				),
				'selectors'  => array(
					self::CARD . ':hover' => 'transform: translateY(-{{SIZE}}{{UNIT}});',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style tab: image area, the blue blob behind it and the badge.
	 */
	private function register_media_style_controls() {
		$this->start_controls_section(
			'section_media_style',
			array(
				'label' => __( 'Image', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'image_height',
			array(
				'label'      => __( 'Image Height', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'unit' => 'px',
					'size' => 120,
				),
				'size_units' => array( 'px', 'vh' ),
				'range'      => array(
					'px' => array(
						'min' => 40,
						'max' => 400,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__image' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'image_fit',
			array(
				'label'     => __( 'Image Fit', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'contain',
				'options'   => array(
					'contain'    => __( 'Contain', 'mdotcar-elementor' ),
					'cover'      => __( 'Cover', 'mdotcar-elementor' ),
					'fill'       => __( 'Fill', 'mdotcar-elementor' ),
					'scale-down' => __( 'Scale down', 'mdotcar-elementor' ),
					'none'       => __( 'None', 'mdotcar-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__image' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'image_max_width',
			array(
				'label'       => __( 'Image Max Width', 'mdotcar-elementor' ),
				'description' => __( 'The width stays automatic; this only caps it so a wide photo cannot reach the card edges.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( '%', 'px' ),
				'default'     => array(
					'unit' => '%',
					'size' => 85,
				),
				'range'       => array(
					'%'  => array(
						'min' => 20,
						'max' => 100,
					),
					'px' => array(
						'min' => 40,
						'max' => 800,
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .mdotcar-vehicle__image' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'blob_color',
			array(
				'label'     => __( 'Backdrop Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E6F4FF',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__blob' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'blob_height',
			array(
				'label'      => __( 'Backdrop Height', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'unit' => 'px',
					'size' => 154,
				),
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 400,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__blob' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'badge_heading',
			array(
				'label'     => __( 'Badge', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'       => __( 'Padding', 'mdotcar-elementor' ),
				'description' => __( 'The badge sizes itself from its icon plus this padding.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em' ),
				'default'     => array(
					'unit' => 'px',
					'size' => 7,
				),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .mdotcar-vehicle__badge' => 'padding: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => 50,
					'right'    => 50,
					'bottom'   => 50,
					'left'     => 50,
					'unit'     => '%',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_offset',
			array(
				'label'      => __( 'Offset', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 16,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 120,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__badge' => 'top: {{SIZE}}{{UNIT}}; inset-inline-start: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_icon_size',
			array(
				'label'      => __( 'Icon Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'unit' => 'px',
					'size' => 34,
				),
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__badge' => '--mdotcar-badge-icon: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .mdotcar-vehicle__badge-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'badge_background',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FCFDFD',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_color',
			array(
				'label'     => __( 'Icon Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#424242',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__badge' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mdotcar-vehicle__badge svg *' => 'stroke: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the image layered over the card background, behind the content.
	 */
	private function register_backdrop_style_controls() {
		$this->start_controls_section(
			'section_backdrop_style',
			array(
				'label' => __( 'Backdrop Layer', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'backdrop_image',
			array(
				'label'       => __( 'Image or SVG', 'mdotcar-elementor' ),
				'description' => __( 'Shared by every card; a vehicle can override it in its own settings. It sits above the card background and below the content.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
			)
		);

		$this->add_control(
			'backdrop_size',
			array(
				'label'     => __( 'Size', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'cover',
				'options'   => array(
					'cover'   => __( 'Cover', 'mdotcar-elementor' ),
					'contain' => __( 'Contain', 'mdotcar-elementor' ),
					'auto'    => __( 'Auto', 'mdotcar-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__backdrop' => 'background-size: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'backdrop_position',
			array(
				'label'     => __( 'Position', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center center',
				'options'   => array(
					'center center' => __( 'Center', 'mdotcar-elementor' ),
					'center top'    => __( 'Top', 'mdotcar-elementor' ),
					'center bottom' => __( 'Bottom', 'mdotcar-elementor' ),
					'left center'   => __( 'Left', 'mdotcar-elementor' ),
					'right center'  => __( 'Right', 'mdotcar-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__backdrop' => 'background-position: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'backdrop_repeat',
			array(
				'label'     => __( 'Repeat', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'no-repeat',
				'options'   => array(
					'no-repeat' => __( 'No Repeat', 'mdotcar-elementor' ),
					'repeat'    => __( 'Repeat', 'mdotcar-elementor' ),
					'repeat-x'  => __( 'Repeat X', 'mdotcar-elementor' ),
					'repeat-y'  => __( 'Repeat Y', 'mdotcar-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__backdrop' => 'background-repeat: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'backdrop_opacity',
			array(
				'label'     => __( 'Opacity', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 1,
						'step' => 0.05,
					),
				),
				'default'   => array( 'size' => 1 ),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__backdrop' => 'opacity: {{SIZE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the card title.
	 */
	private function register_title_style_controls() {
		$this->start_controls_section(
			'section_title_style',
			array(
				'label' => __( 'Title', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'title_typography',
				'fields_options' => array(
					'typography'  => array( 'default' => 'custom' ),
					'font_family' => array(
						'default' => 'Dana-FaNum',
					),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 24,
						),
					),
					'font_weight' => array(
						'default' => '700',
					),
					'line_height' => array(
						'default' => array(
							'unit' => 'px',
							'size' => 32,
						),
					),
				),
				'selector'       => '{{WRAPPER}} .mdotcar-vehicle__title',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the tariff columns.
	 */
	private function register_tariff_style_controls() {
		$this->start_controls_section(
			'section_tariff_style',
			array(
				'label' => __( 'Tariffs', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'tariff_gap',
			array(
				'label'       => __( 'Column Gap', 'mdotcar-elementor' ),
				'description' => __( 'The divider sits in the middle of this gap.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em' ),
				'default'     => array(
					'unit' => 'px',
					'size' => 16,
				),
				'range'       => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'   => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariffs' => 'gap: {{SIZE}}{{UNIT}};',
					// Half the gap, on whichever side the divider sits: the RTL
					// stylesheet flips which of the two offsets is in play.
					'{{WRAPPER}} .mdotcar-vehicle__tariff + .mdotcar-vehicle__tariff::before' => '--mdotcar-tariff-divider-offset: calc({{SIZE}}{{UNIT}} / -2);',
				),
			)
		);

		$this->add_responsive_control(
			'tariff_row_gap',
			array(
				'label'      => __( 'Row Gap', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 4,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'tariff_label_typography',
				'fields_options' => array(
					'typography'  => array( 'default' => 'custom' ),
					'font_family' => array(
						'default' => 'Dana-FaNum',
					),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 14,
						),
					),
					'font_weight' => array(
						'default' => '500',
					),
					'line_height' => array(
						'default' => array(
							'unit' => 'px',
							'size' => 22,
						),
					),
				),
				'label'          => __( 'Label Typography', 'mdotcar-elementor' ),
				'selector'       => '{{WRAPPER}} .mdotcar-vehicle__tariff-label',
			)
		);

		$this->add_control(
			'tariff_label_color',
			array(
				'label'     => __( 'Label Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#424242',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tariff_featured_color',
			array(
				'label'     => __( 'Featured Label Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0088E8',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff--featured .mdotcar-vehicle__tariff-label' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tariff_featured_weight',
			array(
				'label'     => __( 'Featured Label Weight', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => '700',
				'options'   => array(
					''    => __( 'Default', 'mdotcar-elementor' ),
					'500' => '500',
					'600' => '600',
					'700' => '700',
					'800' => '800',
				),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff--featured .mdotcar-vehicle__tariff-label' => 'font-weight: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'tariff_amount_typography',
				'fields_options' => array(
					'typography'  => array( 'default' => 'custom' ),
					'font_family' => array(
						'default' => 'Dana-FaNum',
					),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 18,
						),
					),
					'font_weight' => array(
						'default' => '500',
					),
					'line_height' => array(
						'default' => array(
							'unit' => 'px',
							'size' => 26,
						),
					),
				),
				'label'          => __( 'Amount Typography', 'mdotcar-elementor' ),
				'selector'       => '{{WRAPPER}} .mdotcar-vehicle__tariff-amount',
				'separator'      => 'before',
			)
		);

		$this->add_control(
			'tariff_amount_color',
			array(
				'label'     => __( 'Amount Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff-amount' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'tariff_currency_typography',
				'fields_options' => array(
					'typography'  => array( 'default' => 'custom' ),
					'font_family' => array(
						'default' => 'Dana-FaNum',
					),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 12,
						),
					),
					'font_weight' => array(
						'default' => '400',
					),
					'line_height' => array(
						'default' => array(
							'unit' => 'px',
							'size' => 18,
						),
					),
				),
				'label'          => __( 'Currency Typography', 'mdotcar-elementor' ),
				'selector'       => '{{WRAPPER}} .mdotcar-vehicle__tariff-currency',
				'separator'      => 'before',
			)
		);

		$this->add_control(
			'tariff_currency_color',
			array(
				'label'     => __( 'Currency Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#9E9E9E',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff-currency' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'tariff_divider_color',
			array(
				'label'     => __( 'Divider Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E2E8F0',
				'separator' => 'before',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__tariff + .mdotcar-vehicle__tariff::before' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the divider and the call to action at the foot of the card.
	 */
	private function register_action_style_controls() {
		$this->start_controls_section(
			'section_action_style',
			array(
				'label' => __( 'Action', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'foot_divider_style',
			array(
				'label'     => __( 'Divider Style', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'dashed',
				'options'   => array(
					'dashed' => __( 'Dashed', 'mdotcar-elementor' ),
					'solid'  => __( 'Solid', 'mdotcar-elementor' ),
					'dotted' => __( 'Dotted', 'mdotcar-elementor' ),
					'none'   => __( 'None', 'mdotcar-elementor' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__foot' => 'border-top-style: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'foot_divider_color',
			array(
				'label'     => __( 'Divider Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#DCDFE4',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__foot' => 'border-top-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'action_spacing',
			array(
				'label'      => __( 'Space Above Action', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 12,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__foot' => 'padding-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'action_padding_bottom',
			array(
				'label'      => __( 'Space Below Action', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__action' => 'padding-bottom: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'action_gap',
			array(
				'label'      => __( 'Icon Gap', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 40,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__action' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'action_typography',
				'fields_options' => array(
					'typography'  => array( 'default' => 'custom' ),
					'font_family' => array(
						'default' => 'Dana-FaNum',
					),
					'font_size'   => array(
						'default' => array(
							'unit' => 'px',
							'size' => 16,
						),
					),
					'font_weight' => array(
						'default' => '500',
					),
					'line_height' => array(
						'default' => array(
							'unit' => 'px',
							'size' => 24,
						),
					),
				),
				'selector'       => '{{WRAPPER}} .mdotcar-vehicle__action',
			)
		);

		$this->add_responsive_control(
			'action_icon_size',
			array(
				'label'      => __( 'Icon Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'size_units' => array( 'px', 'em' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-vehicle__action' => '--mdotcar-action-icon: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->start_controls_tabs( 'action_state_tabs' );

		$this->start_controls_tab(
			'action_state_normal',
			array( 'label' => __( 'Normal', 'mdotcar-elementor' ) )
		);

		$this->add_control(
			'action_color',
			array(
				'label'     => __( 'Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3F51B5',
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-vehicle__action' => 'color: {{VALUE}};',
					'{{WRAPPER}} .mdotcar-vehicle__action svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'action_state_hover',
			array( 'label' => __( 'Hover', 'mdotcar-elementor' ) )
		);

		$this->add_control(
			'action_color_hover',
			array(
				'label'     => __( 'Colour', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					self::CARD . ':hover .mdotcar-vehicle__action'       => 'color: {{VALUE}};',
					self::CARD . ':hover .mdotcar-vehicle__action svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'action_background_hover',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					self::CARD . ':hover .mdotcar-vehicle__action' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Front-end output: the grid of vehicle cards.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		MDotCar_Elementor_Plugin::enqueue();

		if ( empty( $settings['vehicles'] ) || ! is_array( $settings['vehicles'] ) ) {
			return;
		}

		echo '<div class="mdotcar-vehicles">';

		foreach ( $settings['vehicles'] as $index => $vehicle ) {
			$this->render_card( $vehicle, $index, $settings );
		}

		echo '</div>';
	}

	/**
	 * Renders one vehicle card.
	 *
	 * The card itself is the link: an `<a>` wrapping everything, so a click
	 * anywhere on it navigates, and the action row at the foot is a `<span>`
	 * styled as a button rather than a second anchor.
	 *
	 * @param array $vehicle  One repeater item.
	 * @param int   $index    Item index, used for unique render attribute keys.
	 * @param array $settings Widget settings.
	 */
	private function render_card( $vehicle, $index, $settings ) {
		$key = 'card_' . $index;

		$this->add_render_attribute( $key, 'class', 'mdotcar-vehicle' );

		$has_link = ! empty( $vehicle['link']['url'] );
		$tag      = $has_link ? 'a' : 'div';

		if ( $has_link ) {
			$this->add_link_attributes( $key, $vehicle['link'] );
		}

		foreach ( $this->storage_attributes( $vehicle, $settings ) as $name => $value ) {
			$this->add_render_attribute( $key, $name, $value );
		}

		$title_tag = $this->sanitize_title_tag( $settings );
		?>
		<<?php echo esc_attr( $tag ); ?> <?php $this->print_render_attribute_string( $key ); ?>>
			<?php
			$backdrop = ! empty( $vehicle['backdrop_image']['url'] )
				? $vehicle['backdrop_image']['url']
				: ( ! empty( $settings['backdrop_image']['url'] ) ? $settings['backdrop_image']['url'] : '' );
			?>

			<?php if ( '' !== $backdrop ) : ?>
				<span
					class="mdotcar-vehicle__backdrop"
					style="background-image: url( <?php echo esc_url( $backdrop ); ?> );"
					aria-hidden="true"
				></span>
			<?php endif; ?>

			<span class="mdotcar-vehicle__blob" aria-hidden="true"></span>

			<span class="mdotcar-vehicle__badge" aria-hidden="true">
				<?php $this->render_badge( $vehicle ); ?>
			</span>

			<div class="mdotcar-vehicle__media">
				<?php if ( ! empty( $vehicle['image']['url'] ) ) : ?>
					<img
						class="mdotcar-vehicle__image"
						src="<?php echo esc_url( $vehicle['image']['url'] ); ?>"
						alt="<?php echo esc_attr( $vehicle['title'] ); ?>"
						loading="lazy"
					/>
				<?php endif; ?>
			</div>

			<?php if ( ! empty( $vehicle['title'] ) ) : ?>
				<<?php echo esc_attr( $title_tag ); ?> class="mdotcar-vehicle__title">
					<?php echo esc_html( $vehicle['title'] ); ?>
				</<?php echo esc_attr( $title_tag ); ?>>
			<?php endif; ?>

			<?php $this->render_tariffs( $vehicle, $settings ); ?>

			<?php if ( ! empty( $settings['action_text'] ) ) : ?>
				<div class="mdotcar-vehicle__foot">
					<span class="mdotcar-vehicle__action">
						<span class="mdotcar-vehicle__action-icon" aria-hidden="true">
							<?php $this->render_action_icon( $settings ); ?>
						</span>
						<span class="mdotcar-vehicle__action-text">
							<?php echo esc_html( $settings['action_text'] ); ?>
						</span>
					</span>
				</div>
			<?php endif; ?>
		</<?php echo esc_attr( $tag ); ?>>
		<?php
	}

	/**
	 * Renders the tariff columns of one card.
	 *
	 * @param array $vehicle  One repeater item.
	 * @param array $settings Widget settings.
	 */
	private function render_tariffs( $vehicle, $settings ) {
		$columns = $this->tariffs_of( $vehicle );

		if ( empty( $columns ) ) {
			return;
		}

		echo '<div class="mdotcar-vehicle__tariffs">';

		foreach ( $columns as $column ) {
			$classes = 'mdotcar-vehicle__tariff';

			if ( $column['featured'] ) {
				$classes .= ' mdotcar-vehicle__tariff--featured';
			}

			printf( '<div class="%s">', esc_attr( $classes ) );
			printf(
				'<span class="mdotcar-vehicle__tariff-label">%s</span>',
				esc_html( $column['label'] )
			);
			printf(
				'<span class="mdotcar-vehicle__tariff-amount">%s</span>',
				esc_html( $this->format_amount( $column['amount'], $settings ) )
			);

			if ( ! empty( $settings['currency'] ) ) {
				printf(
					'<span class="mdotcar-vehicle__tariff-currency">%s</span>',
					esc_html( $settings['currency'] )
				);
			}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * The visible tariff columns of a vehicle, in panel order.
	 *
	 * @param array $vehicle One repeater item.
	 * @return array[] Each with label, amount and featured.
	 */
	private function tariffs_of( $vehicle ) {
		$columns = array();

		for ( $index = 1; $index <= self::TARIFFS; $index++ ) {
			$prefix = 'tariff_' . $index . '_';

			if ( empty( $vehicle[ $prefix . 'show' ] ) ) {
				continue;
			}

			$label  = isset( $vehicle[ $prefix . 'label' ] ) ? $vehicle[ $prefix . 'label' ] : '';
			$amount = isset( $vehicle[ $prefix . 'amount' ] ) ? $vehicle[ $prefix . 'amount' ] : '';

			if ( '' === trim( (string) $label ) && '' === trim( (string) $amount ) ) {
				continue;
			}

			$columns[] = array(
				'label'    => $label,
				'amount'   => $amount,
				'featured' => ! empty( $vehicle[ $prefix . 'featured' ] ),
			);
		}

		return $columns;
	}

	/**
	 * Formats an amount for display: grouped in thousands and, by default, in
	 * Persian numerals. Anything that is not a plain number is passed through
	 * untouched, so "From 660,000" keeps working.
	 *
	 * @param string $amount   Raw amount from the panel.
	 * @param array  $settings Widget settings.
	 * @return string
	 */
	private function format_amount( $amount, $settings ) {
		$amount = trim( (string) $amount );

		if ( '' === $amount ) {
			return '';
		}

		if ( ! empty( $settings['thousand_separator'] ) && ctype_digit( $amount ) ) {
			$amount = number_format( (float) $amount, 0, '.', ',' );
		}

		if ( ! empty( $settings['persian_digits'] ) ) {
			$amount = str_replace(
				array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ),
				array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' ),
				$amount
			);
		}

		return $amount;
	}

	/**
	 * Data attributes the front-end script reads to remember a click.
	 *
	 * @param array $vehicle  One repeater item.
	 * @param array $settings Widget settings.
	 * @return array<string,string> Attribute name => value, empty when storing is off.
	 */
	private function storage_attributes( $vehicle, $settings ) {
		if ( empty( $settings['storage_enabled'] ) || empty( $settings['storage_key'] ) ) {
			return array();
		}

		$value = isset( $vehicle['storage_value'] ) ? trim( (string) $vehicle['storage_value'] ) : '';

		if ( '' === $value ) {
			return array();
		}

		if ( 'details' === $settings['storage_payload'] ) {
			$tariffs = array();

			foreach ( $this->tariffs_of( $vehicle ) as $column ) {
				$tariffs[] = array(
					'label'  => $column['label'],
					'amount' => $column['amount'],
				);
			}

			$value = wp_json_encode(
				array(
					'value'   => $value,
					'title'   => isset( $vehicle['title'] ) ? $vehicle['title'] : '',
					'tariffs' => $tariffs,
				)
			);
		}

		return array(
			'data-mdotcar-store'       => $settings['storage_type'],
			'data-mdotcar-store-key'   => $settings['storage_key'],
			'data-mdotcar-store-value' => $value,
			'data-mdotcar-store-days'  => (string) ( ! empty( $settings['storage_days'] ) ? (int) $settings['storage_days'] : 30 ),
		);
	}

	/**
	 * Prints what the badge holds: an image if one was chosen, otherwise the
	 * chosen icon, otherwise the built-in car outline.
	 *
	 * @param array $vehicle One repeater item.
	 */
	private function render_badge( $vehicle ) {
		if ( ! empty( $vehicle['badge_image']['url'] ) ) {
			printf(
				'<img class="mdotcar-vehicle__badge-image" src="%s" alt="" loading="lazy" />',
				esc_url( $vehicle['badge_image']['url'] )
			);
			return;
		}

		if ( ! empty( $vehicle['badge_icon']['value'] ) ) {
			Icons_Manager::render_icon( $vehicle['badge_icon'], array( 'aria-hidden' => 'true' ) );
			return;
		}

		?>
		<svg viewBox="0 0 40 24" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
			<path d="M2 16h36" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
			<path d="M4 16v-3.2c0-.9.6-1.7 1.5-1.9l6.2-1.6 4.6-3.6c.6-.5 1.4-.7 2.2-.7h8.3c.9 0 1.7.3 2.3.9l4.6 4.4 2.4.7c.9.3 1.5 1.1 1.5 2V16" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>
			<path d="M16.5 5.2V10.5M27 10.5H7" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
			<circle cx="11" cy="16.5" r="3.2" stroke="currentColor" stroke-width="1.4"/>
			<circle cx="29" cy="16.5" r="3.2" stroke="currentColor" stroke-width="1.4"/>
		</svg>
		<?php
	}

	/**
	 * Prints the action icon, falling back to the built-in chevron.
	 *
	 * @param array $settings Widget settings.
	 */
	private function render_action_icon( $settings ) {
		if ( ! empty( $settings['action_icon']['value'] ) ) {
			Icons_Manager::render_icon( $settings['action_icon'], array( 'aria-hidden' => 'true' ) );
			return;
		}

		?>
		<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" focusable="false">
			<path d="M12.3 3.6a.9.9 0 0 1 0 1.3L7.2 10l5.1 5.1a.9.9 0 1 1-1.3 1.3l-5.7-5.7a.9.9 0 0 1 0-1.3l5.7-5.8a.9.9 0 0 1 1.3 0Z" fill="currentColor"/>
		</svg>
		<?php
	}

	/**
	 * Restricts the title tag to the values the control offers.
	 *
	 * @param array $settings Widget settings.
	 * @return string
	 */
	private function sanitize_title_tag( $settings ) {
		$allowed = array( 'h2', 'h3', 'h4', 'h5', 'div', 'span' );
		$tag     = isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h3';

		return in_array( $tag, $allowed, true ) ? $tag : 'h3';
	}
}
