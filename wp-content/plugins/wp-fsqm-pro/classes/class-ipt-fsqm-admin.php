<?php
/**
 * IPT FSQM Admin
 * The library of all the administration classes
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm
 * @subpackage Admin Backend classes
 * @version 2.1.4
 */

/*==============================================================================
 * Admin Classes
 *============================================================================*/
/**
 * About class
 */
class IPT_FSQM_About extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_about_nonce';

		parent::__construct();

		$this->icon = 'dashboard';

		global $ipt_fsqm_settings;

		// Check if trackback page is set
		if ( $ipt_fsqm_settings['track_page'] == '0' || ! $ipt_fsqm_settings['track_page'] || $ipt_fsqm_settings['utrack_page'] == '0' || ! $ipt_fsqm_settings['utrack_page'] ) {
			if ( ! isset( $_GET['action'] )|| $_GET['action'] != 'fsqm_setup_wizard' ) {
				add_action( 'admin_notices', array( $this, 'fsqm_trackback_notice' ) );
			}
		}

		add_action( 'admin_init', array( $this, 'check_update' ) );
	}

	public function check_update() {
		global $ipt_fsqm_settings;
		// Check if updates are available
		// Deprecated as we are using envato market plugins
		// But still some might not activate it now
		// So we leave it there
		if ( ! is_plugin_active( 'envato-market/envato-market.php' ) ) {
			if ( $ipt_fsqm_settings['disable_un'] != true ) {
				add_action( 'init', array( $this, 'disable_un' ) );
				add_action( 'admin_notices', array( $this, 'fsqm_update_notice' ) );
			}
		}
	}

	public function disable_un() {
		global $ipt_fsqm_settings;
		if ( ! is_admin() ) {
			return;
		}
		// Disable checking for updates if it is so
		if ( isset( $_REQUEST['fsqm_disable_un'] ) && current_user_can( 'manage_feedback' ) ) {
			$ipt_fsqm_settings['disable_un'] = true;
			update_option( 'ipt_fsqm_settings', $ipt_fsqm_settings );
			$ipt_fsqm_settings = get_option( 'ipt_fsqm_settings' );
			wp_redirect( remove_query_arg( 'fsqm_disable_un', $_SERVER['REQUEST_URI'] ) );
			die();
		}
	}

	public function fsqm_update_notice() {
		if ( ! current_user_can( 'manage_feedback' ) ) {
			return;
		}
		$ipt_fsqm_api = $this->get_ipt_fsqm_json();
		$dismiss_link = esc_url( add_query_arg( array( 'fsqm_disable_un' => 'true' ), $_SERVER['REQUEST_URI'] ) );

		// Main plugin notice
		if ( version_compare( IPT_FSQM_Loader::$version, $ipt_fsqm_api['version'], '<' ) ) {
			?>
			<div class="notice notice-warning" style="padding-right: 38px; position: relative;">
				<p><?php printf( __( '<strong>eForm:</strong> New updated version <code>%1$s</code> is available. You currently have version <code>%3$s</code>. <a class="button button-primary button-large" target="_blank" href="%2$s">Download Now</a>', 'ipt_fsqm' ), $ipt_fsqm_api['version'], $ipt_fsqm_api['url'], IPT_FSQM_Loader::$version ); ?></p>
				<a title="<?php esc_attr_e( 'Dismiss this notice.', 'ipt_fsqm' ); ?>" style="text-decoration: none;" href="<?php echo $dismiss_link; ?>" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'ipt_fsqm' ); ?></span></a>
			</div>
			<?php
		}

		// Addons notice
		if ( ! empty( $ipt_fsqm_api['addons'] ) && is_array( $ipt_fsqm_api['addons'] ) ) {
			foreach ( $ipt_fsqm_api['addons'] as $addon ) {
				// Do not check if not installed or not released
				if ( $addon['class'] == '' || ! class_exists( $addon['class'] ) || $addon['url'] == '' ) {
					continue;
				}
				if ( version_compare( $addon['class']::$version, $addon['version'], '<' ) ) {
					?>
					<div class="notice notice-warning" style="padding-right: 38px; position: relative;">
						<p><?php printf( __( '<strong>%4$s:</strong> New updated version <code>%1$s</code> is available. You currently have version <code>%3$s</code>. <a class="button button-primary button-large" target="_blank" href="%2$s">Download Now</a>', 'ipt_fsqm' ), $addon['version'], $addon['url'], $addon['class']::$version, $addon['name'] ); ?></p>
						<a title="<?php esc_attr_e( 'Dismiss this notice.', 'ipt_fsqm' ); ?>" style="text-decoration: none;" href="<?php echo $dismiss_link; ?>" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Dismiss this notice.', 'ipt_fsqm' ); ?></span></a>
					</div>
					<?php
				}
			}
		}
	}

	public function fsqm_trackback_notice() {
		?>
		<div class="notice notice-warning">
			<p><?php _e( 'eForm requires setting up two specific pages. To get started, please <a href="admin.php?page=ipt_fsqm_about&action=fsqm_setup_wizard" class="button-primary">click here</a>.', 'ipt_fsqm' ); ?></p>
		</div>
		<?php
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm - WordPress Form Builder', 'ipt_fsqm' ), __( 'Addons & Guide', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_about', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'fsqm_setup_wizard' ) {
			$this->setup_wizard();
			return;
		}
		$this->wizard_complete();
	}

	/*==========================================================================
	 * Interacting with WPQuark JSON API
	 *========================================================================*/

	public function get_ipt_fsqm_json() {
		if ( false === ( $ipt_fsqm_json = get_transient( 'ipt_fsqm_json' ) ) ) {
			// It wasn't there, so regenerate the data and save the transient
			// And reform version and addons
			// Then return
			$do_not_set = false;
			$ipt_fsqm_api = wp_remote_get( 'https://wpquark.com/wp-json/ipt-api/v1/fsqm/' );

			if ( ! is_wp_error( $ipt_fsqm_api ) ) {
				try {
					$ipt_fsqm_json = $this->formulate_response_json( wp_remote_retrieve_body( $ipt_fsqm_api ) );
					if ( $ipt_fsqm_json == false ) {
						$ipt_fsqm_json = array(
							'version' => IPT_FSQM_Loader::$version,
							'url' => '',
							'addons' => array(),
						);
						$do_not_set = true;
					}
				} catch ( Exception $e ) {
					// Some error
					// So we revert back to empty values
					$ipt_fsqm_json = array(
						'version' => IPT_FSQM_Loader::$version,
						'url' => '',
						'addons' => array(),
					);
					$do_not_set = true;
				}
			} else {
				// Some error
				// So we revert back to empty values
				$ipt_fsqm_json = array(
					'version' => IPT_FSQM_Loader::$version,
					'url' => '',
					'addons' => array(),
				);
				$do_not_set = true;
			}

			if ( ! $do_not_set ) {
				// Set the transient and make it expire after 7 days
				set_transient( 'ipt_fsqm_json', $ipt_fsqm_json, ( 7 * 24 * 60 * 60 ) );
			} else {
				// Set the transient but only for a day
				// Why make the site slow because of WPQuark.com errors?
				set_transient( 'ipt_fsqm_json', $ipt_fsqm_json, ( 24 * 60 * 60 ) );
			}
		}
		return $ipt_fsqm_json;
	}

	private function formulate_response_json( $body ) {
		$json = json_decode( $body, true );
		if ( ! is_array( $json ) || ! isset( $json['ep'] ) ) {
			$json = false;
		}
		return $json;
	}


	/*==========================================================================
	 * Primary Setup
	 *========================================================================*/
	public function setup_wizard() {

		global $ipt_fsqm_settings;

		if ( ! current_user_can( 'manage_feedback' ) ) {
			$this->ui->msg_error( __( 'You do not have sufficient permission to complete FSQM setup. Please contact your administrator', 'ipt_fsqm' ) );
			return;
		}

		// respond to the wizard
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			$this->wizard_update();
		}
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Setup Wizard', 'ipt_fsqm' ), false );
		echo '<form method="POST" action="admin.php?page=ipt_fsqm_about&action=fsqm_setup_wizard">';
		if ( $ipt_fsqm_settings['email'] == '' ) {
			$this->ui->iconbox( __( 'Set Notification Email', 'ipt_fsqm' ), array( $this, 'wizard_email' ), 'envelope' );
		} else if ( $ipt_fsqm_settings['track_page'] == '0' || ! $ipt_fsqm_settings['track_page'] ) {
			$this->ui->iconbox( __( 'Set Trackback Page', 'ipt_fsqm' ), array( $this, 'wizard_track' ), 'file-text' );
		} else if ( $ipt_fsqm_settings['utrack_page'] == '0' || ! $ipt_fsqm_settings['utrack_page'] ) {
			$this->ui->iconbox( __( 'Set User Portal Page', 'ipt_fsqm' ), array( $this, 'wizard_utrack' ), 'user' );
		} else {
			$this->ui->msg_okay( __( 'Congratulations! You have completed the setup of eForm. Now head to <a href="admin.php?page=ipt_fsqm_all_forms">All Forms</a> pages to get started.', 'ipt_fsqm' ) );
		}
		echo '</form>';
		$this->index_foot( false, '', '', false );
	}

	public function wizard_complete() {
		global $ipt_fsqm_info;
		delete_transient( 'ipt_fsqm_json' );
		$ipt_fsqm_json = $this->get_ipt_fsqm_json();

		$addons = $ipt_fsqm_json['addons'];
		?>
<style type="text/css">
	.plugin-card a {
		text-decoration: none;
	}
</style>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var hashtags = [];
		$('.nav-tab-wrapper a').each(function() {
			var elm = $( $(this).attr('href') );
			hashtags[$(this).attr('href')] = $(this);
			if ( $(this).hasClass('nav-tab-active') ) {
				elm.show();
			} else {
				elm.hide();
			}
		});
		$('.nav-tab-wrapper').on( 'click', 'a', function(e) {
			e.preventDefault();
			$(this).siblings('a').removeClass('nav-tab-active').each(function() {
				$( $(this).attr('href') ).stop(true, true).hide();
			});
			$(this).addClass('nav-tab-active');
			$( $(this).attr('href') ).fadeIn('fast');
		} );
		// Check if hashtag is there
		var winHash = window.location.hash.replace( '!', '' );
		if ( hashtags[winHash] != undefined ) {
			hashtags[winHash].trigger('click');
		}
	});
</script>
<div class="wrap about-wrap">
	<h1><?php printf( __( 'Welcome to %2$s - %1$s', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, '<i class="ipt-fsqmic-eform-horizontal"></i>' ); ?></h1>
	<div class="about-text"><?php printf( __( 'Thank you for installing eForm version %s. Please see below for available addons and new features.', 'ipt_fsqm' ), IPT_FSQM_Loader::$version ); ?></div>
	<div class="wp-badge" style="background-image: none;background-color: #CB3340;height: 20px; max-width: 140px;"><i class="ipt-fsqmic-eform-large" style="color: #ffffff;position: absolute;left: 1px;top: 2px;font-size: 138px;"></i></div>

	<h2 class="nav-tab-wrapper">
		<a href="#fsqm-addons" class="nav-tab nav-tab-active"><?php _e( 'Addons', 'ipt_fsqm' ); ?></a>
		<a href="#fsqm-whats-new" class="nav-tab"><?php _e( 'What\'s New', 'ipt_fsqm' ); ?></a>
		<a href="#fsqm-video-guide" class="nav-tab"><?php _e( 'Video Guides', 'ipt_fsqm' ); ?></a>
	</h2>
	<div id="fsqm-whats-new">
		<p>
			<img src="<?php echo plugins_url( '/static/admin/images/wpquark-branding.jpg', IPT_FSQM_Loader::$abs_file ); ?>" />
		</p>
		<p>
			<img src="<?php echo plugins_url( '/static/admin/images/eform-preview.jpg', IPT_FSQM_Loader::$abs_file ); ?>" />
		</p>
		<h2><?php printf( __( '%2$s<sup style="font-size: 0.5em;">v%1$s</sup> - WordPress Form Builder', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, '<i class="ipt-fsqmic-eform-horizontal"></i>' ); ?></h2>
		<p>
			<?php _e( 'Last year, during Christmas eForm v3.4 came with astonishing features like WooCommerce Integration, WordPress Guest Blogging, Registration and advanced statistical charts.', 'ipt_fsqm' ); ?>
		</p>
		<p><?php _e( 'eForm 3.6 brings more happiness this year. We have removed old and obsolete themes and came up with fresh looking material themes with built-in customizer to create your own.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( 'But that is not all. We have a new element called <strong><a href="https://eform.live/examples/#el-ex-modal-43">Repeatable Element</a></strong> in our arsenal which you help you collect tabular data from your users.' ) ?></p>
		<p><?php _e( 'We have also refreshed admin interface to give you a better experience. ' ) ?></p>
		<p>
			<?php _e( 'For all of you who were enjoying eForm, we hope that you find our changes arresting.', 'ipt_fsqm' ); ?>
		</p>
		<p>
			<?php _e( 'And if you are new to our system, we believe that you will find eForm unparalled & appealing.', 'ipt_fsqm' ); ?>
		</p>
		<p>
			<?php _e( 'Thank you for believing - <a href="https://wpquark.com">Team @ WPQuark</a>.', 'ipt_fsqm' ); ?>
		</p>
		<h3><?php _e( 'Release Highlights', 'ipt_fsqm' ); ?></h3>
		<p><?php _e( '<strong>Version 3.7.5</strong> Various bug fixes and remove unused files.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.7.4</strong> Various datepicker improvements and several bug fixes.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.7.3</strong> Upgrade MailChimp to v3 and Backend bug fixes.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.7.2</strong> RTL Improvement and various bug fixes.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.7.1</strong> Mailerlite & Enormail integration with improvements of various form elements.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.7.0</strong> reCaptcha Support, Conditional Administrative Email, Vertical Sliders, RTL support, Post meta based prefil, Equals to validation along with WooCommerce Improvements', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.6</strong> made slider & ranges tooltips optional.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.5</strong> form categories got some love.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.4</strong> added length, contains and does not contain conditional logic for single range element.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.3</strong> various admin UI improvements and added user meta related form templates.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.2</strong> added appearance option for thumbnail selectors, main container heading and various UI improvements.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.1</strong> added user meta updater in core integration and color for signature element.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.6.0</strong> added defaults and readonly options for many form elements.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.5.0</strong> added material inspired themes, new admin interface and repeatable element.', 'ipt_fsqm' ); ?></p>
		<p><?php _e( '<strong>Version 3.4.0</strong> added WooCommerce and WP Core integration, Statistics Chart Shortcodes, improved mailing system with respect to payments.', 'ipt_fsqm' ); ?></p>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'Material Themes', 'ipt_fsqm' ); ?></h3>
				<p><?php _e( 'eForm v3.5 comes with 38 material inspired themes. We even have a customizer from where you can pick your color and create your own theme.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'All previous themes has been deprecated and removed. If your forms were using one, it will be upgraded automatically.' ) ?></p>
				<p><?php _e( 'We plan to revamp Bootstrap theme and add more skins in future.', 'ipt_fsqm' ); ?></p>
			</div>
			<div class="col">
				<img src="<?php echo plugins_url( '/static/admin/images/eform-material-theme.jpg', IPT_FSQM_Loader::$abs_file ); ?>" alt="<?php esc_attr_e( 'Eform Material Theme', 'ipt_fsqm' ) ?>">
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'Repeatable Element', 'ipt_fsqm' ); ?></h3>
				<p><?php _e( 'eForm v3.5 comes with a new form element, called Repeatable Element.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'You can use this to give users option to add more "rows" to the form. Every repeatable element can have any number of elements from radio, checkboxes, dropdowns or texts.' ) ?></p>
				<p><?php _e( 'Given the option, your users can repeat the grouped elements in any number they want.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'Of course we have provided configuration for limiting repeats between a minimum and a maximum value.', 'ipt_fsqm' ); ?></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/ddahLkHcyjk" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'Refreshed Form Builder', 'ipt_fsqm' ); ?></h3>
				<p><?php _e( 'We have completely changed the behavior of eForm Form Builder. It is now modern, compact and faster.' ) ?> <?php _e( 'We have tested the new form builder to easily handle over 300 form elements.', 'ipt_fsqm' ); ?> <?php _e( 'The elements settings, conditional states and form settings are now grouped in a smart way to give you better access.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'The builder has four sections to help you fully customize your form:', 'ipt_fsqm' ); ?>
				<ul class="ul-disc">
					<li><?php _e( '<strong>Form Settings</strong>: Use the tabs at the top to name your form, select a form type and customize the settings. We have included over 30 themes to get you started, and you can also play around with different fonts and sizes.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( '<strong>Form Elements</strong>: Over 35 awesome form elements are shown in a list on the right hand side. Simply add a container and drag the elements you want to include.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( '<strong>Form Layout</strong>: On the middle of the screen you can arrange the layout of your form. Add several containers and then drag and drop elements to build your preferred layout. The form builder will automatically save your layout at regular intervals, so you won’t lose any of your changes.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( '<strong>Element Settings</strong>: Once you click on an form element, the settings will open up on the left hand side of the screen. It can have, appearance, interface, items, validation and logic sections from where you can customize how the form element would work.', 'ipt_fsqm' ); ?></li>
				</ul>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/62ILzHHFkWE" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'WooCommerce Integration', 'ipt_fsqm' ); ?></h3>
				<p><?php _e( 'eForm now integrates smoothly with WooCommerce. This brings the power of WooCommerce checkout and eForm customizability together for your users.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-disc">
					<li><?php _e( 'Create a dummy WooCommerce Product and note down the ID.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Setup an eForm with mathematical element and enable WooCommerce from Payment tab.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Simply let eForm decide product price and WooCommerce handles the rest.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Optionally use conditional logic to select different product for different cases.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php _e( 'Product attributes are added directly from the mathematical formula and are always shown both to user and admin. So you would not need to check in the submission to get the data.', 'ipt_fsqm' ); ?></p>
				<p><a href="https://wpquark.com/kb/fsqm/payment-system/woocommerce-integration-eform/" target="_blank"><?php _e( 'Read More', 'ipt_fsqm' ); ?></a></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/Jj4TvH2oTrs" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'WP Core Integration', 'ipt_fsqm' ); ?></h3>
				<p><?php _e( 'eForm v3.4 has introduced features to incorporate some of the core WordPress functionalities.' ) ?></p>
				<ul class="ul-disc">
					<li><?php _e( 'Show a login form through shortcode. Redirect to desired page after login.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Let user register through submitting eForm. Additionally assign custom or built in user meta data through any of the eForm elements.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Let user submit guest post anonymously or while logged in. Even register user while submitting a guest post and assign the article to the newly registered user.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Let user update metadata by filling out an eForm.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php _e( 'Check the video to get an idea of how WP Core integration works. Do checkout our documentation for more information and implementation guide.', 'ipt_fsqm' ); ?></p>
				<p><a href="https://wpquark.com/kb/fsqm/wp-core-integrations/" target="_blank"><?php _e( 'Read More', 'ipt_fsqm' ); ?></a></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/XCDLoNHuOFo" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3><?php _e( 'Statistics Shortcodes', 'ipt_fsqm' ); ?></h3>
				<p><?php _e( 'Now show-off how your forms and users are performing with the new statistics shortcodes.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'With eForm v3.4 we have introduced a total of six beautifully crafted shortcodes for publishing quick stats.', 'ipt_fsqm' ); ?></p>
				<ul class="ul-disc">
					<li><?php _e( 'Form and User submission breakdown.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Form and User score breakdown.', 'ipt_fsqm' ); ?></li>
					<li><?php _e( 'Form and User overall submission statistics.', 'ipt_fsqm' ); ?></li>
				</ul>
				<p><?php _e( 'Each shortcode can handle multiple forms at once. Moreover, user shortcodes can be tuned for currently logged in users and can show a login form otherwise.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'Our advice is to use user statistics in the user portal page.', 'ipt_fsqm' ); ?></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/MIyMDOAH3Ug" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="changelog">
			<h2><?php _e( 'Under the Hood', 'ipt_fsqm'); ?></h2>
				<div class="under-the-hood three-col">
					<div class="col">
						<h3><?php _e( 'Developer\'s API', 'ipt_fsqm' ); ?></h3>
						<p><?php _e( 'eForm now comes with a comprehensive <a href="http://wpquark.com/kb/fsqm/fsqm-api/" target="_blank">Developer\'s API</a>. If you wish to extend eForm with newer elements or want to integrate with your mailing system, it is now possible without touching the core.', 'ipt_fsqm' ); ?></p>
					</div>
					<div class="col">
						<h3><?php _e( 'Complete Javascript Rewrite', 'ipt_fsqm' ); ?></h3>
						<p><?php _e( 'Both frontend and backend rendering are now upto 400% faster. We have rewritten every javascript code from scratch and we now enqueue minified files only. But all minified files have source mapping which is cool for console debugging.', 'ipt_fsqm' ); ?></p>
					</div>
					<div class="col">
						<h3><?php _e( 'Updated Format Strings', 'ipt_fsqm' ); ?></h3>
						<p><?php _e( 'Now both admin and user notification email, as well as success message have all the <a href="http://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/" target="_blank">formatting strings</a> available. We even added mathematical evaluator fields to the format strings.' ) ?></p>
					</div>
				</div>

				<div class="under-the-hood three-col">
					<div class="col">
						<h3><?php _e( 'Theme My Login Compatibility', 'ipt_fsqm' ); ?></h3>
						<p><?php _e( 'eForm now just works with TML, peace.', 'ipt_fsqm' ); ?></p>
					</div>
					<div class="col">
						<h3><?php _e( 'Visual Composer Compatibility', 'ipt_fsqm' ); ?></h3>
						<p><?php _e( 'eForm now works with any version of Visual Composer. We implemented a rather smart way to bypass any version of jQuery WayPoints library loaded in any order.', 'ipt_fsqm' ); ?></p>
					</div>
					<div class="col">
						<h3><?php _e( 'Admin Notification Improvement', 'ipt_fsqm' ); ?></h3>
						<p><?php _e( 'Admin <code>from</code> address can now be changed. It adds a <code>from</code> and <code>reply-to</code> header while keeping the <code>sender</code> header originating from your domain.', 'ipt_fsqm' ); ?></p>
						<p><?php _e( 'Additionally you can configure to add just <code>reply-to</code> and not add <code>from</code> to play nice with Yahoo Emails.', 'ipt_fsqm' ); ?></p>
					</div>
				</div>
			<div class="return-to-dashboard">
				<a href="admin.php?page=ipt_fsqm_dashboard"><?php _e( 'Go to eForm → Dashboard', 'ipt_fsqm' ); ?></a>
			</div>
		</div>

		<div class="clear"></div>
	</div>
	<div id="fsqm-video-guide" class="ipt_uif">
		<h3><?php _e( 'Basic eForm Setup', 'ipt_fsqm' ); ?></h3>
		<div class="golden-video-wrap">
			<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PLtWVAk_srzCa7qxBN4oqW9KjAdlW6wjA1" frameborder="0" allowfullscreen></iframe>
		</div>
		<h3><?php _e( 'Advanced eForm Setup', 'ipt_fsqm' ); ?></h3>
		<div class="golden-video-wrap">
			<iframe width="560" height="315" src="https://www.youtube.com/embed/videoseries?list=PLtWVAk_srzCbZijXogkXQNoC5Pc_U5h7z" frameborder="0" allowfullscreen></iframe>
		</div>
		<div class="clear"></div>
	</div>
	<div class="clear"></div>
</div>
<?php $this->populate_addons( $addons ); ?>
<div class="ipt_uif">
	<p>
		<?php printf( __( '<strong>Plugin Version:</strong> <em>%s(Script)/%s(DB)</em>', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, $ipt_fsqm_info['version'] ); ?> | <?php _e( 'Icons Used from: ', 'ipt_fsqm' ); ?> <a href="http://icomoon.io/" title="IcoMoon" target="_blank">IcoMoon</a>
		<br />
		<span class="description">
			<?php _e( 'If the Script version and DB version do not match, then deactivate the plugin and reactivate again. This should solve the problem. If the problem persists then contact the developer.', 'ipt_fsqm' ); ?>
		</span>
	</p>
</div>
		<?php
	}

	public function populate_addons( $addons, $id = 'fsqm-addons' ) {
		if ( empty( $addons ) || ! is_array( $addons ) ) {
			?>
			<p><?php _e( 'We have not published any addons yet. Please stay tuned.', 'ipt_fsqm' ) ?></p>
			<?php
			return;
		}
		?>
<style type="text/css">
	body .plugin-card-top {
		min-height: 190px;
	}
</style>
<div id="fsqm-addons" class="wrap">
	<div id="the-list">
		<?php foreach ( $addons as $akey => $addon ) : ?>
		<?php
		$exists = false;
		if ( class_exists( $addon['class'] ) ) {
			$exists = true;
		}
		?>
		<div class="plugin-card plugin-card-<?php echo $akey; ?>">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<?php if ( $addon['url'] != '' ) : ?>
						<a href="<?php echo $addon['url']; ?>">
						<?php endif; ?>
						<?php echo $addon['name']; ?>
						<img src="<?php echo $addon['image']; ?>" class="plugin-icon" alt="">
						<?php if ( $addon['url'] != '' ) : ?>
						</a>
						<?php endif; ?>
					</h3>
				</div>
				<div class="action-links">
					<ul class="plugin-action-buttons">
						<?php if ( $exists ) : ?>
						<li><button class="install-now button disabled" disabled="disabled"><?php _e( 'Active', 'ipt_fsqm' ); ?></button></li>
						<li><small><?php printf( __( 'version: %1$s', 'ipt_fsqm' ), $addon['class']::$version ); ?></small></li>
						<?php else : ?>

						<?php if ( $addon['url'] != '' ) : ?>
						<li><a target="_blank" class="install-now button" href="<?php echo $addon['url']; ?>"><?php _e( 'Install Now', 'ipt_fsqm' ); ?></a></li>
						<?php else : ?>
						<li><a target="_blank" class="install-now button disabled" disabled href="javascript:void(null);"><?php _e( 'Coming Soon', 'ipt_fsqm' ); ?></a></li>
						<?php endif; ?>

						<?php endif; ?>
					</ul>
				</div>
				<div class="desc column-description">
					<?php echo wpautop( $addon['description'] ); ?>
					<p class="authors"> <cite><?php printf( __( 'By <a href="%1$s">%2$s</a>', 'ipt_fsqm' ), $addon['authorurl'], $addon['author'] ); ?></cite></p>
				</div>
			</div>
			<div class="plugin-card-bottom">
				<div class="vers column-rating">
					<div class="star-rating" title="<?php printf( esc_attr__( '%1$s rating based on %2$s ratings', 'ipt_fsqm' ), $addon['star'], $addon['starnum'] ); ?>"><?php
					$max_star = floor( $addon['star'] );
					// First print all the full stars
					for ( $i = 1; $i <= $max_star && $i <= 5; $i++ ) {
						echo '<div class="star star-full"></div>';
					}
					// Now adjust the half/full star for next
					if ( $max_star < 5 ) {
						$adjuster = $addon['star'] - $max_star;
						if ( $adjuster > 0.7 ) {
							echo '<div class="star star-full"></div>';
						} else if ( $adjuster <= 0.7 && $adjuster >= 0.3 ) {
							echo '<div class="star star-half"></div>';
						} else {
							echo '<div class="star star-empty"></div>';
						}
					}
					// Now print the rest empty stars
					if ( ( $max_star + 1 ) < 5 ) {
						for ( $i = 0; $i < ( 4 - $max_star ); $i++ ) {
							echo '<div class="star star-empty"></div>';
						}
					}
					?></div>
					<span class="num-ratings">(<?php echo $addon['starnum']; ?>)</span>
				</div>
				<div class="column-updated">
					<?php if ( $addon['url'] != '' ) : ?>
					<strong><?php _e( 'Last Updated:' , 'ipt_fsqm' ); ?></strong> <span> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $addon['date'] ) ); ?> <small><?php printf( _x( '( %1$s )', 'ipt_fsqm_addon_version', 'ipt_fsqm' ), $addon['version'] ); ?></small></span>
					<?php else : ?>
					<strong><?php _e( 'Expected Release:' , 'ipt_fsqm' ); ?></strong> <span> <?php echo date_i18n( get_option( 'date_format' ), strtotime( $addon['date'] ) ); ?></span>
					<?php endif; ?>
				</div>
				<div class="column-downloaded">
					<?php if ( $addon['url'] != '' && $addon['downloaded'] > 0 ) : ?>
					<?php printf( __( '%d+ sales', 'ipt_fsqm' ), $addon['downloaded'] );; ?>
					<?php endif; ?>
				</div>
				<div class="column-compatibility">
					<?php if ( version_compare( get_bloginfo( 'version' ), $addon['compatible'], '>=' ) ) : ?>
					<span class="compatibility-compatible"><?php _e( '<strong>Compatible</strong> with your version of WordPress', 'ipt_fsqm' ); ?></span>
					<?php else : ?>
					<span class="compatibility-untested"><?php _e( 'Untested with your version of WordPress', 'ipt_fsqm' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
</div>
<div class="clear"></div>
		<?php
	}

	public function wizard_update() {
		global $ipt_fsqm_settings;
		if ( isset( $this->post['fsqm']['email'] ) ) {
			$ipt_fsqm_settings['email'] = $this->post['fsqm']['email'];
		}
		if ( isset( $this->post['fsqm']['track'] ) ) {
			$page_title = $this->post['fsqm']['track']['title'];
			$form_label = $this->post['fsqm']['track']['label'];
			$template = $this->post['fsqm']['track']['template'];
			$submit = $this->post['fsqm']['track']['submit'];

			$shortcode = '[ipt_fsqm_trackback label="' . $form_label . '" submit="' . $submit . '"]';

			$page_id = wp_insert_post( array(
				'post_title' => $page_title,
				'post_content' => $shortcode,
				'page_template' => $template,
				'post_type' => 'page',
				'post_status' => 'publish',
			) );
			if ( $page_id ) {
				$ipt_fsqm_settings['track_page'] = "$page_id";
				?>
				<div class="notice notice-success">
					<p><?php printf( __( 'Successfully created Trackback page. To edit, please <a target="_blank" href="post.php?post=%d&action=edit">click here</a>.', 'ipt_fsqm' ), $page_id ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-error">
					<p><?php printf( __( 'Cound not create the trackback page. Something must have went wrong. Please create a page manually and set it up from eForm > Settings.', 'ipt_fsqm' ), $page_id ); ?></p>
				</div>
				<?php
			}
		}
		if ( isset( $_POST['fsqm']['utrack'] ) ) {
			$page_title = $this->post['fsqm']['utrack']['ptitle'];
			$template = $this->post['fsqm']['utrack']['template'];

			$utrack_defaults = array(
				// 'content' => __( 'Welcome %NAME%. Below is the list of all submissions you have made.', 'ipt_fsqm' ),
				'nosubmission' => __( 'No submissions yet.', 'ipt_fsqm' ),
				'login' => __( 'You need to login in order to view your submissions.', 'ipt_fsqm' ),
				'show_register' => '0',
				'show_forgot' => '0',
				'formlabel' => __( 'Form', 'ipt_fsqm' ),
				'filters' => '0',
				'showcategory' => '0',
				'categorylabel' => __( 'Category', 'ipt_fsqm' ),
				'datelabel' => __( 'Date', 'ipt_fsqm' ),
				'showscore' => '0',
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
				'logout_r' => '',
			);

			$shortcode = '[ipt_fsqm_utrackback';

			foreach ( $utrack_defaults as $key => $val ) {
				$attr = ' ' . $key . '="';
				// Checkboxes
				if ( is_numeric( $val ) && $val <= 1 ) {
					if ( isset( $this->post['fsqm']['utrack'][$key] ) && '' != $this->post['fsqm']['utrack'][$key] ) {
						$attr .= '1';
					} else {
						$attr .= '0';
					}
				// Else it is text
				} else {
					if ( isset( $this->post['fsqm']['utrack'][$key] ) && '' != $this->post['fsqm']['utrack'][$key] ) {
						$attr .= str_replace( '"', '&quot;', $this->post['fsqm']['utrack'][$key] );
					} else {
						$attr .= $val;
					}
				}
				$attr .= '"';
				$shortcode .= $attr;
			}

			$shortcode .= ']' . $this->post['fsqm']['utrack']['content'] . '[/ipt_fsqm_utrackback]';

			$page_id = null;
			$page_id = wp_insert_post( array(
				'post_title' => $page_title,
				'post_content' => $shortcode,
				'page_template' => $template,
				'post_type' => 'page',
				'post_status' => 'publish',
			) );
			if ( $page_id ) {
				$ipt_fsqm_settings['utrack_page'] = "$page_id";
				?>
				<div class="notice notice-success">
					<p><?php printf( __( 'Successfully created User Portal page. To edit, please <a target="_blank" href="post.php?post=%d&action=edit">click here</a>.', 'ipt_fsqm' ), $page_id ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-error">
					<p><?php printf( __( 'Cound not create the trackback page. Something must have gone wrong. Please create a page manually and set it up from eForm > Settings.', 'ipt_fsqm' ), $page_id ); ?></p>
				</div>
				<?php
			}
		}
		update_option( 'ipt_fsqm_settings', $ipt_fsqm_settings );
	}

	public function wizard_email() {
		$buttons = array(
			array( __( 'Next', 'ipt_fsqm' ), '', 'large', 'primary', 'normal', array(), 'submit' ),
		);
		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'fsqm[email]', __( 'Set Global Notification Email', 'ipt_fsqm' ) ); ?></th>
			<td>
				<?php $this->ui->text( 'fsqm[email]', get_option( 'admin_email' ), __( 'Email address', 'ipt_fsqm' ) ); ?>
				<span class="description"><?php _e( 'This can be changed later from eForm > Settings', 'ipt_fsqm' ); ?></span>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->buttons( $buttons ); ?>
		<?php
	}

	public function wizard_track() {
		$buttons = array(
			array( __( 'Next', 'ipt_fsqm' ), '', 'large', 'primary', 'normal', array(), 'submit' ),
		);

		?>
<table class="form-table">
	<tbody>
		<tr>
			<th><?php $this->ui->generate_label( 'fsqm[track][title]', __( 'Page Title', 'ipt_fsqm' ) ); ?></th>
			<td><?php $this->ui->text( 'fsqm[track][title]', __( 'Submission Confirmed', 'ipt_fsqm' ), __(  'WP Page Title', 'ipt_fsqm' ) ); ?></td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'fsqm[track][template]', __( 'Page Template', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->select( 'fsqm[track][template]', $this->get_template_items(), '' ); ?><br />
				<span class="description"><?php _e( 'You can change the page template later from WordPress Pages.', 'ipt_fsqm' ); ?></span>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'fsqm[track][label]', __( 'Form Label', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'fsqm[track][label]', __( 'Track Code', 'ipt_fsqm' ), __( 'Required', 'ipt_fsqm' ) ); ?><br />
				<span class="description"><?php _e( 'Enter the label of the text input where the surveyee will need to paste his/her trackback code.', 'ipt_fsqm' ); ?></span>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'fsqm[track][submit]', __( 'Submit Button Label', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<?php $this->ui->text( 'fsqm[track][submit]', __( 'Submit', 'ipt_fsqm' ), __( 'Required', 'ipt_fsqm' ) ); ?><br />
				<span class="description"><?php _e( 'Enter the label of the submit button.', 'ipt_fsqm' ); ?></span>
			</td>
		</tr>
	</tbody>
</table>
<?php $this->ui->buttons( $buttons ); ?>
		<?php

	}

	public function wizard_utrack() {
		$buttons = array(
			array( __( 'Finish', 'ipt_fsqm' ), '', 'large', 'primary', 'normal', array(), 'submit' ),
		);
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
				'showremarks' => __( 'Show Admin Remarks Column', 'ipt_fsqm' ),
				'remarkslabel' => __( 'Admin Remarks Label', 'ipt_fsqm' ),
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
			'filters' => '1',
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
			'logout_r' => '',
		);
		$form_element = new IPT_FSQM_Form_Elements_Base();
		$themes = $form_element->get_available_themes();
		?>
		<h3><?php _e( 'Page Settings', 'ipt_fsqm' ); ?></h3>
		<table class="form-table">
			<tbody>
				<tr>
					<th><?php $this->ui->generate_label( 'fsqm[utrack][ptitle]', __( 'Page Title', 'ipt_fsqm' ) ); ?></th>
					<td><?php $this->ui->text( 'fsqm[utrack][ptitle]', __( 'Browse Submissions', 'ipt_fsqm' ), __(  'WP Page Title', 'ipt_fsqm' ) ); ?></td>
				</tr>
				<tr>
					<th>
						<?php $this->ui->generate_label( 'fsqm[utrack][template]', __( 'Page Template', 'ipt_fsqm' ) ); ?>
					</th>
					<td>
						<?php $this->ui->select( 'fsqm[utrack][template]', $this->get_template_items(), '' ); ?><br />
						<span class="description"><?php _e( 'You can change the page template later from WordPress Pages.', 'ipt_fsqm' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<?php foreach ( $utrack_attrs as $attr => $labels ) : ?>
		<h3><?php echo $utrack_labels[$attr]; ?></h3>
		<table class="form-table">
			<?php foreach ( $labels as $key => $label ) : ?>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'fsqm[utrack][' . $key . ']', $label ); ?>
				</th>
				<td>
					<?php if ( $key == 'content' ) : ?>
					<?php $this->ui->textarea( 'fsqm[utrack][' . $key . ']', $utrack_defaults[$key], __( 'Required', 'ipt_fsqm' ) ); ?>
					<?php elseif ( $key == 'theme' ) : ?>
					<select id="fsqm_utrack_<?php echo $key; ?>" name="fsqm[utrack][<?php echo $key; ?>]" class="ipt_uif_select">
						<?php foreach ( $themes as $theme_grp ) : ?>
						<optgroup label="<?php echo $theme_grp['label']; ?>">
							<?php foreach ( $theme_grp['themes'] as $theme_key => $theme ) : ?>
							<option value="<?php echo $theme_key; ?>"<?php if ( $utrack_defaults[$key] == $theme_key ) echo ' selected="selected"'; ?>><?php echo $theme['label']; ?></option>
							<?php endforeach; ?>
						</optgroup>
						<?php endforeach; ?>
					</select>
					<?php elseif ( is_numeric( $utrack_defaults[$key] ) && $utrack_defaults[$key] <= 1 ) : ?>
					<input type="checkbox" value="1" class="" id="fsqm_utrack_<?php echo $key; ?>" name="fsqm[utrack][<?php echo $key; ?>]"<?php if ( $utrack_defaults[$key] == '1' ) echo ' checked="checked"'; ?> />
					<?php else : ?>
					<?php $this->ui->text( 'fsqm[utrack][' . $key . ']', $utrack_defaults[$key], '' ); ?>
					<?php endif; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</table>
		<?php endforeach; ?>
		<?php $this->ui->buttons( $buttons ); ?>
		<?php
	}

	public function get_template_items() {
		$templates = get_page_templates();
		$template_items = array();
		$template_items[] = array(
			'value' => '',
			'label' => __( 'Default page template', 'ipt_fsqm' ),
		);
		if ( ! is_array( $templates ) || count( $templates ) < 1 ) {
			return $template_items;
		}
		foreach ( $templates as $label => $value ) {
			$template_items[] = array(
				'value' => $value,
				'label' => $label,
			);
		}
		return $template_items;
	}
}
/**
 * Dashboard class
 */
class IPT_FSQM_Dashboard extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_dashboard_nonce';

		parent::__construct();

		$this->icon = 'dashboard';
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_menu_page( __( 'eForm - WordPress Form Builder', 'ipt_fsqm' ), __( 'eForm', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_dashboard', array( $this, 'index' ), 'dashicons-fsqm', 25 );
		add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm - WordPress Form Builder', 'ipt_fsqm' ), __( 'Dashboard', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_dashboard', array( $this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Dashboard', 'ipt_fsqm' ), false );
?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		var protocol = window.location.protocol;
		$.getScript(protocol + '//www.google.com/jsapi', function() {
			google.load('visualization', '1.0', {
				packages : ['corechart'],
				callback : function() {
					if ( typeof( drawLatestTen ) == 'function' ) {
						drawLatestTen();
					}
					if ( typeof( drawOverallPie ) == 'function' ) {
						drawOverallPie();
					}
				}
			});
		});
	});
</script>
<div class="ipt_uif_left_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Latest Submission Statistics', 'ipt_fsqm' ), array( $this, 'meta_stat' ), 'stats' ); ?>
</div></div>
<div class="ipt_uif_right_col"><div class="ipt_uif_col_inner">
	<?php $this->ui->iconbox( __( 'Overall Submission Statistics', 'ipt_fsqm' ), array( $this, 'meta_overall' ), 'pie' ); ?>
</div></div>
<div class="clear"></div>
<?php $this->ui->iconbox( __( 'Latest 10 Submissions', 'ipt_fsqm' ) . '<a class="button ipt_uif_button" href="' . admin_url( 'admin.php?page=ipt_fsqm_view_all_submissions' ) . '">' . __( 'View all', 'ipt_fsqm' ) . '</a>', array( $this, 'meta_ten' ), 'list2' ); ?>
<div class="clear"></div>
<?php $this->ui->iconbox( __( 'Generate Embed Code for Standalone Forms', 'ipt_fsqm' ), array( $this, 'meta_embed_generator' ), 'embed' ); ?>
<div class="clear"></div>
		<?php
		$this->index_foot( false );
	}



	/*==========================================================================
	 * METABOX CB
	 *========================================================================*/

	public function meta_embed_generator() {
		$forms = IPT_FSQM_Form_Elements_Static::get_forms();
		if ( null == $forms || empty( $forms ) ) {
			$this->ui->msg_error( __( 'You have not created any forms yet.', 'ipt_fsqm' ) );
			return;
		}
		$default_permalink = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $forms[0]->id );
		$default_code = '<iframe src="' . $default_permalink['url'] . '" width="960" height="480" style="width: 960px; height: 480px; border: 0 none; overflow-y: auto;" frameborder="0">&nbsp;</iframe>';
		$items = array();
		foreach ( $forms as $form ) {
			$items[] = array(
				'label' => $form->name,
				'value' => $form->id,
			);
		}
		?>
<table class="form-table" id="embed_generator_table">
	<tbody>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_form_id', __( 'Select Form', 'ipt_fsqm' ) ); ?>
			</th>
			<td>
				<div class="ipt_uif_float_right" style="height: 68px; width: 200px;">
					<?php $this->ui->ajax_loader( true, 'ipt_fsqm_embed_generator_al', array(), true ); ?>
				</div>
				<?php $this->ui->select( 'standalone_form_id', $items, '' ); ?>
			</td>
			<td>
				<?php $this->ui->help_head( __( 'Embed Code', 'ipt_fsqm' ) ); ?>
				<p><?php _e( 'Embed codes are useful for embedding your forms on some external sites. Think of it as a YouTube share/embed code.', 'ipt_fsqm' ); ?></p>
				<p><?php _e( 'To use it simply select a form and select width and height. The system will generate the code automatically. Press <kbd>Ctrl</kbd> + <kbd>c</kbd> to copy. Paste it where you want.' ) ?></p>
				<p><?php _e( 'You can also use the URL to link to the standalone page.', 'ipt_fsqm' ); ?></p>
				<?php $this->ui->help_tail(); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_width', __( 'Width', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->slider( 'standalone_width', '960', '320', '2560', '20' ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_height', __( 'Height', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->slider( 'standalone_height', '480', '320', '2560', '20' ); ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_permalink', __( 'Permalink', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->text( 'standalone_permalink', $default_permalink['url'], __( 'Adjust settings to update this', 'ipt_fsqm' ), 'large', 'normal', 'code' ) ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_shortlink', __( 'Short Link', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->text( 'standalone_shortlink', $default_permalink['shortlink'], __( 'Adjust settings to update this', 'ipt_fsqm' ), 'large', 'normal', 'code' ) ?>
			</td>
		</tr>
		<tr>
			<th>
				<?php $this->ui->generate_label( 'standalone_code', __( 'Embed Code', 'ipt_fsqm' ) ); ?>
			</th>
			<td colspan="2">
				<?php $this->ui->textarea( 'standalone_code', $default_code, __( 'Adjust settings to update this', 'ipt_fsqm' ), 'widefat', 'normal', 'code' ); ?>
			</td>
		</tr>
	</tbody>
</table>
<div class="clear"></div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#standalone_code, #standalone_permalink, #standalone_shortlink').on('focus', function() {
			var $this = $(this);
			$this.select();

			$this.on('mouseup', function() {
				$this.off('mouseup');
				return false;
			});
		});
		$('#standalone_form_id').on('change keyup', function() {
			generate_embed();
		});

		$('#embed_generator_table').on('slidestop', function() {
			generate_embed();
		});

		var generate_embed = function() {
			var form_id = $('#standalone_form_id').val(),
			width = $('#standalone_width').val(),
			height = $('#standalone_height').val(),
			permalink = $('#standalone_permalink'),
			shortlink = $('#standalone_shortlink'),
			code = $('#standalone_code'),
			ajax_loader = $('#ipt_fsqm_embed_generator_al'),
			self = $(this);

			// Get the query parameters
			var data = {
				action : 'ipt_fsqm_standalone_embed_generate',
				form_id : form_id
			};

			ajax_loader.fadeIn('fast');

			// Query it
			$.get(ajaxurl, data, function(response) {
				if ( response == false || response === null ) {
					alert('Invalid Form Selected');
					return;
				}

				var embed_code = '<iframe src="' + response.url + '" width="' + width + '" height="' + height + '" style="width: ' + width + 'px; height: ' + height + 'px; border: 0 none; overflow-y: auto;" frameborder="0">&nbsp;</iframe>';
				code.text(embed_code);
				permalink.val(response.url);
				shortlink.val(response.shortlink);
				code.trigger('focus');
			}, 'json').fail(function() {
				alert('AJAX Error');
			}).always(function() {
				ajax_loader.fadeOut('fast');
			});
		}
	});
</script>
		<?php
	}

	public function meta_thank_you() {
		global $ipt_fsqm_info;
?>
<p>
	<?php _e( 'Thank you for Purchasing eForm Plugin.', 'ipt_fsqm' ); ?>
</p>
<ul class="ipt_uif_ul_menu">
	<li><a href="http://wpquark.com/kb/fsqm/fsqm-video-tutorials/"><i class="ipt-icomoon-play"></i> <?php _e( 'Getting Started', 'ipt_fsqm' ) ?></a></li>
	<li><a href="http://wpquark.com/kb/fsqm/"><i class="ipt-icomoon-file3"></i> <?php _e( 'Documentation', 'ipt_fsqm' ); ?></a></li>
	<li><a href="http://wpquark.com/kb/support/forum/wordpress-plugins/wp-feedback-survey-quiz-manager-pro/"><i class="ipt-icomoon-support"></i> <?php _e( 'Get Support', 'ipt_fsqm' ); ?></a></li>
</ul>
<?php $this->ui->help_head( __( 'Plugin Version', 'ipt_fsqm' ), true ); ?>
	<?php _e( 'If the Script version and DB version do not match, then deactivate the plugin and reactivate again. This should solve the problem. If the problem persists then contact the developer.', 'ipt_fsqm' ); ?>
<?php $this->ui->help_tail(); ?>
<p>
	<?php printf( __( '<strong>Plugin Version:</strong> <em>%s(Script)/%s(DB)</em>', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, $ipt_fsqm_info['version'] ); ?> | <?php _e( 'Icons Used from: ', 'ipt_fsqm' ); ?> <a href="http://icomoon.io/" title="IcoMoon" target="_blank">IcoMoon</a>
</p>
<div class="clear"></div>
		<?php
	}

	public function meta_stat() {
		global $wpdb, $ipt_fsqm_info;
		$today = current_time( 'timestamp' );
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id ASC", ARRAY_A );
		$info = array();
		$valid_forms = array();
		for ( $i = 30; $i >= 0; $i-- ) {
			$thedate = date( 'Y-m-d', mktime( 0, 0, 0, date( 'm', $today ), date( 'd', $today ) - $i, date( 'Y', $today ) ) );
			$start_date = $thedate . ' 00:00:00';
			$end_date = $thedate . ' 23:59:59';
			//var_dump($thedate, $start_date, $end_date);
			$info[$thedate] = array();
			$total = 0;

			$counts = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(id) count, form_id FROM {$ipt_fsqm_info['data_table']} WHERE date <= %s AND date >= %s GROUP BY form_id HAVING count > 0", $end_date, $start_date ), ARRAY_A );

			//var_dump($counts);
			foreach ( (array) $counts as $count ) {
				$info[$thedate][$count['form_id']] = (int) $count['count'];
				$total += $count['count'];
				$valid_forms[] = $count['form_id'];
			}

			//ksort( $info[$thedate] );
			$info[$thedate]['total'] = $total;
		}
		if ( empty( $valid_forms ) ) {
			echo '<div style="height: 300px;">';
			$this->ui->msg_error( __( 'No submissions for past 30 days. Please be patient.', 'ipt_fsqm' ) );
			echo '</div>';
			return;
		}
		$valid_forms = array_unique( $valid_forms );

		sort( $valid_forms );

		$json = array();
		$json[0] = array();
		$json[0][0] = __( 'Date', 'ipt_fsqm' );
		foreach ( $forms as $form ) {
			if ( !in_array( $form['id'], $valid_forms ) ) {
				continue;
			}
			$json[0][] = $form['name'];
		}
		$json[0][] = __( 'Total', 'ipt_fsqm' );
		$i = 1;
		foreach ( $info as $date => $count_data ) {
			$json[$i][0] = $date;
			foreach ( $valid_forms as $form ) {
				$json[$i][] = isset( $count_data[$form] ) ? $count_data[$form] : 0;
			}
			$json[$i][] = $count_data['total'];
			$i++;
		}

		//var_dump($json);
?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_ten_stat', array(), true ); ?>
<script type="text/javascript">

function drawLatestTen() {
	var data = google.visualization.arrayToDataTable(<?php echo json_encode( $json ); ?>);

	var ac = new google.visualization.ComboChart(document.getElementById('ipt_fsqm_ten_stat'));
	ac.draw(data, {
		title : '<?php _e( 'Last 30 days form submission statistics', 'ipt_fsqm' ); ?>',
		height : 300,
		vAxis : {title : '<?php _e( 'Submission Hits', 'ipt_fsqm' ) ?>'},
		hAxis : {title : '<?php _e( 'Date', 'ipt_fsqm' ); ?>'},
		seriesType : 'bars',
		series : {<?php echo count( $json[0] ) - 2; ?> : {type : 'line'}},
		legend : {position : 'top'},
		tooltip : {isHTML : true}
	});
}

</script>
		<?php
	}

	public function meta_overall() {
		global $wpdb, $ipt_fsqm_info;
		$query = "SELECT f.name name, COUNT(d.id) subs FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id GROUP BY f.id HAVING subs > 0";
		$json = array();
		$json[] = array( __( 'Form', 'ipt_fsqm' ), __( 'Submissions', 'ipt_fsqm' ) );
		$db_data = $wpdb->get_results( $query );

		if ( !empty( $db_data ) ) {
			foreach ( $db_data as $db ) {
				if ( $db->subs == 0 ) {
					continue;
				}
				$json[] = array( $db->name, (int) $db->subs );
			}
		} else {
			echo '<div style="height: 300px;">';
			$this->ui->msg_error( __( 'No submissions yet. Please be patient.', 'ipt_fsqm' ) );
			echo '</div>';
			return;
		}
?>
<?php $this->ui->ajax_loader( false, 'ipt_fsqm_pie_stat', array(), true ); ?>
<script type="text/javascript">

function drawOverallPie() {
	var data = google.visualization.arrayToDataTable(<?php echo json_encode( $json ); ?>);

	var ac = new google.visualization.PieChart(document.getElementById('ipt_fsqm_pie_stat'));
	ac.draw(data, {
		title : '<?php _e( 'Overall form submission statistics', 'ipt_fsqm' ); ?>',
		height : 300,
		is3D : true,
		legend : {position : 'right'},
		tooltip : {isHTML : true}
	});
}

</script>
		<?php
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function meta_ten() {
		global $wpdb, $ipt_fsqm_info;
		$rows = $wpdb->get_results( "SELECT d.id id, d.f_name f_name, d.l_name l_name, d.email email, d.phone phone, d.ip ip, d.date date, d.star star, d.comment comment, f.name name, f.id form_id FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id ORDER BY d.date DESC LIMIT 0,10", ARRAY_A );
?>
<table class="widefat">
	<thead>
		<tr>
			<th scope="col">
				<img src="<?php echo plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ); ?>" />
			</th>
			<th scope="col">
				<?php _e( 'Name', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Email', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Phone', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Date', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'IP Address', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Form', 'ipt_fsqm' ); ?>
			</th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col">
				<img src="<?php echo plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ); ?>" />
			</th>
			<th scope="col">
				<?php _e( 'Name', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Email', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Phone', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Date', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'IP Address', 'ipt_fsqm' ); ?>
			</th>
			<th scope="col">
				<?php _e( 'Form', 'ipt_fsqm' ); ?>
			</th>
		</tr>
	</tfoot>
	<tbody>
		<?php if ( empty( $rows ) ) : ?>
		<tr>
			<td colspan="7"><?php _e( 'No submissions yet', 'ipt_fsqm' ); ?></td>
		</tr>
		<?php else : ?>
		<?php foreach ( $rows as $item ) : ?>
		<tr>
			<th scope="row"><img src="<?php echo plugins_url( $item['star'] == 1 ? '/static/admin/images/star_on.png' : '/static/admin/images/star_off.png', IPT_FSQM_Loader::$abs_file ) ?>" /></th>
			<td>
				<?php printf( '<strong><a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['id'] . '&width=640&height=500">%s</a></strong>', sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'], $item['name'] ), $item['f_name'] . ' ' . $item['l_name'] ); ?>
			</td>
			<td>
				<?php if ( trim( $item['email'] ) !== '' ) : ?>
				<?php echo '<a href="mailto:' . $item['email'] . '">' . $item['email'] . '</a>'; ?>
				<?php else : ?>
				<?php _e( 'anonymous', 'ipt_fsqm' ); ?>
				<?php endif; ?>
			</td>
			<td>
				<?php echo $item['phone']; ?>
			</td>
			<td>
				<?php echo date_i18n( get_option( 'date_format' ) . __( ' \a\t ', 'ipt_fsqm' ) . get_option( 'time_format' ), strtotime( $item['date'] ) ); ?>
			</td>
			<td>
				<?php echo $item['ip']; ?>
			</td>
			<td>
			<?php if ( current_user_can( 'manage_feedback' ) ) : ?>
				<?php echo '<a href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['form_id'] . '">' . $item['name'] . '</a>'; ?>
			<?php else : ?>
				<?php echo $item['name']; ?>
			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
		<?php
	}

	public function on_load_page() {
		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Thank you for choosing eForm Plugin. This screen provides some basic information of the plugin and Latest Submission Statistics. The design is integrated from WordPress\' own framework. So you should feel like home!', 'ipt_fsqm' ) . '<p>' .
			'<p>' . __( 'The concept and working of the Plugin is very simple.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You setup a form from the <a href="admin.php?page=ipt_fsqm_new_form">New Form</a>.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You use the Shortcodes (check the Shortcodes tab on this help screen) for displaying on your Site/Blog. Simply create a page and you will see a new button added to your editor from where you can put the shortcodes automatically. If you want to use the codes manually, then check the Shortcode section of this help.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'Finally use the <a href="admin.php?page=ipt_fsqm_report">Report & Analysis</a> Or <a href="admin.php?page=ipt_fsqm_view_all_submissions">View all Submissions</a> pages to analyze the submissions.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Sounds easy enough? Then get started by going to the <a href="admin.php?page=ipt_fsqm_new_form">New Form</a> now. You can always click on the <strong>HELP</strong> button above the screen to know more.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'If you have any suggestions or have encountered any bug, please feel free to use the Linked support forum', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'shortcodes',
			'title' => __( 'Shortcodes', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This plugin comes with three shortcodes. One for displaying the FORM and other for displaying the Trends (The same Latest 100 Survey Reports you see on this screen)', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<code>[ipt_fsqm_form id="form_id"]</code> : Just use this inside a Post/Page and the form will start appearing.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_trends form_id="form_id"]</code> : Use this to show the Trends based on all available MCQs. Just like the <strong>Report & Analysis</strong>.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_trackback]</code> : A page from where your users can track their submission. If it is thre in the notification email, then the surveyee should receive a confirmation email with the link to the track page.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<code>[ipt_fsqm_utrackback]</code> : A central page from where your registered users can track all their submissions. It integrates with your wordpress users and if they are not logged in, it will simply show a login form.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'If the output of the shortcodes look weird, then probably you have copied them from the list above with the <code>&lt;code&gt;</code> HTML markup. Please delete them and manually write the shortcode.', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->add_help_tab( array(
			'id' => 'credits',
			'title' => __( 'Credits', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This is a Pro version of the Free <a href="http://wordpress.org/extend/plugins/wp-feedback-survey-manager/">WP Feedback & Survey Manager</a> Plugin.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'The plugin uses a few free and/or open source products, which are:', 'ipt_fsqm' ) .
			'<ul>' .
			'<li>' . __( '<strong><a href="http://www.google.com/webfonts/">Google WebFont</a></strong> : To make the form look better.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="http://jqueryui.com/">jQuery UI</a></strong> : Renders many elements along with the "Tab Like" appearance of the form.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="https://developers.google.com/chart/">Google Charts Tool</a></strong> : Renders the report charts on both backend as well as frontend.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong><a href="https://github.com/posabsolute/jQuery-Validation-Engine">jQuery Validation Engine</a></strong> : Wonderful form validation plugin from Position-absolute.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Icons</strong> : <a href="http://www.icomoon.io/" target="_blank">IcoMoon Icons</a> The wonderful and free collection of Font Icons.', 'ipt_fsqm' ) . '</li>' .
			'</ul>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

/**
 * View all Forms Class
 */
class IPT_FSQM_All_Forms extends IPT_FSQM_Admin_Base {
	public $table_view;
	public $form_data;
	public $form_element_admin;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_form_view_all_nonce';

		parent::__construct();

		$this->icon = 'insert-template';
		add_filter( 'set-screen-option', array( &$this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the forms', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the form', 'ipt_fsqm' ),
		);
		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully added the form', 'ipt_fsqm' ),
		);
		$this->post_result[8] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the forms. Please contact developer if problem persists', 'ipt_fsqm' ),
		);
		$this->post_result[9] = array(
			'type' => 'error',
			'msg' => __( 'Could not delete the forms. Please contact developer if problem persists', 'ipt_fsqm' ),
		);
		$this->post_result[10] = array(
			'type' => 'update',
			'msg' => __( 'Successfully updated the form', 'ipt_fsqm' ),
		);
		$this->post_result[11] = array(
			'type' => 'update',
			'msg' => __( 'Successfully copied the form', 'ipt_fsqm' ),
		);

		if ( isset( $_GET['form_id'] ) ) {
			$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin( (int) $_GET['form_id'] );
		} else {
			$this->form_element_admin = new IPT_FSQM_Form_Elements_Admin();
		}

		add_action( 'wp_ajax_' . $this->admin_post_action, array( $this->form_element_admin, 'ajax_save' ) );
		add_action( 'wp_ajax_ipt_fsqm_submission_download', array( $this, 'ajax_csv_download' ) );
	}

	public function admin_menu() {
		$page_title = __( 'View all Forms', 'ipt_fsqm' );
		if ( isset( $_GET['form_id'] ) ) {
			$page_title = __( 'Edit Form', 'ipt_fsqm' );
		}
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', $page_title, __( 'View all Forms', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_all_forms', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		if ( isset( $_GET['form_id'] ) ) {
			$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Update Form <a href="admin.php?page=ipt_fsqm_all_forms" class="add-new-h2">Go Back</a>', 'ipt_fsqm' ), true, 'none' );
			if ( $this->form_element_admin->form_id != $_GET['form_id'] ) {
				$this->ui->msg_error( __( 'Invalid form ID provided.', 'ipt_fsqm' ) );
			} else {
				$this->form_element_admin->show_form();
			}
			$this->index_foot( false );
		} else {
			$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> View all forms', 'ipt_fsqm' ) . '<a href="admin.php?page=ipt_fsqm_new_form" class="add-new-h2">' . __( 'Add New', 'ipt_fsqm' ) . '</a>', false );
			$this->table_view->prepare_items();
?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Forms', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Forms', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
			<?php
			$this->index_foot();
		}
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function save_post( $check_referer = true ) {
		parent::save_post();
		$this->form_element_admin->process_save();
		wp_redirect( add_query_arg( array( 'post_result' => '10' ), $_POST['_wp_http_referer'] ) );
		die();
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Form_Table();
		$action = $this->table_view->current_action();
		if ( $action == 'delete' ) {
			if ( isset( $_GET['id'] ) ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_form_delete_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				if ( IPT_FSQM_Form_Elements_Static::delete_forms( $_GET['id'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '6' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '9' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				}
			} else {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_form_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				if ( IPT_FSQM_Form_Elements_Static::delete_forms( $_GET['forms'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => '4' ), $_GET['_wp_http_referer'] ) );
				} else {
					wp_redirect( add_query_arg( array( 'post_result' => '8' ), $_GET['_wp_http_referer'] ) );
				}
			}
			die();
		} else if ( $action == 'copy' ) {
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_form_copy_' . $_GET['id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				IPT_FSQM_Form_Elements_Static::copy_form( $_GET['id'] );
				wp_redirect( add_query_arg( array( 'post_result' => '11' ), 'admin.php?page=ipt_fsqm_all_forms' ) );
				die();
			}

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Forms per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'feedback_forms_per_page',
		);
		add_screen_option( $option, $args );

		parent::on_load_page();

		if ( isset( $_GET['form_id'] ) ) {
			$this->form_element_admin->add_help();
		} else {
			get_current_screen()->add_help_tab( array(
					'id'  => 'overview',
					'title'  => __( 'Overview', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'This screen provides access to all of your forms. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
					'<p>' . __( 'By default, this screen will show all the forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'screen-content',
					'title'  => __( 'Screen Content', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
					'<ul>' .
					'<li>' . __( 'You can sort forms based on total submissions or last updated.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( 'You can hide/display columns based on your needs and decide how many forms to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( 'You can search a particular form by using the Search Form. You can type in just the name.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'action-links',
					'title'  => __( 'Available Actions', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>View Submissions</strong> will take you to a page from where you can see all the submissions under that form.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Edit</strong> lets you recustomize the form.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Delete</strong> removes your from this list as well as from the database along with all the submissions under it. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
					'<li>' . __( '<strong>Copy</strong> creates a copy of the form.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
			get_current_screen()->add_help_tab( array(
					'id'  => 'bulk-actions',
					'title'  => __( 'Bulk Actions', 'ipt_fsqm' ),
					'content' =>
					'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
					'<ul>' .
					'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked forms from the database along with all the submissions under it.', 'ipt_fsqm' ) . '</li>' .
					'</ul>'
				) );
		}

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}

	public function ajax_csv_download() {
		global $wpdb, $ipt_fsqm_info;

		// Cap check
		if ( ! current_user_can( 'manage_feedback' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Get form ID and nonce
		$form_id = (int) @$_REQUEST['form_id'];
		$nonce = @$_REQUEST['_wpnonce'];

		// Nonce check
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_submission_download_' . $form_id ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		// Require the file
		// require_once IPT_FSQM_Loader::$abs_path . '/classes/class-ipt-eform-form-elements-values.php';

		// Get all data ids
		$data_ids = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$ipt_fsqm_info['data_table']} WHERE form_id = %d", $form_id ) ); // WPCS: unprepared SQL ok.

		if ( empty( $data_ids ) ) {
			wp_die( __( 'No submissions yet!', 'ipt_fsqm' ), __( 'Export Error' ) );
		}

		$big_data = array(); //Psst: Do not blame me for the name

		$form = new IPT_FSQM_Form_Elements_Base( $form_id );

		// Loop through and add the question titles
		$headings = array();

		// Info
		$headings[] = __( 'Submission ID', 'ipt_fsqm' );
		$headings[] = __( 'User ID', 'ipt_fsqm' );
		$headings[] = __( 'Submission Date and Time', 'ipt_fsqm' );
		$headings[] = __( 'First Name', 'ipt_fsqm' );
		$headings[] = __( 'Last Name', 'ipt_fsqm' );
		$headings[] = __( 'Email', 'ipt_fsqm' );

		// Loop through the MCQ
		foreach ( $form->mcq as $m_key => $mcq ) {
			$headings[] = $mcq['title'];
		}

		// Loop through the Feedback
		foreach ( $form->freetype as $f_key => $freetype ) {
			$headings[] = $freetype['title'];
		}

		// Loop through the Pinfo
		foreach ( $form->pinfo as $p_key => $pinfo ) {
			if ( in_array( $pinfo['type'], array( 'f_name', 'l_name', 'email' ) ) ) {
				continue;
			}
			$headings[] = $pinfo['title'];
		}

		// Others
		$headings[] = __( 'IP Address', 'ipt_fsqm' );
		if ( '' != $form->settings['general']['comment_title'] ) {
			$headings[] = $form->settings['general']['comment_title'];
		}

		// Score
		$headings[] = __( 'Score', 'ipt_fsqm' );
		$headings[] = __( 'Max Score', 'ipt_fsqm' );

		// Referer
		$headings[] = __( 'Referer', 'ipt_fsqm' );
		// URL Track
		$headings[] = __( 'URL Track', 'ipt_fsqm' );

		// Time
		$headings[] = __( 'Time', 'ipt_fsqm' );

		// Link
		$headings[] = __( 'Link', 'ipt_fsqm' );

		$big_data[] = $headings;
		unset( $headings );

		// Now loop through all IDs and create the array with data
		foreach ( $data_ids as $data_id ) {
			$data_row = array();
			$data = new IPT_eForm_Form_Elements_Values( $data_id );
			if ( is_null( $data->data_id ) ) {
				continue;
			}

			// Info
			$data_row[] = $data->data_id;
			$data_row[] = $data->data->user_id;
			$data_row[] = $data->data->date;
			$data_row[] = $data->data->f_name;
			$data_row[] = $data->data->l_name;
			$data_row[] = $data->data->email;

			// Loop through the MCQ
			foreach ( $form->mcq as $m_key => $mcq ) {
				$data_row[] = $data->get_value( 'mcq', $m_key, 'string', 'label' );
			}

			// Loop through the Feedback
			foreach ( $form->freetype as $f_key => $freetype ) {
				$data_row[] = $data->get_value( 'freetype', $f_key, 'string', 'label' );
			}

			// Loop through the Pinfo
			foreach ( $form->pinfo as $p_key => $pinfo ) {
				if ( in_array( $pinfo['type'], array( 'f_name', 'l_name', 'email' ) ) ) {
					continue;
				}
				$data_row[] = $data->get_value( 'pinfo', $p_key, 'string', 'label' );
			}

			$data_row[] = $data->data->ip;
			if ( '' != $form->settings['general']['comment_title'] ) {
				$data_row[] = $data->data->comment;
			}

			// Score
			$data_row[] = $data->data->score;
			$data_row[] = $data->data->max_score;

			// Referer
			$data_row[] = $data->data->referer;
			// URL Track
			$data_row[] = $data->data->url_track;

			// Time
			$data_row[] = $data->data->time;

			// Link
			$data_row[] = admin_url( 'admin.php?page=ipt_fsqm_view_submission&id=' . $data->data_id );

			// Add
			$big_data[] = $data_row;
		}

		$csv = $this->array_to_csv( $big_data );

		// Start the download header
		if ( function_exists( 'mb_strlen' ) ) {
		    $size = mb_strlen( $csv, '8bit' );
		} else {
		    $size = strlen( $csv );
		}
		header( 'Content-Description: File Transfer' );
		header( 'Content-Type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=csv-export-' . $form_id . '.csv' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Expires: 0' );
		header( 'Cache-Control: must-revalidate' );
		header( 'Pragma: public' );
		header( 'Content-Length: ' . $size );

		echo $csv;

		die();
	}

	protected function array_to_csv( $array ) {
		if ( empty( $array ) ) {
			return '';
		}
		ob_start();
		$csv = fopen( 'php://output', 'w' );
		foreach ( $array as $row ) {
			fputcsv( $csv, $row );
		}
		fclose( $csv );
		return ob_get_clean();
	}
}

/**
 * New Form Class
 */
class IPT_FSQM_New_Form extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_forms_nonce';

		parent::__construct();

		$this->icon = 'insert-template';
		$this->is_metabox = false;

		add_action( 'wp_ajax_eform_wizard_preview', array( $this, 'ajax_preview' ) );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/
	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'New Form', 'ipt_fsqm' ), __( 'New Form', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_new_form', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> New Form', 'ipt_fsqm' ) );
		$templates = $this->scan_template_directory();
		$siteurl = parse_url( get_bloginfo( 'url' ) );
		$domain = $siteurl['host'];
		$senderemail = 'no-reply@' . $domain;
		$success_message = __( 'Thank you %NAME% for taking the quiz/survey/feedback.' . "\n" . 'We have received your answers. You can view it anytime from this link below:' . "\n" . '<a href="%TRACK_LINK%">%TRACK_LINK%</a>' . "\n" . 'We have also attached a copy of your submission.', 'ipt_fsqm' );

		$responsive_buttons = array(
			0 => array( '', '', 'small', 'secondary', 'normal', 'eform-nfw-res-large', 'button', array(), array(), '', 'desktop', 'before' ),
			1 => array( '', '', 'small', 'secondary', 'normal', 'eform-nfw-res-medium', 'button', array(), array(), '', 'laptop2', 'before' ),
			2 => array( '', '', 'small', 'secondary', 'normal', 'eform-nfw-res-small active', 'button', array(), array(), '', 'mobile', 'before' ),
		);
		?>
		<?php $this->ui->ajax_loader( false, 'ipt-eform-new-form-wizard-loader', array(), true, __( 'Loading', 'ipt_fsqm' ) ); ?>
<div id="ipt-eform-new-form-wizard" style="display: none;">
	<div id="ipt-eform-new-form-main-tab" class="ipt_uif_tabs">
		<ul>
			<li><a href="#ipt-eform-new-form-blank"><i class="ipt-icomoon-file"></i> <?php _e( 'Blank' ) ?></a></li>
			<?php foreach ( $templates as $form_cat_key => $form_cat_val ) : ?>
				<?php if ( empty( $form_cat_val['forms'] ) ) {
					continue;
				} ?>
				<li><a href="#ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>"><i class="<?php echo $this->get_form_cat_icon( $form_cat_key ); ?>"></i> <?php echo $form_cat_val['label'] ?></a></li>
			<?php endforeach; ?>
		</ul>
		<div id="ipt-eform-new-form-blank"></div>
		<?php foreach ( $templates as $form_cat_key => $form_cat_val ) : ?>
			<?php if ( empty( $form_cat_val['forms'] ) ) {
				continue;
			} ?>
			<div id="ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>" class="has-inner-tab">
				<div class="ipt_uif_tabs vertical eform-form-template">
					<ul>
						<?php foreach ( $form_cat_val['forms'] as $form_key => $form_name ) : ?>
							<li class="eform-form-template-li" data-form-cat-key="<?php echo esc_attr( $form_cat_key ); ?>" data-form-key="<?php echo esc_attr( $form_key ); ?>">
								<a href="#ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>-<?php echo esc_attr( str_replace( '.', '', $form_key ) ); ?>"><?php echo $form_name; ?></a>
							</li>
						<?php endforeach; ?>
					</ul>
					<?php foreach ( $form_cat_val['forms'] as $form_key => $form_name ) : ?>
						<div id="ipt-eform-new-form-<?php echo esc_attr( $form_cat_key ); ?>-<?php echo esc_attr( str_replace( '.', '', $form_key )  ); ?>" class="eform-new-form-wizard-previewer">
							<div class="eform-new-form-wizard-loader">
								<?php $this->ui->ajax_loader( false, '', array(), true, __( 'Loading Preview', 'ipt_fsqm' ) ); ?>
							</div>
							<?php $this->ui->buttons( $responsive_buttons, '', array( 'align-right', 'eform-new-form-wizard-responsive-btns' ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endforeach; ?>
		<div id="ipt-eform-new-form-values">
			<input type="hidden" name="eform[form_cat]" id="eform_form_cat" value="" />
			<input type="hidden" name="eform[form_key]" id="eform_form_key" value="" />
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[name]', __( 'Form Name', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'eform[name]', '', __( 'Required', 'ipt_fsqm' ), 'large', 'normal', array(), false, false, array( 'required' => 'required' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Enter the Name of the Form', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[admin_email]', __( 'Admin Notification Email', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'eform[admin_email]', wp_get_current_user()->user_email, __( 'Required', 'ipt_fsqm' ), 'fit', 'normal', array(), false, false, array( 'required' => 'required', 'type' => 'email' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Your email address where new form submission notifications would go.', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[user_email]', __( 'User Notification Sender\'s Email', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->text( 'eform[user_email]', $senderemail, __( 'Required', 'ipt_fsqm' ), 'fit', 'normal', array(), false, false, array( 'required' => 'required', 'type' => 'email' ) ); ?></td>
						<td><?php $this->ui->help( __( 'Enter the email which the user will see as the Sender\'s Email on the email he/she receives. It is recommended to use an email from the same domain. Otherwise it might end up into spams. Entering an empty email will stop the user notification service. So leave it empty to disable sending emails to users', 'ipt_fsqm' ) ); ?></td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'eform[success_message]', __( 'Form Submission Success Message', 'ipt_fsqm' ) ); ?></th>
						<td><?php $this->ui->textarea( 'eform[success_message]', $success_message, __( 'Required', 'ipt_fsqm' ), 'large', 'normal', array(), false, false, 5, array( 'required' => 'required' ) ); ?></td>
						<td><?php $this->ui->help( __( 'What to show when the form is submitted. ', 'ipt_fsqm' ) . sprintf( __( 'An updated list can always be found <a href="%1$s" target="_blank">here</a>.', 'ipt_fsqm' ), 'https://wpquark.com/kb/fsqm/form-submission-related/available-format-strings-custom-notifications/' ) ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php $this->ui->button( __( 'Create Form' ), 'eform[submit]', 'large', 'secondary', 'normal', array(), 'submit', true, array(), array(), '', 'plus' ); ?>
		</div>
	</div>
</div>
		<?php
		$this->index_foot( false );
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 */
	public function save_post( $check_referer = true ) {
		global $wpdb, $ipt_fsqm_info;

		parent::save_post( $check_referer );

		// Get variables
		$siteurl = parse_url( get_bloginfo( 'url' ) );
		$domain = $siteurl['host'];
		$senderemail = 'no-reply@' . $domain;
		$success_message = __( 'Thank you %NAME% for taking the quiz/survey/feedback.' . "\n" . 'We have received your answers. You can view it anytime from this link below:' . "\n" . '<a href="%TRACK_LINK%">%TRACK_LINK%</a>' . "\n" . 'We have also attached a copy of your submission.', 'ipt_fsqm' );
		$eform = @$_REQUEST['eform'];
		// Pass through wp_unslash
		$eform = wp_unslash( $eform );

		$eform = wp_parse_args( $eform, array(
			'form_cat' => '',
			'form_key' => '',
			'name' => __( 'Untitled', 'ipt_fsqm' ),
			'admin_email' => wp_get_current_user()->user_email,
			'user_email' => $senderemail,
			'success_message' => $success_message,
		) );

		// Create blank form
		$form = new IPT_FSQM_Form_Elements_Front();
		// Check if a template is used
		$filename = IPT_FSQM_Loader::$abs_path . '/templates/' . $eform['form_cat'] . '/' . $eform['form_key'];
		if ( is_file( $filename ) && file_exists( $filename ) ) {
			// Create the template form
			$formdata = maybe_unserialize( base64_decode( file_get_contents( $filename ) ) );
			if ( ! $formdata ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}

			$form->settings = maybe_unserialize( $formdata['settings'] );
			$form->mcq = maybe_unserialize( $formdata['mcq'] );
			$form->pinfo = maybe_unserialize( $formdata['pinfo'] );
			$form->freetype = maybe_unserialize( $formdata['freetype'] );
			$form->design = maybe_unserialize( $formdata['design'] );
			$form->layout = maybe_unserialize( $formdata['layout'] );
			$form->name = $formdata['name'];
			$form->type = $formdata['type'];
			$form->form_id = -9999;
			$form->compat_layout();
		}
		// Change admin provided settings
		$form->name = $eform['name'];
		$form->settings['user']['notification_email'] = $eform['user_email'];
		$form->settings['admin']['email'] = $eform['admin_email'];
		$form->settings['submission']['success_message'] = $eform['success_message'];

		// Insert
		// All set, now import it
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name'     => $form->name,
			'settings' => maybe_serialize( $form->settings ),
			'layout'   => maybe_serialize( $form->layout ),
			'design'   => maybe_serialize( $form->design ),
			'mcq'      => maybe_serialize( $form->mcq ),
			'freetype' => maybe_serialize( $form->freetype ),
			'pinfo'    => maybe_serialize( $form->pinfo ),
			'type'     => $form->type,
			'category' => 0,
		), '%s' );
		// Get form ID
		$new_form_id = $wpdb->insert_id;
		// Redirect
		wp_redirect( add_query_arg( array( 'form_id' => $new_form_id ), 'admin.php?page=ipt_fsqm_all_forms&action=edit' ) );
		die();
	}

	public function on_load_page() {
		parent::on_load_page();

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	/*==========================================================================
	 * Form Wizard Helpers
	 *========================================================================*/

	private function scan_template_directory() {
		$templates = array();
		$path = IPT_FSQM_Loader::$abs_path . '/templates';
		$scan = @scandir( $path );
		if ( ! $scan ) {
			return $templates;
		}
		foreach ( $scan as $dir ) {
			if ( '.' != $dir && '..' != $dir && @is_dir( $path . '/' . $dir ) ) {
				$category_name = str_replace( '-', ' ', $dir );
				$templates[ $dir ] = array(
					'label' => trim( $category_name ),
					'forms' => array(),
				);
				$forms = @scandir( $path . '/' . $dir );
				if ( ! $forms ) {
					continue;
				}
				foreach ( $forms as $form ) {
					if ( '.' != $form && '..' != $form && @is_file( $path . '/' . $dir . '/' . $form ) ) {
						$formname = str_replace( array( 'eForm', '-', '.txt' ), array( ' ', ' ', '' ), $form );
						$templates[ $dir ]['forms'][ $form ] = trim( $formname );
					}
				}
			}
		}
		return $templates;
	}

	private function get_form_cat_icon( $cat_key ) {
		$icon = 'ipt-icomoon-file-text-o';
		switch ( $cat_key ) {
			case 'Feedback-Form':
				$icon = 'ipt-icomoon-envelope';
				break;
			case 'Integrations-Form':
				$icon = 'ipt-icomoon-plus-square';
				break;
			case 'Mathematical-Form':
				$icon = 'ipt-icomoon-calculator';
				break;
			case 'Order-Form':
				$icon = 'ipt-icomoon-shopping-cart';
				break;
			case 'Quiz-Form':
				$icon = 'ipt-icomoon-certificate';
				break;
			case 'Survey-Form':
				$icon = 'ipt-icomoon-signup';
				break;
		}
		return apply_filters( 'ipt_eform_new_form_wizard_tab_icons', $icon, $cat_key );
	}

	public function ajax_preview() {
		if ( ! current_user_can( 'manage_feedback' ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		$form_cat = @$_REQUEST['formcat'];
		$form_key = @$_REQUEST['formkey'];
		$filename = IPT_FSQM_Loader::$abs_path . '/templates/' . $form_cat . '/' . $form_key;
		if ( ! is_file( $filename ) || ! file_exists( $filename ) ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$formdata = maybe_unserialize( base64_decode( file_get_contents( $filename ) ) );
		if ( ! $formdata ) {
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		}
		$form = new IPT_FSQM_Form_Elements_Front();
		$form->settings = maybe_unserialize( $formdata['settings'] );
		$form->mcq = maybe_unserialize( $formdata['mcq'] );
		$form->pinfo = maybe_unserialize( $formdata['pinfo'] );
		$form->freetype = maybe_unserialize( $formdata['freetype'] );
		$form->design = maybe_unserialize( $formdata['design'] );
		$form->layout = maybe_unserialize( $formdata['layout'] );
		$form->name = $formdata['name'];
		$form->type = $formdata['type'];
		$form->form_id = -9999;
		$form->compat_layout();
		?>
<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
	<title><?php echo $form->name; ?></title>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
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
	html {
		overflow-y: auto;
	}
	ul {
		list-style-type: disc;
		list-style-position: inside;
	}
	ol {
		list-style-type: decimal;
		list-style-position: inside;
	}
	body {
		background-color: #fff;
		background-image: none;
		font-family: 'Roboto', Tahoma, Geneva, sans-serif;
		font-weight: normal;
		font-style: normal;
		font-size: 12px;
		color: #333;
		min-width: 320px;
	}
	#fsqm_form {
		max-width: 1200px;
		padding: 20px;
		margin: 0 auto;
	}
	</style>
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'ipt_uif_common' ); ?>>
	<div id="fsqm_form">
		<?php $form->show_form(); ?>
	</div>
	<?php wp_footer(); ?>
	<!-- Fix for #wpadminbar -->
	<style type="text/css">
		html {
			margin-top: 0 !important;
		}
	</style>
</body>
</html>
		<?php
		die();
	}

}

/**
 * Form Categories
 */
class IPT_FSQM_Form_Category extends IPT_FSQM_Admin_Base {
	public $table_view;
	public $page_action;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_form_category_nonce';

		parent::__construct();

		$this->icon = 'folder-open';

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully created the category.', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully updated the category.', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the category.', 'ipt_fsqm' ),
		);
		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted selected categories.', 'ipt_fsqm' ),
		);

		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/
	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Form Categories', 'ipt_fsqm' ), __( 'Form Category', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_form_category', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		global $ipt_fsqm_info;
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Form Category <a href="admin.php?page=ipt_fsqm_form_category&paction=new_cat" class="add-new-h2">Add New</a>', 'ipt_fsqm' ), false );
		switch( $this->page_action ) {
			case 'new_cat' :
				$this->category_form();
				break;
			case 'edit_cat' :
				$this->category_form( $_GET['cat_id'] );
				break;
			default :
				$this->show_table();
		}
		$this->index_foot();
	}

	public function save_post( $check_referer = true ) {
		parent::save_post();
		$action = $this->post['db_action'];
		if ( $action == 'insert' ) {
			IPT_FSQM_Form_Elements_Static::create_category( $this->post['fsqm_cat']['name'], $this->post['fsqm_cat']['description'] );
			wp_redirect( add_query_arg( array( 'post_result' => 4 ), $_POST['_wp_http_referer'] ) );
		} else {
			IPT_FSQM_Form_Elements_Static::update_category( $this->post['fsqm_cat']['id'], $this->post['fsqm_cat']['name'], $this->post['fsqm_cat']['description'] );
			wp_redirect( add_query_arg( array( 'post_result' => 5 ), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ) );
		}
		die();
	}

	public function on_load_page() {
		$this->table_view = new IPT_FSQM_Category_Table();
		// fsqm_category_per_page
		$option = 'per_page';
		$args = array(
			'label' => __( 'Categories Per Page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'fsqm_category_per_page',
		);
		add_screen_option( $option, $args );

		$this->page_action = isset( $_GET['paction'] ) ? $_GET['paction'] : 'table_view';
		$action = $this->table_view->current_action();

		if ( $action == 'delete' ) {
			if ( isset( $_GET['cat_id'] ) ) {
				if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_category_delete_' . $_GET['cat_id'] ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				IPT_FSQM_Form_Elements_Static::delete_categories( $_GET['cat_id'] );
				wp_redirect( add_query_arg( array( 'post_result' => 6 ), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ) );
			} else {
				if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_category_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				IPT_FSQM_Form_Elements_Static::delete_categories( $_GET['cat_ids'] );
				wp_redirect( add_query_arg( array( 'post_result' => 7 ), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ) );
			}

			die();
		}

		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This screen displays all your form categories. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'By default, this screen will show all the categories. You can also create a new category by clicking on the <strong>Add New</strong> Button. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'screen-content',
			'title'  => __( 'Screen Content', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can sort categories based on name.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many categories to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can search a particular category by using the Search Form. You can type in just the name.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'new-category-form',
			'title'  => __( 'Add New Category', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Click on the <strong>Add New</strong> button to get started.' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Category Name</strong>: A short name of the category. This will be shown on admin lists and user portals.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Category Description</strong>: A description of the category. HTML allowed.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Once done, click on the <strong>Create Category</strong> button and it will be added to the list.', 'ipt_fsqm' )
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'action-links',
			'title'  => __( 'Available Actions', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>View Forms</strong> will take you to the all forms page, filtered by this category only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>View Submissions</strong> will take you to the all submissions page, filtered by this category only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Edit</strong> lets you recustomize the category.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Delete</strong> removes your category this list as well as from the database. Any form associated with it will be unassigned. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'bulk-actions',
			'title'  => __( 'Bulk Actions', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked categories from the database. Any form associated with these will be unassigned.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);

		parent::on_load_page();
	}

	protected function show_table() {
		$this->table_view->prepare_items();
		?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Form Categories', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Categories', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
		<?php
	}

	protected function category_form( $id = '' ) {
		$buttons = array(
			array( __( 'Create Category', 'ipt_fsqm' ), '', 'medium', 'primary', 'normal', array(), 'submit' ),
			array( __( 'Reset', 'ipt_fsqm' ), '', 'medium', 'secondary', 'normal', array(), 'reset' ),
			array( __( 'View All', 'ipt_fsqm' ), '', 'medium', 'secondary', 'normal', array(), 'anchor', array(), array(), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) ),
		);
		if ( $id == '' ) {
			$category = new stdClass();
			$category->name = '';
			$category->description = '';
			$category->id = '';
		} else {
			$category = IPT_FSQM_Form_Elements_Static::get_category( $id );
			if ( $category == null ) {
				$this->ui->msg_error( __( 'Invalid Category ID', 'ipt_fsqm' ) );
				$this->ui->button( __( 'View All', 'ipt_fsqm' ), '', 'medium', 'primary', 'normal', array(), 'anchor', true, array(), array(), admin_url( 'admin.php?page=ipt_fsqm_form_category' ) );
				return;
			}
			$buttons[0][0] = __( 'Update Category', 'ipt_fsqm' );
		}
		?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3>
			<span class="ipt-icomoon-tag2"></span>
			<?php if ( $category->id == '' ) : ?>
			<?php _e( 'Create a new Category', 'ipt_fsqm' ); ?>
			<?php else : ?>
			<?php _e( 'Update Category', 'ipt_fsqm' ); ?>
			<?php endif; ?>
		</h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form method="post" action="admin-post.php" id="<?php echo $this->pagehook; ?>_form_primary">
			<input type="hidden" name="action" value="<?php echo $this->admin_post_action; ?>" />
			<?php wp_nonce_field( $this->action_nonce, $this->action_nonce ); ?>
			<input type="hidden" name="db_action" value="<?php echo ( $category->id == '' ? 'insert' : 'update' ); ?>" />
			<input type="hidden" name="fsqm_cat[id]" value="<?php echo $category->id; ?>" />
			<table class="form-table">
				<tbody>
					<tr>
						<th><?php $this->ui->generate_label( 'fsqm_cat[name]', __( 'Category Name', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->text( 'fsqm_cat[name]', $category->name, __( 'Shortname of the category', 'ipt_fsqm' ) ); ?>
						</td>
					</tr>
					<tr>
						<th><?php $this->ui->generate_label( 'fsqm_cat[description]', __( 'Category Description', 'ipt_fsqm' ) ); ?></th>
						<td>
							<?php $this->ui->textarea( 'fsqm_cat[description]', $category->description, '' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="clear"></div>
			<?php $this->ui->buttons( $buttons ); ?>
		</form>
	</div>
</div>
		<?php
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 * Report & Analysis Class
 */
class IPT_FSQM_Report extends IPT_FSQM_Admin_Base {
	public $form_elements_utilities;
	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_survey_report_nonce';
		parent::__construct();
		$this->icon = 'stats';
		$this->form_elements_utilities = new IPT_FSQM_Form_Elements_Utilities();

		//Add the ajax for Survey
		add_action( 'wp_ajax_ipt_fsqm_survey_report', array( $this->form_elements_utilities, 'report_ajax' ) );
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Generate Report for Forms', 'ipt_fsqm' ), __( 'Report &amp; Analysis', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_report', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Report &amp; Analysis', 'ipt_fsqm' ), false );
		$this->form_elements_utilities->report_index();
		$this->index_foot( false );
	}

	public function on_load_page() {
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'overview',
				'title' => __( 'Overview', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'This page provides a nice way to view all the survey reports from beginning to end. As this can be a bit database expensive, so reports are pulled 15/30/50 at a time, depending on the server load. You will need JavaScript to view this page.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'This part of eForm works like a wizard which takes you through the steps necessary to generate just the part of the report you wish to see.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'Please check out the other help items for more information.', 'ipt_fsqm' ) . '</p>'

			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'first_step',
				'title' => __( 'Selecting Form', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'In this page you have the following options to get started.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Select Form:</strong> Select the form for which you want to generate the report.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Report Type:</strong> Please select the type of the report.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Server Load:</strong> Select the load on your server. For shared hosts, Medium Load is recommended.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Custom Date Range:</strong> Tick and select a range of date.', 'ipt_fsqm' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Once done, simply click on the <strong>Select Questions</strong> button.', 'ipt_fsqm' ) . '</p>'
			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'second_step',
				'title' => __( 'Selecting Questions', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'From this page, you will be able to select questions for which you want to generate the report.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Select the Multiple Choice Type Questions:</strong> This will list down all the MCQs in your form in proper order. Select the one you like.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Select the Feedback Questions:</strong> This will list down all the feedbacks in your form in proper order. Select the one you like.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'

			) );
		get_current_screen()->add_help_tab( array(
				'id' => 'third_step',
				'title' => __( 'Generate Report', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'Now all you have to do it wait until the progress bar reaches 100%. Once done, it will show you the reports of all the questions you have selected in a tabular fashion with charts whenever applicable.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'If you want to take a printout then scroll to the bottom of the page and click on the big print button.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'If you wish to put something on this site, then simply use the <strong>Insert Trends</strong> from the eForm editor button.', 'ipt_fsqm' ) . '</p>'

			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

/**
 * View a Submission Class
 */
class IPT_FSQM_View_Submission extends IPT_FSQM_Admin_Base {

	public function __construct() {
		$this->capability = 'view_feedback';
		$this->action_nonce = 'ipt_fsqm_view_nonce';
		parent::__construct();

		$this->icon = 'newspaper';
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'View a Submission', 'ipt_fsqm' ), __( 'View a Submission', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_view_submission', array( &$this, 'index' ) );
		parent::admin_menu();
	}
	public function index() {
		$ui_state = 'back';
		if ( isset( $_GET['id'] ) || isset( $_GET['id2'] ) ) {
			$ui_state = 'clear';
		}
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> View a Submission', 'ipt_fsqm' ), false, $ui_state );
		if ( isset( $_GET['id'] ) || isset( $_GET['id2'] ) ) {
			$this->show_submission();
		} else {
			$this->show_form();
		}
		$this->index_foot();
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function save_post( $check_referer = true ) {
		parent::save_post();
		die();
	}

	public function on_load_page() {
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'overview',
				'title' => __( 'Overview', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'Using this page, you can view/edit a particular submission either by it\'s ID (which is mailed to the notification email when a submission is being submitted) Or select one from the latest 100.', 'ipt_fsqm' ) . '</p>',
			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	private function show_submission() {
		$id = !empty( $_GET['id'] ) ? (int) $_GET['id'] : $_GET['id2'];
		$edit = isset( $_GET['edit'] ) ? true : false;
		$form = new IPT_FSQM_Form_Elements_Front( $id );

		if ( $edit ) {
			if ( !current_user_can( 'manage_feedback' ) ) {
				wp_die( __( 'Cheatin&#8217; uh?' ) );
			}
			$form->show_form( true, true );
		} else {
			IPT_FSQM_Form_Elements_Static::ipt_fsqm_full_preview( $id );
		}

	}

	private function show_form() {
		global $wpdb, $ipt_fsqm_info;
		$s = array();
		$last100 = $wpdb->get_results( "SELECT d.f_name f_name, d.l_name l_name, d.id id, f.name name FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id ORDER BY `date` DESC LIMIT 0, 100" );
		if ( empty( $last100 ) ) {
			$this->ui->msg_error( __( 'There are no submissions in the database. Please be patient!', 'ipt_fsqm' ) );
			return;
		}

		foreach ( $last100 as $l ) {
			$s[$l->id] = $l->f_name . ' ' . $l->l_name . ' - ' . $l->name;
		}
		$buttons = array(
			array( __( 'View', 'ipt_fsqm' ), 'view', 'medium', 'primary', 'normal', array(), 'submit' ),
		);
		if ( current_user_can( 'manage_feedback' ) ) {
			$buttons[] = array( __( 'Edit', 'ipt_fsqm' ), 'edit', 'medium', 'secondary', 'normal', array( 'equal-height' ), 'submit' );
		}
?>
<?php $this->print_p_update( __( 'Please either enter an ID or select one from the latest 100', 'ipt_fsqm' ) ); ?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-menu"></span><?php _e( 'Select a Submission', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">

		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : ?>
			<input type="hidden" name="<?php echo esc_attr( $k ); ?>" value="<?php echo esc_attr( $v ); ?>" />
			<?php endforeach; ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row">
							<label for="id"><?php _e( 'Enter the ID', 'ipt_fsqm' ); ?></label>
						</th>
						<td>
							<?php $this->print_input_text( 'id', '', 'regular-text code' ); ?>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="id2"><?php _e( 'Or Select One', 'ipt_fsqm' ); ?></label>
						</th>
						<td>
							<select name="id2" id="id2" class="ipt_uif_select">
								<?php $this->print_select_op( $s, null, true ); ?>
							</select>
						</td>
					</tr>
				</tbody>
			</table>
			<?php $this->ui->buttons( $buttons ); ?>
		</form>
	</div>
</div>
		<?php
	}
}

/**
 * View all Submissions Class
 */
class IPT_FSQM_View_All_Submissions extends IPT_FSQM_Admin_Base {
	/**
	 * The feedback table class object
	 * Should be instantiated on-load
	 *
	 * @var IPT_FSQM_Data_Table
	 */
	public $table_view;
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_view_all_nonce';

		parent::__construct();
		$this->icon = 'newspaper';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[5] = array(
			'type' => 'error',
			'msg' => __( 'Please select an action', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'update',
			'msg' => __( 'Successfully deleted the submission', 'ipt_fsqm' ),
		);

		$this->post_result[7] = array(
			'type' => 'update',
			'msg' => __( 'Successfully updated the submission', 'ipt_fsqm' ),
		);
		$this->post_result[8] = array(
			'type' => 'update',
			'msg' => __( 'An error has occured updating the submission. Either you haven\'t changed anything or something terrible has happened. Please contact the developer', 'ipt_fsqm' ),
		);
		$this->post_result[9] = array(
			'type' => 'update',
			'msg' => __( 'Successfully starred the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[10] = array(
			'type' => 'update',
			'msg' => __( 'Successfully unstarred the submissions', 'ipt_fsqm' ),
		);
		$this->post_result[11] = array(
			'type' => 'error',
			'msg' => __( 'Please select some submissions to perform the action', 'ipt_fsqm' ),
		);

		add_action( 'wp_ajax_ipt_fsqm_star', array( &$this, 'ajax_star' ) );
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'View all Submissions', 'ipt_fsqm' ), __( 'View all Submissions', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_view_all_submissions', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> View All Submissions', 'ipt_fsqm' ), false );
		$this->table_view->prepare_items();
?>
<style type="text/css">
	.column-star {
		width: 50px;
	}
	.column-title {
		width: 300px;
	}
</style>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Submissions', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<form action="" method="get">
			<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
			<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
			<?php endif; endforeach; ?>
			<?php $this->table_view->search_box( __( 'Search Submissions', 'ipt_fsqm' ), 'search_id' ); ?>
			<?php $this->table_view->display(); ?>
		</form>
	</div>
</div>
<script type="text/javascript">
(function($) {
	$(document).ready(function() {
		var _ipt_fsqm_nonce = '<?php echo wp_create_nonce( 'ipt_fsqm_star' ); ?>';
		$('a.ipt_fsqm_star').click(function(e) {
			e.preventDefault();
			var $this = this;
			$(this).html('<img src="<?php echo admin_url( '/images/wpspin_light.gif' ); ?>" />');
			var data_id = $(this).parent().siblings('th').find('input').attr('value');
			var data = {
				'id' : data_id,
				'action' : 'ipt_fsqm_star',
				'_wpnonce' : _ipt_fsqm_nonce
			};
			$.post(ajaxurl, data, function(response) {
				$($this).html(response.html);
				_ipt_fsqm_nonce = responce.nonce;
			}, 'json');
		});
	});
})(jQuery);
</script>
		<?php
		$this->index_foot();
	}

	public function save_post( $check_referer = true ) {
		parent::save_post();
	}

	public function ajax_star() {
		global $wpdb, $ipt_fsqm_info;
		$id = $_REQUEST['id'];
		$nonce = $_REQUEST['_wpnonce'];
		if ( !wp_verify_nonce( $nonce, 'ipt_fsqm_star' ) ) {
			echo json_encode( array( 'html' => '<img title="Invalid Nonce. Cheating uh?" src="' . plugins_url( '/static/admin/images/error.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => 'boundtoFAIL' ) );
			die();
		}

		$data = $wpdb->get_var( $wpdb->prepare( "SELECT star FROM {$ipt_fsqm_info['data_table']} WHERE id = %d", $id ) );
		if ( null == $data ) {
			echo json_encode( array( 'html' => '<img title="Invalid ID associtated. Try Again?" src="' . plugins_url( '/static/admin/images/error.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
			die();
		}

		if ( 0 == $data ) {
			IPT_FSQM_Form_Elements_Static::star_submissions( $id );
			echo json_encode( array( 'html' => '<img title="' . __( 'Click to Unstar', 'ipt_fsqm' ) . '" src="' . plugins_url( '/static/admin/images/star_on.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
		} else {
			IPT_FSQM_Form_Elements_Static::unstar_submissions( $id );
			echo json_encode( array( 'html' => '<img title="' . __( 'Click to Star', 'ipt_fsqm' ) . '" src="' . plugins_url( '/static/admin/images/star_off.png', IPT_FSQM_Loader::$abs_file ) . '" />', 'nonce' => wp_create_nonce( 'ipt_fsqm_star' ) ) );
		}
		die();
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Data_Table();

		if ( $_SERVER['REQUEST_METHOD'] == 'POST' )
			$this->save_post ();

		$action = $this->table_view->current_action();

		if ( false !== $action ) {
			//check if single delete request
			if ( isset( $_GET['id'] ) ) {
				if ( wp_verify_nonce( $_GET['_wpnonce'], 'ipt_fsqm_delete_' . $_GET['id'] ) ) {
					IPT_FSQM_Form_Elements_Static::delete_submissions( $_GET['id'] );
					wp_redirect( add_query_arg( array( 'post_result' => 6 ), 'admin.php?page=' . $_GET['page'] ) );
				} else {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}
				die();
			} else {
				//bulk actions
				if ( !wp_verify_nonce( $_GET['_wpnonce'], 'bulk-ipt_fsqm_table_items' ) ) {
					wp_die( __( 'Cheatin&#8217; uh?' ) );
				}

				if ( empty( $_GET['feedbacks'] ) ) {
					wp_redirect( add_query_arg( array( 'post_result' => 11 ), $_GET['_wp_http_referer'] ) );
					die();
				}


				switch ( $action ) {
				case 'delete' :
					if ( IPT_FSQM_Form_Elements_Static::delete_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 4 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				case 'star' :
					if ( IPT_FSQM_Form_Elements_Static::star_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 9 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				case 'unstar' :
					if ( IPT_FSQM_Form_Elements_Static::unstar_submissions( $_GET['feedbacks'] ) ) {
						wp_redirect( add_query_arg( array( 'post_result' => 10 ), $_GET['_wp_http_referer'] ) );
					} else {
						wp_redirect( add_query_arg( array( 'post_result' => 2 ), $_GET['_wp_http_referer'] ) );
					}
					break;
				default :
					wp_redirect( add_query_arg( array( 'post_result' => 5 ), $_GET['_wp_http_referer'] ) );
				}
				die();
			}
		}

		//clean up the URL
		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Submissions per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'feedbacks_per_page',
		);
		add_screen_option( $option, $args );
		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
				'id'  => 'overview',
				'title'  => __( 'Overview' ),
				'content' =>
				'<p>' . __( 'This screen provides access to all of your submissions & surveys. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
				'<p>' . __( 'By default, this screen will show all the submissions and submissions of all the available forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'screen-content',
				'title'  => __( 'Screen Content' ),
				'content' =>
				'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
				'<ul>' .
				'<li>' . __( 'You can select a particular form and filter submissions on that form only.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( 'You can hide/display columns based on your needs and decide how many submissions to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( 'You can search a particular submission by using the Search Form. You can type in just the first name or the last name or the email or the ID or even the IP Address.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'action-links',
				'title'  => __( 'Available Actions' ),
				'content' =>
				'<p>' . __( 'Hovering over a row in the posts list will display action links that allow you to manage your submissions. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Quick Preview</strong>: Pops up a modal window with the detailed preview of the particular submission. You can also print the submission if you wish to.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Full View</strong>: Opens up a page where you can view the form along with the submission data.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Edit Submission</strong>: Lets you edit all the aspects of the submission. Most importantly you can add administrator remarks which will be shown on the track page.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Delete</strong> removes the submission from this list as well as from the database. You can not restore it back, so make sure you want to delete it before you do.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Star Column</strong> lets you star/unstar a submission. Simply click on the star to toggle.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );
		get_current_screen()->add_help_tab( array(
				'id'  => 'bulk-actions',
				'title'  => __( 'Bulk Actions' ),
				'content' =>
				'<p>' . __( 'There are a number of bulk actions available. Here are the details.', 'ipt_fsqm' ) . '</p>' .
				'<ul>' .
				'<li>' . __( '<strong>Delete</strong>. This will permanently delete the ticked submissions from the database.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Mark Starred</strong>. This will mark the submissions starred.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Mark Unstarred</strong>. This will mark the submissions unstarred.', 'ipt_fsqm' ) . '</li>' .
				'</ul>'
			) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

class IPT_FSQM_Payments extends IPT_FSQM_Admin_Base {
	/**
	 * @var        IPT_Payments_Table $table_view 	payment table class
	 */
	public $table_view;

	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_payment_nonce';

		parent::__construct();

		$this->icon = 'file-text';
		add_filter( 'set-screen-option', array( $this, 'table_set_option' ), 10, 3 );

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully updated the payment.', 'ipt_fsqm' ),
		);

		$this->post_result[5] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully deleted the payment.', 'ipt_fsqm' ),
		);
		$this->post_result[6] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully deleted the payments.', 'ipt_fsqm' ),
		);
	}

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm Payments', 'ipt_fsqm' ), __( 'Payments', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_payments', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Payments', 'ipt_fsqm' ), false );
		$this->table_view->prepare_items();
		?>
		<style type="text/css">
			.column-title {
				width: 250px;
			}
			.column-txn {
				width: 300px;
			}
		</style>
		<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
			<div class="ipt_uif_box cyan">
				<h3><span class="ipt-icomoon-pencil"></span><?php _e( 'Modify and/or View Payments', 'ipt_fsqm' ); ?></h3>
			</div>
			<div class="ipt_uif_iconbox_inner">
				<form action="" method="get">
					<?php foreach ( $_GET as $k => $v ) : if ( $k == 'order' || $k == 'orderby' || $k == 'page' ) : ?>
					<input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
					<?php endif; endforeach; ?>
					<?php $this->table_view->search_box( __( 'Search Tranasction ID', 'ipt_fsqm' ), 'search_id' ); ?>
					<?php $this->table_view->display(); ?>
				</form>
				<div class="clear"></div>
			</div>
		</div>
		<?php $this->index_foot( false );
	}

	public function save_post( $check_referer = true ) {
		parent::save_post( $check_referer );
	}

	public function on_load_page() {
		global $wpdb, $ipt_fsqm_info;

		$this->table_view = new IPT_FSQM_Payments_Table();

		if ( !empty( $_GET['_wp_http_referer'] ) ) {
			wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ) ) );
			die();
		}

		$option = 'per_page';
		$args = array(
			'label' => __( 'Payments per page', 'ipt_fsqm' ),
			'default' => 20,
			'option' => 'fsqm_payment_per_page',
		);

		add_screen_option( $option, $args );


		parent::on_load_page();

		get_current_screen()->add_help_tab( array(
			'id'  => 'overview',
			'title'  => __( 'Overview' ),
			'content' =>
			'<p>' . __( 'This screen provides access to all of your payments. You can customize the display of this screen to suit your workflow.', 'ipt_fsqm' ) . '</p>' .
			'<p>' . __( 'By default, this screen will show all the payments of all the available forms. Please check the Screen Content for more information.', 'ipt_fsqm' ) . '</p>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'screen-content',
			'title'  => __( 'Screen Content' ),
			'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
			'<li>' . __( 'You can select a particular form and filter payments on that form only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can select a particular payment method and filter payments on that method only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can select a particular payment status and filter payments on that status only.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can hide/display columns based on your needs and decide how many payments to list per screen using the Screen Options tab.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( 'You can search a particular payment by transaction ID through the Search Form.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
		get_current_screen()->add_help_tab( array(
			'id'  => 'action-links',
			'title'  => __( 'Available Actions' ),
			'content' =>
			'<p>' . __( 'Hovering over a row in the payments list will display action links that allow you to manage your payments. You can perform the following actions:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Quick Preview</strong>: Pops up a modal window with the detailed preview of the particular payment. You can also print the payment if you wish to.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Full View</strong>: Opens up a page where you can view the form along with the payment data.', 'ipt_fsqm' ) . '</li>' .
			'</ul>'
		) );
	}

	public function table_set_option( $status, $option, $value ) {
		return $value;
	}
}

/**
 * Settings Class
 */
class IPT_FSQM_Settings extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_settings_nonce';

		parent::__construct();

		$this->icon = 'settings';

		$this->post_result[4] = array(
			'type' => 'okay',
			'msg' => __( 'Successfully saved the options as well as created sample forms. You may now head to <a href="admin.php?page=ipt_fsqm_all_forms">View all Forms</a> to start editing them.', 'ipt_fsqm' ),
		);
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'eForm Settings', 'ipt_fsqm' ), __( 'Settings', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_settings', array( &$this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		global $ipt_fsqm_settings;
		$ipt_fsqm_key = get_option( 'ipt_fsqm_key' );
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Settings', 'ipt_fsqm' ) );
?>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-cog"></span><?php _e( 'Modify Plugin Settings', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="global_email"><?php _e( 'Global Notification Email', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->print_input_text( 'global[email]', $ipt_fsqm_settings['email'], 'regular-text code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Enter the email where you want to send notifications for all the feedback forms.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_track_page"><?php _e( 'Single Submission Trackback Page for Unregistered Users', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->ui->dropdown_pages( array(
						'name' => 'global[track_page]',
						'selected' => $ipt_fsqm_settings['track_page'],
						'show_option_none' => __( 'Please select a page', 'ipt_fsqm' ),
					) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Select the page where you\'ve put the <code>[ipt_fsqm_trackback]</code> shortcode. The page will be linked throughout all the notification email.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_utrack_page"><?php _e( 'Central Trackback page for Registered Users', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->ui->dropdown_pages( array(
						'name' => 'global[utrack_page]',
						'selected' => $ipt_fsqm_settings['utrack_page'],
						'show_option_none' => __( 'Please select a page', 'ipt_fsqm' ),
					) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<?php _e( 'Select the page where you\'ve put the <code>[ipt_fsqm_utrackback]</code> shortcode. The page will be linked throughout all the notification email.', 'ipt_fsqm' ); ?>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[key]', __( 'Secret Encryption Key', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'global[key]', $ipt_fsqm_key, __( 'Can not be empty', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ) ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This key is used to generate the trackback keys. If you change this, then all the trackback codes will get reset.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'Use this with extreme caution and change only if necessary. The new trackback keys will not be sent to the users.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="global_email"><?php _e( 'Google Places API Key', 'ipt_fsqm' ); ?></label>
				</th>
				<td>
					<?php $this->print_input_text( 'global[gplaces_api]', $ipt_fsqm_settings['gplaces_api'], 'regular-text code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
						<p><?php _e( 'You need to enter a valid Google Places API to make sure the localtion picker (GPS) element works.', 'ipt_fsqm' ); ?></p>
						<ul>
							<li><?php _e( 'Go to <a href="https://developers.google.com/maps/documentation/javascript/get-api-key">This Page</a>.', 'ipt_fsqm' ); ?></li>
							<li><?php _e( 'Click on the <strong>Gey Key</strong> button.', 'ipt_fsqm' ); ?></li>
							<li><?php _e( 'Follow onscreen instructions.', 'ipt_fsqm' ); ?></li>
							<li><?php _e( 'Make sure your application has the Google Places API Web Service permission.', 'ipt_fsqm' ); ?></li>
							<li><?php _e( 'Paste the browser key here.', 'ipt_fsqm' ); ?></li>
						</ul>
						<p><?php _e( 'More instructions can be found <a href="https://wpquark.com/kb/?p=9859">here</a>.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[disable_un]', __( 'Disable Update Notification', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[disable_un]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['disable_un'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Starting version 3.1.0 FSQM would show a notice if a newer version is available. If you do not want to get bothered, then please disable it here.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[delete_uninstall]', __( 'Delete all Data when uninstalling plugin', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->toggle( 'global[delete_uninstall]', __( 'yes', 'ipt_fsqm' ), 'no', $ipt_fsqm_settings['delete_uninstall'] ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'If you want to completely wipe out all data when uninstalling, then have this enabled. Keep it disabled, if you are planning to update the plugin by uninstalling and then reinstalling.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
		</table>
	</div>
</div>
<div class="ipt_uif_iconbox ipt_uif_shadow glowy">
	<div class="ipt_uif_box cyan">
		<h3><span class="ipt-icomoon-file2"></span><?php _e( 'Modify Standalone Forms Settings', 'ipt_fsqm' ); ?></h3>
	</div>
	<div class="ipt_uif_iconbox_inner">
		<table class="form-table">
			<tbody>
				<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][base]', __( 'Permalink Base', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'global[standalone][base]', $ipt_fsqm_settings['standalone']['base'], __( 'Can not be empty', 'ipt_fsqm' ), 'fit', 'normal', 'code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This will be the base of any permalink generated for your standalone forms.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'If you want the links to be like <code>http://example.com/<strong>webforms</strong>/my-awesome-form/01/</code> then use <code>webforms</code> as the base.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][head]', __( 'HTML Head Section', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->textarea( 'global[standalone][head]', $ipt_fsqm_settings['standalone']['head'], __( 'CSS or JS or Meta Tags', 'ipt_fsqm' ), 'widefat', 'normal', 'code' ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'If you want to put any custom CSS code or other HTML tags inside the <code>&lt;head&gt;</code> section, then do it here.', 'ipt_fsqm' ); ?></p>
					<p><?php _e( 'Please note that, if a css file named fsqm-pro.css or fsqm-pro-{form_id}.css is present inside your current theme directory, then it will be included by default.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][before]', __( 'Before Form HTML', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->wp_editor( 'global[standalone][before]', $ipt_fsqm_settings['standalone']['before'] ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This content will be appended before the output of the form.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<?php $this->ui->generate_label( 'global[standalone][after]', __( 'After Form HTML', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->wp_editor( 'global[standalone][after]', $ipt_fsqm_settings['standalone']['after'] ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'This content will be appended after the output of the form.', 'ipt_fsqm' ); ?></p>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
			</tbody>
		</table>
	</div>
</div>
		<?php
		$this->index_foot();
	}

	public function save_post( $check_referer = true ) {
		parent::save_post();

		$settings = array(
			'email' => $this->post['global']['email'],
			'track_page' => $this->post['global']['track_page'],
			'utrack_page' => $this->post['global']['utrack_page'],
			'delete_uninstall' => isset( $this->post['global']['delete_uninstall'] ) && '' != $this->post['global']['delete_uninstall'] ? true : false,
			'standalone' => array(
				'base' => $this->post['global']['standalone']['base'],
				'before' => $this->post['global']['standalone']['before'],
				'after' => $this->post['global']['standalone']['after'],
				'head' => $this->post['global']['standalone']['head'],
			),
			'disable_un' => isset( $this->post['global']['disable_un'] ) && '' != $this->post['global']['disable_un'] ? true : false,
			'gplaces_api' => $this->post['global']['gplaces_api'],
		);

		if ( trim( $settings['standalone']['base'] ) == '' ) {
			$settings['standalone']['base'] = 'eforms';
		}

		$settings['standalone']['base'] = sanitize_title( $settings['standalone']['base'] );

		update_option( 'ipt_fsqm_settings', $settings );

		$key = $this->post['global']['key'];
		if ( trim( $key ) == '' ) {
			$key = NONCE_SALT;
		}
		update_option( 'ipt_fsqm_key', $key );

		wp_redirect( add_query_arg( array( 'post_result' => 1 ), $_POST['_wp_http_referer'] ) );
		die();
	}

	public function on_load_page() {
		flush_rewrite_rules();
		parent::on_load_page();
		get_current_screen()->add_help_tab( array(
				'id' => 'track',
				'title' => __( 'Settings', 'ipt_fsqm' ),
				'content' =>
				'<p>' . __( 'There are five settings which you can change.', 'ipt_fsqm' ) . '<p>' .
				'<ul>' .
				'<li>' . __( '<strong>Global Notification Email:</strong> Enter an email where the notification will be sent everytime a user submits any of the forms.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Single Submission Trackback Page for Unregistered Users:</strong> Select the page where you\'ve put the <code>[ipt_fsqm_trackback]</code> shortcode. From this page users can see their submission and print if they want. The page will be linked throughout all the notification email.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Central Trackback page for Registered Users:</strong> Select the page where you\'ve put the [ipt_fsqm_utrackback] shortcode. From this page, logged in users will be able to see all their submissions and also they will be getting a link to the trackback page. The page will be linked throughout all the trackbacks whenever applicable.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Backward Compatible Shortcode:</strong> If you are coming from older version (prior to version 2.x) then you need to leave it enabled in order to make the older format of shortcodes work. Since version 2.x, the shortcode format was changed to a more localized form.', 'ipt_fsqm' ) . '</li>' .
				'<li>' . __( '<strong>Secret Encryption Key:</strong> This key is used to generate the trackback keys. If you change this, then all the trackback codes will get reset.', 'ipt_fsqm' ) . '</li>' .
				'</ul>' .
				'<p>' . __( 'Please set the settings up before going live with your forms.', 'ipt_fsqm' ) . '</p>',
			) );
		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}
}

class IPT_FSQM_Import_Export extends IPT_FSQM_Admin_Base {
	public function __construct() {
		$this->capability = 'manage_feedback';
		$this->action_nonce = 'ipt_fsqm_import_export_nonce';

		parent::__construct();

		$this->icon = 'code';
		add_action( 'wp_ajax_ipt_fsqm_generate_export', array( $this, 'generate_export' ) );
		add_action( 'wp_ajax_ipt_fsqm_generate_import', array( $this, 'generate_import' ) );
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/

	public function admin_menu() {
		$this->pagehook = add_submenu_page( 'ipt_fsqm_dashboard', __( 'Import & Export Forms - eForm', 'ipt_fsqm' ), __( 'Import/Export Forms', 'ipt_fsqm' ), $this->capability, 'ipt_fsqm_import_export', array( $this, 'index' ) );
		parent::admin_menu();
	}

	public function index() {
		$this->index_head( __( 'eForm <span class="ipt-icomoon-arrow-right2"></span> Import/Export Forms', 'ipt_fsqm' ), false );
		wp_nonce_field( 'ipt_fsqm_import_export_nonce', 'ipt_fsqm_ie_nonce' );
		$this->ui->iconbox( __( 'Generate Export Code', 'ipt_fsqm' ), array( $this, 'export_code_html' ), 'copy' );
		$this->ui->iconbox( __( 'Import Form from Code', 'ipt_fsqm' ), array( $this, 'import_code_html' ), 'paste2' );
		$this->index_foot( false );
	}

	public function on_load_page() {
		get_current_screen()->add_help_tab( array(
			'id' => 'overview',
			'title' => __( 'Overview', 'ipt_fsqm' ),
			'content' =>
			'<p>' . __( 'This screen provides tools to export and/or import forms among different sites of yours or friends.', 'ipt_fsqm' ) . '<p>' .
			'<p>' . __( 'Using the export code is pretty easy. You are presented with two options:', 'ipt_fsqm' ) . '</p>' .
			'<ul>' .
			'<li>' . __( '<strong>Generate Export Code:</strong> Simply select the form and hit Generate Code button. It will give you the export code of the form. Copy the code and keep it handy somewhere.', 'ipt_fsqm' ) . '</li>' .
			'<li>' . __( '<strong>Import Form from Code:</strong> Here you can insert previously generated code to recreate the form. Enter form name (if you wish to override the name) and the code in respected fields and hit the Import from Code button. It will automatically generate the form. It will also notify you should any problem is found.', 'ipt_fsqm' ) . '</li>' .
			'</ul>' .
			'<p>' . __( 'Also as a bonus, click on the help icon beside <strong>Enter Export Code</strong> and you will get an amazing form.', 'ipt_fsqm' ) . '</p>',
		) );

		get_current_screen()->set_help_sidebar(
			'<p><strong>' . __( 'For more information:', 'ipt_fsqm' ) . '</strong></p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Documentation</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$documentation ) . '</p>' .
			'<p>' . sprintf( __( '<a href="%s" target="_blank">Support Forums</a>', 'ipt_fsqm' ), IPT_FSQM_Loader::$support_forum ) . '</p>'
		);
	}

	/*==========================================================================
	 * Form HTML
	 *========================================================================*/

	public function export_code_html() {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
		$form_select = array();
		$form_select[] = array(
			'label' => __( '--Please select a form--', 'ipt_fsqm' ),
			'value' => '',
		);
		foreach ( $forms as $form ) {
			$form_select[] = array(
				'label' => $form->name,
				'value' => $form->id,
			);
		}
		?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ipt_fsqm_export_form').on('submit', function(e) {
			// Prevent form submission
			e.preventDefault();

			// Init the variables
			var self = $(this),
			button = self.find('#export_code_generate'),
			ajax_loader = self.find('#ipt_fsqm_export_code_generator_ajax'),
			textarea = self.find('#export_code'),
			tr_to_hide = self.find('.ipt_fsqm_tr_hide'),
			ajax_data = {
				form_id: self.find('#form_id').val(),
				_wpnonce: $('#ipt_fsqm_ie_nonce').val(),
				action: 'ipt_fsqm_generate_export'
			};

			// Hide things first
			tr_to_hide.fadeOut('fast');

			// Disable the submit button
			button.prop('disabled', true);

			// Show the ajax loader
			ajax_loader.fadeIn('fast');

			$.get(ajaxurl, ajax_data, function(data) {
				// Get the message box
				var msg_tr = self.find('.ipt_fsqm_tr_hide.msg_error'),
				// Get the textarea tr
				txt_tr = self.find('.ipt_fsqm_tr_hide.export_code');

				if ( data.error ) { // There is an error, so show the error
					msg_tr.find('.ipt_uif_box.red').html('<p><strong>Error</strong>: ' + data.code + ';</p>');
					msg_tr.fadeIn('fast');
				} else { // It was successful, so show the code
					textarea.val(data.code);
					txt_tr.fadeIn('fast');
				}
			}, 'json').always(function() {
				// Enable submit button
				button.prop('disabled', false);
				// Show the ajax loader
				ajax_loader.fadeOut('fast');
			}).fail(function(jqXHR, textStatus, errorThrown) {
				// Show the message
				var msg_tr = self.find('.ipt_fsqm_tr_hide.msg_error');
				msg_tr.find('.ipt_uif_box.red').html('<p><strong>Ajax Error</strong>: Status: ' + textStatus + '; Error: ' + errorThrown + ';</p>');
				msg_tr.fadeIn('fast');
			});
		});
	});
</script>
<form action="" method="get" id="ipt_fsqm_export_form">
	<table class="form-table">
		<tbody>
			<tr>
				<th><?php $this->ui->generate_label( 'form_id', __( 'Select a form', 'ipt_fsqm' ) ); ?></th>
				<td>
					<?php $this->ui->select( 'form_id', $form_select, '' ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'Please select a form for which you want to generate the export code.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr style="display: none" class="ipt_fsqm_tr_hide export_code">
				<td colspan="3">
					<?php $this->ui->msg_okay( __( 'Please copy the code below', 'ipt_fsqm' ) ); ?>
					<?php $this->ui->textarea( 'export_code', '', '', 'fit', 'normal', array( 'code' ), false, false, 10 ); ?>
				</td>
			</tr>
			<tr class="ipt_fsqm_tr_hide msg_error" style="display: none">
				<td colspan="3">
					<?php $this->ui->msg_error( '' ); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ipt_uif_float_left">
		<?php $this->ui->button( __( 'Generate Code', 'ipt_fsqm' ), 'export_code_generate', 'large', 'primary', 'normal', array(), 'submit' ); ?>
	</div>
	<div class="ipt_uif_float_left">
		<?php $this->ui->ajax_loader( true, 'ipt_fsqm_export_code_generator_ajax', array(), true, __( 'Generating Code', 'ipt_fsqm' ) ); ?>
	</div>
	<?php $this->ui->clear(); ?>
</form>
		<?php
	}

	public function import_code_html() {
		?>
<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#ipt_fsqm_import_form').on('submit', function(e) {
			// Prevent the submit
			e.preventDefault();

			// Get all variables
			var self = $(this),
			button = self.find('#import_code_generate'),
			ajax_loader = self.find('#ipt_fsqm_import_code_generator_ajax'),
			divs_to_hide = self.find('.hide_div'),
			okay_box = self.find('#ipt_fsqm_import_result_okay'),
			error_box = self.find('#ipt_fsqm_import_result_error'),
			ajax_data = {
				form_name: self.find('#form_name').val(),
				form_code: self.find('#form_code').val(),
				_wpnonce: $('#ipt_fsqm_ie_nonce').val(),
				action: 'ipt_fsqm_generate_import'
			};

			// Hide things first
			divs_to_hide.fadeOut('fast');

			// Disable the submit button
			button.prop('disabled', true);

			// Show the ajax loader
			ajax_loader.fadeIn('fast');

			// Post the data
			$.post(ajaxurl, ajax_data, function( data ) {
				// Get the okay box
				if ( data.error ) {
					error_box.find('.ipt_uif_message').html('<p>' + data.code + '</p>');
					error_box.fadeIn('fast');
				} else {
					okay_box.find('.ipt_uif_message').html('<p>' + data.code + '</p>');
					okay_box.fadeIn('fast');
				}
			}).always(function() {
				// Enable submit button
				button.prop('disabled', false);
				// Hide the ajax loader
				ajax_loader.fadeOut('fast');
				// Reset the values
				self.find('#form_name').val('');
				self.find('#form_code').val('');
			}).fail(function(jqXHR, textStatus, errorThrown) {
				error_box.find('.ipt_uif_message').html('<p><strong>Ajax Error</strong>: Status: ' + textStatus + '; Error: ' + errorThrown + ';</p>');
				error_box.fadeIn('fast');
			});
		});
	});
</script>
<form action="" method="get" id="ipt_fsqm_import_form">
	<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'form_name', __( 'Enter Form Name', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->text( 'form_name', '', __( 'Leave empty to use from the code', 'ipt_fsqm' ), 'large' ); ?>
				</td>
				<td>
					<?php $this->ui->help( __( 'You can override the form name from the code. Leaving it empty will simply use the form name available on the import code.', 'ipt_fsqm' ) ); ?>
				</td>
			</tr>
			<tr>
				<th>
					<?php $this->ui->generate_label( 'form_code', __( 'Enter Export Code', 'ipt_fsqm' ) ); ?>
				</th>
				<td>
					<?php $this->ui->textarea( 'form_code', '', __( 'Paste the export code', 'ipt_fsqm' ), 'fit', 'normal', array( 'code' ), false, false, 10 ); ?>
				</td>
				<td>
					<?php $this->ui->help_head(); ?>
					<p><?php _e( 'Please copy paste the export code here. Try the following for fun:', 'ipt_fsqm' ); ?></p>
					<code style="display: block; height: 200px; overflow: auto;">
<pre><?php $this->print_sample_import_code(); ?></pre>
					</code>
					<?php $this->ui->help_tail(); ?>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="ipt_uif_float_left">
		<?php $this->ui->button( __( 'Import from Code', 'ipt_fsqm' ), 'import_code_generate', 'large', 'primary', 'normal', array(), 'submit' ); ?>
	</div>
	<div class="ipt_uif_float_left">
		<?php $this->ui->ajax_loader( true, 'ipt_fsqm_import_code_generator_ajax', array(), true, __( 'Importing Form', 'ipt_fsqm' ) ); ?>
	</div>
	<?php $this->ui->clear(); ?>
	<div class="hide_div" style="display: none;" id="ipt_fsqm_import_result_okay">
		<?php $this->ui->msg_okay( '' ); ?>
	</div>
	<div class="hide_div" style="display: none;" id="ipt_fsqm_import_result_error">
		<?php $this->ui->msg_error( '' ); ?>
	</div>
</form>
		<?php
	}

	public function print_sample_import_code() {
		?>
YToxMDp7czoyOiJpZCI7czoyOiI3NSI7czo0OiJuYW1lIjtzOjExOiJSZXN1bWUgRm9ybSI7czo4
OiJzZXR0aW5ncyI7czoyMTkxOiJhOjEwOntzOjc6ImdlbmVyYWwiO2E6Njp7czoxMDoidGVybXNf
cGFnZSI7czoxOiIwIjtzOjEyOiJ0ZXJtc19waHJhc2UiO3M6MTgwOiJCeSBzdWJtaXR0aW5nIHRo
aXMgZm9ybSwgeW91IGhlcmVieSBhZ3JlZSB0byBhY2NlcHQgb3VyIDxhIGhyZWY9IiUxJHMiIHRh
cmdldD0iX2JsYW5rIj5UZXJtcyAmIENvbmRpdGlvbnM8L2E+LiBZb3VyIElQIGFkZHJlc3MgPHN0
cm9uZz4lMiRzPC9zdHJvbmc+IHdpbGwgYmUgc3RvcmVkIGluIG91ciBkYXRhYmFzZS4iO3M6MTM6
ImNvbW1lbnRfdGl0bGUiO3M6MjE6IkFkbWluaXN0cmF0b3IgUmVtYXJrcyI7czoxNToiZGVmYXVs
dF9jb21tZW50IjtzOjEwOiJQcm9jZXNzaW5nIjtzOjg6ImNhbl9lZGl0IjtiOjE7czo5OiJlZGl0
X3RpbWUiO3M6MDoiIjt9czo0OiJ1c2VyIjthOjY6e3M6MTY6Im5vdGlmaWNhdGlvbl9zdWIiO3M6
MjU6IldlIGhhdmUgZ290IHlvdXIgYW5zd2Vycy4iO3M6MTY6Im5vdGlmaWNhdGlvbl9tc2ciO3M6
MTk1OiJUaGFuayB5b3UgJU5BTUUlIGZvciB0YWtpbmcgdGhlIHF1aXovc3VydmV5L2ZlZWRiYWNr
Lg0KV2UgaGF2ZSByZWNlaXZlZCB5b3VyIGFuc3dlcnMuIFlvdSBjYW4gdmlldyBpdCBhbnl0aW1l
IGZyb20gdGhpcyBsaW5rIGJlbG93Og0KJVRSQUNLX0xJTkslDQpIZXJlIGlzIGEgY29weSBvZiB5
b3VyIHN1Ym1pc3Npb246DQolU1VCTUlTU0lPTiUiO3M6MTc6Im5vdGlmaWNhdGlvbl9mcm9tIjtz
OjM0OiJpUGFuZWxUaGVtZXMgTG9jYWxob3N0IERldmVsb3BtZW50IjtzOjE4OiJub3RpZmljYXRp
b25fZW1haWwiO3M6MjQ6InN3YXNoYXRhQGxvY2FsaG9zdC5sb2NhbCI7czo0OiJzbXRwIjtiOjA7
czoxMToic210cF9jb25maWciO2E6NTp7czo4OiJlbmNfdHlwZSI7czozOiJzc2wiO3M6NDoiaG9z
dCI7czoxNDoic210cC5nbWFpbC5jb20iO3M6NDoicG9ydCI7czozOiI0NjUiO3M6ODoidXNlcm5h
bWUiO3M6NToiYWRtaW4iO3M6ODoicGFzc3dvcmQiO3M6NDQ6Im04aFJnY1ZTcEpzMHdGbXlXbmpM
TTFGbHRHT0hIdGd3OVJnLzBnMDlXS0U9Ijt9fXM6NToiYWRtaW4iO2E6Mzp7czo1OiJlbWFpbCI7
czoyNDoic3dhc2hhdGFAbG9jYWxob3N0LmxvY2FsIjtzOjE1OiJtYWlsX3N1Ym1pc3Npb24iO2I6
MDtzOjE0OiJzZW5kX2Zyb21fdXNlciI7YjowO31zOjEwOiJsaW1pdGF0aW9uIjthOjM6e3M6MTE6
ImVtYWlsX2xpbWl0IjtzOjE6IjAiO3M6ODoiaXBfbGltaXQiO3M6MToiMCI7czoxMDoidXNlcl9s
aW1pdCI7czoxOiIwIjt9czoxMzoidHlwZV9zcGVjaWZpYyI7YTozOntzOjEwOiJwYWdpbmF0aW9u
IjthOjE6e3M6MTc6InNob3dfcHJvZ3Jlc3NfYmFyIjtiOjE7fXM6MzoidGFiIjthOjE6e3M6MTI6
ImNhbl9wcmV2aW91cyI7YjoxO31zOjY6Im5vcm1hbCI7YToxOntzOjc6IndyYXBwZXIiO2I6MDt9
fXM6NzoiYnV0dG9ucyI7YTozOntzOjQ6Im5leHQiO3M6NDoiTmV4dCI7czo0OiJwcmV2IjtzOjg6
IlByZXZpb3VzIjtzOjY6InN1Ym1pdCI7czo2OiJTdWJtaXQiO31zOjEwOiJzdWJtaXNzaW9uIjth
OjM6e3M6MTM6InByb2Nlc3NfdGl0bGUiO3M6MjI6IlByb2Nlc3NpbmcgeW91IHJlcXVlc3QiO3M6
MTM6InN1Y2Nlc3NfdGl0bGUiO3M6Mjg6IllvdXIgZm9ybSBoYXMgYmVlbiBzdWJtaXR0ZWQiO3M6
MTU6InN1Y2Nlc3NfbWVzc2FnZSI7czozMzoiVGhhbmsgeW91IGZvciBnaXZpbmcgeW91ciBhbnN3
ZXJzIjt9czoxMToicmVkaXJlY3Rpb24iO2E6NTp7czo0OiJ0eXBlIjtzOjQ6Im5vbmUiO3M6NToi
ZGVsYXkiO3M6NDoiMTAwMCI7czozOiJ0b3AiO2I6MDtzOjM6InVybCI7czoxMToiJVRSQUNLQkFD
SyUiO3M6NToic2NvcmUiO2E6MDp7fX1zOjc6InJhbmtpbmciO2E6Mzp7czo3OiJlbmFibGVkIjti
OjA7czo1OiJ0aXRsZSI7czoxMToiRGVzaWduYXRpb24iO3M6NToicmFua3MiO2E6MDp7fX1zOjU6
InRoZW1lIjthOjQ6e3M6ODoidGVtcGxhdGUiO3M6NzoiZGVmYXVsdCI7czo0OiJsb2dvIjtzOjA6
IiI7czoxMjoiY3VzdG9tX3N0eWxlIjtiOjA7czo1OiJzdHlsZSI7YTo1OntzOjk6ImhlYWRfZm9u
dCI7czo2OiJvc3dhbGQiO3M6OToiYm9keV9mb250IjtzOjY6InJvYm90byI7czoxNDoiYmFzZV9m
b250X3NpemUiO3M6MjoiMTIiO3M6MTQ6ImhlYWRfZm9udF90eXBvIjthOjI6e3M6NDoiYm9sZCI7
YjowO3M6NjoiaXRhbGljIjtiOjA7fXM6NjoiY3VzdG9tIjtzOjA6IiI7fX19IjtzOjY6ImxheW91
dCI7czoyMDQ5OiJhOjI6e2k6MDthOjc6e3M6NDoidHlwZSI7czozOiJ0YWIiO3M6NToidGl0bGUi
O3M6ODoiSWRlbnRpZnkiO3M6ODoic3VidGl0bGUiO3M6ODoieW91cnNlbGYiO3M6MTE6ImRlc2Ny
aXB0aW9uIjtzOjA6IiI7czo2OiJtX3R5cGUiO3M6NjoibGF5b3V0IjtzOjg6ImVsZW1lbnRzIjth
OjE4OntpOjA7YTozOntzOjY6Im1fdHlwZSI7czo2OiJkZXNpZ24iO3M6NDoidHlwZSI7czo4OiJj
b2xfaGFsZiI7czozOiJrZXkiO3M6MToiMCI7fWk6MTthOjM6e3M6NjoibV90eXBlIjtzOjY6ImRl
c2lnbiI7czo0OiJ0eXBlIjtzOjg6ImNvbF9oYWxmIjtzOjM6ImtleSI7czoxOiIxIjt9aToyO2E6
Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6ODoiY2hlY2tib3giO3M6Mzoi
a2V5IjtzOjE6IjEiO31pOjM7YTozOntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7
czo2OiJzbGlkZXIiO3M6Mzoia2V5IjtzOjE6IjIiO31pOjQ7YTozOntzOjY6Im1fdHlwZSI7czoz
OiJtY3EiO3M6NDoidHlwZSI7czo2OiJzbGlkZXIiO3M6Mzoia2V5IjtzOjE6IjMiO31pOjU7YToz
OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJ0b2dnbGUiO3M6Mzoia2V5
IjtzOjI6IjExIjt9aTo2O2E6Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6
MTA6InN0YXJyYXRpbmciO3M6Mzoia2V5IjtzOjE6IjQiO31pOjc7YTozOntzOjY6Im1fdHlwZSI7
czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJ0b2dnbGUiO3M6Mzoia2V5IjtzOjI6IjEyIjt9aTo4
O2E6Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjM6
ImtleSI7czoxOiI1Ijt9aTo5O2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoi
dHlwZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6Mzoia2V5IjtzOjE6IjMiO31pOjEwO2E6Mzp7
czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoidHlwZSI7czoxNDoiZmVlZGJhY2tfc21h
bGwiO3M6Mzoia2V5IjtzOjE6IjQiO31pOjExO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5
cGUiO3M6NDoidHlwZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6Mzoia2V5IjtzOjE6IjUiO31p
OjEyO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6NDoidHlwZSI7czoxNDoiZmVl
ZGJhY2tfbGFyZ2UiO3M6Mzoia2V5IjtzOjE6IjYiO31pOjEzO2E6Mzp7czo2OiJtX3R5cGUiO3M6
MzoibWNxIjtzOjQ6InR5cGUiO3M6NjoibWF0cml4IjtzOjM6ImtleSI7czoxOiI2Ijt9aToxNDth
OjM6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czo0OiJ0eXBlIjtzOjU6InJhbmdlIjtzOjM6Imtl
eSI7czoxOiI4Ijt9aToxNTthOjM6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czo0OiJ0eXBlIjtz
OjU6InJhbmdlIjtzOjM6ImtleSI7czoxOiI5Ijt9aToxNjthOjM6e3M6NjoibV90eXBlIjtzOjM6
Im1jcSI7czo0OiJ0eXBlIjtzOjY6InRvZ2dsZSI7czozOiJrZXkiO3M6MjoiMTMiO31pOjE3O2E6
Mzp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjQ6InR5cGUiO3M6NToicmFuZ2UiO3M6Mzoia2V5
IjtzOjI6IjEwIjt9fXM6NDoiaWNvbiI7czo1OiI1NzUwNCI7fWk6MTthOjc6e3M6NDoidHlwZSI7
czozOiJ0YWIiO3M6NToidGl0bGUiO3M6NjoiVXBsb2FkIjtzOjg6InN1YnRpdGxlIjtzOjExOiJ5
b3VyIHJlc3VtZSI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjY6Im1fdHlwZSI7czo2OiJs
YXlvdXQiO3M6ODoiZWxlbWVudHMiO2E6Mzp7aTowO2E6Mzp7czo2OiJtX3R5cGUiO3M6ODoiZnJl
ZXR5cGUiO3M6NDoidHlwZSI7czo2OiJ1cGxvYWQiO3M6Mzoia2V5IjtzOjE6IjAiO31pOjE7YToz
OntzOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo0OiJ0eXBlIjtzOjY6InVwbG9hZCI7czoz
OiJrZXkiO3M6MToiMSI7fWk6MjthOjM6e3M6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjQ6
InR5cGUiO3M6NjoidXBsb2FkIjtzOjM6ImtleSI7czoxOiIyIjt9fXM6NDoiaWNvbiI7czo1OiI1
NzQyOCI7fX0iO3M6NjoiZGVzaWduIjtzOjYxMToiYToyOntpOjA7YTo2OntzOjQ6InR5cGUiO3M6
ODoiY29sX2hhbGYiO3M6NToidGl0bGUiO3M6MDoiIjtzOjg6InN1YnRpdGxlIjtzOjA6IiI7czox
MToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjY6Im1fdHlwZSI7czo2OiJkZXNpZ24iO3M6ODoiZWxl
bWVudHMiO2E6Mjp7aTowO2E6Mzp7czo2OiJtX3R5cGUiO3M6NToicGluZm8iO3M6NDoidHlwZSI7
czo2OiJmX25hbWUiO3M6Mzoia2V5IjtzOjE6IjAiO31pOjE7YTozOntzOjY6Im1fdHlwZSI7czo1
OiJwaW5mbyI7czo0OiJ0eXBlIjtzOjY6ImxfbmFtZSI7czozOiJrZXkiO3M6MToiMSI7fX19aTox
O2E6Njp7czo0OiJ0eXBlIjtzOjg6ImNvbF9oYWxmIjtzOjU6InRpdGxlIjtzOjA6IiI7czo4OiJz
dWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czo2OiJtX3R5cGUiO3M6
NjoiZGVzaWduIjtzOjg6ImVsZW1lbnRzIjthOjI6e2k6MDthOjM6e3M6NjoibV90eXBlIjtzOjU6
InBpbmZvIjtzOjQ6InR5cGUiO3M6NToiZW1haWwiO3M6Mzoia2V5IjtzOjE6IjIiO31pOjE7YToz
OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6NDoidHlwZSI7czo2OiJzbGlkZXIiO3M6Mzoia2V5
IjtzOjE6IjAiO319fX0iO3M6MzoibWNxIjtzOjc4ODU6ImE6MTM6e2k6MDthOjg6e3M6NDoidHlw
ZSI7czo2OiJzbGlkZXIiO3M6NToidGl0bGUiO3M6MzoiQWdlIjtzOjEwOiJ2YWxpZGF0aW9uIjth
OjA6e31zOjg6InN1YnRpdGxlIjtzOjc6ImluIHllYXIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtzOjM6
Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjM6Im1pbiI7czoyOiIxOCI7czozOiJtYXgiO3M6
MjoiNjAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9fWk6MTthOjg6e3M6
NDoidHlwZSI7czo4OiJjaGVja2JveCI7czo1OiJ0aXRsZSI7czoyMToiU2VsZWN0IGFsbCB0aGF0
IGFwcGx5IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjI6e3M6ODoicmVxdWlyZWQiO2I6MTtzOjc6ImZp
bHRlcnMiO2E6Mjp7czoxMToibWluQ2hlY2tib3giO3M6MDoiIjtzOjExOiJtYXhDaGVja2JveCI7
czowOiIiO319czo4OiJzdWJ0aXRsZSI7czoyMToiYnV0IGRvIG5vdCBleGFnZ2VyYXRlIjtzOjEx
OiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZl
IjtiOjA7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6Mjp7
aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5IjtzOjE6IjAiO3M6
NToiY2hlY2siO3M6MzoibGVuIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtz
OjE6IjIiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBlIjtzOjg6ImZy
ZWV0eXBlIjtzOjM6ImtleSI7czoxOiIxIjtzOjU6ImNoZWNrIjtzOjM6ImxlbiI7czo4OiJvcGVy
YXRvciI7czozOiJuZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9
fX1zOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6NDp7czo3OiJvcHRpb25z
IjthOjQ6e2k6MDthOjI6e3M6NToibGFiZWwiO3M6MTY6IkkgYW0gYSBQSFAgTmluamEiO3M6NToi
c2NvcmUiO3M6MDoiIjt9aToxO2E6Mjp7czo1OiJsYWJlbCI7czoxNjoiSSBsb3ZlIFdvcmRQcmVz
cyI7czo1OiJzY29yZSI7czowOiIiO31pOjI7YToyOntzOjU6ImxhYmVsIjtzOjU4OiJDU1MzIGFu
ZCBqUXVlcnkgaXMgd2hhdCBJIHVzZSB0byBwZXJzb25pZnkgbXkgaW1hZ2luYXRpb25zIjtzOjU6
InNjb3JlIjtzOjA6IiI7fWk6MzthOjI6e3M6NToibGFiZWwiO3M6NDA6Ik15U1FMIHNpbXBseSBt
ZWFucyBhIHNwYWNlIHRvIHN0b3JlIGRhdGEiO3M6NToic2NvcmUiO3M6MDoiIjt9fXM6NzoiY29s
dW1ucyI7czoxOiIxIjtzOjY6Im90aGVycyI7YjoxO3M6Nzoib19sYWJlbCI7czo2OiJPdGhlcnMi
O319aToyO2E6ODp7czo0OiJ0eXBlIjtzOjY6InNsaWRlciI7czo1OiJ0aXRsZSI7czozNToiWWVh
cnMgb2YgUEhQIGRldmVsb3BtZW50IGV4cGVyaWVuY2UiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7
fXM6ODoic3VidGl0bGUiO3M6MDoiIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNv
bmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNo
YW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6MzoibWNx
IjtzOjM6ImtleSI7czoxOiIxIjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRvciI7
czoyOiJjdCI7czo1OiJ2YWx1ZSI7czozOiJQSFAiO3M6MzoicmVsIjtzOjM6ImFuZCI7fX19czo2
OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRpbmdzIjthOjQ6e3M6MzoibWluIjtzOjE6IjAi
O3M6MzoibWF4IjtzOjI6IjUwIjtzOjQ6InN0ZXAiO2k6MTtzOjEwOiJzaG93X2NvdW50IjtiOjE7
fX1pOjM7YTo4OntzOjQ6InR5cGUiO3M6Njoic2xpZGVyIjtzOjU6InRpdGxlIjtzOjI5OiJZZWFy
cyBvZiBXb3JkUHJlc3MgZXhwZXJpZW5jZSI7czoxMDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJz
dWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9u
YWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjti
OjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoi
a2V5IjtzOjE6IjEiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImN0
IjtzOjU6InZhbHVlIjtzOjk6IldvcmRQcmVzcyI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6
Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6NDp7czozOiJtaW4iO3M6MToiMCI7
czozOiJtYXgiO3M6MjoiNTAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9
fWk6MTE7YTo4OntzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjU6InRpdGxlIjtzOjIxOiJZb3Ug
YSBXb3JkUHJlc3MgTmluamEiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUi
O3M6MTc6Im5vdyBkb24ndCBiZSBzaHkhIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6
ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6
ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6Mzoi
bWNxIjtzOjM6ImtleSI7czoxOiIzIjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRv
ciI7czoyOiJndCI7czo1OiJ2YWx1ZSI7czoyOiI0MCI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1z
OjY6Im1fdHlwZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6Mzp7czoyOiJvbiI7czo0OiJZ
ZWFwIjtzOjM6Im9mZiI7czo0OiJOb3BlIjtzOjc6ImNoZWNrZWQiO2I6MDt9fWk6NDthOjg6e3M6
NDoidHlwZSI7czoxMDoic3RhcnJhdGluZyI7czo1OiJ0aXRsZSI7czo3OiJSYXRlIGl0IjtzOjEw
OiJ2YWxpZGF0aW9uIjthOjE6e3M6ODoicmVxdWlyZWQiO2I6MTt9czo4OiJzdWJ0aXRsZSI7czox
NjoidGhlIHdheSB5b3Ugd2FudCI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25k
aXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjowO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFu
Z2UiO2I6MTtzOjU6ImxvZ2ljIjthOjA6e319czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNl
dHRpbmdzIjthOjI6e3M6Nzoib3B0aW9ucyI7YToyOntpOjA7czoxNDoiVXNlciBJbnRlcmZhY2Ui
O2k6MTtzOjg6Ik5pY2VuZXNzIjt9czozOiJtYXgiO3M6MjoiMTAiO319aToxMjthOjg6e3M6NDoi
dHlwZSI7czo2OiJ0b2dnbGUiO3M6NToidGl0bGUiO3M6MTU6IlNvIHlvdSBsaWtlIHVzPyI7czox
MDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJzdWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0
aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6
InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2Ontz
OjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjQiO3M6NToiY2hlY2siO3M6Mzoi
dmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6Imd0IjtzOjU6InZhbHVlIjtzOjE6IjYiO3M6MzoicmVs
IjtzOjM6ImFuZCI7fX19czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRpbmdzIjthOjM6
e3M6Mjoib24iO3M6NDoiWWVhaCI7czozOiJvZmYiO3M6NDoiTm9wZSI7czo3OiJjaGVja2VkIjti
OjA7fX1pOjU7YTo4OntzOjQ6InR5cGUiO3M6NjoidG9nZ2xlIjtzOjU6InRpdGxlIjtzOjM0OiJX
YW5uYSBhbnN3ZXIgYSBmZXcgbW9yZSBxdWVzdGlvbnM/IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjA6
e31zOjg6InN1YnRpdGxlIjtzOjI0OiJjb21tb24gdGhhdCB3aWxsIGJlIGZ1biEiO3M6MTE6ImRl
c2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6
MDtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YTowOnt9fXM6
NjoibV90eXBlIjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTozOntzOjI6Im9uIjtzOjQ6Illl
YWgiO3M6Mzoib2ZmIjtzOjQ6Ik5vcGUiO3M6NzoiY2hlY2tlZCI7YjowO319aTo2O2E6ODp7czo0
OiJ0eXBlIjtzOjY6Im1hdHJpeCI7czo1OiJ0aXRsZSI7czoxMzoiWW91ciBzdWJqZWN0cyI7czox
MDoidmFsaWRhdGlvbiI7YToxOntzOjg6InJlcXVpcmVkIjtiOjE7fXM6ODoic3VidGl0bGUiO3M6
MjY6ImZvciBkaWZmZXJlbnQgaW5zdGl0dXRpb25zIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIi
O3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6
MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6MTp7aTowO2E6Njp7czo2OiJtX3R5cGUi
O3M6MzoibWNxIjtzOjM6ImtleSI7czoxOiI1IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJv
cGVyYXRvciI7czoyOiJlcSI7czo1OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQi
O319fXM6NjoibV90eXBlIjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjQ6InJvd3Mi
O2E6Mzp7aTowO3M6MTE6IkhpZ2ggU2Nob29sIjtpOjE7czo3OiJDb2xsZWdlIjtpOjI7czoxMDoi
VW5pdmVyc2l0eSI7fXM6NzoiY29sdW1ucyI7YTozOntpOjA7czo3OiJQaHlzaWNzIjtpOjE7czox
MToiTWF0aGVtYXRpY3MiO2k6MjtzOjk6IkNoZW1pc3RyeSI7fXM6Njoic2NvcmVzIjthOjM6e2k6
MDtzOjA6IiI7aToxO3M6MDoiIjtpOjI7czowOiIiO31zOjg6Im11bHRpcGxlIjtiOjE7fX1pOjg7
YTo4OntzOjQ6InR5cGUiO3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MTk6IlBoeXNpY3MgU2Nv
cmUgUmFuZ2UiO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUiO3M6MzQ6Im1p
bmltdW0gdG8gbWF4aW11bSAoaW4gcGVyY2VudGFnZSkiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToyOntpOjA7YTo2OntzOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjYiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6
Im9wZXJhdG9yIjtzOjI6ImN0IjtzOjU6InZhbHVlIjtzOjc6InBoeXNpY3MiO3M6MzoicmVsIjtz
OjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToi
NSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFs
dWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlwZSI7czozOiJtY3Ei
O3M6ODoic2V0dGluZ3MiO2E6NDp7czozOiJtaW4iO3M6MToiMCI7czozOiJtYXgiO3M6MzoiMTAw
IjtzOjQ6InN0ZXAiO2k6MTtzOjEwOiJzaG93X2NvdW50IjtiOjE7fX1pOjk7YTo4OntzOjQ6InR5
cGUiO3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MjM6Ik1hdGhlbWF0aWNzIFNjb3JlIFJhbmdl
IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjA6e31zOjg6InN1YnRpdGxlIjtzOjM0OiJtaW5pbXVtIHRv
IG1heGltdW0gKGluIHBlcmNlbnRhZ2UpIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6
ImNvbmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6
ImNoYW5nZSI7YjoxO3M6NToibG9naWMiO2E6Mjp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6Mzoi
bWNxIjtzOjM6ImtleSI7czoxOiI2IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRv
ciI7czoyOiJjdCI7czo1OiJ2YWx1ZSI7czo0OiJtYXRoIjtzOjM6InJlbCI7czozOiJhbmQiO31p
OjE7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hl
Y2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fX19czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjg6InNldHRp
bmdzIjthOjQ6e3M6MzoibWluIjtzOjE6IjAiO3M6MzoibWF4IjtzOjM6IjEwMCI7czo0OiJzdGVw
IjtpOjE7czoxMDoic2hvd19jb3VudCI7YjoxO319aToxMzthOjg6e3M6NDoidHlwZSI7czo2OiJ0
b2dnbGUiO3M6NToidGl0bGUiO3M6MzQ6IkRvIHlvdSBrbm93IGRpZmZlcmVudGlhbCBjYWxjdWx1
cz8iO3M6MTA6InZhbGlkYXRpb24iO2E6MDp7fXM6ODoic3VidGl0bGUiO3M6MzE6IkF0IHlvdXIg
c2NvcmUgaXQgc2hvdWxkIGJlIGVhc3kiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToi
Y29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6Njoi
Y2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YTozOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czozOiJt
Y3EiO3M6Mzoia2V5IjtzOjE6IjkiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9y
IjtzOjI6Imd0IjtzOjU6InZhbHVlIjtzOjI6IjY5IjtzOjM6InJlbCI7czozOiJhbmQiO31pOjE7
YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjYiO3M6NToiY2hlY2si
O3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImN0IjtzOjU6InZhbHVlIjtzOjQ6Im1hdGgi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czoz
OiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6Mjoi
ZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6ODoic2V0dGluZ3MiO2E6Mzp7czoyOiJvbiI7czo0OiJZZWFoIjtzOjM6
Im9mZiI7czo0OiJOb3BlIjtzOjc6ImNoZWNrZWQiO2I6MDt9fWk6MTA7YTo4OntzOjQ6InR5cGUi
O3M6NToicmFuZ2UiO3M6NToidGl0bGUiO3M6MjE6IkNoZW1pc3RyeSBTY29yZSBSYW5nZSI7czox
MDoidmFsaWRhdGlvbiI7YTowOnt9czo4OiJzdWJ0aXRsZSI7czozNDoibWluaW11bSB0byBtYXhp
bXVtIChpbiBwZXJjZW50YWdlKSI7czoxMToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25k
aXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjoxO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFu
Z2UiO2I6MTtzOjU6ImxvZ2ljIjthOjM6e2k6MDthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7
czozOiJrZXkiO3M6MToiNiI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6
MjoiY3QiO3M6NToidmFsdWUiO3M6OToiY2hlbWlzdHJ5IjtzOjM6InJlbCI7czozOiJhbmQiO31p
OjE7YTo2OntzOjY6Im1fdHlwZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hl
Y2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEi
O3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjM6Im1jcSI7czoz
OiJrZXkiO3M6MToiMCI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6Mjoi
ZXEiO3M6NToidmFsdWUiO3M6MDoiIjtzOjM6InJlbCI7czozOiJhbmQiO319fXM6NjoibV90eXBl
IjtzOjM6Im1jcSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjM6Im1pbiI7czoxOiIwIjtzOjM6Im1h
eCI7czozOiIxMDAiO3M6NDoic3RlcCI7aToxO3M6MTA6InNob3dfY291bnQiO2I6MTt9fX0iO3M6
ODoiZnJlZXR5cGUiO3M6NTc1MDoiYTo3OntpOjM7YTo4OntzOjQ6InR5cGUiO3M6MTQ6ImZlZWRi
YWNrX3NtYWxsIjtzOjU6InRpdGxlIjtzOjE4OiJXaGVyZSBkbyB5b3UgbGl2ZT8iO3M6MTA6InZh
bGlkYXRpb24iO2E6Mjp7czo4OiJyZXF1aXJlZCI7YjoxO3M6NzoiZmlsdGVycyI7YTo1OntzOjQ6
InR5cGUiO3M6MzoiYWxsIjtzOjM6Im1pbiI7czowOiIiO3M6MzoibWF4IjtzOjA6IiI7czo3OiJt
aW5TaXplIjtzOjA6IiI7czo3OiJtYXhTaXplIjtzOjA6IiI7fX1zOjg6InN1YnRpdGxlIjtzOjMw
OiJqdXN0IHRoZSBjb3VudHJ5IHdvdWxkIGJlIGZpbmUiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6
IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7
YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7YToxOntpOjA7YTo2OntzOjY6Im1fdHlw
ZSI7czozOiJtY3EiO3M6Mzoia2V5IjtzOjE6IjUiO3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6
Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVlIjtzOjE6IjEiO3M6MzoicmVsIjtzOjM6ImFu
ZCI7fX19czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6ODoic2V0dGluZ3MiO2E6NDp7czo1
OiJlbWFpbCI7czowOiIiO3M6NDoiaWNvbiI7czo1OiI1NzM0NSI7czoxMToicGxhY2Vob2xkZXIi
O3M6MTA6IldyaXRlIGhlcmUiO3M6NToic2NvcmUiO3M6MDoiIjt9fWk6NDthOjg6e3M6NDoidHlw
ZSI7czoxNDoiZmVlZGJhY2tfc21hbGwiO3M6NToidGl0bGUiO3M6MzY6IkluZGlhPyBUaGF0J3Mg
Z3JlYXQhIEluIHdoaWNoIHN0YXRlPyI7czoxMDoidmFsaWRhdGlvbiI7YToyOntzOjg6InJlcXVp
cmVkIjtiOjE7czo3OiJmaWx0ZXJzIjthOjU6e3M6NDoidHlwZSI7czozOiJhbGwiO3M6MzoibWlu
IjtzOjA6IiI7czozOiJtYXgiO3M6MDoiIjtzOjc6Im1pblNpemUiO3M6MDoiIjtzOjc6Im1heFNp
emUiO3M6MDoiIjt9fXM6ODoic3VidGl0bGUiO3M6MjI6IldlIGxvdmUgSW5kaWEgZG9uJ3Qgd2Ui
O3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJh
Y3RpdmUiO2I6MTtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjE7czo1OiJsb2dpYyI7
YToyOntpOjA7YTo2OntzOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToi
MyI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFs
dWUiO3M6NToiaW5kaWEiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBl
IjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoi
b3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5k
Ijt9fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo4OiJzZXR0aW5ncyI7YTo0OntzOjU6
ImVtYWlsIjtzOjA6IiI7czo0OiJpY29uIjtzOjU6IjU3MzQ1IjtzOjExOiJwbGFjZWhvbGRlciI7
czoxMDoiV3JpdGUgaGVyZSI7czo1OiJzY29yZSI7czowOiIiO319aTo1O2E6ODp7czo0OiJ0eXBl
IjtzOjE0OiJmZWVkYmFja19zbWFsbCI7czo1OiJ0aXRsZSI7czozMzoiUGxlYXNlIGFsc28gbGV0
IHVzIGtub3cgeW91ciBjaXR5IjtzOjEwOiJ2YWxpZGF0aW9uIjthOjI6e3M6ODoicmVxdWlyZWQi
O2I6MTtzOjc6ImZpbHRlcnMiO2E6NTp7czo0OiJ0eXBlIjtzOjM6ImFsbCI7czozOiJtaW4iO3M6
MDoiIjtzOjM6Im1heCI7czowOiIiO3M6NzoibWluU2l6ZSI7czowOiIiO3M6NzoibWF4U2l6ZSI7
czowOiIiO319czo4OiJzdWJ0aXRsZSI7czozMDoiQ2F1c2Ugd2UnZCBhbHdheXMgbGlrZSB0byBr
bm93IjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjthOjQ6e3M6
NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6NToibG9n
aWMiO2E6Mzp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5Ijtz
OjE6IjQiO3M6NToiY2hlY2siO3M6MzoibGVuIjtzOjg6Im9wZXJhdG9yIjtzOjI6Imd0IjtzOjU6
InZhbHVlIjtzOjE6IjEiO3M6MzoicmVsIjtzOjM6ImFuZCI7fWk6MTthOjY6e3M6NjoibV90eXBl
IjtzOjM6Im1jcSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2YWwiO3M6ODoi
b3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6MToiMSI7czozOiJyZWwiO3M6MzoiYW5k
Ijt9aToyO2E6Njp7czo2OiJtX3R5cGUiO3M6ODoiZnJlZXR5cGUiO3M6Mzoia2V5IjtzOjE6IjMi
O3M6NToiY2hlY2siO3M6MzoidmFsIjtzOjg6Im9wZXJhdG9yIjtzOjI6ImVxIjtzOjU6InZhbHVl
IjtzOjU6ImluZGlhIjtzOjM6InJlbCI7czozOiJhbmQiO319fXM6NjoibV90eXBlIjtzOjg6ImZy
ZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjQ6e3M6NToiZW1haWwiO3M6MDoiIjtzOjQ6Imljb24i
O3M6NToiNTczNDUiO3M6MTE6InBsYWNlaG9sZGVyIjtzOjEwOiJXcml0ZSBoZXJlIjtzOjU6InNj
b3JlIjtzOjA6IiI7fX1pOjY7YTo4OntzOjQ6InR5cGUiO3M6MTQ6ImZlZWRiYWNrX2xhcmdlIjtz
OjU6InRpdGxlIjtzOjE4OiJHaXZlIHlvdXIgYWRkcmVzcz8iO3M6MTA6InZhbGlkYXRpb24iO2E6
MTp7czo4OiJyZXF1aXJlZCI7YjowO31zOjg6InN1YnRpdGxlIjtzOjIyOiJ3ZSBsaXZlIGF0IGtv
bGthdGEgdG9vIjtzOjExOiJkZXNjcmlwdGlvbiI7czowOiIiO3M6MTE6ImNvbmRpdGlvbmFsIjth
OjQ6e3M6NjoiYWN0aXZlIjtiOjE7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNoYW5nZSI7YjoxO3M6
NToibG9naWMiO2E6NDp7aTowO2E6Njp7czo2OiJtX3R5cGUiO3M6MzoibWNxIjtzOjM6ImtleSI7
czoxOiI1IjtzOjU6ImNoZWNrIjtzOjM6InZhbCI7czo4OiJvcGVyYXRvciI7czoyOiJlcSI7czo1
OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQiO31pOjE7YTo2OntzOjY6Im1fdHlw
ZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToiMyI7czo1OiJjaGVjayI7czozOiJ2YWwi
O3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6NToiaW5kaWEiO3M6MzoicmVs
IjtzOjM6ImFuZCI7fWk6MjthOjY6e3M6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjM6Imtl
eSI7czoxOiI0IjtzOjU6ImNoZWNrIjtzOjM6ImxlbiI7czo4OiJvcGVyYXRvciI7czoyOiJndCI7
czo1OiJ2YWx1ZSI7czoxOiIxIjtzOjM6InJlbCI7czozOiJhbmQiO31pOjM7YTo2OntzOjY6Im1f
dHlwZSI7czo4OiJmcmVldHlwZSI7czozOiJrZXkiO3M6MToiNSI7czo1OiJjaGVjayI7czozOiJ2
YWwiO3M6ODoib3BlcmF0b3IiO3M6MjoiZXEiO3M6NToidmFsdWUiO3M6Nzoia29sa2F0YSI7czoz
OiJyZWwiO3M6MzoiYW5kIjt9fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7czo4OiJzZXR0
aW5ncyI7YTozOntzOjU6ImVtYWlsIjtzOjA6IiI7czoxMToicGxhY2Vob2xkZXIiO3M6MTA6Ildy
aXRlIGhlcmUiO3M6NToic2NvcmUiO3M6MDoiIjt9fWk6MDthOjg6e3M6NDoidHlwZSI7czo2OiJ1
cGxvYWQiO3M6NToidGl0bGUiO3M6MjU6IlBsZWFzZSB1cGxvYWQgeW91ciByZXN1bWUiO3M6MTA6
InZhbGlkYXRpb24iO2E6MTp7czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6
IiI7czoxMToiZGVzY3JpcHRpb24iO3M6NTQ6IkRvY3VtZW50cyBvbmx5LiBTaG91bGQgY29udGFp
biB5b3VyIHNjYW5uZWQgc2lnbmF0dXJlLiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJh
Y3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7
YTowOnt9fXM6NjoibV90eXBlIjtzOjg6ImZyZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjEyOntz
OjQ6Imljb24iO3M6NToiNTc3ODciO3M6MTc6ImFjY2VwdF9maWxlX3R5cGVzIjtzOjEyOiJkb2Ms
ZG9jeCxwZGYiO3M6MTk6Im1heF9udW1iZXJfb2ZfZmlsZXMiO3M6MToiMiI7czoxOToibWluX251
bWJlcl9vZl9maWxlcyI7czowOiIiO3M6MTM6Im1heF9maWxlX3NpemUiO3M6NzoiODM4ODYwOCI7
czoxMzoibWluX2ZpbGVfc2l6ZSI7czoxOiIxIjtzOjIwOiJ3cF9tZWRpYV9pbnRlZ3JhdGlvbiI7
YjowO3M6MTE6ImF1dG9fdXBsb2FkIjtiOjA7czoxMToiZHJhZ19uX2Ryb3AiO2I6MTtzOjEyOiJw
cm9ncmVzc19iYXIiO2I6MTtzOjEzOiJwcmV2aWV3X21lZGlhIjtiOjE7czoxMDoiY2FuX2RlbGV0
ZSI7YjoxO319aToxO2E6ODp7czo0OiJ0eXBlIjtzOjY6InVwbG9hZCI7czo1OiJ0aXRsZSI7czox
NzoiVXBsb2FkIHlvdXIgcGhvdG8iO3M6MTA6InZhbGlkYXRpb24iO2E6MTp7czo4OiJyZXF1aXJl
ZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czoxMToiZGVzY3JpcHRpb24iO3M6NDk6Iklt
YWdlIG9ubHkuIFNob3VsZCBiZSBhdCBsZWFzdCA2MDBYNjAwcHggaW4gc2l6ZS4iO3M6MTE6ImNv
bmRpdGlvbmFsIjthOjQ6e3M6NjoiYWN0aXZlIjtiOjA7czo2OiJzdGF0dXMiO2I6MDtzOjY6ImNo
YW5nZSI7YjowO3M6NToibG9naWMiO2E6MDp7fX1zOjY6Im1fdHlwZSI7czo4OiJmcmVldHlwZSI7
czo4OiJzZXR0aW5ncyI7YToxMjp7czo0OiJpY29uIjtzOjU6IjU3MzQ2IjtzOjE3OiJhY2NlcHRf
ZmlsZV90eXBlcyI7czoxNjoiZ2lmLGpwZWcscG5nLGpwZyI7czoxOToibWF4X251bWJlcl9vZl9m
aWxlcyI7czoxOiIyIjtzOjE5OiJtaW5fbnVtYmVyX29mX2ZpbGVzIjtzOjE6IjIiO3M6MTM6Im1h
eF9maWxlX3NpemUiO3M6NzoiODM4ODYwOCI7czoxMzoibWluX2ZpbGVfc2l6ZSI7czoxOiIxIjtz
OjIwOiJ3cF9tZWRpYV9pbnRlZ3JhdGlvbiI7YjoxO3M6MTE6ImF1dG9fdXBsb2FkIjtiOjA7czox
MToiZHJhZ19uX2Ryb3AiO2I6MTtzOjEyOiJwcm9ncmVzc19iYXIiO2I6MTtzOjEzOiJwcmV2aWV3
X21lZGlhIjtiOjE7czoxMDoiY2FuX2RlbGV0ZSI7YjoxO319aToyO2E6ODp7czo0OiJ0eXBlIjtz
OjY6InVwbG9hZCI7czo1OiJ0aXRsZSI7czoyODoiVXBsb2FkIHJlY29tbWVuZGF0aW9uIGxldHRl
ciI7czoxMDoidmFsaWRhdGlvbiI7YToxOntzOjg6InJlcXVpcmVkIjtiOjA7fXM6ODoic3VidGl0
bGUiO3M6MDoiIjtzOjExOiJkZXNjcmlwdGlvbiI7czo4MjoiVGhpcyBpcyBvcHRpb25hbC4gQSBy
ZWNvbW1lbmRhdGlvbiB3aWxsIGFsd2F5cyBoZWxwIHlvdSBmaW5kIGEgYmV0dGVyIGluIG91ciBm
aXJtLiI7czoxMToiY29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1
cyI7YjowO3M6NjoiY2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtz
Ojg6ImZyZWV0eXBlIjtzOjg6InNldHRpbmdzIjthOjEyOntzOjQ6Imljb24iO3M6NToiNTc3MjIi
O3M6MTc6ImFjY2VwdF9maWxlX3R5cGVzIjtzOjM3OiJkb2MsZG9jeCxqcGcsanBlZyxnaWYscG5n
LHBkZixtcDQsbXAzIjtzOjE5OiJtYXhfbnVtYmVyX29mX2ZpbGVzIjtzOjE6IjIiO3M6MTk6Im1p
bl9udW1iZXJfb2ZfZmlsZXMiO3M6MDoiIjtzOjEzOiJtYXhfZmlsZV9zaXplIjtzOjc6IjEwMDAw
MDAiO3M6MTM6Im1pbl9maWxlX3NpemUiO3M6MToiMSI7czoyMDoid3BfbWVkaWFfaW50ZWdyYXRp
b24iO2I6MDtzOjExOiJhdXRvX3VwbG9hZCI7YjoxO3M6MTE6ImRyYWdfbl9kcm9wIjtiOjE7czox
MjoicHJvZ3Jlc3NfYmFyIjtiOjE7czoxMzoicHJldmlld19tZWRpYSI7YjoxO3M6MTA6ImNhbl9k
ZWxldGUiO2I6MTt9fX0iO3M6NToicGluZm8iO3M6OTkzOiJhOjM6e2k6MDthOjg6e3M6NDoidHlw
ZSI7czo2OiJmX25hbWUiO3M6NToidGl0bGUiO3M6MTA6IkZpcnN0IE5hbWUiO3M6MTA6InZhbGlk
YXRpb24iO2E6MTp7czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czox
MToiZGVzY3JpcHRpb24iO3M6MDoiIjtzOjExOiJjb25kaXRpb25hbCI7YTo0OntzOjY6ImFjdGl2
ZSI7YjowO3M6Njoic3RhdHVzIjtiOjA7czo2OiJjaGFuZ2UiO2I6MDtzOjU6ImxvZ2ljIjthOjA6
e319czo2OiJtX3R5cGUiO3M6NToicGluZm8iO3M6ODoic2V0dGluZ3MiO2E6MTp7czoxMToicGxh
Y2Vob2xkZXIiO3M6MTA6IldyaXRlIGhlcmUiO319aToxO2E6ODp7czo0OiJ0eXBlIjtzOjY6Imxf
bmFtZSI7czo1OiJ0aXRsZSI7czo5OiJMYXN0IE5hbWUiO3M6MTA6InZhbGlkYXRpb24iO2E6MTp7
czo4OiJyZXF1aXJlZCI7YjoxO31zOjg6InN1YnRpdGxlIjtzOjA6IiI7czoxMToiZGVzY3JpcHRp
b24iO3M6MDoiIjtzOjExOiJjb25kaXRpb25hbCI7YTo0OntzOjY6ImFjdGl2ZSI7YjowO3M6Njoi
c3RhdHVzIjtiOjA7czo2OiJjaGFuZ2UiO2I6MDtzOjU6ImxvZ2ljIjthOjA6e319czo2OiJtX3R5
cGUiO3M6NToicGluZm8iO3M6ODoic2V0dGluZ3MiO2E6MTp7czoxMToicGxhY2Vob2xkZXIiO3M6
MTA6IldyaXRlIGhlcmUiO319aToyO2E6ODp7czo0OiJ0eXBlIjtzOjU6ImVtYWlsIjtzOjU6InRp
dGxlIjtzOjU6IkVtYWlsIjtzOjEwOiJ2YWxpZGF0aW9uIjthOjE6e3M6ODoicmVxdWlyZWQiO2I6
MTt9czo4OiJzdWJ0aXRsZSI7czowOiIiO3M6MTE6ImRlc2NyaXB0aW9uIjtzOjA6IiI7czoxMToi
Y29uZGl0aW9uYWwiO2E6NDp7czo2OiJhY3RpdmUiO2I6MDtzOjY6InN0YXR1cyI7YjowO3M6Njoi
Y2hhbmdlIjtiOjA7czo1OiJsb2dpYyI7YTowOnt9fXM6NjoibV90eXBlIjtzOjU6InBpbmZvIjtz
Ojg6InNldHRpbmdzIjthOjE6e3M6MTE6InBsYWNlaG9sZGVyIjtzOjEwOiJXcml0ZSBoZXJlIjt9
fX0iO3M6NDoidHlwZSI7czoxOiIxIjtzOjc6InVwZGF0ZWQiO3M6MTk6IjIwMTQtMDQtMTkgMTU6
MzY6MDkiO30=
		<?php
	}

	/*==========================================================================
	 * AJAX Methods
	 *========================================================================*/
	public function generate_import() {
		// First set the JSON header
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Init the global arrays
		global $wpdb, $ipt_fsqm_info;

		// Get the variables
		$form_name = @$this->post['form_name'];
		$form_code = @$this->post['form_code'];
		$nonce = @$this->post['_wpnonce'];

		// Init the return
		$return = array(
			'error' => false,
			'code' => '',
		);

		// First check the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_import_export_nonce' ) || ! current_user_can( 'manage_feedback' ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Cheatin&#8217; uh?' );
			die( json_encode( (object) $return ) );
		}

		// Decode the form
		$form = maybe_unserialize( base64_decode( $form_code ) );

		// Check it's integrity
		if ( ! is_array( $form ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Invalid import code', 'ipt_fsqm' );
			die( json_encode( (object) $return ) );
		}

		// So it is an array, now check for required fields
		$required_fields = array(
			'id', 'name', 'settings', 'layout', 'design', 'mcq', 'freetype', 'pinfo', 'type',
		);
		foreach ( $required_fields as $field_key ) {
			if ( ! isset( $form[$field_key] ) ) {
				$return['error'] = true;
				$return['code'] = __( 'Import code missing required argument: ', 'ipt_fsqm' ) . $field_key;
				die( json_encode( (object) $return ) );
			}
		}

		// Override the name
		if ( $form_name != '' ) {
			$form['name'] = $form_name;
		}

		// Sanitize the name
		if ( $form['name'] == '' ) {
			$form['name'] = __( 'Untitled', 'ipt_fsqm' );
		} else {
			$form['name'] = strip_tags( $form['name'] );
		}

		// All set, now import it
		$wpdb->insert( $ipt_fsqm_info['form_table'], array(
			'name'     => $form['name'],
			'settings' => $form['settings'],
			'layout'   => $form['layout'],
			'design'   => $form['design'],
			'mcq'      => $form['mcq'],
			'freetype' => $form['freetype'],
			'pinfo'    => $form['pinfo'],
			'type'     => $form['type'],
			'category' => 0,
		), '%s' );

		$new_form_id = $wpdb->insert_id;

		$return['code'] = sprintf( __( 'Form successfully imported. <a href="%1$s">Click here to edit: %2$s</a>', 'ipt_fsqm' ), admin_url( 'admin.php?page=ipt_fsqm_all_forms&action=edit&form_id=' . $new_form_id ), $form['name'] );
		die( json_encode( (object) $return ) );
	}

	public function generate_export() {
		// First set the JSON header
		@header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );

		// Init the global arrays
		global $wpdb, $ipt_fsqm_info;

		// Get the variables
		$form_id = (int) @$_GET['form_id'];
		$nonce = @$_GET['_wpnonce'];

		// Init the return
		$return = array(
			'error' => false,
			'code' => '',
		);

		// First check the nonce
		if ( ! wp_verify_nonce( $nonce, 'ipt_fsqm_import_export_nonce' ) || ! current_user_can( 'manage_feedback' ) ) {
			$return['error'] = true;
			$return['code'] = __( 'Cheatin&#8217; uh?' );
			die( json_encode( (object) $return ) );
		}

		// Now get the form
		$form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$ipt_fsqm_info['form_table']} WHERE id = %d", $form_id ), ARRAY_A );

		// If it is invalid
		if ( null == $form ) {
			$return['error'] = true;
			$return['code'] = __( 'Invalid Form', 'ipt_fsqm' );
			die( json_encode( (object) $return ) );
		}

		// Now prepare the export
		$export = base64_encode( maybe_serialize( $form ) );
		$return['code'] = chunk_split( $export );
		die( json_encode( (object) $return ) );
	}
}

/**
 * The base admin class
 *
 * @abstract
 */
abstract class IPT_FSQM_Admin_Base {
	/**
	 * Duplicates the $_POST content and properly process it
	 * Holds the typecasted (converted int and floats properly and escaped html) value after the constructor has been called
	 *
	 * @var array
	 */
	public $post = array();

	/**
	 * Holds the hook of this page
	 *
	 * @var string Pagehook
	 * Should be set during the construction
	 */
	public $pagehook;

	/**
	 * The nonce for admin-post.php
	 * Should be set the by extending class
	 *
	 * @var string
	 */
	public $action_nonce;

	/**
	 * The class of the admin page icon
	 * Should be set by the extending class
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * This gets passed directly to current_user_can
	 * Used for security and should be set by the extending class
	 *
	 * @var string
	 */
	public $capability;

	/**
	 * Holds the URL of the static directories
	 * Just the /static/admin/ URL and sub directories under it
	 * access it like $url['js'], ['images'], ['css'], ['root'] etc
	 *
	 * @var array
	 */
	public $url = array();

	/**
	 * Set this to true if you are going to use the WordPress Metabox appearance
	 * This will enqueue all the scripts and will also set the screenlayout option
	 *
	 * @var bool False by default
	 */
	public $is_metabox = false;

	/**
	 * Default number of columns on metabox
	 *
	 * @var int
	 */
	public $metabox_col = 2;

	/**
	 * Holds the post result message string
	 * Each entry is an associative array with the following options
	 *
	 * $key : The code of the post_result value =>
	 *
	 *      'type' => 'update' : The class of the message div update | error
	 *
	 *      'msg' => '' : The message to be displayed
	 *
	 * @var array
	 */
	public $post_result = array();

	/**
	 * The action value to be used for admin-post.php
	 * This is generated automatically by appending _post_action to the action_nonce variable
	 *
	 * @var string
	 */
	public $admin_post_action;

	/**
	 * Whether or not to print form on the admin wrap page
	 * Mainly for manually printing the form
	 *
	 * @var bool
	 */
	public $print_form;

	/**
	 * The USER INTERFACE Object
	 *
	 * @var IPT_Plugin_UIF_Admin
	 */
	public $ui;

	/**
	 * The constructor function
	 * 1. Properly copies the $_POST to $this->post on POST request
	 * 2. Calls the admin_menu() function
	 * You should have parent::__construct() for all these to happen
	 *
	 * @param boolean $gets_hooked Should be true if you wish to actually put this inside an admin menu. False otherwise
	 * It basically hooks into admin_menu and admin_post_ if true
	 */
	public function __construct( $gets_hooked = true ) {
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			//$this->post = $_POST;

			//we do not need to check on magic quotes
			//as wordpress always adds magic quotes
			//@link http://codex.wordpress.org/Function_Reference/stripslashes_deep
			$this->post = wp_unslash( $_POST );

			//convert html to special characters
			//array_walk_recursive ($this->post, array($this, 'htmlspecialchar_ify'));
		}

		$this->ui = IPT_Plugin_UIF_Admin::instance( 'ipt_fsqm' );

		$plugin = IPT_FSQM_Loader::$abs_file;

		$this->url = array(
			'root' => plugins_url( '/static/admin/', $plugin ),
			'js' => plugins_url( '/static/admin/js/', $plugin ),
			'images' => plugins_url( '/static/admin/images/', $plugin ),
			'css' => plugins_url( '/static/admin/css/', $plugin ),
		);

		$this->post_result = array(
			1 => array(
				'type' => 'update',
				'msg' => __( 'Successfully saved the options.', 'ipt_fsqm' ),
			),
			2 => array(
				'type' => 'error',
				'msg' => __( 'Either you have not changed anything or some error has occured. Please contact the developer.', 'ipt_fsqm' ),
			),
			3 => array(
				'type' => 'okay',
				'msg' => __( 'The Master Reset was successful.', 'ipt_fsqm' ),
			),
		);

		$this->admin_post_action = $this->action_nonce . '_post_action';

		if ( $gets_hooked ) {
			//register admin_menu hook
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );

			//register admin-post.php hook
			add_action( 'admin_post_' . $this->admin_post_action, array( &$this, 'save_post' ) );
		}
	}

	/*==========================================================================
	 * SYSTEM METHODS
	 *========================================================================*/


	/**
	 * Hook to the admin menu
	 * Should be overriden and also the hook should be saved in the $this->pagehook
	 * In the end, the parent::admin_menu() should be called for load to hooked properly
	 */
	public function admin_menu() {
		add_action( 'load-' . $this->pagehook, array( &$this, 'on_load_page' ) );
		//$this->pagehook = add_submenu_page($parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
		//do the above or similar in the overriden callback function
	}

	/**
	 * Use this to generate the admin page
	 * always call parent::index() so the save post is called
	 * also call $this->index_foot() after the generation of page (the last line of this function)
	 * to give some compatibility (mainly with the metaboxes)
	 *
	 * @access public
	 */
	abstract public function index();

	protected function index_head( $title = '', $print_form = true, $ui_state = 'back' ) {
		$this->print_form = $print_form;
		$ui_class = 'ipt_uif';

		switch ( $ui_state ) {
		case 'back' :
			$ui_class = 'ipt_uif ipt-eform-backoffice';
			break;
		case 'front' :
			$ui_class = 'ipt_uif_front ipt-eform-backoffice';
			break;
		case 'clear':
			$ui_class = '';
			break;
		default :
		case 'none' :
			$ui_class = 'ipt_uif';
		}
?>
<style type="text/css">
	<?php echo '#' . $this->pagehook; ?>-widgets .meta-box-sortables {
		margin: 0 8px;
	}
</style>
<div class="wrap ipt_uif_common <?php echo $ui_class; ?>" id="<?php echo $this->pagehook; ?>_widgets">
	<div class="icon32">
		<span class="ipt-icomoon-<?php echo $this->icon; ?>"></span>
	</div>
	<h2><?php echo $title; ?></h2>
	<?php $this->ui->clear(); ?>
	<?php
		if ( isset( $_GET['post_result'] ) ) {
			$msg = $this->post_result[(int) $_GET['post_result']];
			if ( !empty( $msg ) ) {
				if ( $msg['type'] == 'update' || $msg['type'] == 'updated' ) {
					$this->print_update( $msg['msg'] );
				} else if ( $msg['type'] == 'okay' ) {
						$this->print_p_okay( $msg['msg'] );
					} else {
					$this->print_error( $msg['msg'] );
				}
			}
		}
?>
	<?php if ( $this->print_form ) : ?>
	<form method="post" action="admin-post.php" id="<?php echo $this->pagehook; ?>_form_primary">
		<input type="hidden" name="action" value="<?php echo $this->admin_post_action; ?>" />
		<?php wp_nonce_field( $this->action_nonce, $this->action_nonce ); ?>
		<?php if ( $this->is_metabox ) : ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php endif; ?>
	<?php endif; ?>
	<?php do_action( $this->pagehook . '_page_before', $this ); ?>
		<?php
	}

	/**
	 * Include this to the end of index function so that metaboxes work
	 */
	protected function index_foot( $submit = true, $save = 'Save Changes', $reset = 'Reset', $do_action = true ) {
		$buttons = array(
			array( $save, '', 'medium', 'primary', 'normal', array(), 'submit' ),
			array( $reset, '', 'medium', 'secondary', 'normal', array(), 'reset' ),
		);
?>
	<?php if ( $this->print_form ) : ?>
		<?php if ( true == $submit ) : ?>
		<div class="clear"></div>
		<?php $this->ui->buttons( $buttons ); ?>
		<?php endif; ?>
	</form>
	<?php endif; ?>
	<div class="clear"></div>
	<?php if ( $do_action ) : ?>
	<?php do_action( $this->pagehook . '_page_after', $this ); ?>
	<?php endif; ?>
</div>
<?php if ( $this->is_metabox ) : ?>
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	if(postboxes) {
		// close postboxes that should be closed
		$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		// postboxes setup
		postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
	}
});
//]]>
</script>
<?php endif; ?>
		<?php
	}

	/**
	 * Override to manage the save_post
	 * This should be written by all the classes extending this
	 *
	 *
	 * * General Template
	 *
	 * //process here your on $_POST validation and / or option saving
	 *
	 * //lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
	 * wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
	 *
	 *
	 */
	public function save_post( $check_referer = true ) {
		//user permission check
		if ( !current_user_can( $this->capability ) )
			wp_die( __( 'Cheatin&#8217; uh?' ) );
		//check nonce
		if ( $check_referer ) {
			if ( !wp_verify_nonce( $_POST[$this->action_nonce], $this->action_nonce ) )
				wp_die( __( 'Cheatin&#8217; uh?' ) );
		}

		//process here your on $_POST validation and / or option saving

		//lets redirect the post request into get request (you may add additional params at the url, if you need to show save results
		//wp_redirect(add_query_arg(array(), $_POST['_wp_http_referer']));
		//The above should be done by the extending after calling parent::save_post and processing post
	}

	/**
	 * Hook to the load plugin page
	 * This should be overriden
	 * Also call parent::on_load_page() for screenoptions
	 *
	 * @uses add_meta_box
	 */
	public function on_load_page() {

	}

	/**
	 * Get the pagehook of this class
	 *
	 * @return string
	 */
	public function get_pagehook() {
		return $this->pagehook;
	}

	/**
	 * Prints the metaboxes of a custom context
	 * Should atleast pass the $context, others are optional
	 *
	 * The screen defaults to the $this->pagehook so make sure it is set before using
	 * This should be the return value given by add_admin_menu or similar function
	 *
	 * The function automatically checks the screen layout columns and prints the normal/side columns accordingly
	 * If screen layout column is 1 then even if you pass with context side, it will be hidden
	 * Also if screen layout is 1 and you pass with context normal, it will get full width
	 *
	 * @param string  $context           The context of the metaboxes. Depending on this HTML ids are generated. Valid options normal | side
	 * @param string  $container_classes (Optional) The HTML class attribute of the container
	 * @param string  $container_style   (Optional) The RAW inline CSS style of the container
	 */
	public function print_metabox_containers( $context = 'normal', $container_classes = '', $container_style = '' ) {
		global $screen_layout_columns;
		$style = 'width: 50%;';

		//check to see if only one column has to be shown

		if ( isset( $screen_layout_columns ) && $screen_layout_columns == 1 ) {
			//normal?
			if ( 'normal' == $context ) {
				$style = 'width: 100%;';
			} else if ( 'side' == $context ) {
					$style = 'display: none;';
				}
		}

		//override for the special debug area (1 column)
		if ( 'debug' == $context ) {
			$style = 'width: 100%;';
			$container_classes .= ' debug-metabox';
		}
?>
<div class="postbox-container <?php echo $container_classes; ?>" style="<?php echo $style . $container_style; ?>" id="<?php echo ( 'normal' == $context )? 'postbox-container-1' : 'postbox-container-2'; ?>">
	<?php do_meta_boxes( $this->pagehook, $context, '' ); ?>
</div>
		<?php
	}


	/*==========================================================================
	 * INTERNAL METHODS
	 *========================================================================*/

	/**
	 * Prints error msg in WP style
	 *
	 * @param string  $msg
	 */
	protected function print_error( $msg = '', $echo = true ) {
		return $this->ui->msg_error( $msg, $echo );
	}

	protected function print_update( $msg = '', $echo = true ) {
		return $this->ui->msg_update( $msg, $echo );
	}

	protected function print_p_error( $msg = '', $echo = true ) {
		return $this->ui->msg_error( $msg, $echo );
	}

	protected function print_p_update( $msg = '', $echo = true ) {
		return $this->ui->msg_update( $msg, $echo );
	}

	protected function print_p_okay( $msg = '', $echo = true ) {
		return $this->ui->msg_okay( $msg, $echo );
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

	/*==========================================================================
	 * SHORTCUT HTML METHODS
	 *========================================================================*/


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
		return $this->ui->shorten_string( $text, $char, $cont );
	}

	/**
	 * Get the first image from a string
	 *
	 * @param string  $html
	 * @return mixed string|bool The src value on success or boolean false if no src found
	 */
	public function get_first_image( $html ) {
		return $this->ui->get_first_image( $html );
	}

	/**
	 * Wrap a RAW JS inside <script> tag
	 *
	 * @param String  $string The JS
	 * @return String The wrapped JS to be used under HTMl document
	 */
	public function js_wrap( $string ) {
		return $this->ui->js_wrap( $string );
	}

	/**
	 * Wrap a RAW CSS inside <style> tag
	 *
	 * @param String  $string The CSS
	 * @return String The wrapped CSS to be used under HTMl document
	 */
	public function css_wrap( $string ) {
		return $this->ui->css_wrap( $string );
	}

	public function print_datetimepicker( $name, $value, $dateonly = false ) {
		if ( $dateonly ) {
			$this->ui->datepicker( $name, $value );
		} else {
			$this->ui->datetimepicker( $name, $value );
		}
	}

	/**
	 * Prints options of a selectbox
	 *
	 * @param array   $ops Should pass either an array of string ('label1', 'label2') or associative array like array('val' => 'val1', 'label' => 'label1'),...
	 * @param string  $key The key in the haystack, if matched a selected="selected" will be printed
	 */
	public function print_select_op( $ops, $key, $inner = false ) {
		$items = $this->ui->convert_old_items( $ops, $inner );
		$this->ui->select( '', $items, $key, false, false, false, false );
	}

	/**
	 * Prints a set of checkboxes for a single HTML name
	 *
	 * @param string  $name    The HTML name of the checkboxes
	 * @param array   $items   The associative array of items array('val' => 'value', 'label' => 'label'),...
	 * @param array   $checked The array of checked items. It matches with the 'val' of the haystack array
	 * @param string  $sep     (Optional) The seperator, HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
	 */
	public function print_checkboxes( $name, $items, $checked, $sep = '&nbsp;&nbsp;' ) {
		$items = $this->ui->convert_old_items( $items );
		$this->ui->checkboxes( $name, $items, $checked, false, false, $sep );
	}

	/**
	 * Prints a set of radioboxes for a single HTML name
	 *
	 * @param string  $name    The HTML name of the checkboxes
	 * @param array   $items   The associative array of items array('val' => 'value', 'label' => 'label'),...
	 * @param string  $checked The value of checked radiobox. It matches with the val of the haystack
	 * @param string  $sep     (Optional) The seperator, two HTML non-breaking-space (&nbsp;) by default. Can be <br /> or anything
	 */
	public function print_radioboxes( $name, $items, $checked, $sep = '&nbsp;&nbsp;' ) {
		$items = $this->ui->convert_old_items( $items );
		$this->ui->radios( $name, $items, $checked, false, false, $sep );
	}

	/**
	 * Print a single checkbox
	 * Useful for printing a single checkbox like for enable/disable type
	 *
	 * @param string  $name  The HTML name
	 * @param string  $value The value attribute
	 * @param mixed   (string|bool) $checked Can be true or can be equal to the $value for adding checked attribute. Anything else and it will not be added.
	 */
	public function print_checkbox( $name, $value, $checked ) {
		if ( $value === $checked || true === $checked ) {
			$checked = true;
		}
		$this->ui->toggle( $name, '', $value, $checked );
	}

	/**
	 * Prints a input[type="text"]
	 * All attributes are escaped except the value
	 *
	 * @param string  $name  The HTML name attribute
	 * @param string  $value The value of the textbox
	 * @param string  $class (Optional) The css class defaults to regular-text
	 */
	public function print_input_text( $name, $value, $class = 'regular-text' ) {
		$this->ui->text( $name, $value, '', $class );
	}

	/**
	 * Prints a <textarea> with custom attributes
	 * All attributes are escaped except the value
	 *
	 * @param string  $name  The HTML name attribute
	 * @param string  $value The value of the textbox
	 * @param string  $class (Optional) The css class defaults to regular-text
	 * @param int     $rows  (Optional) The number of rows in the rows attribute
	 * @param int     $cols  (Optional) The number of columns in the cols attribute
	 */
	public function print_textarea( $name, $value, $class = 'regular-text', $rows = 3, $cols = 20 ) {
		$this->ui->textarea( $name, $value, '', $class );
	}


	/**
	 * Displays a jQuery UI Slider to the page
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param int     $value The initial/saved value of the input box
	 * @param int     $max   The maximum of the range
	 * @param int     $min   The minimum of the range
	 * @param int     $step  The step value
	 */
	public function print_ui_slider( $name, $value, $max = 100, $min = 0, $step = 1 ) {
		$this->ui->slider( $name, $value, $min, $max, $step );
	}

	/**
	 * Prints a ColorPicker
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param string  $value The HEX color code
	 */
	public function print_cpicker( $name, $value ) {
		$this->ui->colorpicker( $name, $value );
	}

	/**
	 * Prints a input box with an attached upload button
	 *
	 * @param string  $name  The HTML name of the input box
	 * @param string  $value The value of the input box
	 */
	public function print_uploadbutton( $name, $value ) {
		$this->ui->upload( $name, $value );
	}
}

/*==============================================================================
 * List Tables
 *============================================================================*/
/**
 * Get the WP_List_Table for populating our table
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * View all Forms Data Table Class
 */
class IPT_FSQM_Form_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
				'singular' => 'ipt_fsqm_form_item',
				'plural' => 'ipt_fsqm_form_items',
				'ajax' => false,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" /><label for="cb-select-all-1"></label>',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'shortcode' => __( 'Shortcode', 'ipt_fsqm' ),
			'submission' => __( 'Submissions', 'ipt_fsqm' ),
			'category' => __( 'Category', 'ipt_fsqm' ),
			'updated' => __( 'Last Updated', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'f.name', false ),
			'submission' => array( 'sub', true ),
			'category' => array( 'c.name', false ),
			'updated' => array( 'f.updated', true ),
		);

		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'title' :
			$permalinks = IPT_FSQM_Form_Elements_Static::standalone_permalink_parts( $item['id'] );
			$actions = array(
				'form_id' => sprintf( __( 'ID: %d', 'ipt_fsqm' ), $item['id'] ),
				'permalink' => sprintf( '<a class="view" title="%3$s" href="%1$s" target="_blank">%2$s</a>', $permalinks['url'], __( 'Preview', 'ipt_fsqm' ), __( 'Preview the form or copy the permalink', 'ipt_fsqm' ) ),
				'view'      => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=%d">%s</a>', $item['id'], __( 'View Submissions', 'ipt_fsqm' ) ),
				'download' => sprintf( '<a class="view" href="%1$s" title="%3$s" target="_blank">%2$s</a>', wp_nonce_url( admin_url( 'admin-ajax.php?action=ipt_fsqm_submission_download&form_id=' . $item['id'] ), 'ipt_fsqm_submission_download_' . $item['id'] ), __( 'Export Submissions', 'ipt_fsqm' ), esc_attr__( 'Export all submissions in a CSV file', 'ipt_fsqm' ) ),
				'edit'      => sprintf( '<a class="edit" href="admin.php?page=ipt_fsqm_all_forms&action=edit&form_id=%d">%s</a>', $item['id'], __( 'Edit', 'ipt_fsqm' ) ),
				'copy'      => sprintf( '<a class="copy" href="%s">%s</a>', wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=copy&id=' . $item['id'], 'ipt_fsqm_form_copy_' . $item['id'] ), __( 'Copy', 'ipt_fsqm' ) ),
				'delete'    => sprintf( '<a class="delete" href="%s">%s</a>', wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_form_delete_' . $item['id'] ), __( 'Delete', 'ipt_fsqm' ) ),
			);
			return sprintf( '%1$s %2$s', '<strong><a title="' . __( 'View all submissions under this form', 'ipt_fsqm' ) . '" href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['id'] . '">' . $item['name'] . '</a></strong>' , $this->row_actions( apply_filters( 'ipt_fsqm_all_forms_row_action', $actions ) ) );
			break;
		case 'shortcode' :
			return '[ipt_fsqm_form id="' . $item['id'] . '"]';
			break;
		case 'submission' :
			return $item['sub'];
			break;
		case 'category' :
			if ( $item['category'] == 0 ) {
				return __( 'Unassigned', 'ipt_fsqm' );
			} else {
				return $item['catname'];
			}
			break;
		case 'updated' :
			if ( 0 == $item['sub'] )
				return __( 'N/A', 'ipt_fsqm' );
			else
				return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
			break;
		default :
			print_r( $item );
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="forms[]" id="eform-forms_%1$s" value="%1$s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global array $ipt_fsqm_info
	 */
	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		//prepare our query
		$query = "SELECT f.id id, f.name name, f.updated updated, f.category category, COUNT(d.id) sub, c.name catname FROM {$ipt_fsqm_info['form_table']} f LEFT JOIN {$ipt_fsqm_info['data_table']} d ON f.id = d.form_id LEFT JOIN {$ipt_fsqm_info['category_table']} c ON f.category = c.id";
		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'f.id';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$wheres = array();
		$where = '';

		if ( ! empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "f.name LIKE %s", $search );
		}

		if ( isset( $_GET['cat_id'] ) && $_GET['cat_id'] !== '' ) {
			$wheres[] = $wpdb->prepare( "f.category = %d", $_GET['cat_id'] );
		}

		if ( ! empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_info['form_table']} f{$where}" );
		$perpage = $this->get_items_per_page( 'feedback_forms_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' GROUP BY f.id ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function extra_tablenav( $which ) {
		$form_categories = array(
			array(
				'value' => '0',
				'label' => __( 'Unassigned Forms', 'ipt_fsqm' ),
			),
		);
		$db_categories = IPT_FSQM_Form_Elements_Static::get_all_categories();
		if ( null != $db_categories ) {
			foreach ( $db_categories as $dbc ) {
				$form_categories[] = array(
					'value' => $dbc->id,
					'label' => $dbc->name,
				);
			}
		}
		switch ( $which ) {
			case 'top' :
			?>
<div class="alignleft actions">
	<select name="cat_id" id="cat_id">
		<option value=""<?php if ( !isset( $_GET['cat_id'] ) || '' == $_GET['cat_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show forms from all categories', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $form_categories as $form_cat ) : ?>
		<option value="<?php echo $form_cat['value']; ?>"<?php if ( isset( $_GET['cat_id'] ) && (string) $form_cat['value'] == $_GET['cat_id'] ) echo ' selected="selected"' ?>><?php echo $form_cat['label']; ?></option>
		<?php endforeach; ?>
	</select>
	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-cat-query-submit' ) ); ?>
</div>
			<?php
				break;

			case 'bottom' :
				if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
					?>
<div class="actions alignleft">
	<?php printf( __( 'Showing search results for "%s"', 'ipt_fsqm' ), $_GET['s'] ); ?>
</div>
					<?php
				}
				break;
		}
	}
}

/**
 * View all Submission Data Table Class
 */
class IPT_FSQM_Data_Table extends WP_List_Table {
	public $feedback;

	public function __construct() {
		$this->feedback = get_option( 'ipt_fsqm_feedback' );

		parent::__construct( array(
				'singular' => 'ipt_fsqm_table_item',
				'plural' => 'ipt_fsqm_table_items',
				'ajax' => true,
			) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'phone' => __( 'Phone', 'ipt_fsqm' ),
			'date' => __( 'Date', 'ipt_fsqm' ),
			'ip' => __( 'IP Address', 'ipt_fsqm' ),
			'score' => __( 'Score', 'ipt_fsqm' ),
			'user' => __( 'Account', 'ipt_fsqm' ),
			'form' => __( 'Form', 'ipt_fsqm' ),
			'category' => __( 'Category', 'ipt_fsqm' ),
			'track' => __( 'URL Track', 'ipt_fsqm' ),
			'referer' => __( 'Referer', 'ipt_fsqm' ),
			'time' => __( 'Time', 'ipt_fsqm' ),
			'star' => __( 'Star', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'd.f_name', false ),
			'date' => array( 'd.date', true ),
			'email' => array( 'd.email', false ),
			'phone' => array( 'd.phone', false ),
			'score' => array( 'd.score', true ),
			'user' => array( 'd.user_id', true ),
			'ip' => array( 'd.ip', false ),
			'form' => array( 'd.form_id', false ),
			'category' => array( 'c.name', false ),
			'track' => array( 'd.url_track', false ),
			'referer' => array( 'd.referer', false ),
			'time' => array( 'd.time', true ),
			'star' => array( 'd.star', true ),
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
		case 'star' :
			return '<a href="javascript: void(null)" class="ipt_fsqm_star"><img title="' . ( $item['star'] == 1 ? __( 'Click to Unstar', 'ipt_fsqm' ) : __( 'Click to Star', 'ipt_fsqm' ) ) . '" src="' . plugins_url( ( $item['star'] == 1 ? '/static/admin/images/star_on.png' : '/static/admin/images/star_off.png' ), IPT_FSQM_Loader::$abs_file ) . '" /></a>';
		case 'title' :
			$actions = array(
				'data_id' => sprintf( __( 'ID: %d', 'ipt_fsqm' ), $item['id'] ),
				'qview' => sprintf( '<a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=%d&width=640&height=500">%s</a>', esc_attr( sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['name'] ) ), $item['id'], __( 'Quick Preview', 'ipt_fsqm' ) ),
				'view' => sprintf( '<a href="admin.php?page=ipt_fsqm_view_submission&id=%d">%s</a>', (int) $item['id'], __( 'Full View', 'ipt_fsqm' ) ),
				'edit' => '<a class="edit" href="admin.php?page=ipt_fsqm_view_submission&id=' . $item['id'] . '&edit=Edit">' . __( 'Edit Submission', 'ipt_fsqm' ) . '</a>',
				'delete' => '<a class="delete" href="' . wp_nonce_url( '?page=' . $_REQUEST['page'] . '&action=delete&id=' . $item['id'], 'ipt_fsqm_delete_' . $item['id'] ) . '">' . __( 'Delete', 'ipt_fsqm' ) . '</a>',
			);

			return sprintf( '%1$s %2$s', '<strong><a class="thickbox" title="' . esc_attr( sprintf( __( 'Submission of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['name'] ) ) . '" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['id'] . '&width=640&height=500">' . $item['f_name'] . ' ' . $item['l_name'] . '</a></strong>', $this->row_actions( apply_filters( 'ipt_fsqm_all_data_row_action', $actions, $item ) ) );
			break;
		case 'email' :
			if ( trim( $item['email'] ) !== '' ) {
				return '<a href="mailto:' . $item[$column_name] . '">' . $item[$column_name] . '</a>';
			} else {
				return __( 'Unknown', 'ipt_fsqm' );
			}
			break;
		case 'phone' :
		case 'ip' :
			return $item[$column_name];
			break;
		case 'date' :
			return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
			break;
		case 'form' :
			return '<a href="admin.php?page=ipt_fsqm_view_all_submissions&form_id=' . $item['form_id'] . '">' . $item['name'] . '</a> <code>' . $item['form_id'] . '</code>';
			break;
		case 'category' :
			return ( $item['catname'] == '' ? __( 'Unassigned', 'ipt_fsqm' ) : $item['catname'] );
			break;
		case 'score' :
			$score = __( 'N/A', 'ipt_fsqm' );
			if ( $item['max_score'] != 0 ) {
				$percent = number_format_i18n( $item['score'] * 100 / $item['max_score'], 2 );
				$score = $item['score'] . '/' . $item['max_score'] . ' <code>(' . $percent . '%)</code>';
			}
			return $score;
			break;
		case 'user' :
			$return = __( 'Guest', 'ipt_fsqm' );
			if ( $item['user_id'] != 0 ) {
				$user = get_user_by( 'id', $item['user_id'] );
				if ( $user instanceof WP_User ) {
					$return = '<a title="' . __( 'Edit user', 'ipt_fsqm' ) . '" href="user-edit.php?user_id=' . $user->ID . '">' . $user->display_name . '</a>';
				}
			}
			return $return;
			break;
		case 'track' :
			if ( $item['track'] == '' ) {
				return __( 'Unknown', 'ipt_fsqm' );
			} else {
				return '<a href="' . esc_attr( 'admin.php?page=ipt_fsqm_view_all_submissions&track_id=' . $item['track'] ) . '">' . $item['track'] . '</a>';
			}
			break;
		case 'referer' :
			if ( $item['referer'] == '' ) {
				return __( 'Unknown', 'ipt_fsqm' );
			} else {
				return '<a href="' . esc_attr( 'admin.php?page=ipt_fsqm_view_all_submissions&referer=' . $item['referer'] ) . '">' . $item['referer'] . '</a>';
			}
			break;
		case 'time' :
			if ( $item['time'] <= 0 ) {
				return __( 'N/A', 'ipt_fsqm' );
			} else {
				return $this->seconds_to_words( $item['time'] );
			}
			break;
		default :
			return print_r( $item[$column_name], true );
		}
	}

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

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="feedbacks[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
			'star' => __( 'Mark Starred', 'ipt_fsqm' ),
			'unstar' => __( 'Mark Unstarred', 'ipt_fsqm' ),
		);
		return $actions;
	}

	/**
	 *
	 *
	 * @global wpdb $wpdb
	 * @global type $_wp_column_headers
	 * @global type $ipt_fsqm_info
	 */
	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		//prepare our query
		$query = "SELECT d.id id, d.f_name f_name, d.l_name l_name, d.email email, d.phone phone, d.ip ip, d.date date, d.star star, d.score score, d.max_score max_score, d.user_id user_id, d.url_track track, d.referer referer, d.time time, f.name name, f.id form_id, c.name catname FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id LEFT JOIN {$ipt_fsqm_info['category_table']} c ON f.category = c.id";
		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'date';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$where = '';
		$wheres = array();

		if ( isset( $_GET['form_id'] ) && !empty( $_GET['form_id'] ) ) {
			$wheres[] = $wpdb->prepare( "d.form_id = %d", $_GET['form_id'] );
		}
		if ( isset( $_GET['user_id'] ) && '' != $_GET['user_id'] ) {
			$wheres[] = $wpdb->prepare( "d.user_id = %d", $_GET['user_id'] );
		}
		if ( isset( $_GET['cat_id'] ) && $_GET['cat_id'] !== '' ) {
			$wheres[] = $wpdb->prepare( "f.category = %d", $_GET['cat_id'] );
		}
		if ( isset( $_GET['track_id'] ) && $_GET['track_id'] !== '' ) {
			$wheres[] = $wpdb->prepare( "d.url_track = %s", stripslashes( $_GET['track_id'] ) );
		}
		if ( isset( $_GET['referer'] ) && $_GET['referer'] !== '' ) {
			$wheres[] = $wpdb->prepare( "d.referer = %s", stripslashes( $_GET['referer'] ) );
		}

		if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "(f_name LIKE %s OR l_name LIKE %s OR email LIKE %s OR phone LIKE %s OR ip LIKE %s)", $search, $search, $search, $search, $search );
		}

		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		//pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(d.id) FROM {$ipt_fsqm_info['data_table']} d LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id{$where}" ); // d is alias for data_table which is used in where clause
		$perpage = $this->get_items_per_page( 'feedbacks_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;
		//print_r($query);

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );

		//var_dump($this->items);
	}

	public function no_items() {
		_e( 'No Feedbacks/Surveys/Quiz Results yet! Please be patient.', 'ipt_fsqm' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;
		$forms = $wpdb->get_results( "SELECT id, name FROM {$ipt_fsqm_info['form_table']} ORDER BY id DESC" );
		$users = $wpdb->get_col( "SELECT distinct user_id FROM {$ipt_fsqm_info['data_table']}" );
		$form_categories = array(
			array(
				'value' => '0',
				'label' => __( 'Unassigned Forms', 'ipt_fsqm' ),
			),
		);
		$db_categories = IPT_FSQM_Form_Elements_Static::get_all_categories();
		if ( null != $db_categories ) {
			foreach ( $db_categories as $dbc ) {
				$form_categories[] = array(
					'value' => $dbc->id,
					'label' => $dbc->name,
				);
			}
		}
		$tracks = $wpdb->get_col( "SELECT distinct url_track FROM {$ipt_fsqm_info['data_table']} WHERE url_track != ''" );
		$referers = $wpdb->get_col( "SELECT distinct referer FROM {$ipt_fsqm_info['data_table']} WHERE referer != ''" );
		switch ( $which ) {
		case 'top' :
?>
<div class="alignleft actions">
	<select name="form_id">
		<option value=""<?php if ( !isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?></option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the database', 'ipt_fsqm' ); ?></option>
		<?php endif; ?>
	</select>

	<select name="user_id">
		<option value=""<?php if ( !isset( $_GET['user_id'] ) || '' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all users', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $users ) : ?>
		<?php foreach ( $users as $user_id ) : ?>
		<?php if ( $user_id == 0 ) : ?>
		<option value="0"<?php if ( isset( $_GET['user_id'] ) && '0' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Guests (Unregistered)', 'ipt_fsqm' ); ?></option>
		<?php else : ?>
		<?php $user = get_user_by( 'id', $user_id ); ?>
		<?php
		if ( ! $user ) {
			$user = new stdClass();
			$user->display_name = __( 'Deleted User', 'ipt_fsqm' ) . ' (' . $user_id . ')';
		}
		?>
		<option value="<?php echo $user_id; ?>"<?php if ( isset( $_GET['user_id'] ) && (string) $user_id == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php echo $user->display_name; ?></option>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</select>

	<select name="cat_id" id="cat_id">
		<option value=""<?php if ( !isset( $_GET['cat_id'] ) || '' == $_GET['cat_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show forms from all categories', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $form_categories as $form_cat ) : ?>
		<option value="<?php echo $form_cat['value']; ?>"<?php if ( isset( $_GET['cat_id'] ) && (string) $form_cat['value'] == $_GET['cat_id'] ) echo ' selected="selected"' ?>><?php echo $form_cat['label']; ?></option>
		<?php endforeach; ?>
	</select>

	<select name="track_id" id="track_id">
		<option value=""<?php if ( !isset( $_GET['track_id'] ) || '' == $_GET['track_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all URL Tracks', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $tracks as $track ) : ?>
		<option value="<?php echo esc_attr( $track ); ?>"<?php if ( isset( $_GET['track_id'] ) && (string) $track == $_GET['track_id'] ) echo ' selected="selected"'; ?>><?php echo $track; ?></option>
		<?php endforeach; ?>
	</select>

	<select id="referer" name="referer">
		<option value=""<?php if ( !isset( $_GET['referer'] ) || '' == $_GET['referer'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all Referers', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $referers as $referer ) : ?>
		<option value="<?php echo esc_attr( $referer ); ?>"<?php if ( isset( $_GET['referer'] ) && (string) $referer == $_GET['referer'] ) echo ' selected="selected"'; ?>><?php echo $referer; ?></option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
				<?php
			break;
		case 'bottom' :
			echo '<div class="alignleft"><p>';
			_e( 'You can also print a submission. Just select Quick Preview from the list and click on the print button.', 'ipt_fsqm' );
			echo '</p></div>';
		}
	}
}

class IPT_FSQM_Category_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
			'singular' => 'ipt_fsqm_category_item',
			'plural' => 'ipt_fsqm_category_items',
			'ajax' => false,
		) );
	}

	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __( 'Name', 'ipt_fsqm' ),
			'description' => __( 'Description', 'ipt_fsqm' ),
			'forms' => __( 'Total Forms', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => 'name',
			'forms' => 'forms',
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title' :
				$actions = array(
					'category_id' => sprintf( __( 'ID: %d', 'ipt_fsqm' ), $item['id'] ),
					'view_form' => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_all_forms&cat_id=%2$d">%1$s</a>', __( 'View Forms', 'ipt_fsqm' ), $item['id'] ),
					'view_submission' => sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_view_all_submissions&cat_id=%2$d">%1$s</a>', __( 'View Submissions', 'ipt_fsqm' ), $item['id'] ),
					'edit' => sprintf( '<a class="edit" href="admin.php?page=ipt_fsqm_form_category&paction=edit_cat&cat_id=%2$d">%1$s</a>', __( 'Edit', 'ipt_fsqm' ), $item['id'] ),
					'delete' => sprintf( '<a class="edit" href="' . wp_nonce_url( 'admin.php?page=ipt_fsqm_form_category&action=delete&cat_id=' . $item['id'], 'ipt_fsqm_category_delete_' . $item['id'], '_wpnonce' ) . '">%1$s</a>', __( 'Delete', 'ipt_fsqm' ) ),
				);
				return sprintf( '%1$s %2$s', '<strong><a title="' . __( 'Edit Category', 'ipt_fsqm' ) . '" href="ipt_fsqm_form_category&paction=edit_cat&cat_id=' . $item['id'] . '">' . $item['name'] . '</a></strong>', $this->row_actions( $actions ) );
				break;

			case 'forms' :
				return sprintf( '<a class="view" href="admin.php?page=ipt_fsqm_all_forms&cat_id=%1$d">%2$s</a>', $item['id'], $item['forms'] );
				break;

			case 'description' :
				return htmlspecialchars( $item['description'] );
				break;
			default :
				print_r( $item );
				break;
		}
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="cat_ids[]" value="%s" />', $item['id'] );
	}

	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete' ),
		);
		return $actions;
	}

	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		// Prepare our query
		$query = "SELECT c.id id, c.name name, c.description description, COUNT( f.id ) forms FROM {$ipt_fsqm_info['category_table']} c LEFT JOIN {$ipt_fsqm_info['form_table']} f ON c.id = f.category GROUP BY c.id";
		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'c.id';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';

		$where = '';

		if ( !empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';

			$where = $wpdb->prepare( " WHERE name LIKE %s", $search );
		}

		$query .= $where;

		// Pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(id) FROM {$ipt_fsqm_info['category_table']}{$where}" );
		$perpage = $this->get_items_per_page( 'fsqm_category_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
			'total_items' => $totalitems,
			'total_pages' => $totalpages,
			'per_page' => $perpage,
		) );
		$current_page = $this->get_pagenum();

		// Put pagination and order on the query
		$query .= ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;

		// register the columns
		$this->_column_headers = $this->get_column_info();

		// fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function extra_tablenav( $which ) {
		if ( isset( $_GET['s'] ) && !empty( $_GET['s'] ) && 'top' == $which ) {
?>
<div class="actions alignleft">
	<?php printf( __( 'Showing search results for "%s"', 'ipt_fsqm' ), $_GET['s'] ); ?>
</div>
			<?php
		}
	}

	public function no_items() {
		_e( 'You have not created any category yet. Please click on the <strong>Add New</strong> button to get started.', 'ipt_fsqm' );
	}
}

class IPT_FSQM_Payments_Table extends WP_List_Table {
	public function __construct() {
		parent::__construct( array(
			'singular' => 'ipt_fsqm_payment_item',
			'plural' => 'ipt_fsqm_payment_items',
			'ajax' => true,
		) );
	}

	public function get_columns() {
		$columns = array(
			'title' => __( 'Name', 'ipt_fsqm' ),
			'txn' => __( 'Transaction ID', 'ipt_fsqm' ),
			'user_id' => __( 'User', 'ipt_fsqm' ),
			'email' => __( 'Email', 'ipt_fsqm' ),
			'date' => __( 'Date', 'ipt_fsqm' ),
			'amount' => __( 'Amount', 'ipt_fsqm' ),
			'currency' => __( 'Currency', 'ipt_fsqm' ),
			'mode' => __( 'Gateway', 'ipt_fsqm' ),
			'status' => __( 'Status', 'ipt_fsqm' ),
			'form' => __( 'Form', 'ipt_fsqm' ),
		);
		return $columns;
	}

	public function get_sortable_columns() {
		$sortable = array(
			'title' => array( 'd.fname', false ),
			'date' => array( 'p.date', true ),
			'user_id' => array( 'p.user_id', false ),
			'email' => array( 'd.email', false ),
			'amount' => array( 'p.amount', true ),
			'mode' => array( 'p.mode', false ),
			'status' => array( 'p.status', true ),
			'currency' => array( 'p.currency', false ),
			'form' => array( 'f.id', false ),
		);
		return $sortable;
	}

	public function column_default( $item, $column_name ) {
		$payment_methods = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_gateways();
		$payment_status = IPT_FSQM_Form_Elements_Static::ipt_fsqm_get_payment_status();
		switch ( $column_name ) {
			case 'title':
				$actions = array(
					'qview' => sprintf( '<a class="thickbox" title="%s" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=%d&width=640&height=500">%s</a>', esc_attr( sprintf( __( 'Payment of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['formname'] ) ), $item['data_id'], __( 'Quick Preview', 'ipt_fsqm' ) ),
					'view' => sprintf( '<a href="admin.php?page=ipt_fsqm_view_submission&id=%d">%s</a>', (int) $item['data_id'], __( 'Full View', 'ipt_fsqm' ) ),
				);

				return sprintf( '%1$s %2$s', '<strong><a class="thickbox" title="' . esc_attr( sprintf( __( 'Payment of %s under %s', 'ipt_fsqm' ), $item['f_name'] . ' ' . $item['l_name'], $item['formname'] ) ) . '" href="admin-ajax.php?action=ipt_fsqm_quick_preview&id=' . $item['data_id'] . '&width=640&height=500">' . $item['f_name'] . ' ' . $item['l_name'] . '</a></strong>', $this->row_actions( $actions ) );
				break;
			case 'txn' :
				return $item[$column_name];
				break;
			case 'user_id' :
				$return = __( 'Guest', 'ipt_fsqm' );
				if ( $item['user_id'] != 0 ) {
					$user = get_user_by( 'id', $item['user_id'] );
					if ( $user instanceof WP_User ) {
						$return = '<a title="' . __( 'Edit user', 'ipt_fsqm' ) . '" href="user-edit.php?user_id=' . $user->ID . '">' . $user->display_name . '</a>';
					}
				}
				return $return;
				break;
			case 'email' :
				if ( trim( $item['email'] ) !== '' ) {
					return '<a href="mailto:' . $item[$column_name] . '">' . $item[$column_name] . '</a>';
				} else {
					return __( 'Unknown', 'ipt_fsqm' );
				}
				break;
			case 'date' :
				return date_i18n( get_option( 'date_format' ) . __(' \a\t ', 'ipt_fsqm') . get_option( 'time_format' ), strtotime( $item[$column_name] ) );
				break;
			case 'amount' :
				return $item[$column_name];
				break;
			case 'currency' :
				return $item[$column_name];
				break;
			case 'mode' :
				if ( $item['mode'] == '' ) {
					return __( 'N/A', 'ipt_fsqm' );
				}
				return '<a href="admin.php?page=ipt_fsqm_payments&pmethod=' . $item['mode'] . '">' . $payment_methods[$item[$column_name]] . '</a>';
				break;
			case 'status' :
				return '<a href="admin.php?page=ipt_fsqm_payments&pstatus=' . $item['status'] . '">' . $payment_status[$item[$column_name]] . '</a>';
				break;
			case 'form' :
				return '<a href="admin.php?page=ipt_fsqm_payments&form_id=' . $item['form_id'] . '">' . $item['formname'] . '</a>';
			break;
			default:
				print_r( $item[$column_name] );
				break;
		}
	}

	public function prepare_items() {
		global $wpdb, $ipt_fsqm_info;

		// prepare the query
		$query = "SELECT p.id id, p.txn txn, p.amount amount, p.status status, p.currency currency, p.date date, p.user_id user_id, p.mode mode, p.data_id data_id, d.f_name f_name, d.l_name l_name, d.email email, f.name formname, f.id form_id FROM {$ipt_fsqm_info['payment_table']} p LEFT JOIN {$ipt_fsqm_info['data_table']} d ON p.data_id = d.id LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id";

		$orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'date';
		$order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'desc';
		$where = '';
		$wheres = array();

		if ( isset( $_GET['form_id'] ) && !empty( $_GET['form_id'] ) ) {
			$wheres[] = $wpdb->prepare( "d.form_id = %d", $_GET['form_id'] );
		}
		if ( isset( $_GET['user_id'] ) && '' != $_GET['user_id'] ) {
			$wheres[] = $wpdb->prepare( "d.user_id = %d", $_GET['user_id'] );
		}
		if ( isset( $_GET['pmethod'] ) && '' != $_GET['pmethod'] ) {
			$wheres[] = $wpdb->prepare( "p.mode = %s", $_GET['pmethod'] );
		}
		if ( isset( $_GET['pstatus'] ) && '' != $_GET['pstatus'] ) {
			$wheres[] = $wpdb->prepare( "p.status = %s", $_GET['pstatus'] );
		}

		if ( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) ) {
			$search = '%' . $_GET['s'] . '%';
			$wheres[] = $wpdb->prepare( "p.txn LIKE %s", $search );
		}
		if ( !empty( $wheres ) ) {
			$where .= ' WHERE ' . implode( ' AND ', $wheres );
		}

		$query .= $where;

		// Pagination
		$totalitems = $wpdb->get_var( "SELECT COUNT(p.id) FROM {$ipt_fsqm_info['payment_table']} p LEFT JOIN {$ipt_fsqm_info['data_table']} d ON p.data_id = d.id{$where}" );
		$perpage = $this->get_items_per_page( 'fsqm_payment_per_page', 20 );
		$totalpages = ceil( $totalitems/$perpage );

		$this->set_pagination_args( array(
				'total_items' => $totalitems,
				'total_pages' => $totalpages,
				'per_page' => $perpage,
			) );
		$current_page = $this->get_pagenum();

		//put pagination and order on the query
		$query .= ' ORDER BY ' . $orderby . ' ' . $order . ' LIMIT ' . ( ( $current_page - 1 ) * $perpage ) . ',' . (int) $perpage;
		//print_r($query);

		//register the columns
		$this->_column_headers = $this->get_column_info();

		//fetch the items
		$this->items = $wpdb->get_results( $query, ARRAY_A );
	}

	public function no_items() {
		_e( 'No payments yet.', 'ipt_fsqm' );
	}

	public function extra_tablenav( $which ) {
		global $wpdb, $ipt_fsqm_info;

		// Get filter by forms
		$forms = $wpdb->get_results( "SELECT f.id id, f.name name, COUNT(p.id) ptotal FROM {$ipt_fsqm_info['payment_table']} p LEFT JOIN {$ipt_fsqm_info['data_table']} d ON p.data_id = d.id LEFT JOIN {$ipt_fsqm_info['form_table']} f ON d.form_id = f.id GROUP BY f.id ORDER BY f.id DESC" );

		// Filter by users
		$users = $wpdb->get_col( "SELECT distinct user_id FROM {$ipt_fsqm_info['payment_table']}" );

		// Filter by payment methods
		$payment_methods = array(
			'paypal_d' => __( 'Direct Payout from PayPal', 'ipt_fsqm' ),
			'paypal_e' => __( 'PayPal Express Checkout', 'ipt_fsqm' ),
			'stripe' => __( 'Direct Payout from Stripe', 'ipt_fsqm' ),
		);

		// Filter by status
		$payment_status = array(
			0 => __( 'Unpaid', 'ipt_fsqm' ),
			1 => __( 'Paid', 'ipt_fsqm' ),
			2 => __( 'Cancelled', 'ipt_fsqm' ),
			3 => __( 'Unsuccessful', 'ipt_fsqm' ),
		);

		switch ( $which ) {
			case 'top' :
?>
<div class="alignleft actions" style="margin-left: -9px;">
	<select name="form_id">
		<option value=""<?php if ( ! isset( $_GET['form_id'] ) || empty( $_GET['form_id'] ) ) echo ' selected="selected"'; ?>><?php _e( 'Show all forms', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $forms ) : ?>
		<?php foreach ( $forms as $form ) : ?>
		<option value="<?php echo $form->id; ?>"<?php if ( isset( $_GET['form_id'] ) && $_GET['form_id'] == $form->id ) echo ' selected="selected"'; ?>><?php echo $form->name; ?> (<?php echo $form->ptotal; ?>)</option>
		<?php endforeach; ?>
		<?php else : ?>
		<option value=""><?php _e( 'No Forms in the payments database', 'ipt_fsqm' ); ?></option>
		<?php endif; ?>
	</select>

	<select name="user_id">
		<option value=""<?php if ( ! isset( $_GET['user_id'] ) || '' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Show all users', 'ipt_fsqm' ); ?></option>
		<?php if ( null != $users ) : ?>
		<?php foreach ( $users as $user_id ) : ?>
		<?php if ( $user_id == 0 ) : ?>
		<option value="0"<?php if ( isset( $_GET['user_id'] ) && '0' == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php _e( 'Guests (Unregistered)', 'ipt_fsqm' ); ?></option>
		<?php else : ?>
		<?php $user = get_user_by( 'id', $user_id ); ?>
		<option value="<?php echo $user_id; ?>"<?php if ( isset( $_GET['user_id'] ) && (string) $user_id == $_GET['user_id'] ) echo ' selected="selected"'; ?>><?php echo $user->display_name; ?></option>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>
	</select>

	<select name="pmethod" id="pmethod">
		<option value=""<?php if ( ! isset( $_GET['pmethod'] ) || '' === $_GET['pmethod'] ) echo ' selected="selected"'; ?>><?php _e( 'All payment methods', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $payment_methods as $pm_key => $pm_val ) : ?>
		<option value="<?php echo esc_attr( $pm_key ); ?>" <?php selected( @$_GET['pmethod'], $pm_key, true ); ?>><?php echo $pm_val; ?></option>
		<?php endforeach; ?>
	</select>

	<select name="pstatus" id="pstatus">
		<option value=""<?php if ( ! isset( $_GET['pstatus'] ) || '' === $_GET['pstatus'] ) echo ' selected="selected"'; ?>><?php _e( 'Transaction Status', 'ipt_fsqm' ); ?></option>
		<?php foreach ( $payment_status as $ps_key => $ps_val ) : ?>
		<option value="<?php echo esc_attr( $ps_key ); ?>" <?php selected( @$_GET['pstatus'], $ps_key, true ); ?>><?php echo $ps_val; ?></option>
		<?php endforeach; ?>
	</select>

	<?php submit_button( __( 'Filter' ), 'secondary', false, false, array( 'id' => 'form-query-submit' ) ); ?>
</div>
<?php
				break;
		}
	}
}
