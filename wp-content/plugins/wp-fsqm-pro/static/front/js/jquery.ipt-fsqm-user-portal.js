/**
 * User Portal Helper script for eForm
 *
 * @author     SwashataATiPanelThemes.com
 *
 * @dependency jquery, ipt-fsqm-up-datatable-yadcf, ipt-fsqm-up-datatable
 * @license    Themeforest Split License
 */
(function($) {
	"use strict";
	var methods = {
		init: function() {
			return this.each(function() {
				var _self = this;
				var primary_css = {
					id : 'eform-up-css',
					src : $( '#eform-up-css' ).attr( 'href' )
				};
				$(this).iptPluginUIFFront({
					callback : function() {
						methods.initDataTable.apply(_self);
					},
					additionalThemes : [primary_css]
				});
			});
		},

		//Other methods
		number_format : function(num) {
			var number = parseFloat(num);
			if(isNaN(number)) {
				return num;
			} else {
				return (parseInt(num * 100) / 100);
			}
		},
		initDataTable : function() {
			var _self = this,
			op = {
				settings : $(this).data('settings'),
				nonce : $(this).data('nonce'),
				progressbar : $(this).find('.ipt_fsqm_up_pb'),
				ajaxloader : $(this).find('.ipt_fsqm_up_al'),
				ajaxurl : $(this).data('ajaxurl')
			};

			// Clear the HTML
			$(this).find('.ipt_fsqm_up_table tbody').html('');

			// Call the fetchData recursively
			methods.fetchData.apply(_self, [0, op]);
		},

		fetchData : function(doing, op) {
			var _self = this;
			$.post(op.ajaxurl, {
				action : 'ipt_fsqm_user_portal',
				settings : op.settings,
				_wpnonce : op.nonce,
				doing : doing
			}, function(response) {
				if ( response == null || response == 0 ) {
					op.ajaxloader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.ajaxloader.find('.ipt_uif_ajax_loader_text').text(iptFSQMUP.ajax.null_response + ' ' + iptFSQMUP.ajax.advice);
				}
				if ( response.success == true ) {
					op.progressbar.progressbar('option', 'value', methods.number_format(response.done));
					$(_self).find('.ipt_fsqm_up_table tbody').append(response.html);

					if ( response.done == 100 ) {
						methods.applyDataTable.apply(_self, [op]);
					} else {
						methods.fetchData.apply(_self, [++doing, op]);
					}
				} else {
					op.ajaxloader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
					op.ajaxloader.find('.ipt_uif_ajax_loader_text').text(response.error_msg);
				}

			}, 'json').fail(function(jqXHR, textStatus, errorThrown) {
				op.ajaxloader.find('.ipt_uif_ajax_loader_inner').removeClass('ipt_uif_ajax_loader_animate');
				op.ajaxloader.find('.ipt_uif_ajax_loader_text').text(iptFSQMUP.ajax.ajax_error + ' (' + textStatus + ' ' + errorThrown + ') ' + iptFSQMUP.ajax.advice);
			});
		},
		applyDataTable : function(op) {
			var _self = this;
			$(this).find('.ipt_fsqm_up_table').iptPluginUIFFront({
				callback : function() {
					var dt = $(_self).find('.ipt_fsqm_up_table');
					dt.trigger('fsqm.userPortalComplete');
					var upTable = $(_self).find('.ipt_fsqm_up_table').show().DataTable({
						"bJQueryUI": true,
						"language" : iptFSQMUP.l10n,
						"sPaginationType": "full_numbers",
						"aaSorting" : [[1, "desc"]],
						"bProcessing" : true,
						"aLengthMenu" : [[10, 30, 60, -1], [10, 30, 60, iptFSQMUP.allLabel]],
						"iDisplayLength" : 30,
						"dom" : '<"fg-toolbar fsqm-up-tt ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lprf>' + 't'+ '<"fg-toolbar ui-toolbar fsqm-up-bt ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"ip>',
						"responsive" : true,
						"autoWidth": true,
						"columnDefs" : [
							{ "width" : "20%", "targets" : 0 },
							{ "width" : "20%", "targets" : 1 },
							{ "width" : "35%", "targets" : -1 }
						],
						initComplete: function () {
							if ( op.settings.filters == '1' ) {
								var table = $(_self).find('.ipt_fsqm_up_table');
								this.api().columns().every( function( index ) {
									var column = this;
									var header = $( column.header() );
									// Do some specific filtering
									if ( header.hasClass('action_label') || header.hasClass('score_label') || header.hasClass('mscore_label') || header.hasClass('pscore_label') ) {
										// Do not add any filtering here
										return true;
									} else if ( header.hasClass('date_label') ) {
										// Do something for date
										// Probably a date range picker
										var input1 = $('<input type="text" class="ipt_uif_text ipt_fsqm_up_dpicker" id="ipt_fsqm_ui_d1" placeholder="' + iptFSQMUP.dpPlaceholderf + '" />'),
										input2 = $('<input type="text" class="ipt_uif_text ipt_fsqm_up_dpicker" id="ipt_fsqm_ui_d2" placeholder="' + iptFSQMUP.dpPlaceholdert + '" />');
										table.find('th.date_filter').append( input1 ).append(' - ').append(input2);
										// Apply the datepicker
										var ui_theme_id = $('.ipt_fsqm_user_portal').data('ui-theme-id');
										input1.datepicker({
											dateFormat: 'yy-mm-dd',
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
											defaultDate: "-1y",
											numberOfMonths: 3,
											onClose: function( selectedDate ) {
												input2.datepicker( 'option', 'minDate', selectedDate );
												$('body').removeClass( ui_theme_id );
												upTable.draw();
											},
											beforeShow : function(input, ins) {
												$('body').addClass( ui_theme_id );
												//return ins.settings;
											},
											appendTo: $('.ipt_fsqm_user_portal'),
											duration: 0
										});
										input2.datepicker({
											dateFormat: 'yy-mm-dd',
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
											defaultDate: "-1y",
											numberOfMonths: 3,
											onClose: function( selectedDate ) {
												input2.datepicker( 'option', 'mixDate', selectedDate );
												$('body').removeClass( ui_theme_id );
												upTable.draw();
											},
											beforeShow : function(input, ins) {
												$('body').addClass( ui_theme_id );
												//return ins.settings;
											},
											duration: 0
										});
										$.fn.dataTable.ext.search.push(function( settings, data, dataIndex ) {
											var min = new Date( input1.val() ).getTime() / 1000,
											max = new Date( input2.val() ).getTime() / 1000,
											cdate = parseFloat( data[index], 10 );
											if ( ( isNaN( min ) && isNaN( max ) ) || ( isNaN( min ) && cdate <= max ) || ( min <= cdate   && isNaN( max ) ) || ( min <= cdate   && cdate <= max ) ) {
												return true;
											}
											return false;
										});
									} else {
										var select = $('<select class="ipt_uif_select"><option value="">' + iptFSQMUP.allFilter + '</option></select>'),
										cssClass = $(column.header()).attr('class').split('_')[0] + '_filter';
										select.appendTo( table.find('th.' + cssClass) ).on( 'change', function() {
											var val = $.fn.dataTable.util.escapeRegex(
												$(this).val()
											);
											column.search( val ? '^'+val+'$' : '', true, false ).draw();
										} );
										var searchData = [];
										column.nodes().to$().each( function() {
											searchData.push( $( this ).data( 'search' ) );
										} );
										searchData = $.unique( searchData ).sort();
										$( searchData ).each( function( d ) {
											select.append( '<option value="' + this + '">' + this + '</option>' );
										} );
									}
								} );
							}
						}
					});
					dt.addClass('ui-widget-content');
					$('.dataTables_filter input[type="text"], .dataTables_filter input[type="search"]').addClass('ipt_uif_text');
					$('.dataTables_length select').addClass('ipt_uif_select');
					upTable.on('responsive-resize', function( e, dataTable, columns ) {
						for ( var index in columns ) {
							// Needed for rather complex header
							if ( columns[index] == true ) {
								dt.find('thead > tr').eq(0).find('th').eq(index).show();
							} else {
								dt.find('thead > tr').eq(0).find('th').eq(index).hide();
							}
						}
					});
					op.progressbar.hide();
					op.ajaxloader.hide();
					dt.trigger('fsqm.userPortalDataTableComplete');
					dt.trigger( 'dataTablesCompleted.eform' );
				}
			});
		}
	};

	$.fn.iptFSQMUserPortal = function(method) {
		if(methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof(method) == 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.iptFSQMUserPortal');
			return this;
		}
	};
})(jQuery);

jQuery(document).ready(function($) {
	$('.ipt_fsqm_user_portal').iptFSQMUserPortal();
});
