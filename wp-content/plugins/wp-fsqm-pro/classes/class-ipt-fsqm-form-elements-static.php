<?php
/**
 * WP Feedback, Surver & Quiz Manager - Pro Form Elements Class
 * Static APIs
 *
 * @package WP Feedback, Surver & Quiz Manager - Pro
 * @subpackage Form Elements
 * @author Swashata Ghosh <swashata@intechgrity.com>
 */
class IPT_FSQM_Form_Elements_Static {
	/*==========================================================================
	 * SYSTEM APIs - Needs to be called
	 *========================================================================*/
	/**
	 * Admin Ajax Init
	 * Call inside the loader with (is_admin()) context
	 */
	public static function admin_init() {
		self::shortcode_to_wp_editor_api_init();
		self::ipt_fsqm_quick_preview();
		add_action( 'init', array( __CLASS__, 'admin_init_hook' ) );
	}

	public static function admin_init_hook() {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'fsqm_mce_extendor' ) );
		add_filter( 'mce_external_plugins', array( __CLASS__, 'mce_external_plugins' ) );
		add_filter( 'mce_buttons', array( __CLASS__, 'mce_buttons' ) );
		add_action( 'before_wp_tiny_mce', array( __CLASS__, 'fsqm_mce_icons' ) );
	}

	public static function common_init() {
		self::richtext_init();
		self::uif_label_latex_init();
		self::ipt_fsqm_report();
		self::ipt_fsqm_shortcodes_init();
		self::ipt_fsqm_save_form();
		self::standalone_form_init();
		self::email_rewrite_init();
		self::payment_email_rewrite_init();
		self::user_portal_init();
		self::uploader_ajax_init();
		self::coupon_ajax_init();
		self::payment_sync_init();
		self::countryjs_api();
	}

	/*==========================================================================
	 * Country Listing APIs
	 *========================================================================*/

	public static function countryjs_api() {
		// Get country list
		add_action( 'wp_ajax_eform_countryjs_clist', array( __CLASS__, 'get_countries' ) );
		add_action( 'wp_ajax_nopriv_eform_countryjs_plist', array( __CLASS__, 'get_countries' ) );
		// Get province list
		add_action( 'wp_ajax_eform_countryjs_plist', array( __CLASS__, 'get_province' ) );
		add_action( 'wp_ajax_nopriv_eform_countryjs_plist', array( __CLASS__, 'get_province' ) );
	}

	public static function get_countries() {
		$path = IPT_FSQM_Loader::$abs_path . '/lib/countryjs/';
		$countries = array();
		$files = @scandir( $path );
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( $files && is_array( $files ) && count( $files ) > 0 ) {
			foreach ( $files as $file ) {
				//var_dump($file);
				if ( preg_match( '/(.+)\.json/i', $file ) ) {
					$country = @json_decode( file_get_contents( $path . $file ), ARRAY_A );
					if ( is_array( $country ) && isset( $country['name'] ) ) {
						$countries[ $file ] = $country['name'];
					}
					unset( $country );
				}
			}
		}
		echo json_encode( $countries );
		die();
	}

	public static function get_province() {
		$path = IPT_FSQM_Loader::$abs_path . '/lib/countryjs/';
		$country = @$_REQUEST['country'];
		// Sanitize
		// Prevent hacks B-) BOOAH
		// Try doing something like ../../../wp-content/... }-D
		$country = preg_replace( '/[^a-zA-Z0-9\._-]/i', '', $country );
		$location = $path . $country;
		$province = array();
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( file_exists( $location ) ) {
			$cinfo = @json_decode( file_get_contents( $location ), ARRAY_A );
			if ( is_array( $cinfo ) && isset( $cinfo['provinces'] ) ) {
				$province = $cinfo['provinces'];
			}
		}
		echo json_encode( $province );
		die();
	}

	public static function get_country_list() {
		return json_decode( '{"afghanistan.json":"Afghanistan","albania.json":"Albania","algeria.json":"Algeria","american_samoa.json":"American Samoa","angola.json":"Angola","anguilla.json":"Anguilla","antigua_and_barbuda.json":"Antigua and Barbuda","argentina.json":"Argentina","armenia.json":"Armenia","aruba.json":"Aruba","australia.json":"Australia","austria.json":"Austria","azerbaijan.json":"Azerbaijan","bahamas.json":"The Bahamas","bahrain.json":"Bahrain","bangladesh.json":"Bangladesh","barbados.json":"Barbados","belarus.json":"Belarus","belgium.json":"Belgium","belize.json":"Belize","benin.json":"Benin","bermuda.json":"Bermuda","bhutan.json":"Bhutan","bolivia.json":"Bolivia","bosnia_and_herzegovina.json":"Bosnia and Herzegovina","botswana.json":"Botswana","brazil.json":"Brazil","british_virgin_islands.json":"British Indian Ocean Territory","brunei.json":"Brunei","bulgaria.json":"Bulgaria","burkina_faso.json":"Burkina Faso","burundi.json":"Burundi","cambodia.json":"Cambodia","cameroon.json":"Cameroon","canada.json":"Canada","cape_verde.json":"Cape Verde","cayman_islands.json":"Cayman Islands","central_african_republic.json":"Central African Republic","chad.json":"Chad","chile.json":"Chile","china.json":"China","christmas_island.json":"Christmas Island","cocos_keeling_islands.json":"Cocos (Keeling) Islands","colombia.json":"Colombia","comoros.json":"Comoros","congo_democratic_republic_of_the.json":"Republic of the Congo","congo_republic_of_the.json":"Democratic Republic of the Congo","cook_islands.json":"Cook Islands","costa_rica.json":"Costa Rica","cote_d_ivoire.json":"Ivory Coast","croatia.json":"Croatia","cuba.json":"Cuba","cyprus.json":"Cyprus","czeck_republic.json":"Czech Republic","denmark.json":"Denmark","djibouti.json":"Djibouti","dominica.json":"Dominica","dominican_republic.json":"Dominican Republic","ecuador.json":"Ecuador","egypt.json":"Egypt","el_salvador.json":"El Salvador","equatorial_guinea.json":"Equatorial Guinea","eritrea.json":"Eritrea","estonia.json":"Estonia","ethiopia.json":"Ethiopia","falkland_islands_islas_malvinas.json":"Falkland Islands","faroe_islands.json":"Faroe Islands","fiji.json":"Fiji","finland.json":"Finland","france.json":"France","french_guiana.json":"French Guiana","french_polynesia.json":"French Polynesia","french_southern_and_antarctic_lands.json":"French Southern and Antarctic Lands","gabon.json":"Gabon","gambia_the.json":"The Gambia","georgia.json":"Georgia","germany.json":"Germany","ghana.json":"Ghana","gibraltar.json":"Gibraltar","greece.json":"Greece","greenland.json":"Greenland","grenada.json":"Grenada","guadeloupe.json":"Guadeloupe","guam.json":"Guam","guatemala.json":"Guatemala","guernsey.json":"Guernsey","guinea.json":"Guinea","guinea_bissau.json":"Guinea-Bissau","guyana.json":"Guyana","haiti.json":"Haiti","heard_island_and_mc_donald_islands.json":"Heard Island and McDonald Islands","honduras.json":"Honduras","hong_kong.json":"Hong Kong","howland_island.json":"Hungary","iceland.json":"Iceland","india.json":"India","indonesia.json":"Indonesia","iran.json":"Iran","iraq.json":"Iraq","ireland.json":"Ireland","israel.json":"Israel","italy.json":"Italy","jamaica.json":"Jamaica","japan.json":"Japan","jersey.json":"Jersey","jordan.json":"Jordan","kazakhstan.json":"Kazakhstan","kenya.json":"Kenya","kiribati.json":"Kiribati","korea_north.json":"North Korea","korea_south.json":"South Korea","kuwait.json":"Kuwait","kyrgyzstan.json":"Kyrgyzstan","laos.json":"Laos","latvia.json":"Latvia","lebanon.json":"Lebanon","lesotho.json":"Lesotho","liberia.json":"Liberia","libya.json":"Libya","liechtenstein.json":"Liechtenstein","lithuania.json":"Lithuania","luxembourg.json":"Luxembourg","macau.json":"Macau","macedonia_former_yugoslav_republic_of.json":"Republic of Macedonia","madagascar.json":"Madagascar","malawi.json":"Malawi","malaysia.json":"Malaysia","maldives.json":"Maldives","mali.json":"Mali","malta.json":"Malta","man_isle_of.json":"Isle of Man","marshall_islands.json":"Marshall Islands","martinique.json":"Martinique","mauritania.json":"Mauritania","mauritius.json":"Mauritius","mayotte.json":"Mayotte","mexico.json":"Mexico","micronesia_federated_states_of.json":"Federated States of Micronesia","moldova.json":"Moldova","monaco.json":"Monaco","mongolia.json":"Mongolia","montserrat.json":"Montserrat","morocco.json":"Morocco","mozambique.json":"Mozambique","namibia.json":"Namibia","nauru.json":"Nauru","nepal.json":"Nepal","netherlands.json":"Netherlands","new_caledonia.json":"New Caledonia","new_zealand.json":"New Zealand","nicaragua.json":"Nicaragua","niger.json":"Niger","nigeria.json":"Nigeria","niue.json":"Niue","norfolk_island.json":"Norfolk Island","northern_mariana_islands.json":"Northern Mariana Islands","norway.json":"Norway","oman.json":"Oman","pakistan.json":"Pakistan","palau.json":"Palau","panama.json":"Panama","papua_new_guinea.json":"Papua New Guinea","paraguay.json":"Paraguay","peru.json":"Peru","philippines.json":"Philippines","pitcaim_islands.json":"Pitcairn Islands","poland.json":"Poland","portugal.json":"Portugal","puerto_rico.json":"Puerto Rico","qatar.json":"Qatar","reunion.json":"R\u00e9union","romainia.json":"Romania","russia.json":"Russia","rwanda.json":"Rwanda","saint_helena.json":"Saint Helena","saint_kitts_and_nevis.json":"Saint Kitts and Nevis","saint_lucia.json":"Saint Lucia","saint_pierre_and_miquelon.json":"Saint Pierre and Miquelon","saint_vincent_and_the_grenadines.json":"Saint Vincent and the Grenadines","samoa.json":"Samoa","san_marino.json":"San Marino","sao_tome_and_principe.json":"S\u00e3o Tom\u00e9 and Pr\u00edncipe","saudi_arabia.json":"Saudi Arabia","scotland.json":"Scotland","senegal.json":"Senegal","seychelles.json":"Seychelles","sierra_leone.json":"Sierra Leone","singapore.json":"Singapore","slovakia.json":"Slovakia","slovenia.json":"Slovenia","solomon_islands.json":"Solomon Islands","somalia.json":"Somalia","south_africa.json":"South Africa","south_georgia_and_south_sandwich_islands.json":"South Georgia","south_sudan.json":"South Sudan","spain.json":"Spain","sri_lanka.json":"Sri Lanka","sudan.json":"Sudan","suriname.json":"Suriname","svalbard.json":"Svalbard and Jan Mayen","swaziland.json":"Swaziland","sweden.json":"Sweden","switzerland.json":"Switzerland","syria.json":"Syria","taiwan.json":"Taiwan","tajikistan.json":"Tajikistan","tanzania.json":"Tanzania","thailand.json":"Thailand","tobago.json":"East Timor","toga.json":"Togo","tokelau.json":"Tokelau","tonga.json":"Tonga","trinidad.json":"Trinidad and Tobago","tunisia.json":"Tunisia","turkey.json":"Turkey","turkmenistan.json":"Turkmenistan","tuvalu.json":"Tuvalu","uganda.json":"Uganda","ukraine.json":"Ukraine","united_arab_emirates.json":"United Arab Emirates","united_kingdom.json":"United Kingdom","united_states_of_america.json":"United States","uruguay.json":"Uruguay","uzbekistan.json":"Uzbekistan","vanuatu.json":"Vanuatu","venezuela.json":"Venezuela","vietnam.json":"Vietnam","wales.json":"Wales","wallis_and_futuna.json":"Wallis and Futuna","western_sahara.json":"Western Sahara","yemen.json":"Yemen","zambia.json":"Zambia","zimbabwe.json":"Zimbabwe"}', ARRAY_A );
	}


	/*==========================================================================
	 * RichText Init - Adds the filters
	 *========================================================================*/
	public static function richtext_init() {
		add_filter( 'ipt_uif_richtext', array( __CLASS__, 'richtext_filter' ), 8 );
	}

	public static function richtext_filter( $content ) {
		global $shortcode_tags, $ipt_fsqm_settings;
		$original_shortcode_tags = $shortcode_tags;
		$shortcodes_to_remove = array();
		$shortcodes_to_remove = array(
			'ipt_fsqm_form', 'ipt_fsqm_trackback', 'ipt_fsqm_utrackback', 'ipt_fsqm_trends',
		);
		$shortcode_tags = array();
		foreach ( $shortcodes_to_remove as $key ) {
			$shortcode_tags[$key] = 1;
		}
		$content = strip_shortcodes( $content );
		$shortcode_tags = $original_shortcode_tags;
		return $content;
	}

	/*==========================================================================
	 * UIF Label Adding Latex Support
	 *========================================================================*/
	public static function uif_label_latex_init() {
		// Add the generic function
		// But add in when all plugins are loaded
		add_action( 'wp_loaded', array( __CLASS__, 'uif_label_latex_markup' ) );
		// Add the shortcode
		add_filter( 'ipt_uif_label', array( __CLASS__, 'uif_label_latex_filter' ) );
	}

	public static function uif_label_latex_markup() {
		// Add the generic function
		if ( function_exists( 'latex_markup' ) ) {
			add_filter( 'ipt_uif_richtext', 'latex_markup', 9 );
			add_filter( 'ipt_uif_label', 'latex_markup' );
		}
	}
	public static function uif_label_latex_filter( $content ) {
		if ( shortcode_exists( 'latex' ) ) {
			global $shortcode_tags;
			$original_shortcode_tags = $shortcode_tags;
			$shortcode_tags = array();
			foreach ( $original_shortcode_tags as $o_key => $o_val ) {
				if ( $o_key == 'latex' ) {
					$shortcode_tags[$o_key] = $o_val;
				}
			}
			$content = do_shortcode( $content );
			$shortcode_tags = $original_shortcode_tags;
			$content = strip_shortcodes( $content );
		}
		return $content;
	}



	/*==========================================================================
	 * Uploader Callbacks
	 *========================================================================*/
	public static function uploader_ajax_init() {
		add_action( 'wp_ajax_ipt_fsqm_fu_upload', array( __CLASS__, 'uploader_ajax_upload' ) );
		add_action( 'wp_ajax_ipt_fsqm_fu_download', array( __CLASS__, 'uploader_ajax_download' ) );
		add_action( 'wp_ajax_ipt_fsqm_fu_delete', array( __CLASS__, 'uploader_ajax_delete' ) );

		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_upload', array( __CLASS__, 'uploader_ajax_upload' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_download', array( __CLASS__, 'uploader_ajax_download' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_fu_delete', array( __CLASS__, 'uploader_ajax_delete' ) );
	}
	public static function uploader_ajax_upload() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$nonce = @$_POST['nonce'];

		$form_id = (int) @$_POST['form_id'];
		$element_id = (int) @$_POST['element_key'];
		$data_id = ( 'null' == @$_POST['data_id'] || '' == @$_POST['data_id'] || 0 == @$_POST['data_id'] ) ? null : (int) @$_POST['data_id'];
		$files_key = @$_POST['files_key'];

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_upload_' . $form_id . '_' . $data_id . '_' . $element_id ) ) {
			$return = array(
				'files' => array(
					array(
						'name' => __( 'Invalid', 'ipt_fsqm' ),
						'size' => 0,
						'error' => __( 'Invalid nonce.', 'ipt_fsqm' ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );
		$return = array(
			'files' => $upload_handler->process_file_uploads( $files_key ),
		);
		echo json_encode( (object) $return );

		wp_create_nonce( 'ipt_fsqm_upload_' . $form_id . '_' . $data_id . '_' . $element_id );

		die();
	}
	public static function uploader_ajax_download() {
		$nonce = @$_GET['download_nonce'];
		$data_id = (int) @$_GET['data_id'];
		$form_id = (int) @$_GET['form_id'];
		$element_id = (int) @$_GET['element_key'];

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_download_' . $form_id . '_' . $data_id . '_' . $element_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );
		$uploads = $upload_handler->get_uploads( $data_id, true );
		$formatted_uploads = array();
		foreach ( (array) $uploads as $upload ) {
			$valid_audio = in_array( strtolower( $upload['ext'] ), array( 'mp3', 'wav', 'ogg' ) ) ? true : false;
			$valid_video = strtolower( $upload['ext'] ) == 'mp4' ? true : false;
			$formatted_uploads[] = array(
				'id' => $upload['id'],
				'name' => $upload['filename'],
				'size' => $upload['size'],
				'url' => $upload['guid'],
				'thumbnailUrl' => $upload['thumb_url'],
				'deleteUrl' => $upload['delete'],
				'deleteType' => 'DELETE',
				'validAudio' => $valid_audio,
				'validVideo' => $valid_video,
				'type' => $upload['mime_type'],
			);
		}
		$return = array(
			'files' => $formatted_uploads,
		);
		echo json_encode( (object) $return );
		die();
	}

	public static function uploader_ajax_delete() {
		if ( $_SERVER['REQUEST_METHOD'] !== 'DELETE' ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$file_id = (int) @$_GET['file_id'];
		$element_id = (int) @$_GET['element_id'];
		$form_id = (int) @$_GET['form_id'];
		$wpnonce = @$_GET['_wpnonce'];
		$file = @$_GET['file'];

		if ( ! wp_verify_nonce( $wpnonce, 'ipt_fsqm_fu_delete_file_' . $file_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$upload_handler = new IPT_FSQM_Form_Elements_Uploader( $form_id, $element_id );

		$return = array(
			$file => $upload_handler->delete_file( $file_id ),
		);

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Coupons Callbacks
	 *========================================================================*/
	public static function coupon_ajax_init() {
		add_action( 'wp_ajax_ipt_fsqm_validate_coupon', array( __CLASS__, 'coupon_ajax_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_validate_coupon', array( __CLASS__, 'coupon_ajax_cb' ) );
	}

	public static function coupon_ajax_cb() {
		$form_id = @$_REQUEST['form_id'];
		$nonce = @$_REQUEST['_wpnonce'];
		$coupon = @$_REQUEST['coupon'];
		$amount = @$_REQUEST['amount'];

		$return = array(
			'success' => true,
			'msg' => '',
		);
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_coupon_' . $form_id ) ) {
			$return['success'] = false;
			$return['msg'] = __( 'Cheatin&#8217; uh?' );

			echo json_encode( (object) $return );
			die();
		}

		// Nonce validated
		// Now check if coupon code exists
		$form = new IPT_FSQM_Form_Elements_Base( $form_id );
		$coupons = array();
		if ( ! empty( $form->settings['payment']['coupons'] ) ) {
			foreach ( $form->settings['payment']['coupons'] as $c_key => $c ) {
				$coupons[$c_key] = $c['code'];
			}
		}

		if ( ! in_array( $coupon, $coupons ) ) {
			$return['success'] = false;
			$return['msg'] = __( 'Invalid coupon code', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}

		// Coupon code in the array
		$coupon_key = array_search( $coupon, $coupons );
		$coupon_config = $form->settings['payment']['coupons'][$coupon_key];

		// Now check if minimum value is satisfied
		if ( $amount < $coupon_config['min'] ) {
			$return['success'] = false;
			$return['msg'] = __( 'Coupon not applicable for this amount', 'ipt_fsqm' );
			echo json_encode( (object) $return );
			die();
		}

		// Calculate the new formula
		$old_formula = $new_formula = $form->settings['payment']['formula'];

		if ( $coupon_config['type'] == 'percentage' ) {
			$new_formula = '(' . $old_formula . ')*' . ( ( 100 - $coupon_config['value'] ) / 100 );
			$return['msg'] = sprintf( __( '%1$s%% Discount', 'ipt_fsqm' ), $coupon_config['value'] );
		} else {
			$new_formula = '(' . $old_formula . ')-' . $coupon_config['value'];
			$return['msg'] = sprintf( __( '%1$s %2$s Discount', 'ipt_fsqm' ), $coupon_config['value'], $form->settings['payment']['currency'] );
		}
		$return['formula'] = $new_formula;
		echo json_encode( (object) $return );
		die();
	}



	/*==========================================================================
	 * Shortcode to WP Editor APIs
	 *========================================================================*/
	public static function fsqm_mce_icons() {
		// Add icon when tinymce is loaded
		wp_register_style( 'ipt-icomoon-fonts', plugins_url( '/lib/fonts/icomoon.css', IPT_FSQM_Loader::$abs_file ), array(), IPT_FSQM_Loader::$version );
		// manually print style because it would be too late anyway
		wp_print_styles( 'ipt-icomoon-fonts' );
	}

	public static function fsqm_mce_extendor() {
		// wp_enqueue_style( 'ipt-icomoon-fonts', plugins_url( '/lib/fonts/icomoon.css', IPT_FSQM_Loader::$abs_file ), array(), IPT_FSQM_Loader::$version );
		// Charting variables
		$chart_helper = self::get_chart_type_n_toggles();
		// Change it a little for using with JSON
		// We need to keep the order for defaulting
		$chart_maps = $chart_helper['possible_chart_types'];
		foreach ( $chart_maps as $etype => $echarts ) {
			$chart_helper['possible_chart_types'][ $etype ]['default'] = current( array_keys( $echarts ) );
		}
		$report_type = array(
			array(
				'text' => __( 'Survey (MCQ) Elements', 'ipt_fsqm' ),
				'value' => 'mcq',
				'checked' => true,
			),
			array(
				'text' => __( 'Feedback & Upload Elements', 'ipt_fsqm' ),
				'value' => 'freetype',
				'checked' => true,
			),
			array(
				'text' => __( 'Other Elements', 'ipt_fsqm' ),
				'value' => 'pinfo',
				'checked' => true,
			),
		);
		$report_data = array(
			array(
				'text' => __( 'Show data alongside graphs for mcqs', 'ipt_fsqm' ),
				'value' => 'data',
				'checked' => true,
			),
			array(
				'text' => __( 'Show optional meta entries for mcqs', 'ipt_fsqm' ),
				'value' => 'others',
				'checked' => true,
			),
			array(
				'text' => __( 'Show names for mcq meta entries and feedbacks', 'ipt_fsqm' ),
				'value' => 'names',
				'checked' => true,
			),
			array(
				'text' => __( 'Show date for mcq meta entries and feedbacks', 'ipt_fsqm' ),
				'value' => 'date',
				'checked' => true,
			),
		);
		$report_appearance = array(
			array(
				'text' => __( 'Wrap inside blocks', 'ipt_fsqm' ),
				'value' => 'block',
				'checked' => true,
			),
			array(
				'text' => __( 'Show Element Heading (Shown anyway if Wrap inside blocks is active)', 'ipt_fsqm' ),
				'value' => 'heading',
				'checked' => true,
			),
			array(
				'text' => __( 'Show element Description', 'ipt_fsqm' ),
				'value' => 'description',
				'checked' => true,
			),
			array(
				'text' => __( 'Show table header', 'ipt_fsqm' ),
				'value' => 'header',
				'checked' => true,
			),
			array(
				'text' => __( 'Show table border', 'ipt_fsqm' ),
				'value' => 'border',
				'checked' => true,
			),
			array(
				'text' => __( 'Use Google Material Charts instead of Classic Charts (for Bar & Column charts only)', 'ipt_fsqm' ),
				'value' => 'material',
				'checked' => false,
			),
			array(
				'text' => __( 'Show the print button', 'ipt_fsqm' ),
				'value' => 'print',
				'checked' => false,
			),
		);

		wp_enqueue_script( 'fsqm_mce_extendor', plugins_url( '/static/admin/js/ipt-fsqm-tinymce-extendor.min.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_Loader::$version );
		wp_localize_script( 'fsqm_mce_extendor', 'iptFSQMTML10n', array(
			'l10n' => array(
				'label' => __( 'Insert Shortcodes for eForm', 'ipt_fsqm' ),
				'slabel' => __( 'eForm - ', 'ipt_fsqm' ),
				'salabel' => __( 'Select Questions', 'ipt_fsqm' ),
				'fselect' => __( 'Please select a form', 'ipt_fsqm' ),
				'ajax' => __( 'Please wait. Press OK to exit!', 'ipt_fsqm' ),
				'ss' => array(
					'ss' => __( 'System Shortcodes', 'ipt_fsqm' ),
					'up' => __( 'Centralized User Portal Page', 'ipt_fsqm' ),
					'tb' => __( 'Single Submission Trackback', 'ipt_fsqm' ),
					'tbfl' => __( 'Form Label', 'ipt_fsqm' ),
					'tbfll' => __( 'Track Code', 'ipt_fsqm' ),
					'tbfltt' => __( 'Enter the label of the text input where the surveyee will need to paste his/her trackback code.', 'ipt_fsqm' ),
					'tbsbtl' => __( 'Submit Button Text', 'ipt_fsqm' ),
					'tbsbt' => __( 'Submit', 'ipt_fsqm' ),
					'uplabels' => array(
						'llogin_attr' => __( 'Login Page Modifications', 'ipt_fsqm' ),
						'lportal_attr' => __( 'Portal Page Modifications', 'ipt_fsqm' ),
						'login_attr' => array(
							'login' => __( 'Message to logged out users', 'ipt_fsqm' ),
							'show_register' => __( 'Show the registration button', 'ipt_fsqm' ),
							'show_forgot' => __( 'Show password recovery link', 'ipt_fsqm' ),
						),
						'portal_attr' => array(
							'title' => __( 'Welcome Title', 'ipt_fsqm' ),
							'content' => __( 'Welcome message', 'ipt_fsqm' ),
							'contenttt' => __( '%NAME% will be replaced by user name', 'ipt_fsqm' ),
							'nosubmission' => __( 'No submissions message', 'ipt_fsqm' ),
							'formlabel' => __( 'Form Heading Label', 'ipt_fsqm' ),
							'filters' => __( 'Show Filters for Forms and Categories', 'ipt_fsqm' ),
							'filterstt' => __( 'If enabled then users would be able to select forms and categories from dropdown and enter date/time range.', 'ipt_fsqm' ),
							'showcategory' => __( 'Show Category', 'ipt_fsqm' ),
							'categorylabel' => __( 'Category Label', 'ipt_fsqm' ),
							'datelabel' => __( 'Date Heading Label', 'ipt_fsqm' ),
							'showscore' => __( 'Show Score Column', 'ipt_fsqm' ),
							'showscorett' => __( 'If enabled then score obtained, total score and percentage would be shown for relevant forms.', 'ipt_fsqm' ),
							'scorelabel' => __( 'Score Heading Label', 'ipt_fsqm' ),
							'mscorelabel' => __( 'Max Score Heading Label', 'ipt_fsqm' ),
							'pscorelabel' => __( 'Percentage Score Heading Label', 'ipt_fsqm' ),
							'showremarks' => __( 'Show Admin Remarks Column', 'ipt_fsqm' ),
							'showremarkstt' => __( 'If enabled, then administrator remarks will be shown in a column.', 'ipt_fsqm' ),
							'remarkslabel' => __( 'Admin Remarks Label', 'ipt_fsqm' ),
							'actionlabel' => __( 'Action Column Heading Label', 'ipt_fsqm' ),
							'linklabel' => __( 'Trackback Button Label', 'ipt_fsqm' ),
							'editlabel' => __( 'Edit Button Label', 'ipt_fsqm' ),
							'avatar' => __( 'Avatar Size', 'ipt_fsqm' ),
							'theme' => __( 'Portal Theme', 'ipt_fsqm' ),
							'logout_r' => __( 'Redirection after Logout', 'ipt_fsqm' ),
							'logout_r_tt' => __( 'Any valid URL starting with http:// or https://', 'ipt_fsqm' ),
						),
					),
					'updefaults' => array(
						'content' => __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' ),
						'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
						'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
						'formlabel' => __( 'Form', 'ipt_fsqm' ),
						'categorylabel' => __( 'Category', 'ipt_fsqm' ),
						'datelabel' => __( 'Date', 'ipt_fsqm' ),
						'scorelabel' => __( 'Score', 'ipt_fsqm' ),
						'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
						'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
						'remarkslabel' => __( 'Remarks', 'ipt_fsqm' ),
						'linklabel' => __( 'View', 'ipt_fsqm' ),
						'actionlabel' => __( 'Action', 'ipt_fsqm' ),
						'editlabel' => __( 'Edit', 'ipt_fsqm' ),
						'avatar' => '96',
						'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
						'logout_r' => '',
					),
					'login' => array(
						'lb' => __( 'Login Form', 'ipt_fsqm' ),
						'rd' => __( 'Redirect To (empty for current URL)', 'ipt_fsqm' ),
						'rg' => __( 'Show Registration', 'ipt_fsqm' ),
						'rgtt' => __( 'If checked then the form will have a Registration button', 'ipt_fsqm' ),
						'rgurl' => __( 'Registration URL (empty for default)', 'ipt_fsqm' ),
						'fg' => __( 'Show Forgot Password', 'ipt_fsqm' ),
						'fgtt' => __( 'If checked then the form will have a Forgot Password button', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'msg' => __( 'Form Heading', 'ipt_fsqm' ),
						'msgdf' => __( 'Please login to our site', 'ipt_fsqm' ),
					),
				),
				'lb' => array(
					'lb' => __( 'Insert Leaderboard', 'ipt_fsqm' ),
					'flb' => __( 'Form Leaderboard', 'ipt_fsqm' ),

					'flba' => __( 'Appearance Options', 'ipt_fsqm' ),

					'flbaa' => __( 'Show Avatar', 'ipt_fsqm' ),
					'flbaas' => __( 'Avatar Size', 'ipt_fsqm' ),
					'flban' => __( 'Show Name', 'ipt_fsqm' ),
					'flbad' => __( 'Show Date', 'ipt_fsqm' ),
					'flbas' => __( 'Show Score', 'ipt_fsqm' ),
					'flbams' => __( 'Show Max Score', 'ipt_fsqm' ),
					'flbap' => __( 'Show Percentage', 'ipt_fsqm' ),
					'flbac' => __( 'Show Administrator Comment', 'ipt_fsqm' ),
					'flbah' => __( 'Show form name as heading', 'ipt_fsqm' ),
					'flbai' => __( 'Show form header image', 'ipt_fsqm' ),
					'flbam' => __( 'Show User Meta in table', 'ipt_fsqm' ),

					'flbl' => __( 'Labels', 'ipt_fsqm' ),

					'flblvname'       => __( 'Name', 'ipt_fsqm' ),
					'flblvdate'       => __( 'Date', 'ipt_fsqm' ),
					'flblvscore'      => __( 'Score', 'ipt_fsqm' ),
					'flblvmax_score'  => __( 'Out of', 'ipt_fsqm' ),
					'flblvpercentage' => __( 'Percentage', 'ipt_fsqm' ),
					'flblvcomment'    => __( 'Remarks', 'ipt_fsqm' ),

					'flblname'       => __( 'Name Column', 'ipt_fsqm' ),
					'flbldate'       => __( 'Date Column', 'ipt_fsqm' ),
					'flblscore'      => __( 'Score Column', 'ipt_fsqm' ),
					'flblmax_score'  => __( 'Max Score Column', 'ipt_fsqm' ),
					'flblpercentage' => __( 'Percentage Column', 'ipt_fsqm' ),
					'flblcomment'    => __( 'Administrator Comment Column', 'ipt_fsqm' ),
					'flblcontent'    => __( 'Content', 'ipt_fsqm' ),
				),
				'st' => array(
					'st' => __( 'Insert Statistics Charts', 'ipt_fsqm' ),

					'stfs' => __( 'Form Statistics', 'ipt_fsqm' ),
					'stus' => __( 'User Statistics', 'ipt_fsqm' ),

					'fssb' => __( 'Form Submission Breakdown', 'ipt_fsqm' ),
					'fssbtt' => __( 'Combo bar chart to show submissions per day per form.', 'ipt_fsqm' ),
					'fssb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'totalline' => __( 'Total line title', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'fssb_df' => array(
						'form_ids' => 'all',
						'days' => '7',
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'totalline' => __( 'Total Submissions', 'ipt_fsqm' ),
						'xlabel' => __( 'Date', 'ipt_fsqm' ),
						'ylabel' => __( 'Submissions', 'ipt_fsqm' ),
						'height' => '700',
						'width' => '1920',
					),
					'fssb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'totalline' => __( 'If you wish to show a total line in the graph then enter the title. If empty, then total line would not be shown.', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'fsos' => __( 'Overall Submissions', 'ipt_fsqm' ),
					'fsostt' => __( 'Pie or Doughnut chart to show overall submissions per form.', 'ipt_fsqm' ),
					'fsos_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'fsos_df' => array(
						'form_ids' => 'all',
						'days' => '7', // Can work both as days or since
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'fsos_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'fscb' => __( 'Score Breakdown', 'ipt_fsqm' ),
					'fscbtt' => __( 'Pie or Doughnut chart to show score percentage breakdown for selected form.', 'ipt_fsqm' ),
					'fscb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'label' => __( 'Graph Legend Format', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'fscb_df' => array(
						'form_ids' => 'all',
						'label' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
						'days' => '',
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'fscb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'label' => __( 'Enter legend format of the score breakdown. From %d%% to %d%% will be replaced by From 0% to 9%, From 10% to 19% etc. It takes formatting of PHP sprintf. So %1$d will be replaced by min score and %2$d will be replaced by max score, use them if you want to change the scoring order in the labels.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'ussb' => __( 'Submission Breakdown', 'ipt_fsqm' ),
					'ussbtt' => __( 'Combo bar chart to show submissions breakdown for selected or current user per day per form.', 'ipt_fsqm' ),
					'ussb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'user_id' => __( 'User ID', 'ipt_fsqm' ),
						'show_login' => __( 'Show Login Form', 'ipt_fsqm' ),
						'login_msg' => __( 'Login Message', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'totalline' => __( 'Total line title', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'ussb_df' => array(
						'form_ids' => 'all',
						'user_id' => 'current',
						'show_login' => '1',
						'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
						'theme' => 'material-default',
						'days' => '7',
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'totalline' => __( 'Total Submissions', 'ipt_fsqm' ),
						'xlabel' => __( 'Date', 'ipt_fsqm' ),
						'ylabel' => __( 'Submissions', 'ipt_fsqm' ),
						'height' => '700',
						'width' => '1920',
					),
					'ussb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'user_id' => __( 'ID of the user for which you want to show stat. Enter current to show for currently logged in user.', 'ipt_fsqm' ),
						'show_login' => __( 'If set to checked and if the stat is for currently logged in user, then a login form would be shown if user is not logged in already.', 'ipt_fsqm' ),
						'login_msg' => __( 'The login form heading. Keep it short.', 'ipt_fsqm' ),
						'theme' => __( 'Login form theme. Please select from the presets.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'totalline' => __( 'If you wish to show a total line in the graph then enter the title. If empty, then total line would not be shown.', 'ipt_fsqm' ),
						'xlabel' => __( 'X Axis Label', 'ipt_fsqm' ),
						'ylabel' => __( 'Y Axis Label', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'usob' => __( 'Overall Submissions', 'ipt_fsqm' ),
					'usobtt' => __( 'Pie or Doughnut chart to show overall submissions for selected or current user for selected forms.', 'ipt_fsqm' ),
					'usob_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'user_id' => __( 'User ID', 'ipt_fsqm' ),
						'show_login' => __( 'Show Login Form', 'ipt_fsqm' ),
						'login_msg' => __( 'Login Message', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'days' => __( 'Number of days or date', 'ipt_fsqm' ),
						'max' => __( 'Max Number of forms to show before grouping', 'ipt_fsqm' ),
						'others' => __( 'Grouping Label', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'usob_df' => array(
						'form_ids' => 'all',
						'user_id' => 'current',
						'show_login' => '1',
						'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
						'theme' => 'material-default',
						'days' => '',
						'max' => '0',
						'others' => __( 'Others', 'ipt_fsqm' ),
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'usob_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'user_id' => __( 'ID of the user for which you want to show stat. Enter current to show for currently logged in user.', 'ipt_fsqm' ),
						'show_login' => __( 'If set to checked and if the stat is for currently logged in user, then a login form would be shown if user is not logged in already.', 'ipt_fsqm' ),
						'login_msg' => __( 'The login form heading. Keep it short.', 'ipt_fsqm' ),
						'theme' => __( 'Login form theme. Please select from the presets.', 'ipt_fsqm' ),
						'days' => __( 'Either enter date in Y-m-d (2016-12-30) format to show since mentioned date. Or enter number of days, like 20, to show for past 20 days. Leave blank to show for all time.', 'ipt_fsqm' ),
						'max' => __( 'For rather large number of forms, it is advised to group forms with smaller scales into "Others". Mention the maximum number of forms (exclusive) the system will count before grouping others. Leave empty or 0 to disable.', 'ipt_fsqm' ),
						'others' => __( 'Enter the label of the others grouping.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),


					'usscb' => __( 'Score Breakdown', 'ipt_fsqm' ),
					'usscbtt' => __( 'Pie or Doughnut chart to show score percentage breakdown for selected forms and selected or current user.', 'ipt_fsqm' ),
					'usscb_lbs' => array(
						'form_ids' => __( 'Form IDs', 'ipt_fsqm' ),
						'user_id' => __( 'User ID', 'ipt_fsqm' ),
						'show_login' => __( 'Show Login Form', 'ipt_fsqm' ),
						'login_msg' => __( 'Login Message', 'ipt_fsqm' ),
						'theme' => __( 'Login Form Theme', 'ipt_fsqm' ),
						'label' => __( 'Graph Legend Format', 'ipt_fsqm' ),
						'type' => __( 'Type of Graph', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),
					'usscb_df' => array(
						'form_ids' => 'all',
						'user_id' => 'current',
						'show_login' => '1',
						'login_msg' => __( 'Please login to view statistics', 'ipt_fsqm' ),
						'theme' => 'material-default',
						'label' => __( 'From %1$d%% to %2$d%% ', 'ipt_fsqm' ),
						'type' => 'pie', // Can be pie, doughnut
						'height' => '400',
						'width' => '600',
					),
					'usscb_tts' => array(
						'form_ids' => __( 'Enter "all" to show all forms. Or Comma separated values like "1,30,51" etc.', 'ipt_fsqm' ),
						'user_id' => __( 'ID of the user for which you want to show stat. Enter current to show for currently logged in user.', 'ipt_fsqm' ),
						'show_login' => __( 'If set to checked and if the stat is for currently logged in user, then a login form would be shown if user is not logged in already.', 'ipt_fsqm' ),
						'login_msg' => __( 'The login form heading. Keep it short.', 'ipt_fsqm' ),
						'theme' => __( 'Login form theme. Please select from the presets.', 'ipt_fsqm' ),
						'label' => __( 'Enter legend format of the score breakdown. From %d%% to %d%% will be replaced by From 0% to 9%, From 10% to 19% etc. It takes formatting of PHP sprintf. So %1$d will be replaced by min score and %2$d will be replaced by max score, use them if you want to change the scoring order in the labels.', 'ipt_fsqm' ),
						'type' => __( 'Type of chart to draw', 'ipt_fsqm' ),
						'height' => __( 'Graph Height (px)', 'ipt_fsqm' ),
						'width' => __( 'Graph Width (px)', 'ipt_fsqm' ),
					),

					'charts' => array(
						0 => array(
							'text' => __( 'Pie Chart', 'ipt_fsqm' ),
							'value' => 'pie',
						),
						1 => array(
							'text' => __( 'Doughnut Chart', 'ipt_fsqm' ),
							'value' => 'doughnut',
						),
					),
				),
				'if' => __( 'Insert Form', 'ipt_fsqm' ),
				'ifl' => __( 'Select Form', 'ipt_fsqm' ),
				'it' => __( 'Insert Trends', 'ipt_fsqm' ),
				'itvc' => __( 'Title of the Visualization Column', 'ipt_fsqm' ),
				'itvcv' => __( 'Trends', 'ipt_fsqm' ),
				'itvsl' => __( 'Server Load', 'ipt_fsqm' ),
				'itvsllb' => array(
					array(
						'text' => __( 'Light Load: 15 queries per hit', 'ipt_fsqm' ),
						'value' => '0',
					),
					array(
						'text' => __( 'Medium Load: 30 queries per hit (Recommended)', 'ipt_fsqm' ),
						'value' => '1',
						'selected' => true,
					),
					array(
						'text' => __( 'Heavy Load: 50 queries per hit', 'ipt_fsqm' ),
						'value' => '2',
					),
				),
				'pf' => __( 'Insert Popup Forms', 'ipt_fsqm' ),
				'pfbt' => __( 'Button Text', 'ipt_fsqm' ),
				'pfbtl' => __( 'Contact Form', 'ipt_fsqm' ),
				'pfbc' => __( 'Button Color', 'ipt_fsqm' ),
				'pfbbc' => __( 'Button Background Color', 'ipt_fsqm' ),
				'pfbp' => __( 'Button Position', 'ipt_fsqm' ),
				'pfbplb' => array(
					array(
						'text' => __( 'Right', 'ipt_fsqm' ),
						'value' => 'r',
					),
					array(
						'text' => __( 'Bottom Right', 'ipt_fsqm' ),
						'value' => 'br',
						'selected' => true,
					),
					array(
						'text' => __( 'Bottom Center', 'ipt_fsqm' ),
						'value' => 'bc',
					),
					array(
						'text' => __( 'Bottom Left', 'ipt_fsqm' ),
						'value' => 'bl',
					),
					array(
						'text' => __( 'Left', 'ipt_fsqm' ),
						'value' => 'l',
					),
					array(
						'text' => __( 'Hidden / Manual Trigger' ),
						'value' => 'h',
					),
				),
				'pfbs' => __( 'Button Style', 'ipt_fsqm' ),
				'pfbslb' => array(
					array(
						'text' => __( 'Rectangular', 'ipt_fsqm' ),
						'value' => 'rect',
					),
					array(
						'text' => __( 'Circular', 'ipt_fsqm' ),
						'value' => 'circ',
					),
				),
				'pfbheader' => __( 'Popup Header Title, %FORM% is form name', 'ipt_fsqm' ), // form name
				'pfbsubtitle' => __( 'Popup Header Subtitle', 'ipt_fsqm' ), // Some subtitle
				'pfbicon' => __( 'Popup Button Icon class', 'ipt_fsqm' ), // icon class needs to be supplied by vendor
				'pfbwidth' => __( 'Initial Popup Width', 'ipt_fsqm' ), // Initial popup width in pixels
				'pfpv' => __( 'Button Preview', 'ipt_fsqm' ),
				'pfmt' => __( 'Since you have chosen manual trigger, you need to insert the following code somewhere in the post. Do you want to insert automatically?', 'ipt_fsqm' ),
				'twb1' => array(
					'rt' => __( 'Report Type', 'ipt_fsqm' ),
					'rdc' => __( 'Report Data Customizations', 'ipt_fsqm' ),
					'ra' => __( 'Report Appearance', 'ipt_fsqm' ),
				),
				'twb2' => array(
					'sm' => __( 'Select the Multiple Choice Type Questions', 'ipt_fsqm' ),
					'sf' => __( 'Select the Feedback Type Questions', 'ipt_fsqm' ),
					'sp' => __( 'Select the Other Form Elements', 'ipt_fsqm' ),
					'sct' => __( 'Chart Type', 'ipt_fsqm' ),
					'sctl' => __( 'Show Chart Title', 'ipt_fsqm' ),
					'scl' => __( 'Show Chart Legend and Axis Ticks', 'ipt_fsqm' ),
					'spm' => __( 'Percentage Meta Line (Overrides graph to combo)', 'ipt_fsqm' ),
					'fl' => __( 'Filter Report', 'ipt_fsqm' ),
					'su' => __( 'Select Users', 'ipt_fsqm' ),
					'surl' => __( 'Select URL Tracks', 'ipt_fsqm' ),
					'umk' => __( 'User Meta Key', 'ipt_fsqm' ),
					'umv' => __( 'User Meta Value', 'ipt_fsqm' ),
					'somin' => __( 'Minimum Score Obtained (Inclusive)', 'ipt_fsqm' ),
					'somax' => __( 'Maximum Score Obtained (Inclusive)', 'ipt_fsqm' ),
					'dtmin' => __( 'Start Date Y-m-d H:i:s ( %s ) (Inclusive)', 'ipt_fsqm' ),
					'dtmax' => __( 'End Date Y-m-d H:i:s ( %s ) (Inclusive)', 'ipt_fsqm' ),
				),
			),
			'addons' => apply_filters( 'ipt_fsqm_mce', array() ),
			'forms' => (array) self::get_forms_for_select(),
			'nonce' => wp_create_nonce( 'ipt_fsqm_shortcode_get_mcqs' ),
			'themes' => (array) self::get_form_themes_for_select(),
			// Trends variables
			'trends' => array(
				'cTypeToggle' => $chart_helper,
				'reportTypes' => $report_type,
				'reportData' => $report_data,
				'reportAppearance' => $report_appearance,
			),
		) );

		do_action( 'ipt_fsqm_tmce_extendor_script' );
	}

	public static function mce_external_plugins( $plugins ) {
		global $ipt_fsqm_settings;
		$plugins['iptFSQMv3'] = plugins_url( '/static/admin/js/ipt-fsqm-tinymce-plugin.min.js', IPT_FSQM_Loader::$abs_file );
		return $plugins;
	}

	public static function mce_buttons( $buttons ) {
		global $ipt_fsqm_settings;
		array_push( $buttons, 'ipt_fsqm_tmce_menubutton' );
		return $buttons;
	}


	protected static function shortcode_to_wp_editor_api_init() {
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert', array( __CLASS__, 'shortcode_insert' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_form', array( __CLASS__, 'shortcode_insert_form' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_popup', array( __CLASS__, 'shortcode_insert_popup' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_system', array( __CLASS__, 'shortcode_insert_system' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_insert_trends', array( __CLASS__, 'shortcode_insert_trends' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_get_mcqs', array( __CLASS__, 'shortcode_insert_trends_helper' ) );
		add_action( 'wp_ajax_ipt_fsqm_shortcode_get_mcqs_for_mce', array( __CLASS__, 'shortcode_get_mcqs_for_mce' ) );
	}

	public static function shortcode_insert_trends() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_title"><?php _e( 'Title of the Visualization Column', 'ipt_fsqm' ); ?></label></th>
			<td>
				<input type="text" class="regular-text" id="ipt_fsqm_shortcode_insert_title" value="<?php _e( 'Trends', 'ipt_fsqm' ); ?>" />
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_load"><?php _e( 'Server Load', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_load">
					<option value="0"><?php _e( 'Light Load: 15 queries per hit', 'ipt_fsqm' ); ?></option>
					<option value="1" selected="selected"><?php _e( 'Medium Load: 30 queries per hit (Recommended)', 'ipt_fsqm' ); ?></option>
					<option value="2"><?php _e( 'Heavy Load: 50 queries per hit', 'ipt_fsqm' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_form"><?php _e( 'Select the form', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_form">
					<option value="" selected="selected"><?php _e( 'Please select a form', 'ipt_fsqm' ); ?></option>
					<?php foreach ( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>"><?php echo $form->name; ?></option>
					<?php endforeach; ?>
				</select><img style="display: none;" src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />
			</td>
		</tr>

		<tr id="ipt_fsqm_shortcode_insert_mcqs">
			<td colspan="2">

			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" disabled="disabled" id="ipt_fsqm_shortcode_insert_trends_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var wpnonce = '<?php echo wp_create_nonce( 'ipt_fsqm_shortcode_get_mcqs' ); ?>';
	$('#ipt_fsqm_shortcode_insert_form').on('change keyup', function() {
		if ( $(this).val() == '' ) {
			$('#ipt_fsqm_shortcode_insert_trends_submit').attr('disabled', true);
			$('#ipt_fsqm_shortcode_insert_mcqs td').html('Please select a valid form.')
			return;
		}
		$(this).next('img').show();
		$('#ipt_fsqm_shortcode_insert_mcqs td').html('Please Wait...');
		$('#ipt_fsqm_shortcode_insert_trends_submit').attr('disabled', true);
		var form_id = $(this).val();
		var data = {
			'form_id' : form_id,
			'action' : 'ipt_fsqm_shortcode_get_mcqs',
			'wpnonce' : wpnonce
		};
		$.get(ajaxurl, data, function(response) {
			var toAppend = $('<div />').html(response.html);
			$('#ipt_fsqm_shortcode_insert_mcqs td').html('');
			$('#ipt_fsqm_shortcode_insert_mcqs td').append(toAppend);
			var checkAll = toAppend.find('.ipt_fsqm_shortcode_mcq_all'),
			mcqChecks = toAppend.find('.ipt_fsqm_shortcode_mcqs');
			mcqChecks.on('change', function() {
				checkAll.attr('checked', false);
			});
			checkAll.on('change', function() {
				if($(this).is(':checked')) {
					mcqChecks.attr('checked', true);
				} else {
					mcqChecks.attr('checked', false);
				}
			});
			wpnonce = response.wpnonce;
			// Fix the height of the TB_ajaxContent
			$('#TB_ajaxContent').height($('#TB_window').height() - 50);
			$('#ipt_fsqm_shortcode_insert_trends_submit').attr('disabled', false);
		}, 'json').fail(function() {
			alert('HTTP error has occured');
		}).always(function() {
			$('#ipt_fsqm_shortcode_insert_form').next('img').hide();
		});
	});

	$('#ipt_fsqm_shortcode_insert_trends_submit').on('click', function(e) {
		e.preventDefault();
		var shortcode = '[ipt_fsqm_trends form_id="' + $('#ipt_fsqm_shortcode_insert_form').val() + '" title="' + $('#ipt_fsqm_shortcode_insert_title').val()  + '" load="' + $('#ipt_fsqm_shortcode_insert_load').val() + '" mcq_ids="',
		mcq_ids,
		all_checkbox = $('#ipt_fsqm_shortcode_insert_mcqs td').find('.ipt_fsqm_shortcode_mcq_all');
		if(all_checkbox.length && all_checkbox.is(':checked')) {
			mcq_ids = 'all';
		} else {
			mcq_ids = $('.ipt_fsqm_shortcode_mcqs:checked').map(function() {
				return $(this).val();
			}).get();
		}
		if('' == mcq_ids) {
			mcq_ids = 'all';
		}
		shortcode += mcq_ids + '"]';
		if(!all_checkbox.length) {
			shortcode = '';
		}
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, '<br />' + shortcode + '<br />');
		}]);
	});
});
</script>
		<?php
		die();
	}

	public static function shortcode_get_mcqs_for_mce() {
		global $wpdb, $ipt_fsqm_info;
		$form_id = (int) $_REQUEST['form_id'];
		$report_config = (array) $_REQUEST['reportType'];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'ipt_fsqm_shortcode_get_mcqs' ) ) {
			$return = array(
				'html' => __( 'Cheatin&#8217; uh?' ),
			);
			echo json_encode( (object) $return );
			die();
		}

		$form_element = new IPT_FSQM_Form_Elements_Utilities( $form_id );
		if ( null == $form_element->form_id ) {
			$return = array(
				'html' => __( 'Please select a form and try again.', 'ipt_fsqm' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Now return elements depending on the report type
		$return = array(
			'mcqs' => array(),
			'freetypes' => array(),
			'pinfos' => array(),
			'filters' => array(),
		);

		// Include mcqs
		if ( isset( $report_config['mcq'] ) && 'true' == $report_config['mcq'] ) {
			$mcqs = $form_element->get_keys_from_layouts_by_m_type( 'mcq', $form_element->layout );
			if ( ! empty( $mcqs ) ) {
				$rmcqs = array();
				$rmcqs[] = array(
					'text' => __( 'Show All', 'ipt_fsqm' ),
					'value' => 'all',
				);
				foreach ( $mcqs as $mcq ) {
					$rmcqs[] = array(
						'text' => $form_element->mcq[ $mcq ]['title'],
						'value' => $mcq,
						'type' => $form_element->mcq[ $mcq ]['type'],
					);
				}
				$return['mcqs'] = $rmcqs;
			}
		}

		// Include feedbacks
		if ( isset( $report_config['freetype'] ) && 'true' == $report_config['freetype'] ) {
			$freetypes = $form_element->get_keys_from_layouts_by_m_type( 'freetype', $form_element->layout );
			if ( ! empty( $freetypes ) ) {
				$rfreetypes = array();
				$rfreetypes[] = array(
					'text' => __( 'Show All', 'ipt_fsqm' ),
					'value' => 'all',
				);
				foreach ( $freetypes as $freetype ) {
					$rfreetypes[] = array(
						'text' => $form_element->freetype[ $freetype ]['title'],
						'value' => $freetype,
						'type' => $form_element->freetype[ $freetype ]['type'],
					);
				}
				$return['freetypes'] = $rfreetypes;
			}
		}

		// Include pinfos
		if ( isset( $report_config['pinfo'] ) && 'true' == $report_config['pinfo'] ) {
			$pinfos = $form_element->get_keys_from_layouts_by_m_type( 'pinfo', $form_element->layout );
			if ( ! empty( $pinfos ) ) {
				$rpinfos = array();
				$rpinfos[] = array(
					'text' => __( 'Show All', 'ipt_fsqm' ),
					'value' => 'all',
				);
				foreach ( $pinfos as $pinfo ) {
					$rpinfos[] = array(
						'text' => $form_element->pinfo[ $pinfo ]['title'],
						'value' => $pinfo,
						'type' => $form_element->pinfo[ $pinfo ]['type'],
					);
				}
				$return['pinfos'] = $rpinfos;
			}
		}

		// Include filters
		// Valid users
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $form_id ) );
		$user_id_items = array();
		$user_id_items[] = array(
			'value' => 'all',
			'text' => __( 'Show for all users', 'ipt_fsqm' ),
		);
		foreach ( $user_ids as $uid ) {
			if ( '0' === $uid ) {
				continue;
			}
			$userdata = get_userdata( $uid );
			if ( ! is_wp_error( $userdata ) && is_object( $userdata ) ) {
				$user_id_items[] = array(
					'value' => $uid,
					'text' => $userdata->user_nicename,
				);
			} else {
				$user_id_items[] = array(
					'value' => $uid,
					'text' => sprintf( __( '(Deleted User) ID: %s', 'ipt_fsqm' ), $uid ),
				);
			}
		}
		$return['filters']['users'] = $user_id_items;

		// Get valid URL tracking
		$url_tracking = (array) $wpdb->get_results( $wpdb->prepare( "SELECT url_track, id, form_id FROM {$ipt_fsqm_info['data_table']} GROUP BY url_track HAVING form_id = %d AND url_track != ''", $form_id ) );
		$url_tracking_items = array(
			0 => array(
				'value' => 'all',
				'text' => __( 'Show for all', 'ipt_fsqm' ),
			),
		);

		foreach ( $url_tracking as $ut ) {
			if ( null == $ut->url_track || '' == $ut->url_track ) {
				continue;
			}
			$url_tracking_items[] = array(
				'value' => $ut->id,
				'text' => $ut->url_track,
			);
		}
		$return['filters']['urltb'] = $url_tracking_items;

		// Get valid date range
		$return['filters']['dates']['least_date'] = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date ASC LIMIT 0,1", $form_id ) );
		$return['filters']['dates']['recent_date'] = $wpdb->get_var( $wpdb->prepare( "SELECT date FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d ORDER BY date DESC LIMIT 0,1", $form_id ) );

		echo json_encode( (object) $return );
		die();
	}

	public static function get_chart_type_n_toggles() {
		// We need to calculate only once
		static $return = null;
		// If already calculated, then just return
		if ( ! is_null( $return ) ) {
			return $return;
		}

		// Initiate the return
		$return = array(
			'possible_chart_types' => array(),
			'toggle_labels' => array(),
			'possible_toggle_types' => array(),
		);

		// Chart type presets
		$chart_mcq = array(
			'pie' => __( 'Pie Chart', 'ipt_fsqm' ),
			'bar' => __( 'Bar Chart', 'ipt_fsqm' ),
			'column' => __( 'Column Chart', 'ipt_fsqm' ),
		);
		$chart_slider = array(
			'bar' => __( 'Bar Chart', 'ipt_fsqm' ),
			'column' => __( 'Column Chart', 'ipt_fsqm' ),
			'area' => __( 'Area Chart', 'ipt_fsqm' ),
		);
		$chart_matrix = array(
			'bar' => __( 'Bar Chart', 'ipt_fsqm' ),
			'sbar' => __( 'Stacked Bar Chart', 'ipt_fsqm' ),
			'column' => __( 'Column Chart', 'ipt_fsqm' ),
			'scolumn' => __( 'Stacked Column Chart', 'ipt_fsqm' ),
		);

		// Extend for third-party
		$return['possible_chart_types'] = apply_filters( 'ipt_fsqm_chart_types', array(
			// MCQ Elements
			'radio' => $chart_mcq,
			'checkbox' => $chart_mcq,
			'select' => $chart_mcq,
			'thumbselect' => $chart_mcq,
			'slider' => $chart_slider,
			'spinners' => $chart_slider,
			'grading' => $chart_slider,
			'smileyrating' => $chart_mcq,
			'starrating' => $chart_slider,
			'scalerating' => $chart_slider,
			'matrix' => $chart_matrix,
			'matrix_dropdown' => $chart_matrix,
			'likedislike' => $chart_mcq,
			'toggle' => $chart_mcq,
			'sorting' => $chart_mcq,
			// Do not need anything for feedback elements at this moment
			// pinfo (Other) Elements
			'p_radio' => $chart_mcq,
			'p_checkbox' => $chart_mcq,
			's_checkbox' => $chart_mcq,
			'p_select' => $chart_mcq,
			'p_sorting' => $chart_mcq,
		) );

		// Toggle options for graphs
		// Extend for third-party
		$return['toggle_labels'] = apply_filters( 'ipt_fsqm_chart_toggle_values', array(
			'average' => __( 'Percentage Meta Line (Overrides graph to combo)', 'ipt_fsqm' ),
			'title' => __( 'Show Chart Title', 'ipt_fsqm' ),
			'legend' => __( 'Show Chart Legend and Axis Ticks', 'ipt_fsqm' ),
		) );
		$return['possible_toggle_types'] = apply_filters( 'ipt_fsqm_chart_toggles', array(
			// MCQ Elements
			'radio' => array( 'title', 'legend' ),
			'checkbox' => array( 'title', 'legend' ),
			'select' => array( 'title', 'legend' ),
			'thumbselect' => array( 'title', 'legend' ),
			'slider' => array( 'title', 'legend', 'average' ),
			'range' => array( 'title', 'legend' ),
			'spinners' => array( 'title', 'legend', 'average' ),
			'grading' => array( 'title', 'legend', 'average' ),
			'smileyrating' => array( 'title', 'legend' ),
			'starrating' => array( 'title', 'legend' ),
			'scalerating' => array( 'title', 'legend' ),
			'matrix' => array( 'title', 'legend' ),
			'matrix_dropdown' => array( 'title', 'legend' ),
			'likedislike' => array( 'title', 'legend' ),
			'toggle' => array( 'title', 'legend' ),
			'sorting' => array( 'title', 'legend' ),
			// pinfo (Other) Elements
			'p_radio' => array( 'title', 'legend' ),
			'p_checkbox' => array( 'title', 'legend' ),
			's_checkbox' => array( 'title', 'legend' ),
			'p_select' => array( 'title', 'legend' ),
			'p_sorting' => array( 'title', 'legend' ),
		) );

		return $return;
	}

	public static function get_default_chart_n_toggle( $mcqs = array(), $pinfos = array() ) {
		// Get the presets
		$chart_type_n_toggle = self::get_chart_type_n_toggles();

		// Create the skeleton
		$cmeta = array(
			'mcq' => array(
				'charttype' => array(),
				'toggles' => array(),
			),
			'pinfo' => array(
				'charttype' => array(),
				'toggles' => array(),
			),
		);

		// Set for mcq
		foreach ( $mcqs as $m_key => $mcq ) {
			// Set the chart type
			if ( isset( $chart_type_n_toggle['possible_chart_types'][ $mcq['type'] ] ) ) {
				$cmeta['mcq']['charttype'][ $m_key ] = current( array_keys( $chart_type_n_toggle['possible_chart_types'][ $mcq['type'] ] ) );
			}
			// Set the toggles
			if ( isset( $chart_type_n_toggle['possible_toggle_types'][ $mcq['type'] ] ) ) {
				$cmeta['mcq']['toggles'][ $m_key ] = array();
				foreach ( $chart_type_n_toggle['possible_toggle_types'][ $mcq['type'] ] as $toggle ) {
					$cmeta['mcq']['toggles'][ $m_key ][ $toggle ] = true;
				}
			}
		}

		// Set for pinfo
		foreach ( $pinfos as $p_key => $pinfo ) {
			// Set the chart type
			if ( isset( $chart_type_n_toggle['possible_chart_types'][ $pinfo['type'] ] ) ) {
				$cmeta['pinfo']['charttype'][ $p_key ] = current( array_keys( $chart_type_n_toggle['possible_chart_types'][ $pinfo['type'] ] ) );
			}
			// Set the toggles
			if ( isset( $chart_type_n_toggle['possible_toggle_types'][ $pinfo['type'] ] ) ) {
				$cmeta['pinfo']['toggles'][ $p_key ] = array();
				foreach ( $chart_type_n_toggle['possible_toggle_types'][ $pinfo['type'] ] as $toggle ) {
					$cmeta['pinfo']['toggles'][ $p_key ][ $toggle ] = true;
				}
			}
		}

		return $cmeta;
	}

	public static function get_chart_elements( $form_id ) {
		// Prepare an empty return
		$return = array(
			'mcqs' => array(),
			'pinfos' => array(),
		);

		// Instantiate the form
		$form = new IPT_FSQM_Form_Elements_Base( $form_id );

		// If invalid form, then just return the empty array
		if ( is_null( $form->form_id ) ) {
			return $return;
		}

		// pinfo types for charts
		$pinfo_charts = self::get_pinfo_chart_elements();

		// Now iterate through all mcq elements
		foreach ( $form->mcq as $m_key => $mcq ) {
			$return['mcqs'][] = array(
				'key' => $m_key,
				'type' => $mcq['type'],
				'title' => $mcq['title'],
			);
		}

		// Iterate through all pinfo elements
		foreach ( $form->pinfo as $p_key => $pinfo ) {
			if ( in_array( $pinfo['type'], $pinfo_charts ) ) {
				$return['pinfos'][] = array(
					'key' => $p_key,
					'type' => $pinfo['type'],
					'title' => $pinfo['title'],
				);
			}
		}

		return apply_filters( 'ipt_fsqm_form_chart_elements', $return, $form_id, $pinfo_charts );
	}

	public static function get_pinfo_chart_elements() {
		return apply_filters( 'ipt_fsqm_pinfo_chart_elements', array(
			'p_radio',
			'p_checkbox',
			's_checkbox',
			'p_select',
			'p_sorting',
		) );
	}

	public static function shortcode_insert_trends_helper() {
		$form_id = (int) $_REQUEST['form_id'];
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		if ( ! wp_verify_nonce( $_REQUEST['wpnonce'], 'ipt_fsqm_shortcode_get_mcqs' ) ) {
			$return = array(
				'wpnonce' => '',
				'html' => __( 'Cheatin&#8217; uh?' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		$wpnonce = wp_create_nonce( 'ipt_fsqm_shortcode_get_mcqs' );
		$form_element = new IPT_FSQM_Form_Elements_Utilities( $form_id );
		if ( null == $form_element->form_id ) {
			$return = array(
				'wpnonce' => $wpnonce,
				'html' => __( 'Please select a form and try again.', 'ipt_fsqm' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		$mcqs = $form_element->get_keys_from_layouts_by_m_type( 'mcq', $form_element->layout );
		if ( empty( $mcqs ) ) {
			$return = array(
				'wpnonce' => $wpnonce,
				'html' => __( 'No survey or multiple choice type questions found for this form.', 'ipt_fsqm' ),
			);
			echo json_encode( (object) $return );
			die();
		}
		ob_start();
?>
<input type="checkbox" class="ipt_fsqm_shortcode_mcq_all" value="all" checked="checked" id="ipt_fsqm_shortcode_mcq_all" />
<label for="ipt_fsqm_shortcode_mcq_all"><?php _e( 'Show All', 'ipt_fsqm' ); ?></label>
		<?php foreach ( $mcqs as $mcq ) : ?>
<br />
<input type="checkbox" class="ipt_fsqm_shortcode_mcqs" checked="checked" value="<?php echo $mcq; ?>" id="ipt_fsqm_shortcode_mcq_<?php echo $mcq; ?>" />
<label for="ipt_fsqm_shortcode_mcq_<?php echo $mcq; ?>"><?php echo esc_attr( $form_element->mcq[$mcq]['title'] ); ?></label>
		<?php endforeach; ?>
		<?php
		$html = ob_get_clean();
		$return = array(
			'wpnonce' => $wpnonce,
			'html' => $html,
		);
		echo json_encode( (object) $return );
		die();
	}

	public static function shortcode_insert_system() {
		$utrack_attrs = array(
			'login_attr' => array(
				'login' => __( 'Message to logged out users', 'ipt_fsqm' ),
				'show_register' => __( 'Show the registration button', 'ipt_fsqm' ),
				'show_forgot' => __( 'Show password recovery link', 'ipt_fsqm' ),
			),
			'portal_attr' => array(
				'title' => __( 'Welcome Title', 'ipt_fsqm' ),
				'content' => __( 'Welcome message', 'ipt_fsqm' ),
				'nosubmission' => __( 'No submissions message', 'ipt_fsqm' ),
				'formlabel' => __( 'Form Heading Label', 'ipt_fsqm' ),
				'filters' => __( 'Show Filters for Forms and Categories', 'ipt_fsqm' ),
				'showcategory' => __( 'Show Category', 'ipt_fsqm' ),
				'categorylabel' => __( 'Category Label', 'ipt_fsqm' ),
				'datelabel' => __( 'Date Heading Label', 'ipt_fsqm' ),
				'showscore' => __( 'Show Score Column', 'ipt_fsqm' ),
				'scorelabel' => __( 'Score Heading Label', 'ipt_fsqm' ),
				'mscorelabel' => __( 'Max Score Heading Label', 'ipt_fsqm' ),
				'pscorelabel' => __( 'Percentage Score Heading Label', 'ipt_fsqm' ),
				'actionlabel' => __( 'Action Column Heading Label', 'ipt_fsqm' ),
				'linklabel' => __( 'Trackback Button Label', 'ipt_fsqm' ),
				'editlabel' => __( 'Edit Button Label', 'ipt_fsqm' ),
				'avatar' => __( 'Avatar Size', 'ipt_fsqm' ),
				'theme' => __( 'Page Theme', 'ipt_fsqm' ),
				'logout_r' => __( 'Redirection after Logout', 'ipt_fsqm' ),
			),
		);
		$utrack_labels = array(
			'login_attr' => __( 'Login Page Modifications', 'ipt_fsqm' ),
			'portal_attr' => __( 'Portal Page Modifications', 'ipt_fsqm' ),
		);
		$utrack_defaults = array(
			'content' => __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' ),
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'filters' => '0',
			'showcategory' => '0',
			'categorylabel' => __( 'Category', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'material-default',
			'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
			'logout_r' => '',
		);
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$themes = $form_element->get_available_themes();
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_system"><?php _e( 'System Shortcode Type', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_system">
					<option value="ipt_fsqm_trackback"><?php _e( 'Single Submission Trackback Page for Unregistered Users', 'ipt_fsqm' ); ?></option>
					<option value="ipt_fsqm_utrackback"><?php _e( 'User Portal - Central Trackback page for Registered Users', 'ipt_fsqm' ); ?></option>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<div id="ipt_fsqm_shortcode_trackback_wrap">
	<table class="form-table">
		<tr id="ipt_fsqm_system_shortcode_trackback_label">
			<th>
				<label for="ipt_fsqm_system_shortcode_tb_label"><?php _e( 'Form Label', 'ipt_fsqm' ); ?></label>
			</th>
			<td>
				<input type="text" id="ipt_fsqm_system_shortcode_tb_label" value="<?php _e( 'Track Code', 'ipt_fsqm' ); ?>" /><br />
				<span class="description"><?php _e( 'Enter the label of the text input where the surveyee will need to paste his/her trackback code.', 'ipt_fsqm' ); ?></span>
			</td>
		</tr>
		<tr id="ipt_fsqm_system_shortcode_trackback_submit">
			<th>
				<label for="ipt_fsqm_system_shortcode_tb_submit"><?php _e( 'Submit Button Text', 'ipt_fsqm' ); ?></label>
			</th>
			<td>
				<input type="text" id="ipt_fsqm_system_shortcode_tb_submit" value="<?php _e( 'Submit', 'ipt_fsqm' ); ?>" />
			</td>
		</tr>
	</table>
</div>
<div id="ipt_fsqm_shortcode_utrackback_wrap">
	<?php foreach ( $utrack_attrs as $attr => $labels ) : ?>
	<h3><?php echo $utrack_labels[$attr]; ?></h3>
	<table class="form-table">
		<?php foreach ( $labels as $key => $label ) : ?>
		<tr>
			<th>
				<label for="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>"><?php echo $label; ?></label>
			</th>
			<td>
				<?php if ( $key == 'content' ) : ?>
				<textarea id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>" class="widefat" rows="5"><?php echo esc_textarea( $utrack_defaults[$key] ); ?></textarea>
				<?php elseif ( $key == 'theme' ) : ?>
				<select id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>">
					<?php foreach ( $themes as $theme_grp ) : ?>
					<optgroup label="<?php echo $theme_grp['label']; ?>">
						<?php foreach ( $theme_grp['themes'] as $theme_key => $theme ) : ?>
						<option value="<?php echo $theme_key; ?>"<?php if ( $utrack_defaults[$key] == $theme_key ) echo ' selected="selected"'; ?>><?php echo $theme['label']; ?></option>
						<?php endforeach; ?>
					</optgroup>
					<?php endforeach; ?>
				</select>
				<?php elseif ( is_numeric( $utrack_defaults[$key] ) && $utrack_defaults[$key] <= 1 ) : ?>
				<input type="checkbox" value="1" id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>"<?php if ( $utrack_defaults[$key] == '1' ) echo ' checked="checked"'; ?> />
				<?php else : ?>
				<input type="text" class="large-text" value="<?php echo esc_html( $utrack_defaults[$key] ); ?>" id="ipt_fsqm_system_shortcode_utb_<?php echo $key; ?>" />
				<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endforeach; ?>
</div>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" id="ipt_fsqm_shortcode_insert_system_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	var utrack_ids = <?php echo json_encode( array_keys( $utrack_defaults ) ); ?>;
	$('#ipt_fsqm_shortcode_insert_system_submit').on('click', function(e) {
		e.preventDefault();
		var val = $('#ipt_fsqm_shortcode_insert_system').val();
		var shortcode = '[' + val;
		if(val == 'ipt_fsqm_trackback') {
			shortcode += ' label="' + $('#ipt_fsqm_system_shortcode_tb_label').val() + '" submit="' + $('#ipt_fsqm_system_shortcode_tb_submit').val() + '"';
		} else {
			for(var id in utrack_ids) {
				var elem = $('#ipt_fsqm_system_shortcode_utb_' + utrack_ids[id]);
				if ( elem.is('textarea') ) {
					continue;
				}
				shortcode += ' ' + utrack_ids[id] + '="';
				if ( elem.is('input[type="checkbox"]') ) {
					shortcode += elem.is(':checked') ? '1' : '0';
				} else {
					shortcode += elem.val();
				}
				shortcode += '"';
			}
			shortcode += ']' + $('#ipt_fsqm_system_shortcode_utb_content').val() + '[/ipt_fsqm_utrackback';
		}
		shortcode += ']';
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, "\n" + '<br />' + shortcode + '<br />' + "\n");
		}]);
	});

	var check_tb = function() {
		if($('#ipt_fsqm_shortcode_insert_system').val() == 'ipt_fsqm_trackback') {
			$('#ipt_fsqm_shortcode_trackback_wrap').show();
			$('#ipt_fsqm_shortcode_utrackback_wrap').hide();
		} else {
			$('#ipt_fsqm_shortcode_utrackback_wrap').show();
			$('#ipt_fsqm_shortcode_trackback_wrap').hide();
		}
		// Fix the height of the TB_ajaxContent
		$('#TB_ajaxContent').height($('#TB_window').height() - 50);
	};
	check_tb();
	$('#ipt_fsqm_shortcode_insert_system').on('change keyup', check_tb);
});
</script>
		<?php
		die();
	}

	public static function shortcode_insert_popup() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
?>
<table class="form-table ipt_fsqm_popup_shortcode_table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_popup_form"><?php _e( 'Select the form', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_popup_form">
					<?php foreach ( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>"><?php echo $form->name; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_popup_text"><?php _e( 'Button Text', 'ipt_fsqm' ); ?></label></th>
			<td>
				<input type="text" id="ipt_fsqm_shortcode_popup_text" value="<?php esc_attr_e( 'Contact Form', 'ipt_fsqm' ); ?>" />
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_popup_color"><?php _e( 'Button Color', 'ipt_fsqm' ); ?></label></th>
			<td>
				<input type="text" id="ipt_fsqm_shortcode_popup_color" class="ipt_uif_colorpicker" value="#ffffff" />
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_popup_bgcolor"><?php _e( 'Button Background Color', 'ipt_fsqm' ); ?></label></th>
			<td>
				<input type="text" id="ipt_fsqm_shortcode_popup_bgcolor" class="ipt_uif_colorpicker" value="#3C609E" />
			</td>
		</tr>
		<tr>
			<th><label for="ipt_fsqm_shortcode_popup_pos"><?php _e( 'Button Position', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_popup_pos">
					<option value="r"><?php _e( 'Right', 'ipt_fsqm' ); ?></option>
					<option value="br" selected="selected"><?php _e( 'Bottom Right', 'ipt_fsqm' ); ?></option>
					<option value="bc"><?php _e( 'Bottom Center', 'ipt_fsqm' ); ?></option>
					<option value="bl"><?php _e( 'Bottom Left', 'ipt_fsqm' ); ?></option>
					<option value="l"><?php _e( 'Left', 'ipt_fsqm' ); ?></option>
					<option value="h"><?php _e( 'Hidden / Manual Trigger', 'ipt_fsqm' ); ?></option>
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<span class="description"><?php _e( 'If you want a manual trigger, then make sure you have the following html in your post/page.', 'ipt_fsqm' ); ?></span>
<pre>
&lt;a href=&quot;#ipt-fsqm-popup-form-<span class="fsqm_popup_shortcode_hidden_id"></span>&quot; data-remodal-target=&quot;ipt-fsqm-popup-form-<span class="fsqm_popup_shortcode_hidden_id"></span>&quot;&gt;<?php _e( 'Some Text', 'ipt_fsqm' ); ?>&lt;/a&gt;
</pre>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" id="ipt_fsqm_shortcode_popup_form_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('.ipt_fsqm_popup_shortcode_table .ipt_uif_colorpicker').wpColorPicker();
	$('#ipt_fsqm_shortcode_popup_form').on('change', function() {
		$('.fsqm_popup_shortcode_hidden_id').html( $(this).val() );
	});
	$('.fsqm_popup_shortcode_hidden_id').html( $('#ipt_fsqm_shortcode_popup_form').val() );
	$('#ipt_fsqm_shortcode_popup_form_submit').on('click', function(e) {
		e.preventDefault();
		var form_id = $('#ipt_fsqm_shortcode_popup_form').val(),
		label = $('#ipt_fsqm_shortcode_popup_text').val(),
		color = $('#ipt_fsqm_shortcode_popup_color').val(),
		bgcolor = $('#ipt_fsqm_shortcode_popup_bgcolor').val(),
		pos = $('#ipt_fsqm_shortcode_popup_pos').val();
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, '<br />[ipt_fsqm_popup form_id="' + form_id + '" pos="' + pos + '" color="' + color + '" bgcolor="' + bgcolor + '"]' + label + '[/ipt_fsqm_popup]<br />');
		}]);
	});
});
</script>
		<?php
		die();
	}

	public static function shortcode_insert_form() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
?>
<table class="form-table">
	<tbody>
		<tr>
			<th><label for="ipt_fsqm_shortcode_insert_form"><?php _e( 'Select the form', 'ipt_fsqm' ); ?></label></th>
			<td>
				<select id="ipt_fsqm_shortcode_insert_form">
					<?php foreach ( $forms as $form ) : ?>
					<option value="<?php echo $form->id; ?>"><?php echo $form->name; ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</tbody>
</table>
<p class="submit">
	<input type="button" class="button-primary" value="<?php _e( 'Insert', 'ipt_fsqm' ); ?>" id="ipt_fsqm_shortcode_insert_form_submit" />
	<a id="ipt_fsqm_shortcode_wizard_back" title="<?php _e( 'Insert Shortcodes for WP Feedback, Survey & Quiz Manager - Pro', 'ipt_fsqm' ); ?>" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert" class="button-secondary"><?php _e( 'Back', 'ipt_fsqm' ); ?></a>
</p>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_shortcode_insert_form_submit').on('click', function(e) {
		e.preventDefault();
		var val = $('#ipt_fsqm_shortcode_insert_form').val();
		$(document).trigger('ipt_fsqm_shortcode_insert', [function(ed) {
			ed.execCommand('mceInsertContent', 0, '<br />[ipt_fsqm_form id="' + val + '"]<br />');
		}]);
	});
});
</script>
		<?php
		die();
	}

	public static function shortcode_insert() {
?>
<div style="min-height: 500px; overflow-y: auto;">
<h3><?php _e( 'Please select what you wish to do', 'ipt_fsqm' ); ?></h3>
<table class="widefat" id="ipt_fsqm_shortcode_actions">
	<thead>
		<tr>
			<th><?php _e( 'Action', 'ipt_fsqm' ); ?></th>
			<th><?php _e( 'Consequence', 'ipt_fsqm' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - System Shortcodes', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_system"><?php _e( 'System Shortcodes', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Helps you insert system shortcodes for proper functionality of WP Feedback, Survey & Quiz Manager Pro Plugin. Once you insert any system shortcode, make sure to go to the <strong>Plugin Settings</strong> page and select the pages where you have put the shortcodes.', 'ipt_fsqm' ) ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - Insert Form', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_form"><?php _e( 'Insert Form', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Helps you insert forms you have created using shortcodes.', 'ipt_fsqm' ); ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - Insert Trends', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_trends"><?php _e( 'Insert Trends', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Helps you insert shortcodes to show trends of selected or all survey type questions of a form.', 'ipt_fsqm' ) ?>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<a title="<?php _e( 'WP Feedback, Survey & Quiz Manager - Pro - Insert Popup Forms', 'ipt_fsqm' ); ?>" class="button-secondary" href="admin-ajax.php?action=ipt_fsqm_shortcode_insert_popup"><?php _e( 'Popup Forms', 'ipt_fsqm' ); ?></a>
			</td>
			<td>
				<span class="description">
					<?php _e( 'Insert a popup form on a page. If you wish to show globally, then add it to a widget which parses shortcodes.', 'ipt_fsqm' ) ?>
				</span>
			</td>
		</tr>
		<?php do_action( 'ipt_fsqm_shortcode_wizard' ); ?>
	</tbody>
</table>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_shortcode_actions').find('a.button-secondary').on('click', function(e) {
		e.preventDefault();
		var href = $(this).attr('href');
		var title = $(this).attr('title');
		//tb_remove();
		tb_show(title, href);
	});
})
</script>
<div class="clear"></div>
</div>
		<?php
		die();
	}

	/*==========================================================================
	 * Standalone Form APIs
	 *========================================================================*/
	protected static function standalone_form_init() {
		// Add the ajax for the Form Builder Page
		add_action( 'wp_ajax_ipt_fsqm_preview_form', array( __CLASS__, 'standalone_form_output' ) );
		add_action( 'wp_ajax_ipt_fsqm_standalone_embed_generate', array( __CLASS__, 'standalone_embed_generate' ) );

		add_action( 'init', array( __CLASS__, 'standalone_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'standalone_frontend' ) );
	}

	public static function standalone_embed_generate() {
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$form_id = isset( $_REQUEST['form_id'] ) ? (int) $_REQUEST['form_id'] : 0;
		$permalink = self::standalone_permalink_parts( $form_id );

		echo json_encode( $permalink );
		die();
	}

	public static function standalone_base() {
		global $ipt_fsqm_settings;
		$base = ( ! isset( $ipt_fsqm_settings['standalone'] ) || ! isset( $ipt_fsqm_settings['standalone']['base'] ) || '' == $ipt_fsqm_settings['standalone']['base'] ) ? false : $ipt_fsqm_settings['standalone']['base'];
		if ( false !== $base ) {
			$base = sanitize_title( $base, 'eforms' );
		}
		return apply_filters( 'ipt_fsqm_standalone_base', $base );
	}

	public static function standalone_permalink_parts( $form_id ) {
		global $wpdb, $ipt_fsqm_info;

		$form_title = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );

		if ( null == $form_title ) {
			return false;
		}

		$base = self::standalone_base();

		if ( false === $base ) {
			return false;
		}

		$slug = sanitize_title( $form_title );

		$return = array(
			'base' => $base,
			'slug' => $slug,
			'id' => $form_id,
			'url' => home_url( '/' ) . $base . '/' . $slug . '/' . $form_id . '/',
			'shortlink' => home_url( '/' ) . $base . '/' . $form_id . '/',
		);

		return apply_filters( 'ipt_fsqm_standalone_permalink', $return );
	}

	public static function standalone_canonical( $url = '' ) {
		$form_id = get_query_var( 'ipt_fsqm_form_id' );
		$permalink = self::standalone_permalink_parts( $form_id );

		if ( false === $permalink || ! isset( $permalink['url'] ) ) {
			return $url;
		} else {
			return $permalink['url'];
		}
	}

	public static function standalone_title( $title, $sep = '|' ) {
		$form_id = get_query_var( 'ipt_fsqm_form_id' );
		$form = self::get_form( $form_id );

		if ( $form == null ) {
			return $title;
		}
		$form_elm = new IPT_FSQM_Form_Elements_Base( $form_id );
		$page_title = $form->name;
		if ( '' != $form_elm->settings['standalone']['title'] ) {
			$page_title = $form_elm->settings['standalone']['title'];
		}

		return sprintf( '%1$s %2$s %3$s', $page_title, $sep, get_bloginfo( 'name' ) );
	}

	public static function standalone_rewrite() {
		global $wp;
		// Now the rewrite rule magic
		// First some sanity check
		$base = self::standalone_base();
		if ( false === $base ) {
			return;
		}
		// Add our query vars
		$wp->add_query_var( 'ipt_fsqm_rewrite' );
		$wp->add_query_var( 'ipt_fsqm_form_id' );

		// Prepare the regex and redirect depending on the base
		$reg_ex = '^' . $base . '/?([^/]*)/([0-9]+)';
		$redirect = 'index.php?ipt_fsqm_rewrite=$matches[1]&ipt_fsqm_form_id=$matches[2]';

		// Add the rewrite rule
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function standalone_frontend() {
		$ipt_fsqm_rewrite = get_query_var( 'ipt_fsqm_rewrite' );
		$ipt_fsqm_form_id = get_query_var( 'ipt_fsqm_form_id' );
		if ( '' == $ipt_fsqm_form_id ) {
			return;
		}

		$permalink = self::standalone_permalink_parts( $ipt_fsqm_form_id );

		// Check if it is present
		if ( $permalink === false ) {
			// 404 it
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Redirect to proper page for SEO
		if ( $permalink['slug'] != $ipt_fsqm_rewrite ) {
			wp_redirect( $permalink['url'], 301 );
			die();
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'standalone_canonical' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'standalone_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'standalone_title' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'standalone_title' ), 10, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'standalone_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'standalone_title' ), 10, 2 );

		// Output the form
		self::standalone_form_output( (int) $ipt_fsqm_form_id );
	}

	public static function standalone_form_output( $form_id = false ) {
		global $ipt_fsqm_settings;
		if ( $form_id == false ) {
			$form_id = @$_REQUEST['form_id'];
		}
		$form = new IPT_FSQM_Form_Elements_Front( null, $form_id );
		$theme_dir = get_stylesheet_directory();
		$theme_uri = get_stylesheet_directory_uri();
		$base_css = '';
		$form_css = '';
		if ( file_exists( $theme_dir . '/fsqm-pro.css' ) ) {
			$base_css = '<link href="' . esc_url( $theme_uri . '/fsqm-pro.css' ) . '" media="all" rel="stylesheet" type="text/css" />';
		}
		if ( file_exists( $theme_dir . '/fsqm-pro-' . $form_id . '.css' ) ) {
			$form_css = '<link href="' . esc_url( $theme_uri . '/fsqm-pro-' . $form_id . '.css' ) . '" media="all" rel="stylesheet" type="text/css" />';
		}
		add_filter( 'show_admin_bar', '__return_false' );

		// Set the dynamic theme
		if ( isset( $_GET['fsqm_theme'] ) ) {
			$theme = $form->get_theme_by_id( strip_tags( $_GET['fsqm_theme'] ) );
			if ( ! empty( $theme['src'] ) || $_GET['fsqm_theme'] == 'default' ) {
				$form->settings['theme']['template'] = strip_tags( $_GET['fsqm_theme'] );
			}
		}

		// Set the bg color
		$bg_color = 'ffffff';
		if ( isset( $_GET['bg'] ) ) {
			$bg_color = strip_tags( $_GET['bg'] );
			if ( ! ctype_xdigit( $bg_color ) && $bg_color !== 'transparent' ) {
				$bg_color = 'ffffff';
			}
		}

		// Prepare variables for JSAPI
		$js_api = array(
			'id' => $form_id,
			'name' => $form->name,
			'theme' => $form->settings['theme']['template'],
			'bg_color' => $bg_color,
			'type' => $form->type,
			'product' => __( 'WP Feedback Survey & Quiz Manager Pro', 'ipt_fsqm' ),
			'version' => IPT_FSQM_Loader::$version,
		);
		$permalink = self::standalone_permalink_parts( $form_id );
		ob_start();
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php wp_title( '|', true ); ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width">
	<meta name="viewport" content="initial-scale=1.0">
	<!-- eForm Page SEO -->
	<?php if ( '' != $form->settings['standalone']['description'] ) : ?>
		<meta name="description" content="<?php echo esc_attr( $form->settings['standalone']['description'] ); ?>" />
	<?php endif; ?>
	<?php if ( '' != $form->settings['standalone']['title'] ) : ?>
		<meta name="og:title" content="<?php echo esc_attr( $form->settings['standalone']['title'] ); ?>" />
	<?php else : ?>
		<meta name="og:title" content="<?php echo esc_attr( $form->name  ); ?>" />
	<?php endif; ?>
	<?php if ( '' != $form->settings['standalone']['image'] ) : ?>
		<meta name="og:image" content="<?php echo esc_attr( $form->settings['standalone']['image'] ); ?>" />
	<?php endif; ?>
	<meta name="og:url" content="<?php echo $permalink['url']; ?>" />
	<!-- #end eForm Page SEO -->

	<style type="text/css">
	<?php if ( 'bootstrap' == $form->settings['theme']['template'] ) :; ?>
		@import url("//fonts.googleapis.com/css?family=Oswald|Roboto:400,700,400italic,700italic");
	<?php else : ?>
		<?php wp_enqueue_style( 'ipt-eform-material-font', 'https://fonts.googleapis.com/css?family=Noto+Sans|Roboto:300,400,400i,700', array(), IPT_FSQM_Loader::$version ); ?>
	<?php endif; ?>
	/* =Reset
	-------------------------------------------------------------- */

	html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
		margin: 0;
		padding: 0;
		border: 0;
		font-size: 100%;
		vertical-align: baseline;
	}
	body {
		line-height: 1;
	}
	html body:before,
	html body:after {
		display: none;
	}
	ol,
	ul {
		list-style: none;
	}
	blockquote,
	q {
		quotes: none;
	}
	blockquote:before,
	blockquote:after,
	q:before,
	q:after {
		content: '';
		content: none;
	}
	table {
		border-collapse: collapse;
		border-spacing: 0;
	}
	caption,
	th,
	td {
		font-weight: normal;
		text-align: left;
	}
	h1,
	h2,
	h3,
	h4,
	h5,
	h6 {
		clear: both;
	}
	html {
		overflow-y: auto;
		font-size: 100%;
		-webkit-text-size-adjust: 100%;
		-ms-text-size-adjust: 100%;
		margin-top: 0 !important;
	}
	a:focus {
		outline: thin dotted;
	}
	article,
	aside,
	details,
	figcaption,
	figure,
	footer,
	header,
	hgroup,
	nav,
	section {
		display: block;
	}
	audio,
	canvas,
	video {
		display: inline-block;
	}
	audio:not([controls]) {
		display: none;
	}
	del {
		color: #333;
	}
	ins {
		background: #fff9c0;
		text-decoration: none;
	}
	hr {
		background-color: #ccc;
		border: 0;
		height: 1px;
		margin: 24px;
		margin-bottom: 1.714285714rem;
	}
	sub,
	sup {
		font-size: 75%;
		line-height: 0;
		position: relative;
		vertical-align: baseline;
	}
	sup {
		top: -0.5em;
	}
	sub {
		bottom: -0.25em;
	}
	small {
		font-size: smaller;
	}
	img {
		border: 0;
		-ms-interpolation-mode: bicubic;
		max-width: 100%;
		height: auto;
	}
	h1, h2, h3, h4, h5, h6, p, ul, ol {
		line-height: 1.3;
		margin: 0 0 20px 0;
	}
	h1, h2, h3, h4, h5, h6 {
		font-family: 'Oswald', 'Arial Narrow', sans-serif;
		font-weight: normal;
		font-style: normal;
	}
	h1 {
		font-size: 2em;
	}
	h2 {
		font-size: 1.8em;
	}
	h3 {
		font-size: 1.6em;
	}
	h4 {
		font-size: 1.4em;
	}
	h5 {
		font-size: 1.2em;
	}
	h6 {
		font-size: 1em;
	}
	ul {
		list-style-type: disc;
		list-style-position: inside;
	}
	ol {
		list-style-type: decimal;
		list-style-position: inside;
	}
	</style>

	<?php echo $ipt_fsqm_settings['standalone']['head']; ?>
	<?php wp_head(); ?>
	<style type="text/css">
	body {
		background-color: #fff;
		background-image: none;
		font-family: 'Roboto', Tahoma, Geneva, sans-serif;
		font-weight: normal;
		font-style: normal;
		font-size: 12px;
		color: #333;
		min-width: 200px;
	}
	#fsqm_form {
		padding: 20px;
		margin: 0 auto;
		width: auto;
		-webkit-box-sizing: border-box;
		-moz-box-sizing: border-box;
		box-sizing: border-box;
	}
	#eform-inner {
		max-width: 1200px;
		margin: 0 auto;
	}
	body {
		background-color: <?php echo ( $bg_color == 'transparent' ? 'transparent' : '#' . $bg_color ); ?>;
	}
	</style>
	<?php echo $base_css; ?>
	<?php echo $form_css; ?>
</head>
<body <?php body_class( 'ipt_uif_common' ); ?>>
	<?php echo do_shortcode( wpautop( $ipt_fsqm_settings['standalone']['before'] ) ); ?>
	<div id="fsqm_form">
		<div id="eform-inner">
			<?php $form->show_form(); ?>
		</div>
	</div>
	<?php echo do_shortcode( wpautop( $ipt_fsqm_settings['standalone']['after'] ) ); ?>
	<?php wp_footer(); ?>
	<!-- Fix for #wpadminbar -->
	<style type="text/css">
		html {
			margin-top: 0 !important;
		}
	</style>
	<script type="text/javascript">
		// A JS API which would trigger an event to top frame
		// So that parents can easily hook into
		jQuery(document).ready(function($) {
			$('html').removeClass('no-js');
			var triggerObj = <?php echo json_encode( (object) $js_api ); ?>,
			w = window;
			triggerObj.bg_color = $('body').css('background-color');
			// fire first event on document load
			if ( w.frameElement != null ) {
				try {
					if ( typeof( w.parent.jQuery ) !== "undefined" ) {
						w.parent.jQuery(w.parent.document).trigger( 'fsqm.ready', [triggerObj] );
						w.parent.jQuery(w.parent.document).trigger( 'ipt.ready', [triggerObj] );
					}
				} catch ( e ) {

				}
			}

			// fire second on window load
			$(w).on( 'load', function() {
				if ( w.frameElement != null ) {
					try {
						if ( typeof( w.parent.jQuery ) !== "undefined" ) {
							w.parent.jQuery(w.parent.document).trigger( 'fsqm.loaded', [triggerObj] );
							w.parent.jQuery(w.parent.document).trigger( 'ipt.loaded', [triggerObj] );
						}
					} catch ( e ) {

					}
				}
			} );
		});
	</script>
</body>
</html>
		<?php
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			require_once IPT_FSQM_Loader::$abs_path . '/lib/classes/class-minify-html.php';
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		echo $form_output;
		die();
	}

	public static function email_rewrite_init() {
		add_action( 'init', array( __CLASS__, 'email_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'email_output' ) );
	}

	public static function email_rewrite() {
		global $wp;
		$wp->add_query_var( 'ipt_eform_email' );
		$reg_ex = '^eform-email';
		$redirect = 'index.php?ipt_eform_email=true';
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function email_output() {
		$eform_email = get_query_var( 'ipt_eform_email' );
		if ( '' == $eform_email ) {
			return;
		}
		// Decrypt the id
		$eform_data = @$_REQUEST['email_id'];
		if ( ! $eform_data ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check the ID
		$data_id = ( int ) self::decrypt( $eform_data );
		if ( ! $data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check if ID exists
		$data = new IPT_FSQM_Form_ELements_Data( $data_id );
		if ( null == $data->data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'email_canonical' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'email_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'email_title' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'email_title' ), 10, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'email_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'email_title' ), 10, 2 );

		// Output the email
		$html = $data->get_email_formatted_html( $data->user_notification_email( '', $data->get_submission_lock_status(), $data->settings['payment']['lock_message'] ), $data->user_email_format( $data->settings['user']['notification_sub'] ) );
		echo $html;

		die();
	}

	public static function email_canonical() {
		return home_url( '/' ) . 'eform-email/';
	}

	public static function email_title( $title, $sep = '|' ) {
		global $wpdb, $ipt_fsqm_info;
		$eform_data = @$_REQUEST['email_id'];
		$data_id = self::decrypt( $eform_data );
		$form = $wpdb->get_var( $wpdb->prepare( "SELECT f.name FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON d.form_id = f.id WHERE d.id = %d", $data_id ) );
		return sprintf( '%1$s %2$s %3$s', $form, $sep, get_bloginfo( 'name' ) );
	}

	public static function payment_email_rewrite_init() {
		add_action( 'init', array( __CLASS__, 'payment_email_rewrite' ) );
		add_action( 'template_redirect', array( __CLASS__, 'payment_email_output' ) );
	}

	public static function payment_email_rewrite() {
		global $wp;
		$wp->add_query_var( 'ipt_eform_payment_email' );
		$reg_ex = '^eform-payment';
		$redirect = 'index.php?ipt_eform_payment_email=true';
		add_rewrite_rule( $reg_ex, $redirect, 'top' );

		// Flush the rewrite rule if necessary
		// Expected is for plugin update or new installation
		if ( get_option( 'ipt_fsqm_flush_rewrite', false ) ) {
			flush_rewrite_rules( true );
			update_option( 'ipt_fsqm_flush_rewrite', false );
		}
	}

	public static function payment_email_output() {
		$eform_email = get_query_var( 'ipt_eform_payment_email' );
		if ( '' == $eform_email ) {
			return;
		}
		// Decrypt the id
		$eform_data = @$_REQUEST['payment_id'];
		if ( ! $eform_data ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check the ID
		$data_id = ( int ) self::decrypt( $eform_data );
		if ( ! $data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}
		// Check if ID exists
		$data = new IPT_FSQM_Form_ELements_Data( $data_id );
		if ( null == $data->data_id ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			return;
		}

		// Add the canonical filters
		add_filter( 'aioseop_canonical_url', array( __CLASS__, 'payment_email_canonical' ) );
		add_filter( 'wpseo_canonical', array( __CLASS__, 'payment_email_canonical' ) );

		// Add the title filter
		add_filter( 'wp_title', array( __CLASS__, 'payment_email_title' ), 20, 2 );
		add_filter( 'wpseo_title', array( __CLASS__, 'payment_email_title' ), 10, 2 );
		add_filter( 'aioseop_title_single', array( __CLASS__, 'payment_email_title' ), 10, 2 );
		add_filter( 'aioseop_title_page', array( __CLASS__, 'payment_email_title' ), 10, 2 );

		// Now prepare variables
		$retry = (boolean) @$_REQUEST['retry'];
		$mode = (string) @$_REQUEST['mode'];
		$success = (boolean) @$_REQUEST['success'];
		$cancelled = (boolean) @$_REQUEST['cancelled'];
		$invoice = (string) @$_REQUEST['invoice'];

		// Set the title and message
		if ( $retry ) {
			$title = $data->settings['payment']['retry_uemail_sub'];
			$msg = $data->settings['payment']['retry_uemail_msg'];
		} else {
			$title = $data->settings['payment']['success_sub'];
			$msg = $data->settings['payment']['success_msg'];
		}
		// In case of paypal express mode
		if ( 'paypal_e' == $mode ) {
			$title = $data->settings['payment']['paypal']['conf_sub'];
			$msg = $data->settings['payment']['paypal']['conf_msg'];
		}
		// But in case of error, just override
		if ( false == $success ) {
			$title = $data->settings['payment']['error_sub'];
			$msg = $data->settings['payment']['error_msg'];
			// If in case of cancellation
			if ( true == $cancelled ) {
				$title = $data->settings['payment']['cancel_sub'];
				$msg = $data->settings['payment']['cancel_msg'];
			}
		}

		// Now format everything
		// Get format string
		$format_string_components = $data->get_format_string();
		$title = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $title );
		$title = sprintf( $title, $invoice );
		$msg = str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), $msg );

		// Output the email
		$data->format_email_style();
		$html = $data->get_email_formatted_html( $data->user_payment_email( $msg, array(
			'retry' => $retry,
			'mode' => $mode,
			'success' => $success,
			'cancelled' => $cancelled,
			'invoice' => $invoice,
		) ), $title );
		echo $html;

		die();
	}

	public static function payment_email_canonical() {
		return home_url( '/' ) . 'eform-payment/';
	}

	public static function payment_email_title( $title, $sep = '|' ) {
		global $wpdb, $ipt_fsqm_info;
		$eform_data = @$_REQUEST['payment_id'];
		$data_id = self::decrypt( $eform_data );
		$form = $wpdb->get_var( $wpdb->prepare( "SELECT f.name FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON d.form_id = f.id WHERE d.id = %d", $data_id ) );
		return sprintf( _x( 'Payment: %1$s %2$s %3$s', 'eform-payment-email-page-title', 'ipt_fsqm' ), $form, $sep, get_bloginfo( 'name' ) );
	}

	/*==========================================================================
	 * Shortcodes
	 *========================================================================*/
	protected static function ipt_fsqm_shortcodes_init() {
		global $ipt_fsqm_settings;
		// Basic Shortcodes
		add_shortcode( 'ipt_fsqm_form', array( __CLASS__, 'ipt_fsqm_form_cb' ) );
		add_shortcode( 'ipt_fsqm_trackback', array( __CLASS__, 'ipt_fsqm_track_cb' ) );
		add_shortcode( 'ipt_fsqm_utrackback', array( __CLASS__, 'ipt_fsqm_utrack_cb' ) );
		add_shortcode( 'ipt_fsqm_trends', array( __CLASS__, 'ipt_fsqm_trends_cb' ) );
		add_shortcode( 'ipt_fsqm_popup', array( __CLASS__, 'ipt_fsqm_popup_cb' ) );
	}

	public static function ipt_fsqm_popup_cb( $args, $content = 'Sample Form', $context = '' ) {
		extract( shortcode_atts( array(
			'form_id' => '1',
			'pos' => 'r', // r => right, br => bottom right, bc => bottom center, bl => bottom left, l => left
			'style' => 'rect', // rect => rectangular, circ => circular
			'header' => '%FORM%', // form name
			'subtitle' => '', // Some subtitle
			'icon' => 'fa fa-file-text', // icon class needs to be supplied by vendor
			'width' => '600', // Initial popup width in pixels
			'color' => '#ffffff',
			'bgcolor' => '#3C609E',
		), $args ) );
		$form = self::get_form( $form_id );
		if ( ! $form ) {
			return;
		}
		$form_urls = self::standalone_permalink_parts( $form_id );
		// Config the JS
		$config_json = new stdClass();
		$config_json->label = $content;
		$config_json->color = $color;
		$config_json->bgcolor = $bgcolor;
		$config_json->position = $pos;
		$config_json->formID = $form_id;
		$config_json->style = $style;
		$config_json->header = str_replace( '%FORM%', $form->name, $header );
		$config_json->subtitle = $subtitle;
		$config_json->icon = $icon;
		$config_json->width = $width;
		$config_json->url = $form_urls['url'];
		// Config the PHP
		global $ipt_fsqm_popup_forms, $ipt_eform_popup_config;
		if ( ! is_array( $ipt_fsqm_popup_forms ) ) {
			$ipt_fsqm_popup_forms = array();
		}
		if ( ! isset( $ipt_eform_popup_config ) ) {
			$ipt_eform_popup_config = array();
		}
		$ipt_fsqm_popup_forms[] = $form_id;
		$ipt_eform_popup_config[] = $config_json;
		// Get output
		ob_start();
		?>
<script type="text/javascript">
	if ( window.iptFSQMModalPopupForms == undefined ) {
		window.iptFSQMModalPopupForms = [];
	}
	window.iptFSQMModalPopupForms[window.iptFSQMModalPopupForms.length] = <?php echo json_encode( $config_json ); ?>;
</script>
		<?php
		return ob_get_clean();
	}

	public static function ipt_fsqm_trends_cb( $atts, $content = null, $context = '' ) {
		global $wpdb, $ipt_fsqm_info;

		// Load the default shortcode atts
		$shortcode_config = shortcode_atts( array(
			'form_id' => '0',
			'load' => '1',
			'title' => __( 'Trends', 'ipt_fsqm' ),
			'mcq_ids' => '',
			'mcq_config' => '',
			'freetype_ids' => '',
			'pinfo_ids' => '',
			'pinfo_config' => '',
			'filters' => '',
			'data' => '',
			'appearance' => '',
		), $atts );

		// Backward compatibility
		if ( 'feedback_trend' == $context ) {
			$shortcode_config['form_id'] = isset( $atts['id'] ) ? (int) $atts['id'] : 0;
		}

		// Create the default set of filters
		// and apply if not present in shortcode
		$default_filters = array(
			'users' => 'all',
			'urlTracks' => 'all',
			'mk' => '',
			'mv' => '',
			'smin' => '',
			'smax' => '',
			'dtmin' => '',
			'dtmax' => '',
		);
		if ( '' !== $shortcode_config['filters'] ) {
			$shortcode_config['filters'] = json_decode( $shortcode_config['filters'], true );
			if ( is_null( $shortcode_config['filters'] ) ) {
				$shortcode_config['filters'] = $default_filters;
			}
		} else {
			$shortcode_config['filters'] = $default_filters;
		}

		// Create a front instance
		// For creating the frontend container
		$front = new IPT_FSQM_Form_Elements_Front( null, $shortcode_config['form_id'] );

		// Create a util instance
		// For creating the report output
		$utils = new IPT_FSQM_Form_Elements_Utilities( $shortcode_config['form_id'], $front->ui );

		// Now process and make compatible variable for passing through util class
		// Create settings
		$settings = array(
			'form_id' => $shortcode_config['form_id'],
			'report' => array(),
			'load' => $shortcode_config['load'],
		);

		// Process MCQs
		$mcqs = array();
		if ( isset( $shortcode_config['mcq_ids'] ) && '' !== $shortcode_config['mcq_ids'] ) {
			// Add to the reports
			$settings['report'][] = 'mcq';

			// Generate mcq ids
			if ( 'all' == $shortcode_config['mcq_ids'] ) {
				$mcqs = array_keys( $utils->mcq );
			} else {
				$mcqs = wp_parse_id_list( $shortcode_config['mcq_ids'] );
			}
		}

		// Process Freetypes
		$freetypes = array();
		if ( isset( $shortcode_config['freetype_ids'] ) && '' !== $shortcode_config['freetype_ids'] ) {
			// Add to the reports
			$settings['report'][] = 'freetype';

			// Generate freetype ids
			if ( 'all' == $shortcode_config['freetype_ids'] ) {
				$freetypes = array_keys( $utils->freetype );
			} else {
				$freetypes = wp_parse_id_list( $shortcode_config['freetype_ids'] );
			}
		}

		// Process PInfos
		$pinfos = array();
		if ( isset( $shortcode_config['pinfo_ids'] ) && '' !== $shortcode_config['pinfo_ids'] ) {
			// Add to the reports
			$settings['report'][] = 'pinfo';

			// Generate pinfo ids
			if ( 'all' == $shortcode_config['pinfo_ids'] ) {
				$pinfos = array_keys( $utils->pinfo );
			} else {
				$pinfos = wp_parse_id_list( $shortcode_config['pinfo_ids'] );
			}
		}

		// Get the default chart types and toggles
		$mcqs_in_report = array();
		foreach ( $mcqs as $m_key ) {
			if ( isset( $front->mcq[ $m_key ] ) ) {
				$mcqs_in_report[ $m_key ] = array(
					'type' => $front->mcq[ $m_key ]['type'],
				);
			}
		}
		$pinfos_in_report = array();
		foreach ( $pinfos as $p_key ) {
			if ( isset( $front->pinfo[ $p_key ] ) ) {
				$pinfos_in_report[ $p_key ] = array(
					'type' => $front->pinfo[ $p_key ]['type'],
				);
			}
		}
		$default_chart_n_toggle = self::get_default_chart_n_toggle( $mcqs_in_report, $pinfos_in_report );

		// Deserialize the JSONs of chartconfigs
		if ( '' !== $shortcode_config['mcq_config'] ) {
			$shortcode_config['mcq_config'] = json_decode( $shortcode_config['mcq_config'], true );
			if ( is_null( $shortcode_config['mcq_config'] ) ) {
				$shortcode_config['mcq_config'] = $default_chart_n_toggle['mcq'];
			}
		} else {
			// Backward compatibility
			$shortcode_config['mcq_config'] = $default_chart_n_toggle['mcq'];
		}

		if ( '' !== $shortcode_config['pinfo_config'] ) {
			$shortcode_config['pinfo_config'] = json_decode( $shortcode_config['pinfo_config'], true );
			if ( is_null( $shortcode_config['pinfo_config'] ) ) {
				$shortcode_config['pinfo_config'] = $default_chart_n_toggle['pinfo'];
			}
		} else {
			$shortcode_config['pinfo_config'] = $default_chart_n_toggle['pinfo'];
		}

		// Appearance config
		$rappearance = json_decode( $shortcode_config['appearance'], true );
		if ( is_null( $rappearance ) ) {
			$rappearance = array();
		}
		$rappearance = wp_parse_args( $rappearance, array(
			'block' => true,
			'heading' => true,
			'description' => true,
			'header' => true,
			'border' => true,
			'material' => false,
			'print' => false,
		) );

		$appearance = array(
			'wrap' => ( true == $rappearance['block'] ) ? true : false,
			'heading' => ( true == $rappearance['heading'] ) ? true : false,
			'description' => ( true == $rappearance['description'] ) ? true : false,
			'theader' => ( true == $rappearance['header'] ) ? true : false,
			'tborder' => ( true == $rappearance['border'] ) ? true : false,
			'material' => ( true == $rappearance['material'] ) ? true : false,
			'print' => ( true == $rappearance['print'] ) ? true : false,
		);

		// Data config
		$data = json_decode( $shortcode_config['data'], true );
		if ( is_null( $data ) ) {
			$data = array(
				'data' => true,
				'others' => false,
				'names' => false,
				'date' => false,
			);
		}

		// Chart meta config
		$cmeta = array(
			'mcq' => $shortcode_config['mcq_config'],
			'pinfo' => $shortcode_config['pinfo_config'],
		);

		// Compat the filters
		$filters = array(
			'user_id' => array(),
			'url_track' => array(),
			'meta' => $shortcode_config['filters']['mk'],
			'mvalue' => $shortcode_config['filters']['mv'],
			'score' => array(
				'min' => $shortcode_config['filters']['smin'],
				'max' => $shortcode_config['filters']['smax'],
			),
			'custom_date' => false,
			'custom_date_start' => $shortcode_config['filters']['dtmin'],
			'custom_date_end' => $shortcode_config['filters']['dtmax'],
		);

		// Add user ids
		if ( 'all' == $shortcode_config['filters']['users'] ) {
			$filters['user_id'][] = '';
		} else {
			$filters['user_id'] = wp_parse_id_list( $shortcode_config['filters']['users'] );
			if ( empty( $filters['user_id'] ) ) {
				$filters['user_id'] = array( '' );
			}
		}

		// Query table and check the URL Tracks
		if ( 'all' == $shortcode_config['filters']['urlTracks'] ) {
			$filters['url_track'][] = '';
		} else {
			// Get the URL tracks
			$url_tracks = $wpdb->get_col( sprintf( "SELECT url_track FROM {$ipt_fsqm_info['data_table']} WHERE id IN (%s)", esc_sql( $shortcode_config['filters']['urlTracks'] ) ) );

			if ( empty( $url_tracks ) ) {
				$filters['url_track'][] = '';
			} else {
				foreach ( $url_tracks as $ut ) {
					$filters['url_track'][] = $ut;
				}
			}
		}

		// Custom date
		if ( '' !== $shortcode_config['filters']['dtmin'] || '' !== $shortcode_config['filters']['dtmax'] ) {
			$filters['custom_date'] = true;
		}

		ob_start();
		$front->container( array( array( $utils, 'report_generate_report' ), array( $settings, $mcqs, $freetypes, $pinfos, $data['data'], $data['names'], $data['date'], $data['others'], false, $appearance, $cmeta, $filters, $shortcode_config['title'] ) ), true, true );
		$output = ob_get_clean();

		if ( WP_DEBUG !== true ) {
			require_once IPT_FSQM_Loader::$abs_path . '/lib/classes/class-minify-html.php';
			$output = IPT_FSQM_Minify_HTML::minify( $output );
		}

		return $output;
	}

	public static function ipt_fsqm_form_cb( $args, $content = null ) {
		extract( shortcode_atts( array(
			'id' => '1',
		), $args ) );
		$form = new IPT_FSQM_Form_Elements_Front( null, $id );
		ob_start();
		$form->show_form();
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			require_once IPT_FSQM_Loader::$abs_path . '/lib/classes/class-minify-html.php';
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		return $form_output;
	}

	public static function ipt_fsqm_track_cb( $args, $content = null ) {
		extract( shortcode_atts( array(
					'label' => __( 'Track Code:', 'ipt_fsqm' ),
					'submit' => __( 'Submit', 'ipt_fsqm' ),
				), $args ) );
		$id = isset( $_GET['id'] ) ? $_GET['id'] : false;
		$action = isset( $_GET['action'] ) ? $_GET['action'] : 'show';
		ob_start();
?>
<?php if ( $id == false ) : ?>
<form action="" method="get">
	<?php foreach ( $_GET as $k => $v ) : ?>
	<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
	<?php endforeach; ?>
	<p>
		<label for="id"><?php echo $label; ?></label><br />
		<input type="text" name="id" id="id" value="" style="width: 90%;" />
	</p>
	<p>
		<input type="submit" value="<?php echo $submit; ?>" />
	</p>
</form>
<?php else : ?>
<?php

	switch ( $action ) {
		default :
		case 'show' :
			self::ipt_fsqm_full_preview( self::decrypt( $id ) );
			break;
		case 'edit' :
			self::ipt_fsqm_form_edit( self::decrypt( $id ) );
			break;
		case 'payment' :
			$form = new IPT_FSQM_Form_Elements_Front( self::decrypt( $id ) );
			$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : 'retry';
			$form->container( array( array( __CLASS__, 'ipt_fsqm_handle_payment_tb' ), array( self::decrypt( $id ), $mode, $form ) ), true );
			break;
	}
?>
<?php endif; ?>
		<?php
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			require_once IPT_FSQM_Loader::$abs_path . '/lib/classes/class-minify-html.php';
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		return $form_output;
	}

	public static function ipt_fsqm_handle_payment_tb( $id, $mode, $form ) {
		switch ( $mode ) {
			// If payment retry
			case 'retry' :
				$form->payment_retry();
				break;
			// If came from paypal express checkout
			case 'paypal_e' :
				self::ipt_fsqm_payment_pe_verify( $id , $_GET['psuccess'], $form );
				break;
			// Hooks for others
			default :
				do_action( 'ipt_fsqm_payment_trackback', $id, $mode, $form );
				break;
		}
	}

	public static function ipt_fsqm_utrack_cb( $args, $content = null ) {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$shortcode_settings = shortcode_atts( array(
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'showcategory' => '0',
			'categorylabel' => __( 'Category', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'showremarks' => '0',
			'remarkslabel' => __( 'Remarks', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'material-default',
			'filters' => '0',
			'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
			'logout_r' => '',
		), $args );
		extract( $shortcode_settings );
		$content = shortcode_unautop( $content );
		$showscore = (int) $showscore;
		$show_register = (int) $show_register;
		$show_forgot = (int) $show_forgot;

		if ( $content == null ) {
			$content = __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' );
		}
		$user = wp_get_current_user();
		// We need a $form_element instance for theme management, only the base should do
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$theme_element = $form_element->get_theme_by_id( $theme );
		// Backward compatibility
		$theme = $theme_element['theme_id'];
		// Check the theme and do the UI
		$themes = $form_element->get_available_themes();
		$theme_info = array();
		foreach ( $themes as $the_theme ) {
			if ( isset( $the_theme['ui-class'] ) ) {
				foreach ( array_keys( $the_theme['themes'] ) as $theme_key ) {
					$theme_info[ $theme_key ] = $the_theme['ui-class'];
				}
			}
		}

		if ( isset( $theme_element['ui-class'] ) && class_exists( $theme_element['ui-class'] ) ) {
			$ui = $theme_element[ 'ui-class' ]::instance( 'ipt_fsqm' );
		} else {
			$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
		}
		$redirect = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if ( $logout_r != '' ) {
			$redirect = $logout_r;
		}
		$ui->enqueue( plugins_url( '/lib/', IPT_FSQM_Loader::$abs_file ), IPT_FSQM_Loader::$version );
		ob_start();
		$ui->ajax_loader( false, '', array(), true, __( 'Please wait', 'ipt_fsqm' ) );
		$ajax_loader = ob_get_clean();
		wp_enqueue_style( 'ipt-fsqm-up-yadcf-css', plugins_url( '/lib/css/jquery.dataTables.yadcf.css', IPT_FSQM_Loader::$abs_file ), array(), IPT_FSQM_Loader::$version, 'all' );
		if ( isset( $theme_element['user_portal_css'] ) && ! empty( $theme_element['user_portal_css'] ) ) {
			wp_enqueue_style( 'eform-up', $theme_element['user_portal_css'], array(), IPT_FSQM_Loader::$version );
		} else {
			wp_enqueue_style( 'eform-up', plugins_url( '/static/front/css/user-portal.css', IPT_FSQM_Loader::$abs_file ), array(), IPT_FSQM_Loader::$version );
		}
		wp_enqueue_script( 'ipt-fsqm-up-datatable', plugins_url( '/lib/js/jquery.dataTables.min.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-up-datatable-yadcf', plugins_url( '/lib/js/jquery.dataTables.yadcf.js', IPT_FSQM_Loader::$abs_file ), array( 'ipt-fsqm-up-datatable' ), IPT_FSQM_Loader::$version, true );
		wp_enqueue_script( 'ipt-fsqm-up-script', plugins_url( '/static/front/js/jquery.ipt-fsqm-user-portal.min.js', IPT_FSQM_Loader::$abs_file ), array( 'jquery', 'ipt-fsqm-up-datatable-yadcf' ), IPT_FSQM_Loader::$version, true );
		wp_localize_script( 'ipt-fsqm-up-script', 'iptFSQMUP', array(
			'location' => plugins_url( '/static/front/', IPT_FSQM_Loader::$abs_file ),
			'version' => IPT_FSQM_Loader::$version,
			'l10n' => array(
				'sEmptyTable' => __( 'No submissions yet!', 'ipt_fsqm' ),
				'sInfo' => __( 'Showing _START_ to _END_ of _TOTAL_ entries', 'ipt_fsqm' ),
				'sInfoEmpty' => __( 'Showing 0 to 0 of 0 entries', 'ipt_fsqm' ),
				'sInfoFiltered' => __( '(filtered from _MAX_ total entries)', 'ipt_fsqm' ),
				/* translators: %s will be replaced by an empty string */
				'sInfoPostFix' => sprintf( _x( '%s', 'sInfoPostFix', 'ipt_fsqm' ), '' ),
				/* translators: For thousands separator inside datatables */
				'sInfoThousands' => _x( ',', 'sInfoThousands', 'ipt_fsqm' ),
				'sLengthMenu' => __( 'Show _MENU_ entries', 'ipt_fsqm' ),
				'sLoadingRecords' => $ajax_loader,
				'sProcessing' => $ajax_loader,
				'sSearch' => '',
				'sSearchPlaceholder' => __( 'Search submissions', 'ipt_fsqm' ),
				'sZeroRecords' => __( 'No matching records found', 'ipt_fsqm' ),
				'oPaginate' => array(
					'sFirst' => __( '<i title="First" class="ipticm ipt-icomoon-first"></i>', 'ipt_fsqm' ),
					'sLast' => __( '<i title="Last" class="ipticm ipt-icomoon-last"></i>', 'ipt_fsqm' ),
					'sNext' => __( '<i title="Next" class="ipticm ipt-icomoon-forward4"></i>', 'ipt_fsqm' ),
					'sPrevious' => __( '<i title="Previous" class="ipticm ipt-icomoon-backward3"></i>', 'ipt_fsqm' ),
				),
				'oAria' => array(
					'sSortAscending' => __( ': activate to sort column ascending', 'ipt_fsqm' ),
					'sSortDescending' => __( ': activate to sort column descending', 'ipt_fsqm' ),
				),
				'filters' => array(
					'form' => __( 'Select form to filter', 'ipt_fsqm' ),
					'category' => __( 'Select category to filter', 'ipt_fsqm' ),
				),
			),
			'ajax' => array(
				'null_response' => __( 'Some error occured on the server.', 'ipt_fsqm' ),
				'ajax_error' => __( 'Error occured while fetching the content.', 'ipt_fsqm' ),
				'advice' => __( 'Please refresh this page to try again.', 'ipt_fsqm' ),
			),
			'allLabel' => __( 'All', 'ipt_fsqm' ),
			'allFilter' => __( 'Show all', 'ipt_fsqm' ),
			'dpPlaceholderf' => __( 'From', 'ipt_fsqm' ),
			'dpPlaceholdert' => __( 'To', 'ipt_fsqm' ),
			'sPlaceholder' => __( 'Search', 'ipt_fsqm' ),
		) );

		do_action( 'ipt_fsqm_form_elements_up_enqueue' );

		if ( ! is_user_logged_in() || ! ($user instanceof WP_User) ) {
			$defaults = array(
				'echo' => true,
				'redirect' => $redirect,
				'form_id' => 'ipt_fsqm_up_login',
				'label_username' => __( 'Username' ),
				'label_password' => __( 'Password' ),
				'label_remember' => __( 'Remember Me' ),
				'label_log_in' => __( 'Log In' ),
				'id_username' => 'ipt_fsqm_up_user_name',
				'id_password' => 'ipt_fsqm_up_user_pwd',
				'id_remember' => 'ipt_fsqm_up_rmm',
				'id_submit' => 'wp-submit',
				'remember' => true,
				'value_username' => '',
				'value_remember' => false, // Set this to true to default the "Remember me" checkbox to checked
			);
			$args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );
			$login_buttons = array();
			$login_buttons[] = array(
				__( 'Login', 'ipt_fsqm' ),
				'wp-submit',
				'small',
				'none',
				'normal',
				array(),
				'submit',
				array(),
				array(),
				'',
				'switch',
			);

			if ( $show_register && get_option( 'users_can_register', false ) ) {
				$login_buttons[] = array(
					__( 'Register', 'ipt_fsqm' ),
					'ipt_fsqm_up_reg',
					'small',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_registration_url() . '"' ),
					'',
					'user-2',
				);
			}

			if ( $show_forgot ) {
				$login_buttons[] = array(
					__( 'Forgot Password', 'ipt_fsqm' ),
					'ipt_fsqm_up_rpwd',
					'small',
					'none',
					'normal',
					array(),
					'button',
					array(),
					array( 'onclick' => 'javascript:window.location.href="' . wp_lostpassword_url( $redirect ) . '"' ),
					'',
					'info-2',
				);
			}

			$login_buttons = apply_filters( 'ipt_fsqm_up_filter_login_buttons', $login_buttons );
		} else {
			$total_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );
			$avg_score = $wpdb->get_var( $wpdb->prepare( "SELECT AVG((score/max_score)) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );

			$toolbar_buttons = array();
			$toolbar_buttons[] = array(
				__( 'Logout', 'ipt_fsqm' ),
				'ipt_fsqm_up_logout',
				'',
				'none',
				'normal',
				array(),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . wp_logout_url( $redirect ) . '"' ),
				'',
				'switch',
			);
			$toolbar_buttons = apply_filters( 'ipt_fsqm_up_filter_toolbar', $toolbar_buttons );
		}
		ob_start();
		?>
<div class="ipt_fsqm_user_portal ipt_uif_front ipt_uif_common" data-ui-theme="<?php echo esc_attr( json_encode( $theme_element['include'] ) ); ?>" data-ui-theme-id="<?php echo esc_attr( $theme ); ?>" data-settings="<?php echo esc_attr( json_encode( $shortcode_settings ) ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'ipt_fsqm_up_nonce_' . $user->ID ) ); ?>" data-ajaxurl="<?php echo esc_attr( admin_url( 'admin-ajax.php' ) ); ?>">
	<noscript>
		<div class="ipt_fsqm_form_message_noscript ui-widget ui-widget-content ui-corner-all">
			<div class="ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
				<h3><?php _e( 'Javascript is disabled', 'ipt_fsqm' ); ?></h3>
			</div>
			<div class="ui-widget-content ui-corner-bottom">
				<p><?php _e( 'Javascript is disabled on your browser. Please enable it in order to use this form.', 'ipt_fsqm' ); ?></p>
			</div>
		</div>
	</noscript>
	<?php $ui->ajax_loader( false, '', array(), true, __( 'Loading', 'ipt_fsqm' ), array( 'ipt_uif_init_loader' ) ); ?>
	<div style="opacity: 0" class="ipt_uif_hidden_init ui-widget-content ui-corner-all ipt_uif_up_main_container">
		<?php if ( !is_user_logged_in() || !( $user instanceof WP_User ) ) : ?>
		<?php $ui->divider( $login, 'h3', 'left', 0xe10f, false, array( 'eform-up-login-header' ) ) ?>
		<form action="<?php echo esc_url( site_url( 'wp-login.php', 'login_post' ) ); ?>" name="<?php echo $args['form_id']; ?>" id="<?php echo $args['form_id']; ?>" method="post">
			<?php $ui->login_form( $args, $login_buttons ); ?>
		</form>
		<div class="clear"></div>
		<?php else : ?>

		<div class="ipt_fsqm_user_portal_welcome">
			<?php if ( $avatar !== '' || $avatar !== '0' || $avatar > 0 ) : ?>
			<div class="ipt_fsqm_up_profile">
				<?php echo get_avatar( $user->ID, $avatar ); ?>
			</div>
			<?php endif; ?>
			<div class="ipt_fsqm_up_welcome">
				<?php if ( $title != '' ) : ?>
				<h2><?php echo $title; ?></h2>
				<?php endif; ?>
				<div class="ipt_fsqm_up_msg">
					<?php echo do_shortcode( wpautop( str_replace( '%NAME%', '<strong>' . $user->display_name . '</strong>', $content ) ) ); ?>
				</div>
			</div>
			<div class="clear"></div>
			<div class="ipt_fsqm_up_toolbar">
				<?php $ui->buttons( $toolbar_buttons ); ?>
				<div class="ipt_fsqm_up_meta">
					<h6><?php $ui->print_icon_by_class( 'drawer2', false ); ?><?php printf( _n( '%d Submission', '%d Submissions', $total_count, 'ipt_fsqm' ), $total_count ); ?></h6>
					<?php if ( $showscore == '1' ) : ?>
					<h6><?php $ui->print_icon_by_class( 'quill', false ); ?><?php printf( __( '%2$s%% Average %1$s', 'ipt_fsqm' ), $scorelabel, number_format_i18n( $avg_score * 100, 2 ) ); ?></h6>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php $ui->progressbar( '', 0, array( 'ipt_fsqm_up_pb' ) ); ?>
		<?php $ui->ajax_loader( false, '', array(), true, __( 'Fetching Data', 'ipt_fsqm' ), array( 'ipt_fsqm_up_al' ) ); ?>
		<table class="ipt_fsqm_up_table" style="display: none">
			<thead>
			<?php if ( $filters == '0' ) : ?>
				<tr>
					<th class="form_label" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showcategory == '1' ) : ?>
					<th class="category_label"><?php echo $categorylabel; ?></th>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<th class="admin_remarks" scope="col"><?php echo $remarkslabel; ?></th>
					<?php endif; ?>
					<th class="action_label" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
			<?php else : ?>
				<tr>
					<th class="form_filter filter_th" scope="col"></th>
					<th class="date_filter filter_th"></th>
					<?php if ( $showcategory == '1' ) : ?>
						<th class="category_filter filter_th"></th>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label" rowspan="2" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label" rowspan="2" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label" rowspan="2" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<th class="admin_remarks" rowspan="2" scope="col"><?php echo $remarkslabel; ?></th>
					<?php endif; ?>
					<th class="action_label" rowspan="2" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
				<tr>
					<th class="form_label" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showcategory == '1' ) : ?>
					<th class="category_label"><?php echo $categorylabel; ?></th>
					<?php endif; ?>
				</tr>
			<?php endif; ?>
			</thead>
			<tbody>
				<tr>
					<td><?php echo $nosubmission; ?></td>
					<td></td>
					<?php if ( $showcategory == '1' ) : ?>
					<td></td>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<td></td>
					<td></td>
					<td></td>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<td></td>
					<?php endif; ?>
					<td></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<th class="form_label" scope="col"><?php echo $formlabel; ?></th>
					<th class="date_label" scope="col"><?php echo $datelabel; ?></th>
					<?php if ( $showcategory == '1' ) : ?>
					<th class="category_label"><?php echo $categorylabel; ?></th>
					<?php endif; ?>
					<?php if ( $showscore == '1' ) : ?>
					<th class="score_label" scope="col"><?php echo $scorelabel; ?></th>
					<th class="mscore_label" scope="col"><?php echo $mscorelabel; ?></th>
					<th class="pscore_label" scope="col"><?php echo $pscorelabel; ?></th>
					<?php endif; ?>
					<?php if ( '1' == $showremarks ) : ?>
						<th class="admin_remarks" scope="col"><?php echo $remarkslabel; ?></th>
					<?php endif; ?>
					<th class="action_label" scope="col"><?php echo $actionlabel; ?></th>
				</tr>
			</tfoot>
		</table>
		<?php endif; ?>
	</div>
</div>
		<?php
		$form_output = ob_get_clean();
		if ( WP_DEBUG !== true ) {
			require_once IPT_FSQM_Loader::$abs_path . '/lib/classes/class-minify-html.php';
			$form_output = IPT_FSQM_Minify_HTML::minify( $form_output );
		}
		return $form_output;
	}

	public static function user_portal_init() {
		// Just for the logged in users, so no need nopriv
		add_action( 'wp_ajax_ipt_fsqm_user_portal', array( __CLASS__, 'user_portal_ajax_response' ) );
	}

	public static function user_portal_ajax_response() {
		global $wpdb, $ipt_fsqm_info;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Get post variables
		$settings = isset( $_POST['settings'] ) ? (array) $_POST['settings'] : array();
		$nonce = isset( $_POST['_wpnonce'] ) ? $_POST['_wpnonce'] : '';
		$user = wp_get_current_user();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;

		// Parse the settings - Basically the shortcode atts
		$settings = wp_parse_args( $settings, array(
			'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
			'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
			'show_register' => '1',
			'show_forgot' => '1',
			'formlabel' => __( 'Form', 'ipt_fsqm' ),
			'showcategory' => '0',
			'categorylabel' => __( 'Category', 'ipt_fsqm' ),
			'datelabel' => __( 'Date', 'ipt_fsqm' ),
			'showscore' => '1',
			'scorelabel' => __( 'Score', 'ipt_fsqm' ),
			'mscorelabel' => __( 'Max', 'ipt_fsqm' ),
			'pscorelabel' => __( '%-age', 'ipt_fsqm' ),
			'showremarks' => '0',
			'remarkslabel' => __( 'Remarks', 'ipt_fsqm' ),
			'linklabel' => __( 'View', 'ipt_fsqm' ),
			'actionlabel' => __( 'Action', 'ipt_fsqm' ),
			'editlabel' => __( 'Edit', 'ipt_fsqm' ),
			'avatar' => '96',
			'theme' => 'material-default',
			'title' => __( 'eForm User Portal', 'ipt_fsqm' ),
		) );

		// Check for authenticity
		if ( ! is_user_logged_in() || ! ( $user instanceof WP_User ) ) {
			echo json_encode( array(
				'success' => false,
				'error_msg' => __( 'You need to be logged in', 'ipt_fsqm' ),
			) );
			die();
		}

		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_up_nonce_' . $user->ID ) ) {
			echo json_encode( array(
				'success' => false,
				'error_msg' => __( 'Invalid Nonce. Cheating?', 'ipt_fsqm' ),
			) );
			die();
		}

		// Prepare the return
		$return = array(
			'success' => true,
			'html' => '',
			'done' => 0,
		);

		// Prepare the db variables
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d", $user->ID ) );
		$per_page = 100;
		$start_page = $doing * $per_page;
		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE user_id = %d ORDER BY date DESC LIMIT %d, %d", $user->ID, $start_page, $per_page ) );
		$data = new IPT_FSQM_Form_Elements_Front();

		// Prepare the UI
		$ui = $data->ui;

		// Check for empty
		if ( empty( $data_ids ) ) {
			$return['html'] .= '';
			$return['done'] = 100;
			echo json_encode( $return );
			die();
		}

		// Loop through and add the html
		foreach ( $data_ids as $id ) {
			$data->init( $id );
			$action_buttons = array();
			$action_buttons[] = array(
				$settings['linklabel'],
				'',
				'medium',
				'secondary',
				'normal',
				array( 'ipt_fsqm_up_tb' ),
				'anchor',
				array(),
				array(),
				$data->get_trackback_url(),
				'newspaper',
			);
			if ( $data->can_user_edit() ) {
				$action_buttons[] = array(
					$settings['editlabel'],
					'',
					'medium',
					'secondary',
					'normal',
					array( 'ipt_fsqm_up_edit' ),
					'anchor',
					array(),
					array(),
					$data->get_edit_url(),
					'pencil',
				);
			}
			$action_buttons = apply_filters( 'ipt_fsqm_up_filter_action_button', $action_buttons, $data );

			$return['html'] .= '<tr>';
			$return['html'] .= '<td class="form_label" data-search="' . esc_attr( $data->name ) . '">' . '<span class="data-id">' . $data->data_id . '</span> ' . $data->name . '</td>';
			$return['html'] .= '<td class="date_label" data-search="' . strtotime( $data->data->date ) . '" data-order="' . strtotime( $data->data->date ) . '">' . date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data->data->date ) ) . '</td>';
			if ( $settings['showcategory'] == '1' ) {
				$category = IPT_FSQM_Form_Elements_Static::get_category( $data->category );
				if ( $category != null ) {
					$return['html'] .= '<td class="category_label" data-search="' . esc_attr( $category->name ) .'">' . $category->name . '</td>';
				} else {
					$return['html'] .= '<td class="category_label" data-search="' . __( 'None', 'ipt_fsqm' ) . '">' . __( 'None', 'ipt_fsqm' ) . '</td>';
				}

			}
			if ( $settings['showscore'] == '1' ) {
				$return['html'] .= '<td class="score_label ipt_fsqm_up_number" data-score="' . $data->data->score . '">' . number_format_i18n( $data->data->score, 2 ) . '</td>';
				$return['html'] .= '<td class="mscore_label ipt_fsqm_up_number" data-max-score="' . $data->data->max_score . '">' . number_format_i18n( $data->data->max_score, 2 ) . '</td>';
				$percentage = 0;
				if ( $data->data->max_score != 0 ) {
					$percentage = $data->data->score * 100 / $data->data->max_score;
				}
				$return['html'] .= '<td class="pscore_label ipt_fsqm_up_number" data-percentage="' . round( $percentage, 2 ) . '">' . number_format_i18n( $percentage, 2 ) . _x( '%', 'fsqm.UP.Percentage', 'ipt_fsqm' ) . '</td>';
			}
			if ( '1' == $settings['showremarks'] ) {
				$return['html'] .= '<td class="admin_remarks">' . $data->data->comment . '</td>';
			}
			ob_start();
			$ui->buttons( $action_buttons );
			$buttons = ob_get_clean();
			$return['html'] .= '<td class="action_label">' . $buttons . '</td>';
			$return['html'] .= '</tr>';
		}

		$done_till_now = $doing * $per_page + $per_page;
		if ( $done_till_now >= $total ) {
			$return['done'] = 100;
		} else {
			$return['done'] = (float) $done_till_now * 100 / $total;
		}

		echo json_encode( $return );
		die();
	}

	/*==========================================================================
	 * Quick Preview
	 *========================================================================*/
	public static function ipt_fsqm_quick_preview() {
		add_action( 'wp_ajax_ipt_fsqm_quick_preview', array( __CLASS__, 'ipt_fsqm_quick_preview_cb' ) );
	}

	public static function ipt_fsqm_quick_preview_cb() {
		$data_id = $_REQUEST['id'];
		$preview = new IPT_FSQM_Form_Elements_Data( $data_id );
		if ( $preview->data_id == null ) {
			echo 'Invalid ID';
			die();
		}
?>
<script type="text/javascript">
jQuery(document).ready(function($) {
	$('#ipt_fsqm_quick_preview_print').click(function(){
		$('#ipt_fsqm_quick_preview').printElement({
			leaveOpen:true,
			printMode:'popup'
		});
	});
});
</script>
<div style="text-align: center; margin: 10px">
	<a id="ipt_fsqm_quick_preview_print" class="button-primary"><?php _e( 'Print', 'ipt_fsqm' ); ?></a>
</div>
<div id="ipt_fsqm_quick_preview">
	<?php $preview->show_quick_preview( false, true, false, true ); ?>
</div>
		<?php
		die();
	}

	/*==========================================================================
	 * TrackBack and Admin Preview
	 *========================================================================*/
	public static function ipt_fsqm_full_preview_cb( $form, $score ) {
		// No need to show if not admin and submission locked
		if ( ! is_admin() && $form->get_submission_lock_status() ) {
			$callback = array( array( $form->ui, 'msg_error' ) );
			$format_string_components = $form->get_format_string();
			$callback[1] = array(
				0 => str_replace( array_keys( $format_string_components ), array_values( $format_string_components ), wpautop( $form->settings['payment']['lock_message'] ) ),
				1 => true,
				2 => __( 'Please complete the payment', 'ipt_fsqm' ),
			);
			$form->container( $callback, false );
			return;
		}

		// Proceed as usual
		$buttons = array();
		if ( true == $form->settings['trackback']['show_print'] ) {
			$buttons[] = array(
				__( 'Print', 'ipt_fsqm' ),
				'ipt_fsqm_report_print_' . $form->form_id,
				'medium',
				'secondary',
				'normal',
				array( 'ipt_uif_printelement' ),
				'button',
				array( 'printid' => 'ipt_fsqm_score_data_' . $form->data_id ),
				array(),
				'',
				'print',
			);
		}

		if ( $form->can_user_edit() && ! is_admin() ) {
			$buttons[] = array(
				__( 'Edit', 'ipt_fsqm' ),
				'ipt_fsqm_report_print_' . $form->form_id,
				'medium',
				'secondary',
				'normal',
				array( 'ipt_uif_printelement' ),
				'button',
				array(),
				array( 'onclick' => 'javascript:window.location.href="' . $form->get_edit_url() . '"' ),
				'',
				'pencil',
			);
		}
		$buttons = apply_filters( 'ipt_fsqm_filter_static_report_print', $buttons, $form, $score );
?>
<div class="ipt_uif_front ipt_uif_common">
	<div class="ipt_uif_mother_wrap ui-widget-content ui-widget-default">
		<?php if ( $form->settings['trackback']['show_full'] == true ) : ?>
		<div id="ipt_fsqm_submission_data_<?php echo $form->data_id; ?>" class="ipt_uif_column ipt_uif_column_full">
			<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_sb">
				<div class="ipt_uif_column_inner">
					<?php $form->ui->heading( $form->settings['trackback']['full_title'], 'h2', 'left', 0xe020, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
				</div>
			</div>
			<?php $form->ui->clear(); ?>
			<?php $form->show_form( false, false, 0, false ); ?>
		</div>
		<?php endif; ?>

		<?php $form->ui->clear(); ?>

		<?php if ( $score->settings['social']['show'] == true ) : ?>
		<div class="ipt_fsqm_social_share" style="margin: 10px auto;">
			<?php echo $score->social_share_buttons( false ); ?>
		</div>
		<?php $form->ui->clear(); ?>
		<?php endif; ?>

		<?php if ( $form->settings['trackback']['show_print'] == true ) : ?>
		<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_print">
			<div class="ipt_uif_column_inner">
				<?php $form->ui->heading( $form->settings['trackback']['print_title'], 'h2', 'left', 0xe08a, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
			</div>
		</div>
		<?php $form->ui->clear(); ?>

		<div id="ipt_fsqm_score_data_<?php echo $form->data_id; ?>" class="ipt_uif_column ipt_uif_column_full ipt_fsqm_trackback_full_preview">
			<?php $score->show_quick_preview( false, true, false, is_admin() ); ?>
		</div>

		<?php $form->ui->clear(); ?>
		<?php endif; ?>

		<?php
		if ( $form->settings['trackback']['show_full'] == false && $form->settings['trackback']['show_print'] == false ) {
			if ( current_user_can( 'manage_feedback' ) ) {
				echo '<p>' . __( 'Both Full Preview and Print & Summary are hidden. Please show at least one of them.', 'ipt_fsqm' ) . '</p>';
			} else {
				echo '<p>' . __( 'Sorry, the information you are looking for, is not available right now.', 'ipt_fsqm' ) . '</p>';
			}
		}
		?>

		<?php
		if ( ! empty( $buttons ) ) {
			$form->ui->buttons( $buttons, '', 'align-center' );
		}
		?>
		<?php $form->ui->clear(); ?>

		<?php if ( $form->settings['trackback']['show_trends'] == true ) : ?>
		<div class="ipt_uif_column ipt_uif_column_full ipt_fsqm_main_heading_column ipt_fsqm_full_preview_print" style="margin-top: 20px; margin-bottom: 20px;">
			<div class="ipt_uif_column_inner">
				<?php $form->ui->heading( $form->settings['trackback']['trends_title'], 'h2', 'left', 0xe0ba, false, false, array( 'ipt_fsqm_main_heading' ) ); ?>
			</div>
		</div>
		<?php $form->ui->clear(); ?>
		<div class="ipt_fsqm_tb_trends">
			<?php echo do_shortcode( '[ipt_fsqm_trends form_id="' . $form->form_id . '" mcq_ids="all" data=\'{"data":false,"others":false,"names":false,"date":false}\' appearance=\'{"block":false,"heading":true,"description":false,"header":false,"border":false,"material":false,"print":false}\']' ); ?>
			<?php $form->ui->clear(); ?>
		</div>
		<?php endif; ?>
	</div>
	<?php $form->ui->clear(); ?>
</div>
<?php if ( $form->settings['trackback']['show_trends'] == true ) : ?>

<?php endif; ?>
		<?php
	}

	public static function ipt_fsqm_full_preview( $id ) {
		$form = new IPT_FSQM_Form_Elements_Front( $id );
		if ( $form->form_id == null ) {
			$param = array( __( 'The ID you have provided is either invalid or has been deleted. Please go back and try again.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		$score = new IPT_FSQM_Form_Elements_Data( $id );
		// $form->settings['type_specific']['normal']['wrapper'] = false;
		$form->type = 0;
		$form->container( array( array( __CLASS__, 'ipt_fsqm_full_preview_cb' ), array( $form, $score ) ), true, true, 'ipt-eform-fullview' );
	}

	public static function ipt_fsqm_form_edit( $id ) {
		$form = new IPT_FSQM_Form_Elements_Front( $id );
		if ( $form->form_id == null ) {
			$param = array( __( 'The ID you have provided is either invalid or has been deleted. Please go back and try again.', 'ipt_fsqm' ), true, __( 'Invalid ID', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		if ( $form->can_user_edit() == false ) {
			$param = array( __( 'Invalid request. You can not edit this submission. If you were expecting this, then please contact the administrator of this website.', 'ipt_fsqm' ), true, __( 'Error', 'ipt_fsqm' ) );
			$form->container( array( array( $form->ui, 'msg_error' ), $param ), true );
			return;
		}
		$form->show_form( true, false, null, true, true );
	}

	/*==========================================================================
	 * Payment Related Tasks
	 *========================================================================*/
	public static function ipt_fsqm_payment_pe_verify( $id, $status, $form ) {
		global $ipt_fsqm_info, $wpdb;

		// Get the data from payment table
		$payment_data = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $id ) );

		// Throw an error if no data was found
		if ( is_null( $payment_data ) ) {
			$form->ui->msg_error( __( 'Could not find payment information in the database. Please contact administrator.', 'ipt_fsqm' ), true, __( 'An error has occured', 'ipt_fsqm' ) );
			return;
		}

		// Check if it is a retry
		$retry = false;

		if ( 0 != $payment_data->status ) {
			$retry = true;
		}

		$invoiceid = str_replace( '{id}', $payment_data->id, $form->settings['payment']['invoicenumber'] );
		// Create a fakish payment status
		// for passing to the email function
		$payment_status = array(
			'needed' => true,
			'success' => false,
			'redirect_url' => false,
			'invoice' => $invoiceid,
		);
		$cancelled = false;

		// Data was found, now process it
		// If it was a success
		if ( $status == 'true' ) {
			$apiContext = $form->get_paypal_api_context();
			$payment_id = $_GET['paymentId'];
			$payer_id = $_GET['PayerID'];
			$result = self::ipt_fsqm_execute_paypal_e( $payment_data, $payment_id, $payer_id, $apiContext );
			// If it was successful
			if ( is_object( $result ) && $result->getState() == 'approved' ) {
				$payment_status['success'] = true;
				// Prepare the new db data
				$pdata_update = array(
					'status' => 1, // Approved and processed payment
					'txn' => $result->getId(),
					'meta' => maybe_serialize( array(
						'create_time' => $result->getCreateTime(),
						'update_time' => $result->getUpdateTime(),
						'intent' => $result->getIntent(),
					) ),
				);
				$wpdb->update( $ipt_fsqm_info['payment_table'], $pdata_update, array(
					'id' => $payment_data->id,
				), array(
					'%d', '%s', '%s',
				), '%d' );
				// Update the paid flag
				$form->set_paid_status( 1 );
				// Process the success message
				// This would resolve #305
				// @link https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/305
				$payment_success_msg = $form->settings['payment']['success_msg'];
				$format_strings = $form->get_format_string();
				$payment_success_msg = str_replace( array_keys( $format_strings ), array_values( $format_strings ), $payment_success_msg );
				$form->ui->msg_okay( $payment_success_msg, true, __( 'Payment received.', 'ipt_fsqm' ) );
				$form->ui->container( array( array( $form, 'get_transaction_status' ), array( false, true ) ), __( 'Transaction Status', 'ipt_fsqm' ), 'paypal' );
			} else {
				// An error has occured
				// Show retry
				if ( $payment_data->status != 1 ) {
					$wpdb->update( $ipt_fsqm_info['payment_table'], array(
						'status' => 3, // 3 - Paypal unapproved/error
					), array(
						'id' => $payment_data->id,
					), '%d', '%d' );
				}
				$form->ui->msg_error( $form->settings['payment']['error_msg'], true, __( 'Payment Processing Error', 'ipt_fsqm' ) );
				$form->payment_retry();
			}

		} else {
			$cancelled = true;
			// Just show a message and the resubmit form
			// Also update the status to cancelled
			if ( $payment_data->status != 1 ) {
				$wpdb->update( $ipt_fsqm_info['payment_table'], array(
					'status' => 2, // User cancelled
				), array(
					'id' => $payment_data->id,
				), '%d', '%d' );
			}
			$form->ui->msg_error( $form->settings['payment']['cancel_msg'], true, __( 'Payment Processing Error', 'ipt_fsqm' ) );
			$form->payment_retry();
		}
		// Send the email
		$data = new IPT_FSQM_Form_Elements_Data( $form->data_id );
		$data->send_payment_email( $payment_status, false, $cancelled, 'paypal_e' );
		// Send the notification email
		$data->send_user_notification_email();
		// Send payment admin email
		// Also pass in the retry
		// Resolves @link {https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/286#reported-bugs}
		$data->send_payment_admin_email( $payment_status, '', $retry );
	}

	public static function ipt_fsqm_execute_paypal_e( $payment_data, $payment_id, $payer_id, $apiContext ) {
		// Load the autoloader
		require_once IPT_FSQM_Loader::$abs_path . '/payments/PayPal-PHP-SDK/autoload.php';

		return ipt_fsqm_paypal_e_execute_payment( $apiContext, $payment_id, $payer_id, $payment_data );
	}

	public static function ipt_fsqm_get_payment_gateways() {
		$gateways = array(
			'paypal_d' => __( 'Direct Payout from PayPal', 'ipt_fsqm' ),
			'paypal_e' => __( 'PayPal Express Checkout', 'ipt_fsqm' ),
			'stripe' => __( 'Direct Payout from Stripe', 'ipt_fsqm' ),
		);
		return apply_filters( 'ipt_fsqm_payment_gateways', $gateways );
	}

	public static function ipt_fsqm_get_payment_status() {
		$payment_status = array(
			0 => __( 'Unpaid', 'ipt_fsqm' ),
			1 => __( 'Paid', 'ipt_fsqm' ),
			2 => __( 'Cancelled', 'ipt_fsqm' ),
			3 => __( 'Unsuccessful', 'ipt_fsqm' ),
		);
		return apply_filters( 'ipt_fsqm_payment_status', $payment_status );
	}

	public static function ipt_fsqm_sync_payments( $data_id ) {
		global $wpdb, $ipt_fsqm_info;
		$delete_ids = implode( ',', (array) $data_id );
		$wpdb->query( "DELETE FROM {$ipt_fsqm_info['payment_table']} WHERE data_id IN ({$delete_ids})" );
	}

	public static function payment_sync_init() {
		add_action( 'ipt_fsqm_submissions_deleted', array( __CLASS__, 'ipt_fsqm_sync_payments' ), 10, 1 );
	}



	/*==========================================================================
	 * Save a data
	 *========================================================================*/
	public static function ipt_fsqm_save_form() {
		add_action( 'wp_ajax_ipt_fsqm_save_form', array( __CLASS__, 'ipt_fsqm_save_form_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_save_form', array( __CLASS__, 'ipt_fsqm_save_form_cb' ) );
		add_action( 'wp_ajax_ipt_fsqm_refresh_nonce', array( __CLASS__, 'ipt_fsqm_form_refresh_nonce' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_refresh_nonce', array( __CLASS__, 'ipt_fsqm_form_refresh_nonce' ) );
		add_action( 'wp_ajax_ipt_fsqm_retry_payment', array( __CLASS__, 'ipt_fsqm_payment_retry_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_retry_payment', array( __CLASS__, 'ipt_fsqm_payment_retry_cb' ) );
	}

	public static function ipt_fsqm_form_refresh_nonce() {
		global $wpdb, $ipt_fsqm_info;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : null;
		$data_id = isset( $_POST['data_id'] ) ? (int) $_POST['data_id'] : null;
		$return = array(
			'success' => false,
			'save_nonce' => '',
			'edit_nonce' => '',
		);

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$admin_update = false;
		$user_update = false;

		// if data id present then this can be either from admin or user
		$user_edit = false;
		if ( $data_id !== null ) {
			$user_edit = isset( $_POST['user_edit'] ) && $_POST['user_edit'] == '1' ? true : false;
		}

		// if from user then check the nonce
		if ( $user_edit ) {
			$user_update = true;
		} else {
			// Maybe Admin request
			// Check for user capability
			if ( $data_id !== null && ( !is_admin() || !current_user_can( 'manage_feedback' ) ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$admin_update = true;
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		if ( $data_id !== null ) {
			$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
			if ( $form_id != $data_id_check ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
		}

		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		// But again check for the user edit capability
		if ( $user_edit && $form_data->can_user_edit() !== true ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		// At this point, everything is just fine
		$return['success'] = true;
		$return['save_nonce'] = wp_create_nonce( 'ipt_fsqm_form_data_save_' . $form_id );
		if ( $data_id !== null ) {
			$return['edit_nonce'] = wp_create_nonce( 'ipt_fsqm_user_edit_' . $data_id );
		}
		echo json_encode( (object) $return );
		die();
	}


	public static function ipt_fsqm_payment_retry_cb() {
		global $wpdb, $ipt_fsqm_info;
		$post_data = wp_unslash( $_POST );
		$post_data_raw = $_POST;
		if (  isset( $post_data['ipt_ps_send_as_str'] ) && $post_data['ipt_ps_send_as_str'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$parse_post = array();
			IPT_FSQM_Form_Elements_Static::safe_parse_str( $post_data[$post_data['ipt_ps_look_into']], $parse_post );
			if ( get_magic_quotes_gpc() ) {
				$parse_post = array_map( 'stripslashes_deep', $parse_post );
			}
			$post_data = $parse_post;
		} else if ( isset( $post_data['ipt_ps_send_as_json'] ) && $post_data['ipt_ps_send_as_json'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$json_post = json_decode( $post_data[$post_data['ipt_ps_look_into']], true, 1024 );
			if ( json_last_error() == JSON_ERROR_SYNTAX ) {
				$json_post = json_decode( $post_data_raw[$post_data['ipt_ps_look_into']], true, 1024 );
			}
			$post_data = $json_post;
		}
		$form_id = (int) $post_data['form_id'];
		$data_id = isset( $post_data['data_id'] ) ? (int) $post_data['data_id'] : null;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Check for valid nonce
		if ( ! isset( $post_data['ipt_fsqm_form_data_payment_retry'] ) || ! wp_verify_nonce( $post_data['ipt_fsqm_form_data_payment_retry'], 'ipt_fsqm_form_data_payment_retry_' . $form_id ) ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
		if ( $form_id != $data_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}


		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		$return = $form_data->retry_payment();
		echo json_encode( (object) $return );
		die();
	}


	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public static function ipt_fsqm_save_form_cb() {
		global $wpdb, $ipt_fsqm_info;
		$post_data = wp_unslash( $_POST );
		$post_data_raw = $_POST;
		if (  isset( $post_data['ipt_ps_send_as_str'] ) && $post_data['ipt_ps_send_as_str'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$parse_post = array();
			IPT_FSQM_Form_Elements_Static::safe_parse_str( $post_data[$post_data['ipt_ps_look_into']], $parse_post );
			if ( get_magic_quotes_gpc() ) {
				$parse_post = array_map( 'stripslashes_deep', $parse_post );
			}
			$post_data = $parse_post;
		} else if ( isset( $post_data['ipt_ps_send_as_json'] ) && $post_data['ipt_ps_send_as_json'] == 'true' && isset( $post_data['ipt_ps_look_into'] ) ) {
			$json_post = json_decode( $post_data[$post_data['ipt_ps_look_into']], true, 1024 );
			if ( json_last_error() == JSON_ERROR_SYNTAX ) {
				$json_post = json_decode( $post_data_raw[$post_data['ipt_ps_look_into']], true, 1024 );
			}
			$post_data = $json_post;
		}
		$form_id = (int) $post_data['form_id'];
		$data_id = isset( $post_data['data_id'] ) ? (int) $post_data['data_id'] : null;
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		$admin_update = false;
		$user_update = false;

		// if data id present then this can be either from admin or user
		$user_edit = false;
		if ( $data_id !== null ) {
			$user_edit = isset( $post_data['user_edit'] ) && $post_data['user_edit'] == '1' ? true : false;
		}

		// if from user then check the nonce
		if ( $user_edit ) {
			if ( ! isset( $post_data['ipt_fsqm_user_edit_nonce'] ) || ! wp_verify_nonce( $post_data['ipt_fsqm_user_edit_nonce'], 'ipt_fsqm_user_edit_' . $data_id ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$user_update = true;
		} else {
			// Maybe Admin request
			// Check for user capability
			if ( $data_id !== null && ( !is_admin() || !current_user_can( 'manage_feedback' ) ) ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
			$admin_update = true;
		}



		//Check for nonce
		$wpnonce = $post_data['ipt_fsqm_form_data_save'];
		if ( !wp_verify_nonce( $wpnonce, 'ipt_fsqm_form_data_save_' . $form_id ) ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid nonce. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		//Check for validity of form_id
		$form_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id=%d", $form_id ) );
		if ( null === $form_id_check ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid form. Cheating?', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}
		// Check for validity of data_id
		if ( $data_id !== null ) {
			$data_id_check = $wpdb->get_var( $wpdb->prepare( "SELECT form_id FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $data_id ) );
			if ( $form_id != $data_id_check ) {
				$return = array(
					'success' => false,
					'errors' => array(
						0 => array(
							'id' => '',
							'msgs' => array( __( 'Invalid data. Cheating?', 'ipt_fsqm' ) ),
						),
					),
				);
				echo json_encode( (object) $return );
				die();
			}
		}

		// All set now instantiate and save and return
		$form_data = new IPT_FSQM_Form_Elements_Data( $data_id, $form_id );

		// But again check for the user edit capability
		if ( $user_edit && $form_data->can_user_edit() !== true ) {
			$return = array(
				'success' => false,
				'errors' => array(
					0 => array(
						'id' => '',
						'msgs' => array( __( 'Invalid request.', 'ipt_fsqm' ) ),
					),
				),
			);
			echo json_encode( (object) $return );
			die();
		}

		$return = $form_data->save_form( $admin_update, $user_update );
		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Report Generator
	 *========================================================================*/
	public static function ipt_fsqm_report() {
		add_action( 'wp_ajax_ipt_fsqm_report', array( __CLASS__, 'ipt_fsqm_report_cb' ) );
		add_action( 'wp_ajax_nopriv_ipt_fsqm_report', array( __CLASS__, 'ipt_fsqm_report_cb' ) );
	}

	public static function ipt_fsqm_report_cb() {
		global $wpdb, $ipt_fsqm_info, $ipt_fsqm_settings;
		$settings = isset( $_POST['settings'] ) ? $_POST['settings'] : array();
		// $survey = isset( $_POST['survey'] ) ? $_POST['survey'] : array();
		// $feedback = isset( $_POST['feedback'] ) ? $_POST['feedback'] : array();
		$doing = isset( $_POST['doing'] ) ? (int) $_POST['doing'] : 0;
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;
		$do_data = isset( $_POST['do_data'] ) && 'true' == $_POST['do_data'] ? true : false;
		$do_names = isset( $_POST['do_names'] ) && 'true' == $_POST['do_names'] ? true : false;
		$do_others = isset( $_POST['do_others'] ) && 'true' == $_POST['do_others'] ? true : false;
		$sensitive_data = isset( $_POST['sensitive_data'] ) && 'true' == $_POST['sensitive_data'] ? true : false;
		$do_date = isset( $_POST['do_date'] ) && 'true' == $_POST['do_date'] ? true : false;
		$query_elements = isset( $_POST['query_elements'] ) ? (array) $_POST['query_elements'] : array();
		$query_elements = wp_parse_args( $query_elements, array(
			'mcqs' => array(),
			'freetypes' => array(),
			'pinfos' => array(),
		) );
		$filters = isset( $_POST['filters'] ) ? wp_unslash( $_POST['filters'] ) : array();

		$debug_info = array();

		// Check general nonce
		if ( ! wp_verify_nonce( $_POST['wpnonce'], 'ipt_fsqm_report_ajax_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' none' );
		}

		// Verify do_data nonce for showing data alongside mcqs
		if ( $do_data && ! wp_verify_nonce( $_POST['do_data_nonce'], 'ipt_fsqm_report_ajax_do_data_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_data' );
		}

		// Verify do_names nonce for showing names to mcq meta and freetype
		if ( $do_names && ! wp_verify_nonce( $_POST['do_names_nonce'], 'ipt_fsqm_report_ajax_do_names_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_names' );
		}

		// Verify do_others for populating meta entries
		if ( $do_others && ! wp_verify_nonce( $_POST['do_others_nonce'], 'ipt_fsqm_report_ajax_do_others_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_others' );
		}

		// If sensitive data is enabled, then must verify the nonce and also current user should be able to manage_feedback
		if ( $sensitive_data && ( ! wp_verify_nonce( $_POST['sensitive_data_nonce'], 'ipt_fsqm_report_ajax_sensitive_data_' . $form_id ) || ! current_user_can( 'manage_feedback' ) ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' sensitive_data' );
		}

		// Verify do_date for populating meta and freetype entries
		if ( $do_date && ! wp_verify_nonce( $_POST['do_date_nonce'], 'ipt_fsqm_report_ajax_do_date_' . $form_id ) ) {
			die( __( 'Cheatin&#8217; uh?' ) . ' do_date' );
		}

		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		$return = array(
			'type' => 'success',
			'done' => '0',
			'survey' => array(),
			'feedback' => array(),
			'pinfo' => array(),
			'form_id' => $form_id,
			'debug_info' => array(),
		);

		// First test the form_id
		if ( null == $wpdb->get_var( $wpdb->prepare( "SELECT name FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) ) ) {
			die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Calculate the number of data to fetch
		$per_page = 15;
		if ( isset( $settings['load'] ) ) {
			switch ( $settings['load'] ) {
			case '1' :
				$per_page = 30;
				break;
			case '2' :
				$per_page = 50;
			}
		}

		$where = '';
		$where_arr = array();
		// Check for date filter
		if ( isset( $filters['custom_date'] ) && 'true' == $filters['custom_date'] ) {
			if ( isset( $filters['custom_date_start'] ) && $filters['custom_date_start'] != '' ) {
				$where_arr[] = $wpdb->prepare( 'date >= %s', date( 'Y-m-d H:i:s', strtotime( $filters['custom_date_start'] ) ) );
				$debug_info['ds'] = $filters['custom_date_start'];
			}

			if ( isset( $filters['custom_date_end'] ) && $filters['custom_date_end'] != '' ) {
				$where_arr[] = $wpdb->prepare( 'date <= %s', date( 'Y-m-d H:i:s', strtotime( $filters['custom_date_end'] ) ) );
				$debug_info['de'] = $filters['custom_date_end'];
			}
		}

		// Check for score filter
		if ( isset( $filters['score'] ) && is_array( $filters['score'] ) ) {
			// Minimum filter
			if ( isset( $filters['score']['min'] ) && '' != $filters['score']['min'] ) {
				$where_arr[] = $wpdb->prepare( 'score >= %f', $filters['score']['min'] );
				$debug_info['smin'] = $filters['score']['min'];
			}
			// Maximum filter
			if ( isset( $filters['score']['max'] ) && '' != $filters['score']['max'] ) {
				$where_arr[] = $wpdb->prepare( 'score <= %f', $filters['score']['max'] );
				$debug_info['smax'] = $filters['score']['max'];
			}
		}

		// Check for url_track
		if ( isset( $filters['url_track'] ) && is_array( $filters['url_track'] ) ) {
			// Omit if it is just all
			if ( ! in_array( '', $filters['url_track'] ) ) {
				$url_track_where = array();

				foreach ( $filters['url_track'] as $ut ) {
					$url_track_where[] = $wpdb->prepare( 'url_track = %s', $ut );
				}

				if ( ! empty( $url_track_where ) ) {
					$debug_info['url_track'] = $where_arr[] = '( ' . implode( ' OR ', $url_track_where ) . ' )';
				}
			}
		}

		// Check for user meta
		$user_ids = array();
		if ( isset( $filters['meta'] ) && '' != $filters['meta'] ) {
			$get_users_args = array(
				'meta_key' => $filters['meta'],
				'fields' => 'ID',
			);

			if ( isset( $filters['mvalue'] ) && '' != $filters['mvalue'] ) {
				$get_users_args['meta_value'] = $filters['mvalue'];
			}
			$users_from_meta = get_users( $get_users_args );
			if ( ! empty( $users_from_meta ) ) {
				$user_ids = array_merge( $user_ids, $users_from_meta );
			} else {
				$user_ids = array( '-1' );
			}
			$debug_info['uid_meta'] = $user_ids;
		}

		// Check for user_id
		if ( isset( $filters['user_id'] ) && is_array( $filters['user_id'] ) ) {
			// Omit if it is just all
			if ( ! in_array( '', $filters['user_id'] ) ) {
				$user_ids = array_merge( $user_ids, $filters['user_id'] );
				$debug_info['uid_filter'] = $filters['user_id'];
			}
		}

		// Add the user id to the query
		if ( ! empty( $user_ids ) ) {
			$user_ids = array_unique( $user_ids );
			$user_ids = array_map( 'intval', $user_ids );
			$where_arr[] = 'user_id IN (' . implode( ',', $user_ids ) . ')';
			$debug_info['user_id'] = $user_ids;
		}

		// Concatenate the where statement
		if ( ! empty( $where_arr ) ) {
			$where .= ' AND ' . implode( ' AND ', $where_arr );
		}

		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where} ORDER BY id ASC LIMIT %d,%d", $form_id, $doing * $per_page, $per_page ) );
		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(id) FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d{$where}", $form_id ) );

		if ( empty( $data_ids ) ) {
			$return['done'] = 100;
			$return['debug_info'] = (object) $debug_info;
			echo json_encode( (object) $return );
			die();
		}

		// Some helper variables
		$date_formats = array(
			'yy-mm-dd' => 'Y-m-d',
			'mm/dd/yy' => 'm/d/Y',
			'dd.mm.yy' => 'd.m.Y',
			'dd-mm-yy' => 'd-m-Y',
		);
		$time_formats = array(
			'HH:mm:ss' => 'H:i:s',
			'hh:mm:ss TT' => 'h:i:s A',
		);

		foreach ( $data_ids as $data_id ) {
			$data = new IPT_FSQM_Form_Elements_Data( $data_id );

			$data_formatted_date = date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $data->data->date ) );
			$data_formatted_email = $data->data->email == '' ? __( 'anonymous', 'ipt_fsqm' ) : '<a href="mailto:' . $data->data->email . '">' . $data->data->email . '</a>';
			$data_formatted_name = ( $data->data->f_name == '' && $data->data->l_name == '' ) ? __( 'anonymous', 'ipt_fsqm' ) : $data->data->f_name . ' ' . $data->data->l_name;

			// blacklist the conditionally hidden elements
			$data->blacklist_conditional_hiddens();

			// Do the survey
			if ( is_array( $query_elements['mcqs'] ) && ! empty( $query_elements['mcqs'] ) ) {
				foreach ( $query_elements['mcqs'] as $m_key ) {
					$m_key = (int) $m_key;
					if ( ! isset( $data->mcq[$m_key] ) || !isset( $data->data->mcq[$m_key] ) ) {
						continue;
					}
					$element = $data->mcq[$m_key];

					if ( isset( $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
						if ( in_array( (string) $m_key, $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
							continue;
						}
					}

					switch ( $element['type'] ) {
						default :
							$definition = $data->get_element_definition($element);
							if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
								if ( ! isset($return['survey']["$m_key"] ) ) {
									$return['survey']["$m_key"] = array();
								}
								$return['survey']["$m_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->mcq[$m_key], $m_key, $do_data, $do_names, $do_others, $sensitive_data, $do_date, $return['survey']["$m_key"], $data );
							}
							break;
						case 'radio' :
						case 'checkbox' :
						case 'select' :
						case 'thumbselect' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
								if ( $do_data ) {
									$return['survey']["$m_key"]['others_data'] = array();
								}
							}
							if ( ! isset( $data->data->mcq[$m_key]['options'] ) || empty( $data->data->mcq[$m_key]['options'] ) ) {
								continue 2;
							}
							foreach ( $data->data->mcq[$m_key]['options'] as $o_key ) {
								$return['survey']["$m_key"]["$o_key"] = isset( $return['survey']["$m_key"]["$o_key"] ) ? $return['survey']["$m_key"]["$o_key"] + 1 : 1;
							}
							if ( isset( $data->data->mcq[$m_key]['others'] ) && ! empty( $data->data->mcq[$m_key]['others'] ) && $do_others ) {
								$others_data = array(
									'value' => esc_textarea( $data->data->mcq[$m_key]['others'] ),
								);
								if ( $do_names ) {
									$others_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$others_data['email'] = $data_formatted_email;
									$others_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$others_data['date'] = $data_formatted_date;
								}
								$return['survey']["$m_key"]['others_data'][] = $others_data;
							}
							break;
						case 'smileyrating' :
							$setting_to_icon_map = array(
								'frown' => 'angry2',
								'sad' => 'sad2',
								'neutral' => 'neutral2',
								'happy' => 'smiley2',
								'excited' => 'happy2',
							);
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'frown' => 0,
									'sad' => 0,
									'neutral' => 0,
									'happy' => 0,
									'excited' => 0,
								);
								$return['survey']["$m_key"]['feedback_data'] = array();
							}
							if ( ! isset( $data->data->mcq[$m_key]['option'] ) || $data->data->mcq[$m_key]['option'] == '' ) {
								continue 2;
							}

							if ( isset( $return['survey']["$m_key"][$data->data->mcq[$m_key]['option']] ) ) {
								$return['survey']["$m_key"][$data->data->mcq[$m_key]['option']]++;
							}
							if ( $do_others && isset( $data->data->mcq[$m_key]['feedback'] ) && $data->data->mcq[$m_key]['feedback'] != '' ) {
								$feedback_data = array(
									'entry' => wpautop( esc_textarea( $data->data->mcq[$m_key]['feedback'] ) ),
									'rating' => '<i class="ipt-icomoon-' . $setting_to_icon_map[$data->data->mcq[$m_key]['option']] . '"></i>',
								);
								if ( $do_names ) {
									$feedback_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$feedback_data['email'] = $data_formatted_email;
									$feedback_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$feedback_data['date'] = $data_formatted_date;
								}
								$return['survey']["$m_key"]['feedback_data'][] = $feedback_data;
							}
							break;
						case 'likedislike' :
							$setting_to_icon_map = array(
								'like' => 'thumbs-o-up',
								'dislike' => 'thumbs-o-down',
							);
							if ( ! isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'like' => 0,
									'dislike' => 0,
								);
								$return['survey']["$m_key"]['feedback_data'] = array();
							}
							if ( ! isset( $data->data->mcq[$m_key]['value'] ) || $data->data->mcq[$m_key]['value'] == '' ) {
								continue 2;
							}
							if ( isset( $return['survey']["$m_key"][$data->data->mcq[$m_key]['value']] ) ) {
								$return['survey']["$m_key"][$data->data->mcq[$m_key]['value']]++;
							}
							if ( $do_others && isset( $data->data->mcq[$m_key]['feedback'] ) && $data->data->mcq[$m_key]['feedback'] != '' ) {
								$feedback_data = array(
									'entry' => wpautop( esc_textarea( $data->data->mcq[$m_key]['feedback'] ) ),
									'rating' => '<i class="ipt-icomoon-' . $setting_to_icon_map[$data->data->mcq[$m_key]['value']] . '"></i>',
								);
								if ( $do_names ) {
									$feedback_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$feedback_data['email'] = $data_formatted_email;
									$feedback_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$feedback_data['date'] = $data_formatted_date;
								}
								$return['survey']["$m_key"]['feedback_data'][] = $feedback_data;
							}
							break;
						case 'slider' :
							if ( ! isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( ! isset($data->data->mcq[$m_key]['value']) || '' == $data->data->mcq[$m_key]['value'] ) {
								continue 2;
							}
							$return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] = isset( $return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] ) ? $return['survey']["$m_key"]["{$data->data->mcq[$m_key]['value']}"] + 1 : 1;
							break;
						case 'range' :
							if ( ! isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['values'] ) ) {
								continue 2;
							}
							$key = "{$data->data->mcq[$m_key]['values']['min']},{$data->data->mcq[$m_key]['values']['max']}";
							$return['survey']["$m_key"][$key] = isset( $return['survey']["$m_key"][$key] ) ? $return['survey']["$m_key"][$key] + 1 : 1;
							break;
						case 'spinners' :
						case 'grading' :
						case 'starrating' :
						case 'scalerating' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['options'] ) ) {
								continue 2;
							}

							foreach ( $data->mcq[$m_key]['settings']['options'] as $o_key => $o_val ) {
								if ( !isset( $return['survey']["$m_key"]["$o_key"] ) ) {
									$return['survey']["$m_key"]["$o_key"] = array();
								}
								if ( !isset( $data->data->mcq[$m_key]['options'][$o_key] ) ) {
									continue;
								}
								if ( is_array( $data->data->mcq[$m_key]['options'][$o_key] ) ) {
									$key = $data->data->mcq[$m_key]['options'][$o_key]['min'] . ',' . $data->data->mcq[$m_key]['options'][$o_key]['max'];
								} else {
									$key = (string) $data->data->mcq[$m_key]['options'][$o_key];
								}

								if ( $key == '' ) {
									continue;
								}

								$return['survey']["$m_key"]["$o_key"][$key] = isset( $return['survey']["$m_key"]["$o_key"][$key] ) ? $return['survey']["$m_key"]["$o_key"][$key] + 1 : 1;
							}
							break;
						case 'matrix_dropdown' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['rows'] ) ) {
								continue 2;
							}
							$options_array = array();
							foreach ( $data->mcq[$m_key]['settings']['options'] as $o_key => $op ) {
								$options_array["$o_key"] = 0;
							}
							foreach ( (array) $data->data->mcq[$m_key]['rows'] as $r_key => $columns ) {
								if ( !isset( $return['survey']["$m_key"]["$r_key"] ) ) {
									$return['survey']["$m_key"]["$r_key"] = array();
								}
								foreach ( (array) $columns as $c_key => $o_key ) {
									if ( ! isset( $return['survey']["$m_key"]["$r_key"]["$c_key"] ) ) {
										$return['survey']["$m_key"]["$r_key"]["$c_key"] = $options_array;
									}
									if ( is_array( $o_key ) ) {
										foreach ( $o_key as $option_key ) {
											if ( isset( $return['survey']["$m_key"]["$r_key"]["$c_key"]["$option_key"] ) ) {
												$return['survey']["$m_key"]["$r_key"]["$c_key"]["$option_key"]++;
											}
										}
									} else {
										if ( isset( $return['survey']["$m_key"]["$r_key"]["$c_key"]["$o_key"] ) ) {
											$return['survey']["$m_key"]["$r_key"]["$c_key"]["$o_key"]++;
										}
									}
								}
							}
							break;
						case 'matrix' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array();
							}
							if ( empty( $data->data->mcq[$m_key]['rows'] ) ) {
								continue 2;
							}
							foreach ( $data->data->mcq[$m_key]['rows'] as $r_key => $columns ) {
								if ( !isset( $return['survey']["$m_key"]["$r_key"] ) ) {
									$return['survey']["$m_key"]["$r_key"] = array();
								}
								foreach ( $columns as $c_key ) {
									$return['survey']["$m_key"]["$r_key"]["$c_key"] = isset( $return['survey']["$m_key"]["$r_key"]["$c_key"] ) ? $return['survey']["$m_key"]["$r_key"]["$c_key"] + 1 : 1;
								}
							}
							break;
						case 'toggle' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'on' => 0,
									'off' => 0,
								);
							}
							if ( $data->data->mcq[$m_key]['value'] == false ) {
								$return['survey']["$m_key"]['off']++;
							} else {
								$return['survey']["$m_key"]['on']++;
							}
							break;
						case 'sorting' :
							if ( !isset( $return['survey']["$m_key"] ) ) {
								$return['survey']["$m_key"] = array(
									'preset' => 0,
									'other' => 0,
									'orders' => array(),
								);
							}
							if ( empty( $data->data->mcq[$m_key]['order'] ) ) {
								continue 2;
							}
							$correct_order = implode( '-', array_keys( $data->mcq[$m_key]['settings']['options'] ) );
							$user_order = implode( '-', $data->data->mcq[$m_key]['order'] );
							if ( $correct_order == $user_order ) {
								$return['survey']["$m_key"]['preset']++;
							} else {
								$return['survey']["$m_key"]['other']++;
							}
							$return['survey']["$m_key"]['orders'][$user_order] = isset( $return['survey']["$m_key"]['orders'][$user_order] ) ? $return['survey']["$m_key"]['orders'][$user_order] + 1 : 1;
					}
				}
			}

			// Do the Feedback
			if ( is_array( $query_elements['freetypes'] ) && !empty( $query_elements['freetypes'] ) ) {
				foreach ( $query_elements['freetypes'] as $f_key ) {
					if ( !isset( $data->freetype[$f_key] ) || !isset( $data->data->freetype[$f_key] ) ) {
						continue;
					}
					$element = $data->freetype[$f_key];

					if ( isset( $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
						if ( in_array( (string) $f_key, $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
							continue;
						}
					}

					switch ( $element['type'] ) {
						default :
							$definition = $data->get_element_definition($element);
							if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
								if ( ! isset( $return['feedback']["$f_key"] ) ) {
									$return['feedback']["$f_key"] = array();
								}
								$return['feedback']["$f_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->freetype[$f_key], $f_key, $do_data, $do_names, $sensitive_data, $do_date, $return['feedback']["$f_key"], $data );
							}
							break;
						case 'upload' :
							// Create the array
							if ( ! isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							// Init the uploader class
							$uploader = new IPT_FSQM_Form_Elements_Uploader( $data->form_id, $f_key );
							$uploads = $uploader->get_uploads( $data->data_id );

							// Loop through all uploads and save the meta
							$upload_array = array();

							if ( ! empty( $uploads ) ) {
								foreach ( $uploads as $upload ) {
									if ( '' == $upload['guid'] ) {
										continue;
									}
									$upload_array[] = array(
										'guid'      => $upload['guid'],
										'thumb_url' => $upload['thumb_url'],
										'name'      => $upload['name'] . ' (' . $upload['mime_type'] . ' )',
										'filename'  => $upload['filename'],
									);
								}
							}
							$feedback_upload = array(
								'uploads' => $upload_array,
							);
							if ( $sensitive_data ) {
								$feedback_upload['id'] = $data->data_id;
								$feedback_upload['email'] = $data_formatted_email;
							}
							if ( $do_names ) {
								$feedback_upload['name'] = $data_formatted_name;
							}
							if ( $do_date ) {
								$feedback_upload['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_upload;

							break;
						case 'feedback_large' :
						case 'feedback_small' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( empty( $data->data->freetype[$f_key]['value'] ) ) {
								continue 2;
							}
							$feedback_text = array(
								'value' => wpautop( esc_textarea( $data->data->freetype["$f_key"]['value'] ) ),
							);
							if ( $do_names ) {
								$feedback_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_text['email'] = $data_formatted_email;
								$feedback_text['phone'] = $data->data->phone;
								$feedback_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_text['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_text;
							break;
						case 'mathematical' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( empty( $data->data->freetype[$f_key]['value'] ) ) {
								continue 2;
							}

							$ui = IPT_Plugin_UIF_Front::instance( 'ipt_fsqm' );
							ob_start();
							$ui->print_icon( $data->freetype[$f_key]['settings']['icon'], false );
							$icon = ob_get_clean();
							$feedback_math = array(
								'value' => $data->freetype[$f_key]['settings']['prefix'] . ' ' . $icon . ' ' . $data->data->freetype["$f_key"]['value'] . ' ' . $data->freetype[$f_key]['settings']['suffix'],
							);
							if ( $do_names ) {
								$feedback_math['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_math['email'] = $data_formatted_email;
								$feedback_math['phone'] = $data->data->phone;
								$feedback_math['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_math['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_math;
							break;
						case 'gps' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( ! is_numeric( $data->data->freetype[$f_key]['lat'] ) || ! is_numeric( $data->data->freetype[$f_key]['long'] ) ) {
								continue 2;
							}

							$feedback_gps = array(
								'map'           => sprintf( '//maps.googleapis.com/maps/api/staticmap?markers=%1$s,%2$s&zoom=%3$s&size=500x300&scale=2&key=%4$s', round( (float) $data->data->freetype[$f_key]['lat'], 6 ), round( (float) $data->data->freetype[$f_key]['long'], 6 ), $data->freetype[$f_key]['settings']['zoom'], $ipt_fsqm_settings['gplaces_api'] ),
								'lat'           => $data->data->freetype[$f_key]['lat'],
								'long'          => $data->data->freetype[$f_key]['long'],
								'location_name' => $data->data->freetype[$f_key]['location_name'],
							);
							if ( $do_names ) {
								$feedback_gps['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_gps['email'] = $data_formatted_email;
								$feedback_gps['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_gps['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_gps;
							break;
						case 'feedback_matrix' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}
							$break_data = true;
							$sanitized_matrix = array();
							foreach ( $data->data->freetype[$f_key]['rows'] as $r_key => $columns ) {
								$sanitized_matrix["$r_key"] = array();
								foreach ( (array) $columns as $c_key => $val ) {
									if ( $val != '' ) {
										$break_data = false;
									}
									$sanitized_matrix["$r_key"]["$c_key"] = wpautop( esc_textarea( $val ) );
								}
							}
							if ( $break_data ) {
								continue 2;
							}
							$feedback_matrix = array(
								'matrix'        => $sanitized_matrix,
							);
							if ( $do_names ) {
								$feedback_matrix['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_matrix['email'] = $data_formatted_email;
								$feedback_matrix['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_matrix['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_matrix;
							break;
						case 'signature' :
							if ( !isset( $return['feedback']["$f_key"] ) ) {
								$return['feedback']["$f_key"] = array();
							}

							if ( empty( $data->data->freetype[$f_key]['value'] ) || $data->data->freetype["$f_key"]['value'] == 'image/jsignature;base30,' ) {
								continue 2;
							}

							$feedback_sign = array(
								'value' => $data->convert_jsignature_image( $data->data->freetype["$f_key"]['value'], $element['settings']['color'] ),
							);
							if ( $do_names ) {
								$feedback_sign['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$feedback_sign['email'] = $data_formatted_email;
								$feedback_sign['phone'] = $data->data->phone;
								$feedback_sign['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$feedback_sign['date'] = $data_formatted_date;
							}
							$return['feedback']["$f_key"][] = $feedback_sign;
							break;
					}
				}
			}

			// Do the pinfos
			if ( is_array( $query_elements['pinfos'] ) && ! empty( $query_elements['pinfos'] ) ) {
				// Loop through all given pinfos
				foreach ( $query_elements['pinfos'] as $p_key ) {
					// Cast to a true integer, because it is stored as string (string '1' not int 1)
					$p_key = (int) $p_key;
					// If element or data does not exists, then leave it
					if ( ! isset( $data->pinfo[$p_key] ) || ! isset( $data->data->pinfo[$p_key] ) ) {
						continue;
					}

					// Set the element
					$element = $data->pinfo[$p_key];

					// Avoid if it is conditionally hidden
					if ( isset( $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
						if ( in_array( (string) $p_key, $data->conditional_hidden_blacklist[$element['m_type']] ) ) {
							continue;
						}
					}

					// Everything checks out
					// So do for specific elements
					switch ( $element['type'] ) {
						// Open scope for third party integration
						default :
							$definition = $data->get_element_definition( $element );
							if ( isset( $definition['callback_report_calculator'] ) && is_callable( $definition['callback_report_calculator'] ) ) {
								if ( ! isset($return['pinfo']["$p_key"] ) ) {
									$return['pinfo']["$p_key"] = array();
								}
								$return['pinfo']["$p_key"] = call_user_func( $definition['callback_report_calculator'], $element, $data->data->pinfo[$p_key], $p_key, $do_data, $do_names, $do_others, $sensitive_data, $do_date, $return['pinfo']["$p_key"], $data );
							}
							break;
						// Text types
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
						case 'hidden' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['value'] ) ) {
								continue 2;
							}

							$pinfo_text = array(
								'value' => wpautop( esc_textarea( $data->data->pinfo["$p_key"]['value'] ) ),
							);
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						case 'datetime' :
							if ( !isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['value'] ) ) {
								continue 2;
							}

							$dtvalue = $data->data->pinfo["$p_key"]['value'];
							$current_picker_timestamp = strtotime( $dtvalue );
							if ( $current_picker_timestamp != false ) {
								switch ( $element['settings']['type'] ) {
								case 'date' :
									$dtvalue = date( $date_formats[$element['settings']['date_format']], $current_picker_timestamp );
									break;
								case 'time' :
									$dtvalue = date( $time_formats[$element['settings']['time_format']], $current_picker_timestamp );
									break;
								case 'datetime' :
									$dtvalue = date( $date_formats[$element['settings']['date_format']] . ' ' . $time_formats[$element['settings']['time_format']], $current_picker_timestamp );
									break;
								}
							}
							$pinfo_text = array(
								'value' => $dtvalue,
							);
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						case 'address' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( $data->data->pinfo[$p_key]['values'] == array(
								'recipient' => '',
								'line_one' => '',
								'line_two' => '',
								'line_three' => '',
								'country' => '',
							) ) {
								continue 2;
							}

							$address = $data->data->pinfo[$p_key]['values'];
							$address = array_map( 'esc_textarea', $address );
							$pinfo_text = array(
								'values' => $address,
							);
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						// Payment
						case 'payment' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							// This needs special data collection
							// Get the data from table
							$payment_db = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['payment_table']} WHERE data_id = %d", $data->data_id ) );
							// Skip if not present
							if ( is_null( $payment_db ) ) {
								continue 2;
							}

							// Populate the data
							$invoiceid = str_replace( '{id}', $payment_db->id, $data->settings['payment']['invoicenumber'] );
							if ( $invoiceid == '' ) {
								$invoiceid = $payment_db->id;
							}
							$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();
							$payment_modes = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();
							$pinfo_payment = array(
								'invoice' => $invoiceid,
								'status' => isset( $payment_status[ $payment_db->status ] ) ? $payment_status[ $payment_db->status ] : __( 'Unknown', 'ipt_fsqm' ),
								'txn' => ( is_null( $payment_db->txn ) ? __( 'N/A', 'ipt_fsqm' ) : $payment_db->txn ) ,
								'gateway' => isset( $payment_modes[ $payment_db->mode ] ) ? $payment_modes[ $payment_db->mode ] : __( 'Unknown', 'ipt_fsqm' ),
								'total' => $payment_db->amount,
							);
							if ( $do_names ) {
								$pinfo_payment['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_payment['email'] = $data_formatted_email;
								$pinfo_payment['phone'] = $data->data->phone;
								$pinfo_payment['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_payment['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_payment;
							break;
						// MCQ
						case 'p_radio' :
						case 'p_checkbox' :
						case 'p_select' :
							if ( ! isset( $return['pinfo']["$p_key"] ) ) {
								$return['pinfo']["$p_key"] = array();
								if ( $do_data ) {
									$return['pinfo']["$p_key"]['others_data'] = array();
								}
							}
							if ( empty( $data->data->pinfo[$p_key]['options'] ) ) {
								continue 2;
							}
							foreach ( $data->data->pinfo[$p_key]['options'] as $o_key ) {
								$return['pinfo']["$p_key"]["$o_key"] = isset( $return['pinfo']["$p_key"]["$o_key"] ) ? $return['pinfo']["$p_key"]["$o_key"] + 1 : 1;
							}
							if ( isset( $data->data->pinfo[$p_key]['others'] ) && ! empty( $data->data->pinfo[$p_key]['others'] ) && $do_others ) {
								$others_data = array(
									'value' => esc_textarea( $data->data->pinfo[$p_key]['others'] ),
								);
								if ( $do_names ) {
									$others_data['name'] = $data_formatted_name;
								}
								if ( $sensitive_data ) {
									$others_data['email'] = $data_formatted_email;
									$others_data['id'] = $data->data_id;
								}
								if ( $do_date ) {
									$others_data['date'] = $data_formatted_date;
								}
								$return['pinfo']["$p_key"]['others_data'][] = $others_data;
							}
							break;
						// Single State
						case 's_checkbox' :
							if ( ! isset( $return['pinfo']["$p_key"] ) ) {
								$return['pinfo']["$p_key"] = array(
									'checked' => 0,
									'unchecked' => 0,
								);
							}
							if ( ! isset( $data->data->pinfo[$p_key]['value'] ) ) {
								continue 2;
							}
							if ( $data->data->pinfo[$p_key]['value'] == false ) {
								$return['pinfo']["$p_key"]['unchecked']++;
							} else {
								$return['pinfo']["$p_key"]['checked']++;
							}
							break;
						// Sorting
						case 'p_sorting' :
							if ( ! isset( $return['pinfo']["$p_key"] ) ) {
								$return['pinfo']["$p_key"] = array(
									'preset' => 0,
									'other' => 0,
									'orders' => array(),
								);
							}
							if ( ! isset( $data->data->pinfo[$p_key]['order'] ) || empty( $data->data->pinfo[$p_key]['order'] ) ) {
								continue 2;
							}
							$correct_order = implode( '-', array_keys( $data->pinfo[$p_key]['settings']['options'] ) );
							$user_order = implode( '-', $data->data->pinfo[$p_key]['order'] );
							if ( $correct_order == $user_order ) {
								$return['pinfo']["$p_key"]['preset']++;
							} else {
								$return['pinfo']["$p_key"]['other']++;
							}
							$return['pinfo']["$p_key"]['orders'][$user_order] = isset( $return['pinfo']["$p_key"]['orders'][$user_order] ) ? $return['pinfo']["$p_key"]['orders'][$user_order] + 1 : 1;
							break;

						// Guest Blog
						case 'guestblog' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['value'] ) ) {
								continue 2;
							}

							$pinfo_text = array(
								'value' => $data->data->pinfo[ "$p_key" ]['value'],
								'title' => $data->data->pinfo[ "$p_key" ]['title'],
								'taxonomy' => array(),
							);
							if ( ! empty( $data->data->pinfo[ "$p_key" ]['taxonomy'] ) ) {
								foreach ( $data->data->pinfo[ "$p_key" ]['taxonomy'] as $taxonomy => $tax_selected ) {
									$tax_data = get_taxonomy( $taxonomy );
									$terms_data = array();
									foreach( (array) $tax_selected as $term ) {
										$terms_data[] = get_term( $term, $taxonomy )->name;
									}
									$pinfo_text['taxonomy'][] = array(
										'tax' => $tax_data->labels->name,
										'terms' => $terms_data,
									);
								}
							}
							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo']["$p_key"][] = $pinfo_text;
							break;
						case 'repeatable' :
							if ( ! isset( $return['pinfo'][ "$p_key" ] ) ) {
								$return['pinfo'][ "$p_key" ] = array();
							}

							if ( empty( $data->data->pinfo[ $p_key ]['values'] ) ) {
								continue 2;
							}

							$pinfo_text = array(
								'value' => '<table class="data-table"><tbody>',
							);

							$rowspan = count( (array) $data->pinfo[ $p_key ]['settings']['group'] ) + 1;
							$i = 1;
							foreach ( (array) $data->data->pinfo[ $p_key ]['values'] as $i_key => $items ) {
								$pinfo_text['value'] .= '<tr><th rowspan="' . $rowspan . '">' . sprintf( _x( '#%d', 'eform-repetable-heading', 'ipt_fsqm' ), $i++ ) . '</th></tr>';
								foreach ( (array) $data->pinfo[ $p_key ]['settings']['group'] as $g_key => $group ) {
									$pinfo_text['value'] .= '<tr><th>' . $group['title'] . '</th><td>';

									if ( isset( $items[ $g_key ] ) ) {
										switch ( $group['type'] ) {
											case 'radio' :
												$pinfo_text['value'] .= str_replace( '__', ' ', $items[ $g_key ] );
												break;
											case 'checkbox' :
											case 'select' :
											case 'select_multiple' :
												$options = array();
												foreach ( (array) $items[ $g_key ] as $op ) {
													$options[] = str_replace( '__', ' ', $op );
												}
												$pinfo_text['value'] .= implode( '<br />', $options );
												break;
											case 'text' :
											case 'phone' :
											case 'url' :
											case 'email' :
											case 'number' :
											case 'integer' :
											case 'personName' :
											case 'password' :
											case 'textarea' :
												$pinfo_text['value'] .= $items[ $g_key ];
												break;
											case 'date' :
												$date = DateTime::createFromFormat( 'Y-m-d', $items[ $g_key ] );
												if ( ! $date ) {
													$pinfo_text['value'] .= $items[ $g_key ];
												} else {
													$pinfo_text['value'] .= $date->format( get_option( 'date_format' ) );
												}
												break;
											case 'time' :
												$date = DateTime::createFromFormat( 'H:i:s', $items[ $g_key ] );
												if ( ! $date ) {
													$pinfo_text['value'] .= $items[ $g_key ];
												} else {
													$pinfo_text['value'] .= $date->format( get_option( 'time_format' ) );
												}
												break;
											case 'datetime' :
												$date = DateTime::createFromFormat( 'Y-m-d H:i:s', $items[ $g_key ] );
												if ( ! $date ) {
													$pinfo_text['value'] .= $items[ $g_key ];
												} else {
													$pinfo_text['value'] .= $date->format( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) );
												}
												break;
										}
									}

									$pinfo_text['value'] .= '</td></tr>';
								}
							}

							$pinfo_text['value'] .= '</tbody></table>';

							if ( $do_names ) {
								$pinfo_text['name'] = $data_formatted_name;
							}
							if ( $sensitive_data ) {
								$pinfo_text['email'] = $data_formatted_email;
								$pinfo_text['phone'] = $data->data->phone;
								$pinfo_text['id'] = $data->data_id;
							}
							if ( $do_date ) {
								$pinfo_text['date'] = $data_formatted_date;
							}
							$return['pinfo'][ "$p_key" ][] = $pinfo_text;
							break;
					}
				}
			}
		}

		//Calculate the done
		$done_till_now = $doing * $per_page + $per_page;
		if ( $done_till_now >= $total ) {
			$return['done'] = 100;
		} else {
			$return['done'] = (float) $done_till_now * 100 / $total;
		}

		$return['survey'] = (object) $return['survey'];
		$return['feedback'] = (object) $return['feedback'];
		$return['pinfo'] = (object) $return['pinfo'];

		// Add the debug info
		$debug_info['total'] = $total;
		$return['debug_info'] = (object) $debug_info;

		echo json_encode( (object) $return );
		die();
	}

	/*==========================================================================
	 * Database abstractions
	 *========================================================================*/

	/**
	 * Get all of the forms
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 * @return array
	 */
	public static function get_forms() {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT * FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
	}

	public static function get_form( $form_id ) {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ) );
	}

	public static function get_forms_for_select() {
		global $wpdb, $ipt_fsqm_info;
		return $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
	}

	public static function get_form_themes_for_select() {
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$themes = $form_element->get_available_themes();
		$return = array();
		foreach ( $themes as $theme_grp ) {
			foreach ( (array) $theme_grp['themes'] as $theme_key => $theme ) {
				$return[$theme_key] = $theme['label'];
			}
		}
		return $return;
	}

	public static function delete_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$delete_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_deleted', $ids );

		return $wpdb->query( "DELETE FROM {$ipt_fsqm_info['data_table']} WHERE id IN ({$delete_ids})" );
	}

	public static function star_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$update_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_starred', $ids );

		return $wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET star = 1 WHERE id IN ({$update_ids})" );
	}

	public static function unstar_submissions( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$update_ids = implode( ',', $ids );

		do_action( 'ipt_fsqm_submissions_unstarred', $ids );

		return $wpdb->query( "UPDATE {$ipt_fsqm_info['data_table']} SET star = 0 WHERE id IN ({$update_ids})" );
	}

	public static function delete_forms( $ids = array() ) {
		global $wpdb, $ipt_fsqm_info;
		if ( empty( $ids ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}

		$ids = array_map( 'intval', $ids );

		$delete_ids = implode( ',', $ids );

		$submission_ids = $wpdb->get_col( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id IN ({$delete_ids})" );

		self::delete_submissions( $submission_ids );

		do_action( 'ipt_fsqm_forms_deleted', $ids );
		return $wpdb->query( "DELETE FROM {$ipt_fsqm_info['form_table']} WHERE id IN ({$delete_ids})" );
	}

	public static function copy_form( $id ) {
		global $wpdb, $ipt_fsqm_info;
		$prev = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $id ) );
		if ( null == $prev ) {
			return;
		}

		$prev->name .= ' Copy';
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name' => $prev->name,
			'settings' => $prev->settings,
			'layout' => $prev->layout,
			'design' => $prev->design,
			'mcq' => $prev->mcq,
			'freetype' => $prev->freetype,
			'pinfo' => $prev->pinfo,
			'type' => $prev->type,
			'category' => $prev->category,
		), '%s' );

		do_action( 'ipt_fsqm_form_copied', $id, $wpdb->insert_id );
	}

	public static function get_category( $id ) {
		global $wpdb, $ipt_fsqm_info;
		$category = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['category_table']} WHERE id = %d", $id ) );
		return $category;
	}

	public static function get_all_categories() {
		global $wpdb, $ipt_fsqm_info;
		$category = $wpdb->get_results( "SELECT * FROM {$ipt_fsqm_info['category_table']}" );
		return $category;
	}

	public static function create_category( $name, $description = '' ) {
		global $wpdb, $ipt_fsqm_info;
		$name = strip_tags( $name );
		$wpdb->insert( $ipt_fsqm_info['category_table'], array(
			'name' => $name,
			'description' => $description,
		), '%s' );
		$cat_id = $wpdb->insert_id;
		do_action( 'ipt_fsqm_form_category_created', $cat_id );
		return $cat_id;
	}

	public static function update_category( $id, $name, $description = '' ) {
		global $wpdb, $ipt_fsqm_info;
		$name = strip_tags( $name );
		$return = $wpdb->update( $ipt_fsqm_info['category_table'], array(
			'name' => $name,
			'description' => $description,
		), array( 'id' => $id ), '%s', '%d' );
		do_action( 'ipt_fsqm_form_category_updated', $id, $return );
		return $return;
	}

	public static function delete_categories( $ids ) {
		global $wpdb, $ipt_fsqm_info;
		if ( ! is_array( $ids ) ) {
			$ids = (array) $ids;
		}
		if ( empty( $ids ) ) {
			return false;
		}
		$ids = array_map( 'intval', $ids );
		$delete_ids = implode( ',', $ids );
		do_action( 'ipt_fsqm_form_category_deleted', $ids );
		$return = $wpdb->query( "DELETE FROM {$ipt_fsqm_info['category_table']} WHERE id IN ({$delete_ids})" );
		// Also unassign the forms
		$wpdb->query( "UPDATE {$ipt_fsqm_info['form_table']} SET category = 0 WHERE category IN ({$delete_ids})" );
		return $return;
	}

	/*==========================================================================
	 * Encrypt & Decrypt
	 *========================================================================*/
	public static function encrypt( $input_string ) {
		$key = get_option( 'ipt_fsqm_key' );
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$h_key = hash( 'sha256', $key, TRUE );
		return base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, $h_key, $input_string, MCRYPT_MODE_ECB, $iv ) );
	}

	public static function decrypt( $encrypted_input_string ) {
		$key = get_option( 'ipt_fsqm_key' );
		$iv_size = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB );
		$iv = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
		$h_key = hash( 'sha256', $key, TRUE );
		return trim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, $h_key, base64_decode( $encrypted_input_string ), MCRYPT_MODE_ECB, $iv ) );
	}

	/*==========================================================================
	 * Some other functions
	 *========================================================================*/
	public static function get_current_url() {
		global $wp;
		$current_url = home_url( add_query_arg( array(), $wp->request ) );
		return $current_url;
	}

	public static function safe_parse_str( $string, &$result ) {
		if( $string === '' )
			return false;
		$result = array();

		// find the pairs "name=value"
		$pairs = explode( '&', $string );
		foreach ( $pairs as $pair ) {

			// use the original parse_str() on each element
			parse_str( $pair, $params );
			foreach ( $params as $key => $param ) {
				if ( ! isset( $result[$key] ) ) {
					$result[$key] = $param;
				} else {
					if ( is_array( $result[$key] ) ) {
						if ( is_array( $param ) ) {
							$result[$key] = self::array_merge_recursive_distinct( $result[$key], $param );
						} else {
							$result[$key][] = $param;
						}
					} else {
						$result[$key] = $param;
					}
				}
			}
		}
		return true;
	}

	// better recursive array merge function listed on the array_merge_recursive PHP page in the comments
	public static function array_merge_recursive_distinct( array $array1, array $array2 ) {
		$merged = $array1;
		foreach ( $array2 as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[$key] ) && is_array( $merged[$key] ) ) {
				$merged[$key] = self::array_merge_recursive_distinct( $merged[$key], $value );
			} else {
				if ( is_numeric( $key ) && isset( $merged[$key] ) ) {
					$merged[] = $value;
				} else {
					$merged[$key] = $value;
				}
			}
		}

		return $merged;
	}

	/**
	 * Gets the valid payment selections.
	 *
	 * @return     array  The valid payment selections. All of them are set to false
	 * so that they can be enabled, disabled manually
	 */
	public static function get_valid_payment_selections() {
		return apply_filters( 'ipt_fsqm_payment_methods', array(
			'paypal_d' => false,
			'paypal_e' => false,
			'stripe' => false,
		) );
	}

	public static function formulate_sda_items( $string ) {
		// First replace to unix new line
		$string = str_replace( "\r\n", "\n", $string );

		// Split
		$raw_options = explode( "\n", $string );
		$options = array();

		foreach ( (array) $raw_options as $r_key => $option ) {
			// Check for first empty option
			if ( 0 == $r_key ) {
				if ( preg_match( '/\[empty\]$/', $option ) ) {
					$options[] = array(
						'value' => '',
						'label' => str_replace( '[empty]', '', $option ),
					);
					continue;
				}
			}

			// Get numeric data
			$op_parts = array();
			if ( preg_match( '/(.*)\[num=(\-?(\d+|\d*\.\d+))\]/', $option, $op_parts ) ) {
				$options[] = array(
					'value' => str_replace( ' ', '__', $op_parts[1] ),
					'label' => $op_parts[1],
					'data' => array(
						'num' => $op_parts[2],
					),
				);
			} else {
				$options[] = array(
					'value' => str_replace( ' ', '__', $option ),
					'label' => $option,
				);
			}
		}
		return $options;
	}

	public static function formulate_sda_attributes( $string ) {
		$attributes = array(
			'min' => '',
			'max' => '',
			'minSize' => '',
			'maxSize' => '',
			'minCheckbox' => '',
			'maxCheckbox' => '',
			'future' => '',
			'past' => '',
		);

		$return = array();
		$matches = array();
		foreach ( $attributes as $r_key => $r_val ) {
			if ( preg_match( '/' . $r_key . '="?([^"]+)"?/im', $string, $matches ) ) {
				$return[ $r_key ] = $matches[1];
			}
		}
		return $return;
	}

	/**
	 * Gets the user metadata.
	 *
	 * Collectively gets userdata from both meta data and user object
	 *
	 * This is a single method to get data from user table like f_name, l_name
	 * or through get_user_meta
	 *
	 * @param      string  $key      The meta or object key
	 * @param      string  $default  The default value to return if not found
	 * @param      int     $user_id  The user identifier Can be null to get current logged in user
	 *
	 * @return     string  The user metadata.
	 */
	public static function get_user_metadata( $key, $default = '', $user_id = null ) {
		// Check if user id is provided
		if ( null == $user_id ) {
			if ( ! is_user_logged_in() ) {
				return $default;
			} else {
				$user_id = get_current_user_id();
			}
		}

		// Now get userdata
		$userdata = get_userdata( $user_id );

		if ( ! $userdata ) {
			return $default;
		}

		// If it has object property
		if ( property_exists( $userdata->data, $key ) ) {
			return strip_tags( $userdata->data->{$key} );
		}

		// Doesn't exist, so check for meta
		$metadata = get_user_meta( $user_id, $key, true );

		// If empty
		if ( empty( $metadata ) ) {
			return $default;
		}

		// Compatibility with array type storage
		if ( is_array( $metadata ) && isset( $metadata['value'] ) ) {
			$metadata = $metadata['value'];
		} else if ( is_array( $metadata ) && ! isset( $metadata['value'] ) ) {
			return $default;
		}

		return strip_tags( $metadata );
	}

	/**
	 * Gets the post metadata.
	 *
	 * @param      string  $key      Meta key. The id of the post can also be
	 *                               given within this key in the format
	 *                               {id}:{key}. If not given, then considers
	 *                               the current post ID of the loop.
	 * @param      string  $default  The default value to return
	 *
	 * @return     string  The fetched post metadata. If no post ID provided and not inside loop, then returns the default value
	 */
	public static function get_post_metadata( $key, $default = '' ) {
		// Check the key parts
		$meta_key = '';
		$post_id = null;
		$key_parts = array();
		// Check if key format specifies post ID
		if ( preg_match( '/([0-9]+)\:(.*)/', $key, $key_parts ) ) {
			$meta_key = $key_parts[2];
			$post_id = $key_parts[1];
		} else {
			// Nothing to do if not in the loop
			$post_id = get_the_ID();
			if ( ! $post_id ) {
				return $default;
			}
			$meta_key = $key;
		}

		// Now retrieve the post meta
		$postmeta = get_post_meta( $post_id, $meta_key, true );
		if ( ! $postmeta ) {
			return $default;
		}
		return $postmeta;
	}

	/**
	 * Gets the request parameter.
	 *
	 * @param      string  $key      The query parameter
	 * @param      string  $default  The default value to return if not found
	 *
	 * @return     string  The request parameter.
	 */
	public static function get_request_parameter( $key, $default = '' ) {
		// If not request set
		if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
			return $default;
		}

		// Set so process it
		return strip_tags( (string) wp_unslash( $_REQUEST[ $key ] ) );
	}

	/**
	 * Gets the request parameter for mcq.
	 *
	 * @param      string  $key      The metakey
	 * @param      array   $default  The default
	 * @param      array   $options  The options
	 *
	 * @return     array   The request parameter for mcq.
	 */
	public static function get_request_parameter_for_mcq( $key, $default = array(), $options = array() ) {
		// If not request set
		if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
			return $default;
		}

		// Set so process it
		$metadata = wp_unslash( $_REQUEST[ $key ] );

		// If empty
		if ( empty( $metadata ) ) {
			return $default;
		}

		// Not empty so let's do it
		$selected_values = array();

		// If array
		if ( is_array( $metadata ) ) {
			// Nothing necessary
		// If string
		} else {
			// Split
			$metadata = explode( "\n", $metadata );
		}

		// Search
		foreach ( $metadata as $key => $val ) {
			$o_key = array_search( $val, $options );
			if ( false !== $o_key ) {
				$selected_values[] = "$o_key";
			}
		}

		if ( empty( $selected_values ) ) {
			return $default;
		} else {
			return $selected_values;
		}
	}

	/**
	 * Gets the user metavalues for mcq.
	 *
	 * @param      string  $key      The metakey
	 * @param      array   $default  The default ones to return if not found
	 * @param      int     $user_id  The user identifier ( optional ) use
	 *                               current user if null provided
	 *
	 * @return     array   The user metavalues for mcq.
	 */
	public static function get_user_metavalues_for_mcq( $key, $default = array(), $options = array(), $user_id = null ) {
		// Check if user id is provided
		if ( null == $user_id ) {
			if ( ! is_user_logged_in() ) {
				return $default;
			} else {
				$user_id = get_current_user_id();
			}
		}

		// Now get userdata
		$userdata = get_userdata( $user_id );
		$return = $default;

		if ( ! $userdata ) {
			return $default;
		}

		// If it has object property
		if ( property_exists( $userdata->data, $key ) ) {
			$metadata = strip_tags( $userdata->data->{$key} );
		// Nothing, so check user meta
		} else {
			$metadata = get_user_meta( $user_id, $key, true );
		}

		// If empty
		if ( empty( $metadata ) ) {
			return $default;
		}

		// Not empty so let's do it
		$selected_values = array();

		// If array
		if ( is_array( $metadata ) ) {
			// Nothing necessary
		// If string
		} else {
			// Split
			$metadata = explode( "\n", $metadata );
		}

		// Search
		foreach ( $metadata as $key => $val ) {
			$o_key = array_search( $val, $options );
			if ( false !== $o_key ) {
				$selected_values[] = "$o_key";
			}
		}

		if ( empty( $selected_values ) ) {
			return $default;
		} else {
			return $selected_values;
		}
	}
}
