/**
 * WPQuark Plugin Framework
 *
 * This is a jQuery plugin which works on the plugin framework to populate the UI
 * Front area
 *
 * @dependency jquery, jquery-ui-widget, jquery-ui-mouse, jquery-ui-button, jquery-touch-punch, jquery-ui-draggable, jquery-ui-droppable,
 *  jquery-ui-sortable, jquery-ui-datepicker, jquery-ui-dialog, jquery-ui-tabs, jquery-ui-slider, jquery-ui-spinner, jquery-ui-progressbar,
 *  jquery-timepicker-addon, jquery-print-element, jquery-mwheelIntent, jquery-mousewheel, jquery-time-circles, jquery-ui-slider-pips,
 *  jquery-ui-autocomplete, ipt-plugin-uif-keyboard, ipt-plugin-uif-validation-engine, ipt-plugin-uif-validation-engine-lang,
 *  ipt-plugin-uif-nivo-slider, ipt-plugin-uif-typewatch, ipt-plugin-uif-fileupload-process, ipt-plugin-uif-fileupload-image,
 *  ipt-plugin-uif-fileupload-audio, ipt-plugin-uif-fileupload-video, ipt-plugin-uif-fileupload-validate, ipt-plugin-uif-fileupload-ui,
 *  ipt-plugin-uif-fileupload-jquery-ui, waypoints, js-expression-evaluator, count-up, jquery-tooltipster, jsignature, jessepollak-card,
 *  jquery-payment, ba-throttle-debounce
 *
 * @author     Swashata@iPanelThemes.com
 * @version 2.0.0
 * @license    Themeforest Split
 */

;(function ( $, window, document, undefined ) {
	"use strict";
	// Create the defaults once
	var pluginName = "iptPluginUIFFront",
	defaults = {
		callback : null,
		themeCheckTimeout : 5000,
		additionalThemes : [],
		waypoints : true,
		applyUIOnly : false,
		debug: false
	};

	// Some window functions
	//Captcha check function
	window.ipt_uif_front_captcha = function(field, rules, i, options) {
		if($(field).val() != $(field).data('sum')) {
			return iptPluginUIFFront.L10n.validationEngine.requiredInFunction.alertText + $(field).data('sum');
		}
	};

	// Image check validation
	window.iptUIFSigVal = function(field, rules, i, options) {
		if ( jQuery.inArray( 'required', rules ) !== -1 && ( $(field).val() === '' || $(field).val() == 'image/jsignature;base30,' ) ) {
			return iptPluginUIFFront.L10n.validationEngine.requiredSignature.alertText;
		}
	};

	// Slider Check validation
	window.iptUIFSliderVal = function(field, rules, i, options) {
		var jField = $(field);
		if ( jField.data('nomin') == 1 ) {
			// if it is just slider
			if ( ! jField.hasClass('slider_range') ) {
				if ( jField.val() == jField.data('min') ) {
					return iptPluginUIFFront.L10n.validationEngine.noMinSlider.alertText;
				}
			// In case of range
			} else {
				if ( jField.val() == jField.data('min') && jField.siblings('.ipt_uif_slider_range_max').val() == jField.data('min') ) {
					return iptPluginUIFFront.L10n.validationEngine.noMinSlider.alertText;
				}
			}
		}
	};

	// CC check validation
	window.iptUIFValidateCC = function(field, rules, i, options) {
		var type, expiry;
		if ( field.hasClass('ipt_uif_cc_number') ) {
			if ( $.payment.validateCardNumber( field.val() ) ) {
				// Also set the type
				type = $.payment.cardType( field.val() );
				if ( type !== null ) {
					field.closest('.ipt_uif_card_holder').find('.ipt_uif_cc_type').val(type);
					// Set the CSS class
					field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-identified jp-card-' + type);
				} else {
					// Set the CSS class
					field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-unknown');
					return iptPluginUIFFront.L10n.validationEngine.ccValidation.type;
				}
				return true;
			} else {
				type = $.payment.cardType( field.val() );
				if ( type !== null ) {
					// Set the CSS class
					field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-identified jp-card-' + type);
				} else {
					// Set the CSS class
					field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-unknown');
				}
				// field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-unknown');
				return iptPluginUIFFront.L10n.validationEngine.ccValidation.number;
			}
		} else if ( field.hasClass('ipt_uif_cc_cvc') ) {
			type = field.closest('.ipt_uif_card_holder').find('.ipt_uif_cc_type').val();
			if ( $.payment.validateCardCVC( field.val(), type ) ) {
				return true;
			} else {
				return iptPluginUIFFront.L10n.validationEngine.ccValidation.cvc;
			}
		} else if ( field.hasClass('ipt_uif_cc_expiry') ) {
			// First get the month and year
			expiry = field.payment('cardExpiryVal');
			if ( $.payment.validateCardExpiry( expiry.month, expiry.year ) ) {
				return true;
			} else {
				return iptPluginUIFFront.L10n.validationEngine.ccValidation.expiry;
			}
		}
		return true;
	};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		this.jElement = $(element);
		// jQuery has an extend method which merges the contents of two or
		// more objects, storing the result in the first object. The first object
		// is generally empty as we don't want to alter the default options for
		// future instances of the plugin
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this.ui_theme_id = 'ipt-uif-custom-none';
		this.ui_theme_slug = 'none';
		this.init();
	}

	Plugin.prototype = {
		// Initialization Logic
		init: function () {
			// Append the default theme
			var links = [], link_ui, i, self = this.jElement;
			if ( $('#ipt_uif_default_theme_link-css').length ) {
				links[ links.length ] = $('#ipt_uif_default_theme_link-css').get(0);
			}
			// Check for IE8 YUCK
			if ( $.support.opacity === false ) {
				if ( $('#ipt_uif_ie8_hack').length ) {
					links[links.length] = $('#ipt_uif_ie8_hack').get(0);
				} else {
					link_ui = $('<link id="#ipt_uif_ie8_hack" rel="stylesheet" media="all" type="text/css" href="' + iptPluginUIFFront.location + 'css/ie8.css?version=' + iptPluginUIFFront.version + '" />');
					$('body').append(link_ui);
					links[links.length] = link_ui.get(0);
				}
			}
			// Append additionalThemes
			if ( this.settings.additionalThemes.length ) {
				for ( i = 0; i < this.settings.additionalThemes.length; i++ ) {
					if ( typeof( this.settings.additionalThemes[i] ) == 'object' && 'id' in this.settings.additionalThemes[i] && 'src' in this.settings.additionalThemes[i] ) {
						if ( $('#' + this.settings.additionalThemes[i].id + '-css').length ) {
							links[links.length] = $('#' + this.settings.additionalThemes[i].id + '-css').get(0);
						} else {
							link_ui = $('<link id="' + this.settings.additionalThemes[i].id + '-css' + '" rel="stylesheet" media="all" type="text/css" href="' + this.settings.additionalThemes[i].src + '" />');
							$('body').append(link_ui);
							links[links.length] = link_ui.get(0);
						}
					}
				}
			}

			// Load html-data defined themes if present
			if ( self.data('ui-theme') && self.data('ui-theme-id') ) {
				// Get the data
				var ui_themes = self.data('ui-theme'),
				ui_theme_id = self.data('ui-theme-id');

				this.ui_theme_id = 'ipt-uif-custom-' + ui_theme_id;
				this.ui_theme_slug = ui_theme_id;

				// Append the class
				self.addClass('ipt-uif-custom-' + ui_theme_id);

				//Init the link elements
				var themes_to_append = [], link_element;

				// Check what we need to append
				if ( typeof( ui_themes ) == 'object' && ui_themes.length ) {
					for ( i = 0; i < ui_themes.length; i++ ) {
						link_element = $(document).find('#' + ui_theme_id + '_' + i + '-css');
						if ( ! link_element.length ) {
							themes_to_append[themes_to_append.length] = i;
						} else {
							links[links.length] = link_element.get(0);
						}
					}
				}

				// If needed then append it
				if ( themes_to_append.length ) {
					// Store the DOM Objs
					var ui_theme_element;
					for( i = 0; i < themes_to_append.length; i++ ) {
						ui_theme_element = $('<link media="all" id="' + ui_theme_id + '_' + i + '-css' + '" type="text/css" rel="stylesheet" href="' + ui_themes[themes_to_append[i]] + '" />');
						links[links.length] = ui_theme_element.get(0);
						$('body').append(ui_theme_element);
					}
				}
			} else {
				// Add the closest theme
				var closestParentUI = self.closest( '[data-ui-theme-id]' );
				if ( closestParentUI.length ) {
					this.ui_theme_slug = closestParentUI.data( 'ui-theme-id' );
					this.ui_theme_id = 'ipt-uif-custom-' + this.ui_theme_slug;
				}
			}

			// Now call the loadthemes
			this.loadThemes( links );
		},
		// Load the themes
		loadThemes: function( links ) {
			// Check if there are some links
			if ( ! links.length ) {
				this.afterThemeLoaded();
				return;
			}

			// Crossbrowser compatibility
			var sheet, cssRules, that = this;
			if ( 'sheet' in links[0] ) {
				sheet = 'sheet'; cssRules = 'cssRules';
			} else {
				sheet = 'styleSheet'; cssRules = 'rules';
			}

			// Set the interval
			var interval_id = setInterval(function() {
				var all_done = true, i;
				for ( i = 0; i < links.length; i ++ ) {
					if ( links[i][sheet] !== undefined && links[i][sheet] !== null && cssRules in links[i][sheet] ) {
						try {
							if ( ! links[i][sheet][cssRules].length ) {
								all_done = false;
								break;
							}
						} catch(e) {
							all_done = false;
						}
					} else {
						all_done = false;
						break;
					}
				}
				if ( all_done ) {
					clearInterval(interval_id);
					clearTimeout(timeout_id);
					that.afterThemeLoaded();
				}
			}, 300);

			//Set the timeout
			var timeout_id = setTimeout(function() {
				clearInterval(interval_id);
				clearTimeout(timeout_id);
				// We just call the callback
				that.afterThemeLoaded();
			}, that.settings.themeCheckTimeout);
		},

		// When the themes are loaded
		// Apply the UI elements
		afterThemeLoaded: function() {
			// Here we try to remove conflict between bootstrap and jquery ui button
			if ( $.fn.button.noConflict ) {
				$.fn.btn = $.fn.button.noConflict();
			}
			// Add CSS classes
			var self = this.jElement, i, blueimp_container;
			self.addClass('ipt_uif_common');

			// Show/Hide the hiddenbox/loader
			this.initLoader();

			// Setup blueimp container
			// Set the blueimp container
			if ( typeof( blueimp ) != 'undefined' && blueimp.Gallery ) {
				blueimp_container = $('#blueimp-gallery');
				if ( blueimp_container.length === 0 ) {
					$('body').append('<div data-filter=":even" class="blueimp-gallery blueimp-gallery-controls" id="blueimp-gallery" style="display: none;">' +
						'<div class="slides" style="width: 21600px;"></div>' +
						'<h3 class="title">Dummy.jpg</h3>' +
						'<a class="prev">‹</a>' +
						'<a class="next">›</a>' +
						'<a class="close">×</a>' +
						'<a class="play-pause"></a>' +
						'<ol class="indicator"></ol>' +
					'</div>');
				}
			}


			// Start applying the UI Elements
			// Apply UI only if the settings say so
			if ( this.settings.applyUIOnly === true ) {
				this.initUIElements();
				this.initSDA( true );
				this.initConditionalLogic();
				// Trigger complete
				this.triggerCompleted();
				// Apply the callback
				if( typeof( this.settings.callback ) == 'function') {
					this.settings.callback.apply( this.jElement, [this.ui_theme_id] );
				}
				return;
			}

			// Otherwise apply everything

			// Call the UI elements
			this.initUIElements();

			// Call the delegation functions
			this.initUIElementsDelegated();

			// Call the SDA
			this.initSDA( false );

			// Call the Conditional Logic
			this.initConditionalLogic();

			// Trigger complete
			this.triggerCompleted();

			// Apply the callback
			if( typeof( this.settings.callback ) == 'function') {
				this.settings.callback.apply( this.jElement, [this.ui_theme_id] );
			}
		},

		triggerCompleted: function() {
			this.jElement.trigger( 'completedUI.eform' );
			this.jElement.data( 'eFormUICompleted', true );
		},

		// Just a safe log to console
		debugLog: function( variable, warning ) {
			if ( warning === undefined ) {
				warning = false;
			}
			try {
				if ( console ) {
					if ( warning ) {
						console.warn( variable );
					} else {
						console.log( variable );
					}
				}
			} catch( e ) {

			}
		},


		/**
		 * Initialize the Sortable/Deletable/Addable
		 *
		 * @method     initSDA
		 */
		initSDA: function( forUI ) {
			var that = this;
			this.jElement.find('.ipt_uif_sda').each(function() {
				// Initialize the SDA UI
				that.uiSDAinit.apply(this);

				// Initialize the sortables for SDA
				that.uiSDAsort.apply(this);
			});
			if ( forUI === true ) {
				return;
			}

			// Call the delegatory methods
			that.edSDAattachAdd();
			that.edSDAattachDel();
		},

		/**
		 * Initialize the loader
		 *
		 * Basically it hides the init_loader and shows the hidden_init
		 * @return void
		 */
		initLoader: function() {
			// Hide the loader and show the hidden object
			this.jElement.find('.ipt_uif_init_loader').hide();
			this.jElement.find('.ipt_uif_hidden_init').show().css( { opacity: 1, visibility: 'visible' } );

			// Show any messages
			this.jElement.find('.ipt_uif_message').show();

			// Trigger a resize
			setTimeout( function() {
				$( window ).trigger( 'resize' );
			}, 500 );
		},

		/**
		 * Initialize the conditional logic
		 * @return void
		 */
		initConditionalLogic: function() {
			var that = this, conditionals = {}, do_conditional = true, elm_id, status;
			// Flag for conditional show restore
			this.conditionalInit = true;

			try {
				conditionals = JSON.parse( this.jElement.find('.ipt_uif_conditional_logic').val() );
			} catch( e ) {
				do_conditional = false;
			}

			if ( ! do_conditional ) {
				return;
			}

			// Hide everything that should be hidden at first
			// But soft-hide it, i.e, don't reset the values
			// and don't add the class iptUIFCHidden
			// Because it would need to pass through conditional logic one more time
			// to get proper values
			for ( elm_id in conditionals.logics ) {
				status = conditionals.logics[elm_id].status;
				if ( status === false ) {
					$('#' + elm_id).hide();
					if ( $('#' + elm_id).attr('aria-controls') ) {
						$('#' + $('#' + elm_id).attr('aria-controls')).hide();
					}
				}
			}

			// Attach the event listeners
			if ( that.settings.applyUIOnly !== true ) {
				that.edConditionalLogicAttachEvent( conditionals );
			}

			// Trigger the change event for all conditional events
			that.jElement.find( '.ipt_uif_conditional' ).trigger( 'change' );

			that.jElement.find( '.ipt_uif_text, .ipt_uif_textarea' ).typeWatch({
				callback: function() {
					$(this).trigger( 'fsqm.conditional' );
				},
				wait: 750,
				highlight: false,
				captureLength: 1
			});

			// Reset the flag
			this.conditionalInit = false;
		},

		/**
		 * Attach event listeners for conditional logics
		 *
		 * @method     edConditionalLogicAttachEvent
		 */
		edConditionalLogicAttachEvent: function( conditionals ) {
			var that = this;

			that.jElement.on( 'change fsqm.conditional', function(e) {
				// Get the target
				var target = $(e.target),
				conditional_selector = target.closest('.ipt_uif_conditional'), // Parent conditional div
				selector_index = conditional_selector.attr( 'id' ), // The ID of the div is the selector index
				target_index, logics_of_element, target_element;

				if ( selector_index && conditionals.indexes[selector_index] !== undefined ) { // Check if it has impact on certain logics
					for ( target_index in conditionals.indexes[selector_index] ) { // Loop through all indexes
						// The conditional logic of the target element
						logics_of_element = conditionals.logics[conditionals.indexes[selector_index][target_index]],
						target_element = $('#' + conditionals.indexes[selector_index][target_index]);

						// There is nothing to do if the target element doesn't exist
						if ( ! target_element.length ) {
							return;
						}

						// Validate all logics
						if ( that.validateLogic.apply( that, [ conditionals.base, logics_of_element.logic, logics_of_element.relation ] ) ) {
							// Matched so change the state
							if ( logics_of_element.change === true ) {
								that.conditionalShowElement.apply(target_element, [that]);
							} else {
								that.conditionalHideElement.apply(target_element, [that]);
							}
						} else {
							// Not matched, so revert to inital state
							if ( logics_of_element.status === true ) {
								that.conditionalShowElement.apply(target_element, [that]);
							} else {
								that.conditionalHideElement.apply(target_element, [that]);
							}
						}
					}
				}
			} );
		},

		/**
		 * Conditionally Show an element
		 * Does not perform any check, just shows and triggers necessary stuff
		 *
		 * @method     conditionalShowElement
		 */
		conditionalShowElement: function( that ) {
			var _self = this;
			// Don't do anything if it is already visible
			if ( _self.is(':visible') ) {
				if ( _self.hasClass('iptUIFCHidden') ) {
					_self.trigger('iptUIFCShow');
				}
				_self.stop( true, true ).show().removeClass('iptUIFCHidden');
				return;
			}
			if ( _self.hasClass('iptUIFCHidden') ) {
				if ( _self.find('.ipt-eform-hidden-field').length ) {
					_self.find('.ipt-eform-hidden-field').each( function() {
						$(this).val( $(this).data( 'eformDefaultValue' ) );
						$(this).trigger( 'change' );
					} );
				}
			}
			_self.stop( true, true ).removeClass('iptUIFCHidden').slideDown('fast').addClass('iptAnimated iptAppear');
			if ( true !== that.conditionalInit ) {
				that.conditionalRestoreDefault( _self );
			}
			setTimeout(function() {
				_self.removeClass('iptAnimated iptAppear');
				_self.removeClass('iptFadeInLeft').css( {
					opacity: ''
				} );
				that.refreshiFrames.apply(_self);
				_self.trigger('iptUIFCShow');
				if ( _self.find( '.ipt_uif_mathematical' ).length ) {
					_self.trigger( 'fsqm.mathematicalReEvaluate' );
				}
			}, 200);
		},

		/**
		 * Conditionally hides an element
		 * Does not perform any check, just hides the element and triggers necessary stuff
		 *
		 * @method     conditionalHideElement
		 */
		conditionalHideElement: function() {
			var _self = this, resetPaymentRadio, resetPaymentRadioRadio, resetCBRadio, resetText, resetSlider, resetSelect, resetjSignature, resetHidden, resetTrum;
			if ( ! _self.hasClass('iptUIFCHidden') ) {
				// Reset all data
				resetPaymentRadio = _self.find( '.ipt_fsqm_payment_method_radio' );
				resetPaymentRadioRadio = _self.find( '.ipt_fsqm_payment_method_radio .ipt_uif_radio' ).filter(':checked');
				if ( resetPaymentRadio.length ) {
					if ( resetPaymentRadioRadio.length ) {
						resetPaymentRadio.data( 'iptfsqmpp', resetPaymentRadioRadio.val() );
					}
				}
				resetCBRadio = _self.find('input[type="checkbox"], input[type="radio"]');
				if ( resetCBRadio.length ) {
					resetCBRadio.prop( 'checked', false ).trigger('change');
				}
				resetText = _self.find('input[type="text"], textarea, input[type="password"], input[type="number"], input[type="email"], input[type="tel"]');
				if ( resetText.length ) {
					resetText.val('').trigger('change');
				}

				resetSlider = _self.find('.ipt_uif_slider');
				if ( resetSlider.length ) {
					resetSlider.val('').trigger('change');
					resetSlider.each( function() {
						var range = $( this ).siblings( 'input' );
						if ( range.length ) {
							range.val( '' ).trigger( 'change' );
						}
					} );
				}

				resetSelect = _self.find('select');
				if ( resetSelect.length ) {
					resetSelect.each(function() {
						var elm = $(this);
						elm.val( elm.prop( 'defaultSelected' ) );
						elm.trigger('change');
					});
				}

				resetjSignature = _self.find('.ipt_uif_jsignature_reset');
				if ( resetjSignature.length ) {
					resetjSignature.trigger('click');
				}

				resetHidden = _self.find('.ipt-eform-hidden-field');
				if ( resetHidden.length ) {
					resetHidden.each( function() {
						var elm = $( this );
						elm.data( 'eformDefaultValue', elm.val() );
						elm.val( '' );
						elm.trigger( 'change' );
					} );
				}

				resetTrum = _self.find( '.ipt-eform-trumbowyg' );
				if ( resetTrum.length ) {
					resetTrum.trumbowyg('empty');
					_self.find( '.ipt-eform-guestpost' ).trigger( 'change' );
				}
			}
			// Don't do anything if it is already hidden
			if ( ! _self.is(':visible') ) {
				var triggerChange = false;
				if ( ! _self.hasClass('iptUIFCHidden') ) {
					triggerChange = true;
				}
				// But we will hide it anyway because it might as well be in a different container
				_self.stop( true, true ).hide().addClass('iptUIFCHidden');
				// We wouldn't do the animations though
				if ( triggerChange ) {
					_self.trigger('iptUIFCHide');
				}
				return;
			}
			_self.addClass('iptAnimated iptDisappear iptUIFCHidden').stop( true, true ).fadeOut('fast');
			if ( _self.attr('aria-controls') ) {
				$('#' + _self.attr('aria-controls')).hide();
			}
			setTimeout(function() {
				_self.removeClass('iptAnimated iptDisappear').hide();
				_self.trigger('iptUIFCHide');
				if ( _self.find( '.ipt_uif_mathematical' ).length ) {
					_self.trigger( 'fsqm.mathematicalReEvaluate' );
				}
			}, 500);
		},

		/**
		 * Restores HTML DOM default on conditional show
		 *
		 * @param      {jQuery Object}  _self   The self
		 */
		conditionalRestoreDefault: function( _self ) {
			// Restore default values
			var selectorForValue = 'input[type="text"], textarea, input[type="password"], input[type="number"], input[type="email"], input[type="tel"]',
			selectorForChecked = 'input[type="radio"], input[type="checkbox"]',
			selectorForOption = 'select';
			_self.find( selectorForValue ).each( function() {
				var elm = $( this );
				if ( elm.prop( 'defaultValue' ) ) {
					elm.val( elm.prop( 'defaultValue' ) );
					elm.trigger( 'change' ).trigger( 'updateTextFields.eform' );
					if ( elm.hasClass( 'ipt_uif_slider' ) ) {
						elm.trigger( 'fsqm.slider' );
					}
					if ( elm.hasClass( 'ipt_uif_slider_range_max' ) ) {
						elm.trigger( 'fsqm.slider' );
					}
				}
			} );
			_self.find( selectorForChecked ).each( function() {
				var elm = $( this ),
				currentState = elm.prop( 'checked' ),
				changedState = false;
				if ( true == elm.prop( 'defaultChecked' ) ) {
					elm.prop( 'checked', true );
					changedState = true;
				} else {
					elm.prop( 'checked', false );
				}
				if ( currentState != changedState ) {
					elm.trigger( 'change' );
				}
			} );
			_self.find( selectorForOption ).each( function() {
				var elm = $( this ),
				options = elm.find( 'option' ),
				currentValue = elm.val(),
				changedValue = false;
				if ( null == currentValue ) {
					currentValue = '';
				}
				if ( typeof( currentValue ) != 'object' ) {
					currentValue = [ currentValue ];
				}
				options.each( function() {
					var option = $( this ),
					value = option.val();
					if ( true == option.prop( 'defaultSelected' ) ) {
						if ( ! $.inArray( value, currentValue ) ) {
							changedValue = true;
						}
						option.prop( 'selected', true );
					} else {
						option.prop( 'selected', false );
					}
				} );

				if ( changedValue ) {
					elm.trigger( 'change' );
				}
			} );
			// Apply Mathematical calculator
			// at some delay
			setTimeout( function() {
				_self.trigger( 'fsqm.mathematicalReEvaluate' );
			}, 500 );
		},

		/**
		 * Validates logic for given element
		 *
		 * @method     validateLogic
		 * @param      {string}  base    { ID of the form with which element ID is constructed  }
		 * @param      {<type>}  logics  { description }
		 */
		validateLogic: function( base, logics ) {
			var that = this;
			var return_val = false;
			var relation_check = [];
			var relation_operator = [];
			var debug_info = [];
			var logic_id, logic, conditional_div, check_type, this_validated,
			compare_source, do_comparison, compare_result, selected_state,
			m_columns, m_check_index, selectedOption, i, regEx, mfVal, gpsSettings,
			rangeElm, rangeElmMax, rangeMin, rangeMax,

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

			for ( logic_id in logics ) {
				logic = logics[logic_id], // Store the logic
				conditional_div = $('#ipt_fsqm_form_' + base + '_' + logic.m_type + '_' + logic.key), // get the conditional div to check against
				check_type = conditional_div.prev('.ipt_fsqm_hf_type').val(), // And the type of the element
				this_validated = false,
				compare_source = null,
				do_comparison = true;
				debug_info[logic_id] = {};
				debug_info[logic_id].x = logic.m_type;
				debug_info[logic_id].k = logic.key;
				debug_info[logic_id].has = logic.check;
				debug_info[logic_id].value = logic.value;
				debug_info[logic_id].rel = logic.rel;
				debug_info[logic_id].which = logic.operator;

				if ( this.settings.debug === true ) {
					this.debugLog( debug_info );
				}

				switch( check_type ) {
					// Radios
					case 'radio' :
					case 'p_radio' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_radio').filter(':checked').each( function() {
							compare_source[compare_source.length] = jQuery.trim($(this).next('label').text());
						} );
						break;

					// Checkboxes
					case 'checkbox' :
					case 'p_checkbox' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_checkbox').filter(':checked').each( function() {
							compare_source[compare_source.length] = jQuery.trim($(this).next('label').text());
						} );
						break;

					case 'select' :
					case 'p_select' :
						compare_source = [];
						conditional_div.find('select.ipt_uif_select option').filter(':selected').each( function() {
							compare_source[compare_source.length] = jQuery.trim($(this).text());
						} );
						break;

					case 'thumbselect' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_radio, input.ipt_uif_checkbox').filter(':checked').each( function() {
							compare_source[compare_source.length] = jQuery.trim($(this).data('label'));
						} );
						break;

					case 'slider' :
						compare_source = that.intelParseFloat( conditional_div.find('input.ipt_uif_slider').val() );
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'range' :
						compare_source = [that.intelParseFloat( conditional_div.find('input.ipt_uif_slider.slider_range').val() ), that.intelParseFloat( conditional_div.find('input.ipt_uif_slider.slider_range').siblings('.ipt_uif_slider_range_max').val() )];
						logic.value = that.intelParseFloat( logic.value );
						// If type is length
						// Then we make length = 1 if both are not at minimum
						// Otherwise length = 0
						if ( 'val' != logic.check ) {
							compare_source = 0;
							rangeElm = conditional_div.find('input.ipt_uif_slider.slider_range');
							rangeElmMax = conditional_div.find('input.ipt_uif_slider.slider_range').siblings('.ipt_uif_slider_range_max');
							rangeMin = that.intelParseFloat( rangeElm.attr( 'min' ) );
							if ( rangeMin != that.intelParseFloat( rangeElm.val() ) && rangeMin != that.intelParseFloat( rangeElmMax.val() ) ) {
								compare_source = 1;
							}
						}
						break;

					case 'spinners' :
						compare_source = [];
						conditional_div.find( 'input.ipt_uif_uispinner' ).each(function() {
							if ( $(this).val() !== '' ) {
								compare_source[compare_source.length] = that.intelParseFloat( $(this).val() );
							}
						});
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'grading' :
						compare_source = [];
						conditional_div.find('input.ipt_uif_slider').each(function() {
							if ( $(this).val() !== '' ) {
								compare_source[compare_source.length] = that.intelParseFloat( $(this).val() );
							}
						});
						conditional_div.find('input.ipt_uif_slider.slider_range').each(function() {
							if ( $(this).val() !== '' ) {
								compare_source[compare_source.length] = that.intelParseFloat( $(this).val() );
							}
							if ( $(this).siblings('.ipt_uif_slider_range_max').length ) {
								compare_source[compare_source.length] = that.intelParseFloat( $(this).siblings('.ipt_uif_slider_range_max').val() );
							}
						});
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'starrating' :
					case 'scalerating' :
						compare_source = [];
						conditional_div.find('.ipt_uif_rating').each(function() {
							if ( $(this).find('input.ipt_uif_radio:checked').length ) {
								compare_source[compare_source.length] = that.intelParseFloat( $(this).find('input.ipt_uif_radio:checked').val() );
							}
						});
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'matrix' :
						compare_source = [];

						// First get the column heads
						m_columns = [];
						conditional_div.find('.ipt_uif_matrix thead th').each(function() {
							m_columns[m_columns.length] = jQuery.trim($(this).text());
						});
						conditional_div.find('.ipt_uif_checkbox,.ipt_uif_radio').filter(':checked').each(function() {
							m_check_index = $(this).closest('tr').find('> *').index( $(this).closest('td') );
							if ( m_columns[m_check_index] !== '' || m_columns[m_check_index] !== undefined ) {
								compare_source[compare_source.length] = m_columns[m_check_index];
							}
						});
						break;
					case 'toggle' :
					case 's_checkbox' :
						compare_source = conditional_div.find('input[type="checkbox"]').is(':checked') ? '1' : '0';
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'smileyrating' :
						selected_state = conditional_div.find('input[type="radio"]:checked').val();
						if ( smileyVals[selected_state] !== undefined ) {
							compare_source = smileyVals[selected_state];
						}
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'likedislike' :
						selected_state = conditional_div.find('input[type="radio"]:checked').val();
						if ( likeDislikeState[selected_state] !== undefined ) {
							compare_source = likeDislikeState[selected_state];
						}
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'matrix_dropdown' :
						compare_source = [];
						conditional_div.find('select').each(function() {
							selectedOption = $(this).find('option').filter(':selected');
							if ( selectedOption.val() !== '' ) {
								compare_source[compare_source.length] = selectedOption.text();
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
						compare_source = conditional_div.find('input.ipt_uif_text').val();
						// For keypad it can be a text area as well
						if ( compare_source === undefined && check_type == 'keypad' )  {
							compare_source = conditional_div.find('textarea').val();
						}
						// Parse to float just in case
						if ( that.isNumeric( compare_source ) ) {
							compare_source = that.intelParseFloat( compare_source );
						}
						break;

					case 'feedback_large' :
					case 'textarea' :
						compare_source = conditional_div.find('textarea').val();
						break;

					case 'upload' :
						compare_source = conditional_div.find('.ipt_uif_uploader').data('totalUpload');
						break;

					case 'mathematical' :
						compare_source = that.intelParseFloat( conditional_div.find('input.ipt_uif_mathematical_input').val() );
						break;
					case 'address' :
						compare_source = [];
						conditional_div.find('.ipt_uif_text').each(function() {
							compare_source[compare_source.length] = $(this).val();
						});
						break;

					case 'datetime' :
						compare_source = conditional_div.find('.ipt_uif_text').val();
						compare_result = that.dates.compare(new Date( compare_source ), new Date( logic.value ));
						// Yet another ugly hack for IE8
						if ( $.support.opacity === false ) {
							compare_result = that.dates.compare(new Date( compare_source.toString().replace(/-/g, '/') ), new Date( logic.value.toString().replace(/-/g, '/') ));
						}
						switch( logic.operator ) {
							case 'eq' :
								if ( compare_result === 0 ) {
									this_validated = true;
								}
								break;
							case 'neq' :
								if ( compare_result !== 0 ) {
									this_validated = true;
								}
								break;
							case 'gt' :
								if ( compare_result === 1 ) {
									this_validated = true;
								}
								break;
							case 'lt' :
								if ( compare_result === -1 ) {
									this_validated = true;
								}
								break;
							default :
								break;
						}
						do_comparison = false;
						break;

					// New elements conditional logic
					// #228

					case 'feedback_matrix' :
						compare_source = [];
						conditional_div.find('.ipt_uif_text, .ipt_uif_textarea').each(function() {
							mfVal = $.trim( $(this).val() );
							if (  '' !== mfVal ) {
								compare_source[ compare_source.length ] = mfVal;
							}
						});
						break;

					case 'gps' :
						compare_source = [];
						if ( conditional_div.find('.ipt_uif_text').length ) {
							conditional_div.find('.ipt_uif_text').each(function() {
								mfVal = $.trim( $(this).val() );
								if (  '' !== mfVal ) {
									compare_source[ compare_source.length ] = mfVal;
								}
							});
						} else {
							gpsSettings = conditional_div.find('.ipt_uif_locationpicker').data('gpsSettings');
							if ( gpsSettings.values ) {
								if ( gpsSettings.values.lat ) {
									compare_source[ compare_source.length ] = gpsSettings.values.lat;
								}
								if ( gpsSettings.values.long ) {
									compare_source[ compare_source.length ] = gpsSettings.values.long;
								}
								if ( gpsSettings.values.location_name ) {
									compare_source[ compare_source.length ] = gpsSettings.values.location_name;
								}
							}
						}
						break;

					case 'signature' :
						compare_source = '0';
						mfVal = conditional_div.find('.ipt_uif_jsignature_input').val();
						if ( '' !== mfVal && 'image/jsignature;base30,' !== mfVal ) {
							compare_source = '1';
						}
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'payment' :
						// Just the payment total
						compare_source = conditional_div.find('.ipt_fsqm_payment_mathematical .ipt_uif_mathematical_input').val();
						// If coupon is present, then use the coupon instead
						if ( conditional_div.find('.ipt_uif_coupon').length ) {
							compare_source = conditional_div.find('.ipt_uif_coupon .ipt_uif_mathematical_input').val();
						}
						logic.value = that.intelParseFloat( logic.value );
						break;

					case 'hidden' :
						compare_source = conditional_div.find( '.ipt-eform-hidden-field' ).val();
						break;

					case 'guestblog' :
						compare_source = [];
						compare_source[ compare_source.length ] = conditional_div.find( '.ipt_uif_text' ).val();
						compare_source[ compare_source.length ] = conditional_div.find( '.ipt-eform-guestpost' ).val();
						break;

					case 'repeatable' :
						compare_source = conditional_div.find( '.ipt_uif_sda_elem' ).length;
						logic.value = that.intelParseFloat( logic.value );
						break;

					default :
						this_validated = false;
						do_comparison = false;
						break;
				}

				// Now do the comparison
				if ( do_comparison ) {
					// Protect against any undefined logic
					if ( compare_source === undefined ) {
						compare_source = [];
					}
					var final_compare_against = null,
					final_compare_with = ( typeof( logic.value ) == 'number' ? logic.value : logic.value.toString().toLowerCase() ); // Lower case for comparison

					if ( logic.check === 'val' ) { // Compare value
						if ( typeof( compare_source ) === 'object' ) { // If collected values are object
							final_compare_against = []; // Init as array
							for( i in compare_source ) {
								final_compare_against[final_compare_against.length] = ( typeof( compare_source[i] ) == 'number' ? compare_source[i] : compare_source[i].toString().toLowerCase() ); // Store lowercased string for comparison
							}
						} else {
							final_compare_against = ( typeof( compare_source ) == 'number' ? compare_source : compare_source.toString().toLowerCase() ); // Store lowercased value
						}

					} else { // Compare length
						final_compare_against = ( typeof( compare_source ) == 'number' ? compare_source : compare_source.length ); // Valid both for string and array type object
						final_compare_with = that.intelParseFloat( final_compare_with );
					}

					// Now do the comparison
					var compare_against_object = typeof( final_compare_against ) === 'object' ? true : false;
					switch( logic.operator ) {
						case 'eq' :
							if ( compare_against_object ) {
								for( i in final_compare_against ) {
									if ( final_compare_against[i] !== '' && final_compare_against[i] == final_compare_with ) {
										this_validated = true;
										break;
									} else if ( final_compare_against[i] === '' && final_compare_with === '' ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against !== '' && final_compare_against == final_compare_with ) {
									this_validated = true;
								} else if ( final_compare_against === '' && final_compare_with === '' ) {
									this_validated = true;
								}
							}
							break;
						case 'neq' :
							if ( compare_against_object ) {
								this_validated = true;
								for( i in final_compare_against ) {
									if ( final_compare_against[i] !== '' && final_compare_against[i] == final_compare_with ) {
										this_validated = false;
										break;
									}
								}
							} else {
								this_validated = true;
								if ( final_compare_against !== '' && final_compare_against == final_compare_with ) {
									this_validated = false;
								}
							}
							break;
						case 'gt' :
							if ( compare_against_object ) {
								for( i in final_compare_against ) {
									if ( final_compare_against[i] > final_compare_with ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against > final_compare_with ) {
									this_validated = true;
								}
							}
							break;
						case 'lt' :
							if ( compare_against_object ) {
								for( i in final_compare_against ) {
									if ( final_compare_against[i] < final_compare_with ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( final_compare_against < final_compare_with ) {
									this_validated = true;
								}
							}
							break;
						case 'ct' :
							if ( compare_against_object ) {
								// A special case for range
								if ( 'range' == check_type ) {
									this_validated = false;
									// Check if value is within the range
									if ( final_compare_with >= final_compare_against[0] && final_compare_with <= final_compare_against[1] ) {
										this_validated = true;
									}
								} else {
									for( i in final_compare_against ) {
										// Convert the value to string
										try {
											final_compare_against[i] = final_compare_against[i].toString();
										} catch( e ) {
											final_compare_against[i] = final_compare_against[i] + '';
										}
										// Now do comparison
										if ( final_compare_against[i] !== '' && final_compare_against[i].indexOf( final_compare_with ) !== -1 ) {
											this_validated = true;
											break;
										}
									}
								}
							} else {
								// Convert the value to string
								try {
									final_compare_against = final_compare_against.toString();
								} catch( e ) {
									final_compare_against = final_compare_against + '';
								}
								if ( final_compare_against !== '' && final_compare_against.indexOf( final_compare_with ) !== -1 ) {
									this_validated = true;
								}
							}
							break;
						case 'dct' :
							if ( compare_against_object ) {
								// A Special case for range
								if ( 'range' == check_type ) {
									this_validated = false;
									// Check if value is within the range
									if ( final_compare_with < final_compare_against[0] || final_compare_with > final_compare_against[1] ) {
										this_validated = true;
									}
								} else {
									this_validated = true;
									for( i in final_compare_against ) {
										// Convert the value to string
										try {
											final_compare_against[i] = final_compare_against[i].toString();
										} catch( e ) {
											final_compare_against[i] = final_compare_against[i] + '';
										}
										if ( final_compare_against[i] !== '' && final_compare_against[i].indexOf( final_compare_with ) !== -1 ) {
											this_validated = false;
											break;
										}
									}
								}
							} else {
								this_validated = true;
								// Convert the value to string
								try {
									final_compare_against = final_compare_against.toString();
								} catch( e ) {
									final_compare_against = final_compare_against + '';
								}
								if ( final_compare_against !== '' && final_compare_against.indexOf( final_compare_with ) !== -1 ) {
									this_validated = false;
								}
							}
							break;
						case 'sw' :
							regEx = new RegExp( '^' + final_compare_with, 'm' );
							if ( compare_against_object ) {
								for( i in final_compare_against ) {
									if ( regEx.test( final_compare_against[i] ) ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( regEx.test( final_compare_against ) ) {
									this_validated = true;
								}
							}
							break;

						case 'ew' :
							regEx = new RegExp( final_compare_with + '$', 'm' );
							if ( compare_against_object ) {
								for( i in final_compare_against ) {
									if ( regEx.test( final_compare_against[i] ) ) {
										this_validated = true;
										break;
									}
								}
							} else {
								if ( regEx.test( final_compare_against ) ) {
									this_validated = true;
								}
							}
							break;

						default :
							break;
					}
				}

				// Store for further checking
				relation_check[logic_id] = this_validated;
				relation_operator[logic_id] = logic.rel;
			}

			// Now check individual if necessary
			var relation_check_against = null,
			relation_check_operator = null,
			relation_check_array = [],
			relation_check_array_key = 0,
			logic_key;

			for ( logic_key in relation_check ) {
				if ( null === relation_check_against ) {
					relation_check_against = relation_check[logic_key];
				} else {
					switch ( relation_check_operator ) {
						case 'and' :
							relation_check_against = relation_check_against && relation_check[logic_key];
							break;
						case 'or' :
							relation_check_array_key++;
							relation_check_against = relation_check[logic_key];
							break;
						default :
							break;
					}
				}

				relation_check_operator = relation_operator[logic_key];
				relation_check_array[relation_check_array_key] = relation_check_against;
			}

			return_val = null;
			for ( i in relation_check_array ) {
				if ( return_val === null ) {
					return_val = relation_check_array[i];
				} else {
					return_val = return_val || relation_check_array[i];
				}
			}

			return return_val;
		},

		/**
		 * Initialize the static UI elements which are not/can not be delegated
		 *
		 * @method     initUIElements
		 */
		initUIElements: function() {
			// Check the selectors of every checkbox togglers
			this.uiCheckboxToggler();

			// Spinners
			//this.uiApplySpinner(); // we can remove this

			// Sliders
			this.uiApplySlider();

			// Progressbar
			this.uiApplyProgressBar();

			// Date and DateTime Picker
			this.uiApplyDateTimePicker();

			// Conditional input and select
			this.uiApplyConditionalInput();
			this.uiApplyConditionalSelect();

			// Image Slider
			this.uiApplyImageSlider();

			// Apply Rating
			this.uiApplyRating();
			this.uiApplySmileyRating();
			this.uiApplyLikeDislikeRating();

			// Apply Keypad
			this.uiApplyKeypad();

			// Autocomplete
			this.uiApplyAutoComplete();

			// Button
			this.uiApplyButtons();

			// Validation
			this.uiApplyValidation();

			// Collapsebox ipt_uif_collapsible
			this.uiApplyCollapsible();

			// Sortable
			this.uiApplySortable();

			// Fileuploader
			this.uiApplyUploader();

			// Locationpicker
			try {
				this.uiApplyLocationPicker();
			} catch ( e ) {
				this.debugLog( e, true );
			}

			// Trumbowyg
			this.uiApplyTrumbowyg();

			// Tabs
			this.uiApplyTabs();

			// Waypoints
			this.uiApplyWayPoints();

			// Tooltip
			this.uiApplyTooltip();

			// jsignature
			this.uiApplyjSignature();

			// Cards
			this.uiApplyCards();

			// TimeCircles
			this.uiApplyTimeCircles();

			// Mathematical Evaluator
			this.uiApplyMathematicalEvaluator();

			// Select Menu
			this.uiApplySelectMenu();

			// Country API
			this.uiApplyCountry();
		},
		uiApplyCountry: function() {
			var that = this, i;
			this.countryAutoComplete = [];
			// Get the country list for once
			this.countryList = {"afghanistan.json":"Afghanistan","albania.json":"Albania","algeria.json":"Algeria","american_samoa.json":"American Samoa","angola.json":"Angola","anguilla.json":"Anguilla","antigua_and_barbuda.json":"Antigua and Barbuda","argentina.json":"Argentina","armenia.json":"Armenia","aruba.json":"Aruba","australia.json":"Australia","austria.json":"Austria","azerbaijan.json":"Azerbaijan","bahamas.json":"The Bahamas","bahrain.json":"Bahrain","bangladesh.json":"Bangladesh","barbados.json":"Barbados","belarus.json":"Belarus","belgium.json":"Belgium","belize.json":"Belize","benin.json":"Benin","bermuda.json":"Bermuda","bhutan.json":"Bhutan","bolivia.json":"Bolivia","bosnia_and_herzegovina.json":"Bosnia and Herzegovina","botswana.json":"Botswana","brazil.json":"Brazil","british_virgin_islands.json":"British Indian Ocean Territory","brunei.json":"Brunei","bulgaria.json":"Bulgaria","burkina_faso.json":"Burkina Faso","burundi.json":"Burundi","cambodia.json":"Cambodia","cameroon.json":"Cameroon","canada.json":"Canada","cape_verde.json":"Cape Verde","cayman_islands.json":"Cayman Islands","central_african_republic.json":"Central African Republic","chad.json":"Chad","chile.json":"Chile","china.json":"China","christmas_island.json":"Christmas Island","cocos_keeling_islands.json":"Cocos (Keeling) Islands","colombia.json":"Colombia","comoros.json":"Comoros","congo_democratic_republic_of_the.json":"Republic of the Congo","congo_republic_of_the.json":"Democratic Republic of the Congo","cook_islands.json":"Cook Islands","costa_rica.json":"Costa Rica","cote_d_ivoire.json":"Ivory Coast","croatia.json":"Croatia","cuba.json":"Cuba","cyprus.json":"Cyprus","czeck_republic.json":"Czech Republic","denmark.json":"Denmark","djibouti.json":"Djibouti","dominica.json":"Dominica","dominican_republic.json":"Dominican Republic","ecuador.json":"Ecuador","egypt.json":"Egypt","el_salvador.json":"El Salvador","equatorial_guinea.json":"Equatorial Guinea","eritrea.json":"Eritrea","estonia.json":"Estonia","ethiopia.json":"Ethiopia","falkland_islands_islas_malvinas.json":"Falkland Islands","faroe_islands.json":"Faroe Islands","fiji.json":"Fiji","finland.json":"Finland","france.json":"France","french_guiana.json":"French Guiana","french_polynesia.json":"French Polynesia","french_southern_and_antarctic_lands.json":"French Southern and Antarctic Lands","gabon.json":"Gabon","gambia_the.json":"The Gambia","georgia.json":"Georgia","germany.json":"Germany","ghana.json":"Ghana","gibraltar.json":"Gibraltar","greece.json":"Greece","greenland.json":"Greenland","grenada.json":"Grenada","guadeloupe.json":"Guadeloupe","guam.json":"Guam","guatemala.json":"Guatemala","guernsey.json":"Guernsey","guinea.json":"Guinea","guinea_bissau.json":"Guinea-Bissau","guyana.json":"Guyana","haiti.json":"Haiti","heard_island_and_mc_donald_islands.json":"Heard Island and McDonald Islands","honduras.json":"Honduras","hong_kong.json":"Hong Kong","howland_island.json":"Hungary","iceland.json":"Iceland","india.json":"India","indonesia.json":"Indonesia","iran.json":"Iran","iraq.json":"Iraq","ireland.json":"Ireland","israel.json":"Israel","italy.json":"Italy","jamaica.json":"Jamaica","japan.json":"Japan","jersey.json":"Jersey","jordan.json":"Jordan","kazakhstan.json":"Kazakhstan","kenya.json":"Kenya","kiribati.json":"Kiribati","korea_north.json":"North Korea","korea_south.json":"South Korea","kuwait.json":"Kuwait","kyrgyzstan.json":"Kyrgyzstan","laos.json":"Laos","latvia.json":"Latvia","lebanon.json":"Lebanon","lesotho.json":"Lesotho","liberia.json":"Liberia","libya.json":"Libya","liechtenstein.json":"Liechtenstein","lithuania.json":"Lithuania","luxembourg.json":"Luxembourg","macau.json":"Macau","macedonia_former_yugoslav_republic_of.json":"Republic of Macedonia","madagascar.json":"Madagascar","malawi.json":"Malawi","malaysia.json":"Malaysia","maldives.json":"Maldives","mali.json":"Mali","malta.json":"Malta","man_isle_of.json":"Isle of Man","marshall_islands.json":"Marshall Islands","martinique.json":"Martinique","mauritania.json":"Mauritania","mauritius.json":"Mauritius","mayotte.json":"Mayotte","mexico.json":"Mexico","micronesia_federated_states_of.json":"Federated States of Micronesia","moldova.json":"Moldova","monaco.json":"Monaco","mongolia.json":"Mongolia","montserrat.json":"Montserrat","morocco.json":"Morocco","mozambique.json":"Mozambique","namibia.json":"Namibia","nauru.json":"Nauru","nepal.json":"Nepal","netherlands.json":"Netherlands","new_caledonia.json":"New Caledonia","new_zealand.json":"New Zealand","nicaragua.json":"Nicaragua","niger.json":"Niger","nigeria.json":"Nigeria","niue.json":"Niue","norfolk_island.json":"Norfolk Island","northern_mariana_islands.json":"Northern Mariana Islands","norway.json":"Norway","oman.json":"Oman","pakistan.json":"Pakistan","palau.json":"Palau","panama.json":"Panama","papua_new_guinea.json":"Papua New Guinea","paraguay.json":"Paraguay","peru.json":"Peru","philippines.json":"Philippines","pitcaim_islands.json":"Pitcairn Islands","poland.json":"Poland","portugal.json":"Portugal","puerto_rico.json":"Puerto Rico","qatar.json":"Qatar","reunion.json":"R\u00e9union","romainia.json":"Romania","russia.json":"Russia","rwanda.json":"Rwanda","saint_helena.json":"Saint Helena","saint_kitts_and_nevis.json":"Saint Kitts and Nevis","saint_lucia.json":"Saint Lucia","saint_pierre_and_miquelon.json":"Saint Pierre and Miquelon","saint_vincent_and_the_grenadines.json":"Saint Vincent and the Grenadines","samoa.json":"Samoa","san_marino.json":"San Marino","sao_tome_and_principe.json":"S\u00e3o Tom\u00e9 and Pr\u00edncipe","saudi_arabia.json":"Saudi Arabia","scotland.json":"Scotland","senegal.json":"Senegal","seychelles.json":"Seychelles","sierra_leone.json":"Sierra Leone","singapore.json":"Singapore","slovakia.json":"Slovakia","slovenia.json":"Slovenia","solomon_islands.json":"Solomon Islands","somalia.json":"Somalia","south_africa.json":"South Africa","south_georgia_and_south_sandwich_islands.json":"South Georgia","south_sudan.json":"South Sudan","spain.json":"Spain","sri_lanka.json":"Sri Lanka","sudan.json":"Sudan","suriname.json":"Suriname","svalbard.json":"Svalbard and Jan Mayen","swaziland.json":"Swaziland","sweden.json":"Sweden","switzerland.json":"Switzerland","syria.json":"Syria","taiwan.json":"Taiwan","tajikistan.json":"Tajikistan","tanzania.json":"Tanzania","thailand.json":"Thailand","tobago.json":"East Timor","toga.json":"Togo","tokelau.json":"Tokelau","tonga.json":"Tonga","trinidad.json":"Trinidad and Tobago","tunisia.json":"Tunisia","turkey.json":"Turkey","turkmenistan.json":"Turkmenistan","tuvalu.json":"Tuvalu","uganda.json":"Uganda","ukraine.json":"Ukraine","united_arab_emirates.json":"United Arab Emirates","united_kingdom.json":"United Kingdom","united_states_of_america.json":"United States","uruguay.json":"Uruguay","uzbekistan.json":"Uzbekistan","vanuatu.json":"Vanuatu","venezuela.json":"Venezuela","vietnam.json":"Vietnam","wales.json":"Wales","wallis_and_futuna.json":"Wallis and Futuna","western_sahara.json":"Western Sahara","yemen.json":"Yemen","zambia.json":"Zambia","zimbabwe.json":"Zimbabwe"};
			// Generate autocomplete array once
			for ( i in this.countryList ) {
				this.countryAutoComplete[ this.countryAutoComplete.length ] = this.countryList[ i ];
			}
			// Prepare cache for province
			this.provinceCache = [];
			// Apply autocomplete on all country pickers
			this.jElement.find( '.ipt-eform-address-country' ).each( function() {
				var countryAutoComplete = $( this ).find( '.ipt_uif_autocomplete' ),
				provinceAutoComplete = $( this ).closest( '.ipt_fsqm_container_address' ).find( '.ipt-eform-address-province .ipt_uif_autocomplete' );
				countryAutoComplete.autocomplete( 'option', 'source', that.countryAutoComplete );
				// Get value of country and set province
				if ( '' != countryAutoComplete.val() && provinceAutoComplete.length ) {
					that._updateProvince( countryAutoComplete );
				}
			} );
			// Apply autocomplete on preset province pickers
			this.jElement.find( '.ipt-eform-address-province .ipt_uif_autocomplete' ).each( function() {
				var elm = $( this );
				if ( elm.data( 'presetCountry' ) && '' != elm.data( 'presetCountry' ) ) {
					that._updateProvince( elm, true );
				}
			} );
		},

		// UI Select Menu
		uiApplySelectMenu: function() {
			if ( typeof( $.fn.select2 ) == 'undefined' ) {
				return;
			}
			var that = this;
			$( 'select.ipt_uif_select' ).select2();
		},

		// Mathematical
		uiApplyMathematicalEvaluator: function() {
			if ( typeof ( Parser ) == 'undefined' ) {
				return;
			}
			var that = this;

			// Initialize the data
			if ( ! this.jElement.data('iptFSQMMathVarToElem') ) {
				this.jElement.data('iptFSQMMathVarToElem', {});
			}

			// Look for every mathematical input and parse the formula
			this.jElement.find('.ipt_uif_mathematical_input').each(function() {
				try {
					that.evaluateMathematicalFormula.apply( this, [ that] );
				} catch (e) {
					that.debugLog( e, true );
				}
			});
		},

		// TimeCircles
		uiApplyTimeCircles: function() {
			if ( typeof( $.fn.TimeCircles ) == 'undefined' ) {
				return;
			}
			this.jElement.find('.ipt_uif_circle_timer').each(function() {
				var timerOptions = $(this).data('coptions'),
				// Now set the translations
				locales = ["Days", "Hours", "Minutes", "Seconds"], locale;
				if ( typeof( timerOptions ) != 'object' ) {
					timerOptions = {};
				}
				if ( timerOptions.time === undefined ) {
					timerOptions.time = {};
				}
				for ( locale in locales ) {
					if ( timerOptions.time[locales[locale]] === undefined ) {
						timerOptions.time[locales[locale]] = {};
					}
					timerOptions.time[locales[locale]].text = iptPluginUIFFront.L10n.timer[locales[locale]];
				}
				$(this).TimeCircles(timerOptions);
			});
		},

		// Cards
		uiApplyCards: function() {
			if ( typeof( $.fn.card ) == 'undefined' || typeof( $.fn.payment ) == 'undefined' ) {
				return;
			}
			this.jElement.find('.ipt_uif_card_holder').each(function() {
				var that = $(this),
				data = that.data('config');
				data.container = that.find('.ipt_uif_card').get(0);
				data.formatting = false;
				data.debug = true;

				// Implement payment validation
				that.find('.ipt_uif_cc_number').payment('formatCardNumber').typeWatch({
					highlight: false,
					captureLength: 1,
					wait: 200,
					callback: function( value ) {
						var field = $(this),
						type = $.payment.cardType( field.val() );
						if ( type !== null ) {
							// Set the CSS class
							field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-identified jp-card-' + type);
						} else {
							// Set the CSS class
							field.closest('.ipt_uif_card_holder').find('.jp-card').attr('class', '').addClass('jp-card jp-card-unknown');
						}
					}
				});
				that.find('.ipt_uif_cc_cvc').payment('formatCardCVC');
				that.find('.ipt_uif_cc_expiry').payment('formatCardExpiry');
				that.closest('form').card(data);
			});
		},

		// jSignature
		uiApplyjSignature: function() {
			// Init the signature PAD
			if ( typeof( $.fn.jSignature ) == 'undefined' ) {
				return;
			}
			this.jElement.find('.ipt_uif_jsignature_pad').jSignature({
				lineWidth: 2,
				UndoButton: true
				// width: 960
			// Set initial data
			}).each(function() {
				// Set the data once
				var signData = $(this).prev('.ipt_uif_jsignature_input').val();
				if ( signData !== '' && signData != 'image/jsignature;base30,' ) {
					$(this).jSignature('setData', signData, 'base30');
				}
			});
		},

		// Tooltip
		uiApplyTooltip: function() {
			this.jElement.find('.ipt_uif_tooltip').tooltipster({
				theme: 'tooltipster-shadow',
				animation: 'grow'
				//iconTouch: false
			});
			this.jElement.find( '.ipt_uif_qtooltip' ).tooltipster( {
				theme: 'tooltipster-shadow',
				animation: 'grow',
				side: 'left',
				contentAsHTML: true,
				interactive: true
			} );
		},

		// Waypoints
		uiApplyWayPoints: function() {
			// If waypoints is not enabled
			if ( this.settings.waypoints !== true ) {
				return;
			}
			// If not the right version
			// if ( typeof Waypoint !== 'function' || typeof Waypoint.Context !== 'function' ) {
			// 	return;
			// }
			var columns = this.jElement.find('.ipt_uif_conditional').filter(':visible').css({opacity: 0}).removeClass('iptAnimated iptFadeInLeft');
			setTimeout(function() {
				columns.waypoint({
					handler: function(direction) {
						var _self;
						// We are including the latest and greatest version of Waypoints
						// But some plugins and themes, especially the popular ones are just
						// using an older version
						// So we make a compatibility layer here
						// If it the greatest version
						if ( typeof( this.destroy ) == 'function' ) {
							_self = $(this.element);
							_self.css({opacity: ''});
							if ( _self.is(':visible') ) {
								_self.addClass('iptAnimated iptFadeInLeft');
								setTimeout(function() {
									_self.removeClass('iptAnimated iptFadeInLeft');
								}, 500);
							}
							this.destroy();
						// Or just fallback to the version 2.0 APIs
						} else {
							_self = $(this);
							_self.css({opacity: ''});
							if ( _self.is(':visible') ) {
								_self.addClass('iptAnimated iptFadeInLeft');
								setTimeout(function() {
									_self.removeClass('iptAnimated iptFadeInLeft');
								}, 500);
							}
							_self.waypoint( 'destroy' );
						}
					},
					offset: '98%'
				});
			}, 100);
		},

		// Locationpicker
		uiApplyLocationPicker: function() {
			if ( typeof( $.fn.locationpicker ) == 'undefined' ) {
				return;
			}
			this.jElement.find('.ipt_uif_locationpicker').each(function() {
				var widget = $(this),
				settings = widget.data('gpsSettings'),
				locationPicker = widget.find('.locationpicker-maps-control'),
				locationPickerLoad = widget.find('.locationpicker-maps-locating'),
				locationPickerError = widget.find('.location-maps-error');

				// If no UI then just populate the maps
				if ( settings.showUI === false ) {
					if ( $.isNumeric( settings.values.lat ) && $.isNumeric( settings.values.long ) ) {
						locationPicker.locationpicker({
							location: {
								latitude: settings.values.lat,
								longitude: settings.values.long
							},
							radius: settings.radius,
							zoom: settings.zoom
						});
						setTimeout( function() {
							widget.trigger('fsqm.conditional');
						}, 200 );
						widget.closest('.ipt_uif_conditional').on('iptUIFCShow', function() {
							locationPicker.locationpicker('autosize');
						});
						widget.closest('.ipt_fsqm_main_tab').on('tabsactivate', function() {
							locationPicker.locationpicker('autosize');
						});
						$(window).on('resize', function() {
							locationPicker.locationpicker('autosize');
						});
						$(window).on('fsqm.rlp', function() {
							locationPicker.locationpicker('autosize');
						});
					} else {
						locationPicker.html( settings.nolocation );
					}
				// Otherwise, populate the widget
				} else {
					// Updater shortcut
					var setLocationFromClient = function() {
						locationPickerLoad.stop(true, true).fadeIn( 'fast' );
						locationPickerError.hide();
						$.geolocation.get({
							success: function( position ) {
								var radius = position.coords.accuracy;
								if ( ! radius ) {
									radius = settings.radius;
								}
								locationPicker.locationpicker( 'location', {
									latitude: position.coords.latitude,
									longitude: position.coords.longitude,
									radius: radius
								} );
								locationPickerLoad.hide();
								locationPickerError.hide();
								setTimeout( function() {
									widget.trigger( 'locationPicker.eform' );
									widget.trigger('fsqm.conditional');
								}, 200 );
							},
							fail: function(error) {
								locationPickerLoad.stop(true, true).hide();
								locationPickerError.stop(true, true).fadeIn('fast').delay(4000).fadeOut('fast');
								setTimeout( function() {
									widget.trigger( 'locationPicker.eform' );
									widget.trigger('fsqm.conditional');
								}, 200 );
							},
							options: {
								enableHighAccuracy: true,
								timeout: 30000,
								maximumAge: 0
							}
						});
					};
					// Set the widget
					locationPicker.locationpicker({
						location: {
							latitude: settings.values.lat,
							longitude: settings.values.long
						},
						locationName: settings.values.location_name,
						radius: settings.radius,
						zoom: settings.zoom,
						scrollwheel: settings.scrollwheel,
						inputBinding: {
							latitudeInput: $('#' + settings.ids.latitudeInput),
							longitudeInput: $('#' + settings.ids.longitudeInput),
							locationNameInput: $('#' + settings.ids.locationNameInput)
						},
						enableAutocomplete: true,
						oninitialized: function(component) {
							widget.trigger( 'locationPicker.eform' );
							// Update if necessary
							if ( ! $.isNumeric( settings.values.lat ) || ! $.isNumeric( settings.values.long ) ) {
								setLocationFromClient();
							}
						},
						onchanged: function() {
							setTimeout( function() {
								widget.trigger( 'locationPicker.eform' );
								widget.trigger('fsqm.conditional');
							}, 200 );
						}
					});

					// Attach to the update button event
					if ( widget.find('.location-update').length ) {
						widget.find('.location-update').on('click', function(e) {
							e.preventDefault();
							setLocationFromClient();
						});
					}

					// Autoresize
					$(window).on('resize', function() {
						locationPicker.locationpicker('autosize');
					});
					widget.closest('.ipt_uif_conditional').on('iptUIFCShow', function() {
						locationPicker.locationpicker('autosize');
					});
					widget.closest('.ipt_fsqm_main_tab').on('tabsactivate', function() {
						locationPicker.locationpicker('autosize');
					});
					$(window).on('fsqm.rlp', function() {
						locationPicker.locationpicker('autosize');
					});
				}
			});
		},

		// Fileuploader
		uiApplyUploader: function() {
			if ( typeof( $.fn.fileupload ) == 'undefined' ) {
				return;
			}
			this.jElement.find('.ipt_uif_uploader').each(function() {
				var widget = $(this), // jQuery object of the widget
				settings = widget.data('settings'), // JSON settings
				configuration = widget.data('configuration'), // JSON configuration
				formData = widget.data('formdata'), // JSON formData
				uploadHandle = widget.find('.ipt_uif_uploader_handle'), // Input type file which is listened for change events
				dropZone = widget.find('.fileinput-dragdrop'), // jQuery object of the dropzone, can be empty in which case it will be disabeld
				acceptFileTypes = new RegExp( "(\.|\/)(" + settings.accept_file_types.split(',').join('|') + ")$", 'i' );

				widget.fileupload({
					url : iptPluginUIFFront.ajaxurl + configuration.upload_url,
					dropZone : dropZone,
					fileInput : uploadHandle,
					formData : formData,
					acceptFileTypes : acceptFileTypes,
					maxFileSize : parseInt(settings.max_file_size, 10),
					minFileSize : parseInt(settings.min_file_size, 10),
					maxNumberOfFiles : parseInt(settings.max_number_of_files, 10),
					uploadTemplateId : configuration.id + '_tmpl_upload',
					downloadTemplateId : configuration.id + '_tmpl_download',
					previewMaxHeight : 100,
					previewMaxWidth : 150,
					autoUpload: settings.auto_upload === true ? true : false,
					messages: iptPluginUIFFront.L10n.uploader.messages
				});

				// Set the active upload data
				widget.data( 'activeUpload', 0 );
				widget.data ( 'totalUpload', 0 );

				// Listen to process event and manipulate the activeUpload data accordingly
				widget.on( 'fileuploadsend', function( e, data ) {
					var activeUpload = widget.data( 'activeUpload' );
					activeUpload++;
					widget.data( 'activeUpload', activeUpload );
				} );
				widget.on( 'fileuploadalways', function( e, data ) {
					var activeUpload = widget.data( 'activeUpload' );
					activeUpload--;
					widget.data( 'activeUpload', activeUpload );
					widget.trigger('change');
				} );
				widget.on( 'fileuploaddone', function( e, data ) {
					var totalUpload = widget.data( 'totalUpload' );
					if ( data._response.result.files[0].error === undefined ) {
						totalUpload++;
					}
					widget.data( 'totalUpload', totalUpload );
				} );
				widget.on( 'fileuploaddestroyed', function( e, data ) {
					var totalUpload = widget.data( 'totalUpload' );
					if ( data.url !== '' ) {
						totalUpload--;
					}
					widget.data( 'totalUpload', totalUpload );
					widget.trigger('change');
				} );

				// Now fetch files if necessary
				if ( configuration.do_download === true ) {
					widget.addClass( 'fileupload-processing' );
					$.ajax({
						url : iptPluginUIFFront.ajaxurl + configuration.download_url,
						data : formData,
						context : widget.get(0)
					}).always(function() {
						$(this).removeClass('fileupload-processing');
					}).done(function( result ) {
						// Update the totalUpload count
						if ( result.files.length !== undefined ) {
							$(this).data( 'totalUpload', result.files.length );
						}
						$(this).fileupload('option', 'done').call( this, $.Event('done'), {result: result} );
					});
				}
			});
		},

		// Sortable
		uiApplySortable: function() {
			this.jElement.find('.ipt_uif_sorting').sortable({
				handle : '.ipt_uif_sorting_handle',
				items : '> .ipt_uif_sortme',
				helper : 'clone',
				appendTo : this.jElement,
				containment : 'parent',
				placeholder : 'ipt_uif_sortme_placeholder',
				forcePlaceholderSize : true
			});
		},

		// Validation
		uiApplyValidation: function() {
			this.jElement.find('form.ipt_uif_validate_form').validationEngine({
				promptPosition : 'topLeft',
				bindOnSubmit : false
			});
		},

		// Buttons
		uiApplyButtons: function() {
			this.jElement.find('.ipt_uif_button, .ipt_uif_ul_menu > li > a').button();
		},

		// Autocomplete
		uiApplyAutoComplete: function() {
			this.jElement.find('.ipt_uif_autocomplete').each(function() {
				$(this).autocomplete({
					source: $(this).data('autocomplete'),
					appendTo : $(this).parents('.ipt_uif_front')
				});
			});
		},

		// Keypad
		uiApplyKeypad: function() {
			var that = this;
			this.jElement.find('.ipt_uif_keypad').each(function() {
				var settings = $(this).data('settings');
				$(this).keyboard({
					layout : settings.layout,
					usePreview : false,
					autoAccept : true,
					appendLocally : false,
					beforeClose : function() {
						$('body').removeClass('ipt_uif_common ' + that.ui_theme_id);
					}
				}).on('focus', function() {
					$('body').addClass('ipt_uif_common ' + that.ui_theme_id);
				});
			});
		},

		// Ratings
		uiApplyRating: function() {
			this.jElement.find('.ipt_uif_rating input:checked').each(function() {
				$(this).addClass('active').prevAll('input').addClass('active');
			});
		},

		// Smiley Rating
		uiApplySmileyRating: function() {
			this.jElement.find('.ipt_uif_rating_smiley').each(function() {
				if ( $(this).find('input.ipt_uif_smiley_rating_radio:checked').length ) {
					$(this).addClass('ipt_uif_smiley_feedback_active');
				} else {
					$(this).removeClass('ipt_uif_smiley_feedback_active');
				}
			});
		},

		// Like Dislike
		uiApplyLikeDislikeRating: function() {
			this.jElement.find('.ipt_uif_rating_likedislike').each(function() {
				if ( $(this).find('input.ipt_uif_likedislike_rating_radio:checked').length ) {
					$(this).addClass('ipt_uif_likedislike_feedback_active');
				} else {
					$(this).removeClass('ipt_uif_likedislike_feedback_active');
				}
			});
		},

		// Image Slider
		uiApplyImageSlider: function() {
			this.jElement.find('.ipt_uif_image_slider_wrap').each(function() {
				var self = $(this),
				settings = self.data('settings'),
				controller = $('<a class=""></a>'),
				slider = self.find('.ipt_uif_image_slider'),
				controller_on_play = settings.on_play,
				controller_on_pause = settings.on_pause;

				//Init the slider
				slider.nivoSlider({
					effect : settings.animation,
					animSpeed : settings.transition * 1000,
					pauseTime : settings.duration * 1000,
					pauseOnHover : false,
					manualAdvance : !settings.autoslide,
					controlNav : true,
					prevText : '',
					nextText : ''
				});

				slider.find('a.nivo-prevNav').after(controller);

				//Init the controller event
				controller.on('click', function(e) {
					e.preventDefault();
					var nivoSlider = slider.data('nivoslider');
					if($(this).hasClass('ipt_uif_image_slider_sliding')) {
						nivoSlider.stop();
						$(this).removeClass('ipt_uif_image_slider_sliding');
						$(this).removeClass(controller_on_play);
						$(this).addClass(controller_on_pause);
					} else {
						nivoSlider.start();
						$(this).addClass('ipt_uif_image_slider_sliding');
						$(this).removeClass(controller_on_pause);
						$(this).addClass(controller_on_play);
					}
				});

				//Initial state of the controller
				if(settings.autoslide === true) {
					controller.addClass('ipt_uif_image_slider_sliding');
					controller.removeClass(controller_on_pause);
					controller.addClass(controller_on_play);
				} else {
					controller.removeClass('ipt_uif_image_slider_sliding');
					controller.removeClass(controller_on_play);
					controller.addClass(controller_on_pause);
				}
			});
		},

		// Checkbox Toggler
		uiCheckboxToggler: function() {
			// Loop through every toggler and add listener to the selectors too
			var jElement = this.jElement;
			jElement.find('.ipt_uif_checkbox_toggler').each(function() {
				var _self = $(this);
				if ( _self.is(':checked') ) {
					$(_self.data('selector')).prop('checked', true);
				}
				jElement.on('change', _self.data('selector'), function() {
					_self.prop('checked', false);
				});
			});
		},

		// Spinners
		uiApplySpinner: function() {
			this.jElement.find('.ipt_uif_uispinner').spinner();
		},

		// Sliders
		uiApplySlider: function() {
			if ( typeof( $.fn.slider ) == 'undefined' ) {
				return;
			}
			var that = this;
			this.jElement.find('.ipt_uif_slider').each(function() {
				var step, min, max, value, slider_range, slider_settings, second_value, first_input = $(this), second_input = null,
				count_div, slider_div, slider_div_duplicate, floats, vertical, vheight;

				// Get the settings
				step = parseFloat( $(this).data('step') );
				if( isNaN( step ) )
					step = 1;

				min = parseFloat( $(this).data('min') );
				if( isNaN( min ) )
					min = 1;

				max = parseFloat( $(this).data('max') );
				if( isNaN( max ) )
					max = null;

				value = parseFloat( $(this).val() );
				if( isNaN( value ) )
					value = min;

				slider_range = $(this).hasClass('slider_range') ? true : false;

				floats = ( 1 == $( this ).data( 'floats' ) ) ? true : false;

				vertical = ( 1 == $( this ).data( 'vertical' ) ) ? true : false;

				vheight = parseInt( $( this ).data( 'height' ) );
				if ( isNaN( vheight ) || vheight <= 0 ) {
					vheight = 300;
				}

				slider_settings = {
					min: min,
					max: max,
					step: step,
					range: slider_range
				};
				if ( vertical ) {
					slider_settings.orientation = 'vertical';
				}

				// Get the second input if necessary
				if ( slider_range ) {
					second_input = first_input.next('input');
					second_value = parseFloat( second_input.val() );
					if( isNaN( second_value ) ) {
						second_value = min;
					}
				}

				// Prepare the show count
				count_div = first_input.siblings('div.ipt_uif_slider_count');

				// Append the div
				slider_div = $('<div />');
				slider_div.addClass(slider_range ? 'ipt_uif_slider_range' : 'ipt_uif_slider_single').addClass('ipt_uif_slider_div');

				// Remove the duplicate div
				// Here for legecy purpose
				if ( slider_range ) {
					slider_div_duplicate = second_input.next('div.ipt_uif_slider_range');
				} else {
					slider_div_duplicate = first_input.next('div.ipt_uif_slider_range');
				}
				if ( slider_div_duplicate.length ) {
					slider_div_duplicate.remove();
				}

				first_input.after(slider_div);

				// Prepare the slide function
				// We do not need to program slide/slidechange at this point
				// because it is being handled by ed
				if ( ! slider_range ) {
					slider_settings.value = value;
				} else {
					slider_settings.values = [value, second_value];
					slider_settings.range = true;
				}

				// Init the counter
				if ( count_div.length ) {
					if ( slider_range ) {
						count_div.find('span.ipt_uif_slider_count_min').text(value);
						count_div.find('span.ipt_uif_slider_count_max').text(second_value);
					} else {
						count_div.find('span').text(value);
					}
				}

				// Set height
				if ( vertical ) {
					slider_div.height( vheight );
				}

				// Init the slider
				var slider = slider_div.slider( slider_settings ).slider( 'pips', first_input.data('labels') );
				if ( floats ) {
					slider.slider( 'float' );
				}
			});
		},

		// Progress bar
		uiApplyProgressBar: function() {
			this.jElement.find('.ipt_uif_progress_bar').each(function() {
				//First get the start value
				var progress_self = $(this),
				start_value = progress_self.data('start') ? progress_self.data('start') : 0,
				decimals = progress_self.data('decimals'),
				//Add the value to the inner div
				value_div = progress_self.find('.ipt_uif_progress_value span').addClass('code');
				value_div.html(start_value + '%');
				value_div.data( 'iptPBVal', start_value );

				//Init the progressbar
				var progressbar = progress_self.progressbar({
					value : start_value,
					change : function(event, ui) {
						var countVal = $(this).progressbar('option', 'value'),
						pbCountUp = new CountUp( value_div.get(0), value_div.data('iptPBVal'), countVal, decimals, 1, {
							useEasing: true,
							useGrouping: false,
							separator: '',
							decimal: '.',
							prefix: '',
							suffix: '%'
						} );

						if ( value_div.data( 'iptPBCU' ) ) {
							value_div.data( 'iptPBCU' ).reset();
						}
						pbCountUp.start();
						value_div.data( 'iptPBVal', countVal );
						value_div.data( 'iptPBCU', pbCountUp );
					}
				});
			});
		},

		// Date and Datetime picker
		uiApplyDateTimePicker: function() {
			var that = this;
			// Date picker
			this.jElement.find('.ipt_uif_datepicker').each(function() {
				var elm = $( this ),
				yrange = elm.data( 'year_range' );
				if ( ! yrange ) {
					yrange = 50;
				}
				elm.datepicker({
					dateFormat : $(this).data('dateformat'),
					duration : 0,
					beforeShow : function() {
						var val = '', minDate = null, maxDate = null, future, past, dateCalc;
						if ( elm.data( 'future' ) ) {
							future = elm.data( 'future' ).toLowerCase();
							if ( future.match( /(\d+)-(\d+)-(\d+)/ ) ) {
								minDate = new Date( future );
							} else {
								val = $( '#' + elm.data( 'future' ) ).val();
								minDate = null;
								if ( '' !== val ) {
									minDate = new Date( val );
								}
							}
							minDate.setDate( minDate.getDate() + 1 );
							elm.datepicker( 'option', 'minDate', minDate );
						}
						if ( elm.data( 'past' ) ) {
							past = elm.data( 'past' ).toLowerCase();
							if ( past.match( /(\d+)-(\d+)-(\d+)/ ) ) {
								maxDate = new Date( past );
							} else {
								val = $( '#' + elm.data( 'past' ) ).val();
								if ( '' != val ) {
									maxDate = new Date( val );
								}
							}
							maxDate.setDate( maxDate.getDate() - 1 );
							elm.datepicker( 'option', 'maxDate', maxDate );
						}
						$('body').addClass( that.ui_theme_slug );
						elm.trigger( 'datepickerOpen.eform' );
					},
					onClose : function() {
						$('body').removeClass( that.ui_theme_slug );
						elm.trigger( 'datepickerClose.eform' );
						try {
							elm.validationEngine( 'validate' );
						} catch( e ) {

						}
						if ( '' == elm.val() ) {
							elm.addClass( 'is-empty' );
						} else {
							elm.removeClass( 'is-empty' );
						}
					},
					showButtonPanel: true,
					closeText: iptPluginUIFDTPL10n.closeText,
					currentText: iptPluginUIFDTPL10n.currentText,
					monthNames: iptPluginUIFDTPL10n.monthNames,
					monthNamesShort: iptPluginUIFDTPL10n.monthNamesShort,
					dayNames: iptPluginUIFDTPL10n.dayNames,
					dayNamesShort: iptPluginUIFDTPL10n.dayNamesShort,
					dayNamesMin: iptPluginUIFDTPL10n.dayNamesMin,
					firstDay: iptPluginUIFDTPL10n.firstDay,
					isRTL: iptPluginUIFDTPL10n.isRTL,
					timezoneText : iptPluginUIFDTPL10n.timezoneText,
					changeMonth: true,
					changeYear: true,
					yearRange: 'c-' + yrange + ':c+' + yrange,
					appendTo: that.jElement
				});
			});

			// Date Time Picker
			this.jElement.find('.ipt_uif_datetimepicker').each(function() {
				var elm = $( this ),
				yrange = elm.data( 'year_range' );
				if ( ! yrange ) {
					yrange = 50;
				}
				$(this).datetimepicker({
					dateFormat : $(this).data('dateformat'),
					duration : 0,
					timeFormat : $(this).data('timeformat'),
					beforeShow : function() {
						$('body').addClass( that.ui_theme_slug );
						elm.trigger( 'datepickerOpen.eform' );
					},
					onClose : function() {
						$('body').removeClass( that.ui_theme_slug );
						elm.trigger( 'datepickerClose.eform' );
						try {
							elm.validationEngine( 'validate' );
						} catch( e ) {

						}
						if ( '' == elm.val() ) {
							elm.addClass( 'is-empty' );
						} else {
							elm.removeClass( 'is-empty' );
						}
					},
					showButtonPanel: true,
					closeText: iptPluginUIFDTPL10n.closeText,
					currentText: iptPluginUIFDTPL10n.tcurrentText,
					monthNames: iptPluginUIFDTPL10n.monthNames,
					monthNamesShort: iptPluginUIFDTPL10n.monthNamesShort,
					dayNames: iptPluginUIFDTPL10n.dayNames,
					dayNamesShort: iptPluginUIFDTPL10n.dayNamesShort,
					dayNamesMin: iptPluginUIFDTPL10n.dayNamesMin,
					firstDay: iptPluginUIFDTPL10n.firstDay,
					isRTL: iptPluginUIFDTPL10n.isRTL,
					amNames : iptPluginUIFDTPL10n.amNames,
					pmNames : iptPluginUIFDTPL10n.pmNames,
					timeSuffix : iptPluginUIFDTPL10n.timeSuffix,
					timeOnlyTitle : iptPluginUIFDTPL10n.timeOnlyTitle,
					timeText : iptPluginUIFDTPL10n.timeText,
					hourText : iptPluginUIFDTPL10n.hourText,
					minuteText : iptPluginUIFDTPL10n.minuteText,
					secondText : iptPluginUIFDTPL10n.secondText,
					millisecText : iptPluginUIFDTPL10n.millisecText,
					microsecText : iptPluginUIFDTPL10n.microsecText,
					timezoneText : iptPluginUIFDTPL10n.timezoneText,
					changeMonth: true,
					changeYear: true,
					yearRange: 'c-' + yrange + ':c+' + yrange,
					appendTo: that.jElement
				});
			});

			// Time Picker
			this.jElement.find('.ipt_uif_timepicker').each(function() {
				var elm = $( this );
				$(this).timepicker({
					timeFormat : $(this).data('timeformat'),
					duration : 0,
					beforeShow : function() {
						$('body').addClass( that.ui_theme_slug );
						elm.trigger( 'datepickerOpen.eform' );
					},
					onClose : function() {
						$('body').removeClass( that.ui_theme_slug );
						elm.trigger( 'datepickerClose.eform' );
						try {
							elm.validationEngine( 'validate' );
						} catch( e ) {

						}
						if ( '' == elm.val() ) {
							elm.addClass( 'is-empty' );
						} else {
							elm.removeClass( 'is-empty' );
						}
					},
					showButtonPanel: true,
					closeText: iptPluginUIFDTPL10n.closeText,
					currentText: iptPluginUIFDTPL10n.tcurrentText,
					isRTL: iptPluginUIFDTPL10n.isRTL,
					amNames : iptPluginUIFDTPL10n.amNames,
					pmNames : iptPluginUIFDTPL10n.pmNames,
					timeSuffix : iptPluginUIFDTPL10n.timeSuffix,
					timeOnlyTitle : iptPluginUIFDTPL10n.timeOnlyTitle,
					timeText : iptPluginUIFDTPL10n.timeText,
					hourText : iptPluginUIFDTPL10n.hourText,
					minuteText : iptPluginUIFDTPL10n.minuteText,
					secondText : iptPluginUIFDTPL10n.secondText,
					millisecText : iptPluginUIFDTPL10n.millisecText,
					microsecText : iptPluginUIFDTPL10n.microsecText,
					timezoneText : iptPluginUIFDTPL10n.timezoneText,
					appendTo: that.jElement
				});
			});
		},

		// Conditional input
		uiApplyConditionalInput: function() {
			this.jElement.find('.ipt_uif_conditional_input').each(function() {
				// init vars
				var _self = $(this),
				inputs = _self.find('input'),
				shown = [], hidden = [], input_ids, i;

				// loop through and populate vars
				inputs.each(function() {
					input_ids = $(this).data('condid');
					if ( typeof ( input_ids ) == 'string' ) {
						input_ids = input_ids.split( ',' );
					} else {
						input_ids = [];
					}

					if ( $(this).is(':checked') ) {
						shown.push.apply( shown, input_ids );
					} else {
						hidden.push.apply( hidden, input_ids );
					}
				});

				// hide all that would be hidden
				for ( i = 0; i < hidden.length; i++ ) {
					$('#' + hidden[i]).stop( true, true ).hide();
				}

				// Now show all that would be shown
				for ( i = 0; i < shown.length; i++ ) {
					$('#' + shown[i]).stop( true, true ).show();
				}

			});
		},

		// Conditional Select
		uiApplyConditionalSelect: function() {
			this.jElement.find('.ipt_uif_conditional_select').each(function() {
				// Init the vars
				var _self = $(this),
				select = _self.find('select'),
				shown = [], hidden = [], input_ids, i;

				// Loop through and populate vars
				select.find('option').each(function() {
					input_ids = $(this).data('condid');
					if ( typeof ( input_ids ) == 'string' ) {
						input_ids = input_ids.split( ',' );
					} else {
						input_ids = [];
					}

					if ( $(this).is(':selected') ) {
						shown.push.apply( shown, input_ids );
					} else {
						hidden.push.apply( hidden, input_ids );
					}
				});

				// hide all that would be hidden
				for ( i = 0; i < hidden.length; i++ ) {
					$('#' + hidden[i]).stop( true, true ).hide();
				}

				// Now show all that would be shown
				for ( i = 0; i < shown.length; i++ ) {
					$('#' + shown[i]).stop( true, true ).show();
				}
			});
		},

		// Collapsible
		uiApplyCollapsible: function() {
			var that = this;
			this.jElement.find('.ipt_uif_collapsible').each(function() {
				var state = false,
				self = $(this),
				collapse_box = self.find('> .ipt_uif_container_inner');

				if ( self.data('opened') === true || self.data('opened') === 1 ) {
					state = true;
				}

				// Check the initial state
				if ( state ) {
					collapse_box.show();
					that.refreshiFrames( collapse_box );
					self.addClass('ipt_uif_collapsible_open');
				} else {
					collapse_box.hide();
					self.removeClass('ipt_uif_collapsible_open');
				}
				$(this).trigger('iptUICollapsible');
			});
		},

		// Trumbowyg
		uiApplyTrumbowyg: function() {
			var that = this;
			if ( 'function' == typeof( jQuery.fn.trumbowyg ) ) {
				this.jElement.find('.ipt-eform-trumbowyg').each(function() {
					var op = $(this).data('efTrum');
					if ( 'object' != typeof( op ) ) {
						op = null;
					}
					$(this).trumbowyg( op );
				});
			}
		},

		// Tabs
		uiApplyTabs: function() {
			var that = this;
			this.jElement.find('.ipt_uif_tabs').each(function() {
				var tab_ops = {
					collapsible : $(this).data('collapsible') ? true : false,
					show : 200,
					create: function(event, ui) {
						if ( that.settings.waypoints === true ) {
							ui.panel.data('iptWaypoints', true);
						}

						// Show the first non hidden element
						var firstTab = 0, tabIndices = ui.tab.parent('.ui-tabs-nav').find('> li');
						while( tabIndices.eq(firstTab).hasClass('iptUIFCHidden') ) {
							firstTab++;
							if ( firstTab >= tabIndices.length ) {
								firstTab = 0;
								break;
							}
						}
						$(this).tabs('option', 'active', firstTab);
					},
					beforeActivate: function(event, ui) {
						if ( ! ui.newPanel.data('iptWaypoints') && that.settings.waypoints === true /* && typeof Waypoint === 'function' && typeof Waypoint.Context === 'function' */ ) {
							ui.newPanel.find('.ipt_uif_conditional').css({opacity: 0}).removeClass('iptAnimated iptFadeInLeft');
						}
					},
					activate: function(event, ui) {
						that.refreshiFrames.apply( ui.oldPanel );
						that.refreshiFrames.apply(ui.newPanel);
						// Don't refresh if either this tab has been shown or it is the first tab
						if ( ! ui.newPanel.data('iptWaypoints') && that.settings.waypoints === true /* && typeof Waypoint === 'function' && typeof Waypoint.Context === 'function' */ ) {
							var columns = ui.newPanel.find('.ipt_uif_conditional');
							columns.waypoint({
								handler: function(direction) {
									var _self;
									// We are including the latest and greatest version of Waypoints
									// But some plugins and themes, especially the popular ones are just
									// using an older version
									// So we make a compatibility layer here
									// If it the greatest version
									if ( typeof( this.destroy ) == 'function' ) {
										_self = $(this.element);
										_self.css({opacity: ''}).addClass('iptAnimated iptFadeInLeft');

										setTimeout(function() {
											_self.removeClass('iptAnimated iptFadeInLeft');
										}, 500);
										this.destroy();
									// Or just fallback to the version 2.0 APIs
									} else {
										_self = $(this);
										_self.css({opacity: ''}).addClass('iptAnimated iptFadeInLeft');

										setTimeout(function() {
											_self.removeClass('iptAnimated iptFadeInLeft');
										}, 500);

										_self.waypoint( 'destroy' );
									}
								},
								offset: '98%'
							});
							ui.newPanel.data('iptWaypoints', true);
						}
					}
				};
				$(this).tabs(tab_ops);

				//Fix for vertical tabs
				if($(this).hasClass('vertical')) {
					$(this).addClass('ui-tabs-vertical ui-helper-clearfix');
					$(this).find('> ul > li').removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
				}
			});
		},

		// SDA initiator
		uiSDAinit: function() {
			var self = $(this),
			submit_button = self.find('> .ipt_uif_sda_foot button.ipt_uif_sda_button'),

			// Get some variables
			vars = {
				sort : self.data('draggable') == 1 ? true : false,
				add : self.data('addable') == 1 ? true : false,
				del : self.data('addable') == 1 ? true : false,
				count : (submit_button.length && submit_button.data('count') ? submit_button.data('count') : 0),
				key : (submit_button.length && submit_button.data('key') ? submit_button.data('key') : '__KEY__'),
				max : self.data( 'max' ),
				min : self.data( 'min' ),
			};

			// store the data
			self.data( 'iptSDAdata', vars );

			// Hide submit button if exceeds maximum
			var total = self.find( '> .ipt_uif_sda_body > .ipt_uif_sda_elem' ).length;

			if ( vars.max !== '' && vars.max > 0 ) {
				if ( total >= vars.max ) {
					submit_button.hide();
				}
			}
			if ( vars.min !== '' && vars.min > 0 ) {
				if ( total <= vars.min ) {
					self.addClass( 'eform-sda-reached-min' );
				} else {
					self.removeClass( 'eform-sda-reached-min' );
				}
			}

			if ( 0 == total ) {
				self.addClass( 'ipt-uif-sda-empty' );
			} else {
				self.removeClass( 'ipt-uif-sda-empty' );
			}
		},

		// SDA List make sortable
		uiSDAsort: function() {
			var self = $(this),
			sdaData = self.data('iptSDAdata');
			if ( sdaData.sort === true ) {
				self.find('> .ipt_uif_sda_body').sortable({
					items : 'div.ipt_uif_sda_elem',
					placeholder : 'ipt_uif_sda_highlight',
					handle : 'div.ipt_uif_sda_drag',
					distance : 5,
					axis : 'y',
					start: function( event, ui ) {
						ui.placeholder.height( ui.item.outerHeight() );
					},
					helper : 'original',
					cursor: 'move',
					appendTo: self.closest( '.ipt_uif_sda_body' ),
					stop: function( event, ui ) {
						self.trigger( 'refreshWaypoints.eform' );
					}
				});
			}
		},

		// Password Reveal
		edRevealPassword: function() {
			var that = this;
			var passWordRevealed = false;
			this.jElement.on( 'mousedown', '.ipt-eform-password .ipticm', function() {
				passWordRevealed = true;
				$( this ).removeClass( 'ipt-icomoon-eye' ).addClass( 'ipt-icomoon-eye-slash' );
				var passwordField = $( this ).closest( '.ipt-eform-password' ).find( '.ipt_uif_password' );
				passwordField.attr( 'type', 'text' );
			} );
			$( 'body' ).on( 'mouseup', function() {
				if ( ! passWordRevealed ) {
					return;
				}
				that.jElement.find( '.ipt-eform-password' ).each( function() {
					var elm = $( this );
					elm.find( '.ipticm' ).removeClass( 'ipt-icomoon-eye-slash' ).addClass( 'ipt-icomoon-eye' );
					elm.find( '.ipt_uif_password' ).attr( 'type', 'password' );
				} );
				passWordRevealed = false;
			} );
		},

		// Change event on country API
		edApplyCountry: function() {
			var that = this;
			this.jElement.on( 'change autocompletechange', '.ipt-eform-address-country .ipt_uif_autocomplete', function() {
				that._updateProvince( $( this ) );
			} );
		},

		/**
		 * Initialize event delegated functionalities
		 * Needs to be initialized only once
		 *
		 * @method     initUIElementsDelegated
		 */
		initUIElementsDelegated: function() {
			var _self = this;
			// Initialize the help toggler
			this.edApplyHelp();

			// Initialize message closer
			this.edApplyMessage();

			// Initialize the checkbox toggler
			this.edCheckboxToggler();

			// Init Spinner
			// this.edApplySpinner();

			// Initialize the slider listener
			this.edSliderInput();

			// Initialize the datetime Now
			this.edDateTimeNow();

			// Initialize the print element
			this.edApplyPrintElement();

			// Initialize conditional input and select
			this.edApplyConditionalInput();
			this.edApplyConditionalSelect();

			// Initialize collapsible
			this.edApplyCollapsible();

			// Init scroll to top
			this.edApplyScrollToTop();

			// Init rating
			this.edApplyRating();
			this.edApplySmileyRating();
			this.edApplyLikeDislikeRating();

			// Init file uploader
			this.edApplyUploader();

			// Init tab toggler
			this.edTabToggler();

			// Init waypoints
			this.edApplyWayPoints();

			// Init jSignature
			this.edApplyjSignature();

			// Init TimeCircles
			this.edApplyTimeCircles();

			// Init Mathematical Evaluator
			this.edApplyMathematicalEvaluator();

			// Init Trumbowyg change listener
			this.edApplyTrumbowyg();

			// Init the popup buttons
			this.edApplyPopupICM();

			// Init the select2
			this.edApplySelectMenu();

			// Init Password Reveal
			this.edRevealPassword();

			// Init Country API
			this.edApplyCountry();
		},

		// Select Menu
		edApplySelectMenu: function() {
			if ( typeof( $.fn.select2 ) == 'undefined' ) {
				return;
			}
			this.jElement.on( 'select2:close', '.ipt_uif_select', function( e ) {
				$( this ).validationEngine( 'validate' );
			} );
		},

		// Popup buttons
		edApplyPopupICM: function() {
			this.jElement.on( 'click', '.eform-icmpopup', function( e ) {
				e.preventDefault();
				var w = $(this).data( 'width' ),
				h = $(this).data( 'height' ),
				url = $(this).attr( 'href' ),

				// Fixes dual-screen position
				dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left,
				dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top,

				width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width,
				height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height,

				left = ((width / 2) - (w / 2)) + dualScreenLeft,
				top = ((height / 2) - (h / 2)) + dualScreenTop,
				newWindow = window.open(url, 'eform-icmpopup', 'scrollbars=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);

				// Puts focus on the newWindow
				if ( window.focus ) {
				   newWindow.focus();
				}
			} );
		},

		// Trumbowyg listener
		edApplyTrumbowyg: function() {
			var that = this;
			this.jElement.on( 'tbwblur', '.ipt-eform-trumbowyg', function(e) {
				$(this).trigger('change');
			} );
		},

		// Mathematical Evaluator
		edApplyMathematicalEvaluator: function() {
			if ( typeof ( Parser ) == 'undefined' ) {
				return;
			}
			var that = this;
			this.jElement.on( 'change fsqm.mathematicalReEvaluate', function(e) {
				// Get the target
				var target = $(e.target);

				$(this).find('.ipt_uif_mathematical_input').each(function() {
					// Dont check if it's the same target
					if ( $(this).is(target) ) {
						return true;
					}
					try {
						that.evaluateMathematicalFormula.apply( this, [ that ] );
					} catch (e) {
						that.debugLog( e, true );
					}
				});
			});
		},

		// TimeCircles
		edApplyTimeCircles: function() {
			if ( typeof( $.fn.TimeCircles ) == 'undefined' ) {
				return;
			}
			var jElement = this.jElement;
			$(window).on('resize iptUIFCShow iptUIFCHide tabsactivate', $.debounce(250, function() {
				jElement.find('.ipt_uif_circle_timer').each(function() {
					$(this).TimeCircles().rebuild();
				});
			}));
		},

		// jSignature
		edApplyjSignature: function() {
			if ( typeof( $.fn.jSignature ) == 'undefined' ) {
				return;
			}
			// Set the undo function
			this.jElement.on('click', '.ipt_uif_jsignature_undo', function() {
				$(this).closest('.ipt_uif_jsignature').find('.ipt_uif_jsignature_pad input[type="button"]').trigger('click');
			});
			// Set the reset function
			this.jElement.on('click', '.ipt_uif_jsignature_reset', function() {
				$(this).closest('.ipt_uif_jsignature').find('.ipt_uif_jsignature_pad').jSignature('clear');
			});
			// Set the value update function
			this.jElement.on('change', '.ipt_uif_jsignature_pad', function( e ) {
				// Don't do anything if this is not visible
				var elm = $(this),
				signatureData = elm.jSignature( 'getData', 'base30' ),
				signatureDataRaw = signatureData.join( ',' ),
				signatureText = elm.siblings('.ipt_uif_jsignature_input').val(signatureData);
				if ( ! elm.data( 'eFormjSignatureUpdating' ) ) {
					signatureText.validationEngine( 'validate' );
				}
				signatureText.trigger('change').trigger('blur');
			});

			// Now reinit during tabshow and conditional show
			this.jElement.on('iptUIFCShow tabsactivate fsqm.jSignatureRedraw', function(e) {
				var that = this;
				setTimeout( $.proxy( function() {
					$(this).find('.ipt_uif_jsignature_pad').each(function() {
						var elm = $( this );
						// Do only if it is visible
						if ( elm.is(':visible') && e.type === 'iptUIFCShow' && ! $( e.target ).find( '.ipt_uif_jsignature_pad' ).is( $( this ) ) ) {
							return true;
						}
						// Not inside a hidden tab
						// Not inside a hidden conditional element
						if ( elm.closest('.ui-tabs-panel[aria-hidden="true"]').length === 0 && elm.closest('.iptUIFCHidden').length === 0 ) {
							// Get the data
							var signData = elm.prev('.ipt_uif_jsignature_input').val();

							// Reinit
							setTimeout(function() {
								// Destryo the canvas
								var sig = elm.find('canvas').data('jSignature.this');
								elm.data( 'eFormjSignatureUpdating', true );
								if(sig) {
									sig.$controlbarLower.remove();
									sig.$controlbarUpper.remove();
									$(sig.canvas).remove();
								}
								elm.jSignature({
									lineWidth: 2,
									UndoButton: true
								});
								if ( signData !== '' && signData !== null && signData !== undefined && signData != 'image/jsignature;base30,' ) {
									elm.jSignature('setData', signData, 'base30');
								}
								elm.data( 'eFormjSignatureUpdating', false );
							}, 100);
						}
					});
				}, this ), 500 );
			});
		},

		// Waypoints
		edApplyWayPoints: function() {
			// if ( typeof Waypoint !== 'function' || typeof Waypoint.Context !== 'function' ) {
			// 	return;
			// }
			this.jElement.on('iptUIFCHide iptUIFCShow iptUICollapsible refreshWaypoints.eform', function() {
				// Compatibility layer to work with older waypoints
				try {
					Waypoint.refreshAll();
				} catch( e ) {
					$.waypoints( 'refresh' );
				}
			});
		},

		// Fileuploader
		edApplyUploader: function() {
			if ( typeof( $.fn.fileupload ) == 'undefined' ) {
				return;
			}
			this.jElement.on( 'dragover', '.fileinput-dragdrop', function() {
				$(this).addClass('hover');
			} );
			this.jElement.on( 'dragleave', '.fileinput-dragdrop', function() {
				$(this).removeClass('hover');
			} );
		},

		// Smiley Rating
		edApplySmileyRating: function() {
			this.jElement.on( 'change', 'input.ipt_uif_smiley_rating_radio', function(e) {
				var parent = $(this).closest('.ipt_uif_rating');
				if ( $(this).is(':checked') && parent.find('.ipt_uif_smiley_rating_feedback_wrap') ) {
					parent.addClass('ipt_uif_smiley_feedback_active');
				} else {
					parent.removeClass('ipt_uif_smiley_feedback_active');
				}
			} );
			this.jElement.on( 'fsqm.check_smiley', function() {
				$(this).find('.ipt_uif_rating_smiley').each(function() {
					if ( $(this).find('input.ipt_uif_smiley_rating_radio:checked').length ) {
						$(this).addClass('ipt_uif_smiley_feedback_active');
					} else {
						$(this).removeClass('ipt_uif_smiley_feedback_active');
					}
				});
			} );
		},

		// Like Dislike
		edApplyLikeDislikeRating: function() {
			this.jElement.on( 'change', 'input.ipt_uif_likedislike_rating_radio', function(e) {
				var parent = $(this).closest('.ipt_uif_rating');
				if ( $(this).is(':checked') && parent.find('.ipt_uif_likedislike_rating_feedback_wrap') ) {
					parent.addClass('ipt_uif_likedislike_feedback_active');
				} else {
					parent.removeClass('ipt_uif_likedislike_feedback_active');
				}
			} );
			this.jElement.on( 'fsqm.check_likedislike', function() {
				if ( $(this).find('input.ipt_uif_likedislike_rating_radio:checked').length ) {
					$(this).addClass('ipt_uif_likedislike_feedback_active');
				} else {
					$(this).removeClass('ipt_uif_likedislike_feedback_active');
				}
			} );
		},

		// Rating elements
		edApplyRating: function() {
			// Add event for mouseenter
			this.jElement.on( 'mouseenter', '.ipt_uif_rating label', function() {
				$(this).siblings('input').removeClass('active');
				$(this).prevAll('input').addClass('hover');
			} );
			// Add event for mouseleave
			this.jElement.on( 'mouseleave', '.ipt_uif_rating label', function() {
				$(this).prevAll('input').removeClass('hover');
				$(this).siblings('input:checked').addClass('active').prevAll('input').addClass('active');
			} );
			// Add event for change
			this.jElement.on( 'change', '.ipt_uif_rating input', function() {
				if ( $(this).is(':checked') ) {
					$(this).nextAll('input').removeClass('active');
					$(this).addClass('active');
					$(this).prevAll('input').addClass('active');
				}
			} );
		},

		// Tab Toggler
		edTabToggler: function() {
			this.jElement.on( 'click', '.ipt_uif_tabs_toggler', function(e) {
				e.preventDefault();
				e.stopPropagation();
				$(this).siblings('.ui-tabs-nav').toggleClass('ipt_uif_tabs_toggle_active');
			} );
		},

		// Scroll to top
		edApplyScrollToTop: function() {
			var container = this.jElement;
			this.jElement.on( 'click', '.ipt_uif_scroll_to_top', function(e) {
				e.preventDefault();
				var scrollTo = container.offset().top - 10;
				var htmlTop = parseFloat($('html').css('margin-top'));
				if(isNaN(htmlTop)) {
					htmlTop = 0;
				}
				htmlTop += parseFloat($('html').css('padding-top'));
				if(!isNaN(htmlTop) || htmlTop !== 0) {
					scrollTo -= htmlTop;
				}
				$('html, body').animate({scrollTop : scrollTo}, 'fast');
			} );
		},

		// Message close
		edApplyMessage: function() {
			this.jElement.on( 'click', '.ipt_uif_message_close', function(e) {
				e.preventDefault();
				$(this).closest('.ipt_uif_message').fadeOut('fast');
			} );
		},

		// Help Toggler
		edApplyHelp: function(e) {
			this.jElement.on( 'click', '.ipt_uif_msg', function(e) {
				e.preventDefault();
				var trigger = $(this).find('.ipt_uif_msg_icon'),
				title = trigger.attr('title'),
				temp, dialog_content;

				if( undefined === title || '' === title ) {
					if( undefined !== ( temp = trigger.parent().parent().siblings('th').find('label').html() ) ) {
						title = temp;
					} else {
						title = iptPluginUIFAdmin.L10n.help;
					}
				}

				dialog_content = $('<div><div style="padding: 10px;">'  + trigger.next('.ipt_uif_msg_body').html() + '</div></div>');
				var buttons = {};
				buttons[iptPluginUIFAdmin.L10n.got_it] = function() {
					$(this).dialog("close");
				};
				dialog_content.dialog({
					autoOpen: true,
					buttons: buttons,
					modal: true,
					minWidth: 600,
					closeOnEscape: true,
					title: title,
					//appendTo : '.ipt_uif_common',
					create : function(event, ui) {
						$('body').addClass('ipt_uif_common');
					},
					close : function(event, ui) {
						$('body').removeClass('ipt_uif_common');
					}
				});
			} );
		},

		// Checkbox Toggler
		edCheckboxToggler: function() {
			// Apply the delegated listen to the change event
			this.jElement.on( 'change', '.ipt_uif_checkbox_toggler', function() {
				var selector = $($(this).data('selector')),
				self = $(this);
				if(self.is(':checked')) {
					selector.prop('checked', true);
				} else {
					selector.prop('checked', false);
				}
			} );
		},

		// Slider input event
		edSliderInput: function() {
			// Listen to the first input change
			this.jElement.on( 'blur fsqm.slider', '.ipt_uif_slider', function() {
				var _self = $(this), second_input, slider, count_div = _self.siblings('.ipt_uif_slider_count'), values, value;
				// If it is a range
				if ( _self.hasClass('slider_range') ) {
					second_input = _self.siblings('.ipt_uif_slider_range_max');
					slider = second_input.siblings('.ipt_uif_slider_div');
					values = [ parseFloat(_self.val()), parseFloat(second_input.val()) ];
					if ( isNaN( values[0] ) ) {
						values[0] = 0;
					}
					if ( isNaN( values[1] ) ) {
						values[1] = 0;
					}
					slider.slider({
						values : values
					});
					count_div.find('span.ipt_uif_slider_count_min').text( parseFloat( _self.val() ) );
				// If it is a slider
				} else {
					slider = _self.siblings('.ipt_uif_slider_div');
					value = parseFloat(_self.val());
					if ( isNaN( value ) ) {
						value = 0;
					}
					slider.slider({
						value : value
					});
					count_div.find('span').text(parseFloat(_self.val()));
				}
			} );

			// Listen to the second input change
			this.jElement.on( 'blur fsqm.slider', '.ipt_uif_slider_range_max', function() {
				var _self = $(this),
				first_input = _self.siblings('.ipt_uif_slider'),
				slider = _self.siblings('.ipt_uif_slider_div'),
				count_div = first_input.siblings('.ipt_uif_slider_count');
				slider.slider({
					values : [parseFloat(first_input.val()), parseFloat(_self.val())]
				});
				count_div.find('span.ipt_uif_slider_count_max').text( parseFloat( _self.val() ) );
			} );

			// Listen to slide and slide_change
			// And update the count
			this.jElement.on( 'slide slidechange', '.ipt_uif_slider_div', function(e, ui) {
				// Set the vars
				var _self = $(this),
				countDiv = _self.siblings('.ipt_uif_slider_count'),
				first_input = _self.siblings('.ipt_uif_slider'),
				second_input = _self.siblings('.ipt_uif_slider_range_max');

				// If for range
				if ( _self.hasClass('ipt_uif_slider_range') ) {
					// Change the inputs
					first_input.val( ui.values[0] ).trigger( 'change' ).validationEngine( 'validate' );
					second_input.val( ui.values[1] ).trigger( 'change' ).validationEngine( 'validate' );

					// Update counddiv
					if ( countDiv.length ) {
						countDiv.find('span.ipt_uif_slider_count_min').text(ui.values[0]);
						countDiv.find('span.ipt_uif_slider_count_max').text(ui.values[1]);
					}

				// If for single
				} else {
					// Change the input
					first_input.val( ui.value ).trigger( 'change' ).validationEngine( 'validate' );

					// Update countDiv
					if ( countDiv.length ) {
						countDiv.find('span').text(ui.value);
					}
				}
			} );
		},

		// Spinners
		edApplySpinner: function() {
			this.jElement.on( 'mousewheel', '.ipt_uif_uispinner', function() {
				$(this).trigger('change');
			} );
			// this.jElement.on( 'spinstop', '.ipt_uif_uispinner', function() {
			// 	$(this).trigger('change');
			// } );
		},

		// DateTime NOW Button
		edDateTimeNow: function() {
			this.jElement.on( 'click', '.ipt_uif_datepicker_now', function() {
				$(this).nextAll('.ipt_uif_text').val('NOW');
			} );
			this.jElement.on( 'click', '.ipt_fsqm_container_datetime .ipticm', function() {
				var elm = $( this ),
				pickerInput = elm.closest( '.ipt_fsqm_container_datetime' ).find( '.ipt_uif_text' );
				pickerInput.focus();
			} );
			this.jElement.on( 'click', '.eform-dp-clear', function( e ) {
				e.preventDefault();
				var elm = $( this ),
				pickerWrapper = elm.closest( '.eform-dp-input-field' ),
				pickerField = pickerWrapper.find( 'input.datepicker' );
				pickerField.val( '' );
				pickerField.addClass( 'is-empty' );
				pickerField.trigger( 'blur' );
				try {
					pickerField.validationEngine( 'validate' );
				} catch( e ) {

				}
			} );
		},

		// Print element
		edApplyPrintElement: function() {
			var that = this;
			this.jElement.on( 'click', '.ipt_uif_printelement', function() {
				$('#' + $(this).data('printid')).printElement({
					leaveOpen:true,
					printMode:'popup',
					printBodyOptions : {
						classNameToAdd : 'ipt_uif_common ' + that.ui_theme_id,
						styleToAdd : 'padding:10px;margin:10px;background: #fff none;color:#333;font-size:12px;'
					},
					pageTitle : document.title
				});
			} );
		},

		// Conditional Input
		edApplyConditionalInput: function() {
			this.jElement.on( 'change', '.ipt_uif_conditional_input', function(e) {
				// init vars
				var _self = $(this),
				inputs = _self.find('input'),
				shown = [], hidden = [], input_ids, i;

				// loop through and populate vars
				inputs.each(function() {
					input_ids = $(this).data('condid');
					if ( typeof ( input_ids ) == 'string' ) {
						input_ids = input_ids.split( ',' );
					} else {
						input_ids = [];
					}

					if ( $(this).is(':checked') ) {
						shown.push.apply( shown, input_ids );
					} else {
						hidden.push.apply( hidden, input_ids );
					}
				});

				// hide all that would be hidden
				for ( i = 0; i < hidden.length; i++ ) {
					$('#' + hidden[i]).stop( true, true ).hide();
				}

				// Now show all that would be shown
				for ( i = 0; i < shown.length; i++ ) {
					$('#' + shown[i]).stop( true, true ).fadeIn('fast');
				}

			} );
		},

		// Conditional Select
		edApplyConditionalSelect: function() {
			this.jElement.on( 'change keyup', '.ipt_uif_conditional_select', function(e) {
				// Init the vars
				var _self = $(this),
				select = _self.find('select'),
				shown = [], hidden = [], input_ids, i;

				// Loop through and populate vars
				select.find('option').each(function() {
					input_ids = $(this).data('condid');
					if ( typeof ( input_ids ) == 'string' ) {
						input_ids = input_ids.split( ',' );
					} else {
						input_ids = [];
					}

					if ( $(this).is(':selected') ) {
						shown.push.apply( shown, input_ids );
					} else {
						hidden.push.apply( hidden, input_ids );
					}
				});

				// hide all that would be hidden
				for ( i = 0; i < hidden.length; i++ ) {
					$('#' + hidden[i]).stop( true, true ).hide();
				}

				// Now show all that would be shown
				for ( i = 0; i < shown.length; i++ ) {
					$('#' + shown[i]).stop( true, true ).fadeIn('fast');
				}
			} );
		},

		// Collapsible
		edApplyCollapsible: function() {
			var that = this;
			this.jElement.on( 'click', '.ipt_uif_collapsible_handle_anchor', function(e) {
				var self = $(this),
				collapse_box = self.closest('.ipt_uif_collapsible').find('> .ipt_uif_container_inner');
				collapse_box.closest('.ipt_uif_collapsible').toggleClass('ipt_uif_collapsible_open');
				collapse_box.slideToggle( 'normal', function() {
					that.refreshiFrames( collapse_box );
					collapse_box.trigger('iptUICollapsible');
				} );

			} );
		},

		// Delete button functionality for SDA
		edSDAattachDel: function() {
			var that = this;
			this.jElement.on( 'click', '.ipt_uif_sda_del', function(e) {
				e.preventDefault();
				if ( $( this ).closest( '.ipt_uif_sda' ).hasClass( 'eform-sda-reached-min' ) ) {
					return;
				}
				that.edSDAdel( $(this) );
			} );
		},
		edSDAdel: function( self ) {
			var that = this;
			var target = self.closest( '.ipt_uif_sda_elem' ),
			sdaItem = self.closest('.ipt_uif_sda'),
			submitButton = sdaItem.find( '> .ipt_uif_sda_foot button.ipt_uif_sda_button' ),
			vars = sdaItem.data('iptSDAdata'),
			total = 0;

			target.slideUp( 'fast', function() {
				target.stop().remove();
				total = sdaItem.find( '> .ipt_uif_sda_body > .ipt_uif_sda_elem' ).length;
				if ( vars.max !== '' && vars.max > 0 ) {
					if ( total < vars.max ) {
						submitButton.show();
					}
				}
				if ( vars.min !== '' && vars.min > 0 ) {
					if ( total <= vars.min ) {
						sdaItem.addClass( 'eform-sda-reached-min' );
					} else {
						sdaItem.removeClass( 'eform-sda-reached-min' );
					}
				}
				if ( 0 == total ) {
					sdaItem.addClass( 'ipt-uif-sda-empty' );
				} else {
					sdaItem.removeClass( 'ipt-uif-sda-empty' );
				}
				sdaItem.trigger( 'fsqm.conditional' ).trigger( 'fsqm.mathematicalReEvaluate' );
				that.jElement.trigger( 'refreshWaypoints.eform' );
			} ).css( {
				opacity: 1
			} ).animate( {
				opacity: 0
			}, 'fast' );
		},

		// Add button functionality for SDA
		edSDAattachAdd: function() {
			//.ipt_uif_sda_foot button.ipt_uif_sda_button
			var that = this;
			this.jElement.on( 'click', '.ipt_uif_sda_foot button.ipt_uif_sda_button', function(e) {
				e.preventDefault();
				var self = $(this),
				sdaItem = self.closest('.ipt_uif_sda'),
				vars = sdaItem.data('iptSDAdata'),
				add_string = sdaItem.find('> .ipt_uif_sda_data').text(),
				count = vars.count++,
				re = new RegExp( that.quote(vars.key), 'g' ), new_div, old_color;

				// Modify the element HTML
				add_string = $('<div></div>').html(add_string).text();
				add_string = add_string.replace( re, count );

				// Add the element HTML to a new DOM
				new_div = $('<div class="ipt_uif_sda_elem" />').append($(add_string));

				// Append to the SDA body
				sdaItem.find('> .ipt_uif_sda_body').append(new_div);

				// Apply the UI framework
				new_div.iptPluginUIFFront({
					applyUIOnly: true
				});

				new_div.hide().slideDown( 'fast' ).css( { opacity: 0 } ).animate( { opacity: 1 }, 'fast', function() {
					var elm = new_div.find( 'input, select, textarea' ).eq( 0 );
					elm.focus();
					if ( elm.is( 'input' ) ) {
						elm.addClass( 'tabbed' );
						elm.one( 'blur', function() {
							$( this ).removeClass( 'tabbed' );
						} );
					}
				} );

				self.data( 'count', vars.count );
				self.attr( 'data-count', vars.count );

				var total = sdaItem.find( '> .ipt_uif_sda_body > .ipt_uif_sda_elem' ).length;
				if ( vars.max !== '' && vars.max > 0 ) {
					if ( total >= vars.max ) {
						self.hide();
					}
				}
				if ( vars.min !== '' && vars.min > 0 ) {
					if ( total <= vars.min ) {
						sdaItem.addClass( 'eform-sda-reached-min' );
					} else {
						sdaItem.removeClass( 'eform-sda-reached-min' );
					}
				}
				if ( 0 == total ) {
					sdaItem.addClass( 'ipt-uif-sda-empty' );
				} else {
					sdaItem.removeClass( 'ipt-uif-sda-empty' );
				}
				sdaItem.trigger( 'fsqm.conditional' ).trigger( 'fsqm.mathematicalReEvaluate' );
				that.jElement.trigger( 'refreshWaypoints.eform' );
			} );
		},

		/**
		 * Mathematical Evaluator methods
		 */
		evaluateMathematicalFormula: function(that) {
			var self = $(this),
			formula = self.data('formula'), i;
			if ( ! formula ) {
				return;
			}
			var precision = self.data('precision'),
			options = self.data('options'),
			noanim = self.data('noanim');
			if ( ! options ) {
				options = {};
			}

			var expr = Parser.parse(formula.toString()).simplify(),
			variables = expr.variables(),
			replacement = {};

			for ( i in variables ) {
				replacement[variables[i]] = that.getMathematicalValue.apply( that, [ variables[i] ] );
			}

			var result, prevResult;
			try {
				result = expr.evaluate(replacement);
			} catch(e) {
				result = 0;
			}

			if ( isNaN( result ) ) {
				result = 0;
			}

			// Now check if the precision is set to auto
			if ( '' === precision ) {
				precision = that.decimalPlaces( result );
			} else {
				precision = that.intelParseFloat( precision );
				result = result.toFixed(precision);
			}

			prevResult = self.val();
			self.val(result); //
			if ( prevResult != result ) {
				self.trigger('fsqm.conditional').trigger('fsqm.mathematicalReEvaluate').trigger('change');
				var spanNext = $(this).next('span.ipt_uif_mathematical_span');
				// Change and animate
				if ( spanNext.length && false === noanim ) {
					var spanNextPrevVal = null,
					spanNextCountUp = spanNext.data('iptUIFMathCU');
					if ( spanNextCountUp !== undefined ) {
						spanNextCountUp.reset();
					}
					spanNextPrevVal = spanNext.data('iptUIFMathPV');
					if ( ! spanNextPrevVal || spanNextPrevVal === undefined ) {
						spanNextPrevVal = that.intelParseFloat( spanNext.text() );
					}
					if ( ! isFinite( spanNextPrevVal ) ) {
						spanNextPrevVal = 0;
					}
					spanNextCountUp = new CountUp( spanNext.get(0), spanNextPrevVal, that.intelParseFloat( result ), precision, 2, options);
					spanNextCountUp.start();
					spanNext.data('iptUIFMathCU', spanNextCountUp);
				// Just change
				} else if ( spanNext.length ) {
					spanNext.html( that.formatNumber( result, precision, options.decimal, options.useGrouping, options.separator ) );
				}
				// Store the actual value
				spanNext.data('iptUIFMathPV', that.intelParseFloat( result ));
			}
		},

		getMathematicalValue: function( variable ) {
			var self = this.jElement,
			that = this,
			varToElem = self.data('iptFSQMMathVarToElem');
			// Set the varToElem data
			if ( ! varToElem ) {
				self.data('iptFSQMMathVarToElem', {});
				varToElem = {};
			}

			// Populate varToElem if needed
			if ( varToElem[variable] === undefined ) {
				var regEx = /([MFO])(\d+)((R)(\d+))?((C)(\d+))?/gi,
				elemParts = regEx.exec( variable ),
				varToElemMap = {
					"M" : 'mcq',
					"F" : 'freetype',
					"O" : 'pinfo'
				};

				if ( elemParts !== null && varToElemMap[elemParts[1]] !== undefined ) {
					// Now find the element
					var form_id = self.find('[name="form_id"]').val(),
					elementWrapper = 'ipt_fsqm_form_' + form_id + '_' + varToElemMap[elemParts[1]] + '_' + elemParts[2],
					elementType = $( '#ipt_fsqm_form_' + form_id + '_' + varToElemMap[elemParts[1]] + '_' + elemParts[2] + '_type' ).val();

					varToElem[variable] = {
						'elem' : $('#' + elementWrapper),
						'parts' : elemParts,
						'type' : elementType
					};

				}
			}

			if ( varToElem[variable] === undefined ) {
				return 0;
			}

			if ( varToElem[variable].elem.hasClass('iptUIFCHidden') ) {
				return 0;
			}

			// Now get the value
			var returnVal = 0, elemIndex, numericValue, rowIndex, colIndex;
			switch ( varToElem[variable].type ) {
				case 'radio' :
				case 'p_radio' :
				case 'checkbox' :
				case 'p_checkbox' :
				case 'thumbselect' :
					varToElem[variable].elem.find('input').filter(':checked').each(function() {
						numericValue = that.intelParseFloat( $(this).data('num') );
						returnVal += numericValue;
					});
					break;

				case 'select' :
				case 'p_select' :
					varToElem[variable].elem.find('select > option:selected').each(function() {
						numericValue = that.intelParseFloat( $(this).data('num') );
						returnVal += numericValue;
					});
					break;

				case 'slider' :
					returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_slider').val() );
					break;
				case 'range' :
					colIndex = varToElem[ variable ].parts[8];
					if ( undefined == colIndex || 0 == colIndex ) {
						returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_slider').val() );
					} else {
						returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_slider_range_max').val() );
					}
					break;

				case 'grading' :
					elemIndex = varToElem[variable].parts[5];
					colIndex = varToElem[ variable ].parts[8];
					if ( elemIndex === undefined ) {
						if ( undefined == colIndex || 0 == colIndex ) {
							varToElem[variable].elem.find('input.ipt_uif_slider').each(function() {
								returnVal += that.intelParseFloat( $(this).val() );
							});
						} else {
							varToElem[variable].elem.find('input.ipt_uif_slider_range_max').each(function() {
								returnVal += that.intelParseFloat( $(this).val() );
							});
						}
					} else {
						if ( undefined == colIndex || 0 == colIndex ) {
							returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_slider').eq(elemIndex).val() );
						} else {
							returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_slider_range_max').eq(elemIndex).val() );
						}
					}
					break;

				case 'starrating' :
				case 'scalerating' :
					elemIndex = varToElem[variable].parts[5];
					if ( elemIndex === undefined ) {
						varToElem[variable].elem.find('.ipt_uif_rating').each(function() {
							returnVal += that.intelParseFloat( $(this).find('input:checked').val() );
						});
					} else {
						returnVal += that.intelParseFloat( varToElem[variable].elem.find('.ipt_uif_rating').eq(elemIndex).find('input:checked').val() );
					}

					break;

				case 'spinners' :
					elemIndex = varToElem[variable].parts[5];
					if ( elemIndex === undefined ) {
						varToElem[variable].elem.find('input.ipt_uif_uispinner').each(function() {
							returnVal += that.intelParseFloat( $(this).val() );
						});
					} else {
						returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_uispinner').eq(elemIndex).val() );
					}
					break;

				case 'feedback_small' :
				case 'textinput' :
				case 'keypad' :
					returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_text').val() );
					break;

				case 'mathematical' :
					returnVal += that.intelParseFloat( varToElem[variable].elem.find('input.ipt_uif_mathematical_input').val() );
					break;
				case 'toggle' :
					if ( varToElem[variable].elem.find('input.ipt_uif_switch').is(':checked') ) {
						returnVal = 1;
					} else {
						returnVal = 0;
					}
					break;
				case 's_checkbox' :
					if ( varToElem[variable].elem.find('input.ipt_uif_checkbox').is(':checked') ) {
						returnVal = 1;
					} else {
						returnVal = 0;
					}
					break;
				case 'smileyrating' :
					var selectedSmiley = varToElem[variable].elem.find('input.ipt_uif_radio').filter(':checked');
					if ( selectedSmiley.length ) {
						returnVal = that.intelParseFloat( selectedSmiley.data('num') );
					} else {
						returnVal = 0;
					}
					break;
				case 'likedislike' :
					var selectedState = varToElem[variable].elem.find('input.ipt_uif_radio').filter(':checked').val();
					if ( selectedState == 'like' ) {
						returnVal = 1;
					} else {
						returnVal = 0;
					}
					break;
				case 'matrix_dropdown' :
					returnVal = 0;
					rowIndex = varToElem[ variable ].parts[5];
					colIndex = varToElem[ variable ].parts[8];
					// If both rowIndex & colIndex
					if ( rowIndex !== undefined && colIndex !== undefined ) {
						// We need to pick the specific element
						varToElem[ variable ].elem.find( 'tbody > tr' ).eq( rowIndex ).find( 'select.ipt_uif_select' ).eq( colIndex ).find( 'option:selected' ).each( function() {
							returnVal += that.intelParseFloat( $(this).data('num') );
						} );
					// If just rowIndex
					} else if ( rowIndex !== undefined && colIndex === undefined ) {
						varToElem[ variable ].elem.find( 'tbody > tr' ).eq( rowIndex ).find( 'select.ipt_uif_select' ).find( 'option:selected' ).each( function() {
							returnVal += that.intelParseFloat( $(this).data('num') );
						} );
					// If just colIndex
					} else if ( rowIndex === undefined && colIndex !== undefined ) {
						varToElem[ variable ].elem.find( 'tbody > tr' ).each( function() {
							$( this ).find( 'select.ipt_uif_select' ).eq( colIndex ).find( 'option:selected' ).each( function() {
								returnVal += that.intelParseFloat( $(this).data('num') );
							} );
						} );
					// Both undefined, so add all
					} else {
						varToElem[ variable ].elem.find( 'select.ipt_uif_select' ).find( 'option:selected' ).each( function() {
							returnVal += that.intelParseFloat( $(this).data('num') );
						} );
					}
					break;
				case 'matrix' :
					rowIndex = varToElem[ variable ].parts[5];
					colIndex = varToElem[ variable ].parts[8];
					returnVal = 0;
					// If both rowIndex & colIndex
					if ( rowIndex !== undefined && colIndex !== undefined ) {
						// We need to pick the specific element
						varToElem[ variable ].elem.find( 'tbody > tr' ).eq( rowIndex ).find( '.ipt_uif_radio , .ipt_uif_checkbox' ).eq( colIndex ).filter( ':checked' ).each( function() {
							returnVal += that.intelParseFloat( $(this).data('num') );
						} );
					// If just rowIndex
					} else if ( rowIndex !== undefined && colIndex === undefined ) {
						varToElem[ variable ].elem.find( 'tbody > tr' ).eq( rowIndex ).find( '.ipt_uif_radio , .ipt_uif_checkbox' ).filter( ':checked' ).each( function() {
							returnVal += that.intelParseFloat( $(this).data('num') );
						} );
					// If just colIndex
					} else if ( rowIndex === undefined && colIndex !== undefined ) {
						varToElem[ variable ].elem.find( 'tbody > tr' ).each( function() {
							$( this ).find( '.ipt_uif_radio , .ipt_uif_checkbox' ).eq( colIndex ).filter( ':checked' ).each( function() {
								returnVal += that.intelParseFloat( $(this).data('num') );
							} );
						} );
					// Both undefined, so add all
					} else {
						varToElem[ variable ].elem.find( '.ipt_uif_radio , .ipt_uif_checkbox' ).filter( ':checked' ).each( function() {
							returnVal += that.intelParseFloat( $(this).data('num') );
						} );
					}
					break;
				case 'repeatable' :
					rowIndex = varToElem[ variable ].parts[5];
					colIndex = varToElem[ variable ].parts[8];
					returnVal = 0;
					// If both rowIndex & colIndex
					if ( rowIndex !== undefined && colIndex !== undefined ) {
						// We need to pick the specific element
						varToElem[ variable ].elem.find( '.ipt_uif_sda_elem' ).eq( rowIndex ).find( '> .ipt_uif_column' ).eq( colIndex ).each( function() {
							returnVal += that._repeatableMathematicalValue( $( this ) );
						} );
					// If just rowIndex
					} else if ( rowIndex !== undefined && colIndex === undefined ) {
						varToElem[ variable ].elem.find( '.ipt_uif_sda_elem' ).eq( rowIndex ).each( function() {
							returnVal += that._repeatableMathematicalValue( $( this ) );
						} );
					// If just colIndex
					} else if ( rowIndex === undefined && colIndex !== undefined ) {
						varToElem[ variable ].elem.find( '.ipt_uif_sda_elem' ).each( function() {
							$( this ).find( '> .ipt_uif_column' ).eq( colIndex ).each( function() {
								returnVal += that._repeatableMathematicalValue( $( this ) );
							} )
						} )
					// Both undefined, so add all
					} else {
						returnVal += that._repeatableMathematicalValue( varToElem[ variable ].elem );
					}
					break;
				case 'datetime':
					returnVal = varToElem[variable].elem.find( '.ipt_uif_datepicker, .ipt_uif_datetimepicker' ).val();
					// Convert to JS can comprehend it
					if ( returnVal ) {
						returnVal = new Date( returnVal.replace( /-/g, '/' ) );
						returnVal = Math.floor( returnVal / 8.64e7 );
					} else {
						returnVal = 0;
					}
					break;
				default :
					that.debugLog('Error! Element not supported by mathematical evaluator. Element variable: ' + variable, true );
					returnVal = 0;
			}

			self.data('iptFSQMMathVarToElem', varToElem);
			return returnVal;
		},


		/**
		 * Other functions
		 *
		 * @internal
		 */
		testImage : function(filename) {
			return (/\.(gif|jpg|jpeg|tiff|png)$/i).test(filename);
		},

		quote : function(str) {
			return str.replace(/([.?*+^$[\]\\(){}|-])/g, "\\$1");
		},

		stripTags: function( string ) {
			var tempDOM = $('<div />'),
			stripped = '';
			tempDOM.html(string);
			stripped = tempDOM.text();
			tempDOM.remove();
			return stripped;
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

		decimalPlaces: function( num ) {
			var match = (''+num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);
			if (!match) { return 0; }
			return Math.max(
				0,
				// Number of digits right of decimal point.
				( match[1] ? match[1].length : 0 ) -
				// Adjust for scientific notation.
				( match[2] ? +match[2] : 0 )
			);
		},

		_repeatableMathematicalValue: function( elm ) {
			var returnVal = 0,
			that = this;
			elm.find( 'input[type="radio"], input[type="checkbox"]' ).filter( ':checked' ).each( function() {
				returnVal += that.intelParseFloat( $(this).data('num') );
			} );
			elm.find( 'select option' ).filter( ':selected' ).each( function() {
				returnVal += that.intelParseFloat( $(this).data('num') );
			} );
			elm.find( 'input.ipt_uif_text' ).each( function() {
				returnVal += that.intelParseFloat( $( this ).val() );
			} );
			return returnVal;
		},

		/**
		 * Format a number using decimal point and separator
		 *
		 * @link http://stackoverflow.com/a/149099/2754557
		 *
		 * @param      {number}       n       The number to format
		 * @param      {number}       c       Decimal Precision
		 * @param      {string}       d       Decimal Separator
		 * @param      {bool}         g       Use grouping
		 * @param      {string}       t       Thousands separator
		 * @return     {string}       Formatted number
		 */
		formatNumber: function( n, c, d, g, t ) {
			c = isNaN(c = Math.abs(c)) ? 2 : c;
			d = d === undefined ? "." : d;
			t = t === undefined ? "," : t;
			g = g === undefined ? true : g;
			if ( g !== true ) {
				t = '';
			}
			var s = n < 0 ? "-" : "",
			i = parseInt(n = Math.abs(+n || 0).toFixed(c), 10) + "",
			j = (j = i.length) > 3 ? j % 3 : 0;
			return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
		},

		refreshiFrames: function() {
			var _self = $(this);
			_self.find('iframe').each(function() {
				if ( $( this ).closest( '.g-recaptcha' ).length ) {
					return;
				}
				$(this).attr('src', $(this).attr('src'));
			});
			_self.find('video').each(function() {
				try {
					this.pause();
				} catch (e) {
					if ( console && console.log ) {
						console.log(e);
					}
				}
			});

			// Update mediaElements
			if ( undefined != window.mejs ) {
				try {
					$( '.wp-video-shortcode' ).each( function() {
						var elm = $( this ),
						width = elm.attr( 'width' ),
						height = elm.attr( 'height' ),
						playerID = elm.closest( '.mejs-container' ).attr( 'id' ),
						player = window.mejs.players[ playerID ];
						player.setPlayerSize( width, height );
					} )
				} catch( e ) {
					console.log( e );
				}
			}
		},

		dates: {
			convert:function(d) {
				// Converts the date in d to a date-object. The input can be:
				//   a date object: returned without modification
				//  an array      : Interpreted as [year,month,day]. NOTE: month is 0-11.
				//   a number     : Interpreted as number of milliseconds
				//                  since 1 Jan 1970 (a timestamp)
				//   a string     : Any format supported by the javascript engine, like
				//                  "YYYY/MM/DD", "MM/DD/YYYY", "Jan 31 2009" etc.
				//  an object     : Interpreted as an object with year, month and date
				//                  attributes.  **NOTE** month is 0-11.
				return (
					d.constructor === Date ? d :
					d.constructor === Array ? new Date(d[0],d[1],d[2]) :
					d.constructor === Number ? new Date(d) :
					d.constructor === String ? new Date(d) :
					typeof d === "object" ? new Date(d.year,d.month,d.date) :
					NaN
				);
			},
			compare:function(a,b) {
				// Compare two dates (could be of any type supported by the convert
				// function above) and returns:
				//  -1 : if a < b
				//   0 : if a = b
				//   1 : if a > b
				// NaN : if a or b is an illegal date
				// NOTE: The code inside isFinite does an assignment (=).
				return (
					isFinite(a=this.convert(a).valueOf()) &&
					isFinite(b=this.convert(b).valueOf()) ?
					(a>b)-(a<b) :
					NaN
				);
			},
			inRange:function(d,start,end) {
				// Checks if date in d is between dates in start and end.
				// Returns a boolean or NaN:
				//    true  : if d is between start and end (inclusive)
				//    false : if d is before start or after end
				//    NaN   : if one or more of the dates is illegal.
				// NOTE: The code inside isFinite does an assignment (=).
			   return (
					isFinite(d=this.convert(d).valueOf()) &&
					isFinite(start=this.convert(start).valueOf()) &&
					isFinite(end=this.convert(end).valueOf()) ?
					start <= d && d <= end :
					NaN
				);
			}
		},
		// Update province based on country value
		_updateProvince: function( countryAutoComplete, provincePreset ) {
			if ( undefined === provincePreset ) {
				provincePreset = false;
			}
			var country = null, i, json = null, provinceAutoComplete, that = this;

			if ( provincePreset ) {
				provinceAutoComplete = countryAutoComplete;
				json = provinceAutoComplete.data( 'presetCountry' );
			} else {
				country = countryAutoComplete.val();
				if ( ! country || '' === country ) {
					return;
				}
				provinceAutoComplete = countryAutoComplete.closest( '.ipt_fsqm_container_address' ).find( '.ipt-eform-address-province .ipt_uif_autocomplete' );
				// If no province
				if ( ! provinceAutoComplete.length ) {
					return;
				}
				// Check to see if this is a valid country
				for ( i in this.countryList ) {
					if ( country == this.countryList[ i ] ) {
						json = i;
						break;
					}
				}
			}

			// If not a valid country
			if ( null == json ) {
				// Reset autocomplete
				provinceAutoComplete.autocomplete( 'option', 'source', [] );
				return;
			}
			// If already in cache
			if ( undefined !== this.provinceCache[ json ] ) {
				provinceAutoComplete.autocomplete( 'option', 'source', this.provinceCache[ json ] );
				return;
			}

			provinceAutoComplete.parent().addClass( 'working' );

			// Not in cache, so get
			$.getJSON( iptPluginUIFFront.ajaxurl, {
				action: 'eform_countryjs_plist',
				country: json
			}, function( province, textStatus ) {
				// Cache it
				that.provinceCache[ json ] = province;
				// Set it
				provinceAutoComplete.autocomplete( 'option', 'source', that.provinceCache[ json ] );
			} ).fail( function() {
				// Cound't get so reset
				provinceAutoComplete.autocomplete( 'option', 'source', [] );
			} ).always( function() {
				provinceAutoComplete.parent().removeClass( 'working' );
			} )
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
		},
		refreshiFrames: function() {
			var _self = $(this);
			_self.find('iframe').each(function() {
				if ( $( this ).closest( '.g-recaptcha' ).length ) {
					return;
				}
				$(this).attr('src', $(this).attr('src'));
			});
			_self.find('video').each(function() {
				try {
					this.pause();
				} catch (e) {
					if ( console && console.log ) {
						console.log(e);
					}
				}
			});
			return this;
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
