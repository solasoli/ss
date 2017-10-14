<?php
/**
 * eForm Material UI class
 *
 * It extends and overrides the front UI to print in material style
 *
 * It also overrides some enqueues, especially with JS validation and UI
 * initiator
 */
class EForm_Material_UI extends IPT_Plugin_UIF_Front {
	public static $instance = null;

	public $material_location = null;

	public static function instance( $text_domain = 'default', $classname = null ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $text_domain, $classname );
		}
		return self::$instance;
	}

	public function __construct( $text_domain = 'default', $classname = __CLASS__ ) {
		parent::__construct( $text_domain, $classname );
	}

	public function enqueue( $static_location, $version, $ignore_css = array(), $ignore_js = array() ) {
		global $wp_locale;
		$datetime_l10n = array(
			'closeText'         => __( 'Done', 'ipt_fsqm' ),
			'currentText'       => __( 'Today', 'ipt_fsqm' ),
			'tcurrentText' => __( 'Now', 'ipt_fsqm' ),
			'monthNames'        => array_values( $wp_locale->month ),
			'monthNamesShort'   => array_values( $wp_locale->month_abbrev ),
			'monthStatus'       => __( 'Show a different month', 'ipt_fsqm' ),
			'dayNames'          => array_values( $wp_locale->weekday ),
			'dayNamesShort'     => array_values( $wp_locale->weekday_abbrev ),
			'dayNamesMin'       => array_values( $wp_locale->weekday_initial ),
			// get the start of week from WP general setting
			'firstDay'          => get_option( 'start_of_week' ),
			// is Right to left language? default is false
			'isRTL'             => $wp_locale->is_rtl(),
			'amNames' => array( __( 'AM', 'ipt_fsqm' ), __( 'A', 'ipt_fsqm' ) ),
			'pmNames' => array( __( 'PM', 'ipt_fsqm' ), __( 'P', 'ipt_fsqm' ) ),
			/* translators: Change %s to the time suffix. %s is always replaced by an empty string */
			'timeSuffix' => sprintf( _x( '%s', 'timeSuffix', 'ipt_fsqm' ), '' ),
			'timeOnlyTitle' => __( 'Choose Time', 'ipt_fsqm' ),
			'timeText' => __( 'Time', 'ipt_fsqm' ),
			'hourText' => __( 'Hour', 'ipt_fsqm' ),
			'minuteText' => __( 'Minute', 'ipt_fsqm' ),
			'secondText' => __( 'Second', 'ipt_fsqm' ),
			'millisecText' => __( 'Millisecond', 'ipt_fsqm' ),
			'microsecText' => __( 'Microsecond', 'ipt_fsqm' ),
			'timezoneText' => __( 'Timezone', 'ipt_fsqm' ),
		);

		$static_location = trailingslashit( $static_location );
		$this->static_location = $static_location;
		$this->version = $version;
		$this->material_location = trailingslashit( plugins_url( '/material/', IPT_FSQM_Loader::$abs_file ) );
		$material_location = $this->material_location;

		// Styles
		$styles = array(
			'ipt-plugin-uif-jquery-icon' => array( $static_location . '/css/jquery.iconfont/jquery-ui.icon-font.css', array() ),
			'ipt-icomoon-fonts' => array( $static_location . 'fonts/icomoon.css', array() ),
			// 'ipt-plugin-uif-validation-engine-css' => array( $static_location . 'css/validationEngine.jquery.css', array() ),
			'ipt-plugin-uif-animate-css' => array( $static_location . 'css/animate.css', array() ),
			'ipt-js-tooltipster' => array( $static_location . 'css/tooltipster.bundle.min.css', array() ),
			'ipt-eform-material-jquery-ui-structure' => array( $material_location . 'jquery-ui/jquery-ui.structure.css', array() ),
		);
		foreach ( $styles as $style_id => $style_prop ) {
			if ( ! in_array( $style_id, $ignore_css ) ) {
				if ( empty( $style_prop ) ) {
					wp_enqueue_style( $style_id );
				} else {
					wp_enqueue_style( $style_id, $style_prop[0], $style_prop[1], $version );
				}
			}
		}

		//Scripts
		$scripts = array(
			'jquery-timepicker-addon' => array( $static_location . 'js/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker' ) ),
			'jquery-print-element' => array( $static_location . 'js/jquery.printElement.min.js', array( 'jquery' ) ),
			'jquery-mwheelIntent' => array( $static_location . 'js/mwheelIntent.js', array( 'jquery' ) ),
			'jquery-mousewheel' => array( $static_location . 'js/jquery.mousewheel.js', array( 'jquery' ) ),
			// 'jquery-serializejson' => array( $static_location . 'js/jquery.serializejson.min.js', array( 'jquery' ) ),
			'jquery-json' => array( $static_location . 'js/jquery.json.min.js', array( 'jquery' ) ),
			'jquery-ui-autocomplete' => array(),
			'ipt-plugin-uif-keyboard' => array( $static_location . 'js/jquery.keyboard.min.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget' ) ),
			'ipt-plugin-uif-validation-engine' => array( $static_location . 'js/jquery.validationEngine' . self::$js_suffix . '.js', array( 'jquery' ) ),
			'ipt-plugin-uif-validation-engine-lang' => array( $static_location . 'js/jquery.validationEngine-all' . self::$js_suffix . '.js', array( 'jquery', 'ipt-plugin-uif-validation-engine' ) ),
			'ipt-plugin-uif-nivo-slider' => array( $static_location . 'js/jquery.nivo.slider.pack.js', array( 'jquery' ) ),
			'ipt-plugin-uif-typewatch' => array( $static_location . 'js/jquery.typewatch.js', array( 'jquery' ) ),
			'waypoints' => array( $static_location . 'js/jquery.waypoints.min.js', array( 'jquery' ) ),
			'count-up' => array( $static_location . 'js/countUp.min.js', array() ),
			'jquery-tooltipster' => array( $static_location . 'js/tooltipster.bundle.min.js', array( 'jquery' ) ),
			'ba-throttle-debounce' => array( $static_location . 'js/jquery.ba-throttle-debounce.min.js', array( 'jquery' ) ),
			'ipt-plugin-uif-front-js' => array( $static_location . 'js/jquery.ipt-plugin-uif-front' . self::$js_suffix . '.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-button', 'jquery-touch-punch', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-spinner', 'jquery-ui-progressbar', 'jquery-ui-selectmenu', 'jquery-timepicker-addon', 'jquery-print-element', 'jquery-mwheelIntent', 'jquery-mousewheel', 'jquery-ui-autocomplete', 'ipt-plugin-uif-keyboard', 'ipt-plugin-uif-validation-engine', 'ipt-plugin-uif-validation-engine-lang', 'ipt-plugin-uif-nivo-slider', 'ipt-plugin-uif-typewatch', 'waypoints', 'count-up', 'jquery-tooltipster', 'ba-throttle-debounce' ) ),
			'eform-material-waves' => array( plugins_url( '/material/js/waves.min.js', IPT_FSQM_Loader::$abs_file ), array() ),
			'eform-material-js' => array( plugins_url( '/material/js/jquery.eform-material.min.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery', 'eform-material-waves', 'ipt-plugin-uif-front-js' ) ),
		);
		$scripts_localize = array(
			'jquery-timepicker-addon' => array(
				'object_name' => 'iptPluginUIFDTPL10n',
				'l10n' => $datetime_l10n,
			),
			'ipt-plugin-uif-validation-engine-lang' => array(
				'object_name' => 'iptPluginValidationEn',
				'l10n' => array(
					'L10n' => $this->default_messages['validationEngine'],
				),
			),
			'ipt-plugin-uif-front-js' => array(
				'object_name' => 'iptPluginUIFFront',
				'l10n' => array(
					'location' => $static_location,
					'version' => $version,
					'L10n' => $this->default_messages,
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				),
			),
		);
		foreach ( $scripts as $script_id => $script_prop ) {
			if ( ! in_array( $script_id, $ignore_js ) ) {
				if ( empty( $script_prop ) ) {
					wp_enqueue_script( $script_id );
				} else {
					wp_enqueue_script( $script_id, $script_prop[0], $script_prop[1], $version, true );
				}
				if ( isset( $scripts_localize[$script_id] ) && is_array( $scripts_localize[$script_id] ) && isset( $scripts_localize[$script_id]['object_name'] ) && isset( $scripts_localize[$script_id]['l10n'] ) ) {
					wp_localize_script( $script_id, $scripts_localize[$script_id]['object_name'], $scripts_localize[$script_id]['l10n'] );
				}
			}
		}
		// Add RTL
		if ( is_rtl() ) {
			wp_enqueue_style( 'eform-material-rtl', plugins_url( '/material/css/material-rtl.css', IPT_FSQM_Loader::$abs_file ), array(), IPT_FSQM_Loader::$version );
		}
		do_action( 'ipt_eform_material_enqueue', $this );
	}

	/*==========================================================================
	 * UI Elements
	 *========================================================================*/
	/**
	 * Create jQuery UI Tabs
	 *
	 * @param      array    $tabs      Associative array of tabs
	 * @param      array    $data      HTML data attributes
	 * @param      boolean  $vertical  Whether to print vertical tab ( not used
	 *                                 )
	 * @param      array    $classes   Additional classes
	 */
	public function tabs( $tabs, $data = array(), $vertical = false, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_tabs';
		$data_attr = $this->convert_data_attributes( $data );
		$classes[] = ( $vertical == true ) ? 'vertical' : 'horizontal';
?>
<div<?php echo $data_attr; ?> class="<?php echo implode( ' ', $classes ); ?>">
	<div class="ipt-eform-tab-nav-wrap">
		<ul>
			<?php foreach ( $tabs as $tab ) : ?>
			<?php $tab = wp_parse_args( $tab, array(
				'id' => '',
				'label' => '',
				'sublabel' => '',
				'callback' => '',
				'icon' => 'none',
				'classes' => array(),
			) ); ?>
			<li id="<?php echo $tab['id'] . '_control_li'; ?>"><a class="eform-ripple" href="#<?php echo $tab['id']; ?>"><?php $this->print_icon( $tab['icon'], false ); ?><span class="eform-tab-labels"><?php echo $tab['label']; ?> <?php if ( ! empty( $tab['sublabel'] ) ) echo '<span class="ipt_uif_tab_subtitle">' . $tab['sublabel'] . '</span>'; ?></span></a></li>
			<?php endforeach; ?>
		</ul>
		<i class="eform-tab-nav eform-tab-nav-right ipt-icomoon-angle-right disabled eform-ripple"></i>
		<i class="eform-tab-nav eform-tab-nav-left ipt-icomoon-angle-left disabled eform-ripple"></i>
		<span class="eform-tab-passive-notifier"></span>
		<span class="eform-tab-active-notifier"></span>
	</div>

	<?php foreach ( $tabs as $tab ) : ?>
	<?php
		$tab = wp_parse_args( $tab, array(
			'id' => '',
			'label' => '',
			'callback' => '',
			'icon' => 'none',
			'classes' => array(),
		) );

		if ( !$this->check_callback( $tab['callback'] ) ) {
			$tab['callback'] = array(
				array( $this, 'msg_error' ), 'Invalid Callback',
			);
		}
		$tab['callback'][1][] = $tab;
		$tab_classes = isset( $tab['classes'] ) && is_array( $tab['classes'] ) ? $tab['classes'] : array();
?>
	<div id="<?php echo $tab['id']; ?>" class="<?php echo implode( ' ', $tab_classes ); ?>">
		<?php call_user_func_array( $tab['callback'][0], $tab['callback'][1] ); ?>
		<?php $this->clear(); ?>
	</div>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
		<?php
	}

	public function buttons( $buttons, $container_id = '', $container_classes = '' ) {
		if ( ! is_array( $buttons ) || empty( $buttons ) ) {
			$this->msg_error( 'Please pass a valid arrays to the <code>EForm_Material_UI::buttons</code> method' );
			return;
		}

		$id_attr = '';
		if ( '' != trim( $container_id ) ) {
			$id_attr = ' id="' . esc_attr( trim( $container_id ) ) . '"';
		}

		if ( !is_array( $container_classes ) ) {
			$container_classes = (array) $container_classes;
		}
		$container_classes[] = 'ipt-eform-material-button-container';

		echo "\n" . '<div' . $id_attr . ' class="' . implode( ' ', $container_classes ) . '"><div class="eform-button-container-inner">' . "\n";

		foreach ( $buttons as $button_index ) {
			$button_index = array_values( $button_index );
			$button = array();
			foreach ( array( 'text', 'name', 'size', 'style', 'state', 'classes', 'type', 'data', 'atts', 'url', 'icon', 'icon_position' ) as $b_key => $b_val ) {
				if ( isset( $button_index[$b_key] ) ) {
					$button[$b_val] = $button_index[$b_key];
				}
			}
			if ( !isset( $button['text'] ) || '' == trim( $button['text'] ) ) {
				continue;
			}
			$text = $button['text'];
			$name = isset( $button['name'] ) ? $button['name'] : '';
			$size = isset( $button['size'] ) ? $button['size'] : 'medium';
			$style = isset( $button['style'] ) ? $button['style'] : 'primary';
			$state = isset( $button['state'] ) ? $button['state'] : 'normal';
			$classes = isset( $button['classes'] ) ? $button['classes'] : array();
			$type = isset( $button['type'] ) ? $button['type'] : 'button';
			$data = isset( $button['data'] ) ? $button['data'] : array();
			$atts = isset( $button['atts'] ) ? $button['atts'] : array();
			$url = isset( $button['url'] ) ? $button['url'] : '';
			$icon = isset( $button['icon'] ) ? $button['icon'] : '';
			$icon_position = isset( $button['icon_position'] ) ? $button['icon_position'] : 'before';

			$this->button( $text, $name, $size, $style, $state, $classes, $type, false, $data, $atts, $url, $icon, $icon_position );
		}

		echo "\n" . '</div></div>' . "\n";
	}

	/**
	 * Generates a single button
	 *
	 * @param      string  $text           The text of the button
	 * @param      string  $name           HTML name. ID is generated
	 *                                     automatically (unless name is an
	 *                                     array, ID is identical to name).
	 * @param      string  $size           Size large|medium|small
	 * @param      string  $style          Style primary|ui
	 * @param      string  $state          HTML state normal|readonly|disabled
	 * @param      array   $classes        Array of additional classes
	 * @param      string  $type           The HTML type of the button
	 *                                     button|submit|reset|anchor
	 * @param      bool    $container      Whether or not to print the
	 *                                     container.
	 * @param      array   $data           HTML5 data attributes
	 * @param      array   $atts           The atts
	 * @param      string  $url            The url
	 * @param      string  $icon           The icon
	 * @param      string  $icon_position  The icon position
	 */
	public function button( $text, $name = '', $size = 'medium', $style = 'primary', $state = 'normal', $classes = array(), $type = 'button', $container = true, $data = array(), $atts = array(), $url = '', $icon = '', $icon_position = 'before' ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		switch ( $size ) {
		case 'large' :
		case 'medium' :
		case 'small' :
		case 'auto' :
			$classes[] = $size;
			break;
		default :
			$classes[] = 'medium';
		}
		switch ( $style ) {
		default :
		case 'primary' :
		case '0' :
			$classes[] = 'primary-button';
			break;
		case 'secondary' :
		case '1' :
			$classes[] = 'secondary-button';
			break;
		case 'ui' :
		case '2' :
			$classes[] = 'ipt-ui-button';
			break;
		}
		$name_id_attr = '';
		if ( '' != trim( $name ) ) {
			$name_id_attr = ' name="' . esc_attr( trim( $name ) ) . '" id="' . $this->generate_id_from_name( $name ) . '"';
		}
		$state_attr = $this->convert_state_to_attribute( $state );

		$type_attr = '';
		if ( '' != trim( $type ) && $type != 'anchor' ) {
			$type_attr = ' type="' . esc_attr( trim( $type ) ) . '"';
		} else {
			$type_attr = ' href="' . esc_url( $url ) . '"';
		}

		$data_attr = '';
		if ( is_array( $data ) ) {
			$data_attr = $this->convert_data_attributes( $data );
		}
		$tag = $type == 'anchor' ? 'a' : 'button';

		$icon_span = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_span .= '<i class="ipticm';
			if ( is_numeric( $icon ) ) {
				$icon_span .= '" data-ipt-icomoon="' . '&#x' . dechex( $icon ) . '">';
			} else {
				$icon_span .= ' ipt-icomoon-' . $icon . '">';
			}
			$icon_span .= '</i>';
		}
		if ( $icon_position == 'before' ) {
			$text = $icon_span . ' ' . $text;
		} else {
			$text .= ' ' . $icon_span;
		}

		$html_atts = '';
		if ( ! empty( $atts ) ) {
			$html_atts = $this->convert_html_attributes( $atts );
		}
?>
<?php if ( true == $container ) : ?>
<div class="ipt-eform-material-button-container"><div class="eform-button-container-inner">
<?php endif; ?>
	<<?php echo $tag; ?><?php echo $type_attr . $data_attr . $html_atts; ?> class="ipt_uif_button eform-material-button eform-ripple <?php echo implode( ' ', $classes ); ?>"<?php echo $name_id_attr . $state_attr; ?>><?php echo $text; ?></<?php echo $tag; ?>>
<?php if ( true == $container ) : ?>
</div></div>
<?php endif; ?>
		<?php
	}

	/**
	 * Buttons and Icons
	 *
	 * @param      array  $items      Associative array of items
	 * @param      string  $alignment  Button alignment
	 */
	public function iconmenu( $items, $alignment = 'center', $open = 'self', $dimension = array() ) {
		$alignment = 'align-' . $alignment;
		?>
<div class="ipt-eform-material-button-container <?php echo $alignment; ?>"><div class="eform-button-container-inner">
	<?php foreach ( $items as $item ) :
		$href = ( '' == $item['url'] ? 'javascript:;' : esc_attr( $item['url'] ) );
		$text = trim( $item['text'] );
		$icon = '';
		if ( isset( $item['icon'] ) && $item['icon'] !== '' && $item['icon'] != 'none' ) {
			$icon = '<i ';
			if ( is_numeric( $item['icon'] ) ) {
				$icon .= 'class="ipticm" data-ipt-icomoon="&#x' . dechex( $item['icon'] ) . ';"';
			} else {
				$icon .= 'class="ipticm ipt-icomoon-' . esc_attr( $item['icon'] ) . '"';
			}
			$icon .= '></i>';
		}

		$attr = array();
		$attr['class'] = 'ipt_uif_button eform-material-button eform-ripple secondary-button';

		if ( 'popup' == $open ) {
			$attr['class'] .= ' eform-icmpopup';
			$attr['data-height'] = $dimension['height'];
			$attr['data-width'] =  $dimension['width'];
		} else if ( 'blank' == $open ) {
			$attr['target'] = '_blank';
		}
	?>
	<a href="<?php echo $href; ?>" <?php echo $this->convert_html_attributes( $attr ); ?>>
		<?php echo $icon; ?>
		<?php echo $text; ?>
	</a>
	<?php endforeach; ?>
</div></div>
		<?php
	}

	/**
	 * Datetime maker
	 *
	 * @param      string   $name         The name
	 * @param      string   $value        The value
	 * @param      string   $type         The type
	 * @param      string   $state        The state
	 * @param      array    $classes      The classes
	 * @param      boolean  $validation   The validation
	 * @param      string   $date_format  The date format
	 * @param      string   $time_format  The time format
	 * @param      string   $placeholder  The placeholder
	 * @param      array    $data_attr    The data attribute
	 */
	public function datetime( $name, $value, $type = 'date', $state = 'normal', $classes = array(), $validation = false, $date_format = 'yy-mm-dd', $time_format = 'HH:mm:ss', $placeholder = '', $data_attr = array(), $hide_icon = false ) {
		wp_enqueue_script( 'jquery-ui-slider' );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'datepicker';
		$icon = 'calendar';

		switch ( $type ) {
		case 'date' :
			$classes[] = 'ipt_uif_datepicker';
			break;
		case 'time' :
			$classes[] = 'ipt_uif_timepicker';
			$icon = 'clock';
			break;
		case 'datetime' :
			$classes[] = 'ipt_uif_datetimepicker';
			break;
		}

		$data = array(
			'dateFormat' => $date_format,
			'timeFormat' => $time_format,
		);

		$data = array_merge( $data, $data_attr );

		$id = $this->generate_id_from_name( $name );
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		if ( '' == $value ) {
			$classes[] = 'is-empty';
		}

		$data_attr_html = $this->convert_data_attributes( $data );
		$maxlength = '';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = $validation['filters']['maxSize'];
		}
		$wrapper_class = array( 'input-field', 'eform-dp-input-field' );
		if ( ! $hide_icon ) {
			$wrapper_class[] = 'has-icon';
		}
?>
<div class="<?php echo implode( ' ', $wrapper_class ); ?>">
	<?php if ( ! $hide_icon ) : ?>
		<?php $this->print_icon_by_class( $icon ); ?>
	<?php endif; ?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr_html; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="text"
	readonly="readonly"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	maxlength="<?php echo esc_attr( $maxlength ); ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
	<?php if ( '' != $placeholder ) : ?>
		<label for="<?php echo $id ?>"><?php echo $placeholder; ?></label>
	<?php endif; ?>
	<a href="javascript:;" class="eform-dp-clear" title="<?php echo __( 'Clear', 'ipt_fsqm' ); ?>">&times;</a>
</div>
		<?php
	}

	/*==========================================================================
	 * Form Elements
	 *========================================================================*/

	/**
	 * Create a guest blogging element
	 *
	 * This can have WYSIWYG element as well as a list of taxonomies
	 *
	 * @param      string                $name_prefix    The name prefix
	 * @param      mixed(boolean|array)  $trumbowyg      Trumbowyg settings
	 *                                                   which would passed
	 *                                                   directly to the widget.
	 *                                                   Pass false to disable
	 *                                                   WYSIWYG editor
	 * @param      mixed(boolean|array)  $taxonomy_list  The taxonomy list. Pass
	 *                                                   an array of taxonomies
	 *                                                   or false to disable
	 */
	public function guest_blog( $name_prefix, $value, $placeholder, $trumbowyg = array(), $post_title = '', $post_title_label = '', $taxonomy_list = false, $taxonomy_single_list = array(), $taxonomy_required_list = array(), $tax_values = array(), $bio = false, $bio_title = '', $bio_value = '' ) {
		// Enqueue
		if ( false !== $trumbowyg ) {
			wp_enqueue_style( 'trumbowyg', $this->static_location . 'css/trumbowyg.min.css', array(), $this->version );
			wp_enqueue_script( 'trumbowyg-js', $this->static_location . 'js/trumbowyg.min.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'trumbowyg-cleanpaste', $this->static_location . 'js/trumbowyg.cleanpaste.min.js', array( 'trumbowyg-js' ), $this->version, true );
			wp_enqueue_script( 'trumbowyg-table', $this->static_location . 'js/trumbowyg.table.min.js', array( 'trumbowyg-js' ), $this->version, true );
		}

		$classes = array( 'ipt-eform-guestpost' );
		$trum_data = array();
		if ( false !== $trumbowyg ) {
			$classes[] = 'ipt-eform-trumbowyg';
			$trum_data['ef-trum'] = json_encode( $trumbowyg );
		}

		// Create the title
		$this->text( $name_prefix . '[title]', $post_title, $post_title_label, 'pushpin', 'normal', array(), array( 'required' => true ) );
		$this->clear();

		// First print the textarea

		$this->textarea( $name_prefix . '[value]', $value, $placeholder, 'normal', $classes, false, $trum_data, false, false, true );
		$this->clear();

		// Now calculate the taxonomies and print them one by one
		// inside checkboxes
		if ( false !== $taxonomy_list && is_array( $taxonomy_list ) && ! empty( $taxonomy_list ) ) {
			$total_taxes = count( $taxonomy_list );
			// 1 column, 2 column or 3 column
			if ( $total_taxes == 1 ) {
				$columns = 1;
			} else if ( 0 == $total_taxes % 3 ) {
				$columns = 3;
			} else {
				$columns = 2;
			}
			echo '<div class="ipt-eform-guestpost-tax-column-wrap ipt-eform-guestpost-tax-column-' . $columns . '">';
			foreach ( $taxonomy_list as $taxonomy ) {
				$tax_data = get_taxonomy( $taxonomy );
				$is_tax_single = false;
				$is_tax_required = false;
				$walker_name = $name_prefix . '[taxonomy][' . $taxonomy . '][]';
				if ( in_array( $taxonomy, $taxonomy_single_list ) ) {
					$is_tax_single = true;
				}
				if ( in_array( $taxonomy, $taxonomy_required_list ) ) {
					$is_tax_required = true;
				}

				$args = array(
					'selected_cats' => ( isset( $tax_values[ $taxonomy ] ) ? $tax_values[ $taxonomy ] : false ),
					'walker' => new IPT_eForm_Tax_Checklist( $walker_name, $is_tax_single, $is_tax_required ),
					'taxonomy' => $taxonomy,
					'name' => $walker_name,
					'is_tax_single' => $is_tax_single,
					'is_tax_required' => $is_tax_required,
				);
				echo '<div class="ipt-eform-guestpost-tax-wrap">';
				$this->question_container( $name_prefix . '[taxonomy][' . $taxonomy . ']', $tax_data->labels->name, '', array( array( $this, 'terms_checklist' ), array( $args ) ), $is_tax_required, false, true, '', array( 'ipt-eform-guestpost-tax', 'tax-' . $taxonomy ) );
				echo '</div>';
			}
			echo '<div class="clear"></div></div>';
		}

		// Create a bio
		if ( true == $bio ) {
			$this->textarea( $name_prefix . '[bio]', $bio_value, $bio_title, 'normal', array(), array( 'required' => true ), false, false, 'quill' );
		}
	}

	public function terms_checklist( $params = array() ) {
		$defaults = array(
			'descendants_and_self' => 0,
			'selected_cats' => false,
			'popular_cats' => false,
			'walker' => null,
			'taxonomy' => 'category',
			'checked_ontop' => true,
			'echo' => true,
			'name' => '',
			'is_tax_single' => '',
			'is_tax_required' => '',
		);

		$r = wp_parse_args( $params, $defaults );

		if ( empty( $r['walker'] ) || ! ( $r['walker'] instanceof Walker ) ) {
			$walker = new IPT_eForm_Tax_Checklist( '' );
		} else {
			$walker = $r['walker'];
		}

		$taxonomy = $r['taxonomy'];
		$descendants_and_self = (int) $r['descendants_and_self'];

		$args = array( 'taxonomy' => $taxonomy );

		$tax = get_taxonomy( $taxonomy );

		$args['list_only'] = ! empty( $r['list_only'] );

		if ( is_array( $r['selected_cats'] ) ) {
			$args['selected_cats'] = $r['selected_cats'];
		} else {
			$args['selected_cats'] = array();
		}

		if ( $descendants_and_self ) {
			$categories = (array) get_terms( $taxonomy, array(
				'child_of' => $descendants_and_self,
				'hierarchical' => 0,
				'hide_empty' => 0
			) );
			$self = get_term( $descendants_and_self, $taxonomy );
			array_unshift( $categories, $self );
		} else {
			$categories = (array) get_terms( $taxonomy, array( 'get' => 'all' ) );
		}

		wp_deregister_script( 'select2' );
		wp_enqueue_script( 'select2', $this->static_location . 'js/select2.min.js', array( 'jquery' ), $this->version, true );

		$output = '<select name="' . $r['name'] . '" id="' . $this->generate_id_from_name( $r['name'] ) . '"';
		if ( true != $r['is_tax_single'] ) {
			$output .= ' multiple="multiple"';
		}
		$output .= ' class="ipt-eform-guestpost-tax-ul';
		if ( true == $r['is_tax_required'] ) {
			$output .= ' check_me validate[required]';
		}
		$output .= ' ipt_uif_select" data-theme="eform-material" data-placeholder="' . esc_attr__( '-- please select --', 'ipt_fsqm' ) . '" data-allow-clear="true">';

		if ( true == $r['is_tax_single'] ) {
			$output .= '<option value=""' . ( empty( $args['selected_cats'] ) ? ' selected="selected"' : '' ) . '>' . esc_attr__( '-- please select --', 'ipt_fsqm' ) . '</option>';
		}

		if ( $r['checked_ontop'] ) {
			// Post process $categories rather than adding an exclude to the get_terms() query to keep the query the same across all posts (for any query cache)
			$checked_categories = array();
			$keys = array_keys( $categories );

			foreach ( $keys as $k ) {
				if ( in_array( $categories[$k]->term_id, $args['selected_cats'] ) ) {
					$checked_categories[] = $categories[$k];
					unset( $categories[$k] );
				}
			}

			// Put checked cats on top
			$output .= call_user_func_array( array( $walker, 'walk' ), array( $checked_categories, 0, $args ) );
		}
		// Then the rest of them
		$output .= call_user_func_array( array( $walker, 'walk' ), array( $categories, 0, $args ) );
		$output .= '</select>';

		if ( $r['echo'] ) {
			echo $output;
		}

		return $output;
	}

	/**
	 * Generate input type text HTML
	 *
	 * @param      string   $name         HTML name of the text input
	 * @param      string   $value        Initial value of the text input
	 * @param      string   $placeholder  Default placeholder
	 * @param      string   $icon         The icon
	 * @param      string   $state        readonly or disabled state
	 * @param      array    $classes      Array of additional classes
	 * @param      array    $validation   Associative array of all validation
	 *                                    clauses
	 * @param      array    $data         HTML 5 data attributes in associative
	 *                                    array
	 * @param      boolean  $attr         The attribute
	 * @param      string  $size   Size of the text input
	 * @see        IPT_Plugin_UIF_Admin::convert_validation_class
	 * @see        IPT_Plugin_UIF_Admin::convert_data_attributes
	 */
	public function text( $name, $value, $placeholder, $icon = 'pencil', $state = 'normal', $classes = array(), $validation = false, $data = false, $attr = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$maxlength = '';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = $validation['filters']['maxSize'];
		}
		$wrapper_class = array( 'input-field' );
		if ( ! empty( $icon ) && 'none' != $icon ) {
			$wrapper_class[] = 'has-icon';
		}

		$input_type = 'text';

		if ( false == $attr ) {
			$attr = array();
		}

		if ( ! is_array( $attr ) ) {
			$attr = (array) $attr;
		}

		if ( is_array( $validation ) && isset( $validation['filters'] ) && isset( $validation['filters']['type'] ) ) {
			switch ( $validation['filters']['type'] ) {
				case 'number':
				case 'integer':
					$input_type = 'number';
					if ( isset( $validation['filters']['min'] ) ) {
						$attr['min'] = $validation['filters']['min'];
					}
					if ( isset( $validation['filters']['max'] ) ) {
						$attr['max'] = $validation['filters']['max'];
					}
					$attr['step'] = 'any';
					break;
				case 'phone':
					$input_type = 'tel';
					break;
				case 'url':
					$input_type = 'url';
					break;
				case 'email':
					$input_type = 'email';
					break;
			}
		}

		$data_attr = $this->convert_data_attributes( $data );
		$html_attr = $this->convert_html_attributes( $attr );
?>
<div class="<?php echo implode( ' ', $wrapper_class ); ?>">
	<?php $this->print_icon_by_class( $icon ); ?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	<?php echo $html_attr; ?>
	<?php if ( ! isset( $attr['type'] ) ) : ?>
	type="<?php echo $input_type; ?>"
	<?php endif; ?>
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	maxlength="<?php echo esc_attr( $maxlength ); ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
	<?php if ( '' != $placeholder ) : ?>
		<label for="<?php echo $id ?>"><?php echo $placeholder; ?></label>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Generate textarea HTML
	 *
	 * @param      string   $name         HTML name of the text input
	 * @param      string   $value        Initial value of the text input
	 * @param      string   $placeholder  Default placeholder
	 * @param      string   $state        readonly or disabled state
	 * @param      array    $classes      Array of additional classes
	 * @param      array    $validation   Associative array of all validation
	 *                                    clauses
	 * @param      array    $data         HTML 5 data attributes in associative
	 *                                    array
	 * @param      boolean  $attr         HTML attributes and values
	 * @param      boolean  $icon         The icon
	 * @param      boolean  $no_material  No material styling
	 * @param      string  $size   Size of the text input
	 * @see        IPT_Plugin_UIF_Admin::convert_validation_class
	 * @see        IPT_Plugin_UIF_Admin::convert_data_attributes
	 */
	public function textarea( $name, $value, $placeholder, $state = 'normal', $classes = array(), $validation = false, $data = false, $attr = false, $icon = false, $no_material = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_textarea';
		if ( ! $no_material ) {
			$classes[] = 'materialize-textarea';
		}

		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
		$html_attr = $this->convert_html_attributes( $attr );

		$maxlength = '';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = $validation['filters']['maxSize'];
		}
		$wrapper_class = array( 'input-field' );
		if ( ! empty( $icon ) && 'none' != $icon ) {
			$wrapper_class[] = 'has-icon';
		}
?>
<div class="<?php echo implode( ' ', $wrapper_class ); ?>">
	<?php $this->print_icon( $icon ); ?>
	<textarea class="<?php echo implode( ' ', $classes ); ?>"
			  rows="4"
		<?php echo $data_attr; ?>
		<?php echo $html_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		maxlength="<?php echo esc_attr( $maxlength ); ?>"
		<?php if ( $no_material ) : ?>
			placeholder="<?php echo esc_attr( $placeholder ); ?>"
		<?php endif; ?>
		id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php if ( ! $no_material && '' != $placeholder ) : ?>
			<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
		<?php endif; ?>
</div>

		<?php
	}

	public function password( $name_prefix, $value, $placeholder = '', $state = 'normal', $confirm = false, $classes = array(), $validation = false, $data = false ) {
		$name = $name_prefix . '[value]';
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
?>
<div class="input-field has-icon ipt-eform-password">
	<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="password"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
<?php if ( $confirm !== false ) : ?>
<div class="input-field has-icon ipt-eform-password">
	<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
	<input class="ipt_uif_text ipt_uif_password ipt_uif_password_confirm check_me validate[equals[<?php echo $id; ?>]]"
		type="password"
		name="<?php echo esc_attr( $name_prefix ); ?>[confirm]"
		id="<?php echo $this->generate_id_from_name( $name_prefix . '[confirm]' ); ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<label for="<?php echo $this->generate_id_from_name( $name_prefix . '[confirm]' ); ?>"><?php echo __( 'Confirm', 'ipt_fsqm' ); ?></label>
</div>
<?php endif; ?>
		<?php
	}

	public function password_simple( $name, $value, $placeholder = '', $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );
?>
<div class="input-field has-icon ipt-eform-password">
	<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="password"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
		<?php
	}

	public function keypad( $name, $value, $settings, $placeholder, $mask = false, $multiline = false, $state = 'normal', $classes = array(), $validation = false, $data = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_keypad';
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' != $validation_attr ) {
			$classes[] = $validation_attr;
		}

		$data_attr = $this->convert_data_attributes( $data );

		if ( $mask ) {
?>
<div class="input-field has-icon ipt-eform-password">
	<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
		autocomplete="off"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
		type="password"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
			<?php
		} else {
			if ( $multiline ) {
?>
<div class="input-field has-icon">
	<?php $this->print_icon_by_class( 'keyboard' ); ?>
	<textarea class="<?php echo implode( ' ', $classes ); ?> ipt_uif_textarea materialize-textarea"
			  rows="4"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
<?php
			} else {
?>
<div class="input-field has-icon">
	<?php $this->print_icon_by_class( 'keyboard' ); ?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
		<label for="<?php echo $id; ?>"><?php echo $placeholder; ?></label>
</div>
				<?php
			}
		}
	}

	/**
	 * Prints a group of radio items for a single HTML name
	 *
	 * @param      string          $name         The HTML name of the radio
	 *                                           group
	 * @param      array           $items        Associative array of all the
	 *                                           radio items. array( 'value' =>
	 *                                           '', 'label' => '', 'disabled'
	 *                                           => true|false,//optional 'data'
	 *                                           => array('key' =>
	 *                                           'value'[,...]), //optional HTML
	 *                                           5 data attributes inside an
	 *                                           associative array )
	 * @param      string          $checked      The value of the checked item
	 * @param      array           $validation   Array of the validation clauses
	 * @param      int             $column       Number of columns 1|2|3|4
	 * @param      bool            $conditional  Whether the group represents
	 *                                           conditional questions. This
	 *                                           will wrap it inside a
	 *                                           conditional div which will be
	 *                                           fired using jQuery. It does not
	 *                                           populate or create anything
	 *                                           inside the conditional div. The
	 *                                           id of the conditional divs
	 *                                           should be given inside the data
	 *                                           value of the items in the form
	 *                                           condID => 'ID_OF_DIV'
	 * @param      bool            $disabled     Set TRUE if all the items are
	 *                                           disabled
	 * @param      integer|string  $icon         The icon
	 * @return     void
	 */
	public function radios( $name, $items, $checked, $validation = false, $column = 2, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}


		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$items = $this->standardize_items( $items );

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		foreach ( (array) $items as $item ) :
		$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
		$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
		$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
?>
<div class="ipt_uif_label_column column_<?php echo $column; ?>">
	<input<?php echo in_array( $item['value'], $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="radio"
		class="<?php echo trim( $validation_class ); ?> ipt_uif_radio with-gap"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>>
		 <?php echo apply_filters( 'ipt_uif_label', $item['label'] ); ?>
	</label>
</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Prints a group of checkbox items for a single HTML name
	 *
	 * @param      string          $name         The HTML name of the radio
	 *                                           group
	 * @param      array           $items        Associative array of all the
	 *                                           radio items. array( 'value' =>
	 *                                           '', 'label' => '', 'disabled'
	 *                                           => true|false,//optional 'data'
	 *                                           => array('key' =>
	 *                                           'value'[,...]), //optional HTML
	 *                                           5 data attributes inside an
	 *                                           associative array )
	 * @param      string          $checked      The value of the checked item
	 * @param      array           $validation   Array of the validation clauses
	 * @param      int             $column       Number of columns 1|2|3|4s
	 * @param      bool            $conditional  Whether the group represents
	 *                                           conditional questions. This
	 *                                           will wrap it inside a
	 *                                           conditional div which will be
	 *                                           fired using jQuery. It does not
	 *                                           populate or create anything
	 *                                           inside the conditional div. The
	 *                                           id of the conditional divs
	 *                                           should be given inside the data
	 *                                           value of the items in the form
	 *                                           condID => 'ID_OF_DIV'
	 * @param      bool            $disabled     Set TRUE if all the items are
	 *                                           disabled
	 * @param      integer|string  $icon         The icon
	 * @return     void
	 */
	public function checkboxes( $name, $items, $checked, $validation = false, $column = 2, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}

		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}

		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$items = $this->standardize_items( $items );

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
			$data_attr = $this->convert_data_attributes( $data );
			$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
			$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
			?>
			<div class="ipt_uif_label_column column_<?php echo $column; ?>">
				<input<?php echo in_array( $item['value'], (array) $checked, true ) ? ' checked="checked"' : ''; ?>
					<?php echo $data_attr; ?>
					<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
					type="checkbox"
					class="<?php echo trim( $validation_class ); ?> ipt_uif_checkbox <?php echo ( isset( $item['class'] ) ? $item['class'] : '' ); ?> filled-in"
					name="<?php echo $name; ?>" id="<?php echo $id; ?>"
					value="<?php echo $item['value']; ?>" />
				<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>>
					 <?php echo apply_filters( 'ipt_uif_label', $item['label'] ); ?>
				</label>
			</div>
			<?php
		endforeach;
		$this->clear();
		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Print a Toggle HTML item
	 *
	 * @param      string  $name         The HTML name of the toggle
	 * @param      string  $on           ON text
	 * @param      string  $off          OFF text
	 * @param      bool    $checked      TRUE if checked
	 * @param      string  $value        The HTML value of the toggle checkbox
	 *                                   (Optional, default to '1')
	 * @param      bool    $disabled     True to make it disabled
	 * @param      bool    $conditional  Whether the group represents
	 *                                   conditional questions. This will wrap
	 *                                   it inside a conditional div which will
	 *                                   be fired using jQuery. It does not
	 *                                   populate or create anything inside the
	 *                                   conditional div. The id of the
	 *                                   conditional divs should be given inside
	 *                                   the data value of the items in the form
	 *                                   condID => 'ID_OF_DIV'
	 * @param      array   $data         HTML 5 data attributes in the form
	 *                                   array('key' => 'value'[,...])
	 */
	public function toggle( $name, $on, $off, $checked, $value = '1', $disabled = false, $conditional = false, $data = array() ) {
		if ( '' == trim( $on ) ) {
			$on = '';
		}
		if ( '' == trim( $off ) ) {
			$off = '';
		}

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$id = $this->generate_id_from_name( $name );
?>
<div class="switch">
	<label for="<?php echo $id; ?>" data-on="<?php echo $on; ?>" data-off="<?php echo $off; ?>">
		<?php echo $off; ?>
		<input<?php echo $this->convert_data_attributes( $data ); ?> type="checkbox"<?php echo $this->convert_state_to_attribute( $disabled == true ? 'disabled' : '' ); ?><?php if ( $checked ) : ?> checked="checked"<?php endif; ?> class="ipt_uif_switch" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $value ); ?>" />
		<span class="lever"></span>
		<?php echo $on; ?>
	</label>
</div>
		<?php

		if ( $conditional == true ) {
			echo '</div>';
		}
		$this->clear();
	}

	/**
	 * Generate a horizontal slider to select between numerical values
	 *
	 * @param      string   $name        HTML name
	 * @param      string   $value       Initial value of the range
	 * @param      bool     $show_count  Whether or not to show the count
	 * @param      int      $min         Minimum of the range
	 * @param      int      $max         Maximum of the range
	 * @param      int      $step        Slider move step
	 * @param      string   $prefix      The prefix
	 * @param      string   $suffix      The suffix
	 * @param      array    $labels      The labels
	 * @param      boolean  $nomin       The nomin
	 * @param      boolean  $floats      The floats
	 * @param      boolean  $vertical    The vertical
	 * @param      integer  $height      The height
	 */
	public function slider( $name, $value, $show_count = true, $min = 0, $max = 100, $step = 1, $prefix = '', $suffix = '', $labels = array(), $nomin = false, $floats = true, $vertical = false, $height = 300 ) {
		// Enqueue
		wp_enqueue_script( 'jquery-ui-slider-pips', $this->static_location . 'js/jquery-ui-slider-pips.js', array( 'jquery', 'jquery-ui-slider' ), $this->version, true );
		wp_enqueue_style( 'ipt-jq-ui-slider-pips', $this->static_location . 'css/jquery-ui-slider-pips.css', array(), $this->version );
		// Other stuff
		$min = (float) $min;
		$max = (float) $max;
		$step = (float) $step;
		$value = $value == '' ? $min : (float) $value;
		if ( $value < $min )
			$value = $min;
		if ( $value > $max )
			$value = $max;

		$label_data = $this->slider_labels( $labels, $min, $max, $step );
		$container_classes = array( 'ipt_uif_empty_box', 'ipt_uif_slider_box' );
		if ( true == $vertical ) {
			$container_classes[] = 'ipt_uif_slider_vertical';
		}
?>
<div class="<?php echo implode( ' ', $container_classes ); ?>">
	<input data-floats="<?php echo $floats; ?>" type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" data-nomin="<?php echo $nomin; ?>" data-show-count="<?php echo $show_count; ?>" data-labels="<?php echo esc_attr( json_encode( (object) $label_data ) ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>" class="ipt_uif_slider check_me validate[funcCall[iptUIFSliderVal]]" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" data-vertical="<?php echo $vertical; ?>" data-height="<?php echo $height; ?>" />
	<?php if ( $show_count ) : ?>
	<div class="ipt_uif_slider_count">
		<?php echo $prefix; ?><span class="ipt_uif_slider_count_single"><?php echo $value != '' ? $value : $min; ?></span><?php echo $suffix; ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Generate a horizontal slider to select a range between numerical values
	 *
	 * @param mixed   array|string $names HTML names in the order Min value -> Max value. If string is given the [max] and [min] is added to make an array
	 * @param array   $values     Initial values of the range in the same order
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function slider_range( $names, $values, $show_count = true, $min = 0, $max = 100, $step = 1, $prefix = '', $suffix = '', $labels = array(), $nomin = false, $floats = true, $vertical = false, $height = 300 ) {
		// Enqueue
		wp_enqueue_script( 'jquery-ui-slider-pips', $this->static_location . 'js/jquery-ui-slider-pips.js', array( 'jquery', 'jquery-ui-slider' ), $this->version, true );
		wp_enqueue_style( 'ipt-jq-ui-slider-pips', $this->static_location . 'css/jquery-ui-slider-pips.css', array(), $this->version );
		// Main stuff
		$min = (float) $min;
		$max = (float) $max;
		$step = (float) $step;
		if ( !is_array( $names ) ) {
			$name = (string) $names;
			$names = array(
				$name . '[min]', $name . '[max]',
			);
		}

		if ( !is_array( $values ) ) {
			$value = (int) $values;
			$values = array(
				$value, $value,
			);
		}
		if ( !isset( $values[0] ) ) {
			$values[0] = $values['min'];
			$values[1] = $values['max'];
		}
		$value_min = $values[0] != '' ? $values[0] : $min;
		$value_max = $values[1] != '' ? $values[1] : $min;

		if ( $value_min < $min )
			$value_min = $min;
		if ( $value_min > $max )
			$value_min = $max;
		if ( $value_max < $min )
			$value_max = $min;
		if ( $value_max > $max )
			$value_max = $max;

		$label_data = $this->slider_labels( $labels, $min, $max, $step );
		$container_classes = array( 'ipt_uif_empty_box', 'ipt_uif_slider_box', 'ipt-eform-rangebox' );
		if ( true == $vertical ) {
			$container_classes[] = 'ipt_uif_slider_vertical';
		}
?>
<div class="<?php echo implode( ' ', $container_classes ); ?>">
	<input data-floats="<?php echo $floats; ?>" type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" data-nomin="<?php echo $nomin; ?>" data-show-count="<?php echo $show_count; ?>" data-labels="<?php echo esc_attr( json_encode( (object) $label_data ) ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>" class="ipt_uif_slider slider_range check_me validate[funcCall[iptUIFSliderVal]]" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $names[0] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[0] ); ?>" value="<?php echo esc_attr( $value_min ); ?>" data-vertical="<?php echo $vertical; ?>" data-height="<?php echo $height; ?>" />
	<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" class="ipt_uif_slider_range_max" name="<?php echo esc_attr( trim( $names[1] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[1] ); ?>" value="<?php echo esc_attr( $value_max ); ?>" />
	<?php if ( $show_count ) : ?>
	<div class="ipt_uif_slider_count">
		<?php echo $prefix; ?><span class="ipt_uif_slider_count_min"><?php echo $value_min; ?></span><?php echo $suffix; ?>
		 - <?php echo $prefix; ?><span class="ipt_uif_slider_count_max"><?php echo $value_max; ?></span><?php echo $suffix; ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Prints a select dropdown form element
	 *
	 * @param      string   $name          The HTML name of the radio group
	 * @param      array    $items         Associative array of all the radio
	 *                                     items. array( 'value' => '', 'label'
	 *                                     => '', 'data' => array('key' =>
	 *                                     'value'[,...]), //optional HTML 5
	 *                                     data attributes inside an associative
	 *                                     array )
	 * @param      string   $selected      The value of the selected item
	 * @param      array    $validation    Array of the validation clauses
	 * @param      bool     $conditional   Whether the group represents
	 *                                     conditional questions. This will wrap
	 *                                     it inside a conditional div which
	 *                                     will be fired using jQuery. It does
	 *                                     not populate or create anything
	 *                                     inside the conditional div. The id of
	 *                                     the conditional divs should be given
	 *                                     inside the data value of the items in
	 *                                     the form condID => 'ID_OF_DIV'
	 * @param      bool     $print_select  Whether or not to print the select
	 *                                     html
	 * @param      bool     $disabled      Set TRUE if all the items are
	 *                                     disabled
	 * @param      boolean  $multiple      Whether multiple select or not
	 * @return     void
	 */
	public function select( $name, $items, $selected, $validation = false, $conditional = false, $print_select = true, $disabled = false, $multiple = false, $e_label = '' ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		// No need to Enqueue Style
		// It is added to the core CSS file
		// wp_enqueue_style( 'select2-css', $this->static_location . 'css/select2.min.css', array(), $this->version );
		// Compatibility with WooCommerce
		wp_deregister_script( 'select2' );
		wp_enqueue_script( 'select2', $this->static_location . 'js/select2.min.js', array( 'jquery' ), $this->version, true );
		$validation_class = $this->convert_validation_class( $validation );

		$classes = array();
		$classes[] = $validation_class;
		$classes[] = 'ipt_uif_select';

		if ( !is_array( $selected ) ) {
			$selected = (array) $selected;
		}

		$id = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_select">';
		}

		$items = $this->standardize_items( $items );

		if ( $print_select ) {
			echo '<select class="' . implode( ' ', $classes ) . '" name="' . esc_attr( trim( $name ) ) . '" id="' . $id . '" ' . $this->convert_state_to_attribute( ( $disabled == true ) ? 'disabled' : '' ) . ( $multiple ? ' multiple="multiple"' : '' ) . ( '' != $e_label ? 'data-placeholder="' . esc_attr( $e_label ) . '" data-allow-clear="true"' : '' ) . ' data-theme="eform-material">';
		}

		foreach ( (array) $items as $item ) :
			$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
?>
<option value="<?php echo $item['value']; ?>"<?php if ( in_array( $item['value'], (array) $selected, true ) ) echo ' selected="selected"'; ?><?php echo $data_attr; ?>><?php echo $item['label']; ?></option>
			<?php
		endforeach;

		if ( $print_select ) {
			echo '</select>';
			$this->clear();
		}

		if ( $conditional == true ) {
			echo '</div>';
		}
	}

	/**
	 * Print Matrix radio and select
	 *
	 * @param      string          $name_prefix  The name prefix
	 * @param      array           $rows         The rows
	 * @param      array           $columns      The columns
	 * @param      array           $values       The values
	 * @param      boolean         $multiple     If checkbox
	 * @param      boolean         $required     If required
	 * @param      integer|string  $icon         The icon
	 * @param      array           $numerics     The numerics
	 */
	public function matrix( $name_prefix, $rows, $columns, $values, $multiple, $required, $icon = 0xe18e, $numerics = array() ) {
		$type = $multiple == true ? 'checkbox' : 'radio';
		$validation = array(
			'required' => $required,
		);
		$validation_attr = $this->convert_validation_class( $validation );
		if ( !is_array( $values ) ) {
			$values = (array) $values;
		}
		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}
?>
<div class="ipt_uif_matrix_container">
	<table class="ipt_uif_matrix highlight bordered">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
			$values[$r_key] = (array) $values[$r_key];
		}
?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $row ); ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
			$name = $name_prefix . '[rows][' . $r_key . '][]';
		$id = $this->generate_id_from_name( $name ) . '_' . $c_key;
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<input type="<?php echo $type; ?>" class="ipt_uif_<?php echo $type . ' ' . $validation_attr; ?>"
						   value="<?php echo $c_key; ?>"
						   name="<?php echo $name; ?>" id="<?php echo $id; ?>"
						   data-num="<?php echo ( isset( $numerics[$c_key] ) ? floatval( $numerics[$c_key] ) : '0' ); ?>"
						   <?php if ( in_array( (string) $c_key, $values[$r_key], true ) ) echo 'checked="checked"'; ?> />
					<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?>></label>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

		<?php
	}

	/**
	 * Matrix dropdown funciton
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      array    $rows         The rows
	 * @param      array    $columns      The columns
	 * @param      array    $items        The items
	 * @param      array    $values       The values
	 * @param      array    $validation   The validation
	 * @param      boolean  $multiple     The multiple
	 */
	public function matrix_select( $name_prefix, $rows, $columns, $items, $values, $validation = array(), $multiple = false ) {
		if ( ! is_array( $values ) ) {
			$values = (array) $values;
		}

		// Adjust items for empty ones
		$e_label = '';
		if ( '' == $items[0]['value'] ) {
			//$empty_one = array_shift( $items );
			$e_label = $items[0]['label'];
			if ( $multiple ) {
				array_shift( $items );
			}
		}
		?>
<div class="ipt_uif_matrix_container ipt_uif_matrix_select">
	<table class="ipt_uif_matrix highlight bordered">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( !isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
				$values[$r_key] = (array) $values[$r_key];
			}
			?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $row ); ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
				$name = $name_prefix . '[rows][' . $r_key . '][' . $c_key . ']';
				if ( $multiple ) {
					$name .= '[]';
				}
				if ( ! isset( $values[$r_key][$c_key] ) ) {
					$values[$r_key][$c_key] = '';
				}
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<?php $this->select( $name, $items, $values[$r_key][$c_key], $validation, false, true, false, $multiple, $e_label ); ?>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
		<?php
	}

	/**
	 * Create matrix feedbacl elements
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      array    $rows         The rows
	 * @param      array    $columns      The columns
	 * @param      array    $values       The values
	 * @param      boolear  $multiline    If multiline
	 * @param      array    $validation   The validation
	 * @param      integer  $icon         The icon
	 */
	public function matrix_text( $name_prefix, $rows, $columns, $values, $multiline, $validation, $icon = 0xe18e ) {
		if ( ! is_array( $values ) ) {
			$values = (array) $values;
		}
		?>
<div class="ipt_uif_matrix_container ipt_uif_matrix_feedback">
	<table class="ipt_uif_matrix highlight bordered">
		<thead>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col"></th>
				<?php foreach ( $columns as $column ) : ?>
				<th scope="col"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $column ); ?></div></th>
				<?php endforeach; ?>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ( $rows as $r_key => $row ) : ?>
			<?php
			if ( ! isset( $values[$r_key] ) ) {
				$values[$r_key] = array();
			} else {
				$values[$r_key] = (array) $values[$r_key];
			}
			?>
			<tr>
				<th scope="row"><div class="ipt_uif_matrix_div_cell"><?php echo apply_filters( 'ipt_uif_label', $row ); ?></div></th>
				<?php foreach ( $columns as $c_key => $column ) : ?>
				<?php
				$name = $name_prefix . '[rows][' . $r_key . '][' . $c_key . ']';
				if ( ! isset( $values[$r_key][$c_key] ) ) {
					$values[$r_key][$c_key] = '';
				}
?>
				<td><div class="ipt_uif_matrix_div_cell">
					<?php if ( $multiline ) : ?>
					<?php $this->textarea( $name, $values[$r_key][$c_key], $column, 'normal', array( 'ipt_uif_matrix_text' ), $validation, false, false, $icon ); ?>
					<?php else : ?>
					<?php $this->text( $name, $values[$r_key][$c_key], $column, $icon, 'normal', array( 'ipt_uif_matrix_text' ), $validation ); ?>
					<?php endif; ?>
				</div></td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>
		<?php
	}

	/*==========================================================================
	 * Rating Elements
	 *========================================================================*/
	/**
	 * Creates smiley rating
	 *
	 * @param      string   $name                  The name
	 * @param      string   $value                 The value
	 * @param      boolean  $enabled               If enabled
	 * @param      boolean  $required              If required
	 * @param      array    $labels                The labels
	 * @param      array    $classes               The classes
	 * @param      array    $data                  The data
	 * @param      boolean  $feedback              The feedback
	 * @param      string   $feedback_name         The feedback name
	 * @param      string   $feedback_value        The feedback value
	 * @param      string   $feedback_placeholder  The feedback placeholder
	 */
	public function smiley_rating( $name, $value, $enabled, $required, $labels = array(), $classes = array(), $data = array(), $feedback = true, $feedback_name = '', $feedback_value = '', $feedback_placeholder = '' ) {
		$value = (string) $value;
		$enabled = wp_parse_args( (array) $enabled, array(
			'frown' => true,
			'sad' => true,
			'neutral' => true,
			'happy' => true,
			'excited' => true,
		) );
		$data = wp_parse_args( $data, array(
			'frown' => array(),
			'sad' => array(),
			'neutral' => array(),
			'happy' => array(),
			'excited' => array(),
		) );
		$smileys = array(
			'frown',
			'sad',
			'neutral',
			'happy',
			'excited',
		);
		$classes = (array) $classes;
		$classes = array_merge( $classes, array(
			'ipt_uif_rating',
			'ipt_uif_rating_smiley',
		) );
		if ( in_array( $value, $smileys ) && $feedback ) {
			$classes[] = 'ipt_uif_smiley_feedback_active';
		}
		?>
<div class="<?php echo implode( ' ', $classes ); ?>">
	<div class="ipt_uif_smiley_rating_inner">
		<?php foreach ( $smileys as $smiley ) : ?>
		<?php if ( $enabled[$smiley] == false ) continue; ?>
		<?php $id = $this->generate_id_from_name( $name ) . '_' . $smiley; ?>
		<input<?php echo ( $value === (string) $smiley ) ? ' checked="checked"' : ''; ?>
			type="radio"
			class="<?php if ( $required ) : ?>check_me validate[required]<?php endif; ?> ipt_uif_radio ipt_uif_smiley_rating_radio ipt_uif_smiley_rating_radio_<?php echo $smiley; ?>"
			name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo $id; ?>"
			<?php echo $this->convert_data_attributes( $data[$smiley] ); ?>
			value="<?php echo $smiley; ?>" />
		<label for="<?php echo $id; ?>"<?php if ( isset( $labels[$smiley] ) ) echo ' title="' . esc_attr( $labels[$smiley] ) . '" class="ipt_uif_tooltip"' ?>></label>
		<?php endforeach; ?>
		<?php $this->clear(); ?>
	</div>
	<?php if ( $feedback ) : ?>
	<div class="ipt_uif_smiley_rating_feedback_wrap">
		<?php $this->textarea( $feedback_name, $feedback_value, $feedback_placeholder, 'normal', array( 'ipt_uif_smiley_rating_feedback' ), false, false, false, 'thumbs_up_down' ); ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Like Dislike Element
	 *
	 * @param      string   $name                  The name
	 * @param      array    $labels                The labels
	 * @param      array    $values                The values
	 * @param      string   $value                 The value
	 * @param      boolean  $required              If required
	 * @param      array    $classes               The classes
	 * @param      array    $data                  The data
	 * @param      boolean  $feedback              The feedback
	 * @param      string   $feedback_name         The feedback name
	 * @param      string   $feedback_value        The feedback value
	 * @param      string   $feedback_placeholder  The feedback placeholder
	 */
	public function likedislike( $name, $labels, $values, $value, $required, $classes = array(), $data = array(), $feedback = false, $feedback_name = '', $feedback_value = '', $feedback_placeholder = '' ) {
		$value = (string) $value;
		$labels = wp_parse_args( $labels, array(
			'like' => '',
			'dislike' => '',
		) );
		$values = wp_parse_args( $values, array(
			'like' => 'like',
			'dislike' => 'dislike',
		) );
		$data = wp_parse_args( $data, array(
			'like' => array(),
			'dislike' => array(),
		) );

		$classes = (array) $classes;
		$classes = array_merge( $classes, array(
			'ipt_uif_rating',
			'ipt_uif_rating_likedislike',
		) );
		$likes = array(
			'like', 'dislike',
		);
		if ( in_array( $value, $values ) && $feedback ) {
			$classes[] = 'ipt_uif_likedislike_feedback_active';
		}
		?>
<div class="<?php echo implode( ' ', $classes ); ?>">
	<div class="ipt_uif_likedislike_rating_inner">
		<?php foreach ( $likes as $like ) : ?>
		<?php $id = $this->generate_id_from_name( $name ) . '_' . $like; ?>
		<input<?php echo ( $value === (string) $values[$like] ) ? ' checked="checked"' : ''; ?>
			type="radio"
			class="<?php if ( $required ) : ?>check_me validate[required]<?php endif; ?> ipt_uif_radio ipt_uif_likedislike_rating_radio ipt_uif_likedislike_rating_radio_<?php echo $like; ?>"
			name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo $id; ?>"
			<?php echo $this->convert_data_attributes( $data[$like] ); ?>
			value="<?php echo esc_attr( $values[$like] ); ?>" />
		<label for="<?php echo $id; ?>"<?php if ( isset( $labels[$like] ) ) echo ' title="' . esc_attr( $labels[$like] ) . '" class="ipt_uif_tooltip"' ?>></label>
		<?php endforeach; ?>
		<?php $this->clear(); ?>
	</div>
	<?php if ( $feedback ) : ?>
	<div class="ipt_uif_likedislike_rating_feedback_wrap">
		<?php $this->textarea( $feedback_name, $feedback_value, $feedback_placeholder, 'normal', array( 'ipt_uif_likedislike_rating_feedback' ), false, false, false, 'thumbs_up_down' ); ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Prints Sortable Elements
	 *
	 * @param      string   $name_prefix  The name prefix
	 * @param      array    $items        The items
	 * @param      array    $order        The order
	 * @param      boolean  $randomize    The randomize
	 */
	public function sortables( $name_prefix, $items, $order = array(), $randomize = false ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}
		$keys = array_keys( $items );

		if ( !empty( $order ) ) {
			$keys = $order;
		}

		if ( $randomize && empty( $order ) ) {
			shuffle( $keys );
		}
?>
<div class="ipt_uif_sorting">
	<?php foreach ( $keys as $key ) : ?>
	<div class="ipt_uif_sortme">
		<a class="ipt_uif_sorting_handle" href="javascript:;"><?php $this->print_icon_by_class( 'bars' ); ?></a>
		<input type="hidden" data-sayt-exclude name="<?php echo $name_prefix; ?>" value="<?php echo $key; ?>" />
		<?php echo apply_filters( 'ipt_uif_label', $items[$key]['label'] ); ?>
	</div>
	<?php endforeach; ?>
</div>
		<?php
	}

	/*==========================================================================
	 * Design Elements
	 *========================================================================*/

	/**
	 * Divider & Heading
	 *
	 * @param      string   $text        The text
	 * @param      string   $type        The type
	 * @param      string   $align       The align
	 * @param      string   $icon        The icon
	 * @param      boolean  $scroll_top  The scroll top
	 * @param      array    $classes     The classes
	 * @param      boolean  $no_bg       No background ( Deprecated )
	 */
	public function divider( $text = '', $type = 'div', $align = 'center', $icon = 'none', $scroll_top = false, $classes = array(), $no_bg = false ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_divider';
		$classes[] = 'ipt_uif_align_' . $align;
		$text = trim( $text );
		if ( '' === $text && ! $scroll_top ) {
			$classes[] = 'ipt_uif_empty_divider';
		}
		if ( 'none' === $icon || '' === $icon ) {
			$classes[] = 'ipt_uif_divider_no_icon';
		}
		if ( $no_bg === true ) {
			$classes[] = 'ipt_uif_divider_icon_no_bg';
		}
		if ( $scroll_top ) {
			$classes[] = 'ipt_uif_divider_has_scroll';
		}
		if ( '' === $text ) {
			$classes[] = 'ipt_uif_divider_no_text';
		}
		if ( 'div' == $type || '' == $text ) {
			$classes[] = 'eform-just-divider';
		}
?>
<<?php echo $type; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( $scroll_top || $text !== '' ) : ?>
		<?php if ( $scroll_top ) : ?>
		<?php $this->print_scroll_to_top(); ?>
		<?php endif; ?>
		<span class="ipt_uif_divider_text">
			<?php $this->print_icon( $icon ); ?>
			<?php if ( $text != '' ) : ?>
				<span class="ipt_uif_divider_text_inner">
				<?php echo $text; ?>
				</span>
			<?php endif; ?>
		</span>
	<?php endif; ?>
</<?php echo $type; ?>>
		<?php
	}

	/**
	 * Prints an eForm styled login form
	 *
	 * @param      array  $args           The atts
	 * @param      <type>  $defaults       The defaults
	 * @param      <type>  $login_buttons  The login buttons
	 * @param      <type>  $redirect       The redirect
	 * @param      <type>  $regurl         The regurl
	 */
	public function login_form( $args, $login_buttons ) {
		/**
		 * Fires when the login form is initialized.
		 *
		 * @since 3.2.0
		 */
		do_action( 'login_init' );

		/**
		 * Enqueue scripts and styles for the login page.
		 *
		 * @since 3.1.0
		 */
		do_action( 'login_enqueue_scripts' );

		// Filter Arguments
		$defaults = array(
			'echo' => true,
			// Default 'redirect' value takes the user back to the request URI.
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
			'form_id' => 'loginform',
			'label_username' => __( 'Username or Email Address' ),
			'label_password' => __( 'Password' ),
			'label_remember' => __( 'Remember Me' ),
			'label_log_in' => __( 'Log In' ),
			'id_username' => 'user_login',
			'id_password' => 'user_pass',
			'id_remember' => 'rememberme',
			'id_submit' => 'wp-submit',
			'remember' => true,
			'value_username' => '',
			// Set 'value_remember' to true to default the "Remember me" checkbox to checked.
			'value_remember' => false,
		);
		/**
		 * Filters the default login form output arguments.
		 *
		 * @since 3.0.0
		 *
		 * @see wp_login_form()
		 *
		 * @param array $defaults An array of default login form arguments.
		 */
		$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );

		// Get filters
		/**
		 * Filters content to display at the top of the login form.
		 *
		 * The filter evaluates just following the opening form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_top = apply_filters( 'login_form_top', '', $args );

		/**
		 * Filters content to display in the middle of the login form.
		 *
		 * The filter evaluates just following the location where the 'login-password'
		 * field is displayed.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_middle = apply_filters( 'login_form_middle', '', $args );

		/**
		 * Filters content to display at the bottom of the login form.
		 *
		 * The filter evaluates just preceding the closing form tag element.
		 *
		 * @since 3.0.0
		 *
		 * @param string $content Content to display. Default empty.
		 * @param array  $args    Array of login form arguments.
		 */
		$login_form_bottom = apply_filters( 'login_form_bottom', '', $args );

		foreach ( $login_buttons as $key => $login_button ) {
			$login_button[2] = 'medium';
			$login_button[3] = 'secondary';
			$login_buttons[ $key ] = $login_button;
		}
		// Start the output
		?>
<?php // Login Form Top ?>
<?php if ( $login_form_top != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_top; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Username ?>
<?php $this->column_head( '', 'half' ); ?>
	<?php $this->question_container( 'log', $args['label_username'], '', array( array( $this, 'text' ), array( 'log', $args['value_username'], $args['label_username'], 'user' ) ), true, false, false, '', array(), array(), true ); ?>
<?php $this->column_tail(); ?>
<?php // Password ?>
<?php $this->column_head( '', 'half' ); ?>
	<?php $this->question_container( 'pwd', $args['label_password'], '', array( array( $this, 'password_simple' ), array( 'pwd', '', $args['label_password'], 'console' ) ), true, false, false, '', array(), array(), true ); ?>
<?php $this->column_tail(); ?>
<?php // Login Form Action ?>
<?php do_action( 'login_form' ); ?>
<?php // Login Form Middle ?>
<?php if ( $login_form_middle != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_middle; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Remember Button ?>
<?php if ( $args['remember'] ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php $this->checkbox( 'rememberme', array(
			'value' => 'forever',
			'label' => $args['label_remember'],
		), $args['value_remember'] ); ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Login Buttons ?>
<?php $this->column_head( '', 'full' ); ?>
	<?php $this->buttons( $login_buttons, '', 'right' ) ?>
<?php $this->column_tail(); ?>
<?php // Login Form Bottom ?>
<?php if ( $login_form_bottom != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_bottom; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<input type="hidden" name="redirect_to" value="<?php echo esc_url( $args['redirect'] ); ?>" />
		<?php
	}



	/*==========================================================================
	 * Preloader
	 *========================================================================*/
	/**
	 * Creates the HTML for the CSS3 Loader.
	 *
	 * @param      bool    $hidden   TRUE(default) if hidden in inital state
	 *                               (Optional).
	 * @param      string  $id       HTML ID (Optional).
	 * @param      array   $labels   Labels which will be converted to HTML data
	 *                               attribute
	 * @param      bool    $inline   Whether inline(true) or overlay (false)
	 * @param      string  $default  Default text
	 * @param      array   $classes  Array of additional classes (Optional).
	 */
	public function ajax_loader( $hidden = true, $id = '', $labels = array(), $inline = false, $default = null, $classes = array() ) {
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		if ( ! $inline ) {
			$classes[] = 'ipt-eform-preloader';
		} else {
			$classes[] = 'ipt-eform-preloader-inline';
		}
		$classes[] = 'ipt_uif_ajax_loader';
		$id_attr = '';
		if ( '' != $id ) {
			$id_attr = ' id="' . esc_attr( trim( $id ) ) . '"';
		}
		$style_attr = '';
		if ( true == $hidden ) {
			$style_attr = ' style="display: none;"';
		}
		$data_attr = $this->convert_data_attributes( $labels );
		if ( null === $default ) {
			$default = $this->default_messages['ajax_loader'];
		}

		?>
		<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr . $style_attr . $data_attr; ?>>
			<div class="ipt-eform-preloader-inner">
				<div class="ipt-eform-preloader-circle">
					<div class="preloader-wrapper active">
						<div class="spinner-layer spinner-blue">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>

						<div class="spinner-layer spinner-red">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>

						<div class="spinner-layer spinner-yellow">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>

						<div class="spinner-layer spinner-green">
							<div class="circle-clipper left">
								<div class="circle"></div>
							</div><div class="gap-patch">
								<div class="circle"></div>
							</div><div class="circle-clipper right">
								<div class="circle"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="ipt-eform-preloader-text">
					<div class="ipt-eform-preloader-text-inner ipt_uif_ajax_loader_text"><?php echo $default; ?></div>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php
	}

	/*==========================================================================
	 * Icons Functions
	 *========================================================================*/
	public function print_icon_by_class( $icon = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background, $additional_classes );
			return;
		}
		if ( ! is_array( $additional_classes ) ) {
			$additional_classes = (array) $additional_classes;
		}
?>
<?php if ( 'none' != $icon && ! empty( $icon ) ) : ?>
<i title="<?php echo esc_attr( $title ); ?>" class="<?php echo implode( ' ', $additional_classes ); ?> ipt-icomoon-<?php echo esc_attr( $icon ); ?> ipticm prefix"></i>
<?php endif; ?>
		<?php
	}

	public function print_icon_by_data( $data = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( ! is_numeric( $data ) ) {
			$this->print_icon_by_class( $data, $background, $additional_classes );
			return;
		}
		if ( ! is_array( $additional_classes ) ) {
			$additional_classes = (array) $additional_classes;
		}
?>
<?php if ( 'none' != $data && ! empty( $data ) ) : ?>
<i title="<?php echo esc_attr( $title ); ?>" class="<?php echo implode( ' ', $additional_classes ); ?> ipticm prefix" data-ipt-icomoon="&#x<?php echo dechex( $data ); ?>;"></i>
<?php endif; ?>
		<?php
	}

	public function print_icon( $icon = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( 'none' == $icon || empty( $icon ) ) {
			return;
		}
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background, $additional_classes, $title );
		} else {
			$this->print_icon_by_class( $icon, $background, $additional_classes, $title );
		}
	}

	/**
	 * Prints scroll to top
	 */
	public function print_scroll_to_top() {
		?>
<a href="#" class="ipt_uif_scroll_to_top"><i class="ipticm ipt-icomoon-chevron-up suffix"></i></a>
		<?php
	}
}
