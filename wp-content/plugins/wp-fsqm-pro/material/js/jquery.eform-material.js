/**
 * eForm Material Theme Interactivity
 */
// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;(function ( $, window, document, undefined ) {
	"use strict";

	// Create the defaults once
	var pluginName = "eFormMaterial",
	defaults = {
		uiOnly: false,
		propertyName: "value"
	};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		this.jElement = $( this.element );
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;

		// Set some variables beforehand
		this.mainForm = this.jElement.find( '.ipt_fsqm_main_form' );
		this.mainTab = this.jElement.find( '.ipt_fsqm_main_tab' );
		this.tabNavWrap = this.mainTab.find( '.ipt-eform-tab-nav-wrap' ).eq( 0 );
		this.tabNav = this.tabNavWrap.find( 'ul' ).eq( 0 );
		this.tabIndices = this.tabNav.find( '> li' );
		this.tabScrollerLeft = this.tabNavWrap.find( '.eform-tab-nav-left' );
		this.tabScrollerRight = this.tabNavWrap.find( '.eform-tab-nav-right' );
		this.activeTabNotifier = this.mainTab.find( '.eform-tab-active-notifier' ).eq( 0 );
		this.mainProgressBar = this.jElement.find( '.ipt_fsqm_main_pb' );
		this.init();
	}

	Plugin.prototype = {
		init: function () {
			// Call the UI handler
			if ( this.jElement.data( 'eFormUICompleted' ) ) {
				this.applyUI();

				// Check to see if delegated are needed
				if ( this.settings.uiOnly ) {
					return;
				}

				// Apply delegatory listeners
				this.applyED();
			}
			this.jElement.on( 'completedUI.eform', $.proxy( function() {
				this.applyUI();

				// Check to see if delegated are needed
				if ( this.settings.uiOnly ) {
					return;
				}

				// Apply delegatory listeners
				this.applyED();
			}, this ) );
		},

		// =====================================================================
		// All UI Handlers
		// =====================================================================
		// Tab handler
		uiTabHandler: function() {
			if ( ! this.mainTab.length ) {
				return;
			}
			// Check for active tab lavalamp
			this._positionTabLavaLamp();

			// Position scroll
			this._scrollMainTab();

			// Check for tab scroller appearance
			this._checkTabScroller();
		},
		// update text fields
		uiUpdateTextFields: function() {
			this._updateTextFields();
		},
		// Modify the validation engine
		modValidationEngine: function() {
			this.mainForm.validationEngine( 'detach' );
			this.mainForm.validationEngine( 'attach', {
				promptPosition: 'inline',
				onFieldFailure: function( field )  {
					var elm = $( field );
					elm.removeClass( 'invalid' ).removeClass( 'valid' );
					if ( elm.hasClass( 'ipt_uif_text' ) || elm.hasClass( 'ipt_uif_textarea' ) || elm.hasClass( 'ipt_uif_select' ) ) {
						elm.addClass( 'invalid' )
						.removeClass( 'valid' );
					}
				},
				onFieldSuccess: function( field ) {
					var elm = $( field );
					elm.removeClass( 'invalid' ).removeClass( 'valid' );
					if ( ( elm.hasClass( 'ipt_uif_text' ) || elm.hasClass( 'ipt_uif_textarea' ) || elm.hasClass( 'ipt_uif_select' ) ) && elm.val() != '' ) {
						elm.addClass( 'valid' );
					}
				}
			} );
		},
		// Log something nice in the console
		logEForm: function() {
			if ( console && console.log ) {
				try {
					var styles = [
						'background: #009688',
						'color: #fff',
						'line-height: 20px',
						'box-shadow: 0 2px 2px 0px rgba(0, 0, 0, 0.14), 0 3px 1px -2px rgba(0, 0, 0, 0.2), 0 1px 5px 0 rgba(0, 0, 0, 0.12)',
						'text-align: center',
						'padding: 10px 20px',
						'border: 1px solid #00796b',
						'line-height: 40px'
					].join( ';' );
					console.log( "%cThis website is powered by eForm - Ultimate WordPress Form Builder. https://eform.live Amazing!", styles );
				} catch( e ) {

				}
			}
		},
		// LocationPicker
		uiApplyLocationPicker: function() {
			var that = this;
			this.jElement.find( '.ipt_uif_locationpicker' ).each( function() {
				that._updateTextFields( $( this ) );
			} );
		},
		// Applies all UI related stuff
		applyUI: function() {
			// Add tab handler
			this.uiTabHandler();
			// update text fields
			this.uiUpdateTextFields();
			// Mod Validation Engine
			this.modValidationEngine();
			// LocationPicker
			this.uiApplyLocationPicker();

			// Let the world know about eForm
			this.logEForm();
		},

		// =====================================================================
		// All ED Handlers
		// =====================================================================
		// Tab handler
		edTabHandler: function() {
			if ( ! this.mainTab.length ) {
				return;
			}
			var that = this;
			this.mainTab.on( 'tabsactivate', function( event, ui ) {
				// Check for active tab lavalamp
				// We do it after scrolling, so everything stays on check
				setTimeout( function() {
					that._positionTabLavaLamp();
				}, 200 );


				// Position scroll
				that._scrollMainTab();

				// Check for tab scroller appearance
				that._checkTabScroller();
			} );

			// Check tab scroller on window resize
			$( window ).on( 'resize', $.throttle( 250, function() {
				that._positionTabLavaLamp();
				that._checkTabScroller();
			} ) );

			// Attach scroll event on the tabNav
			this.tabNav.on( 'scroll checkTabScroll.eform iptUIFCShow iptUIFCHide', $.throttle( 250, function() {
				that._checkTabScroller();
				that._positionTabLavaLamp();
			} ) );

			// Attach scroll functionality
			this.tabNavWrap.on( 'click', '.eform-tab-nav-right', function() {
				that._scrollTabNav( 'right' );
			} );
			this.tabNavWrap.on( 'click', '.eform-tab-nav-left', function() {
				that._scrollTabNav( 'left' );
			} );
		},
		// Apply Ripple Effects for given elements
		edApplyRipple: function() {
			Waves.attach( '.eform-ripple', ['waves-light'] );
			Waves.attach( '.ipt_uif_button', ['waves-light'] );
			Waves.init();

			this.jElement.on( 'dataTablesCompleted.eform', function() {
				Waves.attach( '.eform-ripple', ['waves-light'] );
				Waves.attach( '.ipt_uif_button', ['waves-light'] );
			} );
		},
		// Delegated text & textarea updater
		edTextHandler: function() {
			var that = this;
			this.jElement.on( 'updateTextFields.eform datepickerClose.eform formReset.eform', function() {
				that._updateTextFields();
			} );
		},
		// Add events to the text and textarea
		edMaterialTextHandler: function() {
			var input_selector = 'input[type=text], input[type=password], input[type=email], input[type=url], input[type=tel], input[type=number], input[type=search], textarea';

			// HTML Form Reset Handling
			this.jElement.on( 'reset', function(e) {
				var formReset = $(e.target);
				if ( formReset.is( 'form' ) ) {
					formReset.find( input_selector ).removeClass( 'valid' ).removeClass( 'invalid' );
					formReset.find( input_selector ).each( function () {
						if ( $(this).attr('value') === '' ) {
							$(this).siblings('label').removeClass('active');
						}
					} );
				}
			} );
			// Add active when element has focus
			$(document).on( 'focus', input_selector, function () {
				$(this).siblings('label, .prefix').addClass('active');
			} );

			$(document).on( 'blur', input_selector, function () {
				var $inputElement = $(this);
				var selector = ".prefix";

				if ( $inputElement.val().length === 0 && $inputElement[0].validity.badInput !== true && $inputElement.attr( 'placeholder' ) === undefined ) {
					selector += ", label";
				}

				$inputElement.siblings( selector ).removeClass( 'active' );
			});

			// Textarea Auto Resize
			var hiddenDiv = $('.hiddendiv').first();
			if (!hiddenDiv.length) {
				hiddenDiv = $('<div class="hiddendiv common"></div>');
				$('body').append(hiddenDiv);
			}
			var text_area_selector = '.materialize-textarea';

			function textareaAutoResize($textarea) {
				// Set font properties of hiddenDiv

				var fontFamily = $textarea.css('font-family');
				var fontSize = $textarea.css('font-size');
				var lineHeight = $textarea.css('line-height');

				if (fontSize) { hiddenDiv.css('font-size', fontSize); }
				if (fontFamily) { hiddenDiv.css('font-family', fontFamily); }
				if (lineHeight) { hiddenDiv.css('line-height', lineHeight); }

				if ( $textarea.attr('wrap') === "off" ) {
					hiddenDiv.css('overflow-wrap', "normal")
									 .css('white-space', "pre");
				}

				hiddenDiv.text($textarea.val() + '\n');
				var content = hiddenDiv.html().replace(/\n/g, '<br>');
				hiddenDiv.html(content);


				// When textarea is hidden, width goes crazy.
				// Approximate with half of window size

				if ( $textarea.is(':visible') ) {
					hiddenDiv.css('width', $textarea.width());
				} else {
					hiddenDiv.css('width', $(window).width()/2);
				}

				$textarea.css('height', hiddenDiv.height());
			}

			$( text_area_selector ).each( function () {
				var $textarea = $(this);
				if ( $textarea.val().length ) {
					textareaAutoResize($textarea);
				}
			} );

			this.jElement.on( 'keyup keydown autoresize', text_area_selector, function() {
				textareaAutoResize( $(this) );
			} );

			// Radio and Checkbox focus class
			var radio_checkbox = 'input[type=radio], input[type=checkbox]';
			this.jElement.on( 'keyup.radio', radio_checkbox, function( e ) {
				// TAB, check if tabbing to radio or checkbox.
				if ( e.which === 9 ) {
					var $this = $(this);
					$this.addClass( 'tabbed' );
					$this.one( 'blur', function( e ) {
						$(this).removeClass( 'tabbed' );
					} );
					return;
				}
			} );
		},
		// Apply locationPicker
		edLocationPicker: function() {
			var that = this;
			this.jElement.on( 'locationPicker.eform', '.ipt_uif_locationpicker', function() {
				that._updateTextFields( $( this ) );
			} );
		},
		// Applies all Event delegated handlers
		applyED: function() {
			// Tab Handler
			this.edTabHandler();
			// Ripple handler
			this.edApplyRipple();
			// Text and textarea handler
			this.edTextHandler();
			this.edMaterialTextHandler();
			// LocationPicker
			this.edLocationPicker();
		},

		// =====================================================================
		// Helper methods
		// =====================================================================
		//
		// Tab Related Helpers
		// =====================================================================
		// Position tab lava lamp
		_positionTabLavaLamp: function() {
			// Get current tab
			var activeIndex = this._getActiveTabIndex(),
			// Scroll offset
			liOffset = activeIndex.offset(),
			wrapperOffset = this.tabNavWrap.offset(),
			// Width
			liWidth = activeIndex.width(),
			wrapperWidth = this.tabNavWrap.outerWidth(),
			// Calculate Padding
			padding = ( liWidth - activeIndex.find( 'a' ).width() ) / 2,
			// Calculate position
			left = liOffset.left - wrapperOffset.left + padding - 10,
			right = wrapperWidth - left - liWidth + ( padding * 2 ) - 20,
			// For direction
			direction = 'left',
			currentLeft = parseFloat( this.activeTabNotifier.css( 'left' ) );

			// Calculate direction
			if ( currentLeft <= left ) {
				direction = 'right';
			}

			// Animate
			var that = this;
			if ( 'left' == direction ) {
				this.activeTabNotifier.css( 'left', left + 'px' );
			} else {
				this.activeTabNotifier.css( 'right', right + 'px' );
			}
			setTimeout( function() {
				if ( 'left' == direction ) {
					that.activeTabNotifier.css( 'right', right + 'px' );
				} else {
					that.activeTabNotifier.css( 'left', left + 'px' );
				}
			}, 100 );
		},
		// Scroll the navigation to the current active tab
		_scrollMainTab: function() {
			// No need if scrolling is not needed
			if ( ! this._isTabScrollingNeeded() ) {
				return;
			}
			var that = this;
			// We know scrolling is needed
			// So get the active index
			var activeIndex = this._getActiveTabIndex(),
			// Scroll offset
			left = 0;
			// Calculate position
			this.tabIndices.each( function() {
				var index = $( this );
				// No need to add if conditionally hidden
				if ( index.hasClass( 'iptUIFCHidden' ) ) {
					return true;
				}
				if ( index.is( activeIndex ) ) {
					return false;
				}
				left += index.outerWidth();
			} );

			// Animate
			this.tabNav.animate( {
				scrollLeft: left
			}, 100, function() {
				that.tabNav.trigger( 'checkTabScroll.eform' );
			} );
		},
		// Position and validates the tab scrollers
		_checkTabScroller: function() {
			// First check if scrolling is needed
			if ( ! this._isTabScrollingNeeded() ) {
				this.tabNavWrap.addClass( 'scroll-not-needed' );
				this.tabScrollerLeft.addClass( 'disabled' );
				this.tabScrollerRight.addClass( 'disabled' );
				return;
			}
			// Now that we know scrolling is needed, let's do this
			this.tabNavWrap.removeClass( 'scroll-not-needed' );
			// Get which one needs to be disabled
			var scrollPosition = this.tabNav.scrollLeft(),
			totalWidth = this._getTotalNavWidth(),
			navWidth = this.tabNav.width();

			// If scroll position 0, then on extreme left
			if ( 0 === scrollPosition ) {
				// Add disabled to the left nav and remove disabled from the right
				this.tabScrollerLeft.addClass( 'disabled' );
				this.tabScrollerRight.removeClass( 'disabled' );
			// If scroll + nav >= total, then on extreme right
			} else if ( ( scrollPosition + navWidth ) >= totalWidth ) {
				// Add disabled to the right and remove disabeld from left
				this.tabScrollerLeft.removeClass( 'disabled' );
				this.tabScrollerRight.addClass( 'disabled' );
			// Else somewhere in between
			} else {
				this.tabScrollerRight.removeClass( 'disabled' );
				this.tabScrollerLeft.removeClass( 'disabled' );
			}
		},
		// Scroll the tan nav to left or right
		_scrollTabNav: function( direction ) {
			if ( undefined == direction ) {
				direction = 'right';
			}
			// No need if not needed
			if ( ! this._isTabScrollingNeeded() ) {
				return false;
			}

			// Tab scrolling needed, so scroll
			var scrollPosition = this.tabNav.scrollLeft(),
			totalWidth = this._getTotalNavWidth(),
			navWidth = this.tabNav.width(),
			finalScrollPosition = scrollPosition,
			that = this;

			// If scroll position 0, then on extreme left
			if ( 0 === scrollPosition ) {
				// If direction is left, then makes no sense
				if ( 'left' == direction ) {
					return false;
				}
			// If scroll + nav >= total, then on extreme right
			} else if ( ( scrollPosition + navWidth ) >= totalWidth ) {
				// If direction is right, then makes no sense
				if ( 'right' == direction ) {
					return false;
				}
			}

			// Now scroll by 100px
			if ( 'left' == direction ) {
				finalScrollPosition -= 100;
			} else {
				finalScrollPosition += 100;
			}

			this.tabNav.animate( {
				scrollLeft: finalScrollPosition
			}, 100, function() {
				that.tabNav.trigger( 'checkTabScroll.eform' );
			} );
		},
		// Check if tab scrolling is needed
		_isTabScrollingNeeded: function() {
			var totalWidth = this._getTotalNavWidth();
			if ( totalWidth > ( this.tabNavWrap.width() - this.mainTab.find( '.eform-tab-nav' ).eq( 0 ).width() ) ) {
				return true;
			}
			return false;
		},
		// Get total width of the main tab navbar
		// Adds up all li element widths
		_getTotalNavWidth: function() {
			var totalWidth = 0;
			this.tabIndices.each( function() {
				var index = $( this );
				if ( index.hasClass( 'iptUIFCHidden' ) ) {
					return true;
				}
				totalWidth += index.outerWidth();
			} );
			return totalWidth;
		},
		// Get main tab active li
		_getActiveTabIndex: function() {
			var activeTab = this.mainTab.tabs( 'option', 'active' ),
			activeIndex = this.tabIndices.eq( activeTab );
			return activeIndex;
		},
		// Get last tab index from the list
		// Considers hiddens
		_getLastTabIndex: function() {
			var i = -1,
			loop = true,
			index = null;
			do {
				// Short circuit to save the loop
				if ( Math.abs( i ) > this.tabIndices.length ) {
					return false;
				}
				// If is conditionally hidden then continue after decreasing index
				if ( this.tabIndices.eq( i ).hasClass( 'iptUIFCHidden' ) ) {
					i--;
					continue;
				}
				index = this.tabIndices.eq( i );
				console.log( index );
				loop = false;
			} while ( loop );
			return index;
		},
		_getFirstTabIndex: function() {
			var i = 0,
			loop = true,
			index = null;
			do {
				// Short circuit to save the loop
				if ( Math.abs( i ) > this.tabIndices.length ) {
					return false;
				}
				// If conditionally hidden then continue after increasing index
				if ( this.tabIndices.eq( i ).hasClass( 'iptUIFCHidden' ) ) {
					i++;
					continue;
				}
				index = this.tabIndices.eq( i );
				loop = false;
			} while ( loop );
			return index;
		},
		// Update Text Fields
		_updateTextFields: function( container ) {
			var input_selector = 'input[type=text], input[type=password], input[type=email], input[type=url], input[type=tel], input[type=number], input[type=search], textarea';
			if ( undefined == container ) {
				container = this.jElement;
			}
			container.find( input_selector ).each( function( index, element ) {
				if ($(element).val().length > 0 || element.autofocus ||$(this).attr('placeholder') !== undefined || $(element)[0].validity.badInput === true) {
					$(this).siblings('label').addClass('active');
				}
				else {
					$(this).siblings('label').removeClass('active');
				}
			} );
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
	$( '.ipt_uif_front' ).eFormMaterial();
});
