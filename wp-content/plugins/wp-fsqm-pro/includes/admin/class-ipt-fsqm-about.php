<?php
/**
 * IPT FSQM About
 *
 * Class for handling the About & Add-ons page under eForm
 *
 * @author Swashata <swashata4u@gmail.com>
 * @package eForm - WordPress Form Builder
 * @subpackage Admin\About
 * @codeCoverageIgnore
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
	.col iframe {
		max-width: 100%;
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
			<img src="<?php echo plugins_url( '/static/admin/images/eform-preview.png', IPT_FSQM_Loader::$abs_file ); ?>" />
		</p>
		<h2><?php printf( __( '%2$s<sup style="font-size: 0.5em;">v%1$s</sup> - WordPress Form Builder', 'ipt_fsqm' ), IPT_FSQM_Loader::$version, '<i class="ipt-fsqmic-eform-horizontal"></i>' ); ?></h2>
		<p>A few months back, with the release of v3.5, we introduced a new and modern interface to our system. With v4.0, we have introduced the modernism not just to the product, but also to our workflow.</p>
		<p>Version 4.0 has seen some massive changes in the codebase. The result is obviously faster eForms along with better dependency management. But you don't have to think about all that, because now <a href="https://wpq-develop.wpquark.xyz/wp-fsqm-pro/">our robots</a> do. The results are, faster shipping of new features, automatic product updates (for you, i.e) and better compatibility with newer dependencies.</p>
		<p>We have focused on a very few specific things while bringing new features to this release. We have seen that more and more people are relying on a simple payment form without the hassle of using complex eCommerce system. So we have introduced:</p>
		<ul class="ul-disc">
			<li><strong>Authorize.net</strong> Payment Gateway.</li>
			<li>Better <strong>Stripe &amp; Paypal</strong> support.</li>
			<li><strong>Estimation Slider</strong> for quickly showing your client the cost breakdown.</li>
			<li><strong>Pricing Table</strong> form element to better convert your sales.</li>
		</ul>
		<p>Apart from all these, we have also introduced interactive form elements, better icons, input masking, automatic scoring for feedback elements and many more. Read more about it in our blog.</p>
		<p><a href="https://wp.me/p8gFVp-dl" class="button button-primary">Read More</a></p>
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
		<p><strong>Version 4.0.0</strong> Focused improvement on payment features.</p>
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
				<h3>Better Payment System</h3>
				<p>eForm Version 4.0 comes with a better payment system. The payment form has been simplified and now includes zip and address fields for direct credit card payments.</p>
				<p>Stripe and PayPal both have been rewritten using latest SDK. Now you will need to enter <strong>Stripe Publisahable Key</strong> in the form settings and as per Stripe recommendation, the CC fields won't touch your server. So you can worry less about PCI compliance when paying through Stripe.</p>
				<p>Authorize.net has also been integrated as a gateway for eForm. It will accept Credit Card as payment handler.</p>
				<p>To complement the changes, we have introduced a new element, <strong>Pricing Table</strong> and a new interface <strong>Estimation Slider</strong>.</p>
				<p>You can about them in our knowledgebase.</p>
				<p><a href="https://wpquark.com/kb/fsqm/payment-system/" class="button button-secondary">Read More</a></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/D9LF9hsl8bQ" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3>Interactive Form Elements</h3>
				<p>All modern forms should support interactive elements. Meaning, the value of one form element, changes the heading, label or description of another element.</p>
				<p>Up until now, eForm didn't support this. But no more.</p>
				<p>Interactivity has been added to almost all elements. You can simply put template tags like <code>%M0%</code> to show the value of MCQ element with key 0 anywhere in your form. It works for element title, subtitle, option labels and almost everywhere.</p>
				<p>To learn how to activate, read our knowledgebase.</p>
				<p><a href="https://wpquark.com/kb/fsqm/fsqm-form/interactive-form-piping-element-value-labels/" class="button button-secondary">Read More</a></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/_MRcFH6gFVI" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
		<hr />
		<div class="feature-section two-col">
			<div class="col">
				<h3>Input Masking</h3>
				<p>We have extended validation tools with the support for input masking.</p>
				<p>Now you can force a certain pattern when expecting freetype input from user.</p>
				<p>Check out the example on the video to see how we are setting Social Security Number, Driver's License etc with valid input masks.</p>
				<p>Read our knowledgebase to learn how to set it up.</p>
				<p><a href="https://wpquark.com/kb/fsqm/fsqm-form/make-form-elements-not-required-and-customize-validation-filters/#ipt_kb_toc_5947_4" class="button button-secondary">Read More</a></p>
			</div>
			<div class="col">
				<iframe width="560" height="315" src="https://www.youtube.com/embed/6-86BFEuAtc" frameborder="0" allowfullscreen></iframe>
			</div>
		</div>
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
					<h3>New ApiGen Documentation</h3>
					<p>Working with eForm functions and classes has been easier thanks to our refactored codebase. Now the documentation can be found online.</p>
					<p><a href="https://wpq-develop.wpquark.xyz/wp-fsqm-pro/docs/apigen/" class="button button-secondary">ApiGen Documenation</a></p>
				</div>
				<div class="col">
					<h3>New Action &amp; Filter Reference</h3>
					<p>We have integrated a build system to generate all eForm actions and hooks. Use this list if you are trying to extend eForm.</p>
					<p><a href="https://wpq-develop.wpquark.xyz/wp-fsqm-pro/docs/apiwp/" class="button button-secondary">Hook Reference</a></p>
				</div>
				<div class="col">
					<h3>Automatic Plugin Updates through DevOps</h3>
					<p>We use <a href="https://about.gitlab.com">GitLab</a> for our development lab. We have leveraged the powerful CI/CD to streamline our workflow. Now you will get automatic updates once you have activated eForm.</p>
					<p>The current status of the development can be found here.</p>
					<p><a href="https://wpq-develop.wpquark.xyz/wp-fsqm-pro/" class="button button-secondary">eForm DevOps</a></p>
				</div>
			</div>
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
