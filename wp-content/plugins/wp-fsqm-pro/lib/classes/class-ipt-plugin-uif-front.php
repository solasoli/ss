<?php
/**
 * iPanelThemes User Interface for Plugin's Framework
 * Front Area
 *
 * Generates all user interface/form elements
 * It needs to have the ipt_plugin_uif.admin.css and ipt_plugin_uif.admin.js file
 *
 * @depends base, jQueryUI{menu, autocomplete}
 * @version 1.0.2
 *
 * This part of the framework is meant to ship with eForm plugin only (as of 30th aug, 2013 till infinity)
 */

if ( !class_exists( 'IPT_Plugin_UIF_Front' ) ) :
	class IPT_Plugin_UIF_Front extends IPT_Plugin_UIF_Base {
	/**
	 * Default Messages
	 *
	 * Shortcut to all the messages
	 *
	 * @var array All the default messages
	 */
	public $default_messages = array();

	/*==========================================================================
	 * System API
	 *========================================================================*/
	public static function instance( $text_domain = 'default', $classname = null ) {
		return parent::instance( $text_domain, __CLASS__ );
	}

	public function __construct( $text_domain = 'default', $classname = __CLASS__ ) {
		$this->default_messages = array(
			'pwd_reveal' => __( 'Click to show password', 'ipt_fsqm' ),
			'ajax_loader' => __( 'Please Wait', 'ipt_fsqm' ),
			'timer' => array(
				'Days' => __( 'Days', 'ipt_fsqm' ),
				'Hours' => __( 'Hours', 'ipt_fsqm' ),
				'Minutes' => __( 'Minutes', 'ipt_fsqm' ),
				'Seconds' => __( 'Seconds', 'ipt_fsqm' ),
			),
			'messages' => array(
				'green' => __( 'Success', 'ipt_fsqm' ),
				'okay' => __( 'Success', 'ipt_fsqm' ),
				'update' => __( 'Updated', 'ipt_fsqm' ),
				'yellow' => __( 'Updated', 'ipt_fsqm' ),
				'red' => __( 'Error', 'ipt_fsqm' ),
				'error' => __( 'Error', 'ipt_fsqm' ),
			),
			'ccpl' => array(
				'number' => _x( '•••• •••• •••• ••••', 'ccplaceholder', 'ipt_fsqm' ),
				'name' => _x( 'Full Name', 'ccplaceholder', 'ipt_fsqm' ),
				'expiry' => _x( '••/••', 'ccplaceholder', 'ipt_fsqm' ),
				'cvc' => _x( '•••', 'ccplaceholder', 'ipt_fsqm' ),
			),
			'ccmsg' => array(
				'validDate' => _x( "valid\nthru", 'ccmessage', 'ipt_fsqm' ),
				'monthYear' => _x( "mm/yyyy", 'ccmessage', 'ipt_fsqm' ),
			),
			'uploader' => array(
				'select' => __( 'Select files', 'ipt_fsqm' ),
				'dragdrop' => __( 'Drag \'n Drop files here', 'ipt_fsqm' ),
				'start' => __( 'Start All Uploads', 'ipt_fsqm' ),
				'cancel' => __( 'Cancel All Uploads', 'ipt_fsqm' ),
				'delete' => __( 'Delete Selected', 'ipt_fsqm' ),
				'processing_singular' => __( 'Processing&hellip;', 'ipt_fsqm' ),
				'start_singular' => __( 'Start', 'ipt_fsqm' ),
				'cancel_singular' => __( 'Cancel', 'ipt_fsqm' ),
				'error_singular' => __( 'Error', 'ipt_fsqm' ),
				'delete_singular' => __( 'Delete', 'ipt_fsqm' ),
				'messages' => array(
					'maxNumberOfFiles' => __( 'Maximum number of files exceeded', 'ipt_fsqm' ),
					'acceptFileTypes' => __( 'File type not allowed', 'ipt_fsqm' ),
					'maxFileSize' => __( 'File is too large', 'ipt_fsqm' ),
					'minFileSize' => __( 'File is too small', 'ipt_fsqm' ),
				),
			),
			'validationEngine' => array(
				'required' => array(
					'alertText' =>  __( '* This field is required', 'ipt_fsqm' ),
					'alertTextCheckboxMultiple' =>  __( '* Please select an option', 'ipt_fsqm' ),
					'alertTextCheckboxe' =>  __( '* This checkbox is required', 'ipt_fsqm' ),
					'alertTextDateRange' =>  __( '* Both date range fields are required', 'ipt_fsqm' ),
				),
				'requiredInFunction' => array(
					'alertText' =>  __( '* Incorrect answer. The correct answer is ', 'ipt_fsqm' )
				),
				'requiredSignature' => array(
					'alertText' => __( '* Please sign here', 'ipt_fsqm' ),
				),
				'noMinSlider' => array(
					'alertText' => __( '* Please set a value', 'ipt_fsqm' ),
				),
				'ccValidation' => array(
					'number' => __( '* Invalid card number', 'ipt_fsqm' ),
					'type' => __( '* Unknown card type', 'ipt_fsqm' ),
					'expiry' => __( '* Invalid expiry date', 'ipt_fsqm' ),
					'cvc' => __( '* Invalid CVC number', 'ipt_fsqm' ),
				),
				'dateRange' => array(
					'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
					'alertText2' =>  __( 'Date Range', 'ipt_fsqm' ),
				),
				'dateTimeRange' => array(
					'alertText' =>  __( '* Invalid ', 'ipt_fsqm' ),
					'alertText2' =>  __( 'Date Time Range', 'ipt_fsqm' ),
				),
				'minSize' => array(
					'alertText' =>  __( '* Minimum ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' characters required', 'ipt_fsqm' ),
				),
				'maxSize' => array(
					'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' characters allowed', 'ipt_fsqm' ),
				),
				'groupRequired' => array(
					'alertText' =>  __( '* You must fill one of the following fields', 'ipt_fsqm' ),
				),
				'min' => array(
					'alertText' =>  __( '* Minimum value is ', 'ipt_fsqm' ),
				),
				'max' => array(
					'alertText' =>  __( '* Maximum value is ', 'ipt_fsqm' ),
				),
				'past' => array(
					'alertText' =>  __( '* Date prior to ', 'ipt_fsqm' ),
				),
				'future' => array(
					'alertText' =>  __( '* Date past ', 'ipt_fsqm' ),
				),
				'maxCheckbox' => array(
					'alertText' =>  __( '* Maximum ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' option(s) allowed', 'ipt_fsqm' ),
				),
				'minCheckbox' => array(
					'alertText' =>  __( '* Please select ', 'ipt_fsqm' ),
					'alertText2' =>  __( ' option(s)', 'ipt_fsqm' ),
				),
				'equals' => array(
					'alertText' =>  __( '* Fields do not match', 'ipt_fsqm' ),
				),
				'creditCard' => array(
					'alertText' =>  __( '* Invalid credit card number', 'ipt_fsqm' ),
				),
				'phone' => array(
					// credit => jquery.h5validate.js / orefalo
					'regex' => "/^([\+][0-9]{1,3}[\ \.\-])?([\(]{1}[0-9]{2,6}[\)])?([0-9\ \.\-\/]{3,20})((x|ext|extension)[\ ]?[0-9]{1,4})?$/",
					'alertText' =>  __( '* Invalid phone number', 'ipt_fsqm' ),
				),
				'email' => array(
					// HTML5 compatible email regex ( http =>//www.whatwg.org/specs/web-apps/current-work/multipage/states-of-the-type-attribute.html#    e-mail-state-%28type=email%29 )
					'regex' => "/^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/",
					'alertText' =>  __( '* Invalid email address', 'ipt_fsqm' ),
				),
				'integer' => array(
					'regex' => "/^[\-\+]?\d+$/",
					'alertText' =>  __( '* Not a valid integer', 'ipt_fsqm' ),
				),
				'number' => array(
					// Number, including positive, negative, and floating decimal. credit => orefalo
					'regex' => "/^[\-\+]?((([0-9]{1,3})([,][0-9]{3})*)|([0-9]+))?([\.]([0-9]+))?$/",
					'alertText' =>  __( '* Invalid floating decimal number', 'ipt_fsqm' ),
				),
				'date' => array(
					// Check if date is valid by leap year
					'alertText' =>  __( '* Invalid date, must be in YYYY-MM-DD format', 'ipt_fsqm' ),
				),
				'ipv4' => array(
					'regex' => "/^((([01]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))[.]){3}(([0-1]?[0-9]{1,2})|(2[0-4][0-9])|(25[0-5]))$/",
					'alertText' =>  __( '* Invalid IP address', 'ipt_fsqm' ),
				),
				'url' => array(
					'regex' => "/^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i",
					'alertText' =>  __( '* Invalid URL', 'ipt_fsqm' ),
				),
				'onlyNumberSp' => array(
					'regex' => "/^[0-9\ ]+$/",
					'alertText' =>  __( '* Numbers only', 'ipt_fsqm' ),
				),
				'onlyLetterSp' => array(
					'regex' => "/^[a-zA-Z\ \']+$/",
					'alertText' =>  __( '* Letters only', 'ipt_fsqm' ),
				),
				'onlyLetterNumber' => array(
					'regex' => "/^[0-9a-zA-Z]+$/",
					'alertText' =>  __( '* No spaces or special characters allowed', 'ipt_fsqm' ),
				),
				'onlyLetterNumberSp' => array(
					'regex' => "/^[0-9a-zA-Z\ ]+$/",
					'alertText' =>  __( '* Only letters, number and spaces allowed', 'ipt_fsqm' ),
				),
				'noSpecialCharacter' => array(
					'regex' => "/^[0-9a-zA-Z\ \.\,\?\\\"\']+$/",
					'alertText' => __( '* No special characters allowed', 'ipt_fsqm' ),
				),
				'personName' => array(
					'regex' => "/^[a-zA-Z\ \.]+$/",
					'alertText' => __( 'Valid name only, no special characters except dots and single quote for salutation', 'ipt_fsqm' ),
				),
				//tls warning =>homegrown not fielded
				'dateFormat' => array(
					'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(?:(?:0?[1-9]|1[0-2])(\/|-)(?:0?[1-9]|1\d|2[0-8]))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^(0?2(\/|-)29)(\/|-)(?:(?:0[48]00|[13579][26]00|[2468][048]00)|(?:\d\d)?(?:0[48]|[2468][048]|[13579][26]))$/",
					'alertText' =>  __( '* Invalid Date', 'ipt_fsqm' ),
				),
				//tls warning =>homegrown not fielded
				'dateTimeFormat' => array(
					'regex' => "/^\d{4}[\/\-](0?[1-9]|1[012])[\/\-](0?[1-9]|[12][0-9]|3[01])\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1}$|^(?:(?:(?:0?[13578]|1[02])(\/|-)31)|(?:(?:0?[1,3-9]|1[0-2])(\/|-)(?:29|30)))(\/|-)(?:[1-9]\d\d\d|\d[1-9]\d\d|\d\d[1-9]\d|\d\d\d[1-9])$|^((1[012]|0?[1-9]){1}\/(0?[1-9]|[12][0-9]|3[01]){1}\/\d{2,4}\s+(1[012]|0?[1-9]){1}:(0?[1-5]|[0-6][0-9]){1}:(0?[0-6]|[0-6][0-9]){1}\s+(am|pm|AM|PM){1})$/",
					'alertText' =>  __( '* Invalid Date or Date Format', 'ipt_fsqm' ),
					'alertText2' =>  __( 'Expected Format => ', 'ipt_fsqm' ),
					'alertText3' =>  __( 'mm/dd/yyyy hh =>mm =>ss AM|PM or ', 'ipt_fsqm' ),
					'alertText4' =>  __( 'yyyy-mm-dd hh =>mm =>ss AM|PM', 'ipt_fsqm' ),
				),
			),
		);
		// Add the filters for the richtext
		add_filter( 'ipt_uif_richtext', 'wptexturize'        );
		add_filter( 'ipt_uif_richtext', 'convert_smilies'    );
		add_filter( 'ipt_uif_richtext', 'convert_chars'      );
		add_filter( 'ipt_uif_richtext', 'wpautop'            );
		add_filter( 'ipt_uif_richtext', 'shortcode_unautop'  );
		add_filter( 'ipt_uif_richtext', 'do_shortcode', 11   );
		add_filter( 'ipt_uif_richtext', 'prepend_attachment' );
		global $wp_embed;
		if ( class_exists( 'WP_Embed' ) && $wp_embed instanceof WP_Embed ) {
			add_filter( 'ipt_uif_richtext', array( $wp_embed, 'run_shortcode' ), 8 );
			add_filter( 'ipt_uif_richtext', array( $wp_embed, 'autoembed' ), 8 );
		}
		parent::__construct( $text_domain, $classname );
	}

	/*==========================================================================
	 * FILE DEPENDENCIES
	 *========================================================================*/
	/**
	 * Enqueues Scripts and Style
	 *
	 * @param string  $static_location The URL to the static admin directory
	 * @param string  $version         Version of the scripts/stylesheets
	 */
	public function enqueue( $static_location, $version, $ignore_css = array(), $ignore_js = array() ) {
		parent::enqueue( $static_location, $version, $ignore_css, $ignore_js );
		$static_location = $this->static_location;
		$version = $this->version;

		// Styles
		$styles = array(
			'ipt-plugin-uif-validation-engine-css' => array( $static_location . 'css/validationEngine.jquery.css', array() ),
			'ipt-plugin-uif-animate-css' => array( $static_location . 'css/animate.css', array() ),
			'ipt-js-tooltipster' => array( $static_location . 'css/tooltipster.bundle.min.css', array() ),
			'ipt_uif_default_theme_link' => array( $static_location . 'css/ipt-plugin-uif-front.css', array() ),
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
			'ipt-plugin-uif-front-js' => array( $static_location . 'js/jquery.ipt-plugin-uif-front' . self::$js_suffix . '.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-button', 'jquery-touch-punch', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-spinner', 'jquery-ui-progressbar', 'jquery-timepicker-addon', 'jquery-print-element', 'jquery-mwheelIntent', 'jquery-mousewheel', 'jquery-ui-autocomplete', 'ipt-plugin-uif-keyboard', 'ipt-plugin-uif-validation-engine', 'ipt-plugin-uif-validation-engine-lang', 'ipt-plugin-uif-nivo-slider', 'ipt-plugin-uif-typewatch', 'waypoints', 'count-up', 'jquery-tooltipster', 'ba-throttle-debounce' ) ),
		);
		$scripts_localize = array(
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
	}

	/*==========================================================================
	 * HTML UI ElEMENTS
	 *========================================================================*/
	/**
	 * Prints a group of radio items for a single HTML name
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *      'disabled' => true|false,//optional
	 *      'data' => array('key' => 'value'[,...]), //optional HTML 5 data attributes inside an associative array
	 *  )
	 * @param string  $checked     The value of the checked item
	 * @param array   $validation  Array of the validation clauses
	 * @param int     $column      Number of columns 1|2|3|4
	 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param bool    $disabled    Set TRUE if all the items are disabled
	 * @return void
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
		class="<?php echo trim( $validation_class ); ?> ipt_uif_radio"
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
			'ipt_uif_rating_star',
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
		<?php $this->textarea( $feedback_name, $feedback_value, $feedback_placeholder, 'normal', array( 'ipt_uif_smiley_rating_feedback' ) ); ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	public function rating( $name, $value, $max, $required, $style = 'star', $low = '', $high = '' ) {
		$value = (string) $value;
?>
<div class="ipt_uif_rating ipt_uif_rating_<?php echo esc_attr( $style ); ?>">
	<?php if ( $low != '' ) : ?>
	<h6 class="ipt_uif_rating_heading ipt_uif_rating_label_low"><span class="ipt_uif_span"><?php echo $low; ?></span></h6>
	<?php endif; ?>
	<?php for ( $i = 1; $i <= (int) $max; $i++ ) : ?>
	<?php $id = $this->generate_id_from_name( $name ) . '_' . $i; ?>
	<input<?php echo ( $value === (string) $i ) ? ' checked="checked"' : ''; ?>
		type="radio"
		class="<?php if ( $required ) : ?>check_me validate[required]<?php endif; ?> ipt_uif_radio"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $i; ?>" />
	<label for="<?php echo $id; ?>"></label>
	<?php endfor; ?>
	<?php if ( $high != '' ) : ?>
	<h6 class="ipt_uif_rating_heading ipt_uif_rating_label_high"><span class="ipt_uif_span"><?php echo $high; ?></span></h6>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Prints a group of checkbox items for a single HTML name
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *      'disabled' => true|false,//optional
	 *      'data' => array('key' => 'value'[,...]), //optional HTML 5 data attributes inside an associative array
	 *  )
	 * @param string  $checked     The value of the checked item
	 * @param array   $validation  Array of the validation clauses
	 * @param int     $column      Number of columns 1|2|3|4s
	 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param bool    $disabled    Set TRUE if all the items are disabled
	 * @return void
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
		class="<?php echo trim( $validation_class ); ?> ipt_uif_checkbox <?php echo ( isset( $item['class'] ) ? $item['class'] : '' ); ?>"
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
		// Enqueue
		wp_enqueue_style( 'select2-css', $this->static_location . 'css/select2.min.css', array(), $this->version );
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
			echo '<select class="' . implode( ' ', $classes ) . '" name="' . esc_attr( trim( $name ) ) . '" id="' . $id . '" ' . $this->convert_state_to_attribute( ( $disabled == true ) ? 'disabled' : '' ) . ( $multiple ? ' multiple="multiple"' : '' ) . ( '' != $e_label ? 'data-placeholder="' . esc_attr( $e_label ) . '" data-allow-clear="true"' : '' ) . '>';
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
	 * Thumbnail Selector
	 *
	 * Emulates radio or checkboxes with nicely rendered images as labels
	 *
	 * @param      string   $name         The HTML name of the radio group
	 * @param      array    $items        Associative array of all the radio
	 *                                    items. array( 'value' => '', 'label'
	 *                                    => '', 'image' => '', 'disabled' =>
	 *                                    true|false,//optional 'data' =>
	 *                                    array('key' => 'value'[,...]),
	 *                                    //optional HTML 5 data attributes
	 *                                    inside an associative array )
	 * @param      string   $checked      The value of the checked item
	 * @param      boolean  $multi        If true, then render checkboxes,
	 *                                    otherwise radios
	 * @param      array    $validation   Array of the validation clauses
	 * @param      int      $width        Width in pixels
	 * @param      int      $height       Height in pixels
	 * @param      boolean  $caption      Whether or not to show caption
	 * @param      bool     $conditional  Whether the group represents
	 *                                    conditional questions. This will wrap
	 *                                    it inside a conditional div which will
	 *                                    be fired using jQuery. It does not
	 *                                    populate or create anything inside the
	 *                                    conditional div. The id of the
	 *                                    conditional divs should be given
	 *                                    inside the data value of the items in
	 *                                    the form condID => 'ID_OF_DIV'
	 * @param      bool     $disabled     Set TRUE if all the items are disabled
	 * @param      int      $icon         Number of columns 1|2|3|4
	 * @param      boolean  $tooltip      The tooltip
	 * @param      string   $appearance   The appearance
	 * @return     void
	 */
	public function thumbnail_select( $name, $items, $checked, $multi = false, $validation = false, $width = 100, $height = 100, $caption = true, $conditional = false, $disabled = false, $icon = 0xe18e, $tooltip = true, $appearance = 'normal' ) {
		if ( !is_array( $items ) || empty( $items ) ) {
			return;
		}

		if ( $multi == false ) {
			unset( $validation['filters'] );
		}

		$validation_class = $this->convert_validation_class( $validation );

		if ( !is_array( $checked ) ) {
			$checked = (array) $checked;
		}

		$id_prefix = $this->generate_id_from_name( $name );

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$icon_attr = '';
		if ( $icon != '' && $icon != 'none' ) {
			$icon_attr = ' data-labelcon="&#x' . dechex( $icon ) . ';"';
		}

		foreach ( (array) $items as $item ) :
		$data = isset( $item['data'] ) ? $item['data'] : '';
		$data_attr = $this->convert_data_attributes( $data );
		$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
		$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
		$wh_attr = '';
		$swh_attr = '';
		if ( $width != '' ) {
			$wh_attr .= ' width="' . $width . '"';
			$swh_attr .= ' width:' . $width . 'px;';
		}
		if ( $height != '' ) {
			$wh_attr .= ' height="' . $height . '"';
			$swh_attr .= ' height:' . $height . 'px;';
		}
		$wrapper_classes = array( 'ipt_uif_thumbselect_wrap', 'ipt_uif_label_column' );
		if ( 'normal' != $appearance ) {
			$wrapper_classes[] = 'ipt-eform-thumbselect-' . $appearance;
		}
?>
<div class="<?php echo implode( ' ', $wrapper_classes ); ?>">
	<input<?php echo in_array( $item['value'], (array) $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="<?php echo ( $multi ? 'checkbox' : 'radio' ); ?>"
		class="<?php echo trim( $validation_class ); ?> ipt_uif_thumbselect ipt_uif_<?php echo ( $multi ? 'checkbox' : 'radio' ); ?>"
		name="<?php echo $name; ?>" id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label for="<?php echo $id; ?>"<?php echo $icon_attr; ?><?php if ( $tooltip ) { ?> title="<?php echo esc_attr( strip_tags( strip_shortcodes( $item['label'] ) ) ); ?>" class="ipt_uif_tooltip"<?php } ?>>
		<div class="ui-widget-content">
			<div class="thumbselect-img-wrapper" style="<?php echo $swh_attr; ?>">
				<img class="ui-widget-content" src="<?php echo esc_attr( $item['image'] ) ?>"<?php echo $wh_attr; ?> style="<?php echo $swh_attr; ?>" />
			</div>
			<?php if ( $caption ) : ?>
			<h5 class="ui-widget-header">
				<?php echo apply_filters( 'ipt_uif_label', $item['label'] ); ?>
			</h5>
			<?php endif; ?>
		</div>
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
	 * Prints a single checkbox item
	 *
	 * @param string  $name        The HTML name of the radio group
	 * @param array   $items       Associative array of all the radio items.
	 *  array(
	 *      'value' => '',
	 *      'label' => '',
	 *  )
	 * @param bool    $checked     TRUE if the item is checked, FALSE otherwise
	 * @param array   $validation  Array of the validation clauses
	 * @param array   $conditional Whether or not it will show some conditional clause. If true, then you must add 'data' key to the item
	 * with the following structure
	 *  array(
	 *      'condid' => 'id of the conditional show wrapper'
	 *  )
	 * @param string  $sep         Separator HTML
	 * @param bool    $disabled    Set TRUE if the item is disabled
	 * @return void
	 */
	public function checkbox( $name, $item, $checked, $validation = false, $conditional = false, $disabled = false, $icon = 0xe18e ) {
		if ( !is_array( $item ) || empty( $item ) ) {
			return;
		}

		if ( true === $checked || $item['value'] === $checked ) {
			$checked = $item['value'];
		} else {
			$checked = false;
		}
		if ( ! isset( $item['class'] ) ) {
			$item['class'] = '';
		}
		$item['class'] .= ' ipt_uif_s_checkbox';

		$this->checkboxes( $name, array( $item ), array( $checked ), $validation, 1, $conditional, $disabled, $icon );
	}

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
			'ipt_uif_rating_star',
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
		<?php $this->textarea( $feedback_name, $feedback_value, $feedback_placeholder, 'normal', array( 'ipt_uif_likedislike_rating_feedback' ) ); ?>
	</div>
	<?php endif; ?>
</div>
		<?php
	}

	/**
	 * Print a Toggle HTML item
	 *
	 * @param string  $name        The HTML name of the toggle
	 * @param string  $on          ON text
	 * @param string  $off         OFF text
	 * @param bool    $checked     TRUE if checked
	 * @param string  $value       The HTML value of the toggle checkbox (Optional, default to '1')
	 * @param bool    $disabled    True to make it disabled
	 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
	 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
	 * The id of the conditional divs should be given inside the data value of the items in the form
	 * condID => 'ID_OF_DIV'
	 * @param array   $data        HTML 5 data attributes in the form
	 * array('key' => 'value'[,...])
	 */
	public function toggle( $name, $on, $off, $checked, $value = '1', $disabled = false, $conditional = false, $data = array() ) {
		if ( '' == trim( $on ) ) {
			$on = __( 'On' );
		}
		if ( '' == trim( $off ) ) {
			$off = __( 'Off' );
		}

		if ( $conditional == true ) {
			echo '<div class="ipt_uif_conditional_input">';
		}

		$id = $this->generate_id_from_name( $name );
?>
<input<?php echo $this->convert_data_attributes( $data ); ?> type="checkbox"<?php echo $this->convert_state_to_attribute( $disabled == true ? 'disabled' : '' ); ?><?php if ( $checked ) : ?> checked="checked"<?php endif; ?> class="ipt_uif_switch" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $value ); ?>" />
<label for="<?php echo $id; ?>" data-on="<?php echo $on; ?>" data-off="<?php echo $off; ?>"></label>
		<?php

		if ( $conditional == true ) {
			echo '</div>';
		}
		$this->clear();
	}

	public function creditcard( $name_prefix, $values, $placeholders ) {
		$validation = array(
			'required' => true,
			'funccall' => 'iptUIFValidateCC',
		);
		$id = $this->generate_id_from_name( $name_prefix );
		$card = array(
			'formSelectors' => array(
				'numberInput' => 'input#' . $this->generate_id_from_name( $name_prefix . '[number]' ),
				'expiryInput' => 'input#' . $this->generate_id_from_name( $name_prefix . '[expiry]' ),
				'cvcInput' => 'input#' . $this->generate_id_from_name( $name_prefix . '[cvc]' ),
				'nameInput' => 'input#' . $this->generate_id_from_name( $name_prefix . '[name]' ),
			),
			'messages' => $this->default_messages['ccmsg'],
			'placeholders' => $this->default_messages['ccpl'],
		);

		?>
<div class="ipt_uif_card_holder" data-config="<?php echo esc_attr( json_encode( (object) $card ) ); ?>">
	<input type="hidden" name="<?php echo $name_prefix; ?>[ctype]" value="<?php echo esc_attr( $values['ctype'] ); ?>" class="ipt_uif_cc_type" />
	<?php $this->column_head( '', 'half', false, array( 'ipt_uif_card_wrap' ) ); ?>
	<div class="ipt_uif_card">

	</div>
	<?php $this->column_tail(); ?>


	<?php $this->column_head( '', 'half', false ); ?>

	<?php $this->column_head( '', 'full', true, array('no_margin_right') ); ?>
	<?php $this->text( $name_prefix . '[number]', $values['number'], $placeholders['number'], 'credit-card', 'normal', array( 'ipt_uif_cc_number ipt_fsqm_sayt_exclude' ), $validation, false, array( 'autocomplete' => 'cc-number', 'type' => 'tel' ) ); ?>
	<?php $this->column_tail(); ?>
	<?php $this->column_head( '', 'full', true, array('no_margin_right') ); ?>
	<?php $this->text( $name_prefix . '[name]', $values['name'], $placeholders['name'], 'user', 'normal', array( 'ipt_uif_cc_name ipt_fsqm_sayt_exclude' ), $validation ); ?>
	<?php $this->column_tail(); ?>
	<?php $this->column_head( '', 'half', true, array('no_margin_right') ); ?>
	<?php $this->text( $name_prefix . '[expiry]', $values['expiry'], $placeholders['expiry'], 'calendar', 'normal', array( 'ipt_uif_cc_expiry ipt_fsqm_sayt_exclude' ), $validation, false, array( 'autocomplete' => 'cc-exp', 'type' => 'tel' ) ); ?>
	<?php $this->column_tail(); ?>
	<?php $this->column_head( '', 'half', true, array('no_margin_right') ); ?>
	<?php $this->text( $name_prefix . '[cvc]', $values['cvc'], $placeholders['cvc'], 'lock', 'normal', array( 'ipt_uif_cc_cvc ipt_fsqm_sayt_exclude' ), $validation, false, array( 'autocomplete' => 'off', 'type' => 'tel' ) ); ?>
	<?php $this->column_tail(); ?>

	<?php $this->column_tail(); ?>
</div>
		<?php
	}

	public function coupon( $name, $placeholder, $button, $data, $formula, $mname, $micon, $msuffix, $mprecision, $moptions, $mnoanim ) {
		?>
<div class="ipt_uif_coupon" data-config="<?php echo esc_attr( json_encode( (object) $data ) ); ?>">
	<?php
	$this->column_head( '', 'half', false );
	$this->text( $name, '', $placeholder, 'tag2', 'normal', array( 'ipt_uif_coupon_text', 'ipt_fsqm_sayt_exclude' ) );
	$this->column_tail();

	$this->column_head( '', 'half', true );
	$this->button( $button, '', 'small', 'primary', 'normal', array( 'ipt_uif_coupon_button' ), 'button', false, array(), array(), '', 'none', $icon_position = 'before' );
	echo '<span class="ipt_uif_coupon_message"></span>';
	$this->column_tail();

	$this->column_head( '', 'full', false, array( 'ipt_uif_coupon_final' ) );
	echo '<div class="ipt_uif_fancy_container">';
	$this->mathematical( $mname, '', $formula, false, $micon, __( 'Discounted amount', 'ipt_fsqm' ), $msuffix, $mprecision, $moptions, 'ipt_uif_coupon_elem', $mnoanim );
	echo '<div class="clear"></div></div>';
	$this->column_tail();
	?>
</div>
		<?php
	}

	/**
	 * Generate input type text HTML
	 *
	 * @param string  $name        HTML name of the text input
	 * @param string  $value       Initial value of the text input
	 * @param string  $placeholder Default placeholder
	 * @param string  $size        Size of the text input
	 * @param string  $state       readonly or disabled state
	 * @param array   $classes     Array of additional classes
	 * @param array   $validation  Associative array of all validation clauses @see IPT_Plugin_UIF_Admin::convert_validation_class
	 * @param array   $data        HTML 5 data attributes in associative array @see IPT_Plugin_UIF_Admin::convert_data_attributes
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

		$data_attr = $this->convert_data_attributes( $data );
		$html_attr = $this->convert_html_attributes( $attr );
		$div_class = array( 'ipt_uif_icon_and_form_elem_holder' );
		if ( $icon == 'none' || empty( $icon ) ) {
			$div_class[] = 'ipt_uif_text_no_icon';
		}
		$maxlength = '';
		if ( is_array( $validation ) && isset( $validation['filters']['maxSize'] ) ) {
			$maxlength = $validation['filters']['maxSize'];
		}
?>
<div class="<?php echo implode( ' ', $div_class ); ?>">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	<?php echo $html_attr; ?>
	<?php if ( ! isset( $attr['type'] ) ) : ?>
	type="text"
	<?php endif; ?>
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	maxlength="<?php echo esc_attr( $maxlength ); ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( $icon ); ?>
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
	 * @param      boolean  $attr         The attribute
	 * @param      boolean  $icon         The icon
	 * @param      string  $size   Size of the text input
	 * @see        IPT_Plugin_UIF_Admin::convert_validation_class
	 * @see        IPT_Plugin_UIF_Admin::convert_data_attributes
	 */
	public function textarea( $name, $value, $placeholder, $state = 'normal', $classes = array(), $validation = false, $data = false, $attr = false, $icon = false ) {
		$id = $this->generate_id_from_name( $name );
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_textarea';
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
?>
<textarea class="<?php echo implode( ' ', $classes ); ?>"
		  rows="4"
	<?php echo $data_attr; ?>
	<?php echo $html_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	maxlength="<?php echo esc_attr( $maxlength ); ?>"
	id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
		<?php
	}

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
		$this->textarea( $name_prefix . '[value]', $value, $placeholder, 'normal', $classes, false, $trum_data );
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
			$this->question_container( $name_prefix . '[bio]', $bio_title, '', array( array( $this, 'textarea' ), array( $name_prefix . '[bio]', $bio_value, '', 'normal', array(), array( 'required' => true ) ) ), true, false, true, '', array( 'ipt-eform-guestpost-bio' ) );
		}
	}

	public function anchor_button( $text, $href, $target = '_self', $size = 'medium', $icon = 'none' ) {
?>
<a target="<?php echo esc_attr( $target ); ?>" class="ipt_uif_anchor_button <?php echo esc_attr( $size ); ?>" href="<?php echo esc_url( $href ); ?>"><?php $this->print_icon_by_data( $icon ); ?><?php echo $text; ?></a>
		<?php
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

		// Enqueue
		wp_enqueue_style( 'select2-css', $this->static_location . 'css/select2.min.css', array(), $this->version );
		// Compatibility with WooCommerce
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
		$output .= ' ipt_uif_select">';

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
<div class="ipt_uif_icon_and_form_elem_holder ipt-eform-password">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="password"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
</div>
<?php if ( $confirm !== false ) : ?>
<div class="ipt_uif_icon_and_form_elem_holder ipt-eform-password">
<input class="ipt_uif_text ipt_uif_password ipt_uif_password_confirm check_me validate[equals[<?php echo $id; ?>]]"
	type="password"
	placeholder="<?php echo $confirm; ?>"
	name="<?php echo esc_attr( $name_prefix ); ?>[confirm]"
	id="<?php echo $this->generate_id_from_name( $name_prefix . '[confirm]' ); ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
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
<div class="ipt_uif_icon_and_form_elem_holder ipt-eform-password">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	type="password"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
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
<div class="ipt_uif_icon_and_form_elem_holder ipt-eform-password">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text ipt_uif_password"
	autocomplete="off"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
	type="password"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'eye', true, array(), $this->default_messages['pwd_reveal'] ); ?>
</div>
			<?php
		} else {
			if ( $multiline ) {
?>
<textarea class="<?php echo implode( ' ', $classes ); ?> ipt_uif_textarea"
		  rows="4"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
				<?php
			} else {
?>
<div class="ipt_uif_icon_and_form_elem_holder">
<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
	<?php echo $data_attr; ?>
	<?php echo $this->convert_state_to_attribute( $state ); ?>
	data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>"
	type="text"
	placeholder="<?php echo esc_attr( $placeholder ); ?>"
	name="<?php echo esc_attr( $name ); ?>"
	id="<?php echo $id; ?>"
	value="<?php echo esc_textarea( $value ); ?>" />
<?php $this->print_icon_by_class( 'keyboard' ); ?>
</div>
				<?php
			}
		}
	}

	public function mathematical( $name, $value, $formula, $editable = false, $icon = 0xe074, $prefix = '', $suffix = '', $precision = 2, $options = false, $classes = array(), $noanim = false, $hidden = false ) {
		// Enqueue
		wp_enqueue_script( 'js-expression-evaluator', $this->static_location . 'js/parser.js', array(), $this->version, true );

		// Get the HTML ID
		$id = $this->generate_id_from_name( $name );

		// Set the precision to a float
		// If it is not set to auto, i.e, empty string
		if ( '' != $precision ) {
			$precision = (float) $precision;
			$value = round( $value, $precision );
		}

		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}

		$classes[] = 'ipt_uif_richtext';
		$classes[] = 'ipt_uif_mathematical';

		if ( $hidden ) {
			$classes[] = 'ipt_uif_mathematical_hidden';
		}
		?>
<div class="<?php echo esc_attr( implode( ' ', array_unique( $classes ) ) ); ?>">

	<?php if ( true == $hidden ) : ?>
		<input name="<?php echo esc_attr( $name ); ?>" type="hidden" data-sayt-exclude class="ipt_uif_mathematical_input" data-precision="<?php echo $precision; ?>" data-formula="<?php echo esc_attr( $formula ); ?>" value="<?php echo esc_attr( $value ); ?>" data-options="<?php echo esc_attr( json_encode( (object) $options ) ); ?>" data-noanim="true" />
	<?php else : ?>
		<?php $this->print_icon( $icon, false ); ?>
		<?php echo $prefix; ?>
		<?php if ( $editable == true ) : ?>
			<input name="<?php echo esc_attr( $name ); ?>" type="text" class="ipt_uif_mathematical_input ipt_uif_text check_me validate[custom[number]]" data-precision="<?php echo $precision; ?>" data-formula="<?php echo esc_attr( $formula ); ?>" value="<?php echo esc_attr( $value ); ?>" data-options="<?php echo esc_attr( json_encode( (object) $options ) ); ?>" data-noanim="<?php echo ( $noanim ? 'true' : 'false' ); ?>" />
		<?php else : ?>
			<input name="<?php echo esc_attr( $name ); ?>" type="hidden" data-sayt-exclude class="ipt_uif_mathematical_input" data-precision="<?php echo $precision; ?>" data-formula="<?php echo esc_attr( $formula ); ?>" value="<?php echo esc_attr( $value ); ?>" data-options="<?php echo esc_attr( json_encode( (object) $options ) ); ?>" data-noanim="<?php echo ( $noanim ? 'true' : 'false' ); ?>" />
			<span class="ipt_uif_mathematical_span"><?php echo esc_attr( $value ); ?></span>
		<?php endif; ?>
		<?php echo $suffix; ?>
	<?php endif; ?>


</div>
		<?php
	}

	public function address( $name_prefix, $values, $placeholders, $validation = false, $preset_country = '' ) {
		$other_validation = array(
			'required' => $validation['required'],
		);
		if ( '' != $placeholders['recipient'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-recipient' ) );
			$this->text( $name_prefix . '[recipient]', $values['recipient'], $placeholders['recipient'], 'user', 'normal', array(), $validation );
			$this->column_tail();
		}

		if ( '' != $placeholders['line_one'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-line-one' ) );
			$this->text( $name_prefix . '[line_one]', $values['line_one'], $placeholders['line_one'], 'address-book', 'normal', array(), $other_validation );
			$this->column_tail();
		}

		if ( '' != $placeholders['line_two'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-line-two' ) );
			$this->text( $name_prefix . '[line_two]', $values['line_two'], $placeholders['line_two'], 'address-book', 'normal', array(), $other_validation );
			$this->column_tail();
		}

		if ( '' != $placeholders['line_three'] ) {
			$this->column_head( '', 'full', false, array( 'ipt-eform-address-line-three' ) );
			$this->text( $name_prefix . '[line_three]', $values['line_three'], $placeholders['line_three'], 'address-book', 'normal', array(), false );
			$this->column_tail();
		}

		$cpz_actives = 0;
		if ( '' != $placeholders['country'] ) {
			$cpz_actives++;
		}
		if ( '' != $placeholders['province'] ) {
			$cpz_actives++;
		}
		if ( '' != $placeholders['zip'] ) {
			$cpz_actives++;
		}
		$cpz_column = 'third';
		if ( 2 == $cpz_actives ) {
			$cpz_column = 'half';
		}
		if ( 1 == $cpz_actives ) {
			$cpz_column = 'full';
		}


		if ( '' != $placeholders['country'] ) {
			$this->column_head( '', $cpz_column, false, array( 'ipt-eform-address-country' ) );
			$this->autocomplete( $name_prefix . '[country]', $values['country'], $placeholders['country'], array(), 'flag', 'normal', array(), $other_validation );
			$this->column_tail();
		}
		if ( '' != $placeholders['province'] ) {
			$this->column_head( '', $cpz_column, false, array( 'ipt-eform-address-province' ) );
			$this->autocomplete( $name_prefix . '[province]', $values['province'], $placeholders['province'], array(), 'map-signs', 'normal', array(), $other_validation, array(
					'preset-country' => $preset_country,
				) );
			$this->column_tail();
		}
		if ( '' != $placeholders['zip'] ) {
			$this->column_head( '', $cpz_column, false, array( 'ipt-eform-address-zip' ) );
			$this->text( $name_prefix . '[zip]', $values['zip'], $placeholders['zip'], 'location', 'normal', array(), array(
				'required' => $validation['required'],
				'filters' => array(
					'type' => 'number',
				),
			) );
			$this->column_tail();
		}
		$this->clear();
	}

	public function autocomplete( $name, $value, $placeholder, $autocomplete, $icon = 'pencil', $state = 'normal', $classes = array(), $validation = false, $data_attr = false ) {
		$data = array(
			'autocomplete' => json_encode( (array) $autocomplete ),
		);
		if ( is_array( $data_attr ) ) {
			$data = array_merge( $data, $data_attr );
		}
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_autocomplete';
		$this->text( $name, $value, $placeholder, $icon, $state, $classes, $validation, $data );
	}

	/**
	 * Generate more than a single button inside a single container.
	 *
	 * @param array   $buttons           Associative array of all button elements. See ::button to find more.
	 * @param string  $container_id      The HTML ID of the container (Optional)
	 * @param array   $container_classes Additional Classes of the container (Optional)
	 * @return type
	 */
	public function buttons( $buttons, $container_id = '', $container_classes = '' ) {
		if ( !is_array( $buttons ) || empty( $buttons ) ) {
			$this->msg_error( 'Please pass a valid arrays to the <code>IPT_Plugin_UIF_Front::buttons</code> method' );
			return;
		}

		$id_attr = '';
		if ( '' != trim( $container_id ) ) {
			$id_attr = ' id="' . esc_attr( trim( $container_id ) ) . '"';
		}

		if ( !is_array( $container_classes ) ) {
			$container_classes = (array) $container_classes;
		}
		$container_classes[] = 'ipt_uif_button_container';

		echo "\n" . '<div' . $id_attr . ' class="' . implode( ' ', $container_classes ) . '">' . "\n";

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

		echo "\n" . '<div class="clear"></div></div>' . "\n";
	}

	public function print_button( $id, $text ) {
?>
<div class="ipt_uif_button_container">
	<button class="ipt_uif_button ipt_uif_printelement" data-printid="<?php echo esc_attr( $id ); ?>"><span class="button-icon ipt-icomoon-print"></span> <?php echo $text; ?></button>
</div>
		<?php
	}

	/**
	 * Generates a single button
	 *
	 * @param string  $text      The text of the button
	 * @param string  $name      HTML name. ID is generated automatically (unless name is an array, ID is identical to name).
	 * @param string  $size      Size large|medium|small
	 * @param string  $style     Style primary|ui
	 * @param string  $state     HTML state normal|readonly|disabled
	 * @param array   $classes   Array of additional classes
	 * @param string  $type      The HTML type of the button button|submit|reset|anchor
	 * @param bool    $container Whether or not to print the container.
	 * @param array   $data HTML5 data attributes
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
<div class="ipt_uif_button_container">
<?php endif; ?>
	<<?php echo $tag; ?><?php echo $type_attr . $data_attr . $html_atts; ?> class="ipt_uif_button <?php echo implode( ' ', $classes ); ?>"<?php echo $name_id_attr . $state_attr; ?>><?php echo $text; ?></<?php echo $tag; ?>>
<?php if ( true == $container ) : ?>
</div>
<?php endif; ?>
		<?php
	}

	/**
	 * Generate a spinner to select numerical value
	 *
	 * @param string  $name        HTML name
	 * @param string  $value       Initial value of the range
	 * @param string  $placeholder HTML placeholder
	 * @param int     $min         Minimum of the range
	 * @param int     $max         Maximum of the range
	 * @param int     $step        spinner move step
	 */
	public function spinner( $name, $value, $placeholder = '', $min = '', $max = '', $step = 1, $required = false ) {
		$validation = array(
			'required' => $required,
			'filters' => array(
				'type' => 'number',
				'min' => $min,
				'max' => $max,
			),
		);
		$validation_attr = $this->convert_validation_class( $validation );
		if ( '' == $step || ! is_numeric( $step ) ) {
			$step = 'any';
		}
?>
<input type="number" placeholder="<?php echo $placeholder; ?>" class="ipt_uif_text code ipt_uif_uispinner <?php echo esc_attr( $validation_attr ); ?>" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

	/**
	 * Generate a horizontal slider to select between numerical values
	 *
	 * @param string  $name       HTML name
	 * @param string  $value      Initial value of the range
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
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
	<input data-floats="<?php echo $floats; ?>" data-nomin="<?php echo $nomin; ?>" data-show-count="<?php echo $show_count; ?>" data-labels="<?php echo esc_attr( json_encode( (object) $label_data ) ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>" type="text" class="ipt_uif_slider check_me validate[funcCall[iptUIFSliderVal]]" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" data-vertical="<?php echo $vertical; ?>" data-height="<?php echo $height; ?>" />
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
	<input data-floats="<?php echo $floats; ?>" data-nomin="<?php echo $nomin; ?>" data-show-count="<?php echo $show_count; ?>" data-labels="<?php echo esc_attr( json_encode( (object) $label_data ) ); ?>" data-prefix="<?php echo esc_attr( $prefix ); ?>" data-suffix="<?php echo esc_attr( $suffix ); ?>" type="text" class="ipt_uif_slider slider_range check_me validate[funcCall[iptUIFSliderVal]]" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $names[0] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[0] ); ?>" value="<?php echo esc_attr( $value_min ); ?>" data-vertical="<?php echo $vertical; ?>" data-height="<?php echo $height; ?>" />
	<input type="text" class="ipt_uif_slider_range_max" name="<?php echo esc_attr( trim( $names[1] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[1] ); ?>" value="<?php echo esc_attr( $value_max ); ?>" />
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
	 * Populates multiple sliders or ranges
	 *
	 * @param string  $name       The name of slider
	 * @param array   $sliders    An associative array of the sliders
	 * array(
	 *      'type' => 'range' | 'single',
	 *      'name' => 'HTML Name',
	 *      'value' => int|array(min,max),
	 *      'title' => 'Title or Label',
	 * )
	 * @param bool    $show_count Whether or not to show the count
	 * @param int     $min        Minimum of the range
	 * @param int     $max        Maximum of the range
	 * @param int     $step       Slider move step
	 */
	public function sliders( $name, $sliders, $show_count = false, $min = 0, $max = 100, $step = 1, $labels = array(), $nomin = false, $floats = true, $vertical = false, $vheight = 300 ) {
		foreach ( $sliders as $slider ) {
			$params = array( $slider['name'], $slider['value'], $show_count, $min, $max, $step, $slider['prefix'], $slider['suffix'], $labels, $nomin, $floats, $vertical, $vheight );
			// override the min, max, step if present in config
			foreach ( array( 3 => 'min', 4 => 'max', 5 => 'step' ) as $key => $val ) {
				if ( isset( $slider[$val] ) && $slider[$val] != '' ) {
					$params[$key] = $slider[$val];
				}
			}
			// Add the floats
			if ( $slider['type'] == 'range' ) {
				$callback = array( array( $this, 'slider_range' ), $params );
			} else {
				$callback = array( array( $this, 'slider' ), $params );
			}
			$this->question_container( $name, $slider['title'], '', $callback, false );
		}
	}

	public function spinners( $spinners ) {
		foreach ( $spinners as $spinner ) {
			$params = array( $spinner['name'], $spinner['value'], $spinner['placeholder'], $spinner['min'], $spinner['max'], $spinner['step'], $spinner['required'] );
			$this->question_container( $spinner['name'], $spinner['title'], '', array( array( $this, 'spinner' ), $params ), false );
		}
	}

	public function ratings( $ratings, $style ) {
		foreach ( $ratings as $rating ) {
			$params = array( $rating['name'], $rating['value'], $rating['max'], $rating['required'], $style, $rating['labels']['low'], $rating['labels']['high'] );
			$this->question_container( $rating['name'], $rating['title'], '', array( array( $this, 'rating' ), $params ), false );
		}
	}

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
	<table class="ipt_uif_matrix">
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
	 * Matrix Dropdown Element
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
			$e_label = $items[0]['label'];
			if ( $multiple ) {
				array_shift( $items );
			}
		}
		?>
<div class="ipt_uif_matrix_container ipt_uif_matrix_select">
	<table class="ipt_uif_matrix">
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

	public function matrix_text( $name_prefix, $rows, $columns, $values, $multiline, $validation, $icon = 0xe18e ) {
		if ( ! is_array( $values ) ) {
			$values = (array) $values;
		}
		?>
<div class="ipt_uif_matrix_container ipt_uif_matrix_feedback">
	<table class="ipt_uif_matrix">
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
					<?php $this->textarea( $name, $values[$r_key][$c_key], $column, 'normal', array( 'ipt_uif_matrix_text' ), $validation ); ?>
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
		<a class="ipt_uif_sorting_handle" href="javascript:;"><?php $this->print_icon_by_class( 'unsorted' ); ?></a>
		<input type="hidden" data-sayt-exclude name="<?php echo $name_prefix; ?>" value="<?php echo $key; ?>" />
		<?php echo apply_filters( 'ipt_uif_label', $items[$key]['label'] ); ?>
	</div>
	<?php endforeach; ?>
</div>
		<?php
	}

	/**
	 * Generates a simple jQuery UI Progressbar
	 * Minumum value is 0 and maximum is 100.
	 * So always calculate in percentage.
	 *
	 * @param string  $id      The HTML ID
	 * @param numeric $start   The start value
	 * @param array   $classes Additional classes
	 */
	public function progressbar( $id = '', $start = 0, $classes = array(), $decimals = 2 ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_progress_bar';
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( $id ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>" data-start="<?php echo $start; ?>" data-decimals="<?php echo esc_attr( $decimals ); ?>"<?php echo $id_attr; ?>>
	<div class="ipt_uif_progress_value"><span class="eform-progress-value-span"></span></div>
</div>
		<?php
	}

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

		if ( '' == $value ) {
			$classes[] = 'is-empty';
		}

		if ( $hide_icon ) {
			$icon = 'none';
		}

		$data = array(
			'dateFormat' => $date_format,
			'timeFormat' => $time_format,
		);

		$data = array_merge( $data, $data_attr );
		echo '<div class="eform-dp-input-field">';
		$this->text( $name, $value, $placeholder, $icon, $state, $classes, $validation, $data );
		?><a href="javascript:;" class="eform-dp-clear" title="<?php echo __( 'Clear', 'ipt_fsqm' ); ?>">&times;</a><?php
		echo '</div>';
	}

	public function hiddens( $hiddens, $name_prefix = '' ) {
		if ( !is_array( $hiddens ) || empty( $hiddens ) ) {
			return;
		}
?>
<?php foreach ( $hiddens as $h_key => $h_val ) : ?>
<?php $name = $name_prefix != '' ? $name_prefix . '[' . $h_key . ']' : $h_key; ?>
<input type="hidden" data-sayt-exclude name="<?php echo $name; ?>" value="<?php echo $h_val; ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" />
<?php endforeach; ?>
		<?php
	}

	public function checkbox_toggler( $id, $label, $selector, $checked = false ) {
		$id = esc_attr( trim( $id ) );
?>
<input<?php echo true == $checked ? ' checked="checked"' : ''; ?>
	data-selector="<?php echo esc_attr( $selector ); ?>"
	type="checkbox"
	class="ipt_uif_checkbox ipt_uif_checkbox_toggler"
	id="<?php echo $id; ?>" />
<label for="<?php echo $id; ?>">
	 <?php echo apply_filters( 'ipt_uif_label', $label ); ?>
</label>
		<?php
	}

	public function locationpicker( $name_prefix, $values, $manual_control, $labels, $description = '', $error = '', $loader = 'Locating', $radius = 500, $zoom = 15, $scrollwheel = true, $show_ui = true, $required = false, $api = '' ) {
		// Enqueue all scripts
		// The reason why we are calling it from here is
		// It would be overhead to load location API from google
		// if it is not needed
		wp_enqueue_script( 'google-maps-api', '//maps.google.com/maps/api/js?libraries=places&key=' . $api, array(), $this->version, true );
		wp_enqueue_script( 'jquery-locationpicker', $this->static_location . 'js/locationpicker.jquery.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-geolocation', $this->static_location . 'js/jquery.geolocation.min.js', array( 'jquery' ), $this->version, true );
		$values = wp_parse_args( $values, array(
			'lat' => '',
			'long' => '',
			'location_name' => '',
		) );
		$labels = wp_parse_args( $labels, array(
			'lat' => 'Latitude',
			'long' => 'Longitude',
			'location_name' => 'Location',
			'update' => 'Update Location',
			'auto' => 'Auto Update',
			'nolocation' => 'No location data provided',
		) );
		$gps_settings = array(
			'values' => (object) $values,
			'name_prefix' => $name_prefix,
			'ids' => array(
				'latitudeInput' => $this->generate_id_from_name( $name_prefix . '[lat]' ),
				'longitudeInput' => $this->generate_id_from_name( $name_prefix . '[long]' ),
				'locationNameInput' => $this->generate_id_from_name( $name_prefix . '[location_name]' ),
			),
			'radius' => (float) $radius,
			'zoom' => (float) $zoom,
			'scrollwheel' => $scrollwheel,
			'manualcontrol' => $manual_control,
			'showUI' => $show_ui,
			'nolocationLabel' => $labels['nolocation'],
		);
		$autoupdateid = $this->generate_id_from_name( $name_prefix . '[autoupdate]' );
		?>
<div class="ipt_uif_locationpicker" data-gps-settings="<?php echo esc_attr( json_encode( (object) $gps_settings ) ); ?>">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="locationpicker-description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<?php endif; ?>
	<div class="locationpicker-maps ui-state-active">
		<div class="locationpicker-maps-control"></div>
		<div class="locationpicker-maps-locating">
			<?php $this->ajax_loader( false, '', array(), true, $loader ); ?>
			<?php $this->clear(); ?>
		</div>
		<div class="location-maps-error"><p><?php echo $error; ?></p></div>
	</div>
	<?php if ( $show_ui ) : ?>
	<?php if ( $manual_control ) : ?>
	<div class="locationpicker-widget ui-widget-content">
		<?php $this->column_head( '', 'half', true ); ?>
		<?php $this->text( $name_prefix . '[location_name]', $values['location_name'], $labels['location_name'], 'search2', 'normal', array( 'ipt_uif_tooltip' ), $required, false, array( 'title' => $labels['location_name'] ) ); ?>
		<?php $this->column_tail(); ?>
		<?php $this->column_head( '', 'forth', true ); ?>
		<?php $this->text( $name_prefix . '[lat]', $values['lat'], $labels['lat'], 'location', 'normal', array( 'ipt_uif_tooltip' ), $required, false, array( 'title' => $labels['lat'] ) ); ?>
		<?php $this->column_tail(); ?>
		<?php $this->column_head( '', 'forth', true ); ?>
		<?php $this->text( $name_prefix . '[long]', $values['long'], $labels['long'], 'location', 'normal', array( 'ipt_uif_tooltip' ), $required, false, array( 'title' => $labels['long'] ) ); ?>
		<?php $this->column_tail(); ?>
		<?php $this->clear(); ?>
		<div class="locationpicker-controls">
			<button class="location-update ipt_uif_button"><?php echo $labels['update']; ?></button>
		</div>
		<?php $this->clear(); ?>
	</div>
	<?php else : ?>
	<?php foreach ( $values as $key => $value ) : ?>
	<?php $this->hidden_input( $name_prefix . '[' . $key . ']', $value ); ?>
	<?php endforeach; ?>
	<div class="locationpicker-controls">
		<button class="location-update ipt_uif_button"><?php echo $labels['update']; ?></button>
	</div>
	<?php endif; ?>
	<?php endif; ?>
</div>
		<?php
	}



	/**
	 * Prints a container for BlueIMP Uploader
	 *
	 * @param  string $name        The HTML name of the type="file" input field
	 * @param  string $name_id     The HTML name of the hidden input fields with ID to the uploaded file
	 * @param  array  $labels      Labels of default fields. @see $this->default_messages['uploader']
	 * @param  array  $settings    Array of settings
	 * @param  array  $attributes  An array of other attributes which would be used by JavaScript. This has a preset datatype
	 * @param  array  $form_data   An array of data that is being submitted when uploading or fetching files. This is program specific and can be of any type
	 * @param  string $description Any description text.
	 * @return void
	 */
	public function uploader( $name, $name_id, $settings, $attributes, $form_data, $description = '', $labels = array(), $validation = true, $max_upload_size = 0, $show_ui = true ) {
		$labels = wp_parse_args( (array) $labels, $this->default_messages['uploader'] );

		// Enqueue
		wp_enqueue_style( 'blueimp-fileupload.bundle', $this->static_location . 'css/fileupload.bundle.min.css', array(), $this->version );
		wp_enqueue_script( 'blueimp-fileupload', $this->static_location . 'js/blueimp.fileupload/jquery.file-upload.bundle.min.js', array( 'jquery', 'jquery-ui-widget' ), $this->version, true );

		$settings = wp_parse_args( (array) $settings, array(
			'accept_file_types'    => 'gif,jpeg,png',
			'max_number_of_files'  => '',
			'min_number_of_files'  => '',
			'max_file_size'        => '1000000',
			'min_file_size'        => '1',
			'show_drop_zone'       => true,
			'wp_media_integration' => false,
			'auto_upload'          => false,
			'single_upload'        => false,
			'drag_n_drop'          => true,
			'progress_bar'         => true,
			'preview_media'        => true,
			'can_delete'           => true,
			'required'             => $validation,
			'minimal_ui'           => false,
		) );

		$configuration = array(
			'id' => $this->generate_id_from_name( $name ),
			'upload_url' => '?action=' . $attributes['ajax_upload'],
			'download_url' => '?action=' . $attributes['ajax_download'],
			'do_download' => $attributes['fetch_files'],
		);
		$toggler_id = $configuration['id'] . '_toggler';
		$max_upload_size = ( $max_upload_size == 0 ? $settings['max_file_size'] : $max_upload_size );
		// Change the value if settings is lower
		// Fixes issue #12
		if ( $settings['max_file_size'] < $max_upload_size ) {
			$max_upload_size = $settings['max_file_size'];
		}
		$settings['max_file_size'] = $max_upload_size;
		if ( $validation == true && '' == $settings['min_number_of_files'] ) {
			$settings['min_number_of_files'] = '1';
		}
		$meta = array();
		if ( $max_upload_size > ( 1024 * 1024 ) ) {
			$meta[] = sprintf( __( 'Max file size: %.2f MB.', 'ipt_fsqm' ), ( $max_upload_size / (1024 * 1024 ) ) );
		} else {
			$meta[] = sprintf( __( 'Max file size: %.2f KB.', 'ipt_fsqm' ), ( $max_upload_size / 1024 ) );
		}

		$meta[] = sprintf( __( 'Allowed file types: %s', 'ipt_fsqm' ), $settings['accept_file_types'] );
		if ( $settings['max_number_of_files'] > 0 ) {
			$meta[] = sprintf( _n( 'Max number of file: %d', 'Max number of files: %d', $settings['max_number_of_files'], 'ipt_fsqm' ), $settings['max_number_of_files'] );
		}
		if ( $settings['min_number_of_files'] > 0 ) {
			$meta[] = sprintf( _n( 'Min number of file: %d', 'Min number of files: %d', $settings['min_number_of_files'], 'ipt_fsqm' ), $settings['min_number_of_files'] );
		}
		$colspan = 4;
		// if ( true == $settings['minimal_ui'] ) {
		// 	$colspan = 3;
		// }
		?>
<div class="ipt_uif_uploader" id="<?php echo esc_attr( $configuration['id'] . '_uploader_wrap' ); ?>" data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>" data-configuration="<?php echo esc_attr( json_encode( (object) $configuration ) ); ?>" data-formData="<?php echo esc_attr( json_encode( (object) $form_data ) ); ?>">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="fileupload-description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<?php endif; ?>
	<?php if ( $settings['drag_n_drop'] == true && $show_ui == true ) : ?>
	<div class="fileinput-dragdrop ui-state-active">
		<span><?php echo $labels['dragdrop']; ?></span>
	</div>
	<?php endif; ?>

	<div class="fileupload-meta">
		<p><?php echo implode( ' | ', $meta ); ?></p>
	</div>


	<!-- The table listing the files available for upload/download -->
	<div class="ipt_fsqm_fileuploader_list_wrap">
		<table role="presentation" class="ipt_fsqm_fileuploader_list">
			<?php if ( $show_ui ) : ?>
			<thead>
				<tr>
					<td colspan="<?php echo $colspan; ?>">
						<div class="fileupload-buttonbar">
							<div class="fileupload-buttons">
								<span class="fileinput-button secondary-button small">
									<span class="select secondary-button"><?php echo $labels['select']; ?></span>
									<input class="ipt_uif_uploader_handle" type="file"<?php if ( ! $settings['single_upload'] ) : ?> multiple="multiple"<?php endif; ?> name="<?php echo esc_attr( $name ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" />
								</span>
								<?php if ( $settings['auto_upload'] === false ) : ?>
								<button type="submit" class="start secondary-button small"><?php echo $labels['start']; ?></button>
								<?php endif; ?>
								<?php if ( false == $settings['minimal_ui'] ) : ?>
									<button type="reset" class="cancel secondary-button small"><?php echo $labels['cancel']; ?></button>
								<?php endif; ?>
								<span class="fileupload-process"></span>
							</div>
							<?php if ( $settings['progress_bar'] == true ) : ?>
							<!-- The global progress state -->
							<div class="fileupload-progress fade" style="display:none">
								<!-- The global progress bar -->
								<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
								<!-- The extended global progress state -->
								<div class="progress-extended">&nbsp;</div>
							</div>
							<?php endif; ?>
						</div>
					</td>

					<?php if ( $settings['can_delete'] ) : ?>
					<td class="delete_button">
						<?php if ( false == $settings['minimal_ui'] ) : ?>
							<div class="fileupload-buttonbar">
								<div class="fileupload-buttons">
									<button type="button" class="delete secondary-button small"><?php echo $labels['delete']; ?></button>
								</div>
							</div>
						<?php endif; ?>
					</td>
					<?php if ( false == $settings['minimal_ui'] ) : ?>
						<td class="delete_toggle">
							<div class="fileupload-buttonbar">
								<div class="fileupload-buttons">
									<div class="ipt_uif_label_column">
										<input type="checkbox" class="toggle ipt_uif_checkbox" id="<?php echo $toggler_id; ?>" />
										<label data-labelcon="&#xe18e;" for="<?php echo $toggler_id ?>"></label>
									</div>
								</div>
							</div>
						</td>
					<?php endif;?>
					<?php endif; ?>
				</tr>
			</thead>
			<?php endif; ?>
			<tbody class="files"></tbody>
		</table>
	</div>

	<!-- The template to display files available for upload -->
	<script class="template-upload" id="<?php echo $this->generate_id_from_name( $name ) . '_tmpl_upload'; ?>" type="text/x-tmpl">
	{% for (var i=0, file; file=o.files[i]; i++) { %}
		<tr class="template-upload fade">
			<td>
				<span class="preview"></span>
			</td>
			<td>
				<p class="name">{%=file.name%}</p>
				<strong class="error"></strong>
			</td>
			<td class="fileupload_list_pb">
				<p class="size"><?php echo $labels['processing_singular']; ?></p>
				<div class="progress"></div>
			</td>
			<td colspan="<?php echo ( $settings['can_delete'] == true && false == $settings['minimal_ui'] ? '3' : '1' );  ?>">
				{% if (!i && !o.options.autoUpload) { %}
					<button class="start secondary-button small" disabled><?php echo $labels['start_singular']; ?></button>
				{% } %}
				{% if (!i) { %}
					<button class="cancel secondary-button small"><?php echo $labels['cancel_singular']; ?></button>
				{% } %}
			</td>
		</tr>
	{% } %}
	</script>
	<!-- The template to display files available for download -->
	<script class="template-download" id="<?php echo $this->generate_id_from_name( $name ) . '_tmpl_download'; ?>" type="text/x-tmpl">
	{%
		window.ipt_fsqm_upload_count_global;
		if ( window.ipt_fsqm_upload_count_global == undefined ) {
			window.ipt_fsqm_upload_count_global = 0;
		}
	%}
	{% for (var i=0, file; file=o.files[i]; i++) { %}
	{% var toggler_check_id = window.ipt_fsqm_upload_count_global++; %}
		<tr class="template-download fade">
			<td class="preview_td" colspan="2">
				<span class="preview">
					{% if (file.thumbnailUrl) { %}
						<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}"<?php if ( $settings['preview_media'] == true ) : ?> data-gallery<?php endif; ?>><img src="{%=file.thumbnailUrl%}" /></a>
					{% } else if ( file.validAudio ) { %}
						<?php if ( $settings['preview_media'] == true ) : ?>
						<audio controls="controls">
							<source src="{%=file.url%}" type="{%=file.type%}" />
							<?php _e( 'Your browser does not support audio element.', 'ipt_fsqm' ); ?>
						</audio>
						<?php endif; ?>
					{% } else if ( file.validVideo ) { %}
						<?php if ( $settings['preview_media'] == true ) : ?>
						<video controls="controls" height="100" width="200">
							<source src="{%=file.url%}" type="{%=file.type%}" />
							<?php _e( 'Your browser does not support video element.', 'ipt_fsqm' ); ?>
						</video>
						<?php endif; ?>
					{% } %}
				</span>
			</td>
			<td>
				<p class="name">
					<a href="{%=file.url%}" title="{%=file.name%}" download="{%=file.name%}" {%=file.thumbnailUrl?'<?php if ( $settings['preview_media'] == true ) : ?>data-gallery<?php endif; ?>':''%}>{%=file.name%}</a>
				</p>
				{% if (file.error) { %}
					<div><span class="error"><?php echo $labels['error_singular']; ?></span> {%=file.error%}</div>
				{% } %}
				<input type="hidden" data-sayt-exclude name="<?php echo $name_id; ?>" value="{%=file.id%}" />
			</td>
			<td>
				<span class="size">{%=o.formatFileSize(file.size)%}</span>
			</td>
			<?php if ( $settings['can_delete'] ) : ?>
			<td class="delete_button">
				<button class="delete secondary-button small" data-type="{%=file.deleteType%}" data-url="{%=file.deleteUrl%}"{% if (file.deleteWithCredentials) { %} data-xhr-fields='{"withCredentials":true}'{% } %}><?php echo $labels['delete_singular']; ?></button>
			</td>
			<?php if ( false == $settings['minimal_ui'] ) : ?>
				<td class="delete_toggle">
					<div class="ipt_uif_label_column">
						<input type="checkbox" name="delete" value="1" class="toggle ipt_uif_checkbox" id="<?php echo $toggler_id; ?>_files_{%=toggler_check_id%}" />
						<label data-labelcon="&#xe18e;" for="<?php echo $toggler_id; ?>_files_{%=toggler_check_id%}"></label>
					</div>
				</td>
			<?php endif; ?>
			<?php endif; ?>
		</tr>
	{% } %}
	</script>
</div>
		<?php
	}

	/**
	 * Print a signature Pad
	 *
	 * @param      string  $name         HTML name of the element
	 * @param      string  $data         base30 encoded data
	 * @param      array   $settings     Associative array of settings
	 * @param      string  $description  HTML description
	 * @param      array   $validation   The validation
	 * @param      string  $color        The color
	 */
	public function signature( $name, $data, $settings, $description = '', $validation = array(), $color = '' ) {
		// Enqueue
		wp_enqueue_script( 'jsignature', $this->static_location . 'js/jSignature.min.noconflict.js', array( 'jquery' ), $this->version, true );
		$id = $this->generate_id_from_name( $name );
		$buttons = array();
		// Undo button
		if ( isset( $settings['undo'] ) && $settings['undo'] != '' ) {
			$buttons[] = array(
				'text' => '<i class="ipticm ipt-icomoon-undo2"></i>',
				'name' => '',
				'size' => 'medium',
				'style' => 'primary',
				'state' => 'normal',
				'classes' => array( 'ipt_uif_jsignature_undo', 'ipt_uif_tooltip' ),
				'type' => 'button',
				'data' => array(),
				'atts' => array(
					'title' => $settings['undo'],
				),
				'url' => '',
			);
		}

		// Reset button
		if ( isset( $settings['reset'] ) && $settings['reset'] != '' ) {
			$buttons[] = array(
				'text' => '<i class="ipticm ipt-icomoon-times"></i>',
				'name' => '',
				'size' => 'small',
				'style' => 'ui',
				'state' => 'normal',
				'classes' => array( 'ipt_uif_jsignature_reset', 'ipt_uif_tooltip' ),
				'type' => 'button',
				'data' => array(),
				'atts' => array(
					'title' => $settings['reset'],
				),
				'url' => '',
			);
		}
		$validation_class = $this->convert_validation_class( $validation );

		$style_attr = '';
		if ( '' != $color ) {
			$style_attr = ' style="color: ' . $color . '";';
		}
		?>
<div class="ipt_uif_jsignature">
	<?php if ( '' !== trim( $description ) ) : ?>
	<div class="jsignature_description ipt_uif_richtext">
		<?php echo wpautop( $description ); ?>
	</div>
	<div class="clear"></div>
	<?php endif; ?>
	<input type="text" style="height: 0; width: 0; padding: 0; margin: 0; opacity: 0;" class="ipt_uif_jsignature_input<?php echo $validation_class; ?>" name="<?php echo esc_attr( $name ); ?>" id="<?php echo $id; ?>" value="<?php echo esc_attr( $data ); ?>">
	<div class="ipt_uif_jsignature_pad ui-state-active"<?php echo $style_attr; ?>>

	</div>
	<?php $this->buttons( $buttons ); ?>
</div>
		<?php
	}

	/*==========================================================================
	 * SORTABLE DRAGGABLE & ADDABLE LIST
	 *========================================================================*/

	public function sda_list( $settings, $items, $data, $max_key, $id = '' ) {
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		$default = array(
			'key' => '__SDAKEY__',
			'columns' => array(),
			'features' => array(),
			'labels' => array(),
		);
		$settings = wp_parse_args( $settings, $default );
		$settings['labels'] = wp_parse_args( $settings['labels'], array(
			'add' => __( 'Add New Item', 'ipt_fsqm' ),
		) );

		$settings['features'] = wp_parse_args( $settings['features'], array(
			'draggable' => true,
			'addable' => true,
			'max' => '',
			'hide_label' => false,
			'center_content' => false,
		) );
		$data_total = 0;
		$feature_attr = $this->convert_data_attributes( $settings['features'] );

		if ( $max_key == null && empty( $items ) ) { //No items
			$max_key = 0;
		} else { //Passed the largest key for the items, so should start from the very next key
			$max_key = $max_key + 1;
		}

		$sda_body_classes = array( 'ipt_uif_sda_body' );
		if ( true == $settings['features']['draggable'] || true == $settings['features']['addable'] ) {
			$sda_body_classes[] = 'eform-sda-has-toolbar';
		}

		?>
<div class="ipt-eform-sda ipt_uif_sda" <?php echo $feature_attr; ?> id="<?php echo esc_attr( $id ); ?>">
	<div class="<?php echo implode( ' ', $sda_body_classes ); ?>" data-buttontext="<?php printf( _x( 'please click on %1$s button to get started', 'ipt_uif_sda', 'ipt_fsqm' ), strtoupper( $settings['labels']['add'] ) ); ?>">
		<?php foreach ( $items as $item ) : ?>
			<div class="ipt_uif_sda_elem">
				<?php if ( true == $settings['features']['draggable'] || true == $settings['features']['addable'] ) : ?>
					<div class="ipt-eform-sda-toolbar">
						<?php if ( true == $settings['features']['draggable'] ) : ?>
							<div class="ipt_uif_sda_drag"><i class="ipt-icomoon-bars"></i></div>
						<?php endif; ?>
						<?php if ( true == $settings['features']['addable'] ) : ?>
							<div class="ipt_uif_sda_del"><i class="ipt-icomoon-times"></i></div>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php foreach ( $settings['columns'] as $col_key => $column ) : ?>
					<?php if ( $column['clear'] ) : ?>
						<?php $this->clear( 'left' ); ?>
					<?php endif; ?>
					<?php $this->column_head( '', $column['size'] ); ?>
						<?php $this->question_container( $item[ $col_key ][0], $column['label'], '', array( array( $this, $column['type'] ), (array) $item[ $col_key ] ), $column['required'], false, true, '', array( 'eform-repeatable-qcontainer' ), array(), $settings['features']['hide_label'], $settings['features']['center_content'] ); ?>
					<?php $this->column_tail(); ?>
				<?php endforeach; ?>
				<div class="clear"></div>
			</div>
			<?php $data_total++; ?>
		<?php endforeach; ?>
	</div>

	<script type="text/html" class="ipt_uif_sda_data">
		<?php ob_start(); ?>

		<?php if ( true == $settings['features']['draggable'] || true == $settings['features']['addable'] ) : ?>
			<div class="ipt-eform-sda-toolbar">
				<?php if ( true == $settings['features']['draggable'] ) : ?>
					<div class="ipt_uif_sda_drag"><i class="ipt-icomoon-bars"></i></div>
				<?php endif; ?>
				<?php if ( true == $settings['features']['addable'] ) : ?>
					<div class="ipt_uif_sda_del"><i class="ipt-icomoon-times"></i></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $settings['columns'] as $col_key => $column ) : ?>
			<?php if ( $column['clear'] ) : ?>
				<?php $this->clear( 'left' ); ?>
			<?php endif; ?>
			<?php $this->column_head( '', $column['size'] ); ?>
				<?php $this->question_container( $data[ $col_key ][0], $column['label'], '', array( array( $this, $column['type'] ), (array) $data[ $col_key ] ), $column['required'], false, true, '', array( 'eform-repeatable-qcontainer' ), array(), $settings['features']['hide_label'], $settings['features']['center_content'] ); ?>
			<?php $this->column_tail(); ?>
		<?php endforeach; ?>
		<div class="clear"></div>
		<?php
		$output = ob_get_clean();
		echo htmlspecialchars( $output );
		?>
	</script>

	<?php
	if ( true == $settings['features']['addable'] ) {
		$buttons = array();
		$buttons[] = array(
			$settings['labels']['add'],
			'',
			'small',
			'secondary',
			'normal',
			array( 'ipt_uif_sda_button' ),
			'button',
			array(
				'total' => $data_total,
				'count' => $max_key,
				'key' => $settings['key'],
			),
			array(),
			'',
			'plus',
		);

		$this->buttons( $buttons, '', array( 'ipt_uif_sda_foot' ) );
	}
	?>
	<div class="clear"></div>
</div>
		<?php
	}


	/*==========================================================================
	 * TABS AND BOXES
	 *========================================================================*/
	/**
	 * Generate Tabs with callback populators
	 * Generates all necessary HTMLs. No need to write any classes manually.
	 *
	 * @param array   $tabs Associative array of all the tab elements.
	 * $tab = array(
	 *      'id' => 'ipt_fsqm_form_name',
	 *      'label' => 'Form Name',
	 *      'callback' => 'function',
	 *      'scroll' => false,
	 *      'classes' => array(),
	 *      'has_inner_tab' => false,
	 *  );
	 * @param array   $data The HTML 5 data in forms of key => value
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
	<a href="javascript:;" class="ipt_uif_tabs_toggler ipt_uif_button"><span class="ipt_uif_text_icon_no_bg ipt-icomoon-menu2"></span></a>
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
		<li id="<?php echo $tab['id'] . '_control_li'; ?>"><a href="#<?php echo $tab['id']; ?>"><?php $this->print_icon( $tab['icon'], false ); ?><?php echo $tab['label']; ?> <?php if ( ! empty( $tab['sublabel'] ) ) echo '<span class="ipt_uif_tab_subtitle">' . $tab['sublabel'] . '</span>'; ?></a></li>
		<?php endforeach; ?>
	</ul>
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

	public function column( $callback, $size = 'full', $side_margin = true, $id = '', $additional_classes = array(), $tooltip = '' ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}
		$this->column_head( $id, $size, $side_margin, $additional_classes );
		call_user_func_array( $callback[0], $callback[1] );
		$this->column_tail();
	}

	public function column_head( $id = '', $size = 'full', $side_margin = true, $additional_classes = array(), $tooltip = '' ) {
		$classes = array( 'ipt_uif_column', 'ipt_uif_column_' . esc_attr( $size ), 'ipt_uif_conditional' );
		if ( $size != 'full' ) {
			$classes[] = 'ipt_uif_column_custom';
		}
		if ( '' != $tooltip ) {
			$classes[] = 'ipt_uif_qtooltip';
		}
		$classes = array_unique( array_merge( $classes, (array) $additional_classes ) );
		$id_attr = '';
		$id = trim( $id );
		if ( '' != $id ) {
			$id_attr .= ' id="' . esc_attr( $id ) . '"';
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr; ?><?php echo ( '' != $tooltip ) ? ' title="' . esc_attr( $tooltip ) . '"' : ''; ?>>
	<div class="ipt_uif_column_inner<?php if ( $side_margin ) echo ' side_margin'; ?>">
		<?php
	}

	public function column_tail() {
		$this->clear();
?>
	</div>
</div>
		<?php
	}

	/**
	 * Creates a nice looking container with an icon on top
	 *
	 * @param string  $label   The heading
	 * @param mixed   (array|string) $callback The callback function to populate.
	 * @param string  $icon    The icon. Consult the /static/fonts/fonts.css to pass class name
	 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll. Default is 400.
	 * @param string  $id      HTML ID
	 * @param array   $classes HTML classes
	 * @return type
	 */
	public function container( $callback, $label, $icon = 'none', $collapsible = false, $opened = false, $after = '',  $scroll_top = false, $desc = '', $classes = array() ) {
		if ( !$this->check_callback( $callback ) ) {
			$this->msg_error( 'Invalid Callback supplied' );
			return;
		}

		$this->container_head( $label, $icon, $collapsible, $opened, $after, $classes );
		if ( '' != $desc ) {
			echo '<div class="ipt_fsqm_container_desc">';
			echo wpautop( $desc );
			echo '</div>';
		}
		call_user_func_array( $callback[0], $callback[1] );
		$this->container_tail( $scroll_top );
	}

	public function container_head( $label, $icon = 'none', $collapsible = false, $opened = false, $after = '', $classes = array() ) {
		if ( '' != $after ) {
			$after = '<div class="ipt_uif_float_right">' . $after . '</div>';
		}
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_container';
		if ( $collapsible ) {
			$classes[] = 'ipt_uif_collapsible';
		}
		if ( $icon != 'none' ) {
			$classes[] = 'ipt_uif_iconbox';
		}
		$tmp_icon = (int) $icon;
		if ( $tmp_icon != 0 ) {
			$icon = $tmp_icon;
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>" data-opened="<?php echo $opened; ?>">
	<?php if ( trim( $label ) !== '' || ! empty( $icon ) || true == $collapsible ) : ?>
	<div class="ipt_uif_container_head">
		<?php echo $after; ?>
		<h3><?php if ( $collapsible ) echo '<a href="javascript:;" class="ipt_uif_collapsible_handle_anchor"><span class="ipt-icomoon-arrow-down3 collapsible_state"></span>'; ?>
			<?php if ( is_string( $icon ) ) : ?>
			<?php $this->print_icon_by_class( $icon ); ?>
			<?php else : ?>
			<?php $this->print_icon_by_data( $icon ); ?>
			<?php endif; ?>
			<?php echo '<span class="ipt_uif_container_label">' . $label . '</span>'; ?>
			<?php if ( $collapsible ) echo '</a>'; ?>
		</h3>
	</div>
	<?php endif; ?>
	<div class="ipt_uif_container_inner">
		<?php
	}

	public function container_tail() {
?>
		<?php $this->clear(); ?>
	</div>
</div>
		<?php
	}

	public function iconbox( $callback, $label, $icon, $after = '' ) {
		$this->container( $callback, $label, $icon, false, false, $after );
	}

	public function iconbox_head( $label, $icon, $after = '' ) {
		$this->container_head( $label, $icon, false, false, $after );
	}

	public function iconbox_tail() {
		$this->container_tail();
	}

	public function collapsible( $callback, $label, $icon = 'file3', $after = '', $opened = false ) {
		$this->container( $callback, $label, $icon, true, $opened, $after );
	}

	public function collapsible_head( $label, $opened = false, $icon = 'file3' ) {
		$this->container_head( $label, $icon, true, $opened );
	}

	public function collapsible_tail() {
		$this->container_tail();
	}

	public function fancy_container( $callback ) {
		$this->div( 'ipt_uif_fancy_container', $callback );
	}

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
?>
<<?php echo $type; ?> class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( $scroll_top || $text !== '' ) : ?>
	<span class="ipt_uif_divider_text">
		<?php if ( $scroll_top ) : ?>
		<?php $this->print_scroll_to_top(); ?>
		<?php endif; ?>
		<?php if ( $text != '' ) : ?>
		<?php if ( ! $no_bg ) : ?>
		<?php $this->print_icon( $icon, true ); ?>
		<?php endif; ?>
		<span class="ipt_uif_divider_text_inner">
			<?php if ( $no_bg ) : ?>
			<?php $this->print_icon( $icon, false ); ?>
			<?php endif; ?>
			<?php echo $text; ?>
		</span>
		<?php endif; ?>
	</span>
	<?php endif; ?>
</<?php echo $type; ?>>
		<?php
	}

	public function heading( $text, $type = 'h2', $align = 'center', $icon = 'none', $scroll_top = false, $no_bg = false, $classes = array() ) {
		if ( trim( $text ) == '' ) {
			return;
		}
		if ( ! is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_heading';
		$this->divider( $text, $type, $align, $icon, $scroll_top, $classes, $no_bg );
	}


	/*==========================================================================
	 * Image Slider
	 *========================================================================*/
	public function imageslider( $id, $images, $settings, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_image_slider_wrap';
		$classes[] = 'theme-ipt-uif-imageslider';
		$settings = wp_parse_args( $settings, array(
				'autoslide' => true,
				'duration' => 5,
				'transition' => 1,
				'animation' => 'random',
				'on_play' => 'ipt-icomoon-pause2',
				'on_pause' => 'ipt-icomoon-play3',
			) );
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" data-settings="<?php echo esc_attr( json_encode( (object) $settings ) ); ?>">
	<div id="<?php echo esc_attr( $id ); ?>" class="ipt_uif_image_slider nivoSlider">
		<?php foreach ( $images as $image ) : ?>
		<?php if ( $image['url'] != '' ) : ?>
		<a href="<?php echo esc_attr( $image['url'] ); ?>">
		<?php endif; ?>
			<img src="<?php echo esc_attr( $image['src'] ); ?>" title="<?php echo esc_attr( $image['title'] ); ?>" />
		<?php if ( $image['url'] != '' ) : ?>
		</a>
		<?php endif; ?>
		<?php endforeach; ?>
	</div>
	<div class="ribbon"></div>
</div>
		<?php
	}

	/*==========================================================================
	 * Timer
	 *========================================================================*/
	/**
	 * Create a jQuery Circle Timer
	 *
	 * @param      string  $time     The date time or just time in seconds
	 * @param      string  $type     Type of the timer (Reference Time: date | Stopwatch: timer) Defaults to timer (optional)
	 * @param      array   $classes  array of additional CSS classes (optional)
	 * @param      string  $id       ID of the element (optional)
	 * @param      array   $options  TimeCircles Options (optional)
	 */
	public function timer( $time, $type = 'timer', $classes = array(), $id = '', $options = array() ) {
		// enqueue
		wp_enqueue_script( 'jquery-time-circles', $this->static_location . 'js/TimeCircles.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_style( 'ipt-plugin-uif-time-circles', $this->static_location . 'css/TimeCircles.css', array(), $this->version );

		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_circle_timer';
		$data_attr = ' data-timer="' . esc_attr( $time ) . '"';
		if ( $type == 'date' ) {
			$data_attr = 'data-date="' . esc_attr( $time ) . '"';
		}
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( $id ) . '"';
		}
		?>
<div data-coptions="<?php echo esc_attr( json_encode( (object) $options ) ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $id_attr . $data_attr; ?>></div>
		<?php
	}


	/*==========================================================================
	 * Messages
	 *========================================================================*/
	/**
	 * Prints an error message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_error( $msg = '', $echo = true, $title = '', $wpautop = true, $close_button = false ) {
		return $this->print_message( 'red', $msg, $echo, $title, 'info', $wpautop, $close_button );
	}

	/**
	 * Prints an update message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_update( $msg = '', $echo = true, $title = '', $wpautop = true, $close_button = false ) {
		return $this->print_message( 'yellow', $msg, $echo, $title, 'info', $wpautop, $close_button );
	}

	/**
	 * Prints an okay message in style.
	 *
	 * @param string  $msg  The message
	 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
	 * @return string The HTML output
	 */
	public function msg_okay( $msg = '', $echo = true, $title = '', $wpautop = true, $close_button = false ) {
		return $this->print_message( 'green', $msg, $echo, $title, 'info', $wpautop, $close_button );
	}

	public function print_message( $style, $msg = '', $echo = true, $title = '', $icon = 'info', $wpautop = true, $close_button = false ) {
		if ( $title == '' ) {
			if ( isset( $this->default_messages['messages'][$style] ) ) {
				$title = $this->default_messages['messages'][$style];
			}
		}
		if ( $icon == 'info' ) {
			switch ( $style ) {
			case 'yellow' :
			case 'update' :
				$icon = 'info3';
				break;
			case 'red' :
			case 'error' :
				$icon = 'warning2';
				break;
			case 'okay' :
			case 'green' :
				$icon = 'checkmark-circle';
				break;
			}
		}

		ob_start();
?>
<div class="ipt_uif_message ipt_fsqm_uif_message_<?php echo $style; ?> ui-widget ui-widget-content ui-corner-all ipt_uif_widget_box">
	<?php if ( $title != '' ) : ?>
	<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
		<h3>
			<?php if ( $close_button ) : ?>
			<a href="javascript:void(0)" class="ipt_uif_message_close"><span class="ipt-icomoon-cancel-circle ipt_uif_text_icon_no_bg"></span></a>
			<?php endif; ?>
			<?php if ( $icon != '' ) : ?>
			<span class="ipt-icomoon-<?php echo $icon; ?> ipt_uif_text_icon_no_bg"></span>
			<?php endif; ?>
			<?php echo $title; ?>
		</h3>
	</div>
	<?php endif; ?>
	<div class="ui-widget-content ui-corner-all">
		<?php if ( $wpautop ) : ?>
			<?php echo wpautop( $msg ); ?>
		<?php else : ?>
			<?php echo $msg; ?>
		<?php endif; ?>
	</div>
</div>
		<?php
		$output = ob_get_clean();
		if ( $echo )
			echo $output;
		return $output;
	}

	public function slider_labels( $labels, $min, $max, $step ) {
		$label_data = array(
			'first' => false,
			'last' => false,
			'rest' => false,
			'labels' => array(),
		);
		if ( $step == 0 ) {
			$step = 1;
		}
		// Actual length is $length + 1 (starting from zeroeth value)
		$length = floor( ($max - $min) / $step );
		// We take the lesser integer value of the mid value
		$mid_length = floor( $length / 2 );
		// From zeroeth value to length
		for ( $i = 0; $i <= $length; $i++ ) {
			$label_data['labels'][$i] = '';
		}

		// Show if needed
		if ( isset( $labels['show'] ) && $labels['show'] == true ) {
			$label_data['rest'] = 'pip';
			$label_data['first'] = 'pip';
			$label_data['last'] = 'pip';
		}

		// Set the first label
		if ( isset( $labels['first'] ) && $labels['first'] != '' ) {
			$label_data['first'] = 'label';
			$label_data['labels'][0] = $labels['first'];
		}

		// Set the last label
		if ( isset( $labels['last'] ) && $labels['last'] != '' ) {
			$label_data['last'] = 'label';
			$label_data['labels'][$length] = $labels['last'];
		}

		// Set the mid label
		if ( isset( $labels['mid'] ) && $labels['mid'] != '' ) {
			$label_data['rest'] = 'label';
			$label_data['labels'][$mid_length] = $labels['mid'];
		}

		// Set the rest of the labels
		if ( isset( $labels['rest'] ) && $labels['rest'] != '' ) {
			$rest_labels = explode( ',', $labels['rest'] );
			$rest_labels = array_map( 'trim', $rest_labels );

			$i = 1;
			foreach ( (array) $rest_labels as $rl ) {
				if ( $i == $mid_length ) {
					$i++;
				}
				if ( $i < $length ) {
					$label_data['labels'][$i] = $rl;
				}
				$i++;
			}
		}

		return $label_data;
	}

	/*==========================================================================
	 * CSS 3 Loader
	 *========================================================================*/
	/**
	 * Creates the HTML for the CSS3 Loader.
	 *
	 * @param bool    $hidden  TRUE(default) if hidden in inital state (Optional).
	 * @param string  $id      HTML ID (Optional).
	 * @param array   $labels  Labels which will be converted to HTML data attribute
	 * @param bool    $inline  Whether inline(true) or overlay (false)
	 * @param string  $default Default text
	 * @param array   $classes Array of additional classes (Optional).
	 *
	 */
	public function ajax_loader( $hidden = true, $id = '', $labels = array(), $inline = false, $default = null, $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		if ( !$inline ) {
			$classes[] = 'ipt_uif_ajax_loader';
		} else {
			$classes[] = 'ipt_uif_ajax_loader_inline';
		}
		$id_attr = '';
		if ( $id != '' ) {
			$id_attr = ' id="' . esc_attr( trim( $id ) ) . '"';
		}
		$style_attr = '';
		if ( $hidden == true ) {
			$style_attr = ' style="display: none;"';
		}
		$data_attr = $this->convert_data_attributes( $labels );
		if ( $default === null ) {
			$default = $this->default_messages['ajax_loader'];
		}
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $id_attr . $style_attr . $data_attr; ?>>
	<div class="ipt_uif_ajax_loader_inner ipt_uif_ajax_loader_animate">
		<div class="ipt_uif_ajax_loader_icon ipt_uif_ajax_loader_spin">
			<span class="ipt-icomoon-cog spinner-large"></span>
			<span class="ipt-icomoon-cog2 spinner-small"></span>
		</div>
		<div class="ipt_uif_ajax_loader_hellip">
			<span class="dot1">.</span><span class="dot2">.</span><span class="dot3">.</span>
		</div>
		<div class="ipt_uif_ajax_loader_text"><?php echo $default; ?></div>
		<div class="clear"></div>
	</div>
</div>
		<?php
	}


	public function print_icon_by_class( $icon = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( is_numeric( $icon ) ) {
			$this->print_icon_by_data( $icon, $background );
			return;
		}
		if ( ! is_array( $additional_classes ) ) {
			$additional_classes = (array) $additional_classes;
		}
?>
<?php if ( $icon != 'none' ) : ?>
<span title="<?php echo esc_attr( $title ); ?>" class="<?php echo ( $background == false ) ? 'ipt_uif_text_icon_no_bg' : 'ipt_uif_text_icon'; ?>"><i class="<?php echo implode( ' ', $additional_classes ); ?> ipt-icomoon-<?php echo esc_attr( $icon ); ?> ipticm"></i></span>
<?php endif; ?>
		<?php
	}

	public function print_icon_by_data( $data = 'none', $background = true, $additional_classes = array(), $title = '' ) {
		if ( ! is_numeric( $data ) ) {
			$this->print_icon_by_class( $data, $background );
			return;
		}
		if ( ! is_array( $additional_classes ) ) {
			$additional_classes = (array) $additional_classes;
		}
?>
<?php if ( $data != 'none' ) : ?>
<span title="<?php echo esc_attr( $title ); ?>" class="<?php echo ( $background == false ) ? 'ipt_uif_text_icon_no_bg' : 'ipt_uif_text_icon'; ?>"><i class="<?php echo implode( ' ', $additional_classes ); ?> ipticm" data-ipt-icomoon="&#x<?php echo dechex( $data ); ?>;"></i></span>
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

	public function print_scroll_to_top() {
		?>
<a href="#" class="ipt_uif_scroll_to_top ipt-icomoon-arrow-up4"></a>
		<?php
	}

	public function question_container( $name, $title, $subtitle, $callback, $required = false, $fancy_box = false, $vertical = false, $description = '', $classes = array(), $data_attr = array(), $hidden_label = false, $centered = false ) {
		if ( ! $this->check_callback( $callback ) ) {
			$this->msg_error( __( 'Invalid Callback', 'ipt_fsqm' ) );
			return;
		}
		if ( $required ) {
			$title .= '<span class="ipt_uif_question_required">*</span>';
		}
		$description = trim( (string) $description );
		$classes = (array) $classes;
		$classes[] = 'ipt_uif_question';
		if ( $vertical ) {
			$classes[] = 'ipt_uif_question_vertical';
		}

		if ( $hidden_label || ( '' == $title && '' == $subtitle ) ) {
			$classes[] = 'ipt_uif_question_full';
		}

		if ( $centered ) {
			$classes[] = 'ipt_uif_question_centered';
		}

		$inline_data_attr = $this->convert_data_attributes( (array) $data_attr );
?>
<div class="<?php echo implode( ' ', $classes ); ?>"<?php echo $inline_data_attr; ?>>
	<?php if ( ! $hidden_label && ( '' != $title || '' != $subtitle ) ) : ?>
		<div class="ipt_uif_question_label">
			<?php $this->generate_label( $name, $title, '', 'ipt_uif_question_title' ); ?>
			<?php $this->clear(); ?>
			<?php if ( $subtitle != '' ) : ?>
			<?php $this->generate_label( $name, $subtitle, '', 'ipt_uif_question_subtitle' ); ?>
			<?php endif; ?>
			<?php if ( $description !== '' ) : ?>
			<div class="ipt_uif_richtext">
				<?php echo apply_filters( 'ipt_uif_richtext', $description ); ?>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="ipt_uif_question_content">
		<?php if ( $fancy_box ) : ?>
		<div class="ipt_uif_fancy_container">
		<?php endif; ?>
		<?php call_user_func_array( $callback[0], $callback[1] ); ?>
		<?php $this->clear(); ?>
		<?php if ( $fancy_box ) : ?>
		</div>
		<?php endif; ?>
	</div>
</div>
		<?php
	}

	public function iconmenu( $items, $alignment = 'center', $open = 'self', $dimension = array() ) {
		$dimension = wp_parse_args( $dimension, array(
			'height' => 600,
			'width' => 600,
		) );
?>
<ul class="ipt_uif_ul_menu ipt_uif_align_<?php echo esc_attr( 'center' ); ?>">
	<?php foreach ( $items as $item ) : ?>
	<?php
		$href = '' == $item['url'] ? 'javascript:;' : esc_attr( $item['url'] );
		$text = '' == trim( $item['text'] ) ? '' : $item['text'];
		$attr = array();
		if ( 'popup' == $open ) {
			$attr['class'] = 'eform-icmpopup';
			$attr['data-height'] = $dimension['height'];
			$attr['data-width'] =  $dimension['width'];
		} else if ( 'blank' == $open ) {
			$attr['target'] = '_blank';
		}
?>
	<li>
		<a href="<?php echo $href; ?>" <?php echo $this->convert_html_attributes( $attr ); ?>>
			 <?php if ( isset( $item['icon'] ) && $item['icon'] !== '' && $item['icon'] != 'none' ) : ?>
			<i <?php if ( is_numeric( $item['icon'] ) ) : ?> class="ipticm" data-ipt-icomoon="&#x<?php echo dechex( $item['icon'] ) ?>;"<?php else : ?> class="ipt-icomoon-<?php echo esc_attr( $item['icon'] ); ?> ipticm"<?php endif; ?>>
			</i>
			<?php endif; ?>
			<?php echo $text; ?></a>
	</li>
	<?php endforeach; ?>
</ul>
		<?php
	}

	public function enqueue_payment() {
		wp_enqueue_script( 'jessepollak-card', $this->static_location . 'js/jquery.card.min.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_script( 'jquery-payment', $this->static_location . 'js/jquery.payment.min.js', array( 'jquery' ), $this->version, true );
	}


	/**
	 * Prints an eForm styled login form
	 *
	 * @param      <type>  $atts   The atts
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
	<?php $this->question_container( 'log', $args['label_username'], '', array( array( $this, 'text' ), array( 'log', $args['value_username'], $args['label_username'], 'user' ) ), true ); ?>
<?php $this->column_tail(); ?>
<?php // Password ?>
<?php $this->column_head( '', 'half' ); ?>
	<?php $this->question_container( 'pwd', $args['label_password'], '', array( array( $this, 'password_simple' ), array( 'pwd', '', $args['label_password'], 'console' ) ), true ); ?>
<?php $this->column_tail(); ?>
<?php // Login Form Action ?>
<?php $this->column_head( '', 'full' ); ?>
	<?php do_action( 'login_form' ); ?>
<?php $this->column_tail(); ?>
<?php // Login Form Middle ?>
<?php if ( $login_form_middle != '' ) : ?>
	<?php $this->column_head( '', 'full' ); ?>
		<?php echo $login_form_middle; ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Remember Button ?>
<?php if ( $args['remember'] ) : ?>
	<?php $this->column_head( '', 'forth' ); ?>
		<?php $this->checkbox( 'rememberme', array(
			'value' => 'forever',
			'label' => $args['label_remember'],
		), $args['value_remember'] ); ?>
	<?php $this->column_tail(); ?>
<?php endif; ?>
<?php // Login Buttons ?>
<?php $this->column_head( '', 'three_forth' ); ?>
	<?php $this->buttons( $login_buttons, '', 'center' ) ?>
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

	public function clear( $direction = 'both' ) {
		echo '<div class="clear-' . $direction . '"></div>';
	}

	public function richtext( $text ) {
		echo apply_filters( 'ipt_uif_richtext', $text );
	}
}
endif;
