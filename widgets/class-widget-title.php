<?php
/**
 * MDotCar Title widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Widget_Base;

/**
 * A title with an icon, laid out with the full set of flexbox controls.
 *
 * The widget is built to grow into several presets; Style 1 is the first one.
 */
class MDotCar_Elementor_Widget_Title extends Widget_Base {

	/** CSS selector for the widget's flex box. */
	const BOX = '{{WRAPPER}} .mdotcar-title';

	/**
	 * @return string Widget key used in templates and the panel.
	 */
	public function get_name() {
		return 'mdotcar-title';
	}

	/**
	 * @return string Label shown in the Elementor panel.
	 */
	public function get_title() {
		return __( 'MDotCar Title', 'mdotcar-elementor' );
	}

	/**
	 * @return string Panel icon.
	 */
	public function get_icon() {
		return 'eicon-t-letter';
	}

	/**
	 * @return string[] Panel categories.
	 */
	public function get_categories() {
		return array( MDotCar_Elementor_Plugin::CATEGORY );
	}

	/**
	 * @return string[] Panel search keywords.
	 */
	public function get_keywords() {
		return array( 'title', 'heading', 'icon', 'mdotcar', 'عنوان', 'تیتر' );
	}

	/**
	 * @return string[] Styles enqueued only when the widget is on the page.
	 */
	public function get_style_depends() {
		return array( MDotCar_Elementor_Plugin::HANDLE );
	}

	/**
	 * Registers panel controls.
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_layout_controls();
		$this->register_box_style_controls();
		$this->register_title_style_controls();
		$this->register_icon_style_controls();
	}

	/**
	 * Content tab: preset, title text and icon.
	 */
	private function register_content_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => __( 'Content', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'preset',
			array(
				'label'   => __( 'Style', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => array(
					'style-1' => __( 'Style 1', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Title', 'mdotcar-elementor' ),
				'placeholder' => __( 'Enter your title', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$this->add_control(
			'title_tag',
			array(
				'label'   => __( 'HTML Tag', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'h2',
				'options' => array(
					'h1'   => 'H1',
					'h2'   => 'H2',
					'h3'   => 'H3',
					'h4'   => 'H4',
					'h5'   => 'H5',
					'h6'   => 'H6',
					'div'  => 'div',
					'span' => 'span',
					'p'    => 'p',
				),
			)
		);

		$this->add_control(
			'selected_icon',
			array(
				'label'            => __( 'Icon', 'mdotcar-elementor' ),
				'type'             => Controls_Manager::ICONS,
				'fa4compatibility' => 'icon',
				'skin'             => 'inline',
				'label_block'      => false,
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the flexbox layout of the box holding the icon and the title.
	 *
	 * Justify/align controls come in pairs — one with horizontal icons, one with
	 * vertical ones — swapped by the chosen direction so the icons always match
	 * the axis they act on, the way Elementor's own container controls behave.
	 * Elementor skips CSS for controls whose conditions are not met, so only the
	 * visible control of each pair reaches the page.
	 */
	private function register_layout_controls() {
		$this->start_controls_section(
			'section_layout',
			array(
				'label' => __( 'Layout', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$is_rtl = is_rtl();

		$this->add_responsive_control(
			'display',
			array(
				'label'     => __( 'Display', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'flex',
				'options'   => array(
					'flex'        => __( 'Flex', 'mdotcar-elementor' ),
					'inline-flex' => __( 'Inline Flex', 'mdotcar-elementor' ),
				),
				'selectors' => array(
					self::BOX => 'display: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'flex_direction',
			array(
				'label'     => __( 'Direction', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'default'   => 'row',
				'toggle'    => false,
				'options'   => array(
					'row'            => array(
						'title' => __( 'Row - horizontal', 'mdotcar-elementor' ),
						'icon'  => 'eicon-arrow-' . ( $is_rtl ? 'left' : 'right' ),
					),
					'column'         => array(
						'title' => __( 'Column - vertical', 'mdotcar-elementor' ),
						'icon'  => 'eicon-arrow-down',
					),
					'row-reverse'    => array(
						'title' => __( 'Row - reversed', 'mdotcar-elementor' ),
						'icon'  => 'eicon-arrow-' . ( $is_rtl ? 'right' : 'left' ),
					),
					'column-reverse' => array(
						'title' => __( 'Column - reversed', 'mdotcar-elementor' ),
						'icon'  => 'eicon-arrow-up',
					),
				),
				'selectors' => array(
					self::BOX => 'flex-direction: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'flex_wrap',
			array(
				'label'       => __( 'Wrap', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::CHOOSE,
				'default'     => 'nowrap',
				'toggle'      => false,
				'options'     => array(
					'nowrap' => array(
						'title' => __( 'No Wrap', 'mdotcar-elementor' ),
						'icon'  => 'eicon-flex eicon-nowrap',
					),
					'wrap'   => array(
						'title' => __( 'Wrap', 'mdotcar-elementor' ),
						'icon'  => 'eicon-flex eicon-wrap',
					),
				),
				'description' => __( 'Items stay on a single line unless wrapping is enabled.', 'mdotcar-elementor' ),
				'selectors'   => array(
					self::BOX => 'flex-wrap: {{VALUE}};',
				),
			)
		);

		// Justify Content — main axis, so the icons follow the direction.
		$this->add_flex_pair(
			'justify_content',
			__( 'Justify Content', 'mdotcar-elementor' ),
			'justify-content',
			array(
				'flex-start'    => array( __( 'Start', 'mdotcar-elementor' ), 'justify-start' ),
				'center'        => array( __( 'Center', 'mdotcar-elementor' ), 'justify-center' ),
				'flex-end'      => array( __( 'End', 'mdotcar-elementor' ), 'justify-end' ),
				'space-between' => array( __( 'Space Between', 'mdotcar-elementor' ), 'justify-space-between' ),
				'space-around'  => array( __( 'Space Around', 'mdotcar-elementor' ), 'justify-space-around' ),
				'space-evenly'  => array( __( 'Space Evenly', 'mdotcar-elementor' ), 'justify-space-evenly' ),
			),
			'center',
			// Row lays out along the horizontal axis, column along the vertical one.
			array( 'row' => 'h', 'column' => 'v' )
		);

		// Align Items — cross axis, so the icons are the opposite way round.
		$this->add_flex_pair(
			'align_items',
			__( 'Align Items', 'mdotcar-elementor' ),
			'align-items',
			array(
				'flex-start' => array( __( 'Start', 'mdotcar-elementor' ), 'align-start' ),
				'center'     => array( __( 'Center', 'mdotcar-elementor' ), 'align-center' ),
				'flex-end'   => array( __( 'End', 'mdotcar-elementor' ), 'align-end' ),
				'stretch'    => array( __( 'Stretch', 'mdotcar-elementor' ), 'align-stretch' ),
			),
			'center',
			array( 'row' => 'v', 'column' => 'h' )
		);

		// Align Content — cross axis too, and only meaningful once items wrap.
		$this->add_flex_pair(
			'align_content',
			__( 'Align Content', 'mdotcar-elementor' ),
			'align-content',
			array(
				'flex-start'    => array( __( 'Start', 'mdotcar-elementor' ), 'align-start' ),
				'center'        => array( __( 'Center', 'mdotcar-elementor' ), 'align-center' ),
				'flex-end'      => array( __( 'End', 'mdotcar-elementor' ), 'align-end' ),
				'space-between' => array( __( 'Space Between', 'mdotcar-elementor' ), 'align-space-between' ),
				'space-around'  => array( __( 'Space Around', 'mdotcar-elementor' ), 'align-space-around' ),
				'stretch'       => array( __( 'Stretch', 'mdotcar-elementor' ), 'align-stretch' ),
			),
			'center',
			array( 'row' => 'v', 'column' => 'h' ),
			array( 'flex_wrap' => 'wrap' )
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'range'      => array(
					'px'  => array( 'min' => 0, 'max' => 100 ),
					'em'  => array( 'min' => 0, 'max' => 10, 'step' => 0.1 ),
					'rem' => array( 'min' => 0, 'max' => 10, 'step' => 0.1 ),
					'%'   => array( 'min' => 0, 'max' => 100 ),
				),
				'selectors'  => array(
					self::BOX => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Registers a pair of CHOOSE controls that share one CSS property but carry
	 * axis-specific icons, shown according to the chosen flex direction.
	 *
	 * @param string   $id         Base control id.
	 * @param string   $label      Control label.
	 * @param string   $property   CSS property to output.
	 * @param array[]  $options    value => array( label, eicon base name without axis suffix ).
	 * @param string   $default    Default value.
	 * @param string[] $axis_map   'row' and 'column' mapped to the icon suffix ('h' or 'v').
	 * @param array    $conditions Extra conditions applied to both controls.
	 */
	private function add_flex_pair( $id, $label, $property, $options, $default, $axis_map, $conditions = array() ) {
		$directions = array(
			'row'    => array( 'row', 'row-reverse' ),
			'column' => array( 'column', 'column-reverse' ),
		);

		foreach ( $directions as $orientation => $direction_values ) {
			$axis    = $axis_map[ $orientation ];
			$choices = array();

			foreach ( $options as $value => $option ) {
				list( $option_label, $icon ) = $option;

				$choices[ $value ] = array(
					'title' => $option_label,
					// Every eicon in these sets ships an -h and a -v variant.
					'icon'  => 'eicon-flex eicon-' . $icon . '-' . $axis,
				);
			}

			$this->add_responsive_control(
				$id . '_' . $axis,
				array(
					'label'      => $label,
					'type'       => Controls_Manager::CHOOSE,
					'default'    => $default,
					'options'    => $choices,
					'conditions' => array(
						'relation' => 'and',
						'terms'    => array_merge(
							array(
								array(
									'name'     => 'flex_direction',
									'operator' => 'in',
									'value'    => $direction_values,
								),
							),
							$this->build_condition_terms( $conditions )
						),
					),
					'selectors'  => array(
						self::BOX => $property . ': {{VALUE}};',
					),
				)
			);
		}
	}

	/**
	 * Converts a simple `control => value` map into Elementor condition terms.
	 *
	 * @param array $conditions Control id => expected value.
	 * @return array[]
	 */
	private function build_condition_terms( $conditions ) {
		$terms = array();

		foreach ( $conditions as $name => $value ) {
			$terms[] = array(
				'name'     => $name,
				'operator' => '===',
				'value'    => $value,
			);
		}

		return $terms;
	}

	/**
	 * Style tab: the box itself — size, spacing, background, border and shadow,
	 * in normal and hover states.
	 */
	private function register_box_style_controls() {
		$this->start_controls_section(
			'section_box_style',
			array(
				'label' => __( 'Box', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'width_type',
			array(
				'label'   => __( 'Width', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'fit',
				'options' => array(
					'fit'    => __( 'Fit to content', 'mdotcar-elementor' ),
					'full'   => __( 'Full width (100%)', 'mdotcar-elementor' ),
					'custom' => __( 'Custom', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_responsive_control(
			'width',
			array(
				'label'      => __( 'Custom Width', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem', 'vw' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 320,
				),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 1200 ),
					'%'  => array( 'min' => 0, 'max' => 100 ),
					'vw' => array( 'min' => 0, 'max' => 100 ),
				),
				'condition'  => array( 'width_type' => 'custom' ),
				'selectors'  => array(
					// max-width keeps a fixed width responsive on small screens.
					self::BOX => 'width: {{SIZE}}{{UNIT}}; max-width: 100%;',
				),
			)
		);

		$this->add_control(
			'height_type',
			array(
				'label'     => __( 'Height', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'fit',
				'options'   => array(
					'fit'   => __( 'Fit to content', 'mdotcar-elementor' ),
					'min'   => __( 'Minimum (grows with content)', 'mdotcar-elementor' ),
					'fixed' => __( 'Fixed', 'mdotcar-elementor' ),
				),
				'separator' => 'before',
			)
		);

		$this->add_responsive_control(
			'height',
			array(
				'label'      => __( 'Height Value', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', 'vh' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 56,
				),
				'range'      => array(
					'px' => array( 'min' => 0, 'max' => 600 ),
					'vh' => array( 'min' => 0, 'max' => 100 ),
				),
				'condition'  => array( 'height_type' => array( 'min', 'fixed' ) ),
				'selectors'  => array(
					// The height mode class in the stylesheet decides whether this
					// lands on `min-height` (grows with the content) or `height`
					// (holds the box at exactly this size).
					self::BOX => '--mdotcar-title-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'default'    => array(
					'top'      => 12,
					'right'    => 20,
					'bottom'   => 12,
					'left'     => 20,
					'unit'     => 'px',
					'isLinked' => false,
				),
				'separator'  => 'before',
				'selectors'  => array(
					self::BOX => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'border_radius',
			array(
				'label'      => __( 'Border Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'default'    => array(
					'top'      => 12,
					'right'    => 12,
					'bottom'   => 12,
					'left'     => 12,
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					self::BOX => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'transition_duration',
			array(
				'label'      => __( 'Hover Transition', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 's' ),
				'default'    => array(
					'unit' => 's',
					'size' => 0.3,
				),
				'range'      => array(
					's' => array( 'min' => 0, 'max' => 2, 'step' => 0.1 ),
				),
				'selectors'  => array(
					self::BOX => 'transition: background {{SIZE}}s, border-color {{SIZE}}s, box-shadow {{SIZE}}s, color {{SIZE}}s;',
				),
			)
		);

		$this->start_controls_tabs( 'box_state_tabs' );

		$this->start_controls_tab(
			'box_state_normal',
			array( 'label' => __( 'Normal', 'mdotcar-elementor' ) )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'background',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => self::BOX,
				// The plugin stylesheet carries the default gradient; leaving this
				// control empty keeps it, setting it overrides it.
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'border',
				'selector' => self::BOX,
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow',
				'selector' => self::BOX,
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'box_state_hover',
			array( 'label' => __( 'Hover', 'mdotcar-elementor' ) )
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'     => 'background_hover',
				'types'    => array( 'classic', 'gradient' ),
				'selector' => self::BOX . ':hover',
			)
		);

		$this->add_control(
			'border_color_hover',
			array(
				'label'     => __( 'Border Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					self::BOX . ':hover' => 'border-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'box_shadow_hover',
				'selector' => self::BOX . ':hover',
			)
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();
	}

	/**
	 * Style tab: the title text.
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
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .mdotcar-title__text',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-title__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'title_color_hover',
			array(
				'label'     => __( 'Hover Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					self::BOX . ':hover .mdotcar-title__text' => 'color: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the icon.
	 */
	private function register_icon_style_controls() {
		$this->start_controls_section(
			'section_icon_style',
			array(
				'label'     => __( 'Icon', 'mdotcar-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'selected_icon[value]!' => '' ),
			)
		);

		$this->add_responsive_control(
			'icon_size',
			array(
				'label'       => __( 'Icon Size', 'mdotcar-elementor' ),
				'description' => __( 'Size of the glyph itself. The stylesheet scales both font icons and SVGs from it, so one value covers both.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px', 'em', 'rem' ),
				'default'     => array(
					'unit' => 'px',
					'size' => 24,
				),
				'range'       => array(
					'px'  => array( 'min' => 6, 'max' => 200 ),
					'em'  => array( 'min' => 0.5, 'max' => 10, 'step' => 0.1 ),
					'rem' => array( 'min' => 0.5, 'max' => 10, 'step' => 0.1 ),
				),
				'selectors'   => array(
					// font-size drives the glyph; the SVG is sized in `em` in CSS.
					'{{WRAPPER}} .mdotcar-title__icon' => 'font-size: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'icon_box',
			array(
				'label'        => __( 'Fixed Box', 'mdotcar-elementor' ),
				'description'  => __( 'Gives the icon an explicit width and height instead of letting the glyph decide. A fixed box keeps every icon the same size, is immune to the extra leading font icons carry, and cannot be stretched by Align Items.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => __( 'On', 'mdotcar-elementor' ),
				'label_off'    => __( 'Off', 'mdotcar-elementor' ),
				'return_value' => 'yes',
				'default'      => '',
				'separator'    => 'before',
			)
		);

		$this->add_responsive_control(
			'icon_box_size',
			array(
				'label'      => __( 'Box Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array(
					'unit' => 'px',
					'size' => 40,
				),
				'range'      => array(
					'px'  => array( 'min' => 8, 'max' => 300 ),
					'em'  => array( 'min' => 0.5, 'max' => 16, 'step' => 0.1 ),
					'rem' => array( 'min' => 0.5, 'max' => 16, 'step' => 0.1 ),
				),
				'condition'  => array( 'icon_box' => 'yes' ),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-title__icon' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'icon_box_radius',
			array(
				'label'      => __( 'Box Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'condition'  => array( 'icon_box' => 'yes' ),
				'selectors'  => array(
					'{{WRAPPER}} .mdotcar-title__icon' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'      => 'icon_box_background',
				'types'     => array( 'classic', 'gradient' ),
				'selector'  => '{{WRAPPER}} .mdotcar-title__icon',
				'condition' => array( 'icon_box' => 'yes' ),
			)
		);

		$this->add_control(
			'icon_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .mdotcar-title__icon'          => 'color: {{VALUE}};',
					'{{WRAPPER}} .mdotcar-title__icon svg *'    => 'fill: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'icon_color_hover',
			array(
				'label'     => __( 'Hover Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					self::BOX . ':hover .mdotcar-title__icon'       => 'color: {{VALUE}};',
					self::BOX . ':hover .mdotcar-title__icon svg *' => 'fill: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Front-end output.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		$title = isset( $settings['title'] ) ? $settings['title'] : '';
		$icon  = isset( $settings['selected_icon'] ) ? $settings['selected_icon'] : array();

		$has_icon  = ! empty( $icon['value'] );
		$has_title = '' !== trim( (string) $title );

		if ( ! $has_icon && ! $has_title ) {
			return;
		}

		$preset      = ! empty( $settings['preset'] ) ? $settings['preset'] : 'style-1';
		$width_type  = ! empty( $settings['width_type'] ) ? $settings['width_type'] : 'fit';
		$height_type = ! empty( $settings['height_type'] ) ? $settings['height_type'] : 'fit';

		$icon_classes = array( 'mdotcar-title__icon' );

		if ( ! empty( $settings['icon_box'] ) ) {
			$icon_classes[] = 'mdotcar-title__icon--box';
		}

		$this->add_render_attribute( 'icon', 'class', $icon_classes );

		$this->add_render_attribute(
			'box',
			'class',
			array(
				'mdotcar-title',
				'mdotcar-title--' . $preset,
				'mdotcar-title--width-' . $width_type,
				'mdotcar-title--height-' . $height_type,
			)
		);

		$tag = MDotCar_Elementor_Widget_Title::sanitize_tag(
			isset( $settings['title_tag'] ) ? $settings['title_tag'] : 'h2'
		);

		$this->add_render_attribute( 'title', 'class', 'mdotcar-title__text' );
		$this->add_inline_editing_attributes( 'title', 'none' );

		?>
		<div <?php $this->print_render_attribute_string( 'box' ); ?>>
			<?php if ( $has_icon ) : ?>
				<span <?php $this->print_render_attribute_string( 'icon' ); ?> aria-hidden="true">
					<?php Icons_Manager::render_icon( $icon, array( 'aria-hidden' => 'true' ) ); ?>
				</span>
			<?php endif; ?>

			<?php if ( $has_title ) : ?>
				<<?php echo esc_attr( $tag ); ?> <?php $this->print_render_attribute_string( 'title' ); ?>>
					<?php echo wp_kses_post( $title ); ?>
				</<?php echo esc_attr( $tag ); ?>>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Restricts the title tag to the values offered by the control.
	 *
	 * @param string $tag Requested tag.
	 * @return string
	 */
	public static function sanitize_tag( $tag ) {
		$allowed = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p' );

		return in_array( $tag, $allowed, true ) ? $tag : 'h2';
	}
}
