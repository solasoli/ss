<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Base class
 *
 * Populates the actual form with all the hooks and filters
 *
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_Form_Elements_Base {
	/*==========================================================================
	 * DATABASE REFERENCE VARIABLES
	 *========================================================================*/
	public $form_id = null;

	public $name = '';
	public $type = '1';
	public $category = '0';
	public $settings = array();

	public $mcq = array();
	public $pinfo = array();
	public $freetype = array();
	public $design = array();
	public $layout = array();

	/*==========================================================================
	 * INTERNAL VARIABLES
	 *========================================================================*/
	public $elements = array();
	public $post = array();
	public $post_raw = array();

	public $compatibility = false;

	/*==========================================================================
	 * Static Variables
	 *========================================================================*/
	static $js_suffix = '.min';



	/*==========================================================================
	 * CONSTRUCTOR
	 *========================================================================*/
	public function __construct( $form_id = null, $do_init = true ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG == true ) {
			self::$js_suffix = '';
		}
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			//$this->post = $_POST;

			//we do not need to check on magic quotes
			//as wordpress always adds magic quotes
			//@link http://codex.wordpress.org/Function_Reference/stripslashes_deep
			$this->post = wp_unslash( $_POST );
			$this->post_raw = $_POST;

			// Now check if the ajax has send as post
			// Along with parsable string
			// This addresses issue #11
			if (  isset( $this->post['ipt_ps_send_as_str'] ) && $this->post['ipt_ps_send_as_str'] == 'true' && isset( $this->post['ipt_ps_look_into'] ) ) {
				$parse_post = array();
				IPT_FSQM_Form_Elements_Static::safe_parse_str( $this->post[$this->post['ipt_ps_look_into']], $parse_post );
				if ( get_magic_quotes_gpc() ) {
					$parse_post = array_map( 'stripslashes_deep', $parse_post );
				}
				$this->post = $parse_post;
			} else if ( isset( $this->post['ipt_ps_send_as_json'] ) && $this->post['ipt_ps_send_as_json'] == 'true' && isset( $this->post['ipt_ps_look_into'] ) ) {
				// json_decode doesn't seem to work
				// if magic_quotes_gpc is enabled
				// So check conditionally
				// $json_post = json_decode( $this->post[$this->post['ipt_ps_look_into']], true, 1024 );
				$json_post = json_decode( $this->post[$this->post['ipt_ps_look_into']], true, 1024 );
				if ( json_last_error() == JSON_ERROR_SYNTAX ) {
					$json_post = json_decode( $this->post_raw[$this->post['ipt_ps_look_into']], true, 1024 );
				}

				$this->post = $json_post;
			}

			//convert html to special characters
			//array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
			//No need really Do it the way WordPress does it
		}

		$this->set_valid_elements();

		if ( $do_init ) {
			$this->init( $form_id );
		}
	}


	/* =========================================================================
	 * BASIC ABSTRACTIONS & API
	 * =======================================================================*/
	public function init( $form_id = null ) {
		global $wpdb, $ipt_fsqm_info;
		$this->form_id = null;
		$this->name = '';
		$this->type = '1';
		$this->category = '0';
		$this->settings = $this->get_default_settings();
		$this->mcq = array();
		$this->pinfo = array();
		$this->freetype = array();
		$this->design = array();
		$this->layout = array();
		if ( $form_id != null ) {
			$form_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );
			if ( null == $form_data ) {
				return;
			}
			$this->form_id = $form_id;
			$this->name = $form_data->name;
			$this->type = $form_data->type;
			$this->category = $form_data->category;
			$this->settings = maybe_unserialize( $form_data->settings );
			$this->mcq = maybe_unserialize( $form_data->mcq );
			$this->pinfo = maybe_unserialize( $form_data->pinfo );
			$this->freetype = maybe_unserialize( $form_data->freetype );
			$this->design = maybe_unserialize( $form_data->design );
			$this->layout = maybe_unserialize( $form_data->layout );
		}
		$this->compat_layout();
	}

	public function set_valid_elements() {
		$elements = array();

		// Layout Elements //
		$elements['layout'] = array(
			'title' => __( 'Layout & Structure', 'ipt_fsqm' ),
			'description' => __( 'Select the structure of the appearance of the form.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_layout',
		);
		$elements['layout']['elements'] = array(
			'tab' => array(
				'title' => __( 'Tabular Structure', 'ipt_fsqm' ),
				'description' => __( 'Tab like appearance with next/previous and submit button.', 'ipt_fsqm' ),
			),
			'pagination' => array(
				'title' => __( 'Paginated Structure', 'ipt_fsqm' ),
				'description' => __( 'Paginated appearance with progress bar.', 'ipt_fsqm' ),
			),
			'normal' => array(
				'title' => __( 'Normal Structure', 'ipt_fsqm' ),
				'description' => __( 'Normal continuous appearance without any page breaks.', 'ipt_fsqm' ),
			),
		);

		// Design Elements //
		$elements['design'] = array(
			'title' => __( 'Design & Security (D)', 'ipt_fsqm' ),
			'description' => __( 'Form Design & Security Tools.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_design',
			'icon' => 'paint-brush',
		);
		$elements['design']['elements'] = array(
			'heading' => array(
				'title' => __( 'Heading', 'ipt_fsqm' ),
				'description' => __( 'Show a large heading text with optional scroll to top icon.', 'ipt_fsqm' ),
			),
			'richtext' => array(
				'title' => __( 'Rich Text', 'ipt_fsqm' ),
				'description' => __( 'A Rich content (HTML) box. Can contain shortcodes.', 'ipt_fsqm' ),
			),
			'embed' => array(
				'title' => __( 'Embed Code', 'ipt_fsqm' ),
				'description' => __( 'Embed any code, YouTube, FaceBook, iFrame etc.', 'ipt_fsqm' ),
			),
			'collapsible' => array(
				'title' => __( 'Collapsible Content', 'ipt_fsqm' ),
				'description' => __( 'Collapsible content box. Can contain other elements inside it.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'container' => array(
				'title' => __( 'Styled Container', 'ipt_fsqm' ),
				'description' => __( 'Custom content box with style. Can contain other elements inside it.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'blank_container' => array(
				'title' => __( 'Simple Container', 'ipt_fsqm' ),
				'description' => __( 'Simple content box. Useful to add grouped conditional elements. Can contain other elements inside it.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'iconbox' => array(
				'title' => __( 'Icons and Buttons', 'ipt_fsqm' ),
				'description' => __( 'List of icons and/or texts optionally linked to some URL.', 'ipt_fsqm' ),
			),
			'col_half' => array(
				'title' => __( 'Column Half', 'ipt_fsqm' ),
				'description' => __( 'Column element with width half of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_third' => array(
				'title' => __( 'Column Third', 'ipt_fsqm' ),
				'description' => __( 'Column element with width one third of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_two_third' => array(
				'title' => __( 'Column Two Third', 'ipt_fsqm' ),
				'description' => __( 'Column element with width two third of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_forth' => array(
				'title' => __( 'Column Fourth', 'ipt_fsqm' ),
				'description' => __( 'Column element with width one fourth of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_three_forth' => array(
				'title' => __( 'Column Three Fourth', 'ipt_fsqm' ),
				'description' => __( 'Column element with width three fourth of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'col_three_forth' => array(
				'title' => __( 'Column Three Fourth', 'ipt_fsqm' ),
				'description' => __( 'Column element with width three fourth of the container.', 'ipt_fsqm' ),
				'droppable' => true,
			),
			'clear' => array(
				'title' => __( 'Clear Columns', 'ipt_fsqm' ),
				'description' => __( 'Clears the floating contents. Use this if after the last column of a group.', 'ipt_fsqm' ),
			),
			'horizontal_line' => array(
				'title' => __( 'Horizontal Line', 'ipt_fsqm' ),
				'description' => __( 'Horizontal line with scroll to top.', 'ipt_fsqm' ),
			),
			'divider' => array(
				'title' => __( 'Divider', 'ipt_fsqm' ),
				'description' => __( 'Divider with optional text, icon and/or scroll to top.', 'ipt_fsqm' ),
			),
			'button' => array(
				'title' => __( 'Container Button', 'ipt_fsqm' ),
				'description' => __( 'A button with predefined action of jump to a specific container.', 'ipt_fsqm' ),
			),
			'imageslider' => array(
				'title' => __( 'Image Slider', 'ipt_fsqm' ),
				'description' => __( 'Image gallery slider.', 'ipt_fsqm' ),
			),
			'captcha' => array(
				'title' => __( 'Security Captcha', 'ipt_fsqm' ),
				'description' => __( 'Security challenge for anti bot protection.', 'ipt_fsqm' ),
			),
			'recaptcha' => array(
				'title' => __( 'reCaptcha', 'ipt_fsqm' ),
				'description' => __( 'Google reCaptcha for anti bot protection.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
		);

		// MCQ Elements //
		$elements['mcq'] = array(
			'title' => __( 'Multiple Choice Questions (M)', 'ipt_fsqm' ),
			'description' => __( 'Used for survey and/or Quiz.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_mcq',
			'icon' => 'check-square-o',
		);

		$elements['mcq']['elements'] = array(
			'radio' => array(
				'title' => __( 'Single Options', 'ipt_fsqm' ),
				'description' => __( 'Can select only one option from the list of options.', 'ipt_fsqm' ),
			),
			'checkbox' => array(
				'title' => __( 'Multiple Options', 'ipt_fsqm' ),
				'description' => __( 'Can select multiple options from the list of options.', 'ipt_fsqm' ),
			),
			'select' => array(
				'title' => __( 'Dropdown Options', 'ipt_fsqm' ),
				'description' => __( 'Can select only one or multiple options from a list of dropdown menu.', 'ipt_fsqm' ),
			),
			'thumbselect' => array(
				'title' => __( 'Thumbnail Selection', 'ipt_fsqm' ),
				'description' => __( 'Choose from a list of images', 'ipt_fsqm' ),
			),

			'slider' => array(
				'title' => __( 'Single Slider', 'ipt_fsqm' ),
				'description' => __( 'Can enter a number within a specified range using a slider.', 'ipt_fsqm' ),
			),
			'range' => array(
				'title' => __( 'Single Range', 'ipt_fsqm' ),
				'description' => __( 'Can enter a number within a specified range using a slider.', 'ipt_fsqm' ),
			),
			'spinners' => array(
				'title' => __( 'Spinners', 'ipt_fsqm' ),
				'description' => __( 'Can select one value from a list of available values for a number of options.', 'ipt_fsqm' ),
			),
			'grading' => array(
				'title' => __( 'Multiple Grading', 'ipt_fsqm' ),
				'description' => __( 'Can grade multiple options.', 'ipt_fsqm' ),
			),
			'smileyrating' => array(
				'title' => __( 'Smiley Rating', 'ipt_fsqm' ),
				'description' => __( 'Rate using smileys and take optional feedback.', 'ipt_fsqm' ),
			),
			'starrating' => array(
				'title' => __( 'Star Ratings', 'ipt_fsqm' ),
				'description' => __( 'Can rate multiple options using star rating.', 'ipt_fsqm' ),
			),
			'scalerating' => array(
				'title' => __( 'Scale Ratings', 'ipt_fsqm' ),
				'description' => __( 'Can rate multiple options using radio buttons.', 'ipt_fsqm' ),
			),
			'matrix' => array(
				'title' => __( 'Matrix Question', 'ipt_fsqm' ),
				'description' => __( 'Format multiple questions and options inside a matrix.', 'ipt_fsqm' ),
			),
			'matrix_dropdown' => array(
				'title' => __( 'Matrix Dropdown', 'ipt_fsqm' ),
				'description' => __( 'Dropdown inside matrix table.', 'ipt_fsqm' ),
			),
			'likedislike' => array(
				'title' => __( 'Like Dislike', 'ipt_fsqm' ),
				'description' => __( 'Like and Dislike button.', 'ipt_fsqm' ),
			),
			'toggle' => array(
				'title' => __( 'Toggle Option', 'ipt_fsqm' ),
				'description' => __( 'Can select between two options.', 'ipt_fsqm' ),
			),
			'sorting' => array(
				'title' => __( 'Sortable List', 'ipt_fsqm' ),
				'description' => __( 'User has to sort in correct order to get better score.', 'ipt_fsqm' ),
			),
		);

		// FEEDBACK Elements //
		$elements['freetype'] = array(
			'title' => __( 'Feedback &amp; Upload (F)', 'ipt_fsqm' ),
			'description' => __( 'Gather and/or email feedbacks.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_freetype',
			'icon' => 'textsms',
		);
		$elements['freetype']['elements'] = array(
			'feedback_large' => array(
				'title' => __( 'Feedback Large Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts with multiple lines.', 'ipt_fsqm' ),
			),
			'feedback_small' => array(
				'title' => __( 'Feedback Small Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts within a single line.', 'ipt_fsqm' ),
			),
			'upload' => array(
				'title' => __( 'File Upload', 'ipt_fsqm' ),
				'description' => __( 'Upload multiple files and media.', 'ipt_fsqm' ),
			),
			'mathematical' => array(
				'title' => __( 'Mathematical Evaluator', 'ipt_fsqm' ),
				'description' => __( 'Automatically calculate value based on formula.', 'ipt_fsqm' ),
			),
			'gps' => array(
				'title' => __( 'GPS Tracker', 'ipt_fsqm' ),
				'description' => __( 'Track Location of your user using google maps.', 'ipt_fsqm' ),
			),
			'feedback_matrix' => array(
				'title' => __( 'Feedback Matrix', 'ipt_fsqm' ),
				'description' => __( 'Get feedbacks in a matrix form.', 'ipt_fsqm' ),
			),
			'signature' => array(
				'title' => __( 'Signature Pad', 'ipt_fsqm' ),
				'description' => __( 'Signature Pad for getting user\'s signature.', 'ipt_fsqm' ),
			),
		);

		// PINFO Elements //
		$elements['pinfo'] = array(
			'title' => __( 'Other Form Elements (O)', 'ipt_fsqm' ),
			'description' => __( 'All other form elements.', 'ipt_fsqm' ),
			'id' => 'ipt_fsqm_builder_pinfo',
			'icon' => 'file-text',
		);
		$elements['pinfo']['elements'] = array(
			'f_name' => array(
				'title' => __( 'Primary First Name', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the first name of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'l_name' => array(
				'title' => __( 'Primary Last Name', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the last name of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'email' => array(
				'title' => __( 'Primary Email', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the email of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'phone' => array(
				'title' => __( 'Primary Phone', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect the phone number of the surveyee. Can populate in the list of entries. Can only be used once.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'payment' => array(
				'title' => __( 'Payment Element', 'ipt_fsqm' ),
				'description' => __( 'Use this with the payment settings to put the payment elements inside your form.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'p_name' => array(
				'title' => __( 'Full Name', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect names. By default only allows alphabetic characters with space.', 'ipt_fsqm' ),
			),
			'p_email' => array(
				'title' => __( 'Email Address', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect additional email of the surveyee. Validates the email.', 'ipt_fsqm' ),
			),
			'p_phone' => array(
				'title' => __( 'Phone Number', 'ipt_fsqm' ),
				'description' => __( 'Use this to collect additional phone numbers of the surveyee. Validates the number.', 'ipt_fsqm' ),
			),
			'textinput' => array(
				'title' => __( 'Small Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts in a single line.', 'ipt_fsqm' ),
			),
			'textarea' => array(
				'title' => __( 'Large Text', 'ipt_fsqm' ),
				'description' => __( 'Can input texts with multiple lines.', 'ipt_fsqm' ),
			),
			'guestblog' => array(
				'title' => __( 'Guest Blogging', 'ipt_fsqm' ),
				'description' => __( 'Field for guest blogging with support for category selection.', 'ipt_fsqm' ),
				'dbmap' => true,
			),
			'password' => array(
				'title' => __( 'Password', 'ipt_fsqm' ),
				'description' => __( 'Hidden text input.', 'ipt_fsqm' ),
			),
			'p_radio' => array(
				'title' => __( 'Radio Options', 'ipt_fsqm' ),
				'description' => __( 'Can select only one options from a list.', 'ipt_fsqm' ),
			),
			'p_checkbox' => array(
				'title' => __( 'Checkbox Options', 'ipt_fsqm' ),
				'description' => __( 'Can select multiple options from a list.', 'ipt_fsqm' ),
			),
			's_checkbox' => array(
				'title' => __( 'Single Checkbox', 'ipt_fsqm' ),
				'description' => __( 'Can tick or untick an option.', 'ipt_fsqm' ),
			),
			'p_select' => array(
				'title' => __( 'Dropdown Option', 'ipt_fsqm' ),
				'description' => __( 'Can select only one or multiple options from a list of dropdown menu.', 'ipt_fsqm' ),
			),
			'address' => array(
				'title' => __( 'Address', 'ipt_fsqm' ),
				'description' => __( 'Formatted address input boxes.', 'ipt_fsqm' ),
			),
			'keypad' => array(
				'title' => __( 'Keypad', 'ipt_fsqm' ),
				'description' => __( 'Keypad to enter numbers and/or text.', 'ipt_fsqm' ),
			),
			'datetime' => array(
				'title' => __( 'Date Time', 'ipt_fsqm' ),
				'description' => __( 'Formatted date/time input boxes.', 'ipt_fsqm' ),
			),
			'p_sorting' => array(
				'title' => __( 'Sortable Choices', 'ipt_fsqm' ),
				'description' => __( 'User can sort options according to their choices.', 'ipt_fsqm' ),
			),
			'hidden' => array(
				'title' => __( 'Hidden Element', 'ipt_fsqm' ),
				'description' => __( 'Hidden element to get values from URL or other means.', 'ipt_fsqm' ),
			),
			'repeatable' => array(
				'title' => __( 'Repeatable Element', 'ipt_fsqm' ),
				'description' => __( 'Allows group of fields to be repeated by the user.', 'ipt_fsqm' ),
			),
		);

		foreach ( $elements as $e_key => $element ) {
			foreach ( $element['elements'] as $el_key => $el ) {
				$elements[$e_key]['elements'][$el_key]['m_type'] = $e_key;
				$elements[$e_key]['elements'][$el_key]['type'] = $el_key;
			}
		}

		$this->elements = apply_filters( 'ipt_fsqm_filter_valid_elements', $elements, $this->form_id );
	}

	public function get_element_structure( $element ) {
		$default = array(
			'type' => $element,
			'title' => '',
			'validation' => array(),
			'subtitle' => '',
			'description' => '',
			'conditional' => array(
				'active' => false, // True to use conditional logic, false to ignore
				'status' => false, // Initial status -> True for shown, false for hidden
				'change' => true, // Change to status -> True for shown, false for hide
				// 'relation' => 'indi', // AND, OR, INDI relationship to verify against the logic (and,or,indi) ALWAYS indi
				'logic' => array( // element dependent logics
					// 0 => array(
					// 	'm_type' => '', // Mother type
					// 	'key' => '', // Key of the element
					// 	'check' => 'val', //value(val), length(len)
					// 	'operator' => 'eq', // equals(eq), not equals(neq), greater than(gt), less than(lt), contains (ct), does not contain (dct), starts with (sw), ends with (ew)
					// 	'value' => '',
					// 	'rel' => 'and', // (and, or)
					// ),
				),
			),
		);

		switch ( $element ) {
			default :
				$default = false;
				break;

			// Layout Elements - Stored directly inside layout //
			case 'tab' :
			case 'pagination' :
			case 'normal' :
				$default['m_type'] = 'layout';
				$default['elements'] = array();
				$default['icon'] = 'none';
				// $default['time_limit'] = ''; #defered for FSQM 2.2.6
				unset( $default['validation'] );
				// Here we set the conditional #51 https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/51
				// unset( $default['conditional'] );
				// Set the timer
				$default['timer'] = '120';
				break;

			// Design Elements - Stored directly inside design //
			case 'heading' :
				$default['m_type'] = 'design'; //mother type
				$default['settings'] = array(
					'type' => 'h2',
					'align' => 'left',
					'icon' => 'none',
					'show_top' => true,
				);
				break;

			case 'richtext' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'icon' => 0xe10f,
					'styled' => false,
				);
				break;

			case 'embed' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'full_size' => false,
				);
				break;

			case 'collapsible' :
				$default['m_type'] = 'design';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'icon' => 'none',
					'expanded' => false,
				);
				$default['elements'] = array();
				break;

			case 'container' :
				$default['m_type'] = 'design';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'icon' => 'none',
				);
				$default['elements'] = array();
				break;

			case 'blank_container' :
				$default['m_type'] = 'design';
				$default['tooltip'] = '';
				$default['elements'] = array();
				break;

			case 'iconbox' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'align' => 'center',
					'elements' => array(),
					'open' => 'self', // self, blank, popup
					'popup' => array(
						'height' => '600',
						'width' => '600',
					),
				);
				break;

			case 'col_half' :
			case 'col_third' :
			case 'col_two_third' :
			case 'col_forth' :
			case 'col_three_forth' :
				$default['m_type'] = 'design';
				$default['tooltip'] = '';
				$default['elements'] = array();
				break;

			case 'clear' :
				$default['m_type'] = 'design';
				unset( $default['conditional'] );
				break;

			case 'horizontal_line' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'show_top' => true,
				);
				break;

			case 'divider' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'align' => 'center',
					'icon' => 0xe195,
					'show_top' => true,
				);
				break;
			case 'button' :
					$default['m_type'] = 'design';
					$default['settings'] = array(
						'container' => '1',
						'size' => 'medium',
						'icon' => 'none',
					);
					break;
			case 'imageslider' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'autoslide' => true,
					'duration' => '5',
					'transition' => '0.5',
					'animation' => 'random',
					'images' => array(),
				);
				break;

			case 'captcha' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'type' => 'math', //can be quiz, reCaptcha(future)
					'answer' => '',
				);
				break;

			case 'recaptcha' :
				$default['m_type'] = 'design';
				$default['settings'] = array(
					'site_key' => '',
					'secret_key' => '',
					'theme' => 'light',
					'type' => 'image',
					'size' => 'normal',
					'hl' => 'en',
				);
				break;
			// END Design Elements //

			// MCQ Type - Stored in mcq and populated in report //
			case 'radio' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(), //array(label => value, score => value)
					'columns' => '2',
					'vertical' => false,
					'centered' => false,
					'others' => false,
					'o_label' => __( 'Others', 'ipt_fsqm' ),
					'icon' => 0xe18e,
					'shuffle' => false,
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'checkbox' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'columns' => '2',
					'vertical' => false,
					'centered' => false,
					'others' => false,
					'o_label' => __( 'Others', 'ipt_fsqm' ),
					'icon' => 0xe18e,
					'shuffle' => false,
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'minCheckbox' => '',
						'maxCheckbox' => '',
					),
				);
				break;

			case 'select' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'vertical' => false,
					'centered' => false,
					'others' => false,
					'o_label' => __( 'Others', 'ipt_fsqm' ),
					'e_label' => '',
					'shuffle' => false,
					'multiple' => false,
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'thumbselect' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(), //
					'multiple' => false, //
					'show_label' => false, //
					'appearance' => 'normal', // normal - With Radio/Checkbox, border - Highlight selected border, color - Black/White Colored
					'width' => '100',
					'height' => '100',
					'vertical' => false,
					'centered' => false, //
					'icon' => 0xe18e, //
					'tooltip' => false, // This works in reverse manner, if false then SHOW tooltip, if true then HIDE tooltip
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'minCheckbox' => '',
						'maxCheckbox' => '',
					),
				);
				break;

			case 'slider' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'min' => '0',
					'max' => '100',
					'dmin' => '',
					'step' => '1',
					'show_count' => false,
					'vertical' => false,
					'centered' => false,
					'prefix' => '',
					'suffix' => '',
					'score' => false,
					'score_multiplier' => '1',
					'vertical_ui' => false,
					'height' => '300',
					'label' => array(
						'show' => false,
						'first' => '',
						'last' => '',
						'mid' => '',
						'rest' => '',
					),
					'floats' => true,
					'nomin' => false,
				);
				break;

			case 'range' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'min' => '0',
					'max' => '100',
					'dmin' => '',
					'dmax' => '',
					'step' => '1',
					'show_count' => false,
					'vertical' => false,
					'centered' => false,
					'prefix' => '',
					'suffix' => '',
					'score' => false,
					'score_multiplier' => '1',
					'formula' => 'avg', // avg, add, diff, min, max
					'vertical_ui' => false,
					'height' => '300',
					'label' => array(
						'show' => false,
						'first' => '',
						'last' => '',
						'mid' => '',
						'rest' => '',
					),
					'floats' => true,
					'nomin' => false,
				);
				break;

			case 'spinners' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'min' => '',
					'max' => '',
					'step' => '',
					'vertical' => false,
					'centered' => false,
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'grading' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'min' => '0',
					'max' => '100',
					'dmin' => '',
					'dmax' => '',
					'step' => '1',
					'show_count' => false,
					'range' => false,
					'vertical' => false,
					'centered' => false,
					'score' => false,
					'score_multiplier' => '1',
					'formula' => 'avg', // avg, add, diff
					'vertical_ui' => false,
					'height' => '300',
					'label' => array(
						'show' => false,
						'first' => '',
						'last' => '',
						'mid' => '',
						'rest' => '',
					),
					'floats' => true,
					'nomin' => false,
				);
				break;

			case 'smileyrating' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'vertical' => false,
					'centered' => false, //
					'labels' => array( //
						'frown' => __( 'Angry', 'ipt_fsqm' ),
						'sad' => __( 'Sad', 'ipt_fsqm' ),
						'neutral' => __( 'Neutral', 'ipt_fsqm' ),
						'happy' => __( 'Happy', 'ipt_fsqm' ),
						'excited' => __( 'Excited', 'ipt_fsqm' ),
					),
					'enabled' => array( //
						'frown' => true,
						'sad' => true,
						'neutral' => true,
						'happy' => true,
						'excited' => true,
					),
					'show_feedback' => true, //
					'feedback_label' => __( 'Tell us something about your rating', 'ipt_fsqm' ), //
					'scores' => array( //
						'frown' => '',
						'sad' => '',
						'neutral' => '',
						'happy' => '',
						'excited' => '',
					),
					'num' => array( //
						'frown' => '',
						'sad' => '',
						'neutral' => '',
						'happy' => '',
						'excited' => '',
					),
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'starrating' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'max' => '10',
					'vertical' => false,
					'centered' => false,
					'score' => false,
					'score_multiplier' => '1',
					'label_low' => '',
					'label_high' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'scalerating' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'max' => '10',
					'vertical' => false,
					'centered' => false,
					'score' => false,
					'score_multiplier' => '1',
					'label_low' => '',
					'label_high' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'matrix' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'rows' => array(),
					'columns' => array(),
					'scores' => array(),
					'numerics' => array(),
					'multiple' => false,
					'vertical' => true,
					'centered' => false,
					'icon' => 0xe18e,
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'matrix_dropdown' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'empty' => __( '--please select--', 'ipt_fsqm' ),
					'options' => array(),
					'rows' => array(),
					'columns' => array(),
					'scores' => array(), // multiplier
					'vertical' => true,
					'centered' => false,
					'multiple' => false,
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'likedislike' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'like' => __( 'Like', 'ipt_fsqm' ), //
					'dislike' => __( 'Dislike', 'ipt_fsqm' ), //
					'liked' => false, //
					'vertical' => false,
					'centered' => false, //
					'show_feedback' => true, //
					'feedback_label' => __( 'Tell us something about this', 'ipt_fsqm' ), //
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'toggle' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'on' => __( 'On', 'ipt_fsqm' ),
					'off' => __( 'Off', 'ipt_fsqm' ),
					'checked' => false,
					'vertical' => false,
					'centered' => false,
				);
				break;

			case 'sorting' :
				$default['m_type'] = 'mcq';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'score_type' => 'individual', //Can be individual or combined
					'base_score' => '0',
					'options' => array(),
					'no_shuffle' => false,
					'vertical' => false,
					'centered' => false,
				);
				break;
			// END MCQ Elements //

			// FEEDBACK Elements - Stored in freetype and emails to admins when filled //
			case 'feedback_large' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'email' => '',
					'icon' => 0xe056,
					'placeholder' => $this->get_default_placeholder( $element ),
					'score' => '',
					'vertical' => false,
					'centered' => false,
					'keypad' => false,
					'type' => 'qwerty', //keyboard|international|alpha|dvorak|num
					'type' => 'none', // Can be url, meta, predefined
					'default' => '',
					'readonly' => false,
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
						'min' => '',
						'max' => '',
						'minSize' => '',
						'maxSize' => '',
					),
				);
				break;

			case 'feedback_small' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'email' => '',
					'icon' => 0xe056,
					'placeholder' => $this->get_default_placeholder( $element ),
					'score' => '',
					'vertical' => false,
					'centered' => false,
					'keypad' => false,
					'type' => 'qwerty', //keyboard|international|alpha|dvorak|num
					'type' => 'none', // Can be url, meta, predefined
					'default' => '',
					'readonly' => false,
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
						'min' => '',
						'max' => '',
						'minSize' => '',
						'maxSize' => '',
					),
					'equals' => '',
				);
				break;
			case 'upload' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'icon' => 0xe002,
					'centered' => false,
					'accept_file_types' => 'gif,jpeg,png,jpg',
					'max_number_of_files' => '',
					'min_number_of_files' => '',
					'max_file_size' => '1000000',
					'min_file_size' => '1',
					'wp_media_integration' => false,
					'auto_upload' => true,
					// Adding feature #7
					'single_upload' => false,
					'minimal_ui' => false,
					// --
					'drag_n_drop' => true,
					'dragdrop' => __( 'Drag \'n Drop files here', 'ipt_fsqm' ),
					'progress_bar' => true,
					'preview_media' => true,
					'can_delete' => true,
				);
				$default['validation'] = array(
					'required' => true,
				);

				break;

			case 'mathematical' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'icon' => 0xe074,
					'editable' => false,
					'hidden' => false,
					'noanim' => false,
					'vertical' => false,
					'centered' => false,
					'right' => false,
					'fancy' => false,
					'precision' => '2',
					'prefix' => '',
					'suffix' => '',
					'formula' => '',
					'grouping' => true,
					'separator' => ',',
					'decimal' => '.',
				);
				break;

			case 'feedback_matrix' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'rows' => array(), //
					'columns' => array(), //
					'multiline' => false, //
					'vertical' => true, //
					'centered' => false,
					'icon' => 0xe18e, //
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'gps' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'icon' => 0xe07c,
					'centered' => false,
					'radius' => '500',
					'zoom' => '15',
					'scrollwheel' => true,
					'manualcontrol' => true,
					'lat_label' => __( 'Latitude', 'ipt_fsqm' ),
					'long_label' => __( 'Longitude', 'ipt_fsqm' ),
					'location_name_label' => __( 'Location', 'ipt_fsqm' ),
					'update_label' => __( 'Update Location', 'ipt_fsqm' ),
					'nolocation_label' => __( 'No location provided', 'ipt_fsqm' ),
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;
			case 'signature' :
				$default['m_type'] = 'freetype';
				$default['tooltip'] = '';
				$default['validation'] = array(
					'required' => true,
				);
				$default['settings'] = array(
					'icon' => 0xe055,
					'centered' => false,
					'color' => '#212121',
					'reset' => __( 'Reset', 'ipt_fsqm' ),
					'undo' => __( 'Undo Last Stroke', 'ipt_fsqm' ),
				);
				break;
			// END FEEDBACK Elements //

			// PINFO Elements - Stored in pinfo (named after personal information) //
			case 'f_name' :
			case 'l_name' :
			case 'email' :
			case 'phone' :
			case 'p_name' :
			case 'p_email' :
			case 'p_phone' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => true,
					'placeholder' => $this->get_default_placeholder( $element ),
					'vertical' => false,
					'centered' => false,
					'type' => 'none', // Can be url, meta, predefined
					'default' => '',
					'readonly' => false,
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				if ( $element == 'f_name' || $element == 'l_name' || 'p_name' == $element ) {
					$default['settings']['icon'] = 0xf007;
					$default['validation'] = array(
						'required' => true,
						'filters' => array(
							'type' => 'personName', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
							'min' => '',
							'max' => '',
							'minSize' => '',
							'maxSize' => '',
						),
					);
				} elseif ( $element == 'phone' || $element == 'p_phone' ) {
					$default['settings']['icon'] = 0xe08c;
					$default['validation'] = array(
						'required' => true,
						'filters' => array(
							'minSize' => '',
							'maxSize' => '',
						),
					);
				} else if ( 'email' == $element || 'p_email' == $element ) {
					$default['settings']['icon'] = 0xf0e0;
				}
				// Add equals
				if ( in_array( $element, array( 'p_name', 'phone', 'p_phone', 'email', 'p_email' ) ) ) {
					$default['validation']['equals'] = '';
				}
				break;

			case 'guestblog' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'icon' => 0xe001,
					'centered' => false,
					'editor_type' => 'rich', // Rich or HTML
					'placeholder' => __( 'Write your article here', 'ipt_fsqm' ),
					'title_label' => __( 'Post Title', 'ipt_fsqm' ),
				);
				break;

			case 'payment' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'icon' => 0xe00a,
					'hidden_label' => false,
					'centered' => false,
					'precision' => '2',
					'noanim' => false,
					'right' => false,
					'fancy' => false,
					'prefix' => '',
					'suffix' => '',
					'grouping' => true,
					'separator' => ',',
					'decimal' => '.',
					'ptitle' => __( 'Payment Method', 'ipt_fsqm' ),
					'ctitle' => __( 'Enter Card Details', 'ipt_fsqm' ),
					'ppmsg' => __( 'You will be redirected to PayPal checkout page once you submit. After you complete your payment, you will be redirected back to our site with a confirmation.', 'ipt_fsqm' ),
				);
				unset( $default['validation'] );
				break;

			case 'textinput' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'icon' => 0xe056,
					'placeholder' => $this->get_default_placeholder( $element ),
					'vertical' => false,
					'centered' => false,
					'type' => 'none', // Can be url, meta, predefined
					'default' => '',
					'readonly' => false,
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => false,
					'filters' => array(
						'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
						'min' => '',
						'max' => '',
						'minSize' => '',
						'maxSize' => '',
					),
					'equals' => '',
				);
				break;

			case 'textarea' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'icon' => 0xe056,
					'placeholder' => $this->get_default_placeholder( $element ),
					'vertical' => false,
					'centered' => false,
					'type' => 'none', // Can be url, meta, predefined
					'default' => '',
					'readonly' => false,
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
						'min' => '',
						'max' => '',
						'minSize' => '',
						'maxSize' => '',
					),
				);
				break;

			case 'password' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => true,
					'confirm_duplicate' => false,
					'placeholder' => $this->get_default_placeholder( $element ),
					'vertical' => false,
					'centered' => false,
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'p_radio' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(), //array(label => value)
					'columns' => '2',
					'others' => false,
					'o_label' => __( 'Others', 'ipt_fsqm' ),
					'vertical' => false,
					'centered' => false,
					'icon' => 0xe18e,
					'shuffle' => false,
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'p_checkbox' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'columns' => '2',
					'others' => false,
					'o_label' => __( 'Others', 'ipt_fsqm' ),
					'vertical' => false,
					'centered' => false,
					'icon' => 0xe18e,
					'shuffle' => false,
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'minCheckbox' => '',
						'maxCheckbox' => '',
					),
				);
				break;

			case 'p_select' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'others' => false,
					'o_label' => __( 'Others', 'ipt_fsqm' ),
					'e_label' => '',
					'vertical' => false,
					'centered' => false,
					'shuffle' => false,
					'multiple' => false,
					'type' => 'none', // Can be url, meta, predefined
					'parameter' => '',
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 's_checkbox' : //Single checkbox
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'centered' => false,
					'checked' => false,
					'icon' => 0xe18e,
				);
				$default['validation'] = array(
					'required' => true,
				);
				break;

			case 'address' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'recipient' => __( 'Recipient', 'ipt_fsqm' ),
					'line_one' => __( 'Address line one', 'ipt_fsqm' ),
					'line_two' => __( 'Address line two', 'ipt_fsqm' ),
					'line_three' => __( 'Address line three', 'ipt_fsqm' ),
					'country' => __( 'Country', 'ipt_fsqm' ),
					'province' => __( 'Province', 'ipt_fsqm' ),
					'zip' => __( 'Postal Code', 'ipt_fsqm' ),
					'preset_country' => '',
					'vertical' => false,
					'centered' => false,
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
						'min' => '',
						'max' => '',
						'minSize' => '',
						'maxSize' => '',
					),
				);
				break;

			case 'keypad' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'mask' => true,
					'multiline' => false,
					'type' => 'qwerty', //keyboard|international|alpha|dvorak|num
					'placeholder' => $this->get_default_placeholder( $element ),
					'vertical' => false,
					'centered' => false,
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'type' => 'all', //phone, url, email, date, number, integer, ipv4, onlyNumberSp, onlyLetterSp, onlyLetterNumber
						'min' => '',
						'max' => '',
						'minSize' => '',
						'maxSize' => '',
					),
				);
				break;

			case 'datetime' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'show_current' => false,
					'type' => 'datetime', //date|time|datetime,
					'date_format' => 'yy-mm-dd',
					'time_format' => 'HH:mm:ss',
					'placeholder' => $this->get_default_placeholder( $element ),
					'vertical' => false,
					'centered' => false,
					'hide_icon' => false,
					'year_range' => '50',
				);
				$default['validation'] = array(
					'required' => true,
					'filters' => array(
						'past' => '',
						'future' => '',
					),
				);
				break;
			case 'p_sorting' :
				$default['m_type'] = 'pinfo';
				$default['tooltip'] = '';
				$default['settings'] = array(
					'hidden_label' => false,
					'options' => array(),
					'vertical' => false,
					'centered' => false,
				);
				break;
			case 'hidden' :
				$default['m_type'] = 'pinfo';
				$default['settings'] = array(
					'hidden_label' => false,
					'type' => 'url', // Can be url, meta, predefined
					'default' => '',
					'parameter' => '',
					'admin_only' => true,
				);
				break;
			case 'repeatable' :
				$default['m_type'] = 'pinfo';
				$default['settings'] = array(
					'icon' => 0xf067,
					'show_icons' => true,
					'hide_label' => false,
					'centered' => false,
					'group' => array(), //SDA
					'min' => '1',
					'num' => '1',
					'max' => '',
					'sortable' => false,
					'deletable' => true,
					'button' => __( 'Add New', 'ipt_fsqm' ),
				);
				break;
			// END PINFO Elements //
		}

		if ( $default['m_type'] == 'design' ) {
			unset( $default['validation'] );
		}

		return apply_filters( 'ipt_fsqm_form_element_structure', $default, $element, $this->form_id );

	}

	public function get_default_element_settings( $element, $m_type ) {
		// Get element definition
		$element_def = $this->get_element_definition( array(
			'type' => $element,
			'm_type' => $m_type,
		) );
		// Set the default title
		$title = sprintf( __( '%1$s Title', 'ipt_fsqm' ), $element_def['title'] );
		$default = array(
			'title' => $title,
		);
		// Set default data for empty element
		switch ( $element ) {
			// If it is not set!
			default:
				$default = false;
				break;

			// MCQ Type - Stored in mcq and populated in report //
			case 'radio' :
			case 'checkbox' :
			case 'select' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Option 1', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						1 => array(
							'label' => __( 'Option 2', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						2 => array(
							'label' => __( 'Option 3', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
					),
				);
				break;

			case 'thumbselect' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'image' => 'https://raw.githubusercontent.com/encharm/Font-Awesome-SVG-PNG/master/black/png/128/briefcase.png',
							'label' => __( 'Briefcase', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						1 => array(
							'image' => 'https://raw.githubusercontent.com/encharm/Font-Awesome-SVG-PNG/master/black/png/128/calendar.png',
							'label' => __( 'Calendar', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						2 => array(
							'image' => 'https://raw.githubusercontent.com/encharm/Font-Awesome-SVG-PNG/master/black/png/128/credit-card.png',
							'label' => __( 'Credit Card', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						3 => array(
							'image' => 'https://raw.githubusercontent.com/encharm/Font-Awesome-SVG-PNG/master/black/png/128/chrome.png',
							'label' => __( 'Chrome', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
					), //
				);
				break;

			case 'slider' :
			case 'range' :
				// Just the title
				break;

			case 'spinners' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Item one', 'ipt_fsqm' ),
							'min' => '',
							'max' => '',
							'step' => '',
						),
						1 => array(
							'label' => __( 'Item two', 'ipt_fsqm' ),
							'min' => '',
							'max' => '',
							'step' => '',
						),
					),
				);
				break;

			case 'grading' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Item one', 'ipt_fsqm' ),
							'prefix' => '',
							'suffix' => '',
							'min' => '',
							'max' => '',
							'step' => '',
						),
						1 => array(
							'label' => __( 'Item two', 'ipt_fsqm' ),
							'prefix' => '',
							'suffix' => '',
							'min' => '',
							'max' => '',
							'step' => '',
						),
					),
				);
				break;

			case 'smileyrating' :
				// Just the title
				break;

			case 'starrating' :
			case 'scalerating' :
				$default['m_type'] = 'mcq';
				$default['settings'] = array(
					'options' => array(
						0 => __( 'Item one', 'ipt_fsqm' ),
						1 => __( 'Item two', 'ipt_fsqm' ),
					),
				);
				break;

			case 'matrix' :
				$default['settings'] = array(
					'rows' => array(
						0 => __( 'Row one', 'ipt_fsqm' ),
						1 => __( 'Row two', 'ipt_fsqm' ),
					),
					'columns' => array(
						0 => __( 'Column one', 'ipt_fsqm' ),
						1 => __( 'Column two', 'ipt_fsqm' ),
					),
					'scores' => array(
						0 => '',
						1 => '',
					),
					'numerics' => array(
						0 => '',
						1 => '',
					),
				);
				break;

			case 'matrix_dropdown' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Option 1', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						1 => array(
							'label' => __( 'Option 2', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
						2 => array(
							'label' => __( 'Option 3', 'ipt_fsqm' ),
							'score' => '',
							'num' => '',
						),
					),
					'rows' => array(
						0 => __( 'Row one', 'ipt_fsqm' ),
						1 => __( 'Row two', 'ipt_fsqm' ),
					),
					'columns' => array(
						0 => __( 'Column one', 'ipt_fsqm' ),
						1 => __( 'Column two', 'ipt_fsqm' ),
					),
					'scores' => array(
						0 => '',
						1 => '',
					), // multiplier
				);
				break;

			case 'likedislike' :
				// Just the title
				break;

			case 'toggle' :
				// Just the title
				break;

			case 'sorting' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Item 1', 'ipt_fsqm' ),
							'score' => '',
						),
						1 => array(
							'label' => __( 'Item 2', 'ipt_fsqm' ),
							'score' => '',
						),
						2 => array(
							'label' => __( 'Item 3', 'ipt_fsqm' ),
							'score' => '',
						),
					),
				);
				break;
			// END MCQ Elements //

			// FEEDBACK Elements - Stored in freetype and emails to admins when filled //
			case 'feedback_large' :
			case 'feedback_small' :
			case 'upload' :
			case 'mathematical' :
				// Just the title
				break;

			case 'feedback_matrix' :
				$default['m_type'] = 'freetype';
				$default['settings'] = array(
					'rows' => array(
						0 => __( 'Row one', 'ipt_fsqm' ),
						1 => __( 'Row two', 'ipt_fsqm' ),
					),
					'columns' => array(
						0 => __( 'Column one', 'ipt_fsqm' ),
						1 => __( 'Column two', 'ipt_fsqm' ),
					),
				);
				break;

			case 'gps' :
			case 'signature' :
				// Just the title
				break;
			// END FEEDBACK Elements //

			// PINFO Elements - Stored in pinfo (named after personal information) //
			case 'f_name' :
			case 'l_name' :
			case 'email' :
			case 'phone' :
			case 'p_name' :
			case 'p_email' :
			case 'p_phone' :
			case 'payment' :
			case 'textinput' :
			case 'textarea' :
			case 'password' :
			case 'repeatable' :
			case 'guestblog' :
				// Just the title
				break;
			case 'p_radio' :
			case 'p_checkbox' :
			case 'p_select' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Option 1', 'ipt_fsqm' ),
							'num' => '',
						),
						1 => array(
							'label' => __( 'Option 2', 'ipt_fsqm' ),
							'num' => '',
						),
						2 => array(
							'label' => __( 'Option 3', 'ipt_fsqm' ),
							'num' => '',
						),
					),
				);
				break;

			case 's_checkbox' : //Single checkbox
			case 'address' :
			case 'keypad' :
			case 'datetime' :
				// Just the title
				break;

			case 'p_sorting' :
				$default['settings'] = array(
					'options' => array(
						0 => array(
							'label' => __( 'Item 1', 'ipt_fsqm' ),
						),
						1 => array(
							'label' => __( 'Item 2', 'ipt_fsqm' ),
						),
						2 => array(
							'label' => __( 'Item 3', 'ipt_fsqm' ),
						),
					),
				);
				break;
			// END PINFO Elements //
		}

		return apply_filters( 'ipt_fsqm_form_element_default_settings', $default, $element, $this->form_id );
	}

	public function get_submission_structure( $element ) {
		$default = array(
			'type' => $element,
		);

		switch ( $element ) {
			default :
				$default = false;
				break;

			// Design Elements //
			case 'captcha' :
				$default['m_type'] = 'design';
				$default['hash'] = '';
				$default['value'] = '';
				break;
			case 'recaptcha' :
				$default['m_type'] = 'design';
				$default['recaptcha'] = '';
				break;
			// End Design Elements //

			// MCQ Type - Stored in mcq and populated in report //
			case 'checkbox' :
			case 'radio' :
			case 'select' :
				$default['m_type'] = 'mcq';
				$default['options'] = array();
				$default['others'] = '';
				$default['scoredata'] = array();
				break;

			case 'thumbselect' :
				$default['m_type'] = 'mcq';
				$default['options'] = array();
				$default['scoredata'] = array();
				break;

			case 'slider' :
				$default['m_type'] = 'mcq';
				$default['value'] = '';
				$default['scoredata'] = array();
				break;

			case 'range' :
				$default['m_type'] = 'mcq';
				$default['values'] = array(
					'min' => '',
					'max' => '',
				);
				$default['scoredata'] = array();
				break;

			case 'grading' :
				$default['m_type'] = 'mcq';
				$default['options'] = array(
					/*
						0 => array(
							'min' => '',
							'max' => '',
							) || 0 => string
						),
					*/
				);
				$default['scoredata'] = array();
				break;

			case 'smileyrating' :
				$default['m_type'] = 'mcq';
				$default['option'] = '';
				$default['feedback'] = '';
				$default['scoredata'] = array();
				break;
			case 'starrating' :
			case 'scalerating' :
			case 'spinners' :
				$default['m_type'] = 'mcq';
				$default['options'] = array(
					/*
						0 => '',
					*/
				);
				if ( $element != 'spinners' ) {
					$default['scoredata'] = array();
				}
				break;

			case 'matrix' :
				$default['m_type'] = 'mcq';
				$default['rows'] = array(
					/*
						0 => array([Columns,...]),
					*/
				);
				$default['scoredata'] = array();
				break;

			case 'matrix_dropdown' :
				$default['m_type'] = 'mcq';
				$default['rows'] = array(
					/*
						0 => array([Columns,...]),
					*/
				);
				$default['scoredata'] = array();
				break;

			case 'likedislike' :
				$default['m_type'] = 'mcq';
				$default['value'] = '';
				$default['feedback'] = '';
				break;

			case 'toggle' :
				$default['m_type'] = 'mcq';
				$default['value'] = false;
				break;

			case 'sorting' :
				$default['m_type'] = 'mcq';
				$default['order'] = array();
				$default['scoredata'] = array();
				break;
			// END MCQ Elements //

			// FEEDBACK Elements - Stored in freetype and emails to admins when filled //
			case 'feedback_large' :
			case 'feedback_small' :
				$default['m_type'] = 'freetype';
				$default['value'] = '';
				$default['score'] = '';
				break;
			case 'upload' :
				$default['m_type'] = 'freetype';
				$default['id'] = array();
			break;
			case 'mathematical' :
				$default['m_type'] = 'freetype';
				$default['value'] = '';
				break;

			case 'feedback_matrix' :
				$default['m_type'] = 'freetype';
				$default['rows'] = array();
				break;

			case 'gps' :
				$default['m_type'] = 'freetype';
				$default['location_name'] = '';
				$default['lat'] = '';
				$default['long'] = '';
				break;

			case 'signature' :
				$default['m_type'] = 'freetype';
				$default['value'] = '';
				break;
			// END FEEDBACK Elements //

			// PINFO Elements - Stored in pinfo (named after personal information) //
			case 'f_name' :
			case 'l_name' :
			case 'email' :
			case 'phone' :
			case 'p_name' :
			case 'p_email' :
			case 'p_phone' :
			case 'textinput' :
			case 'textarea' :
			case 'password' :
			case 'keypad' :
			case 'datetime' :
				$default['m_type'] = 'pinfo';
				$default['value'] = '';
				break;
			case 'guestblog' :
				$default['m_type'] = 'pinfo';
				$default['value'] = '';
				$default['taxonomy'] = array();
				$default['bio'] = '';
				$default['title'] = '';
				break;

			case 'p_radio' :
			case 'p_checkbox' :
			case 'p_select' :
				$default['m_type'] = 'pinfo';
				$default['options'] = array();
				$default['others'] = '';
				break;

			case 's_checkbox' : //Single checkbox
				$default['m_type'] = 'pinfo';
				$default['value'] = false;
				break;

			case 'address' :
				$default['m_type'] = 'pinfo';
				$default['values'] = array(
					'recipient' => '',
					'line_one' => '',
					'line_two' => '',
					'line_three' => '',
					'country' => '',
					'province' => '',
					'zip' => '',
				);
				break;

			case 'p_sorting' :
				$default['m_type'] = 'pinfo';
				$default['order'] = array();
				break;
			case 'payment' :
				$default['m_type'] = 'pinfo';
				$default['value'] = '';
				$default['coupon'] = '';
				$default['couponval'] = '';
				$default['pmethod'] = '';
				// $default['cc'] = array(
				// 	'number' => '',
				// 	'name' => '',
				// 	'expiry' => '',
				// 	'cvv' => '',
				// 	'ctype' => '',
				// );
				break;
			case 'hidden' :
				$default['m_type'] = 'pinfo';
				$default['value'] = '';
				break;
			case 'repeatable' :
				$default['m_type'] = 'pinfo';
				$default['values'] = array(); // SDA input
				break;
			// END PINFO Elements //
		}

		return apply_filters( 'ipt_fsqm_filter_form_data_structure', $default, $element, $this->form_id );
	}

	public function get_default_settings() {
		global $wp_rewrite;

		$settings = array(
			'general' => array(
				'terms_page' => '',
				'terms_phrase' => __( 'By submitting this form, you hereby agree to accept our <a href="%1$s" target="_blank">Terms & Conditions</a>. Your IP address <strong>%2$s</strong> will be stored in our database.', 'ipt_fsqm' ),
				'comment_title' => __( 'Administrator Remarks', 'ipt_fsqm' ),
				'default_comment' => __( 'Processing', 'ipt_fsqm' ),
				'can_edit' => false,
				'edit_time' => '',
			),
			'format' => array(
				'math_format' => false,
			),
			'user' => array(
				'notification_sub' => __( 'We have got your answers.', 'ipt_fsqm' ),
				'notification_msg' => __( 'Thank you %NAME% for taking the quiz/survey/feedback.' . "\n" . 'We have received your answers. You can view it anytime from this link below:' . "\n" . '%TRACK_LINK%' . "\n" . 'We have also attached a copy of your submission.', 'ipt_fsqm' ),
				'update_sub' => __( 'Your submission has been updated', 'ipt_fsqm' ),
				'update_msg' => __( 'Thank you for updating your submission.', 'ipt_fsqm' ),
				'notification_from' => get_bloginfo( 'name' ),
				'notification_email' => get_option( 'admin_email' ),
				'header' => '',
				// 'math_format' => false, # Safely moved to the `format` option
				'smtp' => false,
				'smtp_config' => array(
					'enc_type' => 'ssl',
					'host' => 'smtp.gmail.com',
					'port' => '465',
					'username' => '',
					'password' => '',
				),
				'email_logo' => plugins_url( '/static/front/images/email-logo.png', IPT_FSQM_Loader::$abs_file ),
				'top_line' => true,
				'form_name' => true,
				'show_submission' => true,
				'view_online' => false,
				'view_online_text' => __( 'View Online', 'ipt_fsqm' ),
				'footer_msg' => __( 'You are receiving this email because you have submitted a form.', 'ipt_fsqm' ),
			),
			'admin' => array(
				'email' => get_option( 'admin_email' ),
				'conditional' => array(),
				'header' => '',
				'from' => '',
				'from_name' => get_bloginfo( 'name' ),
				'sub' => __( '[%FORMNAME%][%SITENAME%]New Form Submission Notification', 'ipt_fsqm' ),
				'usub' => __( '[%FORMNAME%][%SITENAME%]Form Update Notification', 'ipt_fsqm' ),
				'fsub' => __( '[%FORMNAME%][%ENAME%]New Feedback Notification', 'ipt_fsqm' ),
				'summary_header' => true,
				'f_summary_header' => true,
				'user_info' => true,
				'f_user_info' => true,
				'mail_submission' => false,
				'send_from_user' => false,
				'reply_to_only' => false,
				'top_line' => true,
				'body' => __( '<p>A new submission has been made. You can visit it at</p><p><strong>%ADMINLINK%</strong></p>', 'ipt_fsqm' ),
				'ubody' => __( '<p>An existing submission has been updated. You can visit it at</p><p><strong>%ADMINLINK%</strong></p>', 'ipt_fsqm' ),
				'footer' => sprintf( __( '<p><em>
				This is an autogenerated email. Please do not respond to this.<br />
				You are receiving this notification because you are one of the email subscribers for the mentioned Feedback.<br />
				If you wish to stop receiving emails, then please go to <a href="%1$sadmin.php?page=ipt_fsqm_dashboard">eForm - Management area</a> and remove your email from the form.<br />
				If you can not access the link, then please contact your administrator.
				</em></p>

				<p>Auto-generated email by<br />eForm - Ultimate WordPress Form Builder Plugin</p>', 'ipt_fsqm' ), get_admin_url() ),
				'email_logo' => plugins_url( '/static/front/images/admin-email-logo.png', IPT_FSQM_Loader::$abs_file ),
			),
			'limitation' => array(
				'email_limit' => '0',
				'ip_limit' => '0',
				'user_limit' => '0',
				'total_limit' => '0',
				'cookie_limit' => '0',
				'ip_limit_msg' => __( 'Submission limit from this IP address has been exceeded.', 'ipt_fsqm' ),
				'user_limit_msg' => __( 'Your submission limit has been exceeded. You can check <a href="%PORTAL_LINK%">your portal page</a> to access previous submissions.', 'ipt_fsqm' ),
				'total_limit_msg' => __( 'The submission for this form has been closed since it has reached it\'s limit.', 'ipt_fsqm' ),
				'cookie_limit_msg' => __( 'You have already submitted the form and you can not submit it again.', 'ipt_fsqm' ),
				'total_msg' => __( 'Only %1$d submissions left. %2$d already filled in.', 'ipt_fsqm' ),
				'logged_in' => false,
				'logged_in_fallback' => 'show_login', // show_login => Show login form | redirect => Redirect to a specific page
				'non_logged_redirect' => add_query_arg( 'redirect_to', '_self_', ( $wp_rewrite ? wp_login_url() : home_url( 'wp-login.php' ) ) ),
				// Show only to logged out
				'logged_out' => false,
				'logged_msg' => __( 'Sorry, you can not submit this form.', 'ipt_fsqm' ),
				// New limitations v3.0.0
				'interval_limit' => '0',
				'interval_msg' => __( 'Slow down there. You need to wait for atleast %1$s before submitting again.', 'ipt_fsqm' ),
				'expiration_limit' => '',
				'expiration_msg' => __( 'This form has expired', 'ipt_fsqm' ),
				'starting_limit' => '',
				'starting_title' => __( 'This form is not available yet', 'ipt_fsqm' ),
				'starting_msg' => __( 'Thank you for your interest. Unfortunately we are still not there yet. Take a look at the counter and get back when we have started.', 'ipt_fsqm' ),
				'no_edit_expiration' => false,
				'submission_info' => false,
				'submission_msg' => __( 'It looks like you have already submitted the form. You can submit it again however.', 'ipt_fsqm' ),
			),
			'type_specific' => array(
				'pagination' => array(
					'show_progress_bar' => true,
					'decimal_point' => '2',
					'progress_bar_bottom' => false,
				),
				'tab' => array(
					'auto_progress' => false,
					'auto_progress_delay' => '1500',
					'auto_submit' => false,
					'can_previous' => true,
					'block_previous' => false,
					'any_tab' => false,
					'scroll' => true,
					'scroll_on_error' => true,
				),
				'normal' => array(
					'wrapper' => true,
					'center_heading' => false,
				),
				'scroll' => array(
					'progress' => true,
					'message' => true,
					'offset' => '0',
				),
			),
			'buttons' => array(
				'next' => __( 'Next', 'ipt_fsqm' ),
				'prev' => __( 'Previous', 'ipt_fsqm' ),
				'submit' => __( 'Submit', 'ipt_fsqm' ),
				'supdate' => __( 'Update', 'ipt_fsqm' ),
				'reset' => '',
				'reset_msg' => __( 'This will reset your form and the action can not be undone. Are you sure?', 'ipt_fsqm' ),
				'conditional' => array(
					'active' => false, // True to use conditional logic, false to ignore
					'status' => false, // Initial status -> True for shown, false for hidden
					'change' => true, // Change to status -> True for shown, false for hide
					// 'relation' => 'indi', // AND, OR, INDI relationship to verify against the logic (and,or,indi) ALWAYS indi
					'logic' => array( // element dependent logics
						// 0 => array(
						// 	'm_type' => '', // Mother type
						// 	'key' => '', // Key of the element
						// 	'check' => 'val', //value(val), length(len)
						// 	'operator' => 'eq', // equals(eq), not equals(neq), greater than(gt), less than(lt), contains (ct), does not contain (dct), starts with (sw), ends with (ew)
						// 	'value' => '',
						// 	'rel' => 'and', // (and, or)
						// ),
					),
				),
				'conditional_next' => array(
					'active' => false, // True to use conditional logic, false to ignore
					'status' => false, // Initial status -> True for shown, false for hidden
					'change' => true, // Change to status -> True for shown, false for hide
					// 'relation' => 'indi', // AND, OR, INDI relationship to verify against the logic (and,or,indi) ALWAYS indi
					'logic' => array( // element dependent logics
						// 0 => array(
						// 	'm_type' => '', // Mother type
						// 	'key' => '', // Key of the element
						// 	'check' => 'val', //value(val), length(len)
						// 	'operator' => 'eq', // equals(eq), not equals(neq), greater than(gt), less than(lt), contains (ct), does not contain (dct), starts with (sw), ends with (ew)
						// 	'value' => '',
						// 	'rel' => 'and', // (and, or)
						// ),
					),
				),
				'hide' => false,
			),
			'save_progress' => array(
				'auto_save' => false,
				'show_restore' => true,
				'interval_save' => false,
				'interval' => '30',
				'interval_title' => __( 'Save Progress', 'ipt_fsqm' ),
				'interval_saved_title' => __( 'Form Saved', 'ipt_fsqm' ),
				'restore_msg' => __( 'The form has been restored from your last edit. If wish to start over, please click the button', 'ipt_fsqm' ),
				'restore_head' => __( 'Form successfully restored', 'ipt_fsqm' ),
				'restore_reset' => __( 'Start Over', 'ipt_fsqm' ),
			),
			'submission' => array(
				'no_auto_complete' => false,
				'reset_on_submit' => false,
				'reset_delay' => '10',
				'reset_msg' => __( 'Resetting in %time% second(s)', 'ipt_fsqm' ),
				'process_title' => __( 'Processing your request', 'ipt_fsqm' ),
				'success_title' => __( 'Your form has been submitted', 'ipt_fsqm' ),
				'success_message' => __( 'Thank you for giving your answers', 'ipt_fsqm' ),
				'update_message' => __( 'Thank you for updating your answers', 'ipt_fsqm' ),
				'log_ip' => true,
				'log_registered_user' => true,
				'url_track' => false,
				'url_track_key' => 'fsqmTrack',
			),
			'ganalytics' => array(
				'enabled' => false,
				'manual_load' => false,
				'tracking_id' => '',
				'cookie' => 'auto',
			),
			'redirection' => array(
				'type' => 'none', // 'none'|'flat'|'score'|'conditional'
				'delay' => '1000',
				'message' => __( 'You will be redirected in %TIME% seconds(s). If your browser fails to redirect, then please <a href="%LINK%">Click Here</a>.', 'ipt_fsqm' ),
				'top' => false,
				'url' => '%TRACKBACK%',
				'score' => array(),
				'rscore' => array(),
				'conditional' => array(),
				'rtype' => 'percentage',
			),
			'ranking' => array(
				'precision' => 2,
				'enabled' => false,
				'title' => __( 'Designation', 'ipt_fsqm' ),
				'ranks' => array(),
				'rranks' => array(),
				'rtype' => 'percentage',
			),

			// Trackback and email modification
			'summary' => array(
				'blacklist' => '',
				'show_details' => true,
				'show_elements' => true,
				'f_name' => true, // Done
				'l_name' => true, // Done
				'email' => true, // Done
				'phone' => true, // Done
				'ip' => true, // Done
				'total_score' => true, // Done
				'tscore_title' => __( 'Score Obtained', 'ipt_fsqm' ),
				'average_score' => false,
				'ascore_title' => __( 'Average Score (based on %1$d submissions)', 'ipt_fsqm' ),
				'designation' => true, // Done
				'user_account' => true, // Done
				'link' => true, // Done
				'individual_score' => true, // Done
				'hide_options' => false, // Done
				'highlight_correct' => false, // Done
				'correct_color' => '#519548',
				'hide_unattempted' => false,
				'show_design' => false,
				'id_format' => __( '#%1$\'010d | On %2$s', 'ipt_fsqm' ),
				'id_dt_format' => 'Y-m-d H:i:s',
				'score_title' => __( 'Score Obtained/Total', 'ipt_fsqm' ), // Done
				'before' => '', // Done
				'after' => '', // Done
			),
			'trackback' => array(
				'show_full' => true,
				'show_print' => true,
				'full_title' => __( 'Submission Data', 'ipt_fsqm' ),
				'print_title' => __( 'Print and Summary', 'ipt_fsqm' ),
				'show_trends' => false,
				'trends_title' => __( 'Form Statistics', 'ipt_fsqm' ),
			),

			'email_template' => array(
				'accent_bg' => '#0db9ea',
				'accent_color' => '#ffffff',
				'color' => '#999999',
				'h_color' => '#333333',
				'm_color' => '#95a5a6',
				'a_color' => '#1155cc',
				't_color' => '#f6f4f5',
			),

			'social' => array(
				'show' => false,
				'sites' => array( 'facebook_url' => true, 'twitter_url' => true, 'google_url' => true, 'pinterest_url' => true ),
				'image' => '',
				'facebook_app' => '',
				'url' => '%SELF%',
				'fb_url' => home_url( '/' ),
				'title' => '%NAME%',
				'description' => 'I have scored %SCORE% in the quiz. Check yours now.',
				'twitter_via' => '',
				'twitter_hash' => 'quiz',
				'follow_on_social' => false,
				'auto_append_user' => false,
			),

			// Standalone SEO
			'standalone' => array(
				'title' => '',
				'description' => '',
				'image' => '',
			),

			// Add Timer Limit
			'timer' => array(
				'time_limit_type' => 'none', // overall | page_specific
				'overall_limit' => '120',
			),

			// Add stopwatch
			'stopwatch' => array(
				'enabled' => false,
				'title' => __( 'Completion Time', 'ipt_fsqm' ),
				'seconds' => true,
				'hours' => true,
				'days' => false,
				'add_on_edit' => true,
				'rotate' => true,
				'hidden' => false,
			),

			// Integration
			'integration' => array(
				'conditional' => array(
					'active' => false, // True to use conditional logic, false to ignore
					// 'relation' => 'indi', // AND, OR, INDI relationship to verify against the logic (and,or,indi) ALWAYS indi
					'logic' => array( // element dependent logics
						// 0 => array(
						// 	'm_type' => '', // Mother type
						// 	'key' => '', // Key of the element
						// 	'check' => 'val', //value(val), length(len)
						// 	'operator' => 'eq', // equals(eq), not equals(neq), greater than(gt), less than(lt), contains (ct), does not contain (dct), starts with (sw), ends with (ew)
						// 	'value' => '',
						// 	'rel' => 'and', // (and, or)
						// ),
					),
				),
				'mailchimp' => array(
					'enabled' => false,
					'api' => '',
					'list_id' => '',
					'double_optin' => true,
				),
				'aweber' => array(
					'enabled' => false,
					'authorization_code' => '',
					'list_id' => '',
					'consumerKey' => '',
					'consumerSecret' => '',
					'accessKey' => '',
					'accessSecret' => '',
					'prevac' => '',
				),
				'get_response' => array(
					'enabled' => false,
					'api' => '',
					'campaign_id' => '',
				),
				'campaign_monitor' => array(
					'enabled' => false,
					'api' => '',
					'list_id' => '',
				),
				'mymail' => array(
					'enabled' => false,
					'list_ids' => array(),
					'overwrite' => false,
				),
				'sendy' => array(
					'enabled' => false,
					'list_id' => '',
					'url' => '',
				),
				'active_campaign' => array(
					'enabled' => false,
					'url' => '',
					'api' => '',
					'list_id' => '',
				),
				'mailpoet' => array(
					'enabled' => false,
					'list_ids' => array(),
				),
				'formhandler' => array(
					'enabled' => false,
					'url' => '',
					'method' => 'post',
					'metaarray' => false,
					'meta' => array(),
				),
				'enormail' => array(
					'enabled' => false,
					'api' => '',
					'list_id' => '',
				),
				'mailerlite' => array(
					'enabled' => false,
					'api' => '',
					'group_id' => '',
				),
			),

			'theme' => array(
				'template' => 'material-default',
				'logo' => '',
				'waypoint' => true,
				'material' => array(
					'skin' => 'light', // Light or Dark
					'alternate_pb' => false,
					'width' => '100%',
					'colors' => array(
						'primary-color-dark' => '#00796B', // Dark Primary Color
						'primary-color' => '#009688', // Primary Color
						'primary-color-light' => '#B2DFDB', // Light Primary Color
						'primary-color-text' => '#FFFFFF', // Icon Color
						'accent-color' => '#1de9b6', // Accent Color
						'background-color' => '#fff', //colorpicker
						'primary-text-color' => '#212121',
						'secondary-text-color' => '#757575',
						'border-color' => '#9e9e9e', //base
						'divider-color' => '#eeeeee', //l3
						'disabled-color' => '#f5f5f5', //l4
						'disabled-color-text' => '#eeeeee', //l3
						'ui-bg-color' => '#e0e0e0', // l2
						'widget-bg-color' => '#fafafa', //l5
					),
					'bg' => array(
						'enabled' => false, // Whether to do bg mods
						'background-image' => '', //uploader
						'background-position' => '0% 0%', //text
						'background-size' => 'auto', //text
						'background-repeat' => 'repeat', //select
						'background-origin' => 'padding-box', //select
						'background-clip' => 'border-box', // select
						'background-attachment' => 'scroll', // select
					),
				),
				'custom_style' => false,
				'style' => array(
					'custom_font' => false,
					'head_font' => 'roboto',
					'body_font' => 'roboto',
					'base_font_size' => 14,
					'head_font_typo' => array(
						'bold' => false,
						'italic' => false,
					),
					'custom' => '',
				),
			),

			// Payment Integration
			'payment' => array(
				'enabled' => false,
				'sub_on_success' => false,
				'lock_message' => __( 'Sorry but your submission would be visible only after completing <a href="%RETRY_LINK%">payment</a>.' ),
				'formula' => '', // MCQ ID of the mathematical field
				'currency' => 'USD',
				'c_prefix' => '$',
				'c_suffix' => '',
				'coupons' => array(
					// 0 => array(
					// 	'code' => 'xyz',
					// 	'type' => 'per', // percentage => percentage, amount => value
					// 	'value' => '',
					// 	'min' => '',
					// ),
				),
				'type' => 'paypal_d',  // direct / express
				'itemname' => '',
				'itemdescription' => '',
				'itemsku' => '',
				'invoicenumber' => 'INV-{id}',
				'success_msg' => __( 'Your payment was successful. Please note down the details below.', 'ipt_fsqm' ),
				'success_sub' => __( 'Payment Successful - %FORMNAME%', 'ipt_fsqm' ),
				'error_msg' => __( 'Your payment could not be processed at this moment. Please try again. If any amount was deducted, it will be refunded automatically.', 'ipt_fsqm' ),
				'error_sub' => __( 'Payment Error - %FORMNAME%', 'ipt_fsqm' ),
				'cancel_msg' => __( 'Your payment was cancelled before it could be completed. You can try to reinitiate the payment using the form below.', 'ipt_fsqm' ),
				'cancel_sub' => __( 'Payment Cancelled - %FORMNAME%', 'ipt_fsqm' ),
				'retry_uemail_sub' => __( 'We have processed repayment of %1$s', 'ipt_fsqm' ),
				'retry_uemail_msg' => __( 'Your payment has been processed. The status is given below.', 'ipt_fsqm' ),
				'retry_aemail_sub' => __( 'User has retried form payment for %1$s', 'ipt_fsqm' ),
				'redir_aemail_sub' => __( 'Payment status for %1$s', 'ipt_fsqm' ),
				// Basic Integrations
				// Paypal
				'paypal' => array(
					'enabled' => false,
					'mode' => 'sandbox', // sandbox / live
					'allow_direct' => true,
					'partner' => '',
					'conf_sub' => __( 'Your paypal payment has been processed for %1$s', 'ipt_fsqm' ),
					'conf_msg' => __( 'Your PayPal payment has been processed. The status is given below.', 'ipt_fsqm' ),
					'd_settings' => array(
						'client_id' => '',
						'client_secret' => '',
					),
					'label_paypal_e' => __( 'Paypal Account', 'ipt_fsqm' ),
					'label_paypal_d' => __( 'Credit Card (Paypal)', 'ipt_fsqm' ),
				),
				// Stripe
				'stripe' => array(
					'enabled' => false,
					'label_stripe' => __( 'Credit Card (Stripe)', 'ipt_fsqm' ),
					'zero_decimal' => false,
					'api' => '',
				),
				// WooCommerce
				'woocommerce' => array(
					'enabled' => false,
					'paid_flag_state' => 'processing',
					'product_id' => '',
					'cond_pid' => array(), // Conditional product ID
					'mathematical' => '',
					'additional_attr' => '',
					'quantity_item' => '',
					'redirect' => 'checkout', // 'cart' | 'checkout'
				),
			),
			'core' => array(
				// User Registration
				'reg' => array(
					'enabled' => false,
					'username_id' => '', // pinfo element
					'password_id' => '', // pinfo element
					'hide_pinfo' => true,
					'metaarray' => false,
					'meta' => array(),
					'hide_meta' => false,
					'role' => 'wp_default',
				),
				// Guest Blogging/Posting
				'post' => array(
					'enabled' => false,
					'user_id' => '',
					'bio' => false,
					'bio_title' => __( 'About you', 'ipt_fsqm' ),
					'guest_msg' => __( "<hr>This guest article was submitted by %NAME%.<hr>\n\n<div class=\"eform-post-data\"><img src=\"%AVATAR%\" class=\"alignleft\" /><blockquote>%BIO%</blockquote></div>", 'ipt_fsqm' ),
					'add_msg' => '',
					'feature_image' => '',
					'post_type' => 'post',
					'taxonomies' => array(),
					'taxnomy_single' => array(), // whether to print checkboxes or radio for a taxonomy
					'taxonomy_required' => array(), // Whether the tax is required
					'metaarray' => false,
					'meta' => array(),
					'status' => 'draft',
				),
				// User meta update
				'user_meta' => array(
					'enabled' => false,
					'metaarray' => false,
					'meta' => array(),
				),
			),
		);

		return apply_filters( 'ipt_fsqm_filter_default_settings', $settings, $this->form_id );
	}

	public function get_available_themes() {
		$path = plugins_url( '/static/front/css/ui-themes/', IPT_FSQM_Loader::$abs_file );
		$material_path = plugins_url( '/material/css/', IPT_FSQM_Loader::$abs_file );
		$upload_dir_info = wp_upload_dir();
		$themes = array(
			'material-light' => array(
				'label' => __( 'Material Themes Light', 'ipt_fsqm' ),
				'ui-class' => 'EForm_Material_UI',
				'options' => array(),
				'themes' => array(
					'material-default' => array(
						'label' => __( 'Teal Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material.css',
							),
						),
						'colors' => array( '009688', '00796b', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-bg' => array(
						'label' => __( 'Blue Grey Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-blue-grey.css',
							),
						),
						'colors' => array( '607d8b', '455a64', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-red' => array(
						'label' => __( 'Red Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-red.css',
							),
						),
						'colors' => array( 'f44336', 'd32f2f', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-pink' => array(
						'label' => __( 'Pink Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-pink.css',
							),
						),
						'colors' => array( 'e91e63', 'c2185b', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-purple' => array(
						'label' => __( 'Purple Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-purple.css',
							),
						),
						'colors' => array( '9c27b0', '7b1fa2', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-deep-purple' => array(
						'label' => __( 'Deep Purple Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-deep-purple.css',
							),
						),
						'colors' => array( '673ab7', '512da8', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-indigo' => array(
						'label' => __( 'Indigo Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-indigo.css',
							),
						),
						'colors' => array( '3f51b5', '303f9f', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-blue' => array(
						'label' => __( 'Blue Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-blue.css',
							),
						),
						'colors' => array( '2196f3', '1976d2', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-light-blue' => array(
						'label' => __( 'Light Blue Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-light-blue.css',
							),
						),
						'colors' => array( '03a9f4', '0288d1', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-cyan' => array(
						'label' => __( 'Cyan Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-cyan.css',
							),
						),
						'colors' => array( '00bcd4', '0097a7', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-green' => array(
						'label' => __( 'Green Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-green.css',
							),
						),
						'colors' => array( '4caf50', '388e3c', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-light-green' => array(
						'label' => __( 'Light Green Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-light-green.css',
							),
						),
						'colors' => array( '8bc34a', '689f38', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-lime' => array(
						'label' => __( 'Lime Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-lime.css',
							),
						),
						'colors' => array( 'cddc39', 'afb42b', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-yellow' => array(
						'label' => __( 'Yellow Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-yellow.css',
							),
						),
						'colors' => array( 'ffeb3b', 'fbc02d', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-amber' => array(
						'label' => __( 'Amber Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-amber.css',
							),
						),
						'colors' => array( 'ffc107', 'ffa000', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-orange' => array(
						'label' => __( 'Orange Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-orange.css',
							),
						),
						'colors' => array( 'ff9800', 'f57c00', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-deep-orange' => array(
						'label' => __( 'Deep Orange Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-deep-orange.css',
							),
						),
						'colors' => array( 'ff5722', 'e64a19', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-brown' => array(
						'label' => __( 'Brown Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-brown.css',
							),
						),
						'colors' => array( '795548', '5d4037', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-grey' => array(
						'label' => __( 'Grey Light Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-grey.css',
							),
						),
						'colors' => array( '9e9e9e', '616161', 'ffffff', '424242' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
				),
			),
			'material-dark' => array(
				'label' => __( 'Material Themes Dark', 'ipt_fsqm' ),
				'ui-class' => 'EForm_Material_UI',
				'options' => array(),
				'themes' => array(
					'material-d-default' => array(
						'label' => __( 'Teal Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d.css',
							),
						),
						'colors' => array( '009688', '00796b', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-bg' => array(
						'label' => __( 'Blue Grey Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-blue-grey.css',
							),
						),
						'colors' => array( '607d8b', '455a64', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-red' => array(
						'label' => __( 'Red Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-red.css',
							),
						),
						'colors' => array( 'f44336', 'd32f2f', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-pink' => array(
						'label' => __( 'Pink Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-pink.css',
							),
						),
						'colors' => array( 'e91e63', 'c2185b', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-purple' => array(
						'label' => __( 'Purple Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-purple.css',
							),
						),
						'colors' => array( '9c27b0', '7b1fa2', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-deep-purple' => array(
						'label' => __( 'Deep Purple Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-deep-purple.css',
							),
						),
						'colors' => array( '673ab7', '512da8', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-indigo' => array(
						'label' => __( 'Indigo Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-indigo.css',
							),
						),
						'colors' => array( '3f51b5', '303f9f', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-blue' => array(
						'label' => __( 'Blue Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-blue.css',
							),
						),
						'colors' => array( '2196f3', '1976d2', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-light-blue' => array(
						'label' => __( 'Light Blue Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-light-blue.css',
							),
						),
						'colors' => array( '03a9f4', '0288d1', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-cyan' => array(
						'label' => __( 'Cyan Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-cyan.css',
							),
						),
						'colors' => array( '00bcd4', '0097a7', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-green' => array(
						'label' => __( 'Green Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-green.css',
							),
						),
						'colors' => array( '4caf50', '388e3c', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-light-green' => array(
						'label' => __( 'Light Green Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-light-green.css',
							),
						),
						'colors' => array( '8bc34a', '689f38', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-lime' => array(
						'label' => __( 'Lime Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-lime.css',
							),
						),
						'colors' => array( 'cddc39', 'afb42b', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-yellow' => array(
						'label' => __( 'Yellow Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-yellow.css',
							),
						),
						'colors' => array( 'ffeb3b', 'fbc02d', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-amber' => array(
						'label' => __( 'Amber Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-amber.css',
							),
						),
						'colors' => array( 'ffc107', 'ffa000', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-orange' => array(
						'label' => __( 'Orange Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-orange.css',
							),
						),
						'colors' => array( 'ff9800', 'f57c00', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-deep-orange' => array(
						'label' => __( 'Deep Orange Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-deep-orange.css',
							),
						),
						'colors' => array( 'ff5722', 'e64a19', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-brown' => array(
						'label' => __( 'Brown Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-brown.css',
							),
						),
						'colors' => array( '795548', '5d4037', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
					'material-d-grey' => array(
						'label' => __( 'Grey Dark Color Scheme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$material_path . 'material-d-grey.css',
							),
						),
						'colors' => array( '9e9e9e', '616161', '3A434A', 'e0e0e0' ),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
					),
				),
			),
			'material-custom' => array(
				'label' => __( 'Material Themes Custom', 'ipt_fsqm' ),
				'ui-class' => 'EForm_Material_UI',
				'options' => array(),
				'themes' => array(
					'material-custom' => array(
						'label' => __( 'Customizable Material Theme', 'ipt_fsqm' ),
						'src' => array(
							'common' => array(
								$upload_dir_info['baseurl'] . '/eform-custom-material/form-theme-' . $this->form_id . '.css',
							),
						),
						'js' => array(),
						'has_option' => true,
						'option_container' => 'eform-material-options',
						'option_callback' => array( $this, 'material_options' ),
						'skip_primary_css' => true,
						'user_portal_css' => plugins_url( 'material/css/user-portal-structure.css', IPT_FSQM_Loader::$abs_file ),
						'leaderboard-css' => plugins_url( 'material/css/leaderboard-structure.css', IPT_FSQM_Loader::$abs_file ),
						'admin_save_cb' => array( $this, 'material_custom' ),
					),
				),
			),
			/* Disable bootstrap for now - It needs serious modifications */
			// 'bootstrap' => array(
			// 	'label' => __( 'Bootstrap Themes', 'ipt_fsqm' ),
			// 	'themes' => array(
			// 		'bootstrap' => array(
			// 			'label' => 'Bootstrap Basic',
			// 			'src' => array(
			// 				'common' => array(
			// 					$path . 'bootstrap/1.10/jquery-ui-1.10.3.custom.css',
			// 					$path . 'bootstrap/1.10/jquery-ui-1.10.3.theme.css',
			// 					$path . 'bootstrap/form.css',
			// 				),
			// 			),
			// 			'colors' => array( 'eeeeee', 'dddddd', 'cccccc', '999999', '333333', '428bca', '52a8ec' ),
			// 		),
			// 	),
			// ),
		);

		return apply_filters( 'ipt_fsqm_filter_available_themes', $themes );
	}

	public function get_theme_by_id( $id ) {
		$return = array(
			'label' => '',
			'src' => array(),
			'js' => array(),
			'icons' => '333', // 000 | 333 | 666 | ccc | ddd | fff
			'has_option' => false,
			'option_callback' => '',
			'skip_primary_css' => false,
			'user_portal_css' => '',
			'ui-class' => null,
			'leaderboard-css' => '',
			'admin_save_cb' => null,
			'theme_id' => $id,
		);
		$themes = $this->get_available_themes();
		$theme_found = false;
		foreach ( $themes as $theme_type ) {
			foreach ( $theme_type['themes'] as $theme_id => $theme ) {
				if ( $theme_id == $id ) {
					$return = $this->merge_elements( $theme, $return );
					if ( isset( $theme_type['ui-class'] ) ) {
						$return['ui-class'] = $theme_type['ui-class'];
					}
					$theme_found = true;
					break 2;
				}
			}
		}
		// Revert to material if no theme was found
		if ( false === $theme_found ) {
			return $this->get_theme_by_id( 'material-default' );
		}
		global $wp_version;
		$return['include'] = array();
		if ( version_compare( $wp_version, '3.6' ) < 0 ) {
			if ( isset( $return['src']['1.9'] ) ) {
				$return['include'] = array_merge( $return['include'], (array) $return['src']['1.9'] );
			}
		} else {
			if ( isset( $return['src']['1.10'] ) ) {
				$return['include'] = array_merge( $return['include'], (array) $return['src']['1.10'] );
			}
		}
		if ( isset( $return['src']['common'] ) ) {
			$return['include'] = array_merge( $return['include'], (array) $return['src']['common'] );
		}

		//Append the version
		foreach( $return['include'] as $i_key => $href ) {
			$return['include'][$i_key] = add_query_arg('version', IPT_FSQM_Loader::$version, $href);
		}

		return $return;
	}

	public function material_options( $form ) {
		// Needs to be overriden in the admin class
		error_log( __( 'material_options needs to be overriden' ) );
	}

	public function material_custom( $return_id, $name, $settings, $layout, $save_process, $form_type, $form_category ) {
		// Needs to be overriden in the admin class
		error_log( __( 'material_custom needs to be overriden' ) );
	}

	public function get_available_webfonts() {
		$web_fonts = array(
			'oswald' => array(
				'label' => "'Oswald', 'Arial Narrow', sans-serif",
				'include' => 'Oswald',
			),
			'roboto' => array(
				'label' => "'Roboto', Tahoma, Geneva, sans-serif",
				'include' => 'Roboto',
			),
			'quando' => array(
				'label' => "Quando, Georgia, serif",
				'include' => 'Quando',
			),
			'signika_negative' => array(
				'label' => "'Signika Negative', Verdana, sans-serif",
				'include' => 'Signika+Negative',
			),
			'lobster' => array(
				'label' => "'Lobster', Georgia, Times, serif",
				'include' => 'Lobster',
			),
			'cabin' => array(
				'label' => "'Cabin', Helvetica, Arial, sans-serif",
				'include' => 'Cabin',
			),
			'allerta' => array(
				'label' => "'Allerta', Helvetica, Arial, sans-serif",
				'include' => 'Allerta',
			),
			'crimson' => array(
				'label' => "'Crimson Text', Georgia, Times, serif",
				'include' => 'Crimson+Text',
			),
			'arvo' => array(
				'label' => "'Arvo', Georgia, Times, serif",
				'include' => 'Arvo',
			),
			'pt_sans' => array(
				'label' => "'PT Sans', Helvetica, Arial, sans-serif",
				'include' => 'PT+Sans',
			),
			'dancing_script' => array(
				'label' => "'Dancing Script', Georgia, Times, serif",
				'include' => 'Dancing+Script',
			),
			'josefin_sans' => array(
				'label' => "'Josefin Sans', Helvetica, Arial, sans-serif",
				'include' => 'Josefin+Sans',
			),
			'allan' => array(
				'label' => "'Allan', Helvetica, Arial, sans-serif",
				'include' => 'Allan',
			),
			'cardo' => array(
				'label' => "'Cardo', Georgia, Times, serif",
				'include' => 'Cardo',
			),
			'molengo' => array(
				'label' => "'Molengo', Georgia, Times, serif",
				'include' => 'Molengo',
			),
			'lekton' => array(
				'label' => "'Lekton', Helvetica, Arial, sans-serif",
				'include' => 'Lekton',
			),
			'droid_sans' => array(
				'label' => "'Droid Sans', Helvetica, Arial, sans-serif",
				'include' => 'Droid+Sans',
			),
			'droid_serif' => array(
				'label' => "'Droid Serif', Georgia, Times, serif",
				'include' => 'Droid+Serif',
			),
			'corben' => array(
				'label' => "'Corben', Georgia, Times, serif",
				'include' => 'Corben',
			),
			'nobile' => array(
				'label' => "'Nobile', Helvetica, Arial, sans-serif",
				'include' => 'Nobile',
			),
			'ubuntu' => array(
				'label' => "'Ubuntu', Helvetica, Arial, sans-serif",
				'include' => 'Ubuntu',
			),
			'vollkorn' => array(
				'label' => "'Vollkorn', Georgia, Times, serif",
				'include' => 'Vollkorn',
			),
			'bree_serif' => array(
				'label' => "'Bree Serif', Georgia, serif",
				'include' => 'Bree+Serif',
			),
			'open_sans' => array(
				'label' => "'Open Sans', Verdana, Helvetica, sans-serif",
				'include' => 'Open+Sans',
			),
			'bevan' => array(
				'label' => "'Bevan', Georgia, serif",
				'include' => 'Bevan',
			),
			'pontano_sans' => array(
				'label' => "'Pontano Sans', Verdana, Helvetica, sans-serif",
				'include' => 'Pontano+Sans',
			),
			'abril_fatface' => array(
				'label' => "'Abril Fatface', Georgia, serif",
				'include' => 'Abril+Fatface',
			),
			'average' => array(
				'label' => "'Average', Garamond, Georgia, serif",
				'include' => 'Average',
			),
			'lato' => array(
				'label' => "'Lato', sans-serif",
				'include' => 'Lato',
			),
			'Roboto_Condensed' => array(
				'label' => "'Roboto Condensed', 'Arial', sans-serif",
				'include' => 'Roboto+Condensed',
			),
			'Nato_Sans' => array(
				'label' => "'Nato Sans', Arial, sans-serif",
				'include' => 'Nato+Sans',
			),
			'Titillium Web' => array(
				'label' => "'Titillium Web', Arial, serif",
				'include' => 'Titillium+Web',
			),
			'Oxygen' => array(
				'label' => "'Oxygen', Arial, serif",
				'include' => 'Oxygen',
			),
			'Crafty_Girls' => array(
				'label' => "'Crafty Girls', cursive",
				'include' => 'Crafty+Girls',
			),
			'Dancing_Script' => array(
				'label' => "'Dancing Script', Arial, serif",
				'include' => 'Dancing+Script',
			),
			'Cuprum' => array(
				'label' => "'Cuprum', Arial, serif",
				'include' => 'Cuprum',
			),
			'Josefin_Sans' => array(
				'label' => "'Josefin Sans', sans-serif",
				'include' => 'Josefin+Sans',
			),
			'Philosopher' => array(
				'label' => "'Philosopher', sans-serif",
				'include' => 'Philosopher',
			),
			'Libre_Baskerville' => array(
				'label' => "'Libre Baskerville', serif",
				'include' => 'Libre+Baskerville',
			),
			'Merriweather_Sans' => array(
				'label' => "'Merriweather Sans', sans-serif",
				'include' => 'Merriweather+Sans',
			),
			'Asap' => array(
				'label' => "'Asap', sans-serif",
				'include' => 'Asap',
			),
			'Rokkitt' => array(
				'label' => "'Rokkitt', serif",
				'include' => 'Rokkitt',
			),
			'Gilda_Display' => array(
				'label' => "'Gilda Display', serif",
				'include' => 'Gilda+Display',
			),
			'Pinyon_Script' => array(
				'label' => "'Pinyon Script', cursive",
				'include' => 'Pinyon+Script',
			),
			'Tinos' => array(
				'label' => "'Tinos', serif",
				'include' => 'Tinos',
			),
			'Cabin_Condensed' => array(
				'label' => "'Cabin Condensed', sans-serif",
				'include' => 'Cabin+Condensed',
			),
			'Montserrat_Alternates' => array(
				'label' => "'Montserrat Alternates', sans-serif",
				'include' => 'Montserrat+Alternates',
			),
			'PT_Sans_Caption' => array(
				'label' => "'PT Sans Caption', sans-serif",
				'include' => 'PT+Sans+Caption',
			),
			'Economica' => array(
				'label' => "'Economica', sans-serif",
				'include' => 'Economica',
			),
			'Playfair_Display_SC' => array(
				'label' => "'Playfair Display SC', serif",
				'include' => 'Playfair+Display+SC',
			),
			'Hammersmith_One' => array(
				'label' => "'Hammersmith One', sans-serif",
				'include' => 'Hammersmith+One',
			),
			'Exo' => array(
				'label' => "'Exo', sans-serif",
				'include' => 'Exo',
			),
			'Poiret_One' => array(
				'label' => "'Poiret One', cursive",
				'include' => 'Poiret+One',
			),
			'Oleo_Script' => array(
				'label' => "'Oleo Script', cursive",
				'include' => 'Oleo+Script',
			),
			'Satisfy' => array(
				'label' => "'Satisfy', cursive",
				'include' => 'Satisfy',
			),
			'Chivo' => array(
				'label' => "'Chivo', sans-serif",
				'include' => 'Chivo',
			),
			'Marvel' => array(
				'label' => "'Marvel', sans-serif",
				'include' => 'Marvel',
			),
			'Quattrocento' => array(
				'label' => "'Quattrocento', serif",
				'include' => 'Quattrocento',
			),
			'Metrophobic' => array(
				'label' => "'Metrophobic', sans-serif",
				'include' => 'Metrophobic',
			),
			'Judson' => array(
				'label' => "'Judson', serif",
				'include' => 'Judson',
			),
			'Arbutus_Slab' => array(
				'label' => "'Arbutus Slab', serif",
				'include' => 'Arbutus+Slab',
			),
			'Electrolize' => array(
				'label' => "'Electrolize', sans-serif",
				'include' => 'Electrolize',
			),
			'Varela' => array(
				'label' => "'Varela', sans-serif",
				'include' => 'Varela',
			),
			'Julius_Sans_One' => array(
				'label' => "'Julius Sans One', sans-serif",
				'include' => 'Julius+Sans+One',
			),
			'ABeeZee' => array(
				'label' => "'ABeeZee', sans-serif",
				'include' => 'ABeeZee',
			),
			'Kite_One' => array(
				'label' => "'Kite One', sans-serif",
				'include' => 'Kite+One',
			),
			'Noto_Sans' => array(
				'label' => "'Noto Sans', sans-serif",
				'include' => 'Noto+Sans',
			),
			'Cinzel' => array(
				'label' => "'Cinzel', serif",
				'include' => 'Cinzel',
			),
			'Trykker' => array(
				'label' => "'Trykker', serif",
				'include' => 'Trykker',
			),
			'Jacques_Francois' => array(
				'label' => "'Jacques Francois', serif",
				'include' => 'Jacques+Francois',
			),
			'Domine' => array(
				'label' => "'Domine', serif",
				'include' => 'Domine',
			),
			'Comfortaa' => array(
				'label' => "'Comfortaa', cursive",
				'include' => 'Comfortaa',
			),
			'Salsa' => array(
				'label' => "'Salsa', cursive",
				'include' => 'Salsa',
			),
			'Nova_Square' => array(
				'label' => "'Nova Square', cursive",
				'include' => 'Nova+Square',
			),
			'Iceland' => array(
				'label' => "'Iceland', cursive",
				'include' => 'Iceland',
			),
			'Lancelot' => array(
				'label' => "'Lancelot', cursive",
				'include' => 'Lancelot',
			),
			'Supermercado_One' => array(
				'label' => "'Supermercado One', cursive",
				'include' => 'Supermercado+One',
			),
			'Averia_Libre' => array(
				'label' => "'Averia Libre', cursive",
				'include' => 'Averia+Libre',
			),
			'Croissant_One' => array(
				'label' => "'Croissant One', cursive",
				'include' => 'Croissant+One',
			),
			'Averia_Gruesa_Libre' => array(
				'label' => "'Averia Gruesa Libre', cursive",
				'include' => 'Averia+Gruesa+Libre',
			),
			'Overlock' => array(
				'label' => "'Overlock', cursive",
				'include' => 'Overlock',
			),
			'Lobster_Two' => array(
				'label' => "'Lobster Two', cursive",
				'include' => 'Lobster+Two',
			),
			'Bevan' => array(
				'label' => "'Bevan', cursive",
				'include' => 'Bevan',
			),
			'Pompiere' => array(
				'label' => "'Pompiere', cursive",
				'include' => 'Pompiere',
			),
			'Kelly_Slab' => array(
				'label' => "'Kelly Slab', cursive",
				'include' => 'Kelly+Slab',
			),
			'Carter_One' => array(
				'label' => "'Carter One', cursive",
				'include' => 'Carter+One',
			),
			'Inconsolata' => array(
				'label' => "'Inconsolata'",
				'include' => 'Inconsolata',
			),
			'Ubuntu_Mono' => array(
				'label' => "'Ubuntu Mono'",
				'include' => 'Ubuntu+Mono',
			),
			'Droid_Sans_Mono' => array(
				'label' => "'Droid Sans Mono'",
				'include' => 'Droid+Sans+Mono',
			),
			'Source_Code_Pro' => array(
				'label' => "'Source Code Pro'",
				'include' => 'Source+Code+Pro',
			),
			'Nova_Mono' => array(
				'label' => "'Nova Mono'",
				'include' => 'Nova+Mono',
			),
			'PT_Mono' => array(
				'label' => "'PT Mono'",
				'include' => 'PT+Mono',
			),
			'Cutive_Mono' => array(
				'label' => "'Cutive Mono'",
				'include' => 'Cutive+Mono',
			),
			'Crete_Round' => array(
				'label' => "'Crete Round', serif",
				'include' => 'Crete Round',
			),
			'EB_Garamond' => array(
				'label' => "'EB Garamond', serif",
				'include' => 'EB+Garamond',
			),
			'Cardo' => array(
				'label' => "'Cardo', serif",
				'include' => 'Cardo',
			),
			'Fanwood_Text' => array(
				'label' => "'Fanwood Text', serif",
				'include' => 'Fanwood+Text',
			),
			'Trocchi' => array(
				'label' => "'Trocchi', serif",
				'include' => 'Trocchi',
			),
			'Fauna_One' => array(
				'label' => "'Fauna One', serif",
				'include' => 'Fauna+One',
			),
			'Prata' => array(
				'label' => "'Prata', serif",
				'include' => 'Prata',
			),
		);

		foreach ( $web_fonts as $key => $font ) {
			$web_fonts[$key]['include'] = $font['include'] . ':400,400italic,700,700italic'; // Include the normal, italic, bold and bold italic
		}

		return apply_filters( 'ipt_fsqm_filter_available_webfonts', $web_fonts );
	}

	public function get_element_definition( $element_structure ) {
		return $this->elements[$element_structure['m_type']]['elements'][$element_structure['type']];
	}

	public function get_element_from_layout( $layout_element ) {
		return isset( $this->{$layout_element['m_type']}[$layout_element['key']] ) ? $this->{$layout_element['m_type']}[$layout_element['key']] : array();
	}


	public function build_element_html( $element, $key, $element_data = null, $submission_data = null, $name_prefix = '' ) {
		$type = '';
		if ( is_array( $element ) && isset( $element['type'] ) ) {
			$type = $element['type'];
		} else {
			$type = (string) $element;
		}
		$element_structure = $this->get_element_structure( $element );

		if ( false == $element_structure ) {
			$this->print_error( __( 'Invalid Element type supplied: ', 'ipt_fsqm' ) . $element );
			return false;
		}

		if ( null !== $element_data ) {
			$element_data = $this->merge_elements( $element_data, $element_structure, true );
		} else {
			$element_data = $element_structure;
		}

		// Now check again for default builder elements
		if ( '__EKEY__' === $key ) {
			// Get the default settings structure
			$element_default_settings = $this->get_default_element_settings( $element_data['type'], $element_data['m_type'] );
			// Now merge it if possible
			// But just the title and settings
			if ( false !== $element_default_settings ) {
				if ( isset( $element_default_settings['title'] ) ) {
					$element_data['title'] = $element_default_settings['title'];
				}
				if ( isset( $element_default_settings['settings'] ) ) {
					$element_data['settings'] = wp_parse_args( $element_default_settings['settings'], $element_data['settings'] );
				}
			}
		}

		$submission_structure = $this->get_submission_structure( $element );

		if ( false == $submission_structure && $element_structure['m_type'] != 'design' ) {
			$this->print_error( __( 'Form submission type not set: ', 'ipt_fsqm' ) . $element );
			return false;
		}

		if ( $submission_data != null && false != $submission_structure ) {
			$submission_data = $this->merge_elements( $submission_data, $submission_structure );
		} else {
			$submission_data = $submission_structure;
		}

		$name_prefix = trim( $name_prefix );
		if ( $name_prefix == '' ) {
			$name_prefix .= $element_structure['m_type'] . '[' . $key . ']';
		} else {
			$name_prefix = $name_prefix . '[' . $element_structure['m_type'] . '][' . $key . ']';
		}
		$element_definition = $this->get_element_definition( $element_structure );
		$param = array( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $this );
		if ( method_exists( $this, 'build_' . $element ) ) {
			call_user_func_array( array( $this, 'build_' . $element ), $param );
		} else {
			if ( isset( $element_definition['callback'] ) && is_callable( $element_definition['callback'] ) ) {
				call_user_func_array( $element_definition['callback'], $param );
			} else {
				$this->print_error( __( 'No valid callback assigned.', 'ipt_fsqm' ) );
				return false;
			}
		}

		return true;
	}

	public function get_keys_from_layouts_by_types( $types, $layouts ) {
		$keys = array();
		if ( empty( $layouts ) || !is_array( $layouts ) ) {
			return $keys;
		}

		foreach ( $layouts as $layout ) {
			if ( !is_array( $layout ) || empty( $layout ) || !isset( $layout['elements'] ) || !is_array( $layout['elements'] ) || empty( $layout['elements'] ) ) {
				continue;
			}

			$keys = array_merge( $keys, $this->get_keys_from_layout_by_types( $types, $layout ) );
		}

		return $keys;
	}

	public function get_keys_from_layout_by_types( $types, $layout ) {
		$keys = array();
		if ( !is_array( $types ) ) {
			$types = (array) $types;
		}

		if ( empty( $layout ) || !is_array( $layout ) || !isset( $layout['elements'] ) || empty( $layout['elements'] ) ) {
			return $keys;
		}

		foreach ( $layout['elements'] as $element ) {
			if ( in_array( $element['type'], $types, true ) ) {
				$keys[] = $element['key'];
			} else {
				$element_definition = $this->get_element_definition( $element );
				if ( isset( $element_definition['droppable'] ) && $element_definition['droppable'] == true ) {
					$keys = array_merge( $keys, $this->get_keys_from_layout_by_types( $types, $this->get_element_from_layout( $element ) ) );
				}
			}
		}

		return $keys;
	}

	public function get_keys_from_layouts_by_m_type( $m_type, $layouts ) {
		$keys = array();
		if ( empty( $layouts ) || !is_array( $layouts ) ) {
			return $keys;
		}

		foreach ( $layouts as $layout ) {
			if ( !is_array( $layout ) || empty( $layout ) || !isset( $layout['elements'] ) || !is_array( $layout['elements'] ) || empty( $layout['elements'] ) ) {
				continue;
			}

			$keys = array_merge( $keys, $this->get_keys_from_layout_by_m_type( $m_type, $layout ) );
		}

		return $keys;
	}

	public function get_keys_from_layout_by_m_type( $m_type, $layout ) {
		$keys = array();

		if ( empty( $layout ) || !is_array( $layout ) || !isset( $layout['elements'] ) || empty( $layout['elements'] ) ) {
			return $keys;
		}

		foreach ( $layout['elements'] as $element ) {
			if ( $element['m_type'] == $m_type ) {
				$keys[] = $element['key'];
			} else {
				$element_definition = $this->get_element_definition( $element );
				if ( isset( $element_definition['droppable'] ) && $element_definition['droppable'] == true ) {
					$keys = array_merge( $keys, $this->get_keys_from_layout_by_m_type( $m_type, $this->get_element_from_layout( $element ) ) );
				}
			}
		}

		return $keys;
	}

	public function sanitize_min_max_step( $settings ) {
		if ( !is_array( $settings ) || !isset( $settings['min'] ) || !isset( $settings['max'] ) || empty( $settings['max'] ) || empty( $settings['min'] ) ) {
			return $settings;
		}
		$max = max( array( $settings['max'], $settings['min'] ) );
		$min = min( array( $settings['max'], $settings['min'] ) );
		$settings['max'] = $max;
		$settings['min'] = $min;

		if ( !isset( $settings['step'] ) ) {
			return $settings;
		}

		$settings['step'] = abs( $settings['step'] );
		if ( $settings['step'] == '0' ) {
			$settings['step'] = '1';
		}

		return $settings;
	}

	protected function encrypt( $input_string ) {
		return IPT_FSQM_Form_Elements_Static::encrypt( $input_string );
	}

	protected function decrypt( $encrypted_input_string ) {
		return IPT_FSQM_Form_Elements_Static::decrypt( $encrypted_input_string );
	}

	/**
	 * Recursively checks for the structure and copy value from the element
	 *
	 * @param array   $element
	 * @param array   $structure
	 * @return mixed
	 */
	public function merge_elements( $element, $structure, $merge_only = false ) {
		$fresh = array();
		foreach ( (array) $structure as $s_key => $sval ) {
			if ( is_array( $sval ) ) {
				//sda arrays in structures are always empty
				if ( empty( $sval ) ) {
					$fresh[$s_key] = isset( $element[$s_key] ) ? $element[$s_key] : array();
				} else {
					$new_element = isset( $element[$s_key] ) ? $element[$s_key] : array();
					$fresh[$s_key] = $this->merge_elements( $new_element, $sval, $merge_only );
				}
				//Check for settings
				if ( $s_key == 'settings' && $merge_only == false ) {
					$fresh[$s_key] = $this->sanitize_min_max_step( $fresh[$s_key] );
				}
			} elseif ( is_bool( $sval ) ) {
					$fresh[$s_key] = ( ( isset( $element[$s_key] ) && null !== $element[$s_key] && false !== $element[$s_key] && '' !== $element[$s_key] && 0 != $element[$s_key] ) ? true : ( ( $merge_only && ! isset( $element[$s_key] ) ) ? $sval : false ) ); //Check for ajax submission as well
					//var_dump($element[$s_key], $fresh[$s_key]);
			} else {
				$fresh[$s_key] = isset( $element[$s_key] ) ? $element[$s_key] : $sval;
			}
		}

		return $fresh;
	}

	/*==========================================================================
	 * BASIC DATABASE ABSTRACTIONS
	 *========================================================================*/
	public function get_total_submissions() {
		global $ipt_fsqm_info, $wpdb;
		return (float) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $this->form_id ) );
	}


	/*==========================================================================
	 * DEFAULT ELEMENTS - OVERRIDE
	 *========================================================================*/
	public function build_heading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_richtext( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_embed( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_collapsible( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_container( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_iconbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_half( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_third( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_two_third( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_forth( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_col_three_forth( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_clear( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_horizontal_line( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_divider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_button( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_imageslider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_captcha( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_slider( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_range( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_spinners( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_grading( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_starrating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_scalerating( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_matrix( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_toggle( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_feedback_large( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_feedback_small( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_f_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_l_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_name( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_email( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_phone( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_payment( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_textinput( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_textarea( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_guestblog( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_password( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_radio( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_select( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_s_checkbox( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_address( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_keypad( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_datetime( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_p_sorting( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	public function build_hidden( $element_definition, $key, $element_data, $element_structure, $name_prefix, $submission_data, $submission_structure, $context ) {
		$this->print_error( __( 'Please override this method', 'ipt_fsqm' ) );
	}

	/*==========================================================================
	 * COMPATIBILITY LAYER WITH VERSION < 2
	 *========================================================================*/
	public function compat_notice() {
		$this->print_update( __( 'The form you are working with currently, has an outdated structure. This happens if you are coming from an older version of WP Feedback, Survey & Quiz Manager. Please edit the form and save it to get it updated.', 'ipt_fsqm' ) );
	}
	public function compat_layout() {
		if ( null == $this->form_id || !empty( $this->layout ) ) {
			$default_settings = $this->get_default_settings();
			if ( isset( $this->settings['user']['math_format'] ) ) {
				$this->settings['format']['math_format'] = $this->settings['user']['math_format'];
			}
			$this->settings = $this->merge_elements( $this->settings, $default_settings, true );
			$new_layout = $this->layout;
			foreach ( $new_layout as $l_key => $layout ) {
				$new_layout[$l_key] = $this->merge_elements( $layout, $this->get_element_structure( 'tab' ), true );
			}
			$this->layout = $new_layout;
			$this->compatibility = false;
			// Fix empty theme issue
			$themes = $this->get_available_themes();
			$theme_found = false;
			foreach ( $themes as $theme ) {
				foreach ( $theme['themes'] as $theme_id => $theme_config ) {
					if ( $this->settings['theme']['template'] == $theme_id ) {
						$theme_found = true;
						break 2;
					}
				}
			}
			if ( ! $theme_found ) {
				$this->settings['theme']['template'] = 'material-default';
			}
			return;
		} else {
			//Check to see if this is just an empty form
			if ( empty( $this->mcq ) && empty( $this->freetype ) && empty( $this->pinfo ) ) {
				$default_settings = $this->get_default_settings();
				$this->settings = $this->merge_elements( $this->settings, $default_settings, true );
				$this->compatibility = false;
				return;
			}
			$this->compatibility = true;
			//var_dump($this->pinfo);
			//set the layout at per with old settings
			$this->layout = array();
			$this->type = '1';

			//the setup is tab type
			//loop along with tab order
			$layout_key = 0;
			$theme_shortcode = array(
				'survey' => 'mcq',
				'feedback' => 'free',
				'pinfo' => 'p',
			);

			if ( !isset( $this->settings['tab_order'] ) ) {
				$this->settings['tab_order'] = array(
					0 => 'survey',
					1 => 'feedback',
					2 => 'pinfo',
				);
			}
			foreach ( $this->settings['tab_order'] as $tab ) {
				if ( true != $this->settings['enable_' . $tab] ) {
					continue;
				}

				$layout = $this->get_element_structure( 'tab' );

				//make the title, subtitle, description
				$layout['title'] = $this->settings[$tab . '_title'];
				$layout['subtitle'] = $this->settings[$tab . '_subtitle'];
				$layout['description'] = $this->settings[$tab . '_description'];

				//call the method to update the layout elements and also modify the member variable
				call_user_func_array( array( $this, 'compat_' . $tab ), array( &$layout ) );

				//update this->layout
				$this->layout[$layout_key] = $layout;

				$layout_key++;
			}

			//compat the settings
			$this->compat_settings();
		}
	}

	public function compat_survey( &$layout ) {
		//Make the default survey type question array to replace $this->mcq
		$survey = array();


		//Loop through old mcqs
		foreach ( $this->mcq as $m_key => $mcq ) {
			//delete if not enabled
			if ( false == $mcq['enabled'] ) {
				continue;
			}

			//store the key to the layout elements
			$layout['elements'][] = array(
				'm_type' => 'mcq',
				'key' => $m_key,
				'type' => $mcq['type'] == 'single' ? 'radio' :  'checkbox',
			);

			//either radio or checkbox
			$survey[$m_key] = $mcq['type'] == 'single' ? $this->get_element_structure( 'radio' ) : $this->get_element_structure( 'checkbox' );

			//set the title
			$survey[$m_key]['title'] = $mcq['question'];

			//set the options
			$options = $this->split_options( $mcq['options'] );
			foreach ( $options as $option ) {
				$survey[$m_key]['settings']['options'][] = array(
					'label' => $option,
					'score' => '',
				);
			}

			//set others
			$survey[$m_key]['settings']['others'] = $mcq['others'];
			$survey[$m_key]['settings']['o_label'] = $mcq['o_label'];

			//set validation
			$survey[$m_key]['validation']['required'] = $mcq['required'];

			//Set types
			$survey[$m_key]['type'] = $mcq['type'] == 'single' ? 'radio' :  'checkbox';
			$survey[$m_key]['m_type'] = 'mcq';
		}

		//All set, now replace
		$this->mcq = $survey;
	}

	public function compat_feedback( &$layout ) {
		//make the new array to replace $this->freetype
		$feedback = array();

		//Loop through older feedbacks
		foreach ( $this->freetype as $f_key => $freetype ) {
			//delete if not enabled
			if ( false == $freetype['enabled'] ) {
				continue;
			}

			//Store the key to the layout element
			$layout['elements'][] = array(
				'm_type' => 'freetype',
				'key' => $f_key,
				'type' => 'feedback_large',
			);

			//get the default structure
			$feedback[$f_key] = $this->get_element_structure( 'feedback_large' );

			//set title
			$feedback[$f_key]['title'] = $freetype['name'];

			//set description
			$feedback[$f_key]['subtitle'] = $freetype['description'];

			//set email
			$feedback[$f_key]['settings']['email'] = $freetype['email'];

			//set validation
			$feedback[$f_key]['validation']['required'] = $freetype['required'];

			//Set the types
			$feedback[$f_key]['type'] = 'feedback_large';
			$feedback[$f_key]['m_type'] = 'freetype';
		}

		//Replace the variable
		$this->freetype = $feedback;
	}

	public function compat_pinfo( &$layout ) {
		//make the new array to store modified elements
		$others = array();

		//Loop through older pinfo
		$last_p_key = count( $this->pinfo );
		$pinfo_dbmap = array(
			'f_name' => __( 'First Name', 'ipt_fsqm' ),
			'l_name' => __( 'Last Name', 'ipt_fsqm' ),
			'email' => __( 'Email Address', 'ipt_fsqm' ),
			'phone' => __( 'Phone Number', 'ipt_fsqm' ),
		);
		foreach ( $this->pinfo as $p_key => $pinfo ) {
			//delete if not enabled
			if ( false == $pinfo['enabled'] ) {
				continue;
			}

			if ( !isset( $pinfo['type'] ) ) {
				$pinfo['type'] = 'dbmap';
			}

			$type = $p_key;
			$new_p_key = $pinfo['type'] == 'dbmap' ? $last_p_key++ : $p_key;

			//get the structure
			switch ( $pinfo['type'] ) {
			default :
				//These are presets, just need to check the structure and title.
				//Enabled is already checked
				//Required will be checked after this switch/case
				$others[$new_p_key] = $this->get_element_structure( $p_key );
				$others[$new_p_key]['title'] = $pinfo_dbmap[$p_key];
				break;
			case 'single' :
				$others[$new_p_key] = $this->get_element_structure( 'p_radio' );
				$options = $this->split_options( $pinfo['options'] );
				$others[$new_p_key]['settings']['options'] = array();
				foreach ( $options as $option ) {
					$others[$new_p_key]['settings']['options'][] = array( 'label' => $option );
				}
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'p_radio';
				break;
			case 'multiple' :
				$others[$new_p_key] = $this->get_element_structure( 'p_checkbox' );
				$options = $this->split_options( $pinfo['options'] );
				$others[$new_p_key]['settings']['options'] = array();
				foreach ( $options as $option ) {
					$others[$new_p_key]['settings']['options'][] = array( 'label' => $option );
				}
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'p_checkbox';
				break;
			case 'free-input' :
				$others[$new_p_key] = $this->get_element_structure( 'textinput' );
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'textinput';
				break;
			case 'free-text' :
				$others[$new_p_key] = $this->get_element_structure( 'textarea' );
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 'textarea';
				break;
			case 'required-checkbox' :
				$others[$new_p_key] = $this->get_element_structure( 's_checkbox' );
				$others[$new_p_key]['title'] = $pinfo['question'];
				$type = 's_checkbox';
				break;
			}

			//Store the key to the layout element
			$layout['elements'][] = array(
				'm_type' => 'pinfo',
				'key' => $new_p_key,
				'type' => $type,
			);

			//Validation copy
			$others[$new_p_key]['validation']['required'] = isset( $pinfo['required'] ) ? $pinfo['required'] : false;
			if ( $pinfo['type'] == 'required-checkbox' ) {
				$others[$new_p_key]['validation']['required'] = true;
			}

			//Set types
			$others[$new_p_key]['type'] = $type;
			$others[$new_p_key]['m_type'] = 'pinfo';
		}
		//Append the captcha
		$captcha = $this->get_element_structure( 'captcha' );
		$layout['elements'][] = array(
			'type' => $captcha['type'],
			'm_type' => $captcha['m_type'],
			'key' => '0'
		);
		$this->design = array(
			0 => $captcha,
		);

		$this->pinfo = $others;
	}

	public function compat_settings() {
		$default_settings = $this->get_default_settings();

		$compat_settings = array(
			'general' => array(
				'terms_page' => $this->settings['terms_page'],
				'comment_title' => $this->settings['comment_title'],
				'default_comment' => $this->settings['default_comment'],
			),
			'user' => array(
				'notification_sub' => $this->settings['notification_sub'],
				'notification_msg' => $this->settings['notification_msg'],
				'notification_from' => $this->settings['notification_from'],
				'notification_email' => $this->settings['notification_email'],
			),
			'admin' => array(
				'email' => $this->settings['email'],
				'mail_submission' => isset( $this->settings['mail_submission'] ) ? $this->settings['mail_submission'] : false,
			),
			'limitation' => array(
				'email_limit' => $this->settings['unique_email'] == true ? '1' : '0',
				'ip_limit' => isset( $this->settings['ip_limit'] ) ? $this->settings['ip_limit'] : '0',
			),
			'type_specific' => array(
				'pagination' => array(
					'show_progress_bar' => true,
				),
				'tab' => array(
					'can_previous' => true,
				),
				'normal' => array(
					'wrapper' => false,
				),
			),
			'buttons' => array(
				'next' => __( 'Next', 'ipt_fsqm' ),
				'prev' => __( 'Previous', 'ipt_fsqm' ),
				'submit' => __( 'Submit', 'ipt_fsqm' ),
			),
			'submission' => array(
				'process_title' => $this->settings['process_title'],
				'success_title' => $this->settings['success_title'],
				'success_message' => $this->settings['success_message'],
			),
			'redirection' => array(
				'type' => 'none',
				'delay' => '1000',
				'url' => '',
				'score' => array(),
			),
			'theme' => array(
				'template' => $this->settings['theme'] == 'hot-sneak' ? 'hot-sneaks' : $this->settings['theme'],
				'custom_style' => $this->settings['custom'],
				'style' => array(
					'head_font' => $this->settings['css']['head_font'],
					'body_font' => $this->settings['css']['body_font'],
				),
			),
		);


		$this->settings = $this->merge_elements( $compat_settings, $default_settings, true );
	}


	/*==========================================================================
	 * INTERNAL HTML FORM ELEMENTS METHODS
	 *========================================================================*/

	/**
	 * Converts seconds to readable W days, X hours, Y minutes, Z seconds
	 *
	 * @param      integer  $seconds  The number of second
	 *
	 * @return     string
	 */
	public function seconds_to_words($seconds) {
		$ret = array();

		/*** get the days ***/
		$days = intval( intval( $seconds ) / ( 3600 * 24 ) );
		if ( $days > 0 ) {
			$ret[] = sprintf( _n( '%1$d day', '%1$d days', $days, 'ipt_fsqm' ), $days );
		}

		/*** get the hours ***/
		$hours = ( intval( $seconds ) / 3600 ) % 24;
		if ( $hours > 0 ) {
			$ret[] = sprintf( _n( '%1$d hour', '%1$d hours', $hours, 'ipt_fsqm' ), $hours );
		}

		/*** get the minutes ***/
		$minutes = ( intval( $seconds ) / 60 ) % 60;
		if ( $minutes > 0 ) {
			$ret[] = sprintf( _n( '%1$d minute', '%1$d minutes', $minutes, 'ipt_fsqm' ), $minutes );
		}

		/*** get the seconds ***/
		$seconds = intval( $seconds ) % 60;
		if ( $seconds > 0 ) {
			$ret[] = sprintf( _n( '%1$d second', '%1$d seconds', $seconds, 'ipt_fsqm' ), $seconds );
		}

		return implode( _x( ', ', 'secondstowords', 'ipt_fsqm' ), $ret );
	}
	/**
	 * Generate Label for an element
	 *
	 * @param string  $name The name of the element
	 * @param type    $text
	 */
	public function generate_label( $name, $text, $id = '', $classes = array() ) {
		if ( !is_array( $classes ) ) {
			$classes = (array) $classes;
		}
		$classes[] = 'ipt_uif_label';
?>
<label class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" for="<?php echo $this->generate_id_from_name( $name, $id ); ?>"><?php echo $text; ?></label>
		<?php
	}

	public function generate_id_from_name( $name, $id = '' ) {
		if ( '' == trim( $id ) ) {
			return esc_attr( str_replace( array( '[', ']' ), array( '_', '' ), trim( $name ) ) );
		} else {
			return esc_attr( trim( $id ) );
		}
	}

	public function convert_data_attributes( $data ) {
		if ( false == $data || !is_array( $data ) || empty( $data ) ) {
			return '';
		}

		$data_attr = '';
		foreach ( $data as $d_key => $d_val ) {
			if ( $d_val != '' )
				$data_attr .= ' data-' . esc_attr( $d_key ) . '="' . esc_attr( $d_val ) . '"';
		}

		return $data_attr;
	}


	public function convert_validation_class( $validation = false ) {
		if ( $validation == false || !is_array( $validation ) || empty( $validation ) ) {
			return '';
		}

		$classes = array();

		//check if required
		if ( true == $validation['required'] ) {
			$classes[] = 'required';
		}

		//check for any custom regex
		if ( isset( $validation['filters'] ) && is_array( $validation['filters'] ) ) {
			if ( isset( $validation['filters']['type'] ) ) {
				if ( 'all' != $validation['filters']['type'] ) {
					$classes[] = 'custom[' . esc_attr( $validation['filters']['type'] ) . ']';
				}
			}

			//check for others
			foreach ( $validation['filters'] as $f_key => $f_val ) {
				if ( 'type' == $f_key ) {
					continue;
				}

				if ( $f_val != '' ) {
					$classes[] = esc_attr( $f_key ) . '[' . esc_attr( $f_val ) . ']';
				}
			}
		}

		if ( isset( $validation['funccall'] ) && is_string( $validation['funccall'] ) ) {
			$classes[] = 'funcCall[' . $validation['funccall'] . ']';
		}


		$added = implode( ',', $classes );
		if ( $added != '' ) {
			return ' check_me validate[' . $added . ']';
		} else {
			return '';
		}
	}


	/**
	 * Shortens a string to a specified character length.
	 * Also removes incomplete last word, if any
	 *
	 * @param string  $text The main string
	 * @param string  $char Character length
	 * @param string  $cont Continue character(…)
	 * @return string
	 */
	public function shorten_string( $text, $char, $cont = '…' ) {
		$text = strip_tags( strip_shortcodes( $text ) );
		$text = substr( $text, 0, $char ); //First chop the string to the given character length
		if ( substr( $text, 0, strrpos( $text, ' ' ) )!='' ) $text = substr( $text, 0, strrpos( $text, ' ' ) ); //If there exists any space just before the end of the chopped string take upto that portion only.
		//In this way we remove any incomplete word from the paragraph
		$text = $text.$cont; //Add continuation ... sign
		return $text; //Return the value
	}

	/**
	 * Wrap a RAW JS inside <script> tag
	 *
	 * @param String  $string The JS
	 * @return String The wrapped JS to be used under HTMl document
	 */
	public function js_wrap( $string ) {
		return "\n<script type='text/javascript'>\n" . $string . "\n</script>\n";
	}

	/**
	 * Wrap a RAW CSS inside <style> tag
	 *
	 * @param String  $string The CSS
	 * @return String The wrapped CSS to be used under HTMl document
	 */
	public function css_wrap( $string ) {
		return "\n<style type='text/css'>\n" . $string . "\n</style>\n";
	}


	/*==========================================================================
	 * OTHER INTERNAL METHODS
	 *========================================================================*/

	protected function convert_php_size_to_bytes( $sSize ) {
		if ( is_numeric( $sSize ) ) {
			return $sSize;
		}

		$sSuffix = substr($sSize, -1);
		$iValue = substr($sSize, 0, -1);
		switch(strtoupper($sSuffix)){
		case 'P':
			$iValue *= 1024;
		case 'T':
			$iValue *= 1024;
		case 'G':
			$iValue *= 1024;
		case 'M':
			$iValue *= 1024;
		case 'K':
			$iValue *= 1024;
			break;
		}
		return $iValue;
	}

	public function get_maximum_file_upload_size() {
		return min( $this->convert_php_size_to_bytes( ini_get( 'post_max_size' ) ), $this->convert_php_size_to_bytes( ini_get( 'upload_max_filesize' ) ) );
	}

	/**
	 * Prints error msg in WP style
	 *
	 * @param string  $msg
	 */
	protected function print_error( $msg = '', $echo = true ) {
		$output = '<div class="p-message red"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		else
			return $output;
	}

	protected function print_update( $msg = '', $echo = true ) {
		$output = '<div class="updated fade"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		else
			return $output;
	}

	protected function print_p_error( $msg = '', $echo = true ) {
		$output = '<div class="p-message red"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		return $output;
	}

	protected function print_p_update( $msg = '', $echo = true ) {
		$output = '<div class="p-message yellow"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		return $output;
	}

	protected function print_p_okay( $msg = '', $echo = true ) {
		$output = '<div class="p-message green"><p>' . $msg . '</p></div>';
		if ( $echo )
			echo $output;
		return $output;
	}

	/**
	 * stripslashes gpc
	 * Strips Slashes added by magic quotes gpc thingy
	 *
	 * @access protected
	 * @param string  $value
	 */
	protected function stripslashes_gpc( &$value ) {
		$value = stripslashes( $value );
	}

	protected function htmlspecialchar_ify( &$value ) {
		$value = htmlspecialchars( $value );
	}

	protected function split_options( $option ) {
		$option = explode( "\n", str_replace( "\r", '', $option ) );
		$clean = array();
		array_walk( $option, 'trim' );
		foreach ( $option as $v ) {
			if ( '' != $v )
				$clean[] = $v;
		}
		return $clean;
	}

	/**
	 *
	 *
	 * @deprecated since 1.0.0
	 * @param type    $value
	 */
	protected function clean_options( &$value ) {
		$value = htmlspecialchars( trim( strip_tags( htmlspecialchars_decode( $value ) ) ) );
	}

	/**
	 * Converts jSignature base30 image string to png base64 string
	 * @example    <img src="<?php echo 'data:image/png;base64,' . $this->convert_jsignature_image( $value ); ?>" />
	 *
	 * @param      string  $value  The base30 image string passed by jSignature
	 * @return     string  Empty string if conversion fails, otherwise base64 encoded png image
	 */
	public function convert_jsignature_image( $value, $color = '#000000' ) {
		$signature = '';
		if ( $value != '' && $value != 'image/jsignature;base30,' ) {
			try {
				// Recreate the image
				// @link {https://github.com/brinley/jSignature/issues/97}
				require_once IPT_FSQM_Loader::$abs_path . '/lib/classes/jSignature_Tools_Base30.php';
				$image_data = str_replace( 'image/jsignature;base30,', '', $value );
				$converter = new jSignature_Tools_Base30();
				$raw_image = $converter->Base64ToNative( $image_data );

				// Calculate dimensions
				$width = 0;
				$height = 0;
				foreach ( $raw_image as $line ) {
					if ( max( $line['x'] ) > $width ) {
						$width = max( $line['x'] );
					}
					if ( max( $line['y'] ) > $height ) {
						$height = max( $line['y'] );
					}
				}

				// Create an image
				// Create double the size and we will antialias later
				$im = @imagecreatetruecolor( $width * 2 + 40, $height * 2 + 40 );

				// Save transparency for PNG
				@imagesavealpha( $im, true );

				// Fill background with transparency
				$trans_colour = @imagecolorallocatealpha($im, 255, 255, 255, 127);
				@imagefill($im, 0, 0, $trans_colour);

				// Set pen thickness
				$thickness = 6;
				@imagesetthickness( $im, $thickness );

				// Set pen color to black if not specified
				if ( empty( $color ) || 7 != strlen( $color ) || 0 !== strpos( $color, '#' ) ) {
					$color = '#000000';
				}
				list( $r, $g, $b ) = sscanf( $color, "#%02x%02x%02x" );
				$pen = @imagecolorallocate( $im, $r, $g, $b );

				// Loop through array pairs from each signature word
				for ( $i = 0; $i < count( $raw_image ); $i++ ) {
					// Loop through each pair in a word
					for ( $j = 0; $j < count( $raw_image[$i]['x'] ); $j++ ) {
						// Make sure we are not on the last coordinate in the array
						if ( ! isset( $raw_image[$i]['x'][$j] ) ) {
						   break;
						}
						if ( ! isset( $raw_image[$i]['x'][$j+1] ) ) {
							// Draw the dot for the coordinate
							// But to respect our line thickness, we draw a line up and right
							@imageline( $im, $raw_image[$i]['x'][$j] * 2, $raw_image[$i]['y'][$j] * 2, $raw_image[$i]['x'][$j] * 2 + 2, $raw_image[$i]['y'][$j] * 2 - 2, $pen );
							//@imagesetpixel( $im, $raw_image[$i]['x'][$j], $raw_image[$i]['y'][$j], $pen );
						} else {
							// Draw the line for the coordinate pair
							@imageline( $im, $raw_image[$i]['x'][$j] * 2, $raw_image[$i]['y'][$j] * 2, $raw_image[$i]['x'][$j+1] * 2, $raw_image[$i]['y'][$j+1] * 2, $pen );
						}
					}
				}

				// Create the destination for super sampling and antialiasing
				$dest_image = @imagecreatetruecolor( $width + 20, $height + 20 );
				// Save transparency for PNG
				@imagesavealpha( $dest_image, true );
				// Fill background with transparency
				$dtrans_colour = @imagecolorallocatealpha($dest_image, 255, 255, 255, 127);
				@imagefill($dest_image, 0, 0, $dtrans_colour);

				// Copy and resample
				@imagecopyresampled( $dest_image, $im, 0, 0, 0, 0, $width + 20, $height + 20, $width * 2 + 40, $height * 2 + 40 );

				ob_start();
				@imagepng( $dest_image );
				$signature = ob_get_clean();
				$signature = base64_encode( $signature );
			} catch ( Exception $e ) {
				$signature = '';
			}
		}
		return $signature;
	}

	public function get_default_placeholder( $type ) {
		switch ( $type ) {
			default:
				return __( 'Write Here', 'ipt_fsqm' );
				break;
			case 'email' :
			case 'p_email' :
				return __( 'Email Address', 'ipt_fsqm' );
				break;
			case 'f_name' :
				return __( 'First Name', 'ipt_fsqm' );
				break;
			case 'l_name' :
				return __( 'Last Name', 'ipt_fsqm' );
				break;
			case 'p_name' :
				return __( 'Name', 'ipt_fsqm' );
				break;
			case 'password' :
				return __( 'Password', 'ipt_fsqm' );
				break;
			case 'phone' :
			case 'p_phone' :
				return __( 'Phone', 'ipt_fsqm' );
				break;
			case 'datetime' :
				return __( 'Select Date', 'ipt_fsqm' );
				break;
		}
	}
}
