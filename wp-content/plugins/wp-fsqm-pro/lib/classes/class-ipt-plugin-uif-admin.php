<?php
/**
 * iPanelThemes User Interface for Plugin's Framework
 * Admin Area
 *
 * Generates all user interface/form elements
 * It needs to have the ipt_plugin_uif.admin.css and ipt_plugin_uif.admin.js file
 *
 * @depends base
 * @version 1.0.3
 */

if ( ! class_exists( 'IPT_Plugin_UIF_Admin' ) ) :

	class IPT_Plugin_UIF_Admin extends IPT_Plugin_UIF_Base {
		/**
		 * Default Messages
		 *
		 * Shortcut to all the messages
		 *
		 * @var array All the default messages
		 */
		public $default_messages = array();

		/**
		 * Labels for builder elements
		 *
		 * Should be set during builder init
		 * An associative array of element_type => label
		 * @var array
		 */
		public $builder_labels = array();

		/*==========================================================================
		 * System API
		 *========================================================================*/
		public static function instance( $text_domain = 'default', $classname = __CLASS__ ) {
			return parent::instance( $text_domain, __CLASS__ );
		}

		/**
		 * Constructor
		 */
		public function __construct( $text_domain = 'default', $classname = __CLASS__ ) {
			$this->default_messages = array(
				'sortable_messages' => array(
					'layout_helper_msg' => __( 'This is a container where you can drop other elements to build your layout. This container on itself, has some settings which you can edit by clicking the cog icon nereby.', $text_domain ),
					'layout_helper_title' => __( 'Customizable Layout', $text_domain ),
					'deleter_title' => __( 'Confirm Deletion', $text_domain ),
					'deleter_msg' => __( 'Are you sure you want to remove this container? The action can not be undone.', $text_domain ),
					'empty_msg' => __( 'Please click on the Add Container Button to get started.', $text_domain ),
					'toolbar_settings' => __( 'Click to customize the settings of this container.', $text_domain ),
					'toolbar_deleter' => __( 'Click to remove this container and all elements inside it.', $text_domain ),
					'toolbar_copy' => __( 'Click to make a copy of this container.', $text_domain ),
					'deldropper_title' => __( 'Confirm Removal', 'ipt_fsqm' ),
					'deldropper_msg' => __( 'Are you sure you want to remove this element? This action can not be undone.', 'ipt_fsqm' ),
				),
				'droppable_messages' => array(
					'empty' => __( 'Please drag an element to this position to get started.', $text_domain ),
					'settings' => __( 'Click to customize the settings of this element.', $text_domain ),
					'expand' => __( 'Click to expand/collapse the item to drop more elements inside it.', $text_domain ),
					'drag' => __( 'Click to drag and re-order element.', $text_domain ),
					'copy' => __( 'Click to duplicate this element.', $text_domain ),
					'clipboard' => __( 'Click to copy this element to clipboard which can be used for pasting.', $text_domain ),
					'paste' => __( 'Paste element from clipboard.', $text_domain ),
				),
				'ajax_loader' => __( 'Please Wait', $text_domain ),
				'delete_title' => __( 'Confirm Deletion', $text_domain ),
				'delete_msg' => __( '<p>Are you sure you want to delete?</p><p>The action can not be undone</p>', $text_domain ),
				'elements' => array(
					'heading' => __( 'Heading', $text_domain ),
					'date' => __( 'Date Only', $text_domain ),
					'time' => __( 'Time Only', $text_domain ),
					'datetime' => __( 'Date & Time', $text_domain ),
				),
				'got_it' => __( 'Got it', $text_domain ),
				'help' => __( 'Help!', $text_domain ),
			);
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
			parent::enqueue( $static_location, $version );
			$static_location = $this->static_location;
			$version = $this->version;
			//Styles
			wp_enqueue_style( 'ipt-eform-material-font', '//fonts.googleapis.com/css?family=Noto+Sans|Roboto:300,400,400i,700', array(), IPT_FSQM_Loader::$version );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'thickbox' );
			wp_enqueue_style( 'ipt-plugin-uif-fip', $static_location . 'css/jquery.fonticonpicker.min.css', array(), $version );
			wp_enqueue_style( 'ipt-plugin-uif-fip-theme', $static_location . 'css/jquery.fonticonpicker.ipt.css', array(), $version );
			wp_enqueue_style( 'ipt-plugin-uif-admin-css', $static_location . 'css/ipt-eform-admin.css', array(), $version );

			//Scripts
			wp_enqueue_script( 'thickbox' );
			wp_enqueue_media();
			wp_enqueue_script( 'jquery-color' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_script( 'ipt-plugin-uif-fip-js', $static_location . 'js/jquery.fonticonpicker.min.js', array( 'jquery' ), $version );
			wp_enqueue_script( 'ipt-plugin-uif-admin-js', $static_location . 'js/jquery.ipt-plugin-uif-admin' . self::$js_suffix . '.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-button', 'jquery-touch-punch', 'jquery-ui-draggable', 'jquery-ui-droppable', 'jquery-ui-sortable', 'jquery-ui-datepicker', 'jquery-ui-dialog', 'jquery-ui-tabs', 'jquery-ui-slider', 'jquery-ui-spinner', 'jquery-ui-progressbar', 'jquery-timepicker-addon', 'jquery-print-element', 'jquery-mwheelIntent', 'jquery-mousewheel' ), $version );
			// Enqueue sticky stuff
			wp_enqueue_script( 'ResizeSensor', $static_location . 'js/ResizeSensor.min.js', array(), $version );
			wp_enqueue_script( 'theia-sticky-sidebar', $static_location . 'js/theia-sticky-sidebar.min.js', array( 'ResizeSensor' ), $version );
			wp_enqueue_script( 'jquery-ipt-uif-builder', $static_location . 'js/jquery.ipt-uif-builder' . self::$js_suffix . '.js', array( 'jquery', 'ipt-plugin-uif-admin-js', 'theia-sticky-sidebar' ), $version );
			wp_localize_script( 'ipt-plugin-uif-admin-js', 'iptPluginUIFAdmin', array(
				'L10n' => $this->default_messages,
			) );
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
		 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
		 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
		 * The id of the conditional divs should be given inside the data value of the items in the form
		 * condID => 'ID_OF_DIV'
		 * @param string  $sep         Separator HTML
		 * @param bool    $disabled    Set TRUE if all the items are disabled
		 * @return void
		 */
		public function radios( $name, $items, $checked, $validation = false, $conditional = false, $sep = '&nbsp;&nbsp;', $disabled = false ) {
			if ( ! is_array( $items ) || empty( $items ) ) {
				return;
			}
			$validation_class = $this->convert_validation_class( $validation );

			if ( ! is_string( $checked ) ) {
				$checked = (string) $checked;
			}

			$id_prefix = $this->generate_id_from_name( $name );

			if ( $conditional == true ) {
				echo '<div class="ipt_uif_conditional_input">';
			}

			$items = $this->standardize_items( $items );

			foreach ( (array) $items as $item ) :
				$data = isset( $item['data'] ) ? $item['data'] : '';
			$data_attr = $this->convert_data_attributes( $data );
			$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
			$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
	?>
	<input<?php echo $item['value'] === $checked ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="radio"
		class="ipt_uif_radio <?php echo $validation_class; ?>"
		name="<?php echo $name; ?>"
		id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label for="<?php echo $id; ?>">
		 <?php echo $item['label']; ?>
	</label><?php echo $sep; ?>
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
		 * @param bool    $conditional Whether the group represents conditional questions. This will wrap it inside a conditional div
		 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
		 * The id of the conditional divs should be given inside the data value of the items in the form
		 * condID => 'ID_OF_DIV'
		 * @param string  $sep         Separator HTML
		 * @param bool    $disabled    Set TRUE if all the items are disabled
		 * @return void
		 */
		public function checkboxes( $name, $items, $checked, $validation = false, $conditional = false, $sep = '&nbsp;&nbsp;', $disabled = false ) {
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

			foreach ( (array) $items as $item ) :
				$data = isset( $item['data'] ) ? $item['data'] : '';
			$data_attr = $this->convert_data_attributes( $data );
			$id = $this->generate_id_from_name( '', $id_prefix . '_' . $item['value'] );
			$disabled_item = ( $disabled == true || ( isset( $item['disabled'] ) && true == $item['disabled'] ) ) ? 'disabled' : '';
	?>
	<input<?php echo in_array( $item['value'], $checked, true ) ? ' checked="checked"' : ''; ?>
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $disabled_item ); ?>
		type="checkbox"
		class="ipt_uif_checkbox <?php echo $validation_class; ?>"
		name="<?php echo $name; ?>" id="<?php echo $id; ?>"
		value="<?php echo $item['value']; ?>" />
	<label for="<?php echo $id; ?>">
		 <?php echo $item['label']; ?>
	</label><?php echo $sep; ?>
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
		 * @param string  $name         The HTML name of the radio group
		 * @param array   $items        Associative array of all the radio items.
		 *  array(
		 *      'value' => '',
		 *      'label' => '',
		 *      'data' => array('key' => 'value'[,...]), //optional HTML 5 data attributes inside an associative array
		 *  )
		 * @param string  $selected     The value of the selected item
		 * @param array   $validation   Array of the validation clauses
		 * @param bool    $conditional  Whether the group represents conditional questions. This will wrap it inside a conditional div
		 * which will be fired using jQuery. It does not populate or create anything inside the conditional div.
		 * The id of the conditional divs should be given inside the data value of the items in the form
		 * condID => 'ID_OF_DIV'
		 * @param bool    $disabled     Set TRUE if all the items are disabled
		 * @param bool    $print_select Whether or not to print the select html
		 * @return void
		 */
		public function select( $name, $items, $selected, $validation = false, $conditional = false, $disabled = false, $print_select = true, $classes = array(), $multiple = false ) {
			if ( ! is_array( $items ) || empty( $items ) ) {
				return;
			}
			$validation_class = $this->convert_validation_class( $validation );

			if ( ! is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$classes[] = $validation_class;
			$classes[] = 'ipt_uif_select';

			if ( ! is_string( $selected ) ) {
				$selected = (string) $selected;
			}

			$id = $this->generate_id_from_name( $name );

			if ( $conditional == true ) {
				echo '<div class="ipt_uif_conditional_select">';
			}

			$items = $this->standardize_items( $items );

			if ( $print_select ) {
				echo '<select class="' . implode( ' ', $classes ) . '" name="' . esc_attr( trim( $name ) ) . '" id="' . $id . '" ' . $this->convert_state_to_attribute( ( $disabled == true ) ? 'disabled' : '' ) . ( true === $multiple ? ' multiple="multiple"' : '' ) . '>';
			}

			foreach ( (array) $items as $item ) :
				$data = isset( $item['data'] ) ? $item['data'] : '';
			$data_attr = $this->convert_data_attributes( $data );
	?>
	<option value="<?php echo $item['value']; ?>"<?php if ( $item['value'] === $selected || in_array( $item['value'], (array) $selected ) ) echo ' selected="selected"'; ?><?php echo $data_attr; ?>><?php echo $item['label']; ?></option>
				<?php
			endforeach;

			if ( $print_select ) {
				echo '</select>';
			}

			if ( $conditional == true ) {
				echo '</div>';
			}
		}

		/**
		 * Prints a single checkbox item
		 *
		 * @param string  $name       The HTML name of the radio group
		 * @param array   $items      Associative array of all the radio items.
		 *  array(
		 *      'value' => '',
		 *      'label' => '',
		 *  )
		 * @param bool    $checked    TRUE if the item is checked, FALSE otherwise
		 * @param array   $validation Array of the validation clauses
		 * @param bool    $disabled   Set TRUE if the item is disabled
		 * @return void
		 */
		public function checkbox( $name, $item, $checked, $validation = false, $conditional = false, $disabled = false ) {
			if ( !is_array( $item ) || empty( $item ) ) {
				return;
			}

			if ( true === $checked || $item['value'] === $checked ) {
				$checked = $item['value'];
			} else {
				$checked = false;
			}

			$this->checkboxes( $name, array( $item ), array( $checked ), $validation, $conditional, '&nbsp;&nbsp;', $disabled );
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
		public function text( $name, $value, $placeholder, $size = 'fit', $state = 'normal', $classes = array(), $validation = false, $data = false, $attr = array() ) {
			$id = $this->generate_id_from_name( $name );
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$validation_attr = $this->convert_validation_class( $validation );
			if ( '' != $validation_attr ) {
				$classes[] = $validation_attr;
			}
			$classes[] = $this->convert_size_to_class( $size );
			$data_attr = $this->convert_data_attributes( $data );
			if ( ! is_array( $attr ) ) {
				$attr = (array) $attr;
			}
			if ( ! isset( $attr['type'] ) ) {
				$attr['type'] = 'text';
			}
			$html_attr = $this->convert_html_attributes( $attr );
	?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		<?php echo $html_attr; ?>
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
			<?php
		}

		/**
		 * Generate input type password HTML
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
		public function password( $name, $value, $size = 'fit', $state = 'normal', $classes = array(), $validation = false, $data = false ) {
			$id = $this->generate_id_from_name( $name );
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$validation_attr = $this->convert_validation_class( $validation );
			if ( '' != $validation_attr ) {
				$classes[] = $validation_attr;
			}
			$classes[] = $this->convert_size_to_class( $size );
			$data_attr = $this->convert_data_attributes( $data );
			if ( ! is_string( $value ) ) {
				$value = '';
			}
	?>
	<input class="<?php echo implode( ' ', $classes ); ?> ipt_uif_password ipt_uif_text"
		<?php echo $data_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="password"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"
		value="<?php echo esc_textarea( $value ); ?>" />
			<?php
		}

		/**
		 * Generate textarea HTML
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
		public function textarea( $name, $value, $placeholder, $size = 'fit', $state = 'normal', $classes = array(), $validation = false, $data = false, $rows = 4, $attr = array() ) {
			$id = $this->generate_id_from_name( $name );
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$classes[] = 'ipt_uif_textarea';
			$validation_attr = $this->convert_validation_class( $validation );
			if ( '' != $validation_attr ) {
				$classes[] = $validation_attr;
			}
			$classes[] = $this->convert_size_to_class( $size );
			$data_attr = $this->convert_data_attributes( $data );

			if ( ! is_array( $attr ) ) {
				$attr = (array) $attr;
			}
			$html_attr = $this->convert_html_attributes( $attr );
	?>
	<textarea rows="<?php echo $rows ?>" class="<?php echo implode( ' ', $classes ); ?> ipt_uif_text"
		<?php echo $data_attr; ?>
		<?php echo $html_attr; ?>
		<?php echo $this->convert_state_to_attribute( $state ); ?>
		type="text"
		placeholder="<?php echo esc_attr( $placeholder ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		id="<?php echo $id; ?>"><?php echo esc_textarea( $value ); ?></textarea>
			<?php
		}

		public function textarea_linked_wp_editor( $name, $value, $placeholder, $size = 'regular', $state = 'normal', $classes = array(), $validation = false, $data = false ) {
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$classes[] = 'wp_editor';
			$this->textarea( $name, $value, $placeholder, $size, $state, $classes, $validation, $data );
		}

		public function wp_editor( $name, $value, $additional_settings = array() ) {
			if ( ! is_array( $additional_settings ) ) {
				$additional_settings = (array) $additional_settings;
			}
			$additional_settings['textarea_name'] = $name;
			$editor_id = $this->generate_id_from_name( $name );
			wp_editor( $value, $editor_id, $additional_settings );
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
				$this->msg_error( 'Please pass a valid arrays to the <code>IPT_Plugin_UIF_Admin::buttons</code> method' );
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
				if ( !isset( $button['text'] ) || ( '' == trim( $button['text'] ) && '' == $button['icon'] ) ) {
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
			if ( $icon != '' ) {
				$icon_span .= '<span class="button-icon';
				if ( is_numeric( $icon ) ) {
					$icon_span .= '" data-ipt-icomoon="' . '&#x' . hexdec( $icon ) . '">';
				} else {
					$icon_span .= ' ipt-icomoon-' . $icon . '">';
				}
				$icon_span .= '</span>';
			}

			if ( $text == '' ) {
				$text = $icon_span;
			} else {
				if ( $icon_position == 'before' ) {
					$text = $icon_span . ' ' . $text;
				} else {
					$text .= ' ' . $icon_span;
				}
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
		 * Generate a horizontal slider to select between numerical values
		 *
		 * @param string  $name        HTML name
		 * @param string  $value       Initial value of the range
		 * @param string  $placeholder HTML placeholder
		 * @param int     $min         Minimum of the range
		 * @param int     $max         Maximum of the range
		 * @param int     $step        Slider move step
		 */
		public function spinner( $name, $value, $placeholder = '', $min = '', $max = '', $step = 1 ) {
	?>
	<input type="number" placeholder="<?php echo $placeholder; ?>" class="ipt_uif_text code ipt_uif_uispinner" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
			<?php
		}

		/**
		 * Generate a horizontal slider to select between numerical values
		 *
		 * @param string  $name  HTML name
		 * @param string  $value Initial value of the range
		 * @param int     $min   Minimum of the range
		 * @param int     $max   Maximum of the range
		 * @param int     $step  Slider move step
		 * @param string  $suffix Suffix to add after numeric values, like % etc...
		 * @param string  $prefix Prefix to add before numeric values, like $ etc...
		 */
		public function slider( $name, $value, $min = 0, $max = 100, $step = 1, $suffix = '', $prefix = '' ) {
			// Other stuff
			$min = (float) $min;
			$max = (float) $max;
			$step = (float) $step;
			$value = $value == '' ? $min : (float) $value;
			if ( $value < $min )
				$value = $min;
			if ( $value > $max )
				$value = $max;

	?>
	<div class="ipt_uif_empty_box ipt_uif_slider_box">
		<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" class="ipt_uif_slider check_me ipt_uif_text" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $name ) ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" />
	</div>
			<?php
		}

		/**
		 * Generate a horizontal slider to select a range between numerical values
		 *
		 * @param mixed   array|string $names HTML names in the order Min value -> Max value. If string is given the [max] and [min] is added to make an array
		 * @param array   $values Initial values of the range in the same order
		 * @param int     $min    Minimum of the range
		 * @param int     $max    Maximum of the range
		 * @param int     $step   Slider move step
		 * @param string  $suffix Suffix to add after numeric values, like % etc...
		 * @param string  $prefix Prefix to add before numeric values, like $ etc...
		 */
		public function slider_range( $names, $values, $min = 0, $max = 100, $step = 1, $suffix = '', $prefix = '' ) {
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

			// Main stuff
			$min = (float) $min;
			$max = (float) $max;
			$step = (float) $step;

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
	?>
	<div class="ipt_uif_empty_box ipt_uif_slider_box ipt-eform-rangebox">
		<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" class="ipt_uif_slider slider_range check_me ipt_uif_text" data-min="<?php echo $min; ?>" data-max="<?php echo $max; ?>" data-step="<?php echo $step; ?>" name="<?php echo esc_attr( trim( $names[0] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[0] ); ?>" value="<?php echo esc_attr( $value_min ); ?>" />
		<input type="number" min="<?php echo $min; ?>" max="<?php echo $max; ?>" step="<?php echo $step; ?>" class="ipt_uif_slider_range_max ipt_uif_text" name="<?php echo esc_attr( trim( $names[1] ) ); ?>" id="<?php echo $this->generate_id_from_name( $names[1] ); ?>" value="<?php echo esc_attr( $value_max ); ?>" />
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
		public function progressbar( $id = '', $start = 0, $classes = array() ) {
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$classes[] = 'ipt_uif_progress_bar';
			$id_attr = '';
			if ( $id != '' ) {
				$id_attr = ' id="' . esc_attr( $id ) . '"';
			}
	?>
	<div class="<?php echo implode( ' ', $classes ); ?>" data-start="<?php echo $start; ?>"<?php echo $id_attr; ?>>
		<div class="ipt_uif_progress_value"></div>
	</div>
			<?php
		}

		public function datepicker( $name, $value, $placeholder = '', $now = false ) {
	?>
	<div class="ipt_uif_datepicker">
		<?php if ( $now ) : ?>
		<?php $this->button( 'NOW', $name . '_now', 'auto', 'secondary', 'normal', array( 'ipt_uif_datepicker_now' ), 'button', false ); ?>
		<?php endif; ?>
		<?php $this->text( $name, $value, $placeholder, 'auto' ); ?>
	</div>
			<?php
		}

		public function datetimepicker( $name, $value, $placeholder = '', $now = false ) {
	?>
	<div class="ipt_uif_datetimepicker">
		<?php if ( $now ) : ?>
		<?php $this->button( 'NOW', $name . '_now', 'auto', 'secondary', 'normal', array( 'ipt_uif_datepicker_now' ), 'button', false ); ?>
		<?php endif; ?>
		<?php $this->text( $name, $value, $placeholder, 'auto' ); ?>
	</div>
			<?php
		}

		public function colorpicker( $name, $value, $placeholder = '' ) {
			$value = '#' . ltrim( $value, '#' );
			$this->text( $name, $value, $placeholder, 'small', 'normal', array( 'ipt_uif_colorpicker', 'code' ) );
		}

		public function printelement( $print_id, $label = 'Print' ) {
	?>
	<div class="ipt_uif_button_container">
		<button class="ipt_uif_button secondary-button auto ipt_uif_printelement" data-printid="<?php echo esc_attr( $print_id ); ?>"><?php echo $label; ?></button>
	</div>
			<?php
		}

		public function heading_type( $name, $selected ) {
			$items = array();
			for ( $i = 1; $i <= 6; $i++ ) {
				$items[] = array(
					'label' => $this->default_messages['elements']['heading'] . ' ' . $i,
					'value' => 'h' . $i,
				);
			}

			$this->select( $name, $items, $selected );
		}

		public function layout_select( $name, $selected ) {
			$id = $this->generate_id_from_name( $name );
	?>
	<div class="ipt_uif_radio_layout_wrap">
	<?php for ( $i = 1; $i <= 4; $i++ ) : $layout = (string) $i; ?>
	<input type="radio" class="ipt_uif_radio ipt_uif_radio_layout" name="<?php echo esc_attr( $name ); ?>" id="<?php echo $id . '_' . $i; ?>" value="<?php echo $i; ?>"<?php if ( $layout == $selected ) echo ' checked="checked"'; ?> />
	<label title="<?php echo sprintf( _nx( '%d Column', '%d Columns', $i, 'eform-admin-layout', 'ipt_fsqm' ), $i ); ?>" for="<?php echo $id . '_' . $i; ?>" class="ipt_uif_label_layout ipt_uif_label_layout_<?php echo $i; ?>"><?php echo $i; ?></label>
	<?php endfor; ?>
	<input type="radio" class="ipt_uif_radio ipt_uif_radio_layout" name="<?php echo esc_attr( $name ); ?>" id="<?php echo $id . '_random'; ?>" value="random"<?php if ( 'random' == $selected ) echo ' checked="checked"'; ?> />
	<label title="<?php _e( 'Automatic Columns', 'ipt_fsqm' ); ?>" for="<?php echo $id . '_random'; ?>" class="ipt_uif_label_layout ipt_uif_label_layout_random"><?php _e( 'Auto', 'ipt_fsqm' ); ?></label>
	</div>
			<?php
		}

		public function alignment_radio( $name, $checked ) {
			$items = array(
				'left', 'center', 'right', 'justify',
			);
	?>
	<div class="ipt_uif_radio_align_wrap">
	<?php foreach ( $items as $item ) : ?>
	<?php $id = $this->generate_id_from_name( $name ) . '_' . $item; ?>
	<input type="radio" class="ipt_uif_radio ipt_uif_radio_align" name="<?php echo $name; ?>" id="<?php echo $id; ?>" value="<?php echo $item; ?>"<?php if ( $checked == $item ) echo ' checked="checked"'; ?> />
	<label for="<?php echo $id; ?>" class="ipt_uif_label_align_<?php echo $item; ?>"><?php echo ucfirst( $item ); ?></label>
	<?php endforeach; ?>
	</div>
			<?php
		}

		public function upload( $name, $value, $title_name = '', $label = 'Upload', $title = 'Choose Image', $select = 'Use Image', $width = '', $height = '', $background_size = '' ) {
			$data = array(
				'title' => $title,
				'select' => $select,
				'settitle' => $this->generate_id_from_name( $title_name ),
			);
			$buttons = array();
			$buttons[] = array(
				$label, '', 'small', 'secondary', 'normal', array( 'ipt_uif_upload_button' ), 'button', array(), array(), '', 'upload'
			);
			$buttons[] = array(
				'', '', 'small', 'secondary', 'normal', array( 'ipt_uif_upload_cancel' ), 'button', array(), array(), '', 'close'
			);
			$preview_style = '';
			$container_style = '';
			if ( $width != '' ) {
				$container_style .= 'max-width: none; width: ' . $width . ';';
			}
			if ( $height != '' ) {
				$container_style .= 'height: ' . $height . ';';
			}
			$preview_style .= 'height: 100%;';
			if ( $background_size != '' ) {
				$preview_style .= 'background-size: ' . $background_size . ';';
			}
	?>
	<div class="ipt_uif_upload">
		<div class="ipt_uif_upload_bg" style="<?php echo esc_attr( $container_style ); ?>">
			<div style="<?php echo esc_attr( $preview_style ); ?>" class="ipt_uif_upload_preview"></div>
		</div>
		<input<?php echo $this->convert_data_attributes( $data ); ?> type="text" name="<?php echo $name; ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $value ); ?>" class="ipt_uif_text fit" />
		<?php //$this->button( $label, '', 'small', 'secondary', 'normal', array(), 'button', false ); ?>
		<?php $this->buttons( $buttons, '', 'center' ); ?>
	</div>
			<?php
		}

		public function webfonts( $name, $selected, $fonts ) {
			$items = array();
			foreach ( $fonts as $f_key => $font ) {
				$items[] = array(
					'label' => $font['label'],
					'value' => $f_key,
					'data' => array(
						'fontinclude' => $font['include'],
					),
				);
			}

			echo '<div class="ipt_uif_font_selector">';

			$this->select( $name, $items, $selected );
			echo ' <span class="ipt_uif_font_preview">Grumpy <strong>wizards</strong> <em>make</em> <strong><em>toxic brew</em></strong> for the evil Queen and Jack.</span>';

			echo '</div>';
		}

		public function hiddens( $hiddens, $name_prefix = '' ) {
			if ( !is_array( $hiddens ) || empty( $hiddens ) ) {
				return;
			}
	?>
	<?php foreach ( $hiddens as $h_key => $h_val ) : ?>
	<?php $name = $name_prefix != '' ? $name_prefix . '[' . $h_key . ']' : $h_key; ?>
	<input type="hidden" name="<?php echo $name; ?>" value="<?php echo esc_attr( $h_val ); ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" />
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
		 <?php echo $label; ?>
	</label>
			<?php
		}

		/*==========================================================================
		 * ICON SELECTOR
		 *========================================================================*/

		/**
		 * Print a font Icon Picker
		 *
		 * @param  string          $name                  HTML Name
		 * @param  string|int      $selected_icon         Selected Icon Code
		 * @param  string|bool     $no                    Placeholder text or false if there has to be an icon
		 * @param  string          $by                    What to pick by -> hex | class
		 * @return void
		 */
		public function icon_selector( $name, $selected_icon, $no = 'Do not show', $by = 'hex', $print_cancel = false ) {
			$this->clear();
			$buttons = array();
			$buttons[] = array(
				'', '', 'small', 'secondary', 'normal', array( 'ipt_uif_icon_cancel' ), 'button', array(), array(), '', 'close'
			);
			if ( false === $no ) {
				$print_cancel = false;
			}
	?>
	<input type="text"<?php if ( false === $no ) echo ' data-no-empty="true"'; else echo ' placeholder="' . esc_attr( $no ) . '"'; ?> data-icon-by="<?php echo esc_attr( $by ); ?>" class="ipt_uif_icon_selector code small-text" size="15" name="<?php echo $name; ?>" id="<?php echo $this->generate_id_from_name( $name ); ?>" value="<?php echo esc_attr( $selected_icon ); ?>" />
	<?php if ( $print_cancel ) : ?>
	<?php $this->buttons( $buttons, '', 'ipt_uif_fip_button' ); ?>
	<?php endif; ?>
			<?php
			$this->clear();
		}

		public function print_icon_by_class( $icon = 'none', $size = 24 ) {
			if ( is_numeric( $icon ) ) {
				$this->print_icon_by_data( $icon, $size );
				return;
			}
	?>
	<?php if ( $icon != 'none' ) : ?>
	<i class="ipt-icomoon-<?php echo esc_attr( $icon ); ?> ipticm" style="font-size: <?php echo $size; ?>px;"></i>
	<?php endif; ?>
			<?php
		}

		public function print_icon_by_data( $data = 'none', $size = 24 ) {
			if ( ! is_numeric( $data ) ) {
				$this->print_icon_by_class( $data, $size );
				return;
			}
	?>
	<?php if ( $data != 'none' ) : ?>
	<i class="ipticm" data-ipt-icomoon="&#x<?php echo dechex( $data ); ?>;" style="font-size: <?php echo $size; ?>px;"></i>
	<?php endif; ?>
			<?php
		}

		public function print_icon( $icon = 'none', $size = 24 ) {
			if ( 'none' == $icon || empty( $icon ) ) {
				return;
			}
			if ( is_numeric( $icon ) ) {
				$this->print_icon_by_data( $icon, $size );
			} else {
				$this->print_icon_by_class( $icon, $size );
			}
		}


		/*==========================================================================
		 * TABS AND BOXES
		 *========================================================================*/

		/**
		 * Generate Tabs with callback populators
		 * Generates all necessary HTMLs. No need to write any classes manually.
		 *
		 * @param array   $tabs        Associative array of all the tab elements.
		 * $tab = array(
		 *      'id' => 'ipt_fsqm_form_name',
		 *      'label' => 'Form Name',
		 *      'callback' => 'function',
		 *      'scroll' => false,
		 *      'classes' => array(),
		 *      'has_inner_tab' => false,
		 *  );
		 * @param type    $collapsible
		 * @param type    $vertical
		 */
		public function tabs( $tabs, $collapsible = false, $vertical = false ) {
			$data_collapsible = ( $collapsible == true ) ? ' data-collapsible="true"' : '';
			$classes = array( 'ipt_uif_tabs' );
			$classes[] = ( $vertical == true ) ? 'vertical' : 'horizontal';
	?>
	<div<?php echo $data_collapsible; ?> class="<?php echo implode( ' ', $classes ); ?>">
		<ul>
			<?php foreach ( $tabs as $tab ) : ?>
			<li><a href="#<?php echo $tab['id']; ?>"><?php echo $tab['label']; ?></a></li>
			<?php endforeach; ?>
		</ul>
		<?php foreach ( $tabs as $tab ) : ?>
		<?php
				$tab = wp_parse_args( $tab, array(
						'id' => '',
						'label' => '',
						'callback' => '',
						'scroll' => true,
						'classes' => array(),
						'has_inner_tab' => false,
					) );

			if ( !$this->check_callback( $tab['callback'] ) ) {
				//var_dump($tab['callback']);
				$tab['callback'] = array(
					array( &$this, 'msg_error' ), 'Invalid Callback',
				);
			}
			$tab['callback'][1][] = $tab;
			$tab_classes = isset( $tab['classes'] ) && is_array( $tab['classes'] ) ? $tab['classes'] : array();
			if ( $tab['has_inner_tab'] ) {
				$tab_classes[] = 'has-inner-tab';
			} else if ( $tab['scroll'] ) {
				$tab_classes[] = 'scroll-vertical';
			}

	?>
		<div id="<?php echo $tab['id']; ?>" class="<?php echo implode( ' ', $tab_classes ); ?>">
			<?php if ( true == $tab['scroll'] && false == $tab['has_inner_tab'] ) : ?>
				<div class="ipt_uif_tabs_inner">
			<?php endif; ?>
					<?php call_user_func_array( $tab['callback'][0], $tab['callback'][1] ); ?>
					<?php $this->clear(); ?>
			<?php if ( true == $tab['scroll'] && false == $tab['has_inner_tab'] ) : ?>
				</div>
			<?php endif; ?>
		</div>
		<?php endforeach; ?>
	</div>
	<div class="clear"></div>
			<?php
		}

		/**
		 * Create a shadow container.
		 *
		 * @param string  $style   One of the valid style of shadow boxes -> lifted_corner | glowy
		 * @param mixed   (array|string) $callback The callback function to populate.
		 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll. Default is 400.
		 * @param string  $id      HTML ID
		 * @param array   $classes HTML classes
		 */
		public function shadow( $style, $callback, $scroll = 400, $id = '', $classes = array() ) {
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$classes[] = 'ipt_uif_shadow';
			switch ( strtolower( $style ) ) {
			default :
			case 'lifted_corner' :
			case 'corner' :
			case 'lifted corner' :
			case 'lifted-corner' :
				$style = 'lifted_corner';
				break;
			case 'glowy' :
				$style = 'glowy';
				break;
			}
			$this->div( $style, $callback, $scroll, $id, $classes );
		}

		/**
		 * Create a box container.
		 *
		 * @param string  $style   One of the valid style of boxes -> white | cyan | sky
		 * @param mixed   (array|string) $callback The callback function to populate.
		 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll. Default is 400.
		 * @param string  $id      HTML ID
		 * @param array   $classes HTML classes
		 */
		public function box( $style, $callback, $scroll = 0, $id = '', $classes = array() ) {
			if ( !is_array( $classes ) ) {
				$classes = (array) $classes;
			}
			$classes[] = 'ipt_uif_box';
			switch ( strtolower( $style ) ) {
			default :
			case 'white' :
				$style = 'white';
				break;
			case 'cyan' :
				$style = 'cyan';
				break;
			case 'sky' :
				$style = 'sky';
				break;
			}
			$this->div( $style, $callback, $scroll, $id, $classes );
		}

		public function collapsible( $label, $callback, $open = false ) {
	?>
	<div class="ipt_uif_shadow glowy ipt_uif_collapsible" data-opened="<?php echo $open; ?>">
		<div class="ipt_uif_box cyan">
			<h3><a class="ipt_uif_collapsible_handle_anchor" href="javascript:;"><span class="ipt-icomoon-file3 heading_icon"></span><span class="ipt-icomoon-arrow-down2 collapsible_state"></span><?php echo $label; ?></a></h3>
		</div>
		<?php $this->div( 'ipt_uif_collapsed', $callback ); ?>
	</div>
			<?php
		}

		public function collapsible_head( $label, $open = false ) {
	?>
	<div class="ipt_uif_shadow glowy ipt_uif_collapsible" data-opened="<?php echo $open; ?>">
		<div class="ipt_uif_box cyan">
			<h3><a class="ipt_uif_collapsible_handle_anchor" href="javascript:;"><span class="ipt-icomoon-file3 heading_icon"></span><span class="ipt-icomoon-arrow-down2 collapsible_state"></span><?php echo $label; ?></a></h3>
		</div>
		<div class="ipt_uif_collapsed">
			<?php
		}

		public function collapsible_tail() {
	?>
			<?php $this->clear(); ?>
		</div>
	</div>
			<?php
		}

		/**
		 * Create a box container nested inside a shadow container.
		 *
		 * @param array   $styles  Array of shadow style and box style.
		 * @param mixed   (array|string) $callback The callback function to populate.
		 * @param int     $scroll  The scroll height value in pixels. 0 if no scroll. Default is 400.
		 * @param string  $id      HTML ID
		 * @param array   $classes HTML classes
		 */
		public function shadowbox( $styles, $callback, $scroll = 0, $id = '', $classes = array() ) {
			if ( !is_array( $styles ) ) {
				$styles = array( 'lifted_corner', 'cyan' );
			}
			$styles[0] = array_merge( (array) $styles[0], array( 'ipt_uif_shadow' ) );
			$styles[1] = array_merge( (array) $styles[1], array( 'ipt_uif_box' ) );
			$this->div( $styles, $callback, $scroll, $id, $classes );
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
		public function iconbox( $label, $callback, $icon = 'info2', $scroll = 0, $id = '', $classes = array() ) {
			if ( !$this->check_callback( $callback ) ) {
				$this->msg_error( 'Invalid Callback supplied' );
				return;
			}
	?>
	<div class="ipt_uif_iconbox">
		<div class="ipt_uif_box cyan">
			<h3><span class="ipt-icomoon-<?php echo esc_attr( $icon ); ?>"></span><?php echo $label; ?></h3>
		</div>
		<?php $this->div( 'ipt_uif_iconbox_inner', $callback, $scroll, $id, $classes ); ?>
	</div>
			<?php
		}

		public function iconbox_head( $label, $icon, $after = '' ) {
			if ( '' != $after ) {
				$after = '<div class="ipt_uif_float_right">' . $after . '</div>';
			}
	?>
	<div class="ipt_uif_iconbox">
		<div class="ipt_uif_box cyan ipt_uif_container_head">
			<?php echo $after; ?><h3><span class="ipt-icomoon-<?php echo esc_attr( $icon ); ?>"></span><?php echo $label; ?></h3>
		</div>
		<div class="ipt_uif_iconbox_inner">
			<?php
		}

		public function iconbox_tail() {
	?>
		</div>
	</div>
			<?php
		}

		/*==========================================================================
		 * SORTABLE DRAGGABLE & ADDABLE LIST
		 *========================================================================*/
		/**
		 * Creates a Sortable, Draggable and/or Addable container UI.
		 *
		 * @param array   $settings An associative array of settings. The format is
		 * <code>
		 * array(
		 *      'key' => '__SDAKEY__',
		 *      'columns' => array(
		 *          0 => array(
		 *              'label' => 'Heading',
		 *              'size' => '10',
		 *              'type' => 'text', //This is the callback function from IPT_Plugin_UIF_Admin
		 *          ),
		 *      ),
		 *      'features' => array(
		 *          'sortable' => true,
		 *          'draggable' => true,
		 *          'addable' => true,
		 *      ),
		 *      'labels' => array(
		 *          'confirm' => 'Confirm delete. The action can not be undone.',
		 *          'add' => 'Add New Item',
		 *          'del' => 'Click to delete',
		 *          'drag' => 'Drag this to rearrange',
		 *      ),
		 * );
		 * </code>
		 * @param array   $items    An associative array of items. The format is
		 * <code>
		 * array(
		 *      array([...,])[,...]
		 * )
		 * </code>
		 * Each array should be a list of parameters to the callback function.
		 * @param array   $data     An associative array of callbacks for the data section. The key passed here should match with settings[key]
		 * <code>
		 * array([,...])
		 * </code>
		 */
		public function sda_list( $settings, $items, $data, $max_key, $id = '' ) {
			$default = array(
				'key' => '__SDAKEY__',
				'columns' => array(),
				'features' => array(),
				'labels' => array(),
			);
			$settings = wp_parse_args( $settings, $default );
			$settings['labels'] = wp_parse_args( $settings['labels'], array(
				'confirm' => 'Confirm delete. The action can not be undone.',
				'confirmtitle' => 'Confirm Deletion',
				'add' => 'Add New Item',
				'del' => 'Click to delete',
				'drag' => 'Drag this to rearrange',
			) );
			$settings['features'] = wp_parse_args( $settings['features'], array(
				'draggable' => true,
				'addable' => true,
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
	<div class="ipt-backoffice-sda-wrap"<?php echo ($id != '' ? ' id="' . esc_attr( $id ) . '"' : '') ?>>
		<div class="ipt-backoffice-sda-inner">
			<div class="ipt-eform-sda ipt_uif_sda"<?php echo $feature_attr; ?>>
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
						<div class="ipt_uif_sda_column_<?php echo $column['size']; ?>">
							<?php $this->generate_label( is_string( $item[ $col_key ][0] ) ? $item[ $col_key ][0] : '', $column['label'] ); ?>
							<?php call_user_func_array( array( $this, $column['type'] ), (array)$item[$col_key] ); ?>
						</div>
						<?php endforeach; ?>

						<div class="clear"></div>
					</div>
					<?php $data_total++; endforeach; ?>
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
					<div class="ipt_uif_sda_column_<?php echo $column['size']; ?>">
						<?php $this->generate_label( is_string( $data[ $col_key ][0] ) ? $data[ $col_key ][0] : '', $column['label'] ); ?>
						<?php call_user_func_array( array( $this, $column['type'] ), $data[$col_key] ); ?>
					</div>
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
							'confirm' => $settings['labels']['confirm'],
						),
						array(),
						'',
						'plus',
					);

					$this->buttons( $buttons, '', array( 'ipt_uif_sda_foot' ) );
				}
				?>
			</div>
		</div>
	</div>
			<?php
		}

		/*==========================================================================
		 * LAYOUT BUILDER API
		 *========================================================================*/
		public function builder_init( $id, $callback, $labels = array() ) {
			$this->builder_labels = (array) $labels;
			$this->div( 'ipt_uif_builder', $callback, 0, $id );
		}

		public function builder_adder( $label, $id, $l_key, $callback, $parameter, $layout, $replace_by = '__RBY__' ) {
			$this->button( $label, $id, 'large', 'ui', 'normal', array( 'center', 'no-margin', 'ipt_uif_builder_add_layout' ), 'button', true, array(), array(), '', 'plus' );
	?>
	<script type="text/html" class="ipt_uif_builder_tab_li">
		<?php ob_start(); ?>
		<li class="ipt_uif_builder_layout_tabs">
			<a href=""><span class="ipt-icomoon-insert-template ipt_uif_builder_tab_droppable"></span><span class="ipt-icomoon-expand ipt_uif_builder_tab_sort">&nbsp;</span></a>
			<input type="hidden" class="ipt_uif_builder_helper tab_position" name="containers[]" value="<?php echo $l_key; ?>" />
		</li>
		<?php $output = ob_get_clean(); ?>
		<?php echo htmlspecialchars( $output ); ?>
	</script>
	<script type="text/html" class="ipt_uif_builder_tab_content">
		<?php ob_start(); ?>
		<input type="hidden" class="ipt_uif_builder_helper element_m_type" value="<?php echo $layout['m_type']; ?>" />
		<input type="hidden" class="ipt_uif_builder_helper element_type" value="<?php echo $layout['type']; ?>" />
		<input type="hidden" class="ipt_uif_builder_helper element_key" value="<?php echo $l_key; ?>" />
		<div class="ipt_uif_builder_settings ipt_uif_builder_tab_settings">
			<?php call_user_func_array( $callback, $parameter ); ?>
		</div>
		<div class="ipt_uif_builder_drop_here ipt_uif_builder_drop_here_empty" data-empty="<?php echo $this->default_messages['droppable_messages']['empty']; ?>" data-replaceby="<?php echo esc_attr( $replace_by ); ?>" data-container-key="<?php echo $l_key; ?>"></div>
		<?php $output = ob_get_clean(); ?>
		<?php echo htmlspecialchars( $output ); ?>
	</script>
			<?php
		}

		public function builder_wp_editor( $id, $label = 'Save Settings', $heading = '' ) {
			echo '<div class="ipt_uif_builder_wp_editor ipt_uif_shadow glowy">';
			if ( $heading !== '' ) {
				echo '<h3 class="settings-heading"><i class="ipt-icomoon-text_fields"></i> <span class="settings-heading-text">' . $heading . '</span></h3>';
			}
			echo '<div class="eform-wp-editor-wrap">';
			wp_editor( '', $id, array( 'editor_class' => 'ipt_uif_builder_wp_editor', 'teeny' => true ) );
			echo '</div>';
			$this->button( $label, $id . '_wp_editor_save', 'large', 'ui', 'normal', array( 'ipt_uif_builder_settings_save_wp_editor', 'center' ) );
			echo '</div>';
		}

		public function builder_settings_box( $id, $label ) {
	?>
	<div class=" ipt_uif_builder_settings_box_parent no_padding">
		<h3 class="settings-heading"><i class="ipt-icomoon-settings"></i> <span class="settings-heading-text"></span></h3>
		<div class="ipt_uif_box white ipt_uif_builder_settings_box" id="<?php echo $id; ?>">
			<div class="ipt_uif_builder_settings_box_container">
				<div class="clear"></div>
			</div>
		</div>
		<?php $this->button( $label, $id . '_save', 'large', 'ui', 'normal', array( 'ipt_uif_builder_settings_save', 'center' ) ); ?>
	</div>
			<?php
			//return;
			/*
			$this->shadowbox(array('lifted_corner', 'white ipt_uif_builder_settings_box_container'), array($this, 'clear'), 400, $id, array('ipt_uif_builder_settings_box'));
			$this->button($label, $id . '_save', 'large', 'ui', 'normal', array('ipt_uif_builder_settings_save', 'center'));
			*/
		}

		public function builder_elements( $element, $e_key, $l_key, $class, $callback, $parameter, $data_attr, $data = array(), $child_cb = null, $replace_this = '__RTHIS__', $draggables = false, &$keys = null ) {
			$disabled_attr = '';
			if ( $draggables ) {
				$disabled_attr = ' disabled="disabled"';
			}

			$elm_sub_title = '';
			if ( $element['sub_title'] !== '' ) {
				$elm_sub_title = ' : ' . $element['sub_title'];
				$element['description'] = $element['sub_title'];
			}
			$grayed_out_class = '';
			if ( isset( $element['grayed_out'] ) && $element['grayed_out'] == true ) {
				$grayed_out_class = ' grayed';
			}
			$element_label = isset( $this->builder_labels[$element['m_type']] ) ? $this->builder_labels[$element['m_type']] : $element['m_type'];
	?>
	<div class="ipt_uif_droppable_element ipt_uif_icon_<?php echo $class; ?>"<?php echo $this->convert_data_attributes( $data_attr ); ?> data-replacethis="<?php echo esc_attr( $replace_this ); ?>">
		<input<?php echo $disabled_attr; ?> type="hidden" class="ipt_uif_builder_helper element_m_type" name="<?php echo $replace_this; ?>[<?php echo $l_key; ?>][elements][m_type][]" value="<?php echo $element['m_type']; ?>" />
		<input<?php echo $disabled_attr; ?> type="hidden" class="ipt_uif_builder_helper element_type" name="<?php echo $replace_this; ?>[<?php echo $l_key; ?>][elements][type][]" value="<?php echo $element['type']; ?>" />
		<input<?php echo $disabled_attr; ?> type="hidden" class="ipt_uif_builder_helper element_key" name="<?php echo $replace_this; ?>[<?php echo $l_key; ?>][elements][key][]" value="<?php echo $e_key; ?>" />
		<?php if ( $draggables == false ) : ?>
		<div class="ipt_uif_builder_settings">
			<?php call_user_func_array( $callback, $parameter ); ?>
		</div>
		<?php else : ?>
		<script type="text/html" class="ipt_uif_builder_settings">
			<?php ob_start(); ?>
			<?php call_user_func_array( $callback, $parameter ); ?>
			<?php $output = ob_get_clean();
			echo htmlspecialchars( $output );
	?>
		</script>
		<?php endif; ?>
		<div class="ipt_uif_droppable_element_wrap<?php echo $grayed_out_class; ?>">
			<a title="<?php echo $this->default_messages['droppable_messages']['drag']; ?>" class="icon ipt-icomoon-expand3 ipt_uif_builder_sort_handle ipt_uif_builder_action_handle" href="javascript:;"></a>
			<a title="<?php echo $this->default_messages['droppable_messages']['settings']; ?>" class="ipt-icomoon-cog ipt_uif_builder_settings_handle ipt_uif_builder_action_handle" href="javascript:;"></a>
			<a title="<?php echo $this->default_messages['droppable_messages']['copy']; ?>" class="icon ipt-icomoon-copy3 ipt_uif_builder_action_handle ipt_uif_builder_copy_handle" href="javascript:;"></a>
			<?php if ( isset( $element['droppable'] ) && $element['droppable'] == true ) : ?>
			<a title="<?php echo $this->default_messages['droppable_messages']['expand']; ?>" class="ipt-icomoon-arrow-down3 ipt_uif_builder_droppable_handle ipt_uif_builder_action_handle" href="javascript:;"></a>
			<?php endif; ?>
			<h3 class="element_title_h3" title="<?php echo esc_attr( $element['description'] ); ?>"><span class="element_info"><?php printf( '(%1$s){%2$s}', $element_label, $e_key ); ?></span> <span class="element_name"><?php echo $element['title']; ?></span> <span class="element_title"><?php echo $elm_sub_title; ?></span></h3>
			<?php $this->clear(); ?>
			<?php if ( isset( $element['droppable'] ) && $element['droppable'] == true ) : ?>
			<?php $child_name_pref = $element['m_type']; ?>
			<?php $child_layout_key = $e_key; ?>
			<?php $do_child_element = !empty( $data ) && isset( $data['elements'] ) && !empty( $data['elements'] ); ?>
			<div class="ipt_uif_builder_drop_here ipt_uif_builder_drop_here_inner<?php if ( !$do_child_element ) echo ' ipt_uif_builder_drop_here_empty'; ?>" data-replaceby="<?php echo $child_name_pref; ?>" data-container-key="<?php echo $e_key; ?>" data-empty="<?php echo $this->default_messages['droppable_messages']['empty']; ?>">
				<?php if ( $do_child_element ) : ?>
				<?php foreach ( $data['elements'] as $child_element ) : //Format of child element should be like array('m_type' => '', 'key' => '') ?>
				<?php $new_cb_parameters = call_user_func_array( $child_cb, array( $child_element, $child_layout_key ) ); ?>
				<?php call_user_func_array( array( $this, 'builder_elements' ), array_merge( $new_cb_parameters, array( $child_name_pref, $draggables ) ) ); ?>
				<?php endforeach; ?>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
			<?php
		}

		public function builder_droppables( $id, $items, $key = '__EKEY__', $l_key = '__LKEY__', $back = 'Go Back', $replace_this = '__RTHIS__' ) {
			$keys = array();
	?>
	<div id="<?php echo esc_attr( $id ); ?>" class="ipt_uif_droppable" data-key="<?php echo esc_attr( $key ); ?>">
		<?php $this->button( $back, '', 'small', 'secondary', 'normal', array( 'center', 'ipt_uif_droppable_back', 'no_margin' ), 'button', false, array(), array(), '', 'mail-reply' ); ?>
		<?php foreach ( $items as $item_id => $item ) : ?>
		<?php $keys[$item_id] = 0; ?>
		<div id="<?php echo $item['id']; ?>" class="ipt_uif_droppable_elements_parent">
			<span class="eform-droppable-icon"><i class="ipt-icomoon-<?php echo $item['icon']; ?>"></i></span>
			<h3><?php echo $item['title']; ?></h3>
			<p><?php echo $item['description']; ?></p>
		</div>

		<div id="<?php echo $item['id'] ?>_elements" class="ipt_uif_droppable_elements_wrap">
			<?php foreach ( (array) $item['elements'] as $elem_id => $element ) : ?>
			<?php
			$data_attr = $this->builder_data_attr( $element );
			$callback = $element['callback'];
			$parameters = array_merge( (array) $element['parameters'], array( $item_id, $elem_id, $item, $element ) );
?>
			<?php $this->builder_elements( $element, $key, $l_key, $elem_id, $callback, $parameters, $data_attr, array(), null, $replace_this, true ); ?>
			<?php endforeach; ?>

			<?php $this->clear(); ?>
		</div>
		<?php endforeach; ?>
		<?php $this->clear(); ?>
	</div>
	<input type="hidden" class="ipt_uif_builder_default_keys" value="<?php echo esc_attr( json_encode( $keys ) ); ?>" />
	<input type="hidden" class="ipt_uif_builder_replace_string" value="<?php echo esc_attr( json_encode( array(
						'key' => $key,
						'l_key' => $l_key,
					) ) ); ?>" />
	<?php $this->clear(); ?>
			<?php
		}

		public function builder_sortables( $id, $type, $layouts, $callback, $settings_callback, $msgs, $replace_by = '__RBY__', $keys = array() ) {
			$msgs = wp_parse_args( $msgs, $this->default_messages['sortable_messages'] );
			extract( $msgs );
	?>
	<div class="ipt_uif_builder_layout <?php echo $type; ?>" id="<?php echo esc_attr( $id ); ?>" data-empty="<?php echo $empty_msg; ?>">
		<ul class="ipt_uif_builder_layout_tab">
			<?php foreach ( $layouts as $l_key => $layout ) : ?>
			<?php
			$wrapper_class = 'ipt_uif_builder_layout_tabs';
			if ( isset( $layout['grayed_out'] ) && $layout['grayed_out'] == true ) {
				$wrapper_class .= ' grayed';
			}
			?>
			<li id="<?php echo esc_attr( $id . '_li_' . $l_key ); ?>" class="<?php echo $wrapper_class; ?>">
				<a href="#<?php echo esc_attr( $id . '_' . $l_key ); ?>"><span class="ipt-icomoon-insert-template ipt_uif_builder_tab_droppable"></span><span class="ipt-icomoon-expand ipt_uif_builder_tab_sort">&nbsp;</span></a>
				<input type="hidden" class="ipt_uif_builder_helper tab_position" name="containers[]" value="<?php echo $l_key; ?>" />
			</li>
			<?php $keys['layout'] = max( array( $keys['layout'], $l_key ) ); ?>
			<?php endforeach; unset( $wrapper_class ); ?>
		</ul>

		<?php foreach ( $layouts as $l_key => $layout ) : ?>
		<?php
		$wrapper_class = 'ipt_uif_builder_layout_tabs_wrap';
		if ( isset( $layout['grayed_out'] ) && $layout['grayed_out'] == true ) {
			$wrapper_class .= ' grayed';
		}
		?>
		<div id="<?php echo esc_attr( $id . '_' . $l_key ); ?>" class="<?php echo $wrapper_class; ?>" data-ipt-uif-builder-li="<?php echo esc_attr( $id . '_li_' . $l_key ); ?>">
			<input type="hidden" class="ipt_uif_builder_helper element_m_type" value="<?php echo $layout['m_type']; ?>" />
			<input type="hidden" class="ipt_uif_builder_helper element_type" value="<?php echo $layout['type']; ?>" />
			<input type="hidden" class="ipt_uif_builder_helper element_key" value="<?php echo $l_key; ?>" />
			<div class="ipt_uif_builder_settings ipt_uif_builder_tab_settings">
				<?php call_user_func_array( $settings_callback, array( $l_key, $layout ) ); ?>
			</div>
			<div class="ipt_uif_builder_drop_here" data-replaceby="<?php echo esc_attr( $replace_by ); ?>" data-container-key="<?php echo $l_key; ?>">
				<?php foreach ( $layout['elements'] as $element ) : ?>
				<?php $elements_param = call_user_func_array( $callback, array( $element, $l_key ) ); ?>
				<?php call_user_func_array( array( $this, 'builder_elements' ), array_merge( $elements_param, array( $replace_by, false ) ) ); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<div class="ipt_uif_builder_layout_settings_toolbar ipt-eform-backoffice">
		<ul>
			<li><?php $this->help( $layout_helper_msg, $layout_helper_title ); ?></li>
			<li><a title="<?php echo $toolbar_copy; ?>" class="ipt-icomoon-copy3 ipt_uif_builder_layout_copy" href="javascript:;"></a></li>
			<li><a title="<?php echo $toolbar_settings; ?>" class="ipt-icomoon-cog ipt_uif_builder_layout_settings" href="javascript:;"></a></li>
			<li><a title="<?php echo $toolbar_deleter; ?>" class="ipt-icomoon-cancel-circle ipt_uif_builder_layout_del" data-title="<?php echo esc_attr( $deleter_title ); ?>" data-msg="<?php echo esc_attr( $deleter_msg ); ?>" href="javascript:;"></a></li>
		</ul>
	</div>
	<div class="ipt_uif_builder_deleter" data-title="<?php echo esc_attr( $deldropper_title ); ?>" data-msg="<?php echo esc_attr( $deldropper_msg ); ?>">
		<div class="ipt_uif_builder_deleter_wrap">
			<span class="ipt-icomoon-remove"></span>
		</div>
	</div>

	<input type="hidden" class="ipt_uif_builder_keys" value="<?php echo esc_attr( json_encode( $keys ) ); ?>" />
			<?php
		}

		public function builder_data_attr( $element ) {
			$data = array();
			if ( isset( $element['dbmap'] ) && $element['dbmap'] == true ) {
				$data['dbmap'] = true;
			}
			if ( isset( $element['droppable'] ) && $element['droppable'] == true ) {
				$data['droppable'] = true;
			}
			return $data;
		}


		/*==========================================================================
		 * MESSAGES
		 *========================================================================*/
		public function help( $msg, $title = '', $left = false ) {
	?>
	<div class="ipt_uif_msg <?php if ( $left ) echo 'ipt_uif_msg_left' ?>">
		<a href="javascript:;" class="ipt_uif_msg_icon" title="<?php echo $title; ?>"><i class="ipt-icomoon-live_help"></i></a>
		<div class="ipt_uif_msg_body">
			<?php echo wpautop( $msg ); ?>
		</div>
	</div>
			<?php
		}

		public function help_head( $title = '', $left = false ) {
	?>
	<div class="ipt_uif_msg <?php if ( $left ) echo 'ipt_uif_msg_left' ?>">
		<a href="javascript:;" class="ipt_uif_msg_icon" title="<?php echo $title; ?>"><i class="ipt-icomoon-live_help"></i></a>
		<div class="ipt_uif_msg_body">
			<?php
		}

		public function help_tail() {
	?>
		</div>
	</div>
			<?php
		}

		/**
		 * Prints an error message in style.
		 *
		 * @param string  $msg  The message
		 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
		 * @return string The HTML output
		 */
		public function msg_error( $msg = '', $echo = true ) {
			return $this->print_message( 'red', $msg, $echo );
		}

		/**
		 * Prints an update message in style.
		 *
		 * @param string  $msg  The message
		 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
		 * @return string The HTML output
		 */
		public function msg_update( $msg = '', $echo = true ) {
			return $this->print_message( 'yellow', $msg, $echo );
		}

		/**
		 * Prints an okay message in style.
		 *
		 * @param string  $msg  The message
		 * @param bool    $echo TRUE(default) to echo the output, FALSE to just return
		 * @return string The HTML output
		 */
		public function msg_okay( $msg = '', $echo = true ) {
			return $this->print_message( 'green', $msg, $echo );
		}

		public function print_message( $style, $msg = '', $echo = true ) {
			$icon = 'ipt-icomoon-check';
			if ( 'yellow' == $style || 'update' == $style ) {
				$icon = 'ipt-icomoon-warning';
			} else if ( 'red' == $style || 'error' == $style ) {
				$icon = 'ipt-icomoon-times';
			}
			$output = '<div class="ipt_uif_message ' . $style . '"><p><i class="ipticm ' . $icon . '"></i> ' . $msg . '</p></div>';
			if ( $echo )
				echo $output;
			return $output;
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
				<svg class="eform-loader-circular" viewBox="25 25 50 50">
					<circle class="eform-loader-path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"></circle>
				</svg>
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

		/*==========================================================================
		 * WORDPRESS SPECIFIC HELPER FUNCTIONS
		 *========================================================================*/
		public function dropdown_pages( $args = '' ) {
			$defaults = array(
				//Dropdown arguments
				'name' => 'page_id',
				'selected' => 0,
				'validation' => false,
				'disabled' => false,
				'show_option_none' => '',
				'option_none_value' => '0',
				//Page arguments
				'depth' => 0,
				'child_of' => 0,
			);
			$r = wp_parse_args( $args, $defaults );
			extract( $r, EXTR_SKIP );

			$pages = get_pages( $r );

			$items = array();

			if ( '' != $show_option_none ) {
				$items[] = array(
					'value' => $option_none_value,
					'label' => $show_option_none,
				);
			}

			foreach ( $pages as $page ) {
				$items[] = array(
					'value' => $page->ID,
					'label' => $page->post_title,
				);
			}

			$this->select( $name, $items, $selected, $validation, false, $disabled );
		}

		/*==========================================================================
		 * OTHER INTERNAL METHODS
		 *========================================================================*/
		protected function webfont_text() {
	?>
	<h2>Grumpy wizards make toxic brew for the evil Queen and Jack.</h2>
	<h3>Grumpy wizards make toxic brew for the evil Queen and Jack.</h3>
	<h4>Grumpy wizards make toxic brew for the evil Queen and Jack.</h4>
	<p>Grumpy <strong>wizards</strong> <em>make</em> <strong><em>toxic brew</em></strong> for the evil Queen and Jack.</p>
	<p><strong>Grumpy wizards make toxic brew for the evil Queen and Jack.</strong></p>
	<p><em>Grumpy wizards make toxic brew for the evil Queen and Jack.</em></p>
			<?php
		}
	}

endif;
