/**
 * WPQuark Plugin Framework
 *
 * This is a jQuery plugin which works on the plugin framework to populate the UI
 * Admin area
 *
 * @dependency jquery, ipt-plugin-uif-admin-js
 *
 * @author Swashata Ghosh <swashata@intechgrity.com>
 * @version 1.0.3
 * @license    GPLv3
 */
(function($) {
	"use strict";
	//Default Options
	var defaultOp = {
		callbackUI: 'iptPluginUIFAdmin',
		callbackOp: {
			applyUIOnly: true
		}
	};

	//Methods
	var methods = {
		init : function(options) {
			var op = $.extend(true, {}, defaultOp, options); //No use right now
			var _parent = this;

			return this.each(function() {
				methods.applyBuilder.apply(this);
			});
		},

		applyBuilder : function() {
			var self = this;
			//Store the keys
			var keys = JSON.parse($(self).find('input.ipt_uif_builder_keys').val());
			keys = $.extend(true, {}, JSON.parse($(self).find('input.ipt_uif_builder_default_keys').val()), keys);
			$(self).data('ipt_uif_builder_keys', keys);
			var replace = JSON.parse($(self).find('input.ipt_uif_builder_replace_string').val());
			$(self).data('ipt_uif_builder_replace', replace);

			//Init the variables
			var tabs = $(this).find('.ipt_uif_builder_layout'),
			adds = $(this).find('.ipt_uif_builder_add_layout');
			var tab, add;

			//Apply the layout tabs
			if ( tabs.length ) {
				tab = tabs[0];
				methods.builderTabs.apply(tab, [self]);
			}

			//Init the Add New Layout button
			if ( adds.length ) {
				add = adds[0];
				methods.builderAddTab.apply(add, [tab, self]);
			}

			//Init the new elements button
			$(this).find('.ipt_uif_droppable').each(function() {
				methods.builderDraggables.apply(this, [tab, self]);
			});

			//Init the toolbar
			$(this).find('.ipt_uif_builder_layout_settings_toolbar').each(function() {
				methods.builderToolbar.apply(this, [tab, self, add]);
			});

			//Init the settings
			var settings_box = $(this).find('.ipt_uif_builder_settings_box').eq(0);
			$(this).data('ipt_uif_builder_settings', settings_box);
			settings_box.data('ipt_uif_builder_settings_origin', undefined);


			// Apply the UIF on the settings box once
			$( this ).find( '#ipt-eform-builder-settings-wrap' ).iptPluginUIFAdmin();
			// Hide the top level tab
			$( '#ipt-eform-settings-tab-wrapper' ).hide();

			//Init the settings save
			var settings_save = settings_box.next().find('button');
			methods.builderSettingsSaveInit.apply(settings_save, [settings_box, self]);

			//Delegate all settings and expandables
			methods.builderElementSettingsEvent.apply(this);

			//Hide the wp_editor
			$(this).find('.ipt_uif_builder_wp_editor').css({position : 'absolute', 'left' : -9999})
			// Init the settings save
			.find('button.ipt_uif_button').on('click', function() {
				settings_save.trigger('click');
			});


			//Init the del dragger
			$(this).find('.ipt_uif_builder_deleter').each(function() {
				methods.builderDeleter.apply(this, [settings_box, self]);
			});

			// Init the copier
			$(this).on('click', '.ipt_uif_builder_copy_handle', function(e) {
				methods.builderDuplicate.apply(this, [self, settings_box]);
				// No need to stop propagation because none of the child element can have a builder within!!
			});

			// Stick the settings
			$( '#ipt-eform-builder-settings-wrap' ).theiaStickySidebar({
				additionalMarginTop: 48
			});

			// Collapse
			$( '#ipt-eform-builder-droppable-container-control' ).on( 'click', function( e ) {
				e.preventDefault();
				$( '#ipt_fsqm_form' ).toggleClass( 'eform-full-view' );
			} );
		},

		builderTabs : function(container) {
			var self = this;
			var tab = $(this).tabs();
			tab.find('> .ui-tabs-nav').sortable({
				placeholder : 'ipt_uif_builder_tabs_sortable_highlight',
				stop : function() {
					methods.builderTabRefresh.apply(self);
				},
				handle : '.ipt_uif_builder_tab_sort',
				tolerance : 'pointer',
				containment : 'parent',
				distance : 5
			});

			//Make existing drop_here 's droppable
			tab.find('> .ui-tabs-panel').each(function() {
				methods.builderDroppables.apply(this, [container]);
			});

			//Make the tab li 's droppable
			tab.find('> .ui-tabs-nav li').each(function() {
				methods.builderTabDroppable.apply(this, [container]);
			});

			//Store the tab counter
			var tab_counter = $(this).find('> .ui-tabs-nav > li').length;
			$(this).data('ipt_uif_builder_tab_counter', tab_counter);

			//Add empty class if necessary
			if ( tab_counter === 0 ) {
				$(this).addClass('ipt_uif_builder_empty');
			}
		},

		builderAddTab : function(tab, container) {
			$(this).on('click', function(e) {
				e.preventDefault();
				//alert(container);
				var key = $(container).data('ipt_uif_builder_replace').l_key;
				var tab_content = $(container).find('.ipt_uif_builder_tab_content').text();

				tab_content = $('<div></div>').html(tab_content).text();

				var tab_counter = $(tab).data('ipt_uif_builder_tab_counter');
				var re = new RegExp(methods.quote(key), 'g');
				tab_content = tab_content.replace(re, tab_counter);

				var id = $(tab).attr('id') + '_' + tab_counter,
				liID = $(tab).attr('id') + '_li_' + tab_counter,
				li = $(container).find('.ipt_uif_builder_tab_li').text();
				li = $('<div></div>').html(li).text();
				li = $(li);

				li.find('.tab_position').val(tab_counter);
				li.find('a').attr('href', '#' + id);
				li.attr( 'id', liID );
				$(tab).find('> .ui-tabs-nav').append(li);

				var new_tab = $('<div id="' + id + '">' + tab_content + '</div>');
				new_tab.attr( 'data-ipt-uif-builder-li', liID );
				$(tab).append(new_tab);
				tab_counter++;

				// new_tab.iptPluginUIFAdmin({
				// 	applyUIOnly: true
				// });
				methods.builderTabDroppable.apply(li, [container]);

				$(tab).data('ipt_uif_builder_tab_counter', tab_counter);

				$(tab).removeClass('ipt_uif_builder_empty');

				methods.builderTabRefresh.apply(tab);
				methods.builderDroppables.apply(new_tab, [container]);

				//Open the last tab
				$(tab).tabs('option', 'active', $(tab).find('> .ui-tabs-nav > li').length - 1);

			});
		},

		builderTabDroppable : function(container) {
			$(this).find('.ipt_uif_builder_tab_droppable').droppable({
				greedy : true,
				accept : '.ipt_uif_droppable_element',
				tolerance : 'pointer',
				activate : function(event, ui) {
					$(this).addClass('ipt_uif_builder_tab_droppable_highlight');
				},
				deactivate : function(event, ui) {
					$(this).removeClass('ipt_uif_builder_tab_droppable_highlight');
				},
				over : function(event, ui) {
					$(this).addClass('ipt_uif_builder_tab_droppable_over');
				},
				out : function(event, ui) {
					$(this).removeClass('ipt_uif_builder_tab_droppable_over');
				},
				drop : function(event, ui) {
					var new_droppable = $('#' + $(this).parent().parent().attr('aria-controls')).find('> .ipt_uif_builder_drop_here').get(0);
					var self = this;
					var tab = $(self).parent().parent().parent().parent();
					var move_to = $(self).parent().parent().parent().find('> li').index($(self).parent().parent());
					tab.tabs('option', 'active', move_to);
					var callback = function() {
						$(self).removeClass('ipt_uif_builder_tab_droppable_highlight');
						$(self).removeClass('ipt_uif_builder_tab_droppable_over');
					};
					methods.builderHandleDrop.apply(new_droppable, [event, ui, container, callback]);
				}
			});
		},

		builderTabRefresh : function() {
			$(this).tabs('refresh');

			$(this).find('> .ui-tabs-nav').sortable('refresh');
			$(this).find('> .ui-tabs-nav').sortable('refreshPositions');

			var tab_counter = $(this).find('> .ui-tabs-nav > li').length;
			//Add empty class if necessary
			if ( tab_counter === 0 ) {
				$(this).addClass('ipt_uif_builder_empty');
			}
		},

		builderToolbar : function(tab, container, add) {
			$(this).find('.ipt_uif_builder_layout_settings').on('click', function() {
				var active_tab = $(tab).tabs('option', 'active');
				var panelID = $(tab).find('.ui-tabs-nav li').eq(active_tab).attr('aria-controls');
				var settings_box = $(container).data('ipt_uif_builder_settings').get(0);
				var origin = $('#' + panelID).find('.ipt_uif_builder_tab_settings').get(0);
				//console.log(origin);
				methods.builderSettingsOpen.apply(this, [settings_box, container, origin]);
			});

			$(this).find('.ipt_uif_builder_layout_copy').on('click', function() {
				// Get the original tab in question (this to copy)
				var original_active_tab = $(tab).tabs('option', 'active'),
				originalPanelID = $(tab).find('.ui-tabs-nav li').eq(original_active_tab).attr('aria-controls');

				// First close the settings box
				var settings_box = $(container).data('ipt_uif_builder_settings').get(0);
				methods.builderSettingsClose.apply(this, [settings_box, container]);

				// Add a new tab
				$(add).trigger('click');

				// Get the last added tab
				var active_tab = $(tab).tabs('option', 'active'),
				panelID = $(tab).find('.ui-tabs-nav li').eq(active_tab).attr('aria-controls');

				var originalPanel = $('#' + originalPanelID),
				newPanel = $('#' + panelID),
				cloneDroppable = originalPanel.find('>.ipt_uif_builder_drop_here').clone();

				// Remove the existing droppable area but store the key first
				var existingLayout = newPanel.find('>.ipt_uif_builder_drop_here'),
				existingKey = existingLayout.data('containerKey');
				existingLayout.remove();

				// Remove any db elements inside the cloned element
				cloneDroppable.find('[data-dbmap="1"]').remove();

				// Add the new one
				cloneDroppable.appendTo( newPanel );

				// Now modify existing elements
				methods.builderDuplicateReplaceInnerKeys.apply( cloneDroppable, [container, existingKey] );

				// Finally refresh it
				methods.builderTabRefresh.apply(tab);
				methods.builderDroppables.apply(newPanel, [container]);
				// newPanel.iptPluginUIFAdmin({
				// 	applyUIOnly: true
				// });
				// cloneDroppable.iptPluginUIFAdmin({
				// 	applyUIOnly: true
				// });
				// console.log(newPanel.find('.ipt_uif_sda'));
			});

			$(this).find('.ipt_uif_builder_layout_del').on('click', function() {
				var title = $(this).data('title');
				var dialog_content = $('<div><div style="padding: 10px;"><p>'  + $(this).data('msg') + '</p></div></div>');
				dialog_content.dialog({
					autoOpen: true,
					buttons: {
						"Confirm": function() {
							var active_tab = $(tab).tabs('option', 'active');
							//Remove the li
							var panelID = $(tab).find('.ui-tabs-nav li').eq(active_tab).remove().attr('aria-controls');
							//Remove the Panel
							$(tab).find('#' + panelID).remove();

							methods.builderTabRefresh.apply(tab);
							$(this).dialog("close");
						},
						'Cancel' : function() {
							$(this).dialog("close");
						}
					},
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
			});
		},

		builderSettingsOpen : function(settings_box, container, origin) {
			methods.builderSettingsClose.apply(this, [settings_box, container]);
			container = $(container);
			origin = $(origin);

			//Double Click? Then Toggle
			if(!origin.length) {
				return;
			}

			//Store the parent
			var parent = origin.parent(),
			elementTitle = parent.find( '> .ipt_uif_droppable_element_wrap > .element_title_h3 > .element_name' ).html();
			if ( ! elementTitle ) {
				elementTitle = 'Page';
			}
			elementTitle += ' Settings';
			$(settings_box).data('ipt_uif_builder_settings_parent', parent);

			// Append the origin settings
			$(settings_box).find('.ipt_uif_builder_settings_box_container').prepend(origin);

			// Change the title
			$( settings_box ).closest( '.ipt_uif_builder_settings_box_parent' ).find( '> .settings-heading > .settings-heading-text' ).html( elementTitle );



			//Check wp_editor
			var wp_editor_textarea = $(settings_box).find('textarea.wp_editor').eq(0);
			if(wp_editor_textarea.length) {
				$( '#ipt-eform-settings-editor-li' ).show();
				var wp_editor_container = container.find('.ipt_uif_builder_wp_editor');
				var tmce_textarea = wp_editor_container.find('textarea').eq(0);
				var editor;

				// Init the tinyMCE API
				if ( 'undefined' != typeof( tinyMCE ) ) {
					editor = tinyMCE.get(tmce_textarea.attr('id'));
				}

				// Get the original content
				var content = wp_editor_textarea.val();

				// Show it
				wp_editor_container.css({position : 'static', 'left' : 'auto'});
				// console.log(tmce_textarea);

				// Set the content
				// Check to see which one is active right now
				// Visual or Text
				if ( editor && editor instanceof tinymce.Editor &&  ! tmce_textarea.is(':visible') ) {
					editor.setContent(switchEditors.wpautop(content));
					editor.save({ no_events: true });
				} else {
					if ( 'undefined' != typeof( switchEditors ) ) {
						tmce_textarea.val(switchEditors.pre_wpautop(content));
					} else {
						tmce_textarea.val( content );
					}
				}
			} else {
				$( '#ipt-eform-settings-editor-li' ).hide();
			}

			// See if origin parent is a droppable
			if(parent.hasClass('ipt_uif_droppable_element')) {
				parent.find('> .ipt_uif_droppable_element_wrap').addClass('white');
			}

			// Store the origin
			$(settings_box).data('ipt_uif_builder_settings_origin', origin);

			// Show it
			$( '#ipt-eform-settings-tab-wrapper' ).show().tabs( 'option', 'active', 0 );
			$(settings_box).parent().stop(true, true).css({height : 'auto'}).hide().fadeIn('fast', function() {
				//Init the scroll position
				var scroll_position = $( '#ipt-eform-settings-tab-wrapper' ).offset().top;

				// Scroll the body
				if($('#wpadminbar').length) {
					scroll_position -= ($('#wpadminbar').outerHeight() + 10);
				}
				$('html, body').animate({scrollTop : scroll_position});
			});

			// Apply UI if needed
			if ( ! origin.data( 'iptEformBuilderSettingsUI' ) ) {
				origin.data( 'iptEformBuilderSettingsUI', origin.iptPluginUIFAdmin( {
					applyUIOnly: true
				} ) );
			}

			//$(settings_box).next().show();
		},

		builderSettingsClose : function(settings_box, container) {

			//Get origin and parent
			var origin = $(settings_box).data('ipt_uif_builder_settings_origin');
			var parent = $(settings_box).data('ipt_uif_builder_settings_parent');

			//Check for double click on a single button
			if(origin === undefined || parent === undefined) {
				return;
			}

			//Init the container
			container = $(container);

			//Check wp_editor
			var wp_editor_textarea = $(settings_box).find('textarea.wp_editor').eq(0);
			if ( wp_editor_textarea.length ) {
				// Get the tmce textarea
				var tmce_textareaID = container.find('.ipt_uif_builder_wp_editor textarea').eq(0).attr('id');
				var wp_editor_container = container.find('.ipt_uif_builder_wp_editor');
				var tmce_textarea = wp_editor_container.find('textarea').eq(0);
				var content, editor;
				if ( 'undefined' != typeof( tinyMCE ) ) {
					editor = tinyMCE.get(tmce_textareaID);
					// Get the content
					if( editor && editor instanceof tinymce.Editor && ! tmce_textarea.is(':visible') ) {
						content = switchEditors.pre_wpautop(editor.getContent());
					} else {
						content = switchEditors.pre_wpautop( $('#' + tmce_textareaID).val() );
					}
				} else {
					content = $('#' + tmce_textareaID).val();
				}

				//Update it
				wp_editor_textarea.val(content);

				//Hide the wp_editor
				container.find('.ipt_uif_builder_wp_editor').css({position : 'absolute', 'left' : -9999});
			}

			//See if origin parent is a droppable
			if(parent.hasClass('ipt_uif_droppable_element')) {
				parent.find('> .ipt_uif_droppable_element_wrap').removeClass('white');
			}

			// Check the grayed out class based on conditional logic
			var element_m_type = parent.find('input.ipt_uif_builder_helper.element_m_type').val(),
			element_key = parent.find('input.ipt_uif_builder_helper.element_key').val();

			// Is it a layout?
			if ( element_m_type == 'layout' ) {
				if ( $('#' + element_m_type + '_' + element_key + '_conditional_active').is(':checked') && !$('#' + element_m_type + '_' + element_key + '_conditional_status').is(':checked') ) {
					parent.addClass('grayed');
					$('#' + parent.data('iptUifBuilderLi')).addClass('grayed');
				} else {
					parent.removeClass('grayed');
					$('#' + parent.data('iptUifBuilderLi')).removeClass('grayed');
				}
			// Other elements
			} else {
				if ( $('#' + element_m_type + '_' + element_key + '_conditional_active').is(':checked') && !$('#' + element_m_type + '_' + element_key + '_conditional_status').is(':checked') ) {
					parent.find('> .ipt_uif_droppable_element_wrap').addClass('grayed');
				} else {
					parent.find('> .ipt_uif_droppable_element_wrap').removeClass('grayed');
				}
			}


			//Restore
			parent.append(origin);

			// Change the subtitle of the parent
			if ( parent.hasClass('ipt_uif_droppable_element_added') ) {
				var possible_title_id = parent.find('.element_m_type').val() + '_' + parent.find('.element_key').val() + '_title',
				possible_title = $('#' + possible_title_id).val();
				possible_title = methods.stripTags( possible_title );

				if ( possible_title && typeof( possible_title ) == 'string' ) {
					parent.find('> .ipt_uif_droppable_element_wrap > h3.element_title_h3 > span.element_title').text( ' : ' + possible_title.trim() );
					parent.find('> .ipt_uif_droppable_element_wrap > h3.element_title_h3').attr( 'title', possible_title.trim() );
				}
			}


			//Hide it
			$(settings_box).data('ipt_uif_builder_settings_origin', undefined);
			$(settings_box).data('ipt_uif_builder_settings_parent', undefined);
			$( '#ipt-eform-settings-tab-wrapper' ).hide();
			$(settings_box).parent().stop(true, true).hide();
			//$(settings_box).next().hide();
		},

		builderSettingsSaveInit : function(settings_box, container) {
			$(this).on('click', function() {
				methods.builderSettingsClose.apply(this, [settings_box, container]);
			});
		},

		builderElementSettingsEvent : function() {
			var container = this;
			$( container ).on( 'click', '.ipt_uif_droppable_element_wrap', function( e ) {
				// If it is coming from another droppable element
				if ( $( this ).find( '.ipt_uif_builder_drop_here_inner' ).length && $.contains( $( this ).find( '.ipt_uif_builder_drop_here_inner' ).get( 0 ), e.target ) ) {
					return;
				}
				if ( ! $( e.target ).hasClass( 'ipt_uif_builder_action_handle' ) && ! $( this ).closest( '#ipt-eform-builder-droppables-container' ).length ) {
					var origin = $( this ).closest( '.ipt_uif_droppable_element' ).find('> .ipt_uif_builder_settings').get(0),
					settings_box = $(container).data('ipt_uif_builder_settings').get(0);
					methods.builderSettingsOpen.apply(this, [settings_box, container, origin]);
				}
			} );
			// Delegate the settings
			$(container).on('click', '.ipt_uif_builder_settings_handle', function(e) {
				e.preventDefault();
				var origin = $(this).closest( '.ipt_uif_droppable_element' ).find('> .ipt_uif_builder_settings').get(0),
				settings_box = $(container).data('ipt_uif_builder_settings').get(0);
				methods.builderSettingsOpen.apply(this, [settings_box, container, origin]);
			});

			$(container).on('click', '.ipt_uif_builder_droppable_handle', function(e) {
				e.preventDefault();
				if($(this).hasClass('ipt_uif_builder_droppable_handle_open')) {
					$(this).removeClass('ipt_uif_builder_droppable_handle_open');
					$(this).siblings('.ipt_uif_builder_drop_here').slideUp('normal');
				} else {
					$(this).addClass('ipt_uif_builder_droppable_handle_open');
					$(this).siblings('.ipt_uif_builder_drop_here').slideDown('normal');
				}
			});
		},

		builderDeleter : function(settings_box, container) {
			var self = $(this);
			self.find('.ipt_uif_builder_deleter_wrap').stop(true, false).hide();
			var title = $(this).data('title');
			var dialog_content = $('<div><p>'  + $(this).data('msg') + '</p></div>');
			$(this).droppable({
				greedy : true,
				tolerance : 'pointer',
				accept : '.ipt_uif_builder_drop_here .ipt_uif_droppable_element',
				activate : function(event, ui) {
					self.find('.ipt_uif_builder_deleter_wrap').stop(true, true).hide().fadeIn( 'fast' );
					$( this ).removeClass('active');
				},
				deactivate : function(event, ui) {
					self.find('.ipt_uif_builder_deleter_wrap').stop(true, false).fadeOut( 'fast' );
				},
				over : function(event, ui) {
					$( this ).addClass('active');
					ui.helper.find('.ipt_uif_droppable_element_wrap').addClass('red');
				},
				out : function(event, ui) {
					$( this ).removeClass('active');
					ui.helper.find('.ipt_uif_droppable_element_wrap').removeClass('red');
				},
				drop : function(event, ui) {
					var drop_here = ui.draggable.parent();
					$( this ).removeClass('active');

					//First check for dbmap
					var item = ui.draggable;
					dialog_content.dialog({
						autoOpen: true,
						buttons: {
							"Confirm": function() {
								if ( item.data('dbmap') === 1 ) {
									//Restore
									var original = item.data('ipt_uif_builder_dbmap_original');
									original.removeClass('ipt_uif_droppable_element_disabled');
								}
								ui.draggable.remove();

								methods.builderSettingsClose.apply(this, [settings_box, container]);

								if(drop_here.find('.ipt_uif_droppable_element:not(.ui-sortable-placeholder):not(.ui-sortable-helper)').length < 1) {
									drop_here.addClass('ipt_uif_builder_drop_here_empty');
								}
								$(this).dialog("close");
							},
							'Cancel' : function() {
								$(this).dialog("close");
							}
						},
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

					self.find('.ipt_uif_builder_deleter_wrap').stop(true, false).fadeOut( 'fast' );
				}
			});
		},

		builderDuplicate: function(container, settings_box) {
			// Close the settings box
			methods.builderSettingsClose.apply(this, [settings_box, container]);

			// Get the mother element
			var elementToCopy = $(this).closest('.ipt_uif_droppable_element'),
			// Clone it
			duplicateDOM = elementToCopy.clone(),
			// Init the new key
			key = 0,
			// Init the inner droppable element
			innerDroppableDOM = null;

			// Do not do anything if it is a dbmap
			if ( elementToCopy.data('dbmap') ) {
				return;
			}

			// Patch the textarea
			var originalTextAreas = elementToCopy.find('textarea'),
			duplicateTextAreas = duplicateDOM.find('textarea');
			if ( originalTextAreas.length ) {
				for ( var t = 0; t < originalTextAreas.length; t++ ) {
					$(duplicateTextAreas[t]).val( $(originalTextAreas[t]).val() );
				}
			}

			// Possibility of dbmap elements inside it
			// Just remove them
			duplicateDOM.find('[data-dbmap="1"]').remove();

			// Update the DOM id, name and for attributes
			key = methods.builderDuplicateModifyElements( elementToCopy, duplicateDOM, container );

			// Hide it
			duplicateDOM.hide();

			// Append it
			elementToCopy.after( duplicateDOM );

			//Check for other droppables
			innerDroppableDOM = duplicateDOM.find('> .ipt_uif_droppable_element_wrap > .ipt_uif_builder_drop_here');
			if ( innerDroppableDOM.length ) {
				innerDroppableDOM.each(function() {
					// Replace inner keys
					methods.builderDuplicateReplaceInnerKeys.apply( this, [container, key] );
				});
				// Make it take new elements
				methods.builderDroppables.apply( duplicateDOM.get(0), [container] );
			}

			//Add any new Framework item
			// duplicateDOM.iptPluginUIFAdmin({
			// 	applyUIOnly: true
			// });

			//Show it
			duplicateDOM.slideDown('fast');
		},

		builderDuplicateModifyElements: function( originalDOM, duplicateDOM, container ) {
			var element_m_type = originalDOM.find('> input.ipt_uif_builder_helper.element_m_type').val(),
			// Get type
			element_type = originalDOM.find('> input.ipt_uif_builder_helper.element_type').val(),
			// Get key
			element_key = parseInt( originalDOM.find('> input.ipt_uif_builder_helper.element_key').val(), 10 ),
			// Prepare the name to replace
			name_replace = element_m_type + '\\[' + element_key + '\\]',
			// Prepare the id to replace
			id_replace = element_m_type + '_' + element_key + '_',
			//Get the data variables
			keys = $(container).data('ipt_uif_builder_keys'),
			// Init the new key
			key = 0;

			// Set the new key
			if(undefined !== keys[element_m_type]) {
				key = keys[element_m_type];
				keys[element_m_type]++;
			} else {
				keys[element_m_type] = key;
			}

			//Update the keys
			$(container).data('ipt_uif_builder_keys', keys);

			// Update the DOM id, name and for attributes
			duplicateDOM.find('>.ipt_uif_builder_settings').find('input, textarea, select, button, datalist, keygen, output, label').each(function() {
				var form_elem = $(this),
				name = form_elem.attr('name'),
				id = form_elem.attr('id'),
				label_for = form_elem.attr('for');
				if ( name ) {
					form_elem.attr('name', name.replace(new RegExp(name_replace, 'g'), element_m_type + '[' + key + ']'));
				}
				if ( id ) {
					form_elem.attr('id', id.replace(new RegExp(id_replace, 'g'), element_m_type + '_' + key + '_'));
				}
				if ( label_for ) {
					form_elem.attr('for', label_for.replace(new RegExp(id_replace, 'g'), element_m_type + '_' + key + '_'));
				}
			});

			// Update SDA data, if any
			duplicateDOM.find('script.ipt_uif_sda_data').each(function() {
				var originalSDAData = $(this).html(),
				modifiedSDAData = originalSDAData.replace( new RegExp(name_replace, 'g'), element_m_type + '[' + key + ']' ).replace( new RegExp(id_replace, 'g'), element_m_type + '_' + key + '_' );
				$(this).html(modifiedSDAData);
			});

			// Reset fontIconPicker (if any)
			duplicateDOM.find('.icons-selector').remove();

			// Set the new Key
			duplicateDOM.find('>input.ipt_uif_builder_helper.element_key').val(key);

			// Set the element info (M){K}
			var duplicateElementInfo = duplicateDOM.find('> .ipt_uif_droppable_element_wrap > h3 > .element_info');
			duplicateElementInfo.text( duplicateElementInfo.text().replace(element_key, key) );

			return key;
		},

		builderDuplicateReplaceInnerKeys: function(container, new_key) {
			// Update the key first
			$(this).data('containerKey', new_key);
			// First get the keys of this droppable container and stuff
			var droppable_key = new_key,
			droppable_m_type = $(this).data('replaceby'),
			new_helper_name = droppable_m_type + '[' + droppable_key + '][elements]';

			// Recursively check all ipt_uif_droppable_element
			$(this).find('>.ipt_uif_droppable_element').each( function() {
				var self = $(this),
				key = 0;

				// Update the DOM id, name and for attributes
				key = methods.builderDuplicateModifyElements( self, self, container );

				// Update new layout
				self.find('> input.ipt_uif_builder_helper.element_m_type').attr('name', new_helper_name + '[m_type][]' );
				self.find('> input.ipt_uif_builder_helper.element_type').attr('name', new_helper_name + '[type][]' );
				self.find('> input.ipt_uif_builder_helper.element_key').attr('name', new_helper_name + '[key][]' );

				// Now check if it again contains any inner droppable element
				var innerDroppableDOM = self.find('> .ipt_uif_droppable_element_wrap > .ipt_uif_builder_drop_here');
				if(innerDroppableDOM.length) {
					innerDroppableDOM.each(function() {
						methods.builderDuplicateReplaceInnerKeys.apply( this, [container, key] );
					});
				}
			} );
		},

		builderDraggables : function(tab, container) {
			//Make 'em droppable (err, sorry draggable to the droppables)
			var droppables = $(this).find('.ipt_uif_droppable_element');
			droppables.draggable({
				revert : 'invalid',
				revertDuration : 200,
				helper : 'clone',
				zIndex : 9999,
				appendTo : $(this),
				cancel : '.ipt_uif_droppable_element_disabled',
				handle : '.ipt_uif_builder_sort_handle',
				cursorAt : {left : 19, top : 17},
				delay : 100
			});

			// Emulate the same event when something is clicked
			$(this).on( 'click', '.ipt_uif_droppable_element', function(event) {
				if ( $(this).hasClass('ipt_uif_droppable_element_disabled') ) {
					return;
				}
				var helper = $(this).clone(),
				ui = $(this),
				// Get the active tab droppable
				activeTab = $(tab).tabs( 'option', 'active' ),
				activeTabAria = $(tab).find('>ul>li.ipt_uif_builder_layout_tabs').eq(activeTab).attr('aria-controls'),
				activeTabAriaDOM = $('#' + activeTabAria).find('> .ipt_uif_builder_drop_here');

				methods.builderHandleDrop.apply(activeTabAriaDOM.get(0), [null, {
					draggable: ui,
					helper: helper
				}, container]);
			} );

			// Bind the parent click function -> On click show elements under that category
			$( this ).on( 'click', '.ipt_uif_droppable_elements_parent', function() {
				var elm = $( this ),
				droppableWrap = elm.closest( '.ipt_uif_droppable' ),
				closeButton = droppableWrap.find( '.ipt_uif_droppable_back' );
				droppableWrap.find( '.ipt_uif_droppable_elements_parent' ).stop( true, true ).hide();
				elm.next( '.ipt_uif_droppable_elements_wrap' ).stop( true, true ).fadeIn( 'fast' );
				closeButton.show();
			} );

			// Bind the child go back button function
			$( this ).on( 'click', '.ipt_uif_droppable_back', function( e ) {
				e.preventDefault();
				var elm = $( this ),
				droppableWrap = elm.closest( '.ipt_uif_droppable' );
				droppableWrap.find( '.ipt_uif_droppable_elements_wrap' ).stop( true, true ).hide();
				droppableWrap.find( '.ipt_uif_droppable_elements_parent' ).stop( true, true ).hide().fadeIn('fast');
				elm.hide();
			} );
		},

		builderDroppables : function(container) {
			$(this).find('.ipt_uif_builder_drop_here').droppable({
				greedy : true,
				accept : '.ipt_uif_droppable_element',
				tolerance : 'pointer',
				activate : function(event, ui) {
					$(this).addClass('ipt_uif_highlight');
				},
				deactivate : function(event, ui) {
					$(this).removeClass('ipt_uif_highlight');
				},
				over : function(event, ui) {
					$(this).addClass('ipt_uif_droppable_hover');
					ui.helper.find('.ipt_uif_droppable_element_wrap').addClass('white');
				},
				out : function(event, ui) {
					$(this).removeClass('ipt_uif_droppable_hover');
					ui.helper.find('.ipt_uif_droppable_element_wrap').removeClass('white');
				},
				drop : function(event, ui) {
					methods.builderHandleDrop.apply(this, [event, ui, container]);
					return;
				}
			}).sortable({
				//accept : '.ipt_uif_droppable .ipt_uif_droppable_elements_wrap .ipt_uif_droppable_element',
				items : '> .ipt_uif_droppable_element',
				handle : '> div > a.ipt_uif_builder_sort_handle',
				helper : function(event, item) {
					var c = item.attr('class');
					var insider = item.find('> .ipt_uif_droppable_element_wrap');
					var helper = $('<div class="' + c + '"><div class="' + insider.attr('class') + '"></div></div>');
					helper.addClass('ui-sortable-helper');
					insider.find('> a.ipt_uif_builder_action_handle').each(function() {
						helper.find('> .ipt_uif_droppable_element_wrap').append($(this).clone());
					});
					helper.find('> .ipt_uif_droppable_element_wrap').append(insider.find('> h3').clone()).append('<div class="clear"></div>');
					return helper.appendTo($(this));
				},
				cancel : '.ipt_uif_droppable_element_cancel_sort',
				cursorAt : {left : 19, top : 17},
				stop : function(event, ui) {
					if(ui.item.hasClass('ipt_uif_droppable_element_move')) {
						var self = $(this);
						var append_to = ui.item.data('ipt_uif_droppable_move');
						ui.item.removeClass('ipt_uif_droppable_element_move');
						var parent = ui.item.parent();
						ui.item.slideUp('fast', function() {
							ui.item.appendTo(append_to).slideDown('fast', function() {
								if(parent.find('.ipt_uif_droppable_element:not(.ui-sortable-placeholder):not(.ui-sortable-helper)').length < 1) {
									parent.addClass('ipt_uif_builder_drop_here_empty');
								}
								append_to.sortable('refresh');
								self.sortable('refresh');
							});
						});
					}
				}
			});

			$(this).find('.ipt_uif_droppable_element').each(function() {
				//change the state of dbmap
				if ( $(this).data('dbmap') === 1 ) {
					//get the original container from draggable
					var identify_class = $(this).attr('class');
					var original = $(container).find('.ipt_uif_droppable .ipt_uif_droppable_element').filter('[class="' + identify_class + '"]').addClass('ipt_uif_droppable_element_disabled');
					$(this).data('ipt_uif_builder_dbmap_original', original);
				}

				//Add the added class
				$(this).addClass('ipt_uif_droppable_element_added');
			});
		},

		builderHandleDrop : function(event, ui, container, callback) {
			ui.helper.find('.ipt_uif_droppable_element_wrap').removeClass('white');
			$(this).removeClass('ipt_uif_highlight');
			$(this).removeClass('ipt_uif_droppable_hover');
			//Two conditions
			//First the item is being dragged from .ipt_uif_droppable_elements_wrap
			//The item is being dragged within
			var item;
			var layout_key = $(this).data('containerKey');

			if(ui.draggable.hasClass('ipt_uif_droppable_element_added')) {
				item = ui.draggable;
				//Reset the names
				var new_name = $(this).data('replaceby') + '[' + $(this).data('containerKey') + '][elements]';
				item.find('> input.element_m_type').attr('name', new_name + '[m_type][]');
				item.find('> input.element_type').attr('name', new_name + '[type][]');
				item.find('> input.element_key').attr('name', new_name + '[key][]');

				//Append it
				if($(this).is(item.parent())) {
					//Do nothing
				} else {
					//Tell the bloody sortable to append it when it is done
					var append_to = $(this);
					item.data('ipt_uif_droppable_move', append_to);
					item.addClass('ipt_uif_droppable_element_move');
				}

				//That's it I guess
			} else {
				item = ui.draggable.clone();

				//Remove the template script
				var template_script = item.find('> .ipt_uif_builder_settings');
				var new_settings = $('<div class="ipt_uif_builder_settings"></div>');
				var decoded = new_settings.html(template_script.text()).text();
				new_settings.html(decoded);
				template_script.remove();
				item.find('.ipt_uif_droppable_element_wrap').before(new_settings);

				//Get the data variables
				var keys = $(container).data('ipt_uif_builder_keys');
				var replaces = $(container).data('ipt_uif_builder_replace');

				var prefix_to_replace = ui.draggable.data('replacethis');
				var prefix_replace_by = $(this).data('replaceby');

				var key = 0;
				var type = item.find('.element_m_type').val();
				if(undefined !== keys[type]) {
					key = keys[type];
					keys[type]++;
				} else {
					keys[type] = key;
				}
				var rk = new RegExp(methods.quote(replaces.key), 'g');
				var rl = new RegExp(methods.quote(replaces.l_key), 'g');
				var rprefix = new RegExp(methods.quote(prefix_to_replace), 'g');

				//Set the proper HTML name of the hidden element
				item.html(function(i, oldHTML) {
					var newHTML = oldHTML.replace(rk, key);
					newHTML = newHTML.replace(rprefix, prefix_replace_by);
					return newHTML.replace(rl, layout_key);
				});

				//Make the disabled="disabled" disappear
				item.find('> input.element_m_type').attr('disabled', false);
				item.find('> input.element_type').attr('disabled', false);
				item.find('> input.element_key').attr('disabled', false);

				//Now check for dbmap
				if ( item.data('dbmap') === 1 ) {
					ui.draggable.addClass('ipt_uif_droppable_element_disabled');
					item.data('ipt_uif_builder_dbmap_original', ui.draggable);
				}

				//Apply the added class
				item.addClass('ipt_uif_droppable_element_added');
				item.hide();

				//Append
				$(this).append(item);

				//Add any new Framework item
				// item.iptPluginUIFAdmin({
				// 	applyUIOnly: true
				// });

				//Check for droppables
				if(item.find('.ipt_uif_builder_drop_here').length) {
					methods.builderDroppables.apply(item.get(0), [container]);
				}

				//Apply the Settings Event - not necessary since delegated
				//methods.builderElementSettingsEvent.apply(item.get(0), [container]);

				// Add the title if it is there
				var possible_title_id = item.find('.element_m_type').val() + '_' + item.find('.element_key').val() + '_title',
				possible_title = item.find('#' + possible_title_id).val();
				possible_title = methods.stripTags( possible_title );

				if ( possible_title && typeof( possible_title ) == 'string' ) {
					item.find('> .ipt_uif_droppable_element_wrap > h3.element_title_h3 > span.element_title').text( ' : ' + possible_title.trim() );
					item.find('> .ipt_uif_droppable_element_wrap > h3.element_title_h3').attr( 'title', possible_title.trim() );
				}

				//Show it
				item.slideDown('fast');

				//Update the keys
				$(container).data('ipt_uif_builder_keys', keys);
			}

			$(this).removeClass('ipt_uif_builder_drop_here_empty');

			if(typeof(callback) == 'function') {
				callback();
			}
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

		testImage : function(filename) {
			return (/\.(gif|jpg|jpeg|tiff|png)$/i).test(filename);
		}
	};

	$.fn.iptUIFBuilder = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptUIFBuilder');
			return this;
		}
	};
})(jQuery);

jQuery(document).ready(function($) {
	$( '.ipt_uif_builder' ).iptUIFBuilder();
});

