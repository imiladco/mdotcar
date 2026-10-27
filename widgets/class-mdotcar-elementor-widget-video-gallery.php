<?php
/**
 * MDotCar Video Gallery widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Plugin as Elementor;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * Videos as a row of cards (cover, badge, glass play button, title and
 * description) in a slider, each opening in a modal with the site's own
 * player.
 *
 * The slider is the browser's own scroll with snapping, so a finger, a
 * trackpad and the keyboard all move it; the arrows step it one card. The
 * modal is a dialog: focus goes in and stays, the page does not scroll, Esc
 * and the scrim close it, and closing stops the video's download. A video
 * file plays in the player (play, seek with what is buffered, time, volume,
 * speed, picture-in-picture, fullscreen; the controls hide while it plays);
 * an Aparat or YouTube link plays embedded in the same modal.
 *
 * Every style control carries mdotcar.com's design as its default.
 */
class MDotCar_Elementor_Widget_Video_Gallery extends Widget_Base {

	/** Root of the widget's markup. */
	const ROOT = '{{WRAPPER}} .mdotcar-vgal';

	/** A card. */
	const CARD = '{{WRAPPER}} .mdotcar-vgal__card';

	/** The modal, wherever the script moved it (it is portalled to <body>). */
	const MODAL = '.mdotcar-vmodal[data-for="{{ID}}"]';

	/**
	 * Widget key used in templates and the panel.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mdotcar-video-gallery';
	}

	/**
	 * Label shown in the Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'MDotCar Video Gallery', 'mdotcar-elementor' );
	}

	/**
	 * Icon shown next to the widget in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-video-playlist';
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
		return array( 'video', 'gallery', 'slider', 'carousel', 'modal', 'player', 'mdotcar', 'ویدیو', 'اسلایدر', 'آموزش' );
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
		$this->register_videos_controls();
		$this->register_slider_controls();
		$this->register_card_style_controls();
		$this->register_media_style_controls();
		$this->register_badge_style_controls();
		$this->register_play_style_controls();
		$this->register_text_style_controls();
		$this->register_arrow_style_controls();
		$this->register_modal_style_controls();
	}

	/*
	 * Content
	 */

	/**
	 * Content tab: the videos.
	 */
	private function register_videos_controls() {
		$this->start_controls_section(
			'section_videos',
			array(
				'label' => __( 'Videos', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'image',
			array(
				'label'   => __( 'Cover Image', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'badge',
			array(
				'label'   => __( 'Badge', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => array( 'active' => true ),
				'default' => 'اپلیکیشن مشتری',
			)
		);

		$repeater->add_control(
			'title',
			array(
				'label'       => __( 'Title', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => 'آشنایی با فرآیند دریافت سرویس',
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'description',
			array(
				'label'   => __( 'Description', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXTAREA,
				'dynamic' => array( 'active' => true ),
				'rows'    => 2,
				'default' => 'شروع پذیرش سرویس، مسیریابی و تغییر وضعیت',
			)
		);

		$repeater->add_control(
			'source',
			array(
				'label'     => __( 'Video', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'file',
				'options'   => array(
					'file'  => __( 'A file (uploaded or its address)', 'mdotcar-elementor' ),
					'embed' => __( 'Aparat or YouTube', 'mdotcar-elementor' ),
				),
				'separator' => 'before',
			)
		);

		$repeater->add_control(
			'video',
			array(
				'label'       => __( 'Video File', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'media_types' => array( 'video' ),
				'condition'   => array( 'source' => 'file' ),
			)
		);

		$repeater->add_control(
			'video_url',
			array(
				'label'       => __( 'Or Its Address', 'mdotcar-elementor' ),
				'description' => __( 'An .mp4 or .webm link; used when no file is chosen.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => 'https://…/video.mp4',
				'label_block' => true,
				'condition'   => array( 'source' => 'file' ),
			)
		);

		$repeater->add_control(
			'embed_url',
			array(
				'label'       => __( 'Video Page Address', 'mdotcar-elementor' ),
				'description' => __( 'e.g. https://www.aparat.com/v/abc123 or https://youtu.be/abc123', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'label_block' => true,
				'condition'   => array( 'source' => 'embed' ),
			)
		);

		$this->add_control(
			'videos',
			array(
				'label'       => __( 'Videos', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array( array(), array(), array(), array() ),
				'title_field' => '{{{ title }}}',
			)
		);

		$this->add_control(
			'gallery_label',
			array(
				'label'   => __( 'Name for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'ویدیوهای آموزشی',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the slider.
	 */
	private function register_slider_controls() {
		$this->start_controls_section(
			'section_slider',
			array(
				'label' => __( 'Slider', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_responsive_control(
			'per_view',
			array(
				'label'          => __( 'Cards in View', 'mdotcar-elementor' ),
				'description'    => __( 'A fraction shows a part of the next one, e.g. 1.2 on phones.', 'mdotcar-elementor' ),
				'type'           => Controls_Manager::NUMBER,
				'min'            => 1,
				'max'            => 6,
				'step'           => 0.05,
				'default'        => 3,
				'tablet_default' => 2,
				'mobile_default' => 1.2,
				'selectors'      => array( self::ROOT => '--mdotcar-vgal-per: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'gap',
			array(
				'label'      => __( 'Gap Between Cards', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 48 ) ),
				'default'    => array(
					'unit' => 'px',
					'size' => 12,
				),
				'selectors'  => array( self::ROOT => '--mdotcar-vgal-gap: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'arrows',
			array(
				'label'                => __( 'Arrows', 'mdotcar-elementor' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'show',
				'tablet_default'       => 'show',
				'mobile_default'       => 'hide',
				'options'              => array(
					'show' => __( 'Show', 'mdotcar-elementor' ),
					'hide' => __( 'Hide', 'mdotcar-elementor' ),
				),
				'selectors_dictionary' => array(
					'show' => 'flex',
					'hide' => 'none',
				),
				'selectors'            => array( self::ROOT => '--mdotcar-vgal-arrow: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'prev_label',
			array(
				'label'   => __( 'Previous, for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'ویدیوهای قبلی',
			)
		);

		$this->add_control(
			'next_label',
			array(
				'label'   => __( 'Next, for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'ویدیوهای بعدی',
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Style (every default is the site's design, so the panel shows it)
	 */

	/**
	 * A dimensions default.
	 *
	 * @param int $top    Top.
	 * @param int $side   Right and left.
	 * @param int $bottom Bottom; the top when left out.
	 * @return array
	 */
	private static function box( $top, $side, $bottom = null ) {
		$bottom = null === $bottom ? $top : $bottom;
		return array(
			'top'      => (string) $top,
			'right'    => (string) $side,
			'bottom'   => (string) $bottom,
			'left'     => (string) $side,
			'unit'     => 'px',
			'isLinked' => $top === $side && $side === $bottom,
		);
	}

	/**
	 * Typography defaults.
	 *
	 * @param int    $size   Font size.
	 * @param string $weight Font weight.
	 * @param int    $line   Line height.
	 * @return array
	 */
	private static function type( $size, $weight, $line ) {
		return array(
			'typography'  => array( 'default' => 'custom' ),
			'font_size'   => array(
				'default' => array(
					'unit' => 'px',
					'size' => $size,
				),
			),
			'font_weight' => array( 'default' => $weight ),
			'line_height' => array(
				'default' => array(
					'unit' => 'px',
					'size' => $line,
				),
			),
		);
	}

	/**
	 * A slider control in px with a default.
	 *
	 * @param string $label    Label.
	 * @param int    $value    Default.
	 * @param int    $max      Range end.
	 * @param array  $selector Selectors.
	 * @param int    $min      Range start.
	 * @return array
	 */
	private static function px( $label, $value, $max, array $selector, $min = 0 ) {
		return array(
			'label'      => $label,
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array(
				'px' => array(
					'min'  => $min,
					'max'  => $max,
					'step' => 0.5,
				),
			),
			'default'    => array(
				'unit' => 'px',
				'size' => $value,
			),
			'selectors'  => $selector,
		);
	}

	/**
	 * Style tab: the card.
	 */
	private function register_card_style_controls() {
		$this->start_controls_section(
			'section_style_card',
			array(
				'label' => __( 'Card', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'card_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( self::CARD => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'           => 'card_border',
				'selector'       => self::CARD,
				'fields_options' => array(
					'border' => array( 'default' => 'solid' ),
					'width'  => array( 'default' => self::box( 1, 1 ) ),
					'color'  => array( 'default' => '#E0E0E0' ),
				),
			)
		);

		$this->add_responsive_control( 'card_radius', self::px( __( 'Radius', 'mdotcar-elementor' ), 16, 40, array( self::CARD => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 6, 6, 20 ),
				'selectors'  => array( self::CARD => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => self::CARD,
			)
		);

		$this->add_control(
			'card_hover_border',
			array(
				'label'     => __( 'Hover: Border Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#C5CAE9',
				'selectors' => array( self::CARD . ':hover, ' . self::CARD . ':focus-within' => 'border-color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the cover.
	 */
	private function register_media_style_controls() {
		$this->start_controls_section(
			'section_style_media',
			array(
				'label' => __( 'Cover Image', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$media = self::CARD . ' .mdotcar-vgal__media';

		$this->add_responsive_control( 'media_height', self::px( __( 'Height', 'mdotcar-elementor' ), 194, 480, array( $media => 'height: {{SIZE}}{{UNIT}};' ), 80 ) );
		$this->add_responsive_control( 'media_radius', self::px( __( 'Radius', 'mdotcar-elementor' ), 12, 40, array( $media => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );

		$this->add_control(
			'media_position',
			array(
				'label'     => __( 'Focal Point', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'center center',
				'options'   => array(
					'center center' => __( 'Center', 'mdotcar-elementor' ),
					'center top'    => __( 'Top', 'mdotcar-elementor' ),
					'center bottom' => __( 'Bottom', 'mdotcar-elementor' ),
					'right center'  => __( 'Right', 'mdotcar-elementor' ),
					'left center'   => __( 'Left', 'mdotcar-elementor' ),
				),
				'selectors' => array( $media . ' img' => 'object-position: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'media_blur',
			array_merge(
				self::px( __( 'Blur Over the Image', 'mdotcar-elementor' ), 2, 24, array( $media . ' .mdotcar-vgal__veil' => '-webkit-backdrop-filter: blur({{SIZE}}{{UNIT}}); backdrop-filter: blur({{SIZE}}{{UNIT}});' ) ),
				array( 'description' => __( 'A soft layer over the image so the button and the badge stand out; 0 for none.', 'mdotcar-elementor' ) )
			)
		);

		$this->add_control(
			'media_tint',
			array(
				'label'     => __( 'Tint Over the Image', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0)',
				'selectors' => array( $media . ' .mdotcar-vgal__veil' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'media_zoom',
			array(
				'label'        => __( 'Zoom on Hover', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'prefix_class' => 'mdotcar-vgal-zoom-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the badge.
	 */
	private function register_badge_style_controls() {
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => __( 'Badge', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$badge = self::CARD . ' .mdotcar-vgal__badge';

		$this->add_control(
			'badge_color',
			array(
				'label'     => __( 'Text Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#0095FF',
				'selectors' => array( $badge => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'badge_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E6F4FF',
				'selectors' => array( $badge => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'badge_typography',
				'selector'       => $badge,
				'fields_options' => self::type( 12, '500', 18 ),
			)
		);

		$this->add_responsive_control(
			'badge_padding',
			array(
				'label'      => __( 'Padding', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 6, 6 ),
				'selectors'  => array( $badge => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control( 'badge_radius', self::px( __( 'Radius', 'mdotcar-elementor' ), 6, 24, array( $badge => 'border-radius: {{SIZE}}{{UNIT}};' ) ) );
		$this->add_responsive_control( 'badge_offset', self::px( __( 'Distance From the Corner', 'mdotcar-elementor' ), 12, 40, array( $badge => 'top: {{SIZE}}{{UNIT}}; inset-inline-start: {{SIZE}}{{UNIT}};' ) ) );

		$this->end_controls_section();
	}

	/**
	 * Style tab: the play button on the cards.
	 */
	private function register_play_style_controls() {
		$this->start_controls_section(
			'section_style_play',
			array(
				'label' => __( 'Play Button', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$play = self::CARD . ' .mdotcar-vgal__play';

		$this->add_responsive_control( 'play_size', self::px( __( 'Size', 'mdotcar-elementor' ), 56, 120, array( $play => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ), 32 ) );
		$this->add_responsive_control( 'play_icon', self::px( __( 'Icon Size', 'mdotcar-elementor' ), 24, 64, array( $play . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ), 12 ) );

		$this->add_control(
			'play_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF40',
				'selectors' => array( $play => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'play_color',
			array(
				'label'     => __( 'Icon Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( $play => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control( 'play_blur', self::px( __( 'Glass Blur', 'mdotcar-elementor' ), 11.67, 40, array( $play => '-webkit-backdrop-filter: blur({{SIZE}}{{UNIT}}); backdrop-filter: blur({{SIZE}}{{UNIT}});' ) ) );

		$this->add_control(
			'play_edge',
			array(
				'label'        => __( 'Glass Edge', 'mdotcar-elementor' ),
				'description'  => __( 'The light border fading across the button, and its inner highlight.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
				'prefix_class' => 'mdotcar-vgal-edge-',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the title and the description.
	 */
	private function register_text_style_controls() {
		$this->start_controls_section(
			'section_style_text',
			array(
				'label' => __( 'Title and Description', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'body_padding',
			array(
				'label'      => __( 'Padding Around the Text', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px' ),
				'default'    => self::box( 16, 10, 0 ),
				'selectors'  => array( self::CARD . ' .mdotcar-vgal__body' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control( 'body_gap', self::px( __( 'Gap Between Title and Description', 'mdotcar-elementor' ), 8, 32, array( self::CARD . ' .mdotcar-vgal__body' => 'gap: {{SIZE}}{{UNIT}};' ) ) );

		$this->add_control(
			'title_heading',
			array(
				'label'     => __( 'Title', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array( self::CARD . ' .mdotcar-vgal__title' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'title_typography',
				'selector'       => self::CARD . ' .mdotcar-vgal__title',
				'fields_options' => self::type( 16, '700', 24 ),
			)
		);

		$this->add_responsive_control(
			'title_lines',
			array(
				'label'     => __( 'Lines at Most', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 6,
				'default'   => 1,
				'selectors' => array( self::CARD . ' .mdotcar-vgal__title' => '-webkit-line-clamp: {{VALUE}}; line-clamp: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'desc_heading',
			array(
				'label'     => __( 'Description', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'desc_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#616161',
				'selectors' => array( self::CARD . ' .mdotcar-vgal__desc' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'           => 'desc_typography',
				'selector'       => self::CARD . ' .mdotcar-vgal__desc',
				'fields_options' => self::type( 14, '400', 22 ),
			)
		);

		$this->add_responsive_control(
			'desc_lines',
			array(
				'label'     => __( 'Lines at Most', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::NUMBER,
				'min'       => 1,
				'max'       => 6,
				'default'   => 1,
				'selectors' => array( self::CARD . ' .mdotcar-vgal__desc' => '-webkit-line-clamp: {{VALUE}}; line-clamp: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the slider's arrows.
	 */
	private function register_arrow_style_controls() {
		$this->start_controls_section(
			'section_style_arrows',
			array(
				'label' => __( 'Arrows', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$arrow = self::ROOT . ' .mdotcar-vgal__arrow';

		$this->add_responsive_control( 'arrow_size', self::px( __( 'Size', 'mdotcar-elementor' ), 48, 96, array( $arrow => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ), 24 ) );
		$this->add_responsive_control( 'arrow_icon', self::px( __( 'Icon Size', 'mdotcar-elementor' ), 24, 48, array( $arrow . ' svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};' ), 12 ) );
		$this->add_responsive_control( 'arrow_space', self::px( __( 'Space to the Cards', 'mdotcar-elementor' ), 16, 64, array( self::ROOT => '--mdotcar-vgal-space: {{SIZE}}{{UNIT}};' ) ) );

		$this->add_control(
			'arrow_bg',
			array(
				'label'     => __( 'Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#F5F5F5',
				'selectors' => array( $arrow => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_color',
			array(
				'label'     => __( 'Icon Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#212121',
				'selectors' => array( $arrow => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_hover_bg',
			array(
				'label'     => __( 'Hover: Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#E8EAF6',
				'selectors' => array( $arrow . ':not(:disabled):hover' => 'background-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_hover_color',
			array(
				'label'     => __( 'Hover: Icon Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3F51B5',
				'selectors' => array( $arrow . ':not(:disabled):hover' => 'color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'arrow_disabled',
			array(
				'label'     => __( 'At the End: Icon Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#BDBDBD',
				'selectors' => array( $arrow . ':disabled' => 'color: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the modal and its player.
	 */
	private function register_modal_style_controls() {
		$this->start_controls_section(
			'section_style_modal',
			array(
				'label' => __( 'Video Window', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'modal_accent',
			array(
				'label'       => __( 'Accent', 'mdotcar-elementor' ),
				'description' => __( 'The progress bar, the volume and the focus ring.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => '#3F51B5',
				'selectors'   => array( self::MODAL => '--mdotcar-vm-accent: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'modal_scrim',
			array(
				'label'     => __( 'Page Dim Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(17,20,39,0.78)',
				'selectors' => array( self::MODAL => '--mdotcar-vm-scrim: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'modal_bg',
			array(
				'label'     => __( 'Panel Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#FFFFFF',
				'selectors' => array( self::MODAL => '--mdotcar-vm-bg: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control( 'modal_width', self::px( __( 'Width', 'mdotcar-elementor' ), 960, 1600, array( self::MODAL => '--mdotcar-vm-width: {{SIZE}}{{UNIT}};' ), 320 ) );
		$this->add_responsive_control( 'modal_radius', self::px( __( 'Radius', 'mdotcar-elementor' ), 20, 40, array( self::MODAL => '--mdotcar-vm-radius: {{SIZE}}{{UNIT}};' ) ) );

		$this->end_controls_section();
	}

	/*
	 * Render
	 */

	/**
	 * What the player needs to play a video, or null when there is nothing to play.
	 *
	 * @param array $row Repeater row.
	 * @return array|null
	 */
	private static function video_of( array $row ) {
		if ( 'embed' === $row['source'] ) {
			$embed = self::embed_url( (string) $row['embed_url'] );
			return $embed ? array(
				'type' => 'embed',
				'src'  => $embed,
			) : null;
		}
		$src = ! empty( $row['video']['url'] ) ? $row['video']['url'] : trim( (string) $row['video_url'] );
		return '' !== $src ? array(
			'type' => 'file',
			'src'  => esc_url_raw( $src ),
		) : null;
	}

	/**
	 * An Aparat or YouTube page address as the address of its player.
	 *
	 * @param string $url As typed.
	 * @return string '' when it is neither.
	 */
	private static function embed_url( $url ) {
		$url = trim( $url );
		if ( preg_match( '~aparat\.com/(?:v/|video/video/embed/videohash/)([A-Za-z0-9]+)~', $url, $m ) ) {
			return 'https://www.aparat.com/video/video/embed/videohash/' . $m[1] . '/vt/frame?autoplay=true';
		}
		if ( preg_match( '~(?:youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $url, $m ) ) {
			return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0&playsinline=1';
		}
		return '';
	}

	/**
	 * Renders the widget.
	 */
	protected function render() {
		MDotCar_Elementor_Plugin::enqueue();

		$s      = $this->get_settings_for_display();
		$id     = $this->get_id();
		$videos = (array) $s['videos'];
		?>
		<div class="mdotcar-vgal" data-mdotcar-vgal="<?php echo esc_attr( $id ); ?>">
			<button type="button" class="mdotcar-vgal__arrow mdotcar-vgal__arrow--prev" aria-label="<?php echo esc_attr( $s['prev_label'] ); ?>" disabled>
				<?php echo self::svg( 'prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
			</button>
			<div class="mdotcar-vgal__viewport" role="region" aria-roledescription="carousel" aria-label="<?php echo esc_attr( $s['gallery_label'] ); ?>" tabindex="0">
				<ul class="mdotcar-vgal__track">
					<?php
					foreach ( $videos as $n => $row ) {
						$this->render_card( $row, $n, count( $videos ) );
					}
					?>
				</ul>
			</div>
			<button type="button" class="mdotcar-vgal__arrow mdotcar-vgal__arrow--next" aria-label="<?php echo esc_attr( $s['next_label'] ); ?>">
				<?php echo self::svg( 'next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?>
			</button>
		</div>
		<?php
		$this->render_modal( $id );
	}

	/**
	 * One card.
	 *
	 * @param array $row   Repeater row.
	 * @param int   $n     Its place (also names its title for the button).
	 * @param int   $count How many there are.
	 */
	private function render_card( array $row, $n, $count ) {
		$video = self::video_of( $row );
		$image = ! empty( $row['image']['url'] ) ? $row['image']['url'] : '';
		$data  = array(
			'title' => (string) $row['title'],
			'desc'  => (string) $row['description'],
			'badge' => (string) $row['badge'],
			'image' => $image,
		) + ( $video ? $video : array() );
		$label = 'mdotcar-vgal-' . $this->get_id() . '-' . $n;
		?>
		<li class="mdotcar-vgal__slide elementor-repeater-item-<?php echo esc_attr( $row['_id'] ); ?>" aria-roledescription="slide" aria-label="<?php echo esc_attr( ( $n + 1 ) . ' / ' . $count ); ?>">
			<article class="mdotcar-vgal__card">
				<div class="mdotcar-vgal__media">
					<?php if ( $image ) : ?>
						<img src="<?php echo esc_url( $image ); ?>" alt="" loading="lazy" decoding="async">
					<?php endif; ?>
					<span class="mdotcar-vgal__veil" aria-hidden="true"></span>
					<?php if ( '' !== trim( (string) $row['badge'] ) ) : ?>
						<span class="mdotcar-vgal__badge"><?php echo esc_html( $row['badge'] ); ?></span>
					<?php endif; ?>
					<span class="mdotcar-vgal__play" aria-hidden="true"><?php echo self::svg( 'play' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></span>
				</div>
				<div class="mdotcar-vgal__body">
					<h3 class="mdotcar-vgal__title" id="<?php echo esc_attr( $label ); ?>"><?php echo esc_html( $row['title'] ); ?></h3>
					<?php if ( '' !== trim( (string) $row['description'] ) ) : ?>
						<p class="mdotcar-vgal__desc"><?php echo esc_html( $row['description'] ); ?></p>
					<?php endif; ?>
				</div>
				<?php if ( $video ) : ?>
					<button type="button" class="mdotcar-vgal__open" aria-labelledby="<?php echo esc_attr( $label ); ?>" aria-haspopup="dialog" data-video="<?php echo esc_attr( wp_json_encode( $data ) ); ?>"></button>
				<?php endif; ?>
			</article>
		</li>
		<?php
	}

	/**
	 * The video window (moved to <body> by the script; one per widget, so its style controls reach it).
	 *
	 * @param string $id Widget id.
	 */
	private function render_modal( $id ) {
		$labels = array(
			'play'     => 'پخش',
			'pause'    => 'توقف',
			'mute'     => 'بی‌صدا',
			'unmute'   => 'صدادار',
			'full'     => 'تمام‌صفحه',
			'exitFull' => 'خروج از تمام‌صفحه',
			'pip'      => 'تصویر در تصویر',
			'speed'    => 'سرعت پخش',
			'seek'     => 'پیشرفت ویدیو',
			'volume'   => 'بلندی صدا',
			'close'    => 'بستن',
			'prev'     => 'ویدیوی قبلی',
			'next'     => 'ویدیوی بعدی',
			'replay'   => 'پخش دوباره',
			'error'    => 'پخش این ویدیو ممکن نشد.',
		);
		?>
		<div class="mdotcar-vmodal" data-for="<?php echo esc_attr( $id ); ?>" data-labels="<?php echo esc_attr( wp_json_encode( $labels ) ); ?>" hidden>
			<div class="mdotcar-vmodal__scrim" data-close></div>
			<div class="mdotcar-vmodal__dialog" role="dialog" aria-modal="true" aria-labelledby="mdotcar-vm-title-<?php echo esc_attr( $id ); ?>" tabindex="-1">
				<button type="button" class="mdotcar-vmodal__close" data-close aria-label="<?php echo esc_attr( $labels['close'] ); ?>"><?php echo self::svg( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></button>
				<div class="mdotcar-vmodal__stage"></div>
				<div class="mdotcar-vmodal__info">
					<div class="mdotcar-vmodal__text">
						<span class="mdotcar-vmodal__badge"></span>
						<h2 class="mdotcar-vmodal__title" id="mdotcar-vm-title-<?php echo esc_attr( $id ); ?>"></h2>
						<p class="mdotcar-vmodal__desc"></p>
					</div>
					<div class="mdotcar-vmodal__nav">
						<button type="button" class="mdotcar-vmodal__step" data-step="-1" aria-label="<?php echo esc_attr( $labels['prev'] ); ?>"><?php echo self::svg( 'prev' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></button>
						<span class="mdotcar-vmodal__count" aria-live="polite"></span>
						<button type="button" class="mdotcar-vmodal__step" data-step="1" aria-label="<?php echo esc_attr( $labels['next'] ); ?>"><?php echo self::svg( 'next' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static markup. ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * The widget's icons (currentColor). The arrows point to the reading direction's ends; CSS flips them for LTR.
	 *
	 * @param string $name play, prev, next or close.
	 * @return string
	 */
	private static function svg( $name ) {
		$shapes = array(
			'play'  => '<path d="M8 5.14v13.72a1 1 0 0 0 1.52.85l10.6-6.86a1 1 0 0 0 0-1.7L9.52 4.29A1 1 0 0 0 8 5.14Z" fill="currentColor"/>',
			'prev'  => '<path d="M14.43 5.93 20.5 12l-6.07 6.07M3.5 12h16.83" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>',
			'next'  => '<path d="M9.57 5.93 3.5 12l6.07 6.07M20.5 12H3.67" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>',
			'close' => '<path d="M6 6l12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
		);
		return '<svg class="mdotcar-vgal__svg mdotcar-vgal__svg--' . $name . '" width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">' . $shapes[ $name ] . '</svg>';
	}
}
