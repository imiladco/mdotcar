<?php
/**
 * MDotCar Mobile Menu widget.
 *
 * @package MDotCar\Elementor
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

/**
 * The menu as the drawer shows it: rows with an icon and an arrow, sub items
 * in an accordion along a guide line with a dot per item, and buttons below.
 *
 * Made to sit in the saved template the Header Menu's drawer opens, beside the
 * logo and the search, but it works on any page. A row is a top item or a sub
 * item of the top item above it; a top item with sub items opens and closes
 * its list (assets/js/mdotcar-elementor.js) instead of following a link.
 */
class MDotCar_Elementor_Widget_Mobile_Menu extends Widget_Base {

	/** Root of the widget's markup. */
	const ROOT = '{{WRAPPER}} .mdotcar-mmenu';

	/**
	 * Widget key used in templates and the panel.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mdotcar-mobile-menu';
	}

	/**
	 * Label shown in the Elementor panel.
	 *
	 * @return string
	 */
	public function get_title() {
		return __( 'MDotCar Mobile Menu', 'mdotcar-elementor' );
	}

	/**
	 * Icon shown next to the widget in the panel.
	 *
	 * @return string
	 */
	public function get_icon() {
		return 'eicon-menu-bar';
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
		return array( 'menu', 'mobile', 'drawer', 'accordion', 'mdotcar', 'منو', 'موبایل' );
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
		$this->register_items_controls();
		$this->register_buttons_controls();
		$this->register_row_style_controls();
		$this->register_sub_style_controls();
		$this->register_button_style_controls();
	}

	/*
	 * Content
	 */

	/**
	 * Content tab: the items.
	 */
	private function register_items_controls() {
		$this->start_controls_section(
			'section_items',
			array(
				'label' => __( 'Menu Items', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'level',
			array(
				'label'       => __( 'Level', 'mdotcar-elementor' ),
				'description' => __( 'A sub item belongs to the nearest top item above it.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => 'top',
				'options'     => array(
					'top' => __( 'Top item', 'mdotcar-elementor' ),
					'sub' => __( 'Sub item', 'mdotcar-elementor' ),
				),
			)
		);

		$repeater->add_control(
			'label',
			array(
				'label'       => __( 'Label', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Item', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'       => __( 'Link', 'mdotcar-elementor' ),
				'description' => __( 'A top item with sub items opens them instead.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => array( 'active' => true ),
				'placeholder' => '/pricing/',
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'     => __( 'Icon', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::MEDIA,
				'dynamic'   => array( 'active' => true ),
				'condition' => array( 'level' => 'top' ),
			)
		);

		$repeater->add_control(
			'icon_active',
			array(
				'label'       => __( 'Icon When Open or Current', 'mdotcar-elementor' ),
				'description' => __( 'Optional: a coloured version, shown while its sub items are open or it is the current page.', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::MEDIA,
				'dynamic'     => array( 'active' => true ),
				'condition'   => array( 'level' => 'top' ),
			)
		);

		$repeater->add_control(
			'soon',
			array(
				'label'        => __( 'Coming Soon', 'mdotcar-elementor' ),
				'description'  => __( 'Shown faded with the "coming soon" note, and not a link.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'items',
			array(
				'label'       => __( 'Items', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => self::default_items(),
				'title_field' => '{{{ "sub" === level ? "↳ " : "" }}}{{{ label }}}',
			)
		);

		$this->add_control(
			'open_current',
			array(
				'label'        => __( 'Open on the Current Page', 'mdotcar-elementor' ),
				'description'  => __( 'Sub items start open when one of them is the page being viewed.', 'mdotcar-elementor' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => 'yes',
				'return_value' => 'yes',
			)
		);

		$this->add_control(
			'soon_text',
			array(
				'label'   => __( 'Coming Soon Note', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '(به زودی)',
			)
		);

		$this->add_control(
			'nav_label',
			array(
				'label'   => __( 'Menu Name for Screen Readers', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => 'منوی اصلی',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab: the buttons under the menu.
	 */
	private function register_buttons_controls() {
		$this->start_controls_section(
			'section_buttons',
			array(
				'label' => __( 'Buttons', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'text',
			array(
				'label'       => __( 'Text', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => array( 'active' => true ),
				'default'     => __( 'Button', 'mdotcar-elementor' ),
				'label_block' => true,
			)
		);

		$repeater->add_control(
			'link',
			array(
				'label'   => __( 'Link', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::URL,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'icon',
			array(
				'label'   => __( 'Icon', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => array( 'active' => true ),
			)
		);

		$repeater->add_control(
			'style',
			array(
				'label'   => __( 'Style', 'mdotcar-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'solid',
				'options' => array(
					'solid'   => __( 'Filled', 'mdotcar-elementor' ),
					'outline' => __( 'Outline', 'mdotcar-elementor' ),
				),
			)
		);

		$this->add_control(
			'buttons',
			array(
				'label'       => __( 'Buttons', 'mdotcar-elementor' ),
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'default'     => array(
					array(
						'text'  => 'دانلود اپلیکیشن',
						'link'  => array(
							'url'         => 'https://cafebazaar.ir/app/com.mdotcar.client',
							'is_external' => 'on',
						),
						'icon'  => array( 'url' => '/wp-content/uploads/2024/07/note-2.svg' ),
						'style' => 'solid',
					),
					array(
						'text'  => 'ورود | ثبت‌نام',
						'link'  => array( 'url' => '/client/' ),
						'icon'  => array( 'url' => '/wp-content/uploads/2024/07/login.svg' ),
						'style' => 'outline',
					),
				),
				'title_field' => '{{{ text }}}',
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Style
	 */

	/**
	 * Style tab: the rows.
	 */
	private function register_row_style_controls() {
		$this->start_controls_section(
			'section_style_rows',
			array(
				'label' => __( 'Rows', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'row_typography',
				'selector' => self::ROOT . ' .mdotcar-mmenu__link',
			)
		);

		$this->add_responsive_control(
			'row_height',
			array(
				'label'      => __( 'Row Height', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 28,
						'max' => 80,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-mm-row: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'row_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_active_color',
			array(
				'label'     => __( 'Current Page / Open Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-active: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_open_bg',
			array(
				'label'     => __( 'Open Item Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-open-bg: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'row_icon',
			array(
				'label'      => __( 'Icon Size', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 10,
						'max' => 40,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-mm-icon: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'row_arrow',
			array(
				'label'     => __( 'Arrow Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-arrow: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_arrow_open',
			array(
				'label'     => __( 'Open Arrow Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-arrow-open: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'row_separator',
			array(
				'label'     => __( 'Separator Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-sep: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the sub items.
	 */
	private function register_sub_style_controls() {
		$this->start_controls_section(
			'section_style_sub',
			array(
				'label' => __( 'Sub Items', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sub_typography',
				'selector' => self::ROOT . ' .mdotcar-mmenu__sublink',
			)
		);

		$this->add_responsive_control(
			'sub_height',
			array(
				'label'      => __( 'Row Height', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 24,
						'max' => 72,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-mm-sub-row: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'sub_color',
			array(
				'label'     => __( 'Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-sub-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'sub_line',
			array(
				'label'     => __( 'Guide Line Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'sub_dot',
			array(
				'label'     => __( 'Dot Color', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-dot: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab: the buttons.
	 */
	private function register_button_style_controls() {
		$this->start_controls_section(
			'section_style_buttons',
			array(
				'label' => __( 'Buttons', 'mdotcar-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'selector' => self::ROOT . ' .mdotcar-mmenu__btn',
			)
		);

		$this->add_responsive_control(
			'button_height',
			array(
				'label'      => __( 'Height', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 32,
						'max' => 72,
					),
				),
				'selectors'  => array( self::ROOT => '--mdotcar-mm-btn-h: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_responsive_control(
			'button_radius',
			array(
				'label'      => __( 'Radius', 'mdotcar-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'max' => 32 ) ),
				'selectors'  => array( self::ROOT => '--mdotcar-mm-btn-r: {{SIZE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'button_solid_bg',
			array(
				'label'     => __( 'Filled: Background', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( self::ROOT => '--mdotcar-mm-btn-solid: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_solid_color',
			array(
				'label'     => __( 'Filled: Text', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-btn-solid-text: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_outline_border',
			array(
				'label'     => __( 'Outline: Border', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( self::ROOT => '--mdotcar-mm-btn-line: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'button_outline_color',
			array(
				'label'     => __( 'Outline: Text', 'mdotcar-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( self::ROOT => '--mdotcar-mm-btn-line-text: {{VALUE}};' ),
			)
		);

		$this->end_controls_section();
	}

	/*
	 * Data
	 */

	/**
	 * The drawer menu mdotcar.com had when the widget was introduced.
	 *
	 * @return array[]
	 */
	private static function default_items() {
		$up   = '/wp-content/uploads/';
		$item = static function ( $level, $label, $link, $icon = '', $active = '' ) use ( $up ) {
			return array(
				'level'       => $level,
				'label'       => $label,
				'link'        => array( 'url' => $link ),
				'icon'        => array( 'url' => $icon ? $up . $icon : '' ),
				'icon_active' => array( 'url' => $active ? $up . $active : '' ),
			);
		};

		return array(
			$item( 'top', 'صفحه اصلی', '/', '2024/07/home-2.svg' ),
			$item( 'top', 'خدمات', '', '2024/07/reserve.svg', '2024/09/reserve.svg' ),
			$item( 'sub', 'امداد خودرو', '/car-assistance/' ),
			$item( 'sub', 'تعویض روغن', '/oil-change-on-site/' ),
			$item( 'sub', 'یدک‌کش و حمل خودرو', '/car-shipping/' ),
			$item( 'sub', 'شبکه تعمیرگاهی', '/repair-shops/' ),
			$item( 'sub', 'کارواش سیار', '/carwash/' ),
			$item( 'sub', 'چک قبل از سفر', '/service-check-before-trip/' ),
			$item( 'sub', 'عیب‌یابی خودرو', '/check-light/' ),
			$item( 'sub', 'استعلام و پرداخت خودرویی', '/vehicle-services/' ),
			$item( 'top', 'تعرفه‌ها', '/pricing/', '2024/07/tag.svg' ),
			$item( 'top', 'دانستنی‌ها', '/blog/', '2024/07/document-copy-1.svg' ),
			$item( 'top', 'درباره ما', '/aboutus/', '2024/07/bubble.svg' ),
			$item( 'top', 'همکاری با امداتکار', '/attracting-rescuers/', '2024/07/emoji-normal.svg' ),
		);
	}

	/**
	 * Rows grouped as top items with their sub items.
	 *
	 * @param array $rows Repeater rows.
	 * @return array[] Each: the top row plus 'children'.
	 */
	private static function tree( array $rows ) {
		$tree = array();
		foreach ( $rows as $row ) {
			if ( 'sub' === ( isset( $row['level'] ) ? $row['level'] : 'top' ) ) {
				if ( $tree ) {
					$tree[ count( $tree ) - 1 ]['children'][] = $row;
				}
				continue; // A sub item before any top item has nowhere to go.
			}
			$row['children'] = array();
			$tree[]          = $row;
		}
		return $tree;
	}

	/*
	 * Render
	 */

	/**
	 * Renders the widget.
	 */
	protected function render() {
		MDotCar_Elementor_Plugin::enqueue();

		$s    = $this->get_settings_for_display();
		$rows = isset( $s['items'] ) && is_array( $s['items'] ) ? $s['items'] : array();
		$id   = $this->get_id();
		?>
		<nav class="mdotcar-mmenu" aria-label="<?php echo esc_attr( $s['nav_label'] ); ?>">
			<ul class="mdotcar-mmenu__list">
				<?php
				foreach ( self::tree( $rows ) as $n => $top ) {
					$this->render_row( $top, $s, $id . '-' . $n );
				}
				?>
			</ul>
			<?php if ( ! empty( $s['buttons'] ) ) : ?>
				<div class="mdotcar-mmenu__buttons">
					<?php
					foreach ( $s['buttons'] as $button ) {
						$this->render_button( $button );
					}
					?>
				</div>
			<?php endif; ?>
		</nav>
		<?php
	}

	/**
	 * One row; with sub items, an accordion.
	 *
	 * @param array  $top Top item with children.
	 * @param array  $s   Settings.
	 * @param string $key Unique key for ids.
	 */
	private function render_row( array $top, array $s, $key ) {
		$kids    = $top['children'];
		$current = MDotCar_Elementor_Plugin::is_current_url( $top['link'] );
		$inside  = false;
		foreach ( $kids as $kid ) {
			$inside = $inside || MDotCar_Elementor_Plugin::is_current_url( $kid['link'] );
		}
		$open    = $kids && $inside && 'yes' === $s['open_current'];
		$soon    = 'yes' === $top['soon'];
		$classes = array( 'mdotcar-mmenu__row' );
		if ( $kids ) {
			$classes[] = 'has-sub';
		}
		if ( $current || $inside ) {
			$classes[] = 'is-current';
		}
		if ( $open ) {
			$classes[] = 'is-open';
		}

		$inner  = '<span class="mdotcar-mmenu__icon" aria-hidden="true">';
		$inner .= ! empty( $top['icon']['url'] ) ? '<img class="mdotcar-mmenu__img" src="' . esc_url( $top['icon']['url'] ) . '" alt="">' : '';
		$inner .= ! empty( $top['icon_active']['url'] ) ? '<img class="mdotcar-mmenu__img mdotcar-mmenu__img--active" src="' . esc_url( $top['icon_active']['url'] ) . '" alt="">' : '';
		$inner .= '</span><span class="mdotcar-mmenu__label">' . esc_html( $top['label'] );
		$inner .= $soon ? ' <span class="mdotcar-mmenu__soon">' . esc_html( $s['soon_text'] ) . '</span>' : '';
		$inner .= '</span>' . self::svg_arrow();
		if ( ! empty( $top['icon_active']['url'] ) ) {
			$classes[] = 'has-active-icon';
		}
		?>
		<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
			<?php if ( $kids ) : ?>
				<button type="button" class="mdotcar-mmenu__link" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>" aria-controls="mdotcar-mm-<?php echo esc_attr( $key ); ?>">
					<?php echo $inner; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above. ?>
				</button>
				<div class="mdotcar-mmenu__sub" id="mdotcar-mm-<?php echo esc_attr( $key ); ?>">
					<ul class="mdotcar-mmenu__sublist">
						<?php foreach ( $kids as $kid ) : ?>
							<li>
								<?php
								$this->render_link(
									$kid,
									'mdotcar-mmenu__sublink',
									esc_html( $kid['label'] ) . ( 'yes' === $kid['soon'] ? ' <span class="mdotcar-mmenu__soon">' . esc_html( $s['soon_text'] ) . '</span>' : '' ),
									MDotCar_Elementor_Plugin::is_current_url( $kid['link'] )
								);
								?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php else : ?>
				<?php $this->render_link( $top, 'mdotcar-mmenu__link', $inner, $current ); ?>
			<?php endif; ?>
		</li>
		<?php
	}

	/**
	 * A link, or a span when it is coming soon or has no URL.
	 *
	 * @param array  $row       Item.
	 * @param string $css_class Class.
	 * @param string $inner     Escaped markup.
	 * @param bool   $current   Whether it is the current page.
	 */
	private function render_link( array $row, $css_class, $inner, $current ) {
		$soon = 'yes' === $row['soon'];
		if ( $soon || empty( $row['link']['url'] ) ) {
			echo '<span class="' . esc_attr( $css_class . ( $soon ? ' is-soon' : '' ) ) . '"' . ( $soon ? ' aria-disabled="true"' : '' ) . '>' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts.
			return;
		}
		echo '<a class="' . esc_attr( $css_class . ( $current ? ' is-current' : '' ) ) . '"' . self::link_attrs( $row['link'] ) . ( $current ? ' aria-current="page"' : '' ) . '>' . $inner . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts.
	}

	/**
	 * A button under the menu.
	 *
	 * @param array $button Repeater row.
	 */
	private function render_button( array $button ) {
		$style = 'outline' === $button['style'] ? 'outline' : 'solid';
		$inner = ( ! empty( $button['icon']['url'] ) ? '<img src="' . esc_url( $button['icon']['url'] ) . '" alt="" aria-hidden="true">' : '' ) . '<span>' . esc_html( $button['text'] ) . '</span>';
		$class = 'mdotcar-mmenu__btn mdotcar-mmenu__btn--' . $style;
		if ( empty( $button['link']['url'] ) ) {
			echo '<span class="' . esc_attr( $class ) . '">' . $inner . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts.
			return;
		}
		echo '<a class="' . esc_attr( $class ) . '"' . self::link_attrs( $button['link'] ) . '>' . $inner . '</a>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped parts.
	}

	/**
	 * The href, target and rel of a URL control value, escaped, with a leading space.
	 *
	 * @param array $link URL control value.
	 * @return string
	 */
	private static function link_attrs( array $link ) {
		$out = ' href="' . esc_url( $link['url'] ) . '"';
		$rel = array();
		if ( ! empty( $link['is_external'] ) ) {
			$out  .= ' target="_blank"';
			$rel[] = 'noopener';
		}
		if ( ! empty( $link['nofollow'] ) ) {
			$rel[] = 'nofollow';
		}
		if ( $rel ) {
			$out .= ' rel="' . esc_attr( implode( ' ', $rel ) ) . '"';
		}
		return $out;
	}

	/**
	 * The row's arrow (points to the reading end; flipped for LTR in CSS).
	 *
	 * @return string
	 */
	private static function svg_arrow() {
		return '<svg class="mdotcar-mmenu__arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true" focusable="false"><path d="M10 13.28 5.65 8.93a1.32 1.32 0 0 1 0-1.86L10 2.72" stroke="currentColor" stroke-width="1.2" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	}
}
