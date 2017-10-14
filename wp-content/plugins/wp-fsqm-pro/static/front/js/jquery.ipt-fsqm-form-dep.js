/**
 * The main plugin for FSQM Forms
 *
 * This is fired after Plugin UIF INIT
 */

(function($) {
	"use strict";
	var methods = {
		init : function() { // Done
			return $(this).each(function() {
				var _self = this;
				var primary_css = {
					id : 'ipt_fsqm_primary_css',
					src : iptFSQM.location + 'css/form.css?version=' + iptFSQM.version
				},
				waypoint_animation = $(this).data('animation') == 1 ? true: false;
				methods.applySayt.apply(this);
				methods.restoreStopwatch.apply(this);
				$(this).iptPluginUIFFront({
					callback : function() {
						methods.applyFSQM.apply(_self);
						methods.applySaytRestoreTab.apply(_self);
						methods.applyGoogleAnalytics.apply(_self);
					},
					additionalThemes : [primary_css],
					waypoints: waypoint_animation
				});
			});
		},
		timerTabFormSync : {
			timerEnabled: false,
			forceProgress: false,
			forceSubmit: false
		},
		applyGoogleAnalytics: function() {
			var container = $(this),
			form = container.find('form'),
			settings = container.data('fsqmsayt'),
			main_tab = container.find('.ipt_fsqm_main_tab'),
			fsqm_ga_data = container.data('fsqmga'),
			tracker_name = '';
			console.log(fsqm_ga_data);
			// Do not do anything if settings does not say so
			if ( typeof( fsqm_ga_data ) !== 'object' || fsqm_ga_data.enabled != true ) {
				return;
			}

			// Now load the script if window.ga isn't present
			if ( window.ga === undefined && fsqm_ga_data.manual_load == true && fsqm_ga_data.tracking_id != '' ) {
				(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
				(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
				m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
				})(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

				ga( 'create', {
					trackingId: fsqm_ga_data.tracking_id,
					cookieDomain: fsqm_ga_data.cookie,
					name: 'FSQM' + fsqm_ga_data.form_id
				} );
				tracker_name = 'FSQM' + fsqm_ga_data.form_id + '.';
				ga( tracker_name + 'send', 'pageview' );
			}

			// Now set tracking events
			// 1. Form Page Change
			main_tab.on('tabsactivate', function(event, ui) {

			});
			// 2. On change
			container.on( 'blur change', '.ipt_uif_conditional', function() {
				var elm = $(this);
				// console.log(elm.prev().prev());

				// Do only if this is a valid element
				if ( $.inArray( elm.prev().prev().val(), [ 'mcq', 'freetype', 'pinfo' ] ) != -1 ) {
					var validationElm = $(this).find('.check_me'), validationState = false, validationValue = validationElm.val(),
					validationElmType = elm.prev().val();
					if ( validationElm.length ) {
						validationState = validationElm.validationEngine('validateSilent');
					}
					console.log(validationState, validationElm.val());
					// Now populate the ga send parameters
					var eventAction = 'completed';
					if ( validationState == true ) {
						eventAction = 'skipped';
					} else if (  validationState == false && ( validationValue == undefined || validationValue.length == 0 ) ) {
						// But do not say skip in case of some special elements
						// like sortable
					}
				}
				// ga( 'send', {
				// 	hitType: 'event',
				// 	eventCategory: 'Videos', // Event Category Should be Form Name
				// 	eventAction: 'play', // Event Action can be either "completed" | "skipped"
				// 	eventLabel: 'Fall Campaign' // Label is the question title
				// } );
			} );
		},
		applySaytRestoreTab: function() { // Done
			var container = $(this),
			form = container.find('form'),
			settings = container.data('fsqmsayt'),
			main_tab = container.find('.ipt_fsqm_main_tab');

			if ( settings != undefined && settings.admin_override == false && settings.auto_save == true ) {
				if ( settings.restore == true && form.sayt({'checksaveexists': true}) == true ) {
					// Restore the tab too
					var tab_pos = form.find('.ipt_fsqm_form_tab_pos').val();
					if ( main_tab.length && tab_pos != undefined ) {
						main_tab.data('ipt_fsqm_sayt_restore', true);
						main_tab.tabs({
							active: tab_pos
						});
						main_tab.data('ipt_fsqm_sayt_restore', false);
					}
				}
			}
		},
		applySayt: function() { // Done
			var container = $(this),
			form = container.find('form'),
			settings = container.data('fsqmsayt'),
			main_tab = container.find('.ipt_fsqm_main_tab');

			// Dont do anything if admin override
			if ( settings != undefined && settings.admin_override == false && settings.auto_save == true ) {
				form.sayt({
					'autosave': true,
					'autorecover': false,
					'days': 30,
					'exclude': ['.ipt_fsqm_sayt_exclude']
				});
				if ( settings.restore == true && form.sayt({'checksaveexists': true}) == true ) {
					form.sayt({'recover': true});

					if ( settings.show_restore ) {
						container.find('.ipt_fsqm_form_message_restore').show().addClass('iptFadeInLeft iptAnimated');
					}
				}
				container.find('.ipt_fsqm_form_message_restore .ipt_fsqm_sayt_reset').on('click', function(e) {
					e.preventDefault();
					form.sayt({'erase': true});
					form.find('.ipt_fsqm_form_tab_pos').val('0');
					// location.reload(true);
					form.trigger('reset');
					form.find('.ipt_uif_slider, .ipt_uif_slider_range_max').val('0').trigger('fsqm.slider');
					form.trigger('fsqm.mathematicalReEvaluate').trigger('fsqm.check_likedislike').trigger('fsqm.check_smiley');
					form.find('.ipt_uif_jsignature_reset').trigger('click');
					form.find('.ipt_uif_conditional').trigger('fsqm.conditional');
					container.find('.ipt_fsqm_form_message_restore').slideUp('fast');
					if ( main_tab.length ) {
						// Reinit the TAB
						main_tab.data('fsqm_reset_override', true);
						main_tab.tabs({
							active: 0
						});
						main_tab.data('fsqm_reset_override', false);
					}
				});
				container.find('.ipt_fsqm_form_message_restore .ipt_fsqm_form_message_close').on('click', function(e) {
					e.preventDefault();
					container.find('.ipt_fsqm_form_message_restore').slideUp('fast');
				});
			}

		},
		applyFSQM : function() { // Done
			//methods.applyValidation.apply(this);
			methods.applyRefreshStartupTimer.apply(this);
			methods.applyTimerEvent.apply(this);
			methods.applyTabEvents.apply(this);
			methods.applyFormEvents.apply(this);
			methods.applyNonceEvents.apply(this);
			methods.applyCoupons.apply(this);
			methods.applyStopwatch.apply(this);
		},
		endStopwatch: function() { // Done
			var container = $(this),
			stopwatchTimer = container.find('.ipt_fsqm_form_stopwatch'),
			stopwatchTimerVal = container.find('.ipt_fsqm_form_stopwatch_val');
			if ( stopwatchTimer.length ) {
				stopwatchTimer.TimeCircles().destroy();
			}
		},
		restoreStopwatch: function() { // Done
			var container = $(this),
			stopwatchTimer = container.find('.ipt_fsqm_form_stopwatch'),
			stopwatchTimerVal = container.find('.ipt_fsqm_form_stopwatch_val');
			if ( stopwatchTimer.length ) {
				stopwatchTimer.attr('data-timer', stopwatchTimerVal.val());
			}
		},
		applyStopwatch: function() { // Done
			var container = $(this),
			stopwatchTimer = container.find('.ipt_fsqm_form_stopwatch'),
			stopwatchTimerVal = container.find('.ipt_fsqm_form_stopwatch_val');
			if ( stopwatchTimer.length ) {
				stopwatchTimer.TimeCircles().addListener(function( unit, value, total ) {
					stopwatchTimerVal.val(total);
				}, 'all');
			}
		},
		applyCoupons: function() { // Done
			var couponButton = $(this).find('.ipt_uif_coupon_button');
			if ( couponButton.length == 0 ) {
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

				if ( couponContainer.find('.ipt_uif_coupon_text').val() == '' ) {
					that.prop('disabled', false);
					that.find('.ui-button-text').html(data.normal);
					couponMath.data('formula', couponMath.attr('data-formula'));
					couponContainer.find('.ipt_uif_coupon_final').trigger('fsqm.mathematicalReEvaluate');
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
					if ( response.success == true ) {
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
				});
			});

			$(this).find('.ipt_uif_coupon_text').on('keyup keypress', function(e) {
				var keyCode = e.keyCode || e.which;
				if ( keyCode === 13 ) {
					e.preventDefault();
					couponButton.trigger('click');
					return false;
				}
			});
			$(this).find('.ipt_uif_coupon_final').on('change', function() {
				couponButton.trigger('click');
			});
		},
		applyRefreshStartupTimer: function() { // Done
			// Also check for startup timer and refresh page when done
			if ( $(this).find('.ipt_fsqm_form_startup_timer').length ) {
				var startUpTimer = $(this).find('.ipt_fsqm_form_startup_timer').TimeCircles();
				startUpTimer.addListener(function(unit, value, total) {
					if ( total <= 0 ) {
						window.location.reload(true);
					}
				});
			}
		},
		applyNonceEvents: function() { // Done
			var container = $(this);
			// Do nothing if no form_id
			if ( ! container.find('input[name="form_id"]').length ) {
				return;
			}

			var form_id = container.find('input[name="form_id"]').val(),
			dataIDField = container.find('input[name="data_id"]'),
			data_id = dataIDField.length ? dataIDField.val() : null,
			nonceSaveField = container.find('input[name="ipt_fsqm_form_data_save"]'),
			nonceUpdateField = container.find('input[name="ipt_fsqm_user_edit_nonce"]'),
			userEditField = container.find('input[name="user_edit"]'),
			ajaxData = {
				form_id: form_id,
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
			var nonceInterval = setInterval(refreshNonce, 3600000);
			container.data('iptFSQMNonceInterval', nonceInterval);
		},
		applyFormEvents : function() { // Done
			var container = $(this);

			container.find('form.ipt_uif_validate_form').on('submit', function(e) {
				e.preventDefault();
				var _self_form = this;

				//Prevent submission if not from last tab
				var main_tab = container.find('.ipt_fsqm_main_tab');
				if( main_tab.length ) {
					var tabIndices = main_tab.find('> ul.ui-tabs-nav > li'),
					selected_tab = tabIndices.index(tabIndices.filter('[tabindex="0"]'));
					if ( selected_tab != tabIndices.length -1 && (  container.data('timerTabFormSync').timerEnabled != true || container.data('timerTabFormSync').forceSubmit != true ) ) {
						// There is still a possibility that it is the last tab, if rest of the tabs are just hidden
						var lastTabIndex = tabIndices.length - 1,
						totalIndices = tabIndices.length;
						while ( tabIndices.eq(lastTabIndex).hasClass('iptUIFCHidden') ) {
							lastTabIndex--;
							if ( lastTabIndex < 0 ) {
								lastTabIndex = totalIndices - 1;
								break;
							}
						}
						if ( selected_tab != lastTabIndex ) {
							//Change the tab
							main_tab.tabs('option', 'active', selected_tab + 1);
							return;
						}
					}
				}

				//Make sure there is no collapsed required element
				methods.openRequiredCollapsedElements.apply(this, [container]);

				// Prevent submission if not validates
				// but ignore validation anyway if timer says so
				if ( container.data('timerTabFormSync').timerEnabled != true || container.data('timerTabFormSync').forceSubmit != true ) {
					if( $(this).validationEngine('validate') === false ) {
						return;
					}
					// Check to see any active upload + required upload
					var pass_upload = methods.checkUploadRequests.apply( this, [$(_self_form)] );
					if ( pass_upload === false ) {
						return;
					}
				}



				//Do the ajax submission
				//Get all the necessary variables
				var process = container.find('.ipt_fsqm_form_message_process'),
				success = container.find('.ipt_fsqm_form_message_success'),
				http_error = container.find('.ipt_fsqm_form_message_error'),
				form_wrap = container.find('.ipt_fsqm_main_form');

				//Hide the form_wrap
				form_wrap.hide();
				container.find('.ipt_fsqm_form_message_restore').hide();
				success.hide();
				http_error.hide();

				// Stop any videos
				form_wrap.iptPluginUIFFront('refreshiFrames');

				//Show the process
				process.show();
				var process_ajax = process.find('.ipt_uif_ajax_loader_inline').css('width', 'auto'),
				init_width = process.width(),
				process_width = process_ajax.width() + 50,
				process_height = process_ajax.height();

				process_ajax.css({width: init_width, height: process_height, opacity: 0}).animate({width: process_width, opacity: 1}, 'normal', function() {
					//Post the data
					var data = {
						action: $(_self_form).find('[name="action"]').val(),
						ipt_ps_post: $(_self_form).serialize(),
						ipt_ps_send_as_str: true,
						ipt_ps_look_into: 'ipt_ps_post'
					};
					container.data('fsqm_submitting', true);
					methods.endStopwatch.apply(container.get(0));
					$.post(iptFSQM.ajaxurl, data, function(response) {
						if(response == null || response == 0) {
							http_error.find('.textStatus').html('Null Data');
							http_error.find('.errorThrown').html('Possible Server Error');
							http_error.slideDown('fast');
							//Show the form
							form_wrap.show();
							//Scroll to the http_error
							methods.scrollToPosition(http_error.offset().top - 10);
							return;
						}

						if(response.success == true) {
							success.find('.ui-widget-content').html(response.msg);
							success.slideDown('fast', function() {
								//Scroll to success
								methods.scrollToPosition(success.offset().top - 10);

								//Redirect if necessary
								if(response.components.redirect == true) {
									if ( success.find('.ipt_fsqm_redirection_countdown').length ) {

										var finalTime = response.components.redirect_delay / 1000,
										rcountUp = new CountUp( success.find('.ipt_fsqm_redirection_countdown').get(0), finalTime, 0, 0, finalTime, {
											useEasing : false,
											useGrouping : true,
											separator : ',',
											decimal : '.',
											prefix : '',
											suffix : ''
										} );
										rcountUp.start();
									}
									setTimeout(function() {
										if ( window.self === window.top || ! response.components.redirect_top ) {
											window.location.href = response.components.redirect_url;
										} else {
											window.top.location.href = response.components.redirect_url;
										}
									}, response.components.redirect_delay);
								}
							});
							clearInterval( container.data('iptFSQMNonceInterval') );

							if ( $(_self_form).sayt({'checksaveexists': true}) == true ) {
								$(_self_form).sayt({'erase': true});
							}
						} else {
							form_wrap.show();
							if(undefined !== response.errors && typeof(response.errors) == 'object') {
								var show_alert = false;
								var errors = response.errors;
								for(var i = 0; i < errors.length; i++) {
									var msgs = errors[i]['msgs'].join('<br />');
									if(errors[i]['id'] != '') {
										var error_to = $('#' + errors[i]['id']);
										if(error_to.length) {
											show_alert = true;
											error_to.validationEngine('showPrompt', msgs, 'red', 'topLeft');
										} else {
											form_wrap.validationEngine('showPrompt', msgs, 'red', 'topLeft');
										}
									} else {
										form_wrap.validationEngine('showPrompt', msgs, 'red', 'topLeft');
									}
								}
								if ( show_alert ) {
									alert( iptFSQM.l10n.validation_on_submit );
								}
							}
							methods.scrollToPosition(form_wrap.offset().top - 10);
						}
					}, 'json').fail(function(jqXHR, textStatus, errorThrown) {
						//Show the form
						form_wrap.show();
						//Show the http_error
						http_error.find('.textStatus').html(textStatus);
						http_error.find('.errorThrown').html(errorThrown);
						http_error.show();
						//Scroll to the http_error
						methods.scrollToPosition(http_error.offset().top - 10);
					}).always(function() {
						//Hide the process
						process.hide();
					});
				});
				//Scroll to the process
				methods.scrollToPosition(process.offset().top - 10, 0);
			});
		},
		applyTabEvents : function() { // Done
			var main_tab = $(this).find('.ipt_fsqm_main_tab');
			var form = $(this).find('form.ipt_uif_validate_form');
			var main_pb = $(this).find('.ipt_fsqm_main_pb');

			// Set the jump to container buttons
			$(this).on('click', '.ipt_fsqm_jump_button', function(e) {
				e.preventDefault();
				if ( ! main_tab.length ) {
					return false;
				}

				// Set the data
				main_tab.data('ipt_fsqm_jump_action', true);
				main_tab.tabs('option', 'active', $(this).data('pos') - 1);
				main_tab.data('ipt_fsqm_jump_action', false);
			});

			if ( ! main_tab.length ) {
				// Init the just the reset button
				var reset_button = $(this).find('.ipt_fsqm_form_button_container .ipt_fsqm_form_button_reset');
				var for_reset_m_c = $(this);
				if ( reset_button.length ) {
					reset_button.on('click', function(e) {
						e.preventDefault();
						var input = confirm( iptFSQM.l10n.reset_confirm );
						if ( input ) {
							// Reset the form
							if ( form.sayt({'checksaveexists': true}) == true ) {
								form.sayt({'erase': true});
							}
							form.trigger('reset').trigger('fsqm.mathematicalReEvaluate').trigger('fsqm.check_likedislike').trigger('fsqm.check_smiley');
							form.find('.ipt_uif_conditional').trigger('fsqm.conditional');
							// Hide the sayt
							for_reset_m_c.find('.ipt_fsqm_form_message_restore').hide();
							// Scroll to top
							methods.scrollToPosition( for_reset_m_c.offset().top - 10, 200 );
						}
					});
				}
				return;
			}

			var tab_settings = main_tab.data('settings'),
			container = this,
			next_button = $(this).find('.ipt_fsqm_form_button_next'),
			previous_button = $(this).find('.ipt_fsqm_form_button_prev');

			//Hide the Uls for progressbar
			if(tab_settings.type == 2) {
				main_tab.find('> ul.ui-tabs-nav').hide();
			}

			// Hide the previous button if needed
			// https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/6
			if ( tab_settings['block-previous'] == true ) {
				previous_button.hide();
			}

			//Do the common stuff
			//Get all the li for indexing
			var tabIndices = main_tab.find('> ul.ui-tabs-nav > li');
			//Init the buttons
			methods.initButtonsForTabs.apply(container, [tabIndices, main_tab, tab_settings]);
			main_tab.on('tabsbeforeactivate', function(event, ui) {
				//Get the current tab index
				var indexOld = tabIndices.index(ui.oldTab),
				indexNew = tabIndices.index(ui.newTab);

				// Nothing really matters if it is a sayt restore
				if ( main_tab.data('ipt_fsqm_sayt_restore') == true ) {
					// Possibility of skipping tabs
					if ( methods.skipTabIfNecessary( ui, indexNew, indexOld, tabIndices, main_tab ) ) {
						main_tab.data('ipt_fsqm_sayt_restore', false);
						return false;
					}
					main_tab.data('ipt_fsqm_sayt_restore', false);
					return true;
				}

				// Nothing except timer matters for fsqm jump
				if ( main_tab.data('ipt_fsqm_jump_action') == true && $(container).data('timerTabFormSync').timerEnabled != true ) {
					// Possibility of skipping tabs
					if ( methods.skipTabIfNecessary( ui, indexNew, indexOld, tabIndices, main_tab ) ) {
						return false;
					}
					return true;
				}

				// Always block if moving away from multiple forward
				// Rather trigger a click on the next button
				if(indexNew > indexOld && Math.abs(indexOld - indexNew) > 1) {
					// But only if it hasn't skipped the hidden tabs
					// https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/51
					var skipCheck = false;
					for ( var i = indexOld + 1; i < indexNew; i++ ) {
						if ( tabIndices.eq(i).is(':visible') ) {
							skipCheck = true;
							break;
						}
					}
					if ( skipCheck == true ) {
						if(!next_button.button('option', 'disabled')) {
							next_button.trigger('click');
						}
						return false;
					}
				}

				// Check for moving backward
				if(indexNew < indexOld) {
					// If it's a reset override
					if ( main_tab.data('fsqm_reset_override') === true ) {
						main_tab.data('fsqm_reset_override', false);
						return true;
					}
					//If settings permit
					//https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/6
					if ( tab_settings['block-previous'] == true ) {
						return false;
					}
					// If can previous without validation
					if(tab_settings['can-previous'] == true) {
						// Also check for any possible skips
						if ( methods.skipTabIfNecessary( ui, indexNew, indexOld, tabIndices, main_tab ) ) {
							return false;
						}

						methods.scrollToTab(main_tab, tab_settings);
						return true;
					}
				}

				// Now just move if a timer event is triggered
				if ( $(container).data('timerTabFormSync').forceProgress == true && $(container).data('timerTabFormSync').timerEnabled == true ) {
					// There is a possibility that the new tab is conditionally hidden
					// https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/51
					if ( methods.skipTabIfNecessary( ui, indexNew, indexOld, tabIndices, main_tab ) ) {
						return false;
					} else {
						return true;
					}
					$(container).data('timerTabFormSync').forceProgress = false;
				}

				//Make sure there is no collapsed required element
				methods.openRequiredCollapsedElements.apply(this, [ui.oldPanel]);

				//Else validate the current panel
				var check_me = ui.oldPanel.find('.check_me');
				for(var item = 0; item < check_me.length; item++) {
					var jItem = check_me.eq(item);
					if(true == jItem.validationEngine('validate')) {
						//Scroll to its position
						var scrollTo = jItem.offset().top - 80;
						methods.scrollToPosition(scrollTo);
						return false;
					}
				}

				// Now check any upload requests
				// Check to see any active upload + required upload
				var pass_upload = methods.checkUploadRequests.apply( this, [ui.oldPanel] );
				if ( pass_upload === false ) {
					return false;
				}

				// There is a possibility that the new tab is conditionally hidden
				// https://iptlabz.com/ipanelthemes/wp-fsqm-pro/issues/51
				if ( methods.skipTabIfNecessary( ui, indexNew, indexOld, tabIndices, main_tab ) ) {
					return false;
				}

				methods.scrollToTab(main_tab, tab_settings);
				return true;
			});
			main_tab.on('tabsactivate', function(event, ui) {
				var indexNew = tabIndices.index(ui.newTab);
				if(tab_settings.type == 2 && tab_settings['show-progress-bar'] === true) {
					var percentage = ( indexNew / tabIndices.length ) * 100; // Math.round(10000 * indexNew / tabIndices.length) / 100;
					percentage = +percentage.toFixed( tab_settings['decimal-point'] );
					main_pb.progressbar('option', 'value', percentage);
				}
				form.find('.ipt_fsqm_form_tab_pos').val(indexNew).trigger('change');

				methods.refreshButtonsForTabs.apply(container, [tabIndices, ui.newTab]);
			});
			main_tab.on('iptUIFCHide iptUIFCShow', '[role="tab"]', function() {
				var currentTab = tabIndices.filter('[aria-selected="true"]');
				methods.refreshButtonsForTabs.apply(container, [tabIndices, currentTab]);
			});
		},

		applyTimerEvent: function() { // Done
			$(this).data( 'timerTabFormSync', {
				timerEnabled: false,
				forceProgress: false,
				forceSubmit: false
			} );
			if ( $(this).find('.ipt_fsqm_timer_data').val() == null || $(this).find('.ipt_fsqm_timer_data').val() == '' ) {
				return;
			}
			var that = $(this),
			form_id = that.find('input[name="form_id"]').val(),
			timerVar = $.parseJSON( that.find('.ipt_fsqm_timer_data').val() ),
			timerOuterDIV = that.find('.ipt_fsqm_timer'),
			timerDIV = timerOuterDIV.find('> .ipt_fsqm_timer_inner'),
			button_container = that.find('.ipt_fsqm_form_button_container'),
			prev_button = button_container.find('.ipt_fsqm_form_button_prev'),
			next_button = button_container.find('.ipt_fsqm_form_button_next'),
			submit_button = button_container.find('.ipt_fsqm_form_button_submit'),
			destroyTimer = function() {
				timerDIV.hide().parent().hide().next('.ipt_fsqm_timer_spacer').hide();
				that.data('timerTabFormSync').timerEnabled = false;
				that.data('timerTabFormSync').forceProgress = false;
				that.data('timerTabFormSync').forceSubmit = false;
			},
			reInitTimer = function() {
				timerDIV.show().parent().show().next('.ipt_fsqm_timer_spacer').show();
				that.data('timerTabFormSync').timerEnabled = true;
			},
			progressTimerPage = function() {
				if ( that.data('fsqm_submitting') == true ) {
					destroyTimer();
					return false;
				}
				if ( next_button.button('option', 'disabled') || timerVar.type == 'overall' ) {
					// Submit
					that.data('timerTabFormSync').forceProgress = false;
					that.data('timerTabFormSync').forceSubmit = true;
					var forceSubmit = false;
					if ( submit_button.button( 'option', 'disabled' ) ) {
						submit_button.button( 'option', 'disabled', false );
						forceSubmit = true;
					}
					submit_button.button( 'option', 'disabled', false ).trigger('click');
					if ( forceSubmit ) {
						submit_button.button( 'option', 'disabled', true );
					}
					destroyTimer();
				} else {
					// Progress
					that.data('timerTabFormSync').forceProgress = true;
					that.data('timerTabFormSync').forceSubmit = false;
					next_button.trigger('click');
				}
			};
			if ( null == timerVar || ! timerVar ) {
				return;
			}
			that.data('timerTabFormSync').timerEnabled = true;
			that.data('timerTabFormSync').timerVar = timerVar;
			if ( timerVar.type == 'overall' ) {
				if ( timerVar.time == 0 || timerVar.time == '' || isNaN( timerVar.time ) ) {
					destroyTimer();
				} else {
					timerDIV.data('timer', timerVar.time);
					timerDIV.TimeCircles({
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
						if ( total <= 0 ) {
							progressTimerPage();
						}
					});
				}
			} else if ( timerVar.type == 'page_specific' ) {
				timerDIV.TimeCircles()
				var main_tab = that.find('.ipt_fsqm_main_tab'),
				totalTime = 0;
				for ( var i in timerVar.time ) {
					var pageTime = parseFloat( timerVar.time[i] );
					if ( isNaN(  pageTime) ) {
						pageTime = 0;
					}
					$('#ipt_fsqm_form_' + form_id + '_tab_' + i).data('ipt_fsqm_timer', pageTime);
					totalTime += pageTime;
				}
				if ( ! main_tab.length ) {
					// Just use the totalTime to submit the form
					if ( totalTime == 0 || totalTime == '' || isNaN( totalTime ) ) {
						destroyTimer();
					} else {
						timerDIV.TimeCircles().destroy();
						timerDIV.data('timer', totalTime);
						timerDIV.TimeCircles({
							time: {
								Days: {show: false}
							},
							total_duration: 'Auto',
							count_past_zero: false
						}).addListener(function(unit, value, total) {
							if ( total <= 0 ) {
								progressTimerPage();
							}
						});
					}
				} else {
					// Modify the tab settings beforehand
					var tab_settings = main_tab.data('settings');
					tab_settings['block-previous'] = true;
					main_tab.data('settings', tab_settings);
					var applyActiveTabTimer = function() {
						var activeTab = main_tab.find('.ui-tabs-panel').eq( main_tab.tabs( 'option', 'active' ) ),
						tabTimer = parseFloat( activeTab.data('ipt_fsqm_timer') );
						timerDIV.TimeCircles().destroy();
						if ( tabTimer == 0 || isNaN( tabTimer ) ) {
							destroyTimer();
						} else {
							reInitTimer();
							timerDIV.data('timer', tabTimer);
							timerDIV.TimeCircles({
								time: {
									Days: {show: false}
								},
								total_duration: 'Auto',
								count_past_zero: false
							}).addListener(function(unit, value, total) {
								if ( total <= 0 ) {
									progressTimerPage();
								}
							});
						}
					};

					applyActiveTabTimer();

					main_tab.on('tabsactivate', applyActiveTabTimer);
				}
			} else {
				destroyTimer();
			}

			// Attach the scroll event
			if ( timerVar.type == 'overall' || timerVar.type == 'page_specific' ) {
				var affixTimerScroll = function() {
					var windowTop = $(window).scrollTop(),
					windowBottom = windowTop + $(window).height(),
					containerOffset = that.offset(),
					containerTop = containerOffset.top + 10,
					containerBottom = containerTop + that.outerHeight() + 90;

					if ( ( windowBottom >= containerTop ) && ( containerBottom >= windowBottom ) ) {
						if ( ! timerOuterDIV.hasClass('fixed') ) {
							timerOuterDIV.addClass('fixed');
							timerDIV.TimeCircles().rebuild();
						}
					} else {
						if ( timerOuterDIV.hasClass('fixed') ) {
							timerOuterDIV.removeClass('fixed');
							timerDIV.TimeCircles().rebuild();
						}
					}
				};

				$(document).on( 'scroll', $.debounce( 250, affixTimerScroll ) );

				affixTimerScroll();

				$(window).on('resize iptUIFCShow iptUIFCHide tabsactivate', $.debounce(250, function() {
					affixTimerScroll();
					timerDIV.TimeCircles().rebuild();
				}));
			}
		},

		skipTabIfNecessary: function( ui, indexNew, indexOld, tabIndices, main_tab ) { // Done
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

				var newTab = tabIndices.index( visibleTab );

				if ( newTab != -1 ) {
					main_tab.tabs('option', 'active', newTab);
				}
				return true;
			}
			return false;
		},

		checkUploadRequests : function( activeContainer ) { // Done
			var passed_validation = true;
			activeContainer.find( '.ipt_uif_uploader' ).each( function() {
				if ( ! $(this).is(':visible') ) {
					return true;
				}
				var widget = $(this),
				activeUpload = widget.data( 'activeUpload' ),
				totalUpload = widget.data( 'totalUpload' ),
				uploadSettings = widget.data( 'settings' );

				// Check for active uploads
				if ( activeUpload > 0 ) {
					widget.validationEngine('showPrompt', iptFSQM.l10n.uploader_active_upload, 'red', 'topLeft');
					passed_validation = false;
				}

				// Check for required uploads
				if ( uploadSettings.required === true && totalUpload < 1 ) {
					widget.validationEngine('showPrompt', iptFSQM.l10n.uploader_required, 'red', 'topLeft');
					passed_validation = false;
				}

				// Check for min number of files
				var min_number_of_files = parseInt( uploadSettings.min_number_of_files, 10 );
				if ( isNaN( min_number_of_files ) || min_number_of_files < 0 ) {
					min_number_of_files = 0;
				}
				if ( min_number_of_files > 1 && totalUpload < min_number_of_files ) {
					widget.validationEngine('showPrompt', iptFSQM.l10n.uploader_required_number + ' ' + min_number_of_files, 'red', 'topLeft');
					passed_validation = false;
				}

				if ( passed_validation === false ) {
					// Scroll to required position
					var scroll_to = widget.offset().top - 10;
					methods.scrollToPosition( scroll_to );
					return false;
				}
			} );

			return passed_validation;
		},
		openRequiredCollapsedElements : function(container) { // Done
			//Find all collapsible elements and if it has a required anything,
			//then open it
			container.find('.ipt_uif_collapsible').each(function() {
				var openIt = false;
				$(this).find('.check_me').each(function() {
					if($(this).attr('class').match(/required/)) {
						openIt = true;
						return false;
					}
				});
				if(openIt && !$(this).hasClass('ipt_uif_collapsible_open')) {
					$(this).find('>.ipt_uif_container_head > h3 > a').trigger('click');
				}
			});
		},
		initButtonsForTabs : function(tabIndices, main_tab, tab_settings) { // Done
			var button_container = $(this).find('.ipt_fsqm_form_button_container'),
			prev_button = button_container.find('.ipt_fsqm_form_button_prev'),
			next_button = button_container.find('.ipt_fsqm_form_button_next'),
			submit_button = button_container.find('.ipt_fsqm_form_button_submit'),
			reset_button = button_container.find('.ipt_fsqm_form_button_reset'),
			form = $(this).find('form'),
			mother_container = $(this),
			terms_wrap = button_container.prev('.ipt_fsqm_terms_wrap');
			if(tabIndices.length == 1) { //Remove if unnecessary
				prev_button.remove();
				next_button.remove();
				submit_button.button('enable');
			} else { //Init them
				prev_button.button('disable');
				submit_button.button('disable');
				next_button.button('enable');
				terms_wrap.hide();

				prev_button.on('click', function(e) { // Done
					e.preventDefault();
					var newTab = tabIndices.index(tabIndices.filter('[aria-selected="true"]').prev('li'));
					if(newTab != -1) {
						main_tab.tabs('option', 'active', newTab);
					}
				});
				next_button.on('click', function(e) {
					e.preventDefault();
					var newTab = tabIndices.index(tabIndices.filter('[aria-selected="true"]').next('li'));
					if(newTab != -1) {
						main_tab.tabs('option', 'active', newTab);
					}
				});
			}
			if ( reset_button.length ) {
				reset_button.on('click', function(e) {
					e.preventDefault();
					var input = confirm( iptFSQM.l10n.reset_confirm );
					if ( input ) {
						// Reset the form
						if ( form.sayt({'checksaveexists': true}) == true ) {
							form.sayt({'erase': true});
						}
						form.trigger('reset');
						form.find('.ipt_uif_slider, .ipt_uif_slider_range_max').val('0').trigger('fsqm.slider');
						form.trigger('fsqm.mathematicalReEvaluate').trigger('fsqm.check_likedislike').trigger('fsqm.check_smiley');
						form.find('.ipt_uif_conditional').trigger('fsqm.conditional');
						form.find('.ipt_uif_jsignature_reset').trigger('click');
						// Hide the sayt
						mother_container.find('.ipt_fsqm_form_message_restore').hide();
						// Reinit the TAB
						main_tab.data('fsqm_reset_override', true);
						main_tab.tabs({
							active: 0
						});
						main_tab.data('fsqm_reset_override', false);
					}
				});
			}
			methods.refreshButtonsForTabs.apply(this, [tabIndices, tabIndices.filter('[aria-selected="true"]')]);
		},
		scrollToTab : function(main_tab, tab_settings) { // Done
			if ( tab_settings.scroll == false ) {
				return;
			}
			var scrollTo = main_tab.offset().top - 10 + tab_settings.scroll_offset;
			if(tab_settings.type == 2 && tab_settings['show-progress-bar'] == true) {
				scrollTo = main_tab.prev('.ipt_uif_progress_bar').offset().top - 10 + tab_settings.scroll_offset;
			}
			methods.scrollToPosition(scrollTo);
		},
		scrollToPosition : function(scrollTo, duration) { // Done
			if(duration == undefined) {
				duration = 200;
			}
			var htmlTop = parseFloat($('html').css('margin-top'));
			if(isNaN(htmlTop)) {
				htmlTop = 0;
			}
			htmlTop += parseFloat($('html').css('padding-top'));
			if(!isNaN(htmlTop) && htmlTop != 0) {
				scrollTo -= htmlTop;
			}
			if(duration != 0) {
				$('html, body').animate({scrollTop : scrollTo}, duration);
			} else {
				$('html, body').scrollTop(scrollTo);
			}
		},
		refreshButtonsForTabs : function(tabIndices, currentTab) { // Done
			var button_container = $(this).find('.ipt_fsqm_form_button_container'),
			prev_button = button_container.find('.ipt_fsqm_form_button_prev'),
			next_button = button_container.find('.ipt_fsqm_form_button_next'),
			submit_button = button_container.find('.ipt_fsqm_form_button_submit'),
			terms_wrap = button_container.prev('.ipt_fsqm_terms_wrap');
			var currentIndex = tabIndices.index(currentTab),
			totalIndices = tabIndices.length;

			// get the index of first tab and last tab
			var firstTabIndex = 0, lastTabIndex = totalIndices - 1;
			while ( tabIndices.eq(firstTabIndex).hasClass('iptUIFCHidden') ) {
				firstTabIndex++;
				if ( firstTabIndex >= totalIndices ) {
					firstTabIndex = totalIndices - 1;
					break;
				}
			}
			while ( tabIndices.eq(lastTabIndex).hasClass('iptUIFCHidden') ) {
				lastTabIndex--;
				if ( lastTabIndex < 0 ) {
					lastTabIndex = totalIndices - 1;
					break;
				}
			}

			if(currentIndex == lastTabIndex) { //Check if last
				if ( currentIndex != firstTabIndex ) {
					prev_button.button('enable');
				}
				next_button.button('disable');
				submit_button.button('enable');
				terms_wrap.show();
			} else if (currentIndex == firstTabIndex) { //Check if first
				prev_button.button('disable');
				next_button.button('enable');
				submit_button.button('disable');
				terms_wrap.hide();
			} else { //Somewhere in between
				prev_button.button('enable');
				next_button.button('enable');
				submit_button.button('disable');
				terms_wrap.hide();
			}
		}
	};

	$.fn.iptFSQMForm = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMForm');
			return this;
		}
	};
})(jQuery);

jQuery(document).ready(function($) {
	$('.ipt_fsqm_form').iptFSQMForm();
});
