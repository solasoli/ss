/**
 * The main plugin for FSQM Forms
 *
 * Handles all form related functions
 * Navigating to tabs, switching pages etc
 *
 * This is fired after Plugin UIF INIT
 *
 * @author     Swashata@iPanelThemes.com
 * @dependency jquery, jquery-sayt, ipt-plugin-uif-front-js
 *
 * @license    Themeforest Split License
 */
;(function ( $, window, document, undefined ) {
	"use strict";

	// Create the defaults once
	var pluginName = "iptFSQMForm",
	defaults = {
		propertyName: "value"
	};

	/**
	 * Custom reCaptcha Loader Function
	 *
	 * Checks all forms and loads recaptcha if found
	 */
	window.eFormreCaptchaLoad = function() {
		$( '.ipt_fsqm_form' ).each( function() {
			var elm = $( this ),
			greCaptchaElm = elm.find( '.g-recaptcha' ).eq( 0 ),
			greCaptchaInput = greCaptchaElm.prev( 'input' ),
			captchaHandler = function( value ) {
				greCaptchaInput.val( value );
				elm.data( 'reCaptchaValidated', true );
				elm.find( 'form.ipt_uif_validate_form' ).validationEngine( 'hideAll' );
			},
			captchaExpirer = function() {
				greCaptchaInput.val( '' );
				elm.data( 'reCaptchaValidated', false );
			};
			if ( greCaptchaElm.length ) {
				// Although we are expecting only one reCaptcha Element inside
				var widgets = {
					sitekey: greCaptchaElm.data( 'sitekey' ),
					theme: greCaptchaElm.data( 'theme' ),
					type: greCaptchaElm.data( 'type' ),
					size: greCaptchaElm.data( 'size' ),
					callback: captchaHandler,
					'expired-callback': captchaExpirer,
				};
				grecaptcha.render( greCaptchaElm.get( 0 ), widgets );
			}
			elm.data( 'reCaptchaValidated', false );
		} );
	};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		// DOM variables
		this.element = element;
		this.jElement = $(element);

		// Plugin Settings
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;

		// Plugin Names
		this._name = pluginName;

		// Initialize
		this.init();
	}

	Plugin.prototype = {
		// Main initialization logic
		init: function () {
			var self = this,
			waypoint_animation = this.jElement.data('eformanim') == 1 ? true: false,
			additional_themes = [];
			if ( $( '#ipt_fsqm_primary_css-css' ).length ) {
				additional_themes[ additional_themes.length ] = {
					id : 'ipt_fsqm_primary_css',
					src : iptFSQM.location + 'css/form.css?version=' + iptFSQM.version
				};
			}

			// Fix the customizer issue #265
			if ( "undefined" !== typeof( wp ) && "undefined" !== typeof( wp.customize ) ) {
				// Stop animating the loader
				var loaderElem = this.jElement.find('.ipt_uif_init_loader .ipt_uif_ajax_loader_inner');
				loaderElem.removeClass('ipt_uif_ajax_loader_animate');
				loaderElem.find('.ipt_uif_ajax_loader_text').text( iptFSQM.l10n.customizer_msg );
				if ( console && console.warn ) {
					console.warn('Customizer Detected. Shutting Down eForm');
				}
				return;
			}

			// Init basic variables
			this.initBasicVariables();

			// First we apply Sayt and restore the stopwatch
			// If necessary
			this.applySayt();
			this._restoreStopwatchVal();

			// Now we call the UI elements with callback
			// The callback would initiate the rest of the form elements
			this.jElement.iptPluginUIFFront({
				callback : function() {
					self.initVariables();
					self.applyFSQM();
					self._saytRestoreTab();
					self.applyGoogleAnalytics();
					self.applyLogins();
				},
				additionalThemes : additional_themes,
				waypoints: waypoint_animation
			});
		},

		// Initializes basic variables
		// These do not need application of front UI
		initBasicVariables: function() {
			// DOM elements
			this.main_tab = this.jElement.find('.ipt_fsqm_main_tab');
			this.main_form = this.jElement.find('form.ipt_fsqm_main_form');
			this.form_id = this.jElement.find('input[name="form_id"]').val();
			this.data_id = this.jElement.find('input[name="data_id"]').val();
			this.restore_block = this.jElement.find('.ipt_fsqm_form_message_restore');
			this.validation_block = this.jElement.find( '.ipt_fsqm_form_validation_error' );
			this.interval_save_button = this.jElement.find('.ipt_fsqm_form_button_interval_save');

			// DOM settings
			this.sayt_settings = this.jElement.data('fsqmsayt');
			this.formReset = this.jElement.data('fsqmreset');
			this.regSettings = this.jElement.data('eformreg');

			// Cookie
			this.eFormCookie = this.jElement.data( 'eformCookie' );

			// reCaptcha
			this.reCaptchaNeeded = false;
			if ( this.jElement.find( '.ipt_fsqm_container_recaptcha' ).length ) {
				this.reCaptchaNeeded = true;
			}
		},

		// Initializes variables
		// We call this after iptPluginUIFFront
		// Because many UI elements needs to be applied before initiating
		initVariables: function() {
			// Rest of DOM elements
			this.main_pb = this.jElement.find('.ipt_fsqm_main_pb');
			this.button_container = this.jElement.find('.ipt_fsqm_form_button_container');
			this.prev_button = this.button_container.find('.ipt_fsqm_form_button_prev');
			this.next_button = this.button_container.find('.ipt_fsqm_form_button_next');
			this.submit_button = this.button_container.find('.ipt_fsqm_form_button_submit');
			this.reset_button = this.button_container.find('.ipt_fsqm_form_button_reset');
			this.terms_wrap = this.jElement.find('.ipt_fsqm_terms_wrap');
			this.tabIndices = this.main_tab.find( 'ul.ui-tabs-nav' ).eq(0).find('> li');
			this.process = this.jElement.find('.ipt_fsqm_form_message_process');
			this.success = this.jElement.find('.ipt_fsqm_form_message_success');
			this.http_error = this.jElement.find('.ipt_fsqm_form_message_error');

			// Internal settings
			this.timerTabFormSync = {
				timerEnabled: false,
				forceProgress: false,
				forceSubmit: false
			};
			this.nonce_interval = undefined;
			this.ga_tracker_name = '';
			this.ga_cache = {};

			// Rest of DOM settings
			this.fsqm_ga_data = this.jElement.data('fsqmga');
			this.ui_type = this.jElement.data('uiType');
			this.hidden_button = this.jElement.data('hiddenButtons');
			this.tab_settings = this.main_tab.data('settings');
			this.scroll_settings = this.jElement.data( 'eformscroll' );

			// Action flags
			this.fsqm_submitting = false;
			this.skipping_tab_for_conditional = false;
			this.restoring_form = false;
			this.sayt_restoring_tab = false;
			this.jumping_on_button = false;
			this.changing_tab_on_submit_error = false;
			this.changing_tab_on_timer = false;
			this.auto_progressing = false;
			this.auto_progress_timer = false;
			this.sayt_interval_saving = false;

			// State flags
			this.on_last_page = false;
			// This would also be true if form type is simple container
			if ( ! this.main_tab.length ) {
				this.on_last_page = true;
			}

			// Conditions flags
			this.block_prev_on_timer = false;
		},

		// Apply some login logics
		applyLogins: function() {
			if ( ! this.regSettings ) {
				return;
			}
			var i;
			if ( true === iptFSQM.core.logged_in && true === this.regSettings.enabled ) {
				// Hide the username and password
				this.jElement.find( '#ipt_fsqm_form_' + this.form_id + '_pinfo_' + this.regSettings.username_id ).hide();
				this.jElement.find( '#ipt_fsqm_form_' + this.form_id + '_pinfo_' + this.regSettings.password_id ).hide();
				// Hide basic pinfo elements if settings say so
				if ( true === this.regSettings.hide_pinfo ) {
					this.jElement.find('.ipt_fsqm_container_f_name').hide();
					this.jElement.find('.ipt_fsqm_container_l_name').hide();
					this.jElement.find('.ipt_fsqm_container_email').hide();
				}
				// Hide meta entries if settings say so
				if ( true === this.regSettings.hide_meta ) {
					for ( i in this.regSettings.meta ) {
						this.jElement.find( '#ipt_fsqm_form_' + this.form_id + '_' + this.regSettings.meta[ i ].m_type + '_' + this.regSettings.meta[ i ].key ).hide();
					}
				}
			}
		},

		// Apply Google Analytics Tracking
		applyGoogleAnalytics: function() {
			// Store the reference
			var that = this,
			eventCategory = '',
			eventLabels = {
				mcq: 'Multiple Choice Questions (M)',
				freetype: 'Feedback and Upload (F)',
				pinfo: 'Other Form Elements (O)'
			},
			mTypes = {
				mcq: 'M',
				freetype: 'F',
				pinfo: 'O'
			};

			// Do not do anything if settings does not say so
			if ( typeof( this.fsqm_ga_data ) !== 'object' || this.fsqm_ga_data.enabled !== true ) {
				return;
			}

			// change eventCategory according
			if ( this.fsqm_ga_data.user_update === true ) {
				eventCategory = 'FSQM Update: ' + this.fsqm_ga_data.form_id;
			} else {
				eventCategory = 'FSQM New Submission: ' + this.fsqm_ga_data.form_id;
			}

			// Now load the script if window.ga isn't present
			if ( ( window.ga === undefined || this.fsqm_ga_data.manual_load === true ) && this.fsqm_ga_data.tracking_id !== '' ) {
				/* jshint ignore:start */
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
				/* jshint ignore:end */
			}

			// Check if window.ga is present
			// Exit otherwise
			if ( window.ga === undefined ) {
				return;
			}

			// Create a separate tracking object
			ga( 'create', {
				trackingId: this.fsqm_ga_data.tracking_id,
				cookieDomain: this.fsqm_ga_data.cookie,
				name: 'FSQM' + this.fsqm_ga_data.form_id
			} );
			this.ga_tracker_name = 'FSQM' + this.fsqm_ga_data.form_id + '.';

			// Create a few dimensions and metrics for storing data
			var ga_dimensions = {
				dimension1: '', // Would be element m_type
				dimension2: '', // Would be element type
				dimension3: '', // Would be element key
				dimension4: '' // Would be element value
			};

			// Send a pageview to the form
			ga( this.ga_tracker_name + 'send', 'pageview' );

			// Now set tracking events
			// 1. Form Page Change
			this.jElement.on( 'tabsactivate', function( event, ui ) {
				// Do not do anything if from system
				if ( that.skipping_tab_for_conditional || that.restoring_form || that.sayt_restoring_tab || that.changing_tab_on_submit_error ) {
					return;
				}

				// Log event
				var completedIndex = that.tabIndices.index( ui.oldTab ),
				eventLabel = ui.oldTab.text();
				ga( that.ga_tracker_name + 'send', 'event', eventCategory, 'Pagination', eventLabel, {
					dimension5: completedIndex.toString()
				} );
			} );

			// 2. On change
			this.jElement.on( 'blur change', '.ipt_uif_conditional', function(e) {
				// We do this only for originalEvent
				// This behavior stops for additional checks
				// Like if triggered on conditional or on load
				// Besides we should track only user activities
				// Not system acitivities
				if ( e.originalEvent === undefined ) {
					return;
				}

				var elm = $(this),
				elm_m_type = elm.prev().prev().val();

				// Do only if this is a valid element
				if ( $.inArray( elm_m_type, [ 'mcq', 'freetype', 'pinfo' ] ) != -1 ) {
					var validationElm = $(this).find('.check_me'),
					validationState = false,
					validationElmType = elm.prev().val(),
					elmComponents = /ipt_fsqm_form_([0-9]+)_(mcq|freetype|pinfo)_([0-9]+)/gi.exec( elm.attr('id') ),
					elmKey = elmComponents[3],
					validationValue = that._getElementValues( elm, validationElmType ),
					qContainerLabel = elm.find('> .ipt_uif_column_inner > .ipt_uif_question > .ipt_uif_question_label > .ipt_uif_question_title').length ? elm.find('> .ipt_uif_column_inner > .ipt_uif_question > .ipt_uif_question_label > .ipt_uif_question_title') : elm.find('label').eq(0),
					eventLabel = '(' + mTypes[elm_m_type] + '){' + elmKey + '} ' + qContainerLabel.text();


					// See if element is supported
					if ( validationValue === false || validationValue === undefined ) {
						return;
					}

					// Check the validation state
					// if feasible
					if ( validationElm.length ) {
						validationState = validationElm.validationEngine('validateSilent');
					}

					// Now populate the ga send parameters
					var eventAction = 'Completed';
					if ( validationState === true ) {
						eventAction = 'Skipped';
					}

					if ( typeof( validationValue ) == 'object' ) {
						validationValue = validationValue.join(', ');
					} else {
						validationValue = validationValue.toString();
					}

					// Modify the ga dimensions and metrics
					ga_dimensions.dimension1 = eventLabels[elm_m_type]; // m_type
					ga_dimensions.dimension2 = validationElmType; // type
					ga_dimensions.dimension3 = elmKey; // key
					ga_dimensions.dimension4 = validationValue; // value

					// Send it
					ga( that.ga_tracker_name + 'send', 'event', eventCategory, eventAction, eventLabel, ga_dimensions );
				}
			} );

			// 3. On form submit
			this.jElement.on( 'submit', function() {
				ga( that.ga_tracker_name + 'send', 'event', eventCategory, 'Submit', that.fsqm_ga_data.name );
			} );
		},

		// Apply Sayt
		// If necessary
		applySayt: function() {
			// No need if sayt isn't present
			if ( typeof( $.fn.sayt ) == 'undefined' ) {
				return;
			}
			var that = this;

			// Don't do anything if settings or situation does not permit
			if ( this.sayt_settings === undefined || this.sayt_settings.admin_override !== false || this.sayt_settings.user_update !== false || this.sayt_settings.auto_save !== true ) {
				this.restore_block.hide();
				return;
			}

			// Restore if necessary
			if ( this.sayt_settings.restore === true && this.main_form.sayt({'autosave': false,'checksaveexists': true}) === true ) {
				this.main_form.sayt({'recover': true});

				// Also show the restore message
				if ( this.sayt_settings.show_restore ) {
					this.restore_block.fadeIn('fast');
					this.restore_block.on( 'click', '.ipt_fsqm_form_message_close', function(e) {
						e.preventDefault();
						that.restore_block.slideUp('fast');
					} );
				}
			} else {
				this.restore_block.hide();
			}

			// Apply Sayt
			if ( true === this.sayt_settings.interval_save ) {
				// Do interval save
				// We do by closure because of accessing this
				if ( this.sayt_settings.interval > 0 ) {
					this.saytIntervalID = window.setInterval( function() {
						that.saytManualSave();
					}, ( this.sayt_settings.interval * 1000 ) );
				}

				// Hooking on the save button
				if ( this.interval_save_button.length ) {
					this.interval_save_button.on( 'click', function(e) {
						e.preventDefault();
						that.saytManualSave();
						var btn = $(this);
						btn.tooltipster( 'close' );
						setTimeout( function() {
							btn.tooltipster( 'content', btn.data( 'stitle' ) );
							btn.tooltipster( 'open' );
						}, 500 );
					} );
				}
			} else {
				// We need autosave
				this.main_form.sayt({
					'autosave': true,
					'autorecover': false,
					'days': 30,
					'exclude': ['.ipt_fsqm_sayt_exclude']
				});
			}

			// Reset form of click on restore
			this.jElement.on( 'click', '.ipt_fsqm_form_message_restore .ipt_fsqm_sayt_reset', function(e) {
				// Stop default action
				e.preventDefault();

				// Restore the form
				that._restoreForm();

				// Close the restore message
				$(this).closest('.ipt_fsqm_form_message_restore').slideUp('fast');
			} );
		},

		saytManualSave: function() {
			// No need if sayt isn't present
			if ( typeof( $.fn.sayt ) == 'undefined' ) {
				return;
			}
			// If already being saved
			if ( true === this.sayt_interval_saving ) {
				return;
			}
			// Mark the flag
			this.sayt_interval_saving = true;
			this.main_form.sayt({
				'autosave': false,
				'autorecover': false,
				'days': 30,
				'exclude': ['.ipt_fsqm_sayt_exclude'],
				'savenow': true
			});
			this.sayt_interval_saving = false;
		},

		// Apply main FSQM form functions
		applyFSQM: function() {
			// Check for form startup timer
			this._refreshStartupTimer();

			// Apply the Form events
			this.applyFormEvents();

			// Check for timer events
			this.applyTimerEvent();

			// Apply the Tab events
			this.applyTabEvents();

			// Apply auto progress events
			this.applyAutoProgress();

			// Apply the Nonce events
			this.applyNonceEvents();

			// Apply coupons functionality
			this.applyCoupons();

			// Apply Stopwatch
			this.applyStopwatch();

			// Apply interval button tooltipster
			this.applyIntervalTooltipster();
		},

		// Interval Button Tooltipster
		applyIntervalTooltipster: function() {
			if ( this.interval_save_button.length ) {
				// button
				var btn = this.interval_save_button;
				// tooltipster instance
				var instance = this.interval_save_button.tooltipster( 'instance' );
				instance.on( 'closing', function() {
					btn.tooltipster( 'content', btn.data( 'otitle' ) );
				} );
			}
		},

		// Stopwatch functionality
		applyStopwatch: function() {
			var stopwatchTimer = this.jElement.find('.ipt_fsqm_form_stopwatch'),
			stopwatchTimerVal = this.jElement.find('.ipt_fsqm_form_stopwatch_val');
			if ( stopwatchTimer.length ) {
				stopwatchTimer.TimeCircles().addListener(function( unit, value, total ) {
					stopwatchTimerVal.val(total);
				}, 'all');
			}
		},

		// Coupon code functionality
		applyCoupons: function() {
			// Get the coupon button and check for existence
			var couponButton = this.jElement.find('.ipt_uif_coupon_button');
			if ( couponButton.length === 0 ) {
				return;
			}

			// Now bind the event
			couponButton.on('click', function(e) {
				e.preventDefault();
				// Disable the button
				var that = $(this).prop('disabled', true);
				var couponContainer = $(this).closest( '.ipt_uif_coupon' ),
				data = couponContainer.data('config'),
				couponMessage = couponContainer.find('.ipt_uif_coupon_message'),
				couponMath = couponContainer.find('.ipt_uif_mathematical_input');

				that.find('.ui-button-text').html(data.wait);
				couponMessage.html('');

				if ( couponContainer.find('.ipt_uif_coupon_text').val() === '' ) {
					that.prop('disabled', false);
					that.find('.ui-button-text').html(data.normal);
					couponMath.data('formula', couponMath.attr('data-formula'));
					couponContainer.find('.ipt_uif_coupon_final').trigger('fsqm.mathematicalReEvaluate');
					couponContainer.trigger('fsqm.conditional');
					return;
				}

				$.get(iptFSQM.ajaxurl, {
					action: data.action,
					_wpnonce: data.cnonce,
					form_id: data.form_id,
					coupon: couponContainer.find('.ipt_uif_coupon_text').val(),
					amount: couponContainer.closest('.ipt_fsqm_container_payment').find('.ipt_fsqm_payment_mathematical .ipt_uif_mathematical_input').val(),
				}, function( response ) {
					couponMessage.html( response.msg ).removeClass('msg_okay').removeClass('msg_error');
					if ( response.success === true ) {
						couponMessage.addClass('msg_okay');
						couponMath.data('formula', response.formula);
					} else {
						couponMessage.addClass('msg_error');
						couponMath.data('formula', couponMath.attr('data-formula'));
					}
				}).fail(function() {
					couponMessage.addClass('msg_error');
					couponMath.data('formula', couponMath.attr('data-formula'));
					alert( data.http_error );
				}).always(function() {
					that.prop('disabled', false);
					that.find('.ui-button-text').html(data.normal);
					couponContainer.find('.ipt_uif_coupon_final').trigger('fsqm.mathematicalReEvaluate');
					couponContainer.trigger('fsqm.conditional');
				});
			});

			// Process for enter key
			this.jElement.find('.ipt_uif_coupon_text').on('keyup keypress', function(e) {
				var keyCode = e.keyCode || e.which;
				if ( keyCode === 13 ) {
					e.preventDefault();
					couponButton.trigger('click');
					return false;
				}
			});

			// Recheck for coupon if final value changes
			this.jElement.find('.ipt_uif_coupon_final').on('change', function() {
				couponButton.trigger('click');
			});
		},

		// Nonce Refresh event
		applyNonceEvents: function() {
			// Do nothing if no form_id
			if ( ! this.jElement.find('input[name="form_id"]').length ) {
				return;
			}

			// Set some variables
			var data_id = this.data_id === undefined ? null : this.data_id,
			nonceSaveField = this.jElement.find('input[name="ipt_fsqm_form_data_save"]'),
			nonceUpdateField = this.jElement.find('input[name="ipt_fsqm_user_edit_nonce"]'),
			userEditField = this.jElement.find('input[name="user_edit"]'),
			ajaxData = {
				form_id: this.form_id,
				action: 'ipt_fsqm_refresh_nonce'
			};

			if ( data_id !== null ) {
				ajaxData.data_id = data_id;
			}

			if ( userEditField.length ) {
				ajaxData.user_edit = '1';
			}

			// Refresh the nonce
			var refreshNonce = function() {
				$.post(iptFSQM.ajaxurl, ajaxData, function(data, textStatus, xhr) {
					if ( typeof( data ) == 'object' && data.success === true ) {
						nonceSaveField.val(data.save_nonce);
						if ( nonceUpdateField.length ) {
							nonceUpdateField.val(data.edit_nonce);
						}
					}
				});
			};
			refreshNonce();
			this.nonce_interval = setInterval(refreshNonce, 3600000);
		},

		applyAutoProgress: function() {
			// Do not do anything if auto_progress isn't defined
			if ( this.tab_settings === undefined || this.tab_settings.auto_progress !== true ) {
				return;
			}

			// Also no need to do anything if not tabbed
			if ( ! this.main_tab.length ) {
				return;
			}

			// Store reference
			var that = this;

			// Now add listener for auto progress
			this.jElement.on( 'blur change', '.check_me', $.debounce( 250, function() {
				// We do not do anything if already autoprogressing
				if ( that.auto_progressing === true ) {
					return;
				}
				// Clear previous timeout if needed
				if ( that.auto_progress_timer !== false ) {
					clearTimeout( that.auto_progress_timer );
					that.auto_progress_timer = false;
				}
				// Get the delay
				var progressDelay = parseInt( that.tab_settings.auto_progress_delay, 10 );

				if ( undefined === progressDelay || isNaN( progressDelay ) ) {
					progressDelay = 1500;
				}

				// First we validate the current tab
				var all_validated = true;
				that.main_tab.find('> div.ui-tabs-panel[aria-hidden="false"] .check_me').each(function() {
					// If this element does not validate then there is no need check any further
					if ( $(this).validationEngine('validateSilent') === true ) {
						all_validated = false;
						return false;
					}
				});

				// If not all validated
				// Then stop execution
				if ( all_validated !== true ) {
					return;
				}

				// Automatic progress if it is in between
				if ( that.on_last_page !== true ) {
					that.auto_progress_timer = setTimeout( function() {
						that.auto_progressing = true;
						that._navigateNextTab();
						that.auto_progressing = false;
						that.auto_progress_timer = false;
					}, progressDelay );
				// Automatic submit if in the last page and settings say so
				} else {
					that.auto_progress_timer = setTimeout( function() {
						that.auto_progress_timer = false;
						// If terms wrap is present
						if ( that.terms_wrap.length && that.terms_wrap.find('.check_me').validationEngine('validateSilent') === true ) {
							return;
						}

						if ( that.tab_settings.auto_submit === true ) {
							that.main_form.submit();
						}
					}, progressDelay );
				}
			} ) );
		},

		// Apply the form events
		applyFormEvents: function() {
			// Store the reference
			var that = this;

			// Attach the submit event
			this.main_form.on( 'submit', function(e) {
				// prevent default
				e.preventDefault();

				var currentTabValidated = true;

				// Ignore everything and just proceed with submission if it is timer related
				if ( that.timerTabFormSync.timerEnabled === true && that.timerTabFormSync.forceSubmit === true ) {
					that._processSubmission();
					return;
				}

				// Prevent submission if not from last tab
				if ( that.on_last_page !== true ) {
					that._navigateNextTab();
					return;
				}

				// Now we head to the last tab/page or simple container validation
				// First open any collapsed container
				that._openRequiredCollapsedElements( that.main_form );

				// Now just validate the form
				// If not validates then just return
				// In case of tabbed form we just validate the last tab
				if ( that.main_tab.length !== 0 ) {
					that.main_tab.find('> div.ui-tabs-panel[aria-hidden="false"] .check_me').each(function() {
						if ( true === $(this).validationEngine( 'validate' ) ) {
							currentTabValidated = false;
							that._scrollToPosition( $(this), 200, 80 );
							return false;
						}
					});
					if ( currentTabValidated === false ) {
						return false;
					}
					// Also the terms and conditions
					if ( that.terms_wrap.length && that.terms_wrap.find('.check_me').validationEngine('validate') ) {
						return false;
					}
				// Otherwise we validate the whole form
				} else {
					if ( that.main_form.validationEngine( 'validate' ) === false ) {
						return false;
					}
				}

				// Also check for uploads
				if ( that._checkUploadRequests( that.main_form ) === false ) {
					return false;
				}

				// Check for reCaptcha
				if ( false == that._checkForReCaptcha() ) {
					return false;
				}

				// At this point so let us do the submission processing
				that._processSubmission();

				// Now hide the timer
				that._destroyTimer( true );

				return true;

			} );
		},

		// Apply the Tab Events
		applyTabEvents: function() {
			// First we store the reference
			var that = this;

			// Set the jump to container buttons
			this.jElement.on( 'click', '.ipt_fsqm_jump_button', function(e) {
				e.preventDefault();
				if ( ! that.main_tab.length ) {
					return false;
				}

				// Set the flag and do the action
				that.jumping_on_button = true;
				that.main_tab.tabs('option', 'active', $(this).data('pos') - 1);
				that.jumping_on_button = false;
			} );

			// Attach the event on reset button
			this._onResetButton();

			// If no main tab then just revert to usual
			if ( ! this.main_tab.length ) {
				// Set the state flag
				this.on_last_page = true;
				return;
			}

			// Hide the uls for paginated appearance
			if ( this.tab_settings.type == 2 ) {
				this.main_tab.find('ul.ui-tabs-nav').eq(0).hide();
			}

			// Initialize the buttons
			// This would also hide the previous button if needed
			this._initButtonsForTab();

			// Attach the check event on before tab activate
			this.main_tab.on( 'tabsbeforeactivate', function( event, ui ) {
				if ( ! that.main_tab.is( $( event.target ) ) ) {
					return;
				}
				// First we set some variables
				var indexOld = that.tabIndices.index(ui.oldTab),
				indexNew = that.tabIndices.index(ui.newTab),
				currentTabValidated = true;

				// We do not do anything at all
				// If the action flag says so
				// Case#1: Skipping tab for conditional
				if ( that.skipping_tab_for_conditional === true ) {
					return true;
				}

				// We also do not do anything for restore
				if ( that.restoring_form === true ) {
					// But we may just need to check for conditionals
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Case#2: Sayt restore
				if ( that.sayt_restoring_tab === true ) {
					// Still there is a possibility for conditional jump
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Case#3: Nothing except timer matters for jump on button
				if ( that.jumping_on_button === true && that.timerTabFormSync.timerEnabled !== true ) {
					// Still a possibility for skipping tabs
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Case#4: If jumping because of submit error
				if ( that.changing_tab_on_submit_error === true ) {
					// Still a possibility for skipping tabs
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Case#5: If jumping because admin allows
				if ( true == that.tab_settings.any_tab ) {
					// Still a possibility for skipping tabs
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Case#6: If jumping because of timer persistance
				if ( true == that.changing_tab_on_timer ) {
					// Still a possibility for skipping tabs
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Do not let moving forward if next button is conditionally hidden
				if ( indexNew > indexOld && that.next_button.hasClass('iptUIFCHidden') ) {
					return false;
				}

				// Always block if moving away from multiple forward
				// Rather just move to the next tab
				if ( indexNew > indexOld && ( indexNew - indexOld ) > 1 ) {
					that._navigateNextTab();
					return false;
				}

				// Check for moving backward
				if ( indexNew < indexOld ) {
					// Block according to settings
					if ( that.tab_settings.block_previous === true || that.block_prev_on_timer === true ) {
						return false;
					}

					// If can previous without validation
					if ( that.tab_settings.can_previous === true ) {
						// There is also a possibility for skipping tab
						if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
							return false;
						} else {
							return true;
						}
					}
				}

				// Just move if a timer event is triggered
				if ( that.timerTabFormSync.forceProgress === true && that.timerTabFormSync.timerEnabled === true ) {
					// But also possibility for skipping tab
					if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
						return false;
					} else {
						return true;
					}
				}

				// Now that we have passed all flag related actions
				// Lets check for elements and validate

				// First open any collapsed elements
				that._openRequiredCollapsedElements( ui.oldPanel );

				// Validate the current panel
				ui.oldPanel.find('.check_me').each(function() {
					if ( true === $(this).validationEngine( 'validate' ) ) {
						var elm = $( this );
						currentTabValidated = false;
						if ( that.tab_settings.scroll_on_error ) {
							that._scrollToPosition( $(this), 200, 80 );
						}
						if ( elm.hasClass( 'ipt_uif_text' ) || elm.hasClass( 'ipt_uif_textarea' ) ) {
							elm.addClass( 'invalid' )
							.removeClass( 'valid' );
						}
						return false;
					}
				});
				if ( currentTabValidated === false ) {
					return false;
				}

				// Check any upload requests
				if ( that._checkUploadRequests( ui.oldPanel ) === false ) {
					return false;
				}

				// Check for any active captcha
				if ( false == that._checkForReCaptcha() ) {
					return false;
				}

				// Account for the possibility that this tab may be conditionally hidden
				if ( that._skipTabIfNecessary( ui, indexNew, indexOld ) === true ) {
					return false;
				}

				// Everything checks out
				return true;
			} );

			// Add event on tabs activate
			this.main_tab.on( 'tabsactivate', function( event, ui ) {
				if ( ! that.main_tab.is( $( event.target ) ) ) {
					return;
				}
				var indexNew = that.tabIndices.index(ui.newTab);
				// Update the progressbar
				if ( that.tab_settings.type == 2 && that.tab_settings.show_progress_bar === true ) {
					var percentage = ( indexNew / that.tabIndices.length ) * 100;
					// We convert to a fixed decimal float
					// Note the '+' sign before var makes the "string" (from toFixed) cast to float
					percentage = + percentage.toFixed( that.tab_settings.decimal_point );
					that.main_pb.progressbar( 'option', 'value', percentage );
				}

				// Change the tab position hidden element
				that.main_form.find('.ipt_fsqm_form_tab_pos').val(indexNew).trigger('change');

				// Refresh buttons for tabs
				that._refreshButtonsForTab();

				// Scroll to the tab
				that._scrollToTab();

				// Add a Cookie
				Cookies.set( 'eform-quiz-tab-' + that.form_id, indexNew, {
					expires: 30,
					path: ''
				} );
			} );

			// Add event on conditional
			this.main_tab.on( 'iptUIFCHide iptUIFCShow', '[role="tab"]', function() {
				that._refreshButtonsForTab();
			} );
		},

		// Apply timer events
		applyTimerEvent: function() {
			// No need if JS not present
			if ( typeof( $.fn.TimeCircles ) == 'undefined' ) {
				return;
			}

			// Store reference
			var that = this,
			timerRawData = this.jElement.find('.ipt_fsqm_timer_data').val();

			// Do not do anything of not needed
			if ( timerRawData === null || timerRawData === '' || timerRawData === undefined ) {
				return;
			}

			// Set reference variables for use with internal
			this.timerVar = $.parseJSON(timerRawData);
			this.timerOuterDIV = this.jElement.find('.ipt_fsqm_timer');
			this.timerDIV = this.timerOuterDIV.find('> .ipt_fsqm_timer_inner');
			this.timerSpacer = this.timerOuterDIV.next('.ipt_fsqm_timer_spacer');

			// No need to progress if invalid timer data
			if ( this.timerVar === null || ! this.timerVar ) {
				return;
			}

			// Set the flags
			this.timerTabFormSync.timerEnabled = true;
			this.timerTabFormSync.timerVar = this.timerVar;

			// Initialize the timer
			this._initTimer();

			// Now attach the scroll events
			if ( this.timerVar.type == 'overall' || this.timerVar.type == 'page_specific' ) {
				var affixTimerScroll = function() {
					var windowTop = $(window).scrollTop(),
					windowBottom = windowTop + $(window).height(),
					containerOffset = that.jElement.offset(),
					containerTop = containerOffset.top + 10,
					containerBottom = containerTop + that.jElement.outerHeight() + 90;

					// Affix it if scrolling within form
					if ( ( windowBottom >= containerTop ) && ( containerBottom >= windowBottom ) ) {
						if ( ! that.timerOuterDIV.hasClass('fixed') ) {
							that.timerOuterDIV.appendTo('body');
							that.timerDIV.TimeCircles().rebuild();
							// We add the class at last because it gives some time for CSS engine to take over on transition animation
							that.timerOuterDIV.addClass('fixed');
						}
					// Set to normal otherwise
					} else {
						if ( that.timerOuterDIV.hasClass('fixed') ) {
							that.timerOuterDIV.insertBefore( that.timerSpacer );
							that.timerDIV.TimeCircles().rebuild();
							that.timerOuterDIV.removeClass('fixed');
						}
					}
				};

				$(document).on( 'scroll', $.debounce( 250, affixTimerScroll ) );

				affixTimerScroll();

				$(window).on('resize iptUIFCShow iptUIFCHide tabsactivate', $.debounce(250, function() {
					affixTimerScroll();
					that.timerDIV.TimeCircles().rebuild();
				}));
			}
		},

		/**
		 * Internal methods
		 *
		 */
		// Timer initialization
		_initTimer: function() {
			var that = this, i, pageTime, totalTime, persistentTime;
			this._reInitTimer();
			// If overall timer
			if ( this.timerVar.type == 'overall' ) {
				// Check for invalid settings
				if ( this.timerVar.time === 0 || this.timerVar.time === '' || isNaN( this.timerVar.time ) ) {
					this._destroyTimer();

				// All checks out
				// Setup the timer and add event listener
				} else {
					// Check for remaining time in Cookies
					persistentTime = this._getPersistentTime();
					if ( undefined != persistentTime ) {
						this.timerDIV.data('timer', this._sanitizePersistentTime(persistentTime, this.timerVar.time) );
					} else {
						this.timerDIV.data('timer', this.timerVar.time);
					}

					this.timerDIV.TimeCircles({
						time: {
							Days: {show: false},
							Hours: {
								text: iptPluginUIFFront.L10n.timer.Hours
							},
							Minutes: {
								text: iptPluginUIFFront.L10n.timer.Minutes
							},
							Seconds: {
								text: iptPluginUIFFront.L10n.timer.Seconds
							}
						},
						total_duration: 'Auto',
						count_past_zero: false
					}).addListener(function(unit, value, total) {
						Cookies.set( 'eform-quiz-time-' + that.form_id, total, {
							expires: 30,
							path: ''
						} );
						if ( total <= 0 ) {
							that._progressTimerPage();
						}
					});
				}
			// If page specific timer
			} else if ( this.timerVar.type == 'page_specific' ) {
				// Get the totalTime
				// Just if needed
				totalTime = 0;
				for ( i in this.timerVar.time ) {
					pageTime = parseFloat( this.timerVar.time[i] );
					if ( isNaN(  pageTime) ) {
						pageTime = 0;
					}
					$('#ipt_fsqm_form_' + that.form_id + '_tab_' + i).data('ipt_fsqm_timer', pageTime);
					totalTime += pageTime;
				}

				// Use the total time if not tabbed
				if ( ! this.main_tab.length ) {
					if ( totalTime === 0 || totalTime === '' || isNaN( totalTime ) ) {
						this._destroyTimer();
					} else {
						// Get the persistent Time
						persistentTime = this._getPersistentTime();
						if ( undefined != persistentTime ) {
							this.timerDIV.data( 'timer', this._sanitizePersistentTime( persistentTime, totalTime ) );
						} else {
							this.timerDIV.data( 'timer', totalTime );
						}
						this.timerDIV.TimeCircles({
							time: {
								Days: {show: false},
								Hours: {
									text: iptPluginUIFFront.L10n.timer.Hours
								},
								Minutes: {
									text: iptPluginUIFFront.L10n.timer.Minutes
								},
								Seconds: {
									text: iptPluginUIFFront.L10n.timer.Seconds
								}
							},
							total_duration: 'Auto',
							count_past_zero: false
						}).addListener(function(unit, value, total) {
							Cookies.set( 'eform-quiz-time-' + that.form_id, total, {
								expires: 30,
								path: ''
							} );
							if ( total <= 0 ) {
								that._progressTimerPage();
							}
						});
					}
				// Modify tab settings beforehand
				// To abide by the timer
				} else {
					this.block_prev_on_timer = true;
					this.initialTabTimerRestore = true;
					// Restore the tab position
					if ( ! this._restoreTimerTabPosition() ) {
						this._activeTabTimer();
						that.initialTabTimerRestore = false;
					}
					// Add further events
					this.__activeTabTimerRef = function() {
						that._activeTabTimer();
						that.initialTabTimerRestore = false;
					};
					this.main_tab.on( 'tabsactivate', this.__activeTabTimerRef );
				}
			// Incorrect settings
			} else {
				this._destroyTimer();
			}
		},
		// RestoreTab from persistan
		_restoreTimerTabPosition: function() {
			var tabPosition = Cookies.get( 'eform-quiz-tab-' + this.form_id );
			if ( undefined !== tabPosition && 0 != tabPosition ) {
				this.changing_tab_on_timer = true;
				this.main_tab.tabs( 'option', 'active', tabPosition );
				this.changing_tab_on_timer = false;
				return true;
			}
			return false;
		},
		// get persistent time from Cookie
		_getPersistentTime: function() {
			var time = Cookies.get( 'eform-quiz-time-' + this.form_id );
			if ( undefined == time ) {
				return undefined;
			}
			time = parseInt( time, 10 );
			if ( isNaN( time ) ) {
				return undefined;
			}
			return time;
		},
		// Sanitize persistentTime w.r.t given time
		_sanitizePersistentTime: function( persistentTime, actualTime ) {
			// If it is zero or less than zero, then just submit
			if ( persistentTime <= 0 ) {
				this._progressTimerPage();
				return 0;
			}
			// If the saved one is greater than current, then also reset
			if ( persistentTime > actualTime ) {
				persistentTime = actualTime;
			}
			return persistentTime;
		},
		// active tab timer
		_activeTabTimer: function() {
			var that = this;
			var activeTab = this.main_tab.find('.ui-tabs-panel').eq( this.main_tab.tabs( 'option', 'active' ) ),
			tabTimer = parseFloat( activeTab.data('ipt_fsqm_timer') );
			this.timerDIV.TimeCircles().destroy();
			if ( tabTimer === 0 || isNaN( tabTimer ) ) {
				this._destroyTimer( false );
			} else {
				this._reInitTimer();
				if ( true == this.initialTabTimerRestore ) {
					// Get the persistance data
					var tabPersistanceTimer = this._getPersistentTime();
					if ( undefined !== tabPersistanceTimer ) {
						this.timerDIV.data( 'timer', this._sanitizePersistentTime( tabPersistanceTimer, tabTimer ) );
					} else {
						this.timerDIV.data('timer', tabTimer);
					}
				} else {
					this.timerDIV.data('timer', tabTimer);
				}
				this.timerDIV.TimeCircles({
					time: {
						Days: {show: false},
						Hours: {
							text: iptPluginUIFFront.L10n.timer.Hours
						},
						Minutes: {
							text: iptPluginUIFFront.L10n.timer.Minutes
						},
						Seconds: {
							text: iptPluginUIFFront.L10n.timer.Seconds
						}
					},
					total_duration: 'Auto',
					count_past_zero: false
				}).addListener(function(unit, value, total) {
					Cookies.set( 'eform-quiz-time-' + that.form_id, total, {
						expires: 30,
						path: ''
					} );
					if ( total <= 0 ) {
						that._progressTimerPage();
					}
				});
			}
		},
		// Destroy the timer
		_destroyTimer: function( detachTab ) {
			detachTab = undefined === detachTab ? false : detachTab;
			if ( ! this.timerVar ) {
				return;
			}
			this.timerDIV.hide().parent().hide().next('.ipt_fsqm_timer_spacer').hide();
			this.timerTabFormSync.timerEnabled = false;
			this.timerTabFormSync.forceProgress = false;
			this.timerTabFormSync.forceSubmit = false;
			if ( detachTab && this.main_tab.length && this.__activeTabTimerRef ) {
				this.main_tab.off( 'tabsactivate', this.__activeTabTimerRef );
			}
			try {
				this.timerDIV.TimeCircles().destroy();
			} catch ( e ) {
				// There wasn't any timecircles
			}
			// Remove the cookie
			Cookies.remove( 'eform-quiz-time-' + this.form_id, {
				expires: 30,
				path: ''
			} );
			Cookies.remove( 'eform-quiz-tab-' + this.form_id, {
				expires: 30,
				path: ''
			} );
		},
		// reinitialize the timer
		_reInitTimer: function() {
			if ( ! this.timerVar ) {
				return;
			}
			this.timerDIV.show().parent().show().next('.ipt_fsqm_timer_spacer').show();
			this.timerTabFormSync.timerEnabled = true;
			this.timerTabFormSync.forceProgress = false;
			this.timerTabFormSync.forceSubmit = false;
		},
		// Progress page according to timer
		_progressTimerPage: function() {
			if ( ! this.timerVar ) {
				return;
			}
			// Do not do anything if it is already submitting
			if ( this.fsqm_submitting === true ) {
				this._destroyTimer( true );
				return false;
			}

			// Submit if on the last page
			if ( this.on_last_page || this.timerVar.type == 'overall' ) {
				this.timerTabFormSync.forceProgress = false;
				this.timerTabFormSync.forceSubmit = true;
				this.main_form.submit();
				this._destroyTimer( true );
			// Progress if on middle
			} else {
				this.timerTabFormSync.forceProgress = true;
				this.timerTabFormSync.forceSubmit = false;
				this._navigateNextTab();
				this.timerTabFormSync.forceProgress = false;
			}
		},
		// Tab Navigations
		// Navigate to next tab
		_navigateNextTab: function() {
			// Dont do anything if on the last page
			if ( this.on_last_page ) {
				return false;
			}
			var newTab = this.tabIndices.index( this.tabIndices.filter('[aria-selected="true"]').next('li') );
			if ( newTab !== -1 ) {
				this.main_tab.tabs( 'option', 'active', newTab );
				return true;
			}
			return false;
		},
		// Navigate to previous tab
		_navigatePrevTab: function() {
			// Dont do anything if on the first page
			if ( this.on_first_page ) {
				return false;
			}
			var newTab = this.tabIndices.index( this.tabIndices.filter('[aria-selected="true"]').prev('li') );
			if ( newTab !== -1 ) {
				this.main_tab.tabs( 'option', 'active', newTab );
				return true;
			}
			return false;
		},
		// Reset the form on reset button
		_onResetButton: function() {
			var that = this,
			reset_button = that.jElement.find('.ipt_fsqm_form_button_container .ipt_fsqm_form_button_reset');
			if ( reset_button.length ) {
				reset_button.on( 'click', function(e) {
					e.preventDefault();
					var input = confirm( iptFSQM.l10n.reset_confirm );
					if ( input ) {
						// Reset the form
						that._restoreForm();
						// Hide the sayt
						that.jElement.find('.ipt_fsqm_form_message_restore').hide();
						// Scroll to top
						that._scrollToPosition( that.jElement, 200, 10 );
					}
				} );
			}
		},

		// initialize buttons for tabs
		_initButtonsForTab: function() {
			var that = this;
			// Remove if not enough tab indices
			if ( this.tabIndices.length === 1 ) {
				this.prev_button.remove();
				this.next_button.remove();
				this.submit_button.button('enable');
			// Initialize if enough tab indices
			} else {
				// Assume this is the first page and do accordingly
				this.prev_button.button('disable');
				this.submit_button.button('disable');
				this.next_button.button('enable');
				this.terms_wrap.hide();

				this.prev_button.on( 'click', function(e) {
					e.preventDefault();
					that._navigatePrevTab();
				} );
				this.next_button.on( 'click', function(e) {
					e.preventDefault();
					that._navigateNextTab();
				} );
			}
			this._refreshButtonsForTab();
		},

		// Change prev button
		_changePrevButton: function( show ) {
			if ( show === undefined ) {
				show = false;
			}

			// No need to do anything if previous button needs to stay hidden
			if ( this.tab_settings.block_previous === true || this.block_prev_on_timer ) {
				this.prev_button.stop(true, true).hide();
				return;
			}

			// Enable
			if ( show ) {
				this.prev_button.button('enable');
				if ( this.tab_settings.hidden_buttons && ! this.prev_button.hasClass('iptUIFCHidden') ) {
					this.prev_button.stop(true, true).fadeIn('fast');
				}
			// Disable
			} else {
				this.prev_button.button('disable');
				if ( this.tab_settings.hidden_buttons && ! this.prev_button.hasClass('iptUIFCHidden') ) {
					this.prev_button.stop(true, true).hide();
				}
			}
		},
		// Change next button
		_changeNextButton: function( show ) {
			if ( show === undefined ) {
				show = false;
			}

			// Enable
			if ( show ) {
				this.next_button.button('enable');
				if ( this.tab_settings.hidden_buttons && ! this.next_button.hasClass('iptUIFCHidden') ) {
					this.next_button.stop(true, true).fadeIn('fast');
				}
			// Disable
			} else {
				this.next_button.button('disable');
				if ( this.tab_settings.hidden_buttons && ! this.next_button.hasClass('iptUIFCHidden') ) {
					this.next_button.stop(true, true).hide();
				}
			}
		},
		// Change submit button
		_changeSubmitButton: function( show ) {
			if ( show === undefined ) {
				show = false;
			}

			// Enable
			if ( show ) {
				this.submit_button.button('enable');
				if ( this.tab_settings.hidden_buttons && ! this.submit_button.hasClass('iptUIFCHidden') ) {
					this.submit_button.stop(true, true).fadeIn('fast');
				}
			// Disable
			} else {
				this.submit_button.button('disable');
				if ( this.tab_settings.hidden_buttons && ! this.submit_button.hasClass('iptUIFCHidden') ) {
					this.submit_button.stop(true, true).hide();
				}
			}
		},
		// Change terms wrap
		_changeTermsWrap: function( show ) {
			if ( show === undefined ) {
				show = false;
			}

			// Enable
			if ( show ) {
				this.terms_wrap.show();
			// Disable
			} else {
				this.terms_wrap.hide();
			}
		},

		// refresh buttons on tab events
		_refreshButtonsForTab: function() {
			var that = this,
			currentIndex = this.main_tab.tabs( 'option', 'active' ), // this.tabIndices.index( this.tabIndices.filter('[aria-selected="true"]') ),
			totalIndices = this.tabIndices.length,

			// Get the index of first tab and last tab
			// All of these tabs can be conditionally hidden
			// So we need to take into account
			firstTabIndex = 0, lastTabIndex = totalIndices - 1;

			while ( this.tabIndices.eq(firstTabIndex).hasClass('iptUIFCHidden') ) {
				firstTabIndex++;
				if ( firstTabIndex >= totalIndices ) {
					firstTabIndex = totalIndices - 1;
					break;
				}
			}
			while ( this.tabIndices.eq(lastTabIndex).hasClass('iptUIFCHidden') ) {
				lastTabIndex--;
				if ( lastTabIndex < 0 ) {
					lastTabIndex = totalIndices - 1;
					break;
				}
			}

			// If last
			if ( currentIndex == lastTabIndex ) {
				// Hide previous button
				// But not if on the first page
				if ( currentIndex != firstTabIndex ) {
					this._changePrevButton( true );
				} else {
					this._changePrevButton( false );
				}
				// Hide the next button
				this._changeNextButton( false );
				// Show submit button
				this._changeSubmitButton( true );
				// Show terms wrap
				this._changeTermsWrap( true );

				// Set page status
				this.on_last_page = true;
				this.on_first_page = false;
			// If first
			} else if ( currentIndex == firstTabIndex ) {
				// Hide previous button
				this._changePrevButton( false );
				// Show the next button
				this._changeNextButton( true );
				// Hide the submit button
				this._changeSubmitButton( false );
				// Hide terms wrap
				this._changeTermsWrap( false );

				// Set page status
				this.on_last_page = false;
				this.on_first_page = true;
			// If somewhere in between
			} else {
				// Show previous button
				this._changePrevButton( true );
				// Show next button
				this._changeNextButton( true );
				// Hide submit button
				this._changeSubmitButton( false );
				// Hide terms wrap
				this._changeTermsWrap( false );

				// Set page status
				this.on_last_page = false;
				this.on_first_page = false;
			}
		},

		// Refresh startup Timer
		_refreshStartupTimer: function() {
			// Check for startup timer
			// and refresh page when done
			if ( this.jElement.find('.ipt_fsqm_form_startup_timer').length ) {
				var startUpTimer = this.jElement.find('.ipt_fsqm_form_startup_timer').TimeCircles();
				startUpTimer.addListener(function( unit, value, total ) {
					if ( total <= 0 ) {
						window.location.reload(true);
					}
				});
			}
		},

		// Skip tab if necessary
		_skipTabIfNecessary: function( ui, indexNew, indexOld ) {
			// Flag the change
			this.skipping_tab_for_conditional = true;
			var returnVal = false;

			if ( ui.newTab.hasClass('iptUIFCHidden') ) {
				var visibleTab = null;
				// If it's a move left request
				if ( indexNew < indexOld ) {
					// Get the nearest visible tab
					visibleTab = ui.newTab.prev('li');
					while ( visibleTab.hasClass('iptUIFCHidden') ) {
						visibleTab = visibleTab.prev('li');

						if ( ! visibleTab.length ) {
							break;
						}
					}
				// If it is a move right request
				} else {
					visibleTab = ui.newTab.next('li');
					while ( visibleTab.hasClass('iptUIFCHidden') ) {
						visibleTab = visibleTab.next('li');

						if ( ! visibleTab.length ) {
							break;
						}
					}
				}

				var newTab = this.tabIndices.index( visibleTab );

				if ( newTab != -1 ) {
					this.main_tab.tabs('option', 'active', newTab);
				}
				// We need to change the tab, so we return true
				returnVal = true;
			}
			// Remove the flag
			this.skipping_tab_for_conditional = false;
			return returnVal;
		},

		// Restore form
		_restoreForm: function() {
			// Activate flag
			this.restoring_form = true;

			// Trigger a DOM reset
			this.main_form.trigger('reset');

			// Some special triggers for special elements
			this.main_form.find('.ipt_uif_slider, .ipt_uif_slider_range_max').val('0').trigger('fsqm.slider');
			this.main_form.trigger('fsqm.mathematicalReEvaluate').trigger('fsqm.check_likedislike').trigger('fsqm.check_smiley');
			this.main_form.find('.ipt_uif_jsignature_reset').trigger('click');

			// Re-evaluate conditional logic
			this.main_form.find('.ipt_uif_conditional').trigger('fsqm.conditional');

			// Reset the tab to the first one
			if ( this.main_tab.length ) {
				this.main_tab.tabs({
					active: 0
				});
			}
			// Restore tab position value
			this.main_form.find('.ipt_fsqm_form_tab_pos').val('0');

			// Erase any Sayt data
			if ( typeof( $.fn.sayt ) != 'undefined' ) {
				this.main_form.sayt({'erase': true});
			}

			// Deactivate flag
			this.restoring_form = false;
			this.main_form.trigger( 'formReset.eform' );
		},

		// Restore Tabs after FSQM has loaded
		// Corresponds to the sayt
		_saytRestoreTab: function() {
			if ( typeof( $.fn.sayt ) == 'undefined' ) {
				return;
			}
			// Activate the flag
			this.sayt_restoring_tab = true;

			// Do only if necessary
			if ( this.sayt_settings !== undefined && this.sayt_settings.admin_override === false && this.sayt_settings.auto_save === true ) {
				if ( this.sayt_settings.restore === true && this.main_form.sayt({'checksaveexists': true}) === true ) {
					// Restore the tab too
					var tab_pos = this.main_form.find('.ipt_fsqm_form_tab_pos').val();
					if ( this.main_tab.length && tab_pos !== undefined ) {
						this.main_tab.tabs({
							active: tab_pos
						});
					}
				}
			}

			// Deactivate the flag
			this.sayt_restoring_tab = false;
		},

		// Restore stopwatch attribute
		// before init
		_restoreStopwatchVal: function() {
			var stopwatchTimer = this.jElement.find('.ipt_fsqm_form_stopwatch'),
			stopwatchTimerVal = this.jElement.find('.ipt_fsqm_form_stopwatch_val');
			if ( stopwatchTimer.length ) {
				stopwatchTimer.attr('data-timer', stopwatchTimerVal.val());
			}
		},

		// End stopwatch
		_endStopwatch: function( hideOnly ) {
			hideOnly = undefined === hideOnly ? false : hideOnly;
			var stopwatchTimer = this.jElement.find('.ipt_fsqm_form_stopwatch'),
			stopwatchTimerVal = this.jElement.find('.ipt_fsqm_form_stopwatch_val');
			if ( stopwatchTimer.length ) {
				if ( hideOnly ) {
					stopwatchTimer.hide();
				} else {
					stopwatchTimer.TimeCircles().destroy();
				}
			}
		},

		// Scroll to a position
		_scrollToPosition: function( elm, duration, negOffset ) {
			// Set variables
			var scrollTo = elm.offset().top,
			remodal_wrap = this.main_form.closest('.remodal-wrapper'),
			htmlMarginTop = parseFloat($('html').css('margin-top')),
			htmlPaddingTop = parseFloat($('html').css('padding-top')),
			scrollElm = $('html, body');

			// Set the default negOffset
			if ( negOffset === undefined ) {
				negOffset = 0;
			}

			// Set the default durations
			if ( duration === undefined ) {
				duration = 200;
			}

			// Check if inside a remodal
			if ( remodal_wrap.length ) {
				scrollTo = elm.position().top - negOffset - 55;
			} else {
				if ( isNaN( htmlMarginTop ) ) {
					htmlMarginTop = 0;
				}
				if ( isNaN( htmlPaddingTop ) ) {
					htmlPaddingTop = 0;
				}
				scrollTo = scrollTo - htmlMarginTop - htmlPaddingTop;
				// Negate the manual offset
				scrollTo -= negOffset;
			}

			// Negate the settings offset
			if ( this.scroll_settings !== undefined ) {
				scrollTo -= this.scroll_settings.offset;
			}

			// Dial it down to zero
			if ( scrollTo < 0 ) {
				scrollTo = 0;
			}

			// Set the scrolling element accordingly
			if ( remodal_wrap.length ) {
				scrollElm = remodal_wrap;
			}

			if ( duration !== 0 ) {
				scrollElm.animate( {scrollTop : scrollTo}, duration );
			} else {
				scrollElm.scrollTop(scrollTo);
			}

		},

		// Scroll to tab
		_scrollToTab: function() {
			if ( this.tab_settings.scroll === false ) {
				return;
			}

			if ( this.tab_settings.type == 2 && this.tab_settings.show_progress_bar === true && false == this.tab_settings.progress_bar_bottom ) {
				this._scrollToPosition( this.main_pb, 200, 10 );
			} else {
				this._scrollToPosition( this.main_tab, 200, 10 );
			}
		},

		// Open collapsed elements
		_openRequiredCollapsedElements: function( container ) {
			var that = this;
			container.find('.ipt_uif_collapsible').each(function() {
				var openIt = false;
				$(this).find('.check_me').each(function() {
					if ( $(this).attr('class').match(/required/) ) {
						openIt = true;
						return false;
					}
				});
				if ( openIt && ! $(this).hasClass('ipt_uif_collapsible_open') ) {
					$(this).find('>.ipt_uif_container_head > h3 > a').trigger('click');
				}
			});
		},
		// Check upload requests
		_checkUploadRequests: function( container ) {
			var that = this,
			passed_validation = true;

			container.find('.ipt_uif_uploader').each(function() {
				if ( ! $(this).is(':visible') ) {
					return true;
				}
				var widget = $(this),
				activeUpload = widget.data( 'activeUpload' ),
				totalUpload = widget.data( 'totalUpload' ),
				uploadSettings = widget.data( 'settings' );

				// Check for active uploads
				if ( activeUpload > 0 ) {
					widget.validationEngine('showPrompt', iptFSQM.l10n.uploader_active_upload, 'red' );
					passed_validation = false;
				}

				// Check for required uploads
				if ( uploadSettings.required === true && totalUpload < 1 ) {
					widget.validationEngine('showPrompt', iptFSQM.l10n.uploader_required, 'red' );
					passed_validation = false;
				}

				// Check for min number of files
				var min_number_of_files = parseInt( uploadSettings.min_number_of_files, 10 );
				if ( isNaN( min_number_of_files ) || min_number_of_files < 0 ) {
					min_number_of_files = 0;
				}
				if ( min_number_of_files > 1 && totalUpload < min_number_of_files ) {
					widget.validationEngine('showPrompt', iptFSQM.l10n.uploader_required_number + ' ' + min_number_of_files, 'red' );
					passed_validation = false;
				}

				if ( passed_validation === false ) {
					// Scroll to required position
					that._scrollToPosition( widget, 200, 50 );
					return false;
				}
			});
			return passed_validation;
		},

		// Process submission
		_processSubmission: function() {
			// Store the reference
			var that = this;

			// Hide some dom elements
			this.main_form.hide();
			this.restore_block.hide();
			this.success.hide();
			this.http_error.hide();

			// Also we hide the errors (if any)
			this.main_form.validationEngine('hideAll');

			// Stop any videos
			this.main_form.iptPluginUIFFront('refreshiFrames');

			// Show and manipulate the process
			this.process.show();
			var process_ajax = this.process.find('.ipt_uif_ajax_loader_inline').css('width', 'auto'),
			init_width = this.process.width(),
			process_width = process_ajax.width() + 50,
			process_height = process_ajax.height();

			// Scroll to the process
			// If settings say so
			if ( that.scroll_settings && true == that.scroll_settings.progress ) {
				that._scrollToPosition( that.process, 10, 10 );
			}

			// Animate the process block
			process_ajax.css({
				width: init_width,
				height: process_height,
				opacity: 0
			}).animate( {
				width: process_width,
				opacity: 1
			}, 'normal' );

			// Proceed with submission
			//
			// Set the flag
			this.fsqm_submitting = true;

			// End the stopwatch
			// Only if reset isn't set to true
			this._endStopwatch( that.formReset && that.formReset.reset );

			// Prepare the submission data
			var data = {
				action: this.main_form.find('[name="action"]').val(),
				ipt_ps_post: this.main_form.serialize(),
				ipt_ps_send_as_str: true,
				ipt_ps_look_into: 'ipt_ps_post'
			};

			// Submit using jQuery POST
			$.post( iptFSQM.ajaxurl, data, function( response ) {
				// First check for any response error
				if ( response === null || response === 0 || response === '0' ) {
					// Show the http error block
					that.http_error.find('.textStatus').html('Null Data');
					that.http_error.find('.errorThrown').html('Possible Server Error');
					that.http_error.slideDown('fast');

					// Show the form
					that.main_form.show();

					// Scroll to the http_error
					// If settings say so
					if ( that.scroll_settings && true == that.scroll_settings.message ) {
						that._scrollToPosition( that.http_error, 200, 10 );
					}

					// Stop execution
					return;
				}

				// If server returns success
				if ( response.success === true ) {
					// Add the success message
					that.success.find('.ui-widget-content.ipt_fsqm_success_wrap').html(response.msg);

					// Slidedown the success message and then do the necessary actions
					that.success.slideDown( 'fast', function() {
						// Scroll to success block
						// If settings say so
						if ( that.scroll_settings && true == that.scroll_settings.message ) {
							that._scrollToPosition( that.success, 200, 10 );
						}

						// Redirect if necessary
						if ( response.components.redirect === true ) {
							// Add the redirect message
							if ( response.components.redirect_msg !== '' ) {
								that.success.find('.ipt_fsqm_sm_meta').remove();
								that.success.find('.ui-widget-content.ipt_fsqm_success_wrap').after( '<div class="ui-widget-content ui-corner-all ipt_fsqm_sm_meta"><p class="ipt_fsqm_sm_meta_p">' + response.components.redirect_msg + '</p></div>' );
							}
							// Apply the CountUp
							if ( that.success.find('.ipt_fsqm_redirection_countdown').length ) {
								var finalTime = response.components.redirect_delay / 1000,
								rcountUp = new CountUp( that.success.find('.ipt_fsqm_redirection_countdown').get(0), finalTime, 0, 0, finalTime, {
									useEasing : false,
									useGrouping : true,
									separator : ',',
									decimal : '.',
									prefix : '',
									suffix : ''
								} );
								rcountUp.start();
							}
							// Set the timeout function for redirect
							setTimeout( function() {
								if ( window.self === window.top || ! response.components.redirect_top ) {
									window.location.href = response.components.redirect_url;
								} else {
									window.top.location.href = response.components.redirect_url;
								}
							}, response.components.redirect_delay );
						}
					} );

					// Clear the nonce interval
					if ( that.nonce_interval !== undefined ) {
						clearInterval( that.nonce_interval );
					}

					// Clear sayt checksaveexists
					if ( that.saytIntervalID ) {
						window.clearInterval( that.saytIntervalID );
					}
					if ( typeof( $.fn.sayt ) != 'undefined' ) {
						that.main_form.sayt({'erase': true});
					}

					// Reset the form if settings say so
					// But no need if it is set to redirect
					// This will improve the UX
					if ( that.formReset && that.formReset.reset && response.components.redirect !== true ) {
						if ( that.formReset.delay > 0 ) {
							var formResetCountUp = new CountUp( that.success.find('.ipt_fsqm_form_reset_cu').get(0), that.formReset.delay, 0, 0, that.formReset.delay, {
								useEasing : false,
								useGrouping : true,
								separator : ',',
								decimal : '.',
								prefix : '',
								suffix : ''
							} );
							formResetCountUp.start();
							setTimeout( function() {
								that._resetFormOnSubmit();
							}, that.formReset.delay * 1000 );
						} else {
							that._resetFormOnSubmit();
						}
					}
					// Set Cookie
					try {
						var totalSubmission = parseInt( Cookies.get( 'eform-submission-' + that.form_id ), 10 );
						if ( isNaN( totalSubmission ) ) {
							totalSubmission = 0;
						}
						Cookies.set( 'eform-submission-' + that.form_id, ++totalSubmission, { expires: 365, path: iptFSQM.core.siteurl } );
					} catch ( e ) {
						if ( console && console.log ) {
							console.log( e );
						}
					}
				// If server returns error
				} else {
					// Process the email and ip limitation beforehand
					if ( 'object' == typeof( response.errors ) ) {
						var k;
						for ( k in response.errors ) {
							if ( 'fsqm_email_limit' == response.errors[ k ].id || 'fsqm_ip_limit' == response.errors[ k ].id ) {
								// Set the heading
								that.validation_block.find( '.fsqm_ve_text' ).html( response.errors[ k ].msgs[0] );
								// Set the message
								that.validation_block.find( '.fsqm_ve_msg' ).html( response.errors[ k ].msgs[1] );
								// Show it
								that.validation_block.show();
								that.validation_block.addClass('iptAnimated iptPulseSubtle');
								// Nothing else needed
								return;
							}
						}
					}
					// Show the form
					that.main_form.show();

					// Show the errors if any
					if ( typeof( response.errors ) == 'object' ) {
						var show_alert = false,
						i, msgs, error_to;

						// Loop through all errors and set prompt accordingly
						for ( i = 0; i < response.errors.length; i++ ) {
							msgs = response.errors[i].msgs.join('<br />');
							// If ID is associated with the errors
							if ( response.errors[i].id !== '' ) {
								error_to = $('#' + response.errors[i].id);
								// If element exists
								if ( error_to.length ) {
									show_alert = true;
									error_to.validationEngine( 'showPrompt', msgs, 'red' );
									error_to.closest('.ipt_uif_column_inner').css({
										position: 'relative'
									});
								// Otherwise show to the form
								} else {
									that.main_form.validationEngine( 'showPrompt', msgs, 'red' );
								}
							// Just show to the form
							} else {
								that.main_form.validationEngine( 'showPrompt', msgs, 'red' );
							}
						}

						// Navigate to the first error elements tab
						if ( that.main_tab.length !== 0 ) {
							var tabPanel;
							that.main_tab.find('> .ipt_fsqm_form_tab_panel').each(function() {
								if ( $(this).find('.formErrorContent').length ) {
									tabPanel = $(this);
									return false;
								}
							});
							if ( tabPanel !== undefined && tabPanel.length ) {
								var tabIndex = that.tabIndices.index( that.tabIndices.filter( '[aria-controls="' + tabPanel.attr('id') + '"]' ) ),
								show_inside_alert = show_alert;
								show_alert = false;
								that.changing_tab_on_submit_error = true;
								that.main_tab.tabs( 'option', 'active', tabIndex );
								that.changing_tab_on_submit_error = false;
								setTimeout( function() {
									that._scrollToPosition( tabPanel.find('.formErrorContent').eq(0), 200, 10 );
									// A nasty hack to show the alert after tab change
									if ( show_inside_alert ) {
										alert( iptFSQM.l10n.validation_on_submit );
									}
								}, 500 );
							}
						}

						// Show alert if needed
						if ( show_alert ) {
							alert( iptFSQM.l10n.validation_on_submit );
						}
					}
				}
			}, 'json' ).fail(function( jqXHR, textStatus, errorThrown ) {
				// Show the http error block
				that.http_error.find('.textStatus').html( textStatus );
				that.http_error.find('.errorThrown').html( errorThrown.message );
				that.http_error.slideDown('fast');

				// Show the form
				that.main_form.show();

				// Scroll to the http_error
				// If settings say so
				if ( that.scroll_settings && true == that.scroll_settings.message ) {
					that._scrollToPosition( that.http_error, 200, 10 );
				}
			}).always(function() {
				// hide the process
				that.process.hide();
				that.fsqm_submitting = false;
			});
		},

		_resetFormOnSubmit: function() {
			var that = this;
			// We shall reinit the nonce interval
			// Clear out all filled data
			// Clear sayt save (if any)
			// Restore the tab

			// Check for timer events
			if ( this.timerVar ) {
				this._destroyTimer();
				this._initTimer();
			}

			// Apply the Nonce events
			this.applyNonceEvents();

			// Reset the form
			this._restoreForm();
			// Hide the sayt
			this.jElement.find('.ipt_fsqm_form_message_restore').hide();
			if ( this.sayt_settings && true === this.sayt_settings.interval_save ) {
				// Do interval save
				// We do by closure because of accessing this
				if ( this.sayt_settings.interval > 0 ) {
					this.saytIntervalID = window.setInterval( function() {
						that.saytManualSave();
					}, ( this.sayt_settings.interval * 1000 ) );
				}
			}
			// Show the form
			this.main_form.fadeIn('fast');
			this.restore_block.hide();
			this.success.hide();
			this.http_error.hide();

			// Scroll to top
			this._scrollToPosition( this.jElement, 200, 10 );

			// Apply Stopwatch
			this.jElement.find('.ipt_fsqm_form_stopwatch_val').val(0);
			var stopwatchTimer = this.jElement.find('.ipt_fsqm_form_stopwatch');
			if ( stopwatchTimer.length ) {
				stopwatchTimer.show();
				stopwatchTimer.TimeCircles().restart();
				// stopwatchTimer.TimeCircles().rebuild();
			}
		},

		_getElementValues: function( conditional_div, check_type ) {
			var return_obj = [],
			that = this,
			selected_state, m_columns, m_check_index, selectedOption,
			smileyVals = {
				frown: 1,
				sad: 2,
				neutral: 3,
				happy: 4,
				excited: 5
			},
			likeDislikeState = {
				like: 1,
				dislike: 0
			};
			switch( check_type ) {
				// Radios
				case 'radio' :
				case 'p_radio' :
					return_obj = [];
					conditional_div.find('input.ipt_uif_radio').filter(':checked').each( function() {
						return_obj[return_obj.length] = jQuery.trim($(this).next('label').text());
					} );
					break;

				// Checkboxes
				case 'checkbox' :
				case 'p_checkbox' :
					return_obj = [];
					conditional_div.find('input.ipt_uif_checkbox').filter(':checked').each( function() {
						return_obj[return_obj.length] = jQuery.trim($(this).next('label').text());
					} );
					break;

				case 'select' :
				case 'p_select' :
					return_obj = [];
					conditional_div.find('select.ipt_uif_select option').filter(':selected').each( function() {
						return_obj[return_obj.length] = jQuery.trim($(this).text());
					} );
					break;

				case 'thumbselect' :
					return_obj = [];
					conditional_div.find('input.ipt_uif_radio, input.ipt_uif_checkbox').filter(':checked').each( function() {
						return_obj[return_obj.length] = jQuery.trim($(this).data('label'));
					} );
					break;

				case 'slider' :
					return_obj = that.intelParseFloat( conditional_div.find('input.ipt_uif_slider').val() );
					break;

				case 'range' :
					return_obj = [that.intelParseFloat( conditional_div.find('input.ipt_uif_slider.slider_range').val() ), that.intelParseFloat( conditional_div.find('input.ipt_uif_slider.slider_range').next('input').val() )];
					break;

				case 'spinners' :
					return_obj = [];
					conditional_div.find( 'input.ipt_uif_uispinner' ).each(function() {
						if ( $(this).val() !== '' ) {
							return_obj[return_obj.length] = that.intelParseFloat( $(this).val() );
						}
					});
					break;

				case 'grading' :
					return_obj = [];
					conditional_div.find('input.ipt_uif_slider').each(function() {
						if ( $(this).val() !== '' ) {
							return_obj[return_obj.length] = that.intelParseFloat( $(this).val() );
						}
						if ( $(this).hasClass('slider_range') && $(this).next('input').val() ) {
							return_obj[return_obj.length] = that.intelParseFloat( $(this).next('input').val() );
						}
					});
					break;

				case 'starrating' :
				case 'scalerating' :
					return_obj = [];
					conditional_div.find('.ipt_uif_rating').each(function() {
						if ( $(this).find('input.ipt_uif_radio:checked').length ) {
							return_obj[return_obj.length] = that.intelParseFloat( $(this).find('input.ipt_uif_radio:checked').val() );
						}
					});
					break;

				case 'matrix' :
					return_obj = [];

					// First get the column heads
					m_columns = [];
					conditional_div.find('.ipt_uif_matrix thead th').each(function() {
						m_columns[m_columns.length] = jQuery.trim($(this).text());
					});
					conditional_div.find('.ipt_uif_checkbox,.ipt_uif_radio').filter(':checked').each(function() {
						m_check_index = $(this).closest('tr').find('> *').index( $(this).closest('td') );
						if ( m_columns[m_check_index] !== '' || m_columns[m_check_index] !== undefined ) {
							return_obj[return_obj.length] = m_columns[m_check_index];
						}
					});
					break;
				case 'toggle' :
				case 's_checkbox' :
					return_obj = conditional_div.find('input[type="checkbox"]').is(':checked') ? '1' : '0';
					break;

				case 'smileyrating' :
					selected_state = conditional_div.find('input[type="radio"]:checked').val();
					if ( smileyVals[selected_state] !== undefined ) {
						return_obj = smileyVals[selected_state];
					}
					break;

				case 'likedislike' :
					selected_state = conditional_div.find('input[type="radio"]:checked').val();
					if ( likeDislikeState[selected_state] !== undefined ) {
						return_obj = likeDislikeState[selected_state];
					}
					break;

				case 'matrix_dropdown' :
					return_obj = [];
					conditional_div.find('select').each(function() {
						selectedOption = $(this).find('option').filter(':selected');
						if ( selectedOption.val() !== '' ) {
							return_obj[return_obj.length] = selectedOption.text();
						}
					});
					break;

				case 'feedback_small' :
				case 'f_name' :
				case 'l_name' :
				case 'email' :
				case 'phone' :
				case 'p_name' :
				case 'p_email' :
				case 'p_phone' :
				case 'textinput' :
				case 'password' :
				case 'keypad' :
				case 'datetime' :
					return_obj = conditional_div.find('input[type="text"]').val();
					// For keypad it can be a text area as well
					if ( return_obj === undefined && check_type == 'keypad' )  {
						return_obj = conditional_div.find('textarea').val();
					}
					// Parse to float just in case
					if ( that.isNumeric( return_obj ) ) {
						return_obj = that.intelParseFloat( return_obj );
					}
					break;

				case 'feedback_large' :
				case 'textarea' :
					return_obj = conditional_div.find('textarea').val();
					break;

				case 'upload' :
					return_obj = conditional_div.find('.ipt_uif_uploader').data('totalUpload');
					break;

				case 'mathematical' :
					return_obj = that.intelParseFloat( conditional_div.find('input.ipt_uif_mathematical_input').val() );
					break;
				case 'address' :
					return_obj = [];
					conditional_div.find('.ipt_uif_text').each(function() {
						return_obj[return_obj.length] = $(this).val();
					});
					break;
				default :
					return_obj = false;
					break;
			}

			return return_obj;
		},

		_checkForReCaptcha: function() {
			if ( this.reCaptchaNeeded && false == this.jElement.data( 'reCaptchaValidated' ) && this.jElement.find( '.g-recaptcha' ).is( ':visible' ) ) {
				this.jElement.find( '.g-recaptcha' ).validationEngine('showPrompt', iptFSQM.l10n.recaptcha, 'red' );
				this._scrollToPosition( this.jElement.find( '.g-recaptcha' ) )
				return false;
			}
			return true;
		},

		intelParseFloat: function( num, default_val ) {
			if ( default_val === undefined ) {
				default_val = 0;
			}
			var parsedNum = parseFloat( num );
			if ( isNaN( parsedNum ) ) {
				parsedNum = default_val;
			}
			return parsedNum;
		},

		isNumeric: function( num ) {
			return !isNaN( parseFloat( num ) ) && isFinite( num );
		},

		yourOtherFunction: function () {
			// some logic
		}
	};

	var methods = {
		init: function( options ) {
			return this.each(function() {
				if ( !$.data( this, "plugin_" + pluginName ) ) {
					$.data( this, "plugin_" + pluginName, new Plugin( this, options ) );
				}
			});
		}
	};

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[ pluginName ] = function ( method ) {
		if( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( typeof( method ) == 'object' || !method ) {
			methods.init.apply(this, arguments);
		} else {
			$.error( 'Method ' + method + ' does not exist on jQuery.' + pluginName );
		}

		// chain jQuery functions
		return this;
	};

})( jQuery, window, document );

jQuery(document).ready(function($) {
	$('.ipt_fsqm_form').iptFSQMForm();
});
