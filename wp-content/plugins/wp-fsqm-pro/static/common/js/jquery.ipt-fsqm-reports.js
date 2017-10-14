/**
 * Report generator for FSQM Reports & Analysis
 *
 * @dependency jquery, google charts
 *
 * @author     Swashata@iPanelThemes.com
 * @version 2.0.0
 * @license    Themeforest Split
 */
;(function ( $, window, document, undefined ) {
	"use strict";

	// At first we just load the google charts
	google.charts.load( 'current', {
		packages: [ 'corechart', 'bar' ]
	} );
	// And a variable to check the status
	var hasChartLoaded = false;
	// We change the variable when chart packages load
	// It is completely okay to call multiple setOnLoadCallback
	// @link http://stackoverflow.com/questions/1380043/is-it-ok-to-use-google-setonloadcallback-multiple-times
	// @link https://developers.google.com/chart/interactive/docs/basic_multiple_charts
	google.charts.setOnLoadCallback( function() {
		hasChartLoaded = true;
	} );

	// Create the defaults once
	var pluginName = "iptFSQMReport",
	// Quite a large list of options, but it is needed so let us not make a fuss
	defaults = {
		settings: {},
		survey: {},
		feedback: {},
		pinfo: {},
		filters: {},
		wpnonce: '',
		ajaxurl: '',
		form_id: 0,
		do_data: false,
		do_data_nonce: '',
		do_names: false,
		do_names_nonce: '',
		do_others: false,
		do_others_nonce: '',
		do_date: false,
		do_date_nonce: '',
		cmeta: {},
		material: false,
		action: 'ipt_fsqm_report',
		query_elements: {},
		sensitive_data: false,
		sensitive_data_nonce: ''
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
		this.init();
	}

	Plugin.prototype = {
		// Initialization logic
		init: function () {
			// Abort if customizer is active
			if ( "undefined" !== typeof( wp ) && "undefined" !== typeof( wp.customize ) ) {
				if ( console && console.warn ) {
					console.warn('Customizer Detected. Shutting Down FSQM Reports');
				}
				// Rest will be managed by the main FSQM script
				return;
			}

			this.progressBar = this.jElement.find('.ipt_fsqm_report_progressbar').eq(0).progressbar();
			this.loader = this.jElement.find('.ipt_fsqm_report_ajax').eq(0);
			this.printer = this.jElement.next('.ipt_fsqm_report_print');
			// No need to hide the printer
			// because of new CSS
			this.jElement.data( 'ipt_fsqm_report_op', this.settings );
			this.generateReport( 0 );
		},

		// Generate Report
		generateReport: function( doing ) {
			// Store some references
			var that = this,
			op = this.settings;

			// Start the post and do accordingly
			$.post( this.settings.ajaxurl, {
				action : this.settings.action,
				settings : this.settings.settings,
				wpnonce : this.settings.wpnonce,
				form_id : this.settings.form_id,
				do_data : this.settings.do_data,
				do_data_nonce : this.settings.do_data_nonce,
				sensitive_data : this.settings.sensitive_data,
				sensitive_data_nonce : this.settings.sensitive_data_nonce,
				do_names: this.settings.do_names,
				do_names_nonce: this.settings.do_names_nonce,
				do_others: this.settings.do_others,
				do_others_nonce: this.settings.do_others_nonce,
				do_date: this.settings.do_date,
				do_date_nonce: this.settings.do_date_nonce,
				query_elements : this.settings.query_elements,
				filters: this.settings.filters,
				doing : doing
			}, function( response ) {
				// Check for null response
				if ( response === null ) {
					that.loader.find('.ipt_uif_ajax_loader_icon').removeClass('ipt_uif_ajax_loader_spin');
					that.loader.find('.ipt_uif_ajax_loader_text').text('ServerSide Error');
					return;
				}

				// Save the response
				that.saveResponse( response );

				// Progress the progressbar
				// We need to check if the progressbar is okay progressing
				// Because pluginUIF might not have fired at this instance
				try {
					that.progressBar.progressbar( 'option', 'value', that.numberFormat( response.done ) );
				} catch ( e ) {
					that.progressBar.progressbar();
					that.progressBar.progressbar( 'option', 'value', that.numberFormat( response.done ) );
				}


				// If all done
				if ( response.done >= 100 ) {
					that.initCharts();
				// Else shoot the same function again
				} else {
					that.generateReport( ++doing );
				}
			}, 'json' ).fail( function( jqXHR, textStatus, errorThrown ) {
				that.loader.find('.ipt_uif_ajax_loader_inner').removeClass( 'ipt_uif_ajax_loader_animate' );
				that.loader.find('.ipt_uif_ajax_loader_text').text( 'HTTP ERROR: ' + textStatus + ' ' + errorThrown );
			} );
		},

		// Calculate loop
		saveResponse: function( response ) {
			// Init reference variables
			var that = this,
			op = this.settings;

			// Init all variables
			var count, other, avg, avg_total, avg_count, difference, g_data, g_key, g_tmp_arr, table_to_update, data_table, viz_table, viz_div,
				other_table, td_count_to_update, table_to_append, new_tr, to_append, o_key, other_data, m_key, o_head, params, s_key,
				feedback_data, r_key, c_key, t_key, td_row_span_sorting, orders, order, sorting_okey_first, f_key, uploads_key, uploads,
				total_uploads, upload_row_span, upload_key, upload, upload_html, file_ext, feedback, matrixSkel, matrixHead, matrixOutput,
				p_key, tax, term, new_td, new_ul;

			// Save the survey/mcq
			for ( m_key in op.survey.elements ) {
				// Do not do anything if response does not contain data
				if ( response.survey[m_key] === undefined || response.survey[m_key] === null ) {
					continue;
				}

				// Initiate a new object if necessary
				if ( op.survey.data[m_key] === undefined || $.type( op.survey.data[m_key] ) !== 'object' ) {
					op.survey.data[m_key] = {};
				}

				// Init some DOM elements
				table_to_update = that.jElement.find('.ipt_fsqm_report_survey_' + m_key + ' table.table_to_update').eq(0);
				data_table = table_to_update.find('td.data table');
				other_table = table_to_update.next('div').find('table.others');
				if ( data_table.length === 0 ) {
					data_table = table_to_update;
				}

				// Now do accordingly
				switch ( op.survey.elements[m_key].type ) {
					// Default for third party extensibility
					default :
						if ( undefined !== iptFSQMReport.callbacks[op.survey.elements[m_key].type] && typeof( window[iptFSQMReport.callbacks[op.survey.elements[m_key].type]] ) == 'function' ) {
							window[iptFSQMReport.callbacks[op.survey.elements[m_key].type]].apply( that, [ op.survey.elements[m_key], op.survey.data[m_key], response.survey[m_key], m_key, table_to_update, data_table, op ] );
						}
						break;

					// Radio, checkbox, select
					case 'radio' :
					case 'checkbox' :
					case 'select' :
					case 'thumbselect' :
						for ( o_key in response.survey[m_key] ) {
							if ( o_key == 'others_data' && op.do_others ) {
								if ( response.survey[m_key].others_data !== undefined && response.survey[m_key].others_data.length ) {
									for( other_data in response.survey[m_key].others_data ) {
										other = response.survey[m_key].others_data[other_data];
										// Append it
										table_to_append = other_table.find('tbody');
										table_to_append.find('tr.empty').remove();
										new_tr = $('<tr />');
										// Append the value
										new_tr.append('<th>' + other.value + '</th>');
										// Append the name
										if ( op.do_names ) {
											if ( op.sensitive_data ) {
												new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
											} else {
												new_tr.append('<td>' + other.name + '</td>');
											}
										}
										// Append the email
										if ( op.sensitive_data ) {
											new_tr.append('<td>' + other.email + '</td>');
										}
										// Append the date
										if ( op.do_date ) {
											new_tr.append( '<td>' + other.date + '</td>' );
										}

										table_to_append.append(new_tr);
									}
								}
								continue;
							}
							if ( op.survey.data[m_key][o_key] === undefined || op.survey.data[m_key][o_key] === null ) {
								op.survey.data[m_key][o_key] = 0;
							}
							count = response.survey[m_key][o_key];
							if ( !isNaN( count ) ) {
								op.survey.data[m_key][o_key] += count;
								// Update the tds
								if ( op.do_data ) {
									that.updateCount( data_table.find('td.data_op_' + o_key), op.survey.data[m_key][o_key] );
								}
							}
						}
						break;

					// Slider
					case 'slider' :
						that.calculateSlider( response.survey[m_key], op.survey.data[m_key], data_table, data_table.find('tbody'), null, 'slider_value_to_count_tr', 'slider_value_to_count', data_table.find('> tfoot > tr > th:first-child'), op.survey.elements[m_key].settings );
						break;

					// Range
					case 'range' :
						that.calculateRange( response.survey[m_key], op.survey.data[m_key], data_table, data_table.find('tbody'), null, 'range_value_to_count_tr', 'range_value_to_count', data_table.find('> tfoot > tr > th:first-child'), op.survey.elements[m_key].settings );
						break;

					// Grading
					case 'grading' :
						for ( o_key in response.survey[m_key] ) {
							// backward compatibility -2.4.0
							if ( typeof( op.survey.elements[m_key].settings.options[o_key] ) !== 'object' ) {
								op.survey.elements[m_key].settings.options[o_key] = {
									label: op.survey.elements[m_key].settings.options[o_key],
									prefix: '',
									suffix: ''
								};
							}

							if ( op.do_data ) {
								o_head = data_table.find('tr.grading_' + o_key + '.head');
								if ( !o_head.length ) {
									o_head = $('<tr class="grading_' + o_key + ' head"><th class="head_th" rowspan="1">' + op.survey.elements[m_key].settings.options[o_key].label + '</th><th>' + iptFSQMReport.grading + '</th><th>' + iptFSQMReport.count + '</th></tr>');
									data_table.find('tbody').append(o_head);
									o_head.after('<tr class="grading_' + o_key + '_avg avg head"><th>' + iptFSQMReport.avg + ' <span class="avg_count">0</span> ' + iptFSQMReport.avg_count + '</th><th><span class="avg">0</span></th><th>' + (op.survey.elements[m_key].settings.range ? iptFSQMReport.avg_range : iptFSQMReport.avg_slider) + '</th></tr>');
								}
							}


							if ( undefined === op.survey.data[m_key][o_key] || op.survey.data[m_key][o_key] === null ) {
								op.survey.data[m_key][o_key] = {};
							}

							op.survey.elements[m_key].settings.prefix = op.survey.elements[m_key].settings.options[o_key].prefix;
							op.survey.elements[m_key].settings.suffix = op.survey.elements[m_key].settings.options[o_key].suffix;
							params = [response.survey[m_key][o_key], op.survey.data[m_key][o_key], data_table, data_table.find('tbody'), o_head, 'grading_' + o_key + '_value_to_count_tr', 'grading_' + o_key + '_value_to_count', data_table.find('tr.grading_' + o_key + '_avg th'), op.survey.elements[m_key].settings];

							if ( op.survey.elements[m_key].settings.range ) {
								that.calculateRange.apply(that, params);
							} else {
								that.calculateSlider.apply(that, params);
							}

							if ( op.do_data ) {
								o_head.find('th.head_th').attr('rowspan', data_table.find('tr.grading_' + o_key + '_value_to_count_tr').length + 1);
							}
						}
						break;

					// Spinners
					case 'spinners' :
						for ( o_key in response.survey[m_key] ) {
							// Add header if do_data
							if ( op.do_data ) {
								o_head = data_table.find('tr.spinners_' + o_key + '.head');
								// Create a head if not already present
								if ( ! o_head.length ) {
									o_head = $('<tr class="spinners_' + o_key + ' head"><th class="head_th" rowspan="1">' + op.survey.elements[m_key].settings.options[o_key].label + '</th><th>' + iptFSQMReport.value + '</th><th>' + iptFSQMReport.count + '</th></tr>');
									data_table.find('tbody').append(o_head);
									o_head.after('<tr class="spinners_' + o_key + '_avg avg head"><th>' + iptFSQMReport.avg + ' <span class="avg_count">0</span> ' + iptFSQMReport.avg_count + '</th><th><span class="avg">0</span></th><th>' + (op.survey.elements[m_key].settings.show_range ? iptFSQMReport.avg_range : iptFSQMReport.avg_slider) + '</th></tr>');
								}
							}

							// Create a new object if not already present
							if ( undefined === op.survey.data[m_key][o_key] ) {
								op.survey.data[m_key][o_key] = {};
							}

							params = [response.survey[m_key][o_key], op.survey.data[m_key][o_key], data_table, data_table.find('tbody'), o_head, 'spinners_' + o_key + '_value_to_count_tr', 'spinners_' + o_key + '_value_to_count', data_table.find('tr.spinners_' + o_key + '_avg th'), op.survey.elements[m_key].settings];

							that.calculateSlider.apply( that, params );

							if ( op.do_data ) {
								o_head.find('th.head_th').attr('rowspan', data_table.find('tr.spinners_' + o_key + '_value_to_count_tr').length + 1);
							}
						}
						break;

					// Star & Scale rating
					case 'starrating' :
					case 'scalerating' :
						for ( o_key in response.survey[m_key] ) {
							if ( op.do_data ) {
								o_head = data_table.find('tr.rating_' + o_key + '.head');
								if ( !o_head.length ) {
									o_head = $('<tr class="rating_' + o_key + ' head"><th class="head_th" rowspan="1">' + op.survey.elements[m_key].settings.options[o_key] + '</th><th>' + iptFSQMReport.rating + '</th><th>' + iptFSQMReport.count + '</th></tr>');
									data_table.find('tbody').append(o_head);
									o_head.after('<tr class="rating_' + o_key + '_avg avg head"><th>' + iptFSQMReport.avg + ' <span class="avg_count">0</span> ' + iptFSQMReport.avg_count + '</th><th><span class="avg_img"></span></th><th><span class="avg">0</span> ' + (op.survey.elements[m_key].settings.show_range ? iptFSQMReport.avg_range : iptFSQMReport.avg_slider) + '</th></tr>');
								}
							} else {
								o_head = null;
							}

							if ( undefined === op.survey.data[m_key][o_key] ) {
								op.survey.data[m_key][o_key] = {};
							}
							params = [ response.survey[m_key][o_key], op.survey.data[m_key][o_key], data_table, data_table.find('tbody'), o_head, 'rating_' + o_key + '_value_to_count_tr', 'rating_' + o_key + '_value_to_count', data_table.find('tr.rating_' + o_key + '_avg th'), op.survey.elements[m_key].settings ];

							that.calculateRating.apply( that, params );

							if ( op.do_data ) {
								o_head.find('th.head_th').attr('rowspan', data_table.find('tr.rating_' + o_key + '_value_to_count_tr').length + 1);
							}
						}
						break;

					// Smiley Rating
					case 'smileyrating' :
						for ( s_key in response.survey[m_key] ) {
							if ( s_key == 'feedback_data' && op.do_others ) {
								if ( response.survey[m_key].feedback_data !== undefined && response.survey[m_key].feedback_data.length ) {
									for ( feedback_data in response.survey[m_key].feedback_data ) {
										other = response.survey[m_key].feedback_data[feedback_data];
										new_tr = $('<tr>');
										table_to_append = other_table.find('tbody');
										table_to_append.find('tr.empty').remove();
										new_tr.append('<th>' + other.entry + '</th>');
										new_tr.append('<td>' + other.rating + '</td>');
										if ( op.do_names ) {
											if ( op.sensitive_data ) {
												new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
											} else {
												new_tr.append('<td>' + other.name + '</td>');
											}
										}
										if ( op.sensitive_data ) {
											new_tr.append('<td>' + other.email + '</td>');
										}
										if ( op.do_date ) {
											new_tr.append('<td>' + other.date + '</td>');
										}
										table_to_append.append(new_tr);
									}
								}
							} else {
								if ( undefined === op.survey.data[m_key][s_key] ) {
									op.survey.data[m_key][s_key] = 0;
								}
								op.survey.data[m_key][s_key] += response.survey[m_key][s_key];
								if ( op.do_data ) {
									that.updateCount( data_table.find('td.' + s_key), op.survey.data[m_key][s_key] );
								}
							}
						}
						break;

					// Like Dislike
					case 'likedislike' :
						for ( s_key in response.survey[m_key] ) {
							if ( s_key == 'feedback_data' && op.do_others ) {
								if ( response.survey[m_key].feedback_data !== undefined && response.survey[m_key].feedback_data.length ) {
									for ( feedback_data in response.survey[m_key].feedback_data ) {
										other = response.survey[m_key].feedback_data[feedback_data];
										new_tr = $('<tr>');
										table_to_append = other_table.find('tbody');
										table_to_append.find('tr.empty').remove();
										new_tr.append('<th>' + other.entry + '</th>');
										new_tr.append('<td>' + other.rating + '</td>');
										if ( op.do_names ) {
											if ( op.sensitive_data ) {
												new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
											} else {
												new_tr.append('<td>' + other.name + '</td>');
											}
										}
										if ( op.sensitive_data ) {
											new_tr.append('<td>' + other.email + '</td>');
										}
										if ( op.do_date ) {
											new_tr.append('<td>' + other.date + '</td>');
										}
										table_to_append.append(new_tr);
									}
								}
							} else {
								if ( undefined === op.survey.data[m_key][s_key] ) {
									op.survey.data[m_key][s_key] = 0;
								}
								op.survey.data[m_key][s_key] += response.survey[m_key][s_key];
								if ( op.do_data ) {
									data_table.find('td.' + s_key).html(op.survey.data[m_key][s_key]);
								}
							}
						}
						break;

					// Matrix Dropdown
					case 'matrix_dropdown' :
						for ( r_key in response.survey[m_key] ) {
							if ( op.survey.data[m_key][r_key] === undefined ) {
								op.survey.data[m_key][r_key] = {};
							}
							for ( c_key in response.survey[m_key][r_key] ) {
								if ( op.survey.data[m_key][r_key][c_key] === undefined ) {
									op.survey.data[m_key][r_key][c_key] = {};
								}
								for ( o_key in response.survey[m_key][r_key][c_key] ) {
									if ( op.survey.data[m_key][r_key][c_key][o_key] === undefined ) {
										op.survey.data[m_key][r_key][c_key][o_key] = 0;
									}
									if ( $.isNumeric( response.survey[m_key][r_key][c_key][o_key] ) ) {
										op.survey.data[m_key][r_key][c_key][o_key] += response.survey[m_key][r_key][c_key][o_key];
									}

									if ( op.do_data ) {
										table_to_update.find( 'td.row-' + r_key + '-column-' + c_key + '-op-' + o_key ).html( op.survey.data[m_key][r_key][c_key][o_key] );
									}
								}
							}
						}
						break;

					// Matrix
					case 'matrix' :
						for ( r_key in response.survey[m_key] ) {
							if ( op.survey.data[m_key][r_key] === undefined ) {
								op.survey.data[m_key][r_key] = {};
							}
							for ( c_key in response.survey[m_key][r_key] ) {
								if ( !isNaN(response.survey[m_key][r_key][c_key]) ) {
									if ( undefined === op.survey.data[m_key][r_key][c_key] ) {
										op.survey.data[m_key][r_key][c_key] = response.survey[m_key][r_key][c_key];
									} else {
										op.survey.data[m_key][r_key][c_key] += response.survey[m_key][r_key][c_key];
									}

									if ( op.do_data ) {
										that.updateCount(data_table.find('td.row_' + r_key + '_col_' + c_key), op.survey.data[m_key][r_key][c_key]);
									}
								}
							}
						}
						break;

					// Toggle
					case 'toggle' :
						for ( t_key in response.survey[m_key] ) {
							if ( !isNaN(response.survey[m_key][t_key]) ) {
								if ( undefined === op.survey.data[m_key][t_key] ) {
									op.survey.data[m_key][t_key] = response.survey[m_key][t_key];
								} else {
									op.survey.data[m_key][t_key] += response.survey[m_key][t_key];
								}
								if ( op.do_data ) {
									that.updateCount( data_table.find('td.data_op_' + t_key), op.survey.data[m_key][t_key] );
								}
							}
						}
						break;

					// Sorting
					case 'sorting' :
						for ( s_key in response.survey[m_key] ) {
							td_row_span_sorting = that.objectLength( op.survey.elements[m_key].settings.options );
							if ( isNaN( response.survey[m_key][s_key] ) ) {
								//Might be other orders
								if ( s_key == 'orders' && typeof(response.survey[m_key][s_key]) == 'object' ) {
									for ( orders in response.survey[m_key][s_key] ) {
										if ( orders === '' ) {
											continue;
										}
										if ( undefined === op.survey.data[m_key].orders[orders] ) {
											// Add it
											op.survey.data[m_key].orders[orders] = response.survey[m_key][s_key][orders];

											// Append it
											if ( op.do_data ) {
												order = orders.split('-');
												if ( data_table.find('tbody tr').length ) {
													data_table.find('tbody').append('<tr class="head"><th colspan="3"></th></tr>');
												}
												sorting_okey_first = true;
												for ( o_key in order ) {
													if ( undefined === op.survey.elements[m_key].settings.options[order[o_key]] ) {
														continue;
													}
													to_append = '';
													if ( sorting_okey_first === true ) {
														sorting_okey_first = false;
														to_append = '<tr><td class="icons">' + iptFSQMReport.sorting_img + '</td><th>' + op.survey.elements[m_key].settings.options[order[o_key]].label + '</th><td rowspan="' + td_row_span_sorting + '" data="' + orders + '"></td></tr>';
													} else {
														to_append = '<tr><td class="icons">' + iptFSQMReport.sorting_img + '</td><th>' + op.survey.elements[m_key].settings.options[order[o_key]].label + '</th>';
													}
													data_table.find('tbody').append(to_append);
												}
											}
										} else {
											// Increase it
											op.survey.data[m_key].orders[orders] += response.survey[m_key][s_key][orders];
										}

										// Update it
										if ( op.do_data ) {
											that.updateCount( data_table.find('td[data="' + orders + '"]'), op.survey.data[m_key].orders[orders] );
										}
									}
								}
							} else {
								if ( undefined === op.survey.data[m_key][s_key] ) {
									op.survey.data[m_key][s_key] = response.survey[m_key][s_key];
								} else {
									op.survey.data[m_key][s_key] += response.survey[m_key][s_key];
								}
							}
						}
						break;
				}
			} // End mcq loop

			// Save the feedback/freetype
			for ( f_key in op.feedback.elements ) {
				if ( typeof(response.feedback[f_key] ) !== 'object' ) {
					continue;
				}

				table_to_update = that.jElement.find('.ipt_fsqm_report_feedback_' + f_key + ' table.table_to_update').eq(0);
				table_to_append = table_to_update.find('> tbody');
				table_to_append.find('tr.empty').remove();

				switch ( op.feedback.elements[f_key].type ) {
					// Open scope for third party integration
					default :
						if ( undefined !== iptFSQMReport.callbacks[op.feedback.elements[f_key].type] && typeof(window[iptFSQMReport.callbacks[op.feedback.elements[f_key].type]]) == 'function' ) {
							window[iptFSQMReport.callbacks[op.feedback.elements[f_key].type]].apply( that, [op.feedback.elements[f_key], op.feedback.data[f_key], response.feedback[f_key], f_key, table_to_update, op] );
						}
						break;
					case 'upload' :
						for ( uploads_key in response.feedback[f_key] ) {
							uploads = response.feedback[f_key][uploads_key];
							total_uploads = uploads.uploads.length;
							upload_row_span = total_uploads === 0 ? 1 : total_uploads;
							new_tr = $('<tr />');
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append( '<td rowspan="' +  upload_row_span + '"><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + uploads.id + '">' + uploads.name + '</a></td>' );
								} else {
									new_tr.append( '<td rowspan="' +  upload_row_span + '">' + uploads.name + '</td>' );
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append( '<td rowspan="' +  upload_row_span + '">' + uploads.email + '</td>' );
							}
							if ( op.do_date ) {
								new_tr.append( '<td rowspan="' +  upload_row_span + '">' + uploads.date + '</td>' );
							}

							if ( total_uploads === 0 ) {
								new_tr.append('<th rowspan="' + upload_row_span + '">' + iptFSQMReport.noupload + '</th>');
								table_to_append.append(new_tr);
							} else {
								for ( upload_key in uploads.uploads ) {
									upload = uploads.uploads[upload_key];
									upload_html = '<a href="' + upload.guid + '" target="_blank" title="' + upload.filename + '">';
									file_ext = '';
									if ( upload.thumb_url !== '' ) {
										file_ext = upload.thumb_url.split('.').pop();
										if ( file_ext !== undefined && file_ext !== null ) {
											file_ext = file_ext.toLowerCase();
										}
									}

									if ( upload.thumb_url !== '' && file_ext !== undefined && file_ext !== null && $.inArray( file_ext, [ 'jpg', 'jpeg', 'gif', 'png', 'apng', 'tiff', 'bmp' ] ) !== -1 ) {
										upload_html += '<img src="' + upload.thumb_url + '" /><br />';
									}
									upload_html += upload.name + '</a>';
									new_tr.append('<th>' + upload_html + '</th>');
									table_to_append.append(new_tr);
									new_tr = $('<tr />');
								}
							}
						}

						break;
					case 'feedback_large' :
					case 'feedback_small' :
					case 'mathematical' :
						for ( feedback in response.feedback[f_key] ) {
							other = response.feedback[f_key][feedback];
							new_tr = $('<tr />');
							new_tr.append('<th>' + other.value + '</th>');
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td>' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td>' + other.email + '</td>');
								new_tr.append('<td>' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td>' + other.date + '</td>');
							}
							table_to_append.append(new_tr);
						}
						break;
					case 'signature' :
						for ( feedback in response.feedback[f_key] ) {
							other = response.feedback[f_key][feedback];
							new_tr = $('<tr />');
							if ( other.value !== '' ) {
								new_tr.append('<th><img src="data:image/png;base64,' + other.value + '" style="max-width: 400px; height: auto;" /></th>');
							} else {
								new_tr.append('<th></th>');
							}
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td>' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td>' + other.email + '</td>');
								new_tr.append('<td>' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td>' + other.date + '</td>');
							}
							table_to_append.append(new_tr);
						}
						break;
					case 'gps' :
						for ( feedback in response.feedback[f_key] ) {
							other = response.feedback[f_key][feedback];
							new_tr = '<tr>';
							// Conditionally add the name
							if ( op.do_names ) {
								new_tr += '<td rowspan="4">';
								if ( op.sensitive_data ) {
									new_tr += '<a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' +
									other.id + '">' + other.name + '</a>';
								} else {
									new_tr += other.name;
								}
								new_tr += '</td>';
							}
							// Add the map image
							new_tr += '<th colspan="2"><a target="_blank" href="https://maps.google.com/maps?z=12&t=m&q=loc:' + other.lat + '+' + other.long + '"><img src="' + other.map + '" height="300" width="500" /></a></th>';
							// Add email
							if ( op.sensitive_data ) {
								new_tr += '<td rowspan="4">' + other.email + '</td>';
							}
							// Add date
							if ( op.do_date ) {
								new_tr += '<td rowspan="4">' + other.date + '</td>';
							}
							// Close the tr
							new_tr += '</tr>';
							// Add additional data
							new_tr += '<tr><th>' + op.feedback.elements[f_key].settings.location_name_label + '</th><td>' + other.location_name + '</td></tr>';
							new_tr += '<tr><th>' + op.feedback.elements[f_key].settings.lat_label + '</th><td>' + other.lat + '</td></tr>';
							new_tr += '<tr><th>' + op.feedback.elements[f_key].settings.long_label + '</th><td>' + other.long + '</td></tr>';

							table_to_append.append(new_tr);
						}
						break;

					case 'feedback_matrix' :
						matrixSkel = '<table class="ipt_fsqm_preview">';
						matrixHead = '<tr><th></th>';
						for ( c_key in op.feedback.elements[f_key].settings.columns ) {
							matrixHead += '<th>' + op.feedback.elements[f_key].settings.columns[c_key] + '</th>';
						}
						matrixHead += '</tr>';
						matrixSkel += '<thead>' + matrixHead + '</thead><tfoot>' + matrixHead + '</tfoot><tbody>';
						for ( r_key in op.feedback.elements[f_key].settings.rows ) {
							matrixSkel += '<tr><th>' + op.feedback.elements[f_key].settings.rows[r_key] + '</th>';
							for ( c_key in op.feedback.elements[f_key].settings.columns ) {
								matrixSkel += '<td class="row-' + r_key + '-col-' + c_key + '"></td>';
							}
							matrixSkel += '</tr>';
						}
						matrixSkel += '</tbody></table>';
						matrixSkel = $(matrixSkel);

						for ( feedback in response.feedback[f_key] ) {
							other = response.feedback[f_key][feedback];
							matrixOutput = matrixSkel.clone();
							for ( r_key in other.matrix ) {
								for ( c_key in other.matrix[r_key] ) {
									matrixOutput.find('.row-' + r_key + '-col-' + c_key).html( other.matrix[r_key][c_key] );
								}
							}
							new_tr = '<tr>';
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr += '<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>';
								} else {
									new_tr += '<td>' + other.name + '</td>';
								}
							}
							new_tr += '<td class="data matrix"></td>';
							if ( op.sensitive_data ) {
								new_tr += '<td>' + other.email + '</td>';
							}
							if ( op.do_date ) {
								new_tr += '<td>' + other.date + '</td></tr>';
							}
							new_tr = $(new_tr);
							new_tr.find('td.matrix').append(matrixOutput);
							table_to_append.append(new_tr);
						}
						break;
				}
			} // End freetype loop

			// Save the other/pinfo
			for ( p_key in op.pinfo.elements ) {
				// No need to do anything of there isn't any response
				if ( typeof ( response.pinfo[p_key] ) !== 'object' ) {
					continue;
				}

				// Init some DOM elements
				table_to_update = that.jElement.find('.ipt_fsqm_report_pinfo_' + p_key + ' table.table_to_update').eq(0);
				table_to_append = table_to_update.find('> tbody');
				table_to_append.find('tr.empty').remove();
				data_table = table_to_update.find('td.data table');
				other_table = table_to_update.next('div').find('table.others');
				if ( data_table.length === 0 ) {
					data_table = table_to_update;
				}

				// Now do accordingly to the element
				switch ( op.pinfo.elements[p_key].type ) {
					// Default for third party extensibility
					default :
						if ( undefined !== iptFSQMReport.callbacks[op.pinfo.elements[p_key].type] && typeof( window[iptFSQMReport.callbacks[op.pinfo.elements[p_key].type]] ) == 'function' ) {
							window[iptFSQMReport.callbacks[op.pinfo.elements[p_key].type]].apply( that, [ op.pinfo.elements[p_key], op.pinfo.data[p_key], response.pinfo[p_key], p_key, table_to_update, data_table, op ] );
						}
						break;
					// Text Types
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
					case 'datetime' :
					case 'hidden' :
						// Just like freetype
						for ( feedback in response.pinfo[p_key] ) {
							other = response.pinfo[p_key][feedback];
							new_tr = $('<tr />');
							new_tr.append('<th>' + other.value + '</th>');
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td>' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td>' + other.email + '</td>');
								new_tr.append('<td>' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td>' + other.date + '</td>');
							}
							table_to_append.append(new_tr);
						}
						break;
					// Repeatable
					case 'repeatable' :
						for ( feedback in response.pinfo[p_key] ) {
							other = response.pinfo[p_key][feedback];
							new_tr = $('<tr />');
							new_tr.append('<td class="data">' + other.value + '</td>');
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td style="vertical-align: top;"><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td style="vertical-align: top;">' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td style="vertical-align: top;">' + other.email + '</td>');
								new_tr.append('<td style="vertical-align: top;">' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td style="vertical-align: top;">' + other.date + '</td>');
							}
							table_to_append.append(new_tr);
						}
						break;

					// Guest blog
					case 'guestblog' :
						for ( feedback in response.pinfo[ p_key ] ) {
							other = response.pinfo[p_key][feedback];
							// Add post title
							new_tr = $('<tr />');
							new_tr.append('<th>' + other.title + '</th>');

							// Add other information and meta
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td rowspan="3"><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td rowspan="3">' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td rowspan="3">' + other.email + '</td>');
								new_tr.append('<td rowspan="3">' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td rowspan="3">' + other.date + '</td>');
							}
							// Append the first row
							table_to_append.append(new_tr);

							// Add post value and append the second row
							new_tr = $( '<tr />' );
							new_tr.append( '<td>' + other.value + '</td>' );
							table_to_append.append(new_tr);

							// Add post taxonomy and append the third row
							new_tr = $( '<tr />' );
							new_td = $( '<td />' );
							if ( other.taxonomy.length ) {
								for ( tax in other.taxonomy ) {
									new_td.append( '<h4>' + other.taxonomy[ tax ].tax );
									if ( other.taxonomy[ tax ].terms.length ) {
										new_ul = $( '<ul class="ul-disc" />' );
										for ( term in other.taxonomy[ tax ].terms ) {
											new_ul.append( '<li>' + other.taxonomy[ tax ].terms[ term ] + '</li>' );
										}
										new_td.append( new_ul );
									}
								}
							}
							new_tr.append( new_td );
							table_to_append.append( new_tr );
						}
						break;

					// Address
					case 'address' :
						for ( feedback in response.pinfo[p_key] ) {
							other = response.pinfo[p_key][feedback];
							new_tr = $('<tr />');
							// Append default ones
							new_tr.append( '<th>' + other.values.recipient + '</th>' );
							new_tr.append( '<td>' + other.values.line_one + '</td>' );
							new_tr.append( '<td>' + other.values.line_two + '</td>' );
							new_tr.append( '<td>' + other.values.line_three + '</td>' );
							new_tr.append( '<td>' + other.values.country + '</td>' );
							// Append name
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td>' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td>' + other.email + '</td>');
								new_tr.append('<td>' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td>' + other.date + '</td>');
							}
							table_to_append.append(new_tr);
						}
						break;

					// Payment
					case 'payment' :
						for ( feedback in response.pinfo[p_key] ) {
							other = response.pinfo[p_key][feedback];
							new_tr = $('<tr />');
							// Append default ones
							new_tr.append( '<th>' + other.invoice + '</th>' );
							new_tr.append( '<td>' + other.status + '</td>' );
							new_tr.append( '<th>' + other.txn + '</th>' );
							new_tr.append( '<td>' + other.gateway + '</td>' );
							new_tr.append( '<th>' + other.total + '</th>' );
							// Append name
							if ( op.do_names ) {
								if ( op.sensitive_data ) {
									new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
								} else {
									new_tr.append('<td>' + other.name + '</td>');
								}
							}
							if ( op.sensitive_data ) {
								new_tr.append('<td>' + other.email + '</td>');
								new_tr.append('<td>' + other.phone + '</td>');
							}
							if ( op.do_date ) {
								new_tr.append('<td>' + other.date + '</td>');
							}
							table_to_append.append(new_tr);
						}
						break;
					// MCQs
					case 'p_radio' :
					case 'p_checkbox' :
					case 'p_select' :
						for ( o_key in response.pinfo[p_key] ) {
							if ( o_key == 'others_data' && op.do_others ) {
								if ( response.pinfo[p_key].others_data !== undefined && response.pinfo[p_key].others_data.length ) {
									for( other_data in response.pinfo[p_key].others_data ) {
										other = response.pinfo[p_key].others_data[other_data];
										// Append it
										table_to_append = other_table.find('tbody');
										table_to_append.find('tr.empty').remove();
										new_tr = $('<tr />');
										// Append the value
										new_tr.append('<th>' + other.value + '</th>');
										// Append the name
										if ( op.do_names ) {
											if ( op.sensitive_data ) {
												new_tr.append('<td><a class="thickbox" href="' + op.ajaxurl + '?action=ipt_fsqm_quick_preview&id=' + other.id + '">' + other.name + '</a></td>');
											} else {
												new_tr.append('<td>' + other.name + '</td>');
											}
										}
										// Append the email
										if ( op.sensitive_data ) {
											new_tr.append('<td>' + other.email + '</td>');
										}
										// Append the date
										if ( op.do_date ) {
											new_tr.append( '<td>' + other.date + '</td>' );
										}

										table_to_append.append(new_tr);
									}
								}
								continue;
							}
							if ( op.pinfo.data[p_key][o_key] === undefined || op.pinfo.data[p_key][o_key] === null ) {
								op.pinfo.data[p_key][o_key] = 0;
							}
							count = response.pinfo[p_key][o_key];
							if ( !isNaN( count ) ) {
								op.pinfo.data[p_key][o_key] += count;
								// Update the tds
								if ( op.do_data ) {
									that.updateCount( data_table.find('td.data_op_' + o_key), op.pinfo.data[p_key][o_key] );
								}
							}
						}
						break;
					// Single State
					case 's_checkbox' :
						for ( t_key in response.pinfo[p_key] ) {
							if ( !isNaN(response.pinfo[p_key][t_key]) ) {
								if ( undefined === op.pinfo.data[p_key][t_key] ) {
									op.pinfo.data[p_key][t_key] = response.pinfo[p_key][t_key];
								} else {
									op.pinfo.data[p_key][t_key] += response.pinfo[p_key][t_key];
								}
								if ( op.do_data ) {
									that.updateCount( data_table.find('td.data_op_' + t_key), op.pinfo.data[p_key][t_key] );
								}
							}
						}
						break;
					// Sorting
					case 'p_sorting' :
						for ( s_key in response.pinfo[p_key] ) {
							td_row_span_sorting = that.objectLength( op.pinfo.elements[p_key].settings.options );
							if ( isNaN( response.pinfo[p_key][s_key] ) ) {
								//Might be other orders
								if ( s_key == 'orders' && typeof(response.pinfo[p_key][s_key]) == 'object' ) {
									for ( orders in response.pinfo[p_key][s_key] ) {
										if ( orders === '' ) {
											continue;
										}
										if ( undefined === op.pinfo.data[p_key].orders[orders] ) {
											// Add it
											op.pinfo.data[p_key].orders[orders] = response.pinfo[p_key][s_key][orders];

											// Append it
											if ( op.do_data ) {
												order = orders.split('-');
												if ( data_table.find('tbody tr').length ) {
													data_table.find('tbody').append('<tr class="head"><th colspan="3"></th></tr>');
												}
												sorting_okey_first = true;
												for ( o_key in order ) {
													if ( undefined === op.pinfo.elements[p_key].settings.options[order[o_key]] ) {
														continue;
													}
													to_append = '';
													if ( sorting_okey_first === true ) {
														sorting_okey_first = false;
														to_append = '<tr><td class="icons">' + iptFSQMReport.sorting_img + '</td><th>' + op.pinfo.elements[p_key].settings.options[order[o_key]].label + '</th><td rowspan="' + td_row_span_sorting + '" data="' + orders + '"></td></tr>';
													} else {
														to_append = '<tr><td class="icons">' + iptFSQMReport.sorting_img + '</td><th>' + op.pinfo.elements[p_key].settings.options[order[o_key]].label + '</th>';
													}
													data_table.find('tbody').append(to_append);
												}
											}
										} else {
											// Increase it
											op.pinfo.data[p_key].orders[orders] += response.pinfo[p_key][s_key][orders];
										}

										// Update it
										if ( op.do_data ) {
											that.updateCount( data_table.find('td[data="' + orders + '"]'), op.pinfo.data[p_key].orders[orders] );
										}
									}
								}
							} else {
								if ( undefined === op.pinfo.data[p_key][s_key] ) {
									op.pinfo.data[p_key][s_key] = response.pinfo[p_key][s_key];
								} else {
									op.pinfo.data[p_key][s_key] += response.pinfo[p_key][s_key];
								}
							}
						}
						break;
				}
			} // end pinfo loop
		},

		// Initialize the charts JS
		// And finalizes the reports DOM
		initCharts: function() {
			// Store reference
			var that = this,
			op = this.settings;

			// Show the containers
			this.jElement.find( '.ipt_fsqm_report_container' ).show();

			// Init Google Charts
			if ( hasChartLoaded ) {
				that.drawCharts();
			} else {
				google.charts.setOnLoadCallback( function() {
					hasChartLoaded = true;
					that.drawCharts();
				} );
			}

			// Show the printer
			this.printer.show().find('button.ipt_fsqm_report_print').on( 'click', function() {
				that.jElement.printElement({
					leaveOpen:true,
					printMode:'popup',
					pageTitle : document.title,
					printBodyOptions : {
						classNameToAdd : that.jElement.parents('.ipt_uif_common').attr('class'),
						styleToAdd : 'padding:10px;margin:10px;background: #fff none;color:#333;font-size:12px;'
					}
				});
			} );

			// Reinit Thickbox anchors
			if ( typeof( $.fn.iptPluginUIFAdmin ) == 'function' ) {
				this.jElement.iptPluginUIFAdmin( 'reinitTBAnchors' );
			}

			// Hide the progress bar
			this.progressBar.fadeOut('fast');

			// Set the loader to generating charts
			this.loader.find('.ipt_uif_ajax_loader_text').html( iptFSQMReport.charts );
		},

		// Actually draws the chart
		// Assumes the Google Charts JS is already loaded
		drawCharts: function() {
			// Hide ajax loader & progress bar
			this.loader.fadeOut('fast');

			// Reference variables
			var that = this,
			op = this.settings;

			// Init variables used
			var m_key, p_key, table_to_update, viz_table, g_data, viz_div, data_table, o_key, op_title, s_key, title, search, new_viz_table, r_key,
				i, c_key,tmp_arr, row, col, correct_order, order, chart_type, show_average, defaultOp, show_title, show_legend, new_viz_table_row, mdop_arr;

			// Loop through mcqs
			for ( m_key in op.survey.data ) {
				// Set some variables
				table_to_update = that.jElement.find('.ipt_fsqm_report_survey_' + m_key + ' table.table_to_update').eq(0);
				viz_table = table_to_update.find('td.visualization');
				data_table = table_to_update.find('td.data');

				// Some charting config
				show_title = that.getChartTitle( 'mcq', m_key );
				show_legend = that.getChartLegend( 'mcq', m_key );

				// Now check element type and do accordingly
				switch ( op.survey.elements[m_key].type ) {
					// Open up scope for third party extensibility
					default :
						if ( undefined !== iptFSQMReport.gcallbacks[op.survey.elements[m_key].type] && typeof( window[iptFSQMReport.gcallbacks[op.survey.elements[m_key].type]] ) == 'function' ) {
							window[iptFSQMReport.gcallbacks[op.survey.elements[m_key].type]].apply( that, [op.survey.elements[m_key], op.survey.data[m_key], m_key, table_to_update, data_table, op, show_title, show_legend] );
						}
						break;
					// Basic MCQs
					case 'radio' :
					case 'checkbox' :
					case 'select' :
					case 'thumbselect' :
						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						g_data = [];
						g_data[0] = [iptFSQMReport.g_data.op_label, iptFSQMReport.g_data.ct_label];
						for ( o_key in op.survey.data[m_key] ) {
							if ( undefined !== op.survey.data[m_key][o_key] ) {
								if ( o_key == 'others' && undefined !== op.survey.elements[m_key].settings.o_label && true === op.survey.elements[m_key].settings.others ) {
									g_data[g_data.length] = [op.survey.elements[m_key].settings.o_label.replace(/(<([^>]+)>)/ig,""), op.survey.data[m_key][o_key]];
								} else if ( undefined !== op.survey.elements[m_key].settings.options[o_key] ) {
									g_data[g_data.length] = [op.survey.elements[m_key].settings.options[o_key].label.replace(/(<([^>]+)>)/ig,""), op.survey.data[m_key][o_key]];
								}
							}
						}
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						// Check the chart type set (if any)
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );

						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}

						break;
					// Smileyrating
					case 'smileyrating' :
						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						g_data = [];
						g_data[0] = [iptFSQMReport.g_data.op_label, iptFSQMReport.g_data.ct_label];
						for ( s_key in op.survey.data[m_key] ) {
							if ( undefined !== op.survey.data[m_key][s_key] && s_key != 'feedback_data' ) {
								if ( op.survey.elements[m_key].settings.labels[s_key] !== undefined ) {
									g_data[g_data.length] = [op.survey.elements[m_key].settings.labels[s_key].replace(/(<([^>]+)>)/ig,""), op.survey.data[m_key][s_key]];
								}
							}
						}
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						// Check the chart type set (if any)
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}
						break;
					// LikeDislike
					case 'likedislike' :
						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						g_data = [];
						g_data[0] = [iptFSQMReport.g_data.op_label, iptFSQMReport.g_data.ct_label];
						for ( s_key in op.survey.data[m_key] ) {
							if ( undefined !== op.survey.data[m_key][s_key] && s_key != 'feedback_data' ) {
								if ( op.survey.elements[m_key].settings[s_key] !== undefined ) {
									g_data[g_data.length] = [op.survey.elements[m_key].settings[s_key].replace(/(<([^>]+)>)/ig,""), op.survey.data[m_key][s_key]];
								}
							}
						}
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						// Check the chart type set (if any)
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}
						break;
					// Sliders, Ranges and Grading
					case 'slider' :
						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						// Check the chart type set (if any)
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						show_average = that.getChartAverage( 'mcq', m_key, false );
						that.populateSliderChart( op.survey.data[m_key], op_title, 300, viz_div, chart_type, show_average, show_title, show_legend );
						break;
					case 'range' :
						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						that.populateRangeChart( op.survey.data[m_key], op_title, 300, viz_div, op.survey.elements[m_key].settings, show_title, show_legend );
						break;
					case 'grading' :
						// Check the chart type set (if any)
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						show_average = that.getChartAverage( 'mcq', m_key, false );
						for ( o_key in op.survey.data[m_key] ) {
							if ( undefined === op.survey.elements[m_key].settings.options[o_key] ) {
								continue;
							}
							title = typeof( op.survey.elements[m_key].settings.options[o_key]) === 'object' ? op.survey.elements[m_key].settings.options[o_key].label : op.survey.elements[m_key].settings.options[o_key];
							title = title.replace(/(<([^>]+)>)/ig,"");
							search = 'grading';
							new_viz_table = $('<td colspan=""></td>');
							if ( op.do_data ) {
								new_viz_table.attr( 'rowspan', table_to_update.find( 'tbody tr.' + search + '_' + o_key + '_value_to_count_tr' ).length + 2 );
								table_to_update.find( 'tbody tr.' + search + '_' + o_key + '.head' ).prepend( new_viz_table );
							} else {
								new_viz_table_row = $('<tr></tr>');
								new_viz_table_row.append( new_viz_table );
								table_to_update.find( 'tbody' ).append( new_viz_table_row );
							}

							viz_div = document.createElement('div');

							new_viz_table.append(viz_div);
							if ( op.survey.elements[m_key].settings.range === true ) {
								that.populateRangeChart( op.survey.data[m_key][o_key], title, 'auto', viz_div, op.survey.elements[m_key].settings, show_title, show_legend );
							} else {
								that.populateSliderChart( op.survey.data[m_key][o_key], title, 'auto', viz_div, chart_type, show_average, show_title, show_legend );
							}
						}
						break;
					// Spinners, Starrating & Scalerating
					case 'spinners' :
					case 'starrating' :
					case 'scalerating' :
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						for ( o_key in op.survey.data[m_key] ) {
							if ( undefined === op.survey.elements[m_key].settings.options[o_key] ) {
								continue;
							}
							title = op.survey.elements[m_key].settings.options[o_key];
							if ( typeof( title ) == 'object' ) {
								title = title.label;
							}
							title = title.replace(/(<([^>]+)>)/ig,"");
							search = op.survey.elements[m_key].type == 'spinners' ? 'spinners' : 'rating';
							new_viz_table = $('<td colspan=""></td>');
							if ( op.do_data ) {
								new_viz_table.attr( 'rowspan', table_to_update.find( 'tbody tr.' + search + '_' + o_key + '_value_to_count_tr' ).length + 2 );
								table_to_update.find( 'tbody tr.' + search + '_' + o_key + '.head' ).prepend( new_viz_table );
							} else {
								new_viz_table_row = $('<tr></tr>');
								new_viz_table_row.append( new_viz_table );
								table_to_update.find( 'tbody' ).append( new_viz_table_row );
							}

							viz_div = document.createElement('div');
							new_viz_table.append(viz_div);
							show_average = false;
							if ( op.survey.elements[m_key].type === 'spinners' ) {
								show_average = that.getChartAverage( 'mcq', m_key, false );
							}
							that.populateSliderChart( op.survey.data[m_key][o_key], title, 'auto', viz_div, chart_type, show_average, show_title, show_legend );
						}
						break;
					// Matrix Dropdown
					case 'matrix_dropdown' :
						// Create the options array
						mdop_arr = [ '' ];
						for ( i in op.survey.elements[m_key].settings.options ) {
							mdop_arr[ mdop_arr.length ] = op.survey.elements[m_key].settings.options[i].label.replace(/(<([^>]+)>)/ig,"");
						}

						// Loop through rows
						for ( r_key in op.survey.elements[ m_key ].settings.rows ) {
							// Init the g_data
							g_data = [];
							// Insert the options array
							g_data[0] = mdop_arr;

							// Create a dummy data if needed
							if ( undefined === op.survey.data[ m_key ][ r_key ] ) {
								op.survey.data[ m_key ][ r_key ] = [];
							}

							// Loop through columns and insert the data
							for ( c_key in op.survey.elements[ m_key ].settings.columns ) {
								// Create a dummy data if needed
								if ( undefined === op.survey.data[ m_key ][ r_key ][ c_key ] ) {
									op.survey.data[ m_key ][ r_key ][ c_key ] = [];
								}

								// Insert the title
								tmp_arr = [ op.survey.elements[ m_key ].settings.columns[ c_key ].replace(/(<([^>]+)>)/ig,"") ];

								// Insert the data
								for ( o_key in op.survey.elements[ m_key ].settings.options ) {
									tmp_arr[ tmp_arr.length ] = ( undefined === op.survey.data[ m_key ][ r_key ][ c_key ][ o_key ] ) ? 0 : op.survey.data[ m_key ][ r_key ][ c_key ][ o_key ];
								}
								g_data[ g_data.length ] = tmp_arr;
							}

							// Add the chart
							viz_div = document.createElement('div');
							viz_table.filter('.row-' + r_key).append(viz_div);
							op_title = op.survey.elements[m_key].settings.rows[r_key].replace(/(<([^>]+)>)/ig,"");
							// Check the chart type set (if any)
							chart_type = that.getChartType( 'mcq', m_key, 'pie' );
							switch ( chart_type ) {
								default:
								case 'bar':
								case 'sbar':
									if ( chart_type == 'sbar' ) {
										defaultOp = {
											isStacked: true
										};
									} else {
										defaultOp = {};
									}
									that.drawBarChart( viz_div, g_data, '', '', op_title, 300, defaultOp, show_title, show_legend );
									break;
								case 'column':
								case 'scolumn':
									if ( chart_type == 'scolumn' ) {
										defaultOp = {
											isStacked: true
										};
									} else {
										defaultOp = {};
									}
									that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, defaultOp, show_title, show_legend );
									break;
							}
						}
						break;
					// Matrix
					case 'matrix' :
						g_data = [];
						g_data[0] = [''];
						for ( i in op.survey.elements[m_key].settings.columns ) {
							g_data[0][g_data[0].length] = op.survey.elements[m_key].settings.columns[i].replace(/(<([^>]+)>)/ig,"");
						}

						// We interate through the actual element
						// And push data from the saved info
						for ( row in op.survey.elements[ m_key ].settings.rows ) {
							// If undefined in the data, then just create a blank
							if ( undefined === op.survey.data[ m_key ][ row ] ) {
								op.survey.data[ m_key ][ row ] = [];
							}
							// Store the row label
							tmp_arr = [ op.survey.elements[m_key].settings.rows[row].replace(/(<([^>]+)>)/ig,"") ];
							// Iterate through column
							for ( col in op.survey.elements[ m_key ].settings.columns ) {
								// If not present in data, then zero count
								if ( undefined === op.survey.data[ m_key ][ row ][ col ] ) {
									tmp_arr[ tmp_arr.length ] = 0;
								// Otherwise defined count
								} else {
									tmp_arr[ tmp_arr.length ] = parseFloat( op.survey.data[ m_key ][ row ][ col ] );
								}
							}
							// Push to g_data
							g_data[ g_data.length ] = tmp_arr;
						}

						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						// Check the chart type set (if any)
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'bar':
							case 'sbar':
								if ( chart_type == 'sbar' ) {
									defaultOp = {
										isStacked: true
									};
								} else {
									defaultOp = {};
								}
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, defaultOp, show_title, show_legend );
								break;
							case 'column':
							case 'scolumn':
								if ( chart_type == 'scolumn' ) {
									defaultOp = {
										isStacked: true
									};
								} else {
									defaultOp = {};
								}
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, defaultOp, show_title, show_legend );
								break;
						}
						break;
					// Toggle
					case 'toggle' :
						g_data = [];
						g_data[0] = [ iptFSQMReport.g_data.tg_label, iptFSQMReport.g_data.ct_label ];
						g_data[1] = [ op.survey.elements[m_key].settings.on, op.survey.data[m_key].on ];
						g_data[2] = [ op.survey.elements[m_key].settings.off, op.survey.data[m_key].off ];

						viz_div = document.createElement('div');
						viz_table.append(viz_div);

						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}
						break;
					// Sorting
					case 'sorting' :
						correct_order = [];
						for ( i in op.survey.elements[m_key].settings.options ) {
							correct_order[correct_order.length] = op.survey.elements[m_key].settings.options[i].label.replace(/(<([^>]+)>)/ig,"");
						}
						// First create the g_data for order type correct vs other
						g_data = [];
						g_data[0] = [ iptFSQMReport.g_data.tg_label, iptFSQMReport.g_data.ct_label ];
						// g_data[1] = ['Bogus', 0];
						g_data[1] = [iptFSQMReport.g_data.s_presets, {
								v : ( undefined !== op.survey.data[m_key].preset ? op.survey.data[m_key].preset : 0 ),
								f : ( undefined !== op.survey.data[m_key].preset ? op.survey.data[m_key].preset : 0 ) + "\n" + iptFSQMReport.g_data.s_order + "\n" + correct_order.join("\n")
						}];
						g_data[2] = [iptFSQMReport.g_data.s_others, {
								v : ( undefined !== op.survey.data[m_key].other ? op.survey.data[m_key].other : 0 ),
								f : ( undefined !== op.survey.data[m_key].other ? op.survey.data[m_key].other : 0 ) + "\n" + iptFSQMReport.g_data.s_order_custom
						}];

						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						op_title = op.survey.elements[m_key].title.replace(/(<([^>]+)>)/ig,"");

						// Draw the given chart
						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}

						// Now create another graph with different orders
						g_data = [];

						op.survey.data[m_key].orders.sort( function( a, b ) {
							return b - a;
						} );
						g_data[0] = [ iptFSQMReport.g_data.tg_label, iptFSQMReport.g_data.ct_label ];
						// g_data[1] = ['Bogus', 0];
						for ( order in op.survey.data[m_key].orders ) {
							s_key = order.split('-');
							title = [];
							for ( i in s_key ) {
								if ( undefined === op.survey.elements[m_key].settings.options[s_key[i]] ) {
									continue;
								}
								title[title.length] = op.survey.elements[m_key].settings.options[s_key[i]].label.replace(/(<([^>]+)>)/ig,"");
							}
							g_data[g_data.length] = [order, {
									v : op.survey.data[m_key].orders[order],
									f : op.survey.data[m_key].orders[order] + "\n" + iptFSQMReport.g_data.s_order + "\n" + title.join("\n")
							}];
						}

						viz_div = document.createElement('div');
						viz_table.append(viz_div);

						chart_type = that.getChartType( 'mcq', m_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, iptFSQMReport.g_data.s_breakdown, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', iptFSQMReport.g_data.s_breakdown, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', iptFSQMReport.g_data.s_breakdown, 300, {}, show_title, show_legend );
								break;
						}
						break;
				}
			} // end mcq loop

			// Loop through pinfos
			for ( p_key in op.pinfo.data ) {
				// Set some variables
				table_to_update = that.jElement.find('.ipt_fsqm_report_pinfo_' + p_key + ' table.table_to_update').eq(0);
				viz_table = table_to_update.find('td.visualization');
				data_table = table_to_update.find('td.data');

				// Some charting config
				show_title = that.getChartTitle( 'pinfo', p_key );
				show_legend = that.getChartLegend( 'pinfo', p_key );

				// Now check element type and do accordingly
				switch ( op.pinfo.elements[p_key].type ) {
					// Open up scope for third party extensibility
					default :
						if ( undefined !== iptFSQMReport.gcallbacks[op.pinfo.elements[p_key].type] && typeof( window[iptFSQMReport.gcallbacks[op.pinfo.elements[p_key].type]] ) == 'function' ) {
							window[iptFSQMReport.gcallbacks[op.pinfo.elements[p_key].type]].apply( that, [op.pinfo.elements[p_key], op.pinfo.data[p_key], p_key, table_to_update, data_table, op, show_title, show_legend] );
						}
						break;
					// Basic MCQs
					case 'p_radio' :
					case 'p_checkbox' :
					case 'p_select' :
						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						g_data = [];
						g_data[0] = [iptFSQMReport.g_data.op_label, iptFSQMReport.g_data.ct_label];
						for ( o_key in op.pinfo.data[p_key] ) {
							if ( undefined !== op.pinfo.data[p_key][o_key] ) {
								if ( o_key == 'others' && undefined !== op.pinfo.elements[p_key].settings.o_label && true === op.pinfo.elements[p_key].settings.others ) {
									g_data[g_data.length] = [op.pinfo.elements[p_key].settings.o_label.replace(/(<([^>]+)>)/ig,""), op.pinfo.data[p_key][o_key]];
								} else if ( undefined !== op.pinfo.elements[p_key].settings.options[o_key] ) {
									g_data[g_data.length] = [op.pinfo.elements[p_key].settings.options[o_key].label.replace(/(<([^>]+)>)/ig,""), op.pinfo.data[p_key][o_key]];
								}
							}
						}
						op_title = op.pinfo.elements[p_key].title.replace(/(<([^>]+)>)/ig,"");

						// Check the chart type set (if any)
						chart_type = that.getChartType( 'pinfo', p_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}
						break;

					// Single Checkbox
					case 's_checkbox' :
						g_data = [];
						g_data[0] = [ iptFSQMReport.g_data.sc_label, iptFSQMReport.g_data.ct_label ];
						g_data[1] = [ iptFSQMReport.g_data.scon_label, op.pinfo.data[p_key].checked ];
						g_data[2] = [ iptFSQMReport.g_data.scoff_label, op.pinfo.data[p_key].unchecked ];

						viz_div = document.createElement('div');
						viz_table.append(viz_div);

						op_title = op.pinfo.elements[p_key].title.replace(/(<([^>]+)>)/ig,"");

						// Check the chart type set (if any)
						chart_type = that.getChartType( 'pinfo', p_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}
						break;

					// Sorting
					case 'p_sorting' :
						correct_order = [];
						for ( i in op.pinfo.elements[p_key].settings.options ) {
							correct_order[correct_order.length] = op.pinfo.elements[p_key].settings.options[i].label.replace(/(<([^>]+)>)/ig,"");
						}
						// First create the g_data for order type correct vs other
						g_data = [];
						g_data[0] = [ iptFSQMReport.g_data.tg_label, iptFSQMReport.g_data.ct_label ];
						// g_data[1] = ['Bogus', 0];
						g_data[1] = [iptFSQMReport.g_data.s_presets, {
								v : ( undefined !== op.pinfo.data[p_key].preset ? op.pinfo.data[p_key].preset : 0 ),
								f : ( undefined !== op.pinfo.data[p_key].preset ? op.pinfo.data[p_key].preset : 0 ) + "\n" + iptFSQMReport.g_data.s_order + "\n" + correct_order.join("\n")
						}];
						g_data[2] = [iptFSQMReport.g_data.s_others, {
								v : ( undefined !== op.pinfo.data[p_key].other ? op.pinfo.data[p_key].other : 0 ),
								f : ( undefined !== op.pinfo.data[p_key].other ? op.pinfo.data[p_key].other : 0 ) + "\n" + iptFSQMReport.g_data.s_order_custom
						}];

						viz_div = document.createElement('div');
						viz_table.append(viz_div);
						op_title = op.pinfo.elements[p_key].title.replace(/(<([^>]+)>)/ig,"");

						// Draw the given chart
						chart_type = that.getChartType( 'pinfo', p_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, op_title, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', op_title, 300, {}, show_title, show_legend );
								break;
						}

						// Now create another graph with different orders
						g_data = [];

						op.pinfo.data[p_key].orders.sort( function( a, b ) {
							return b - a;
						} );
						g_data[0] = [ iptFSQMReport.g_data.tg_label, iptFSQMReport.g_data.ct_label ];
						// g_data[1] = ['Bogus', 0];
						for ( order in op.pinfo.data[p_key].orders ) {
							s_key = order.split('-');
							title = [];
							for ( i in s_key ) {
								if ( undefined === op.pinfo.elements[p_key].settings.options[s_key[i]] ) {
									continue;
								}
								title[title.length] = op.pinfo.elements[p_key].settings.options[s_key[i]].label.replace(/(<([^>]+)>)/ig,"");
							}
							g_data[g_data.length] = [order, {
									v : op.pinfo.data[p_key].orders[order],
									f : op.pinfo.data[p_key].orders[order] + "\n" + iptFSQMReport.g_data.s_order + "\n" + title.join("\n")
							}];
						}

						viz_div = document.createElement('div');
						viz_table.append(viz_div);

						chart_type = that.getChartType( 'pinfo', p_key, 'pie' );
						switch ( chart_type ) {
							default:
							case 'pie':
								that.drawPieChart( viz_div, g_data, iptFSQMReport.g_data.s_breakdown, {}, show_title, show_legend );
								break;
							case 'bar':
								that.drawBarChart( viz_div, g_data, '', '', iptFSQMReport.g_data.s_breakdown, 300, {}, show_title, show_legend );
								break;
							case 'column':
								that.drawColumnChart( viz_div, g_data, '', '', iptFSQMReport.g_data.s_breakdown, 300, {}, show_title, show_legend );
								break;
						}
						break;
				}
			} // end pinfo loop

			// That's it. We do not need anything for feedback right now
			// Infact at any time for that matter. Feedback elements are
			// not supposed to have charts and we do not intend to change it!!!!
		},

		// Get chart type from definition
		// Or simply return the default
		getChartType: function( etype, ekey, dchart ) {
			var chart_type;
			try {
				chart_type = this.settings.cmeta[ etype ].charttype[ ekey ];
			} catch ( e ) {
				chart_type = dchart;
			}
			if ( undefined === chart_type ) {
				chart_type = dchart;
			}
			return chart_type;
		},

		// Get show average variable from definition
		// Or simply return the default
		getChartAverage: function( etype, ekey ) {
			return this._getChartToggles( etype, ekey, 'average' );
		},

		// Get the chart title variable from definition
		getChartTitle: function( etype, ekey ) {
			return this._getChartToggles( etype, ekey, 'title' );
		},

		// Get the chart legend variable from definition
		getChartLegend: function( etype, ekey ) {
			return this._getChartToggles( etype, ekey, 'legend' );
		},

		/**
		 * Gets the chart toggles from settings
		 *
		 * @private
		 *
		 * @param      {string}   etype   The etype
		 * @param      {string}   ekey    The ekey
		 * @param      {string}   tkey    The tkey
		 * @return     {boolean}   The chart toggle true or false
		 */
		_getChartToggles: function( etype, ekey, tkey ) {
			var rvalue;
			try {
				rvalue = this.settings.cmeta[ etype ].toggles[ ekey ][ tkey ];
			} catch ( e ) {
				rvalue = false;
			}
			if ( undefined === rvalue ) {
				rvalue = false;
			}
			return !!rvalue;
		},

		// Some Internal helpers
		// Calculate object length of an object
		objectLength: function( obj ) {
			if ( typeof(obj) !== 'object' ) {
				return false;
			}
			var count = 0, i;
			for ( i in obj ) {
				if ( obj.hasOwnProperty( i ) ) {
					count++;
				}
			}
			return count;
		},

		// Get number with formatting
		numberFormat: function( num ) {
			var number = parseFloat( num );
			if ( isNaN( number ) ) {
				return num;
			} else {
				return ( parseInt( num * 100, 10 ) / 100 );
			}
		},

		// Append to table in position for minimum and maximum (usually for slider, range etc)
		appendToTableInPositionForMinMax : function( to_append, table, append_to, append_after, search_class, position, min, max, step ) {
			if ( ! position.length ) {
				if ( append_after === null ) {
					append_to.append(to_append);
				} else {
				   append_after.after(to_append);
				}
			   return;
			}
			var position_min = parseFloat(position[0]),
			position_max = parseFloat(position[1]),
			i, j, possible_chunk_after, k, l, possible_chunk_before;
			min = parseFloat(min);
			max = parseFloat(max);
			step = parseFloat(step);

			if ( table.find('tbody tr td.' + search_class).length && !( isNaN(position_min) || isNaN(position_max) || isNaN(min) || isNaN(max) || isNaN(step) ) ) {
				//First search for possible minimum
				for ( i = position_min, j = position_min; i >= min || j <= max; i = i - step, j = j + step) {
					possible_chunk_after = table.find('td[data-value-min="' + i + '"].' + search_class);
					if ( possible_chunk_after.length ) {
						//Here we have to put after the td whose max value is just lesser than the current position_max
						//Or before the td whose max value is just greater than the current position_max
						for ( k = position_max, l = position_max; k >= min || l <= max; k = k - step, l = l + step ) {
							//This is to put just after
							if ( possible_chunk_after.filter('td[data-value-max="' + k + '"].' + search_class).length ) {
								possible_chunk_after.filter('td[data-value-max="' + k + '"].' + search_class).parent().after(to_append);
								return;
							}

							//This is to put just before
							if ( possible_chunk_after.filter('td[data-value-max="' + l + '"].' + search_class).length ) {
								possible_chunk_after.filter('td[data-value-max="' + l + '"].' + search_class).parent().before(to_append);
								return;
							}
						}
					}

					possible_chunk_before = table.find('td[data-value-min="' + j + '"].' + search_class);
					if ( possible_chunk_before.length ) {
						for ( k = position_max, l = position_max; k >= min || l <= max; k = k - step, l = l + step ) {
							//This is to put just after
							if ( possible_chunk_before.filter('td[data-value-max="' + k + '"].' + search_class).length ) {
								possible_chunk_before.filter('td[data-value-max="' + k + '"].' + search_class).parent().after(to_append);
								return;
							}

							//This is to put just before
							if ( possible_chunk_before.filter('td[data-value-max="' + l + '"].' + search_class).length ) {
								possible_chunk_before.filter('td[data-value-max="' + l + '"].' + search_class).parent().before(to_append);
								return;
							}
						}
					}
				}
			}

			if ( append_after === null || append_after === undefined ) {
				append_to.append(to_append);
			} else {
				append_after.after(to_append);
			}
		},

		// Append to table in position for value ( for basic freetype elements )
		appendToTableInPositionForVal : function( to_append, table, append_to, append_after, search_class, position, min, max, step ) {
			position = parseFloat(position);
			min = parseFloat(min);
			max = parseFloat(max);
			step = parseFloat(step);

			var i, j, possible_after, possible_before;

			if ( table.find('tbody tr td.' + search_class).length && !(isNaN(position) || isNaN(min) || isNaN(max) || isNaN(step)) ) {
				for ( i = position, j = position; i >= min || j <= max; i = i - step, j = j + step ) {
					possible_after = table.find('td[data-value="' + i + '"].' + search_class);
					if ( possible_after.length ) {
						possible_after.parent().after(to_append);
						return;
					}
					possible_before = table.find('td[data-value="' + j + '"].' + search_class);
					if ( possible_before.length ) {
						possible_before.parent().before(to_append);
						return;
					}
				}
			}

			if ( append_after === null || append_after === undefined ) {
				append_to.append(to_append);
			} else {
				append_after.after(to_append);
			}
		},

		// Helper function to updatecount
		updateCount : function( to_update, val ) {
			to_update.html(val);
		},

		// Helper for calculating some elements

		// Calculate for rating elements
		calculateRating : function( values, data, data_table, append_to, append_after, tr_class, td_class, avg_update, settings ) {
			var avg_total = 0,
			avg_count = 0,
			value, td_count_to_update, rating_img, i, a, to_append, avg_img, avg_to_check;

			for ( value in values ) {
				if ( !isNaN(values[value]) ) {
					avg_total += value * values[value];
					avg_count += values[value];

					if ( undefined === data[value] ) {
						data[value] = values[value];
					} else {
						data[value] += values[value];
					}

					if ( this.settings.do_data ) {
						td_count_to_update = data_table.find('td[data-value="' + value + '"].' + td_class);
						if ( !td_count_to_update.length ) {
							rating_img = '';
							for( i = 1; i <= settings.max; i++ ) {
								if ( i <= value ) {
									rating_img += iptFSQMReport.rating_img_full;
								} else {
									rating_img += iptFSQMReport.rating_img_empty;
								}
							}
							to_append = '<tr class="' + tr_class + '"><th>' + rating_img + '</th><td class="' + td_class + '" data-value="' + value + '">' +  data[value] + '</td></tr>';
							this.appendToTableInPositionForVal( to_append, data_table, append_to, append_after, td_class, value, settings.min, settings.max, settings.step );
						} else {
							td_count_to_update.text(data[value]);
						}
					}
				}
			}

			if ( undefined === data.average_meta ) {
				data.average_meta = {
					total : avg_total,
					count : avg_count
				};
			} else {
				data.average_meta.total += avg_total;
				data.average_meta.count += avg_count;
			}

			data.average = this.numberFormat( data.average_meta.total / data.average_meta.count );

			if ( this.settings.do_data ) {
				avg_img = '';
				avg_to_check = Math.floor( data.average );

				for ( a = 1; a <= settings.max; a++ ) {
					if ( avg_to_check >= a ) {
						avg_img += iptFSQMReport.rating_img_full;
					} else {
						//Check for the crossing point
						if(avg_to_check + 1 === a) {
							if(a === Math.round(data.average)) {
								avg_img += iptFSQMReport.rating_img_half;
							} else {
								avg_img += iptFSQMReport.rating_img_empty;
							}
						} else {
							avg_img += iptFSQMReport.rating_img_empty;
						}
					}
				}
				this.updateCount(avg_update.find('span.avg'), data.average);
				this.updateCount(avg_update.find('span.avg_img'), avg_img);
				this.updateCount(avg_update.find('span.avg_count'), data.average_meta.count);
			}
		},

		// Calculate for slider elements
		calculateSlider : function( values, data, data_table, append_to, append_after, tr_class, td_class, avg_update, settings ) {
			var avg_total = 0,
			avg_count = 0,
			value, td_count_to_update, to_append;
			if ( undefined === settings.prefix ) {
				settings.prefix = '';
			}
			if ( undefined === settings.suffix ) {
				settings.suffix = '';
			}
			for ( value in values ) {
				if ( !isNaN( values[value] ) ) {
					avg_total += value * values[value];
					avg_count += values[value];

					if ( undefined === data[value] ) {
						data[value] = values[value];
					} else {
						data[value] += values[value];
					}

					if ( this.settings.do_data ) {
						td_count_to_update = data_table.find('td[data-value="' + value + '"].' + td_class);
						if ( !td_count_to_update.length ) {
							to_append = '<tr class="' + tr_class + '"><th>' + settings.prefix + value + settings.suffix + '</th><td class="' + td_class + '" data-value="' + value + '">' +  data[value] + '</td></tr>';
							this.appendToTableInPositionForVal( to_append, data_table, append_to, append_after, td_class, value, settings.min, settings.max, settings.step );
						} else {
							td_count_to_update.text(data[value]);
						}
					}
				}
			}

			if ( undefined === data.average_meta ) {
				data.average_meta = {
					total : avg_total,
					count : avg_count
				};
			} else {
				data.average_meta.total += avg_total;
				data.average_meta.count += avg_count;
			}
			data.average = this.numberFormat(data.average_meta.total / data.average_meta.count);
			if ( this.settings.do_data ) {
				this.updateCount(avg_update.find('span.avg'), settings.prefix + data.average + settings.suffix);
				this.updateCount(avg_update.find('span.avg_count'), data.average_meta.count);
			}
		},

		// Calculate for range element
		calculateRange : function( values_delimited, data, data_table, append_to, append_after, tr_class, td_class, avg_update, settings ) {
			var avg_total_min = 0,
			avg_total_max = 0,
			avg_count = 0,
			values, value, difference, data_key, td_count_to_update, to_append, th_label;
			if ( undefined === settings.prefix ) {
				settings.prefix = '';
			}
			if ( undefined === settings.suffix ) {
				settings.suffix = '';
			}
			for ( values in values_delimited ) {
				if ( !isNaN(values_delimited[values]) ) {
					value = values.split(',');
					avg_count += values_delimited[values];
					avg_total_min += value[0] * values_delimited[values];
					avg_total_max += value[1] * values_delimited[values];
					data_key = value[0] + iptFSQMReport.range_text + value[1];
					th_label = settings.prefix + value[0] + settings.suffix + iptFSQMReport.range_text + settings.prefix + value[1] + settings.suffix;
					if ( undefined === data[data_key] ) {
						data[data_key] = values_delimited[values];
					} else {
						data[data_key] += values_delimited[values];
					}

					if ( this.settings.do_data ) {
						td_count_to_update = data_table.find('td[data-value-min="' + value[0] + '"][data-value-max="' + value[1] + '"].' + td_class);
						if ( !td_count_to_update.length ) {
							to_append = '<tr class="' + tr_class + '"><th>' + th_label + '</th><td class="'+ td_class + '" data-value-min="' + value[0] + '" data-value-max="' + value[1] + '">' +  data[data_key] + '</td></tr>';
							this.appendToTableInPositionForMinMax( to_append, data_table, append_to, append_after, td_class, value, settings.min, settings.max, settings.step );
						} else {
							td_count_to_update.text(data[data_key]);
						}
					}
				}
			}


			if ( undefined === data.average_meta ) {
				data.average_meta = {
					total_min : avg_total_min,
					total_max : avg_total_max,
					count : avg_count
				};
			} else {
				data.average_meta.total_min += avg_total_min;
				data.average_meta.total_max += avg_total_max;
				data.average_meta.count += avg_count;
			}
			data.average = settings.prefix + this.numberFormat(data.average_meta.total_min / data.average_meta.count) + settings.suffix + iptFSQMReport.range_text + settings.prefix + this.numberFormat(data.average_meta.total_max / data.average_meta.count) + settings.suffix;

			if ( this.settings.do_data ) {
				this.updateCount( avg_update.find('span.avg'), data.average );
				this.updateCount( avg_update.find('span.avg_count'), data.average_meta.count );
			}
		},

		// Chart related functions

		// Populate slider chart
		populateSliderChart : function( data, title, height, viz_div, chart_type, show_average, show_title, show_legend ) {
			var slider_keys = [], val, heading, i, defaultOp;

			for ( val in data ) {
				if ( !isNaN(val) )
					slider_keys[slider_keys.length] = parseFloat(val);
			}

			// Set the default chart_type
			chart_type = undefined === chart_type ? 'bar' : chart_type;

			// Sort the array
			slider_keys.sort( function( a, b ) {
				// Sort in increasing order for all
				// But in case of column, sort in decreasing
				if ( show_average === false && chart_type !== 'column' ) {
					return b - a;
				}
				return a - b;
			} );

			var g_data = [];
			// We add value vs count
			// First member is a value
			// Then it is total number of entries/count
			// In the end we insert average, if needed
			g_data[ g_data.length ] = [ iptFSQMReport.g_data.sl_label, iptFSQMReport.g_data.en_label ];

			for ( val in slider_keys ) {
				// Here we convert the first var (value) to string
				// So that GoogleCharts does not mess it up
				// But we do that only in case the chart is not area or average line is not shown (i.e, combo)
				g_data[ g_data.length ] = [ ( ( show_average === false && chart_type !== 'area' ) ? '' : 0 ) + slider_keys[ val ], data[ slider_keys[ val ] ] ];
			}



			// But if average is present, then we have to show
			// A combo chart
			if ( show_average ) {
				for ( i = 0; i < g_data.length; i++ ) {
					// 0 -> value, 1 -> count
					if ( 0 === i ) {
						g_data[ i ][ g_data[ i ].length ] = iptFSQMReport.g_data.perv;
						g_data[ i ][ g_data[ i ].length ] = iptFSQMReport.g_data.perc;
					} else {
						g_data[ i ][ g_data[ i ].length ] = this.numberFormat( ( ( g_data[ i ][0] * g_data[ i ][1] ) / data.average_meta.total ) * 100 );
						g_data[ i ][ g_data[ i ].length ] = this.numberFormat( ( g_data[ i ][1] / data.average_meta.count ) * 100 );
					}
				}
				i = g_data[0].length - 2;
				defaultOp = {
					seriesType: 'bars',
					series: {}
				};
				defaultOp.series[ i ] = {type: 'line', format: 'percent'};
				defaultOp.series[ (i - 1) ] = {type: 'line', format: 'percent'};
				this.drawComboChart( viz_div, g_data, iptFSQMReport.g_data.sl_label, iptFSQMReport.g_data.en_label, title, height, defaultOp, show_title, show_legend );
			// Otherwise just the default charts
			} else {
				defaultOp = {
					hAxis: {
						baseline: 'automatic'
					}
				};
				switch ( chart_type ) {
					default:
					case 'bar':
						this.drawBarChart( viz_div, g_data, iptFSQMReport.g_data.en_label, iptFSQMReport.g_data.sl_label, title, height, defaultOp, show_title, show_legend );
						break;
					case 'column':
						this.drawColumnChart( viz_div, g_data, iptFSQMReport.g_data.en_label, iptFSQMReport.g_data.sl_label, title, height, defaultOp, show_title, show_legend );
						break;
					case 'area':
						this.drawAreaChart( viz_div, g_data, iptFSQMReport.g_data.en_label, iptFSQMReport.g_data.sl_label, title, height, defaultOp, show_title, show_legend );
						break;
				}
			}

		},

		// Populate range chart
		populateRangeChart : function( data, title, height, viz_div, settings, show_title, show_legend ) {
			var range_keys = [], range, ranges;
			for( range in data ) {
				ranges = range.split(iptFSQMReport.range_text);
				if( ranges.length != 2 ) {
					continue;
				}
				range_keys[range_keys.length] = ranges;
			}
			range_keys.sort( function( a, b ) {
				if ( a[0] > b[0] ) {
					return -1;
				} else if (a[0] < b[0]) {
					return 1;
				} else {
					if ( a[1] > b[1] ) {
						return -1;
					} else if ( a[1] < b[1] ) {
						return 1;
					} else {
						return 0;
					}
				}
			} );

			var g_data = [], val, key, heading;

			for ( val in range_keys ) {
				key = range_keys[val].join(iptFSQMReport.range_text);
				heading = data[key] + ' ' + ( data[key] == 1 ? iptFSQMReport.g_data.sl_head_label_s : iptFSQMReport.g_data.sl_head_label_p );
				g_data[g_data.length] = [heading, parseFloat(settings.min), parseFloat(range_keys[val][0]), parseFloat(range_keys[val][1]), parseFloat(settings.max)];
			}

			this.drawCandlestickChart( viz_div, g_data, iptFSQMReport.g_data.sg_label, iptFSQMReport.g_data.ct_label, title, height, settings, {}, show_title, show_legend );
		},

		// Draw Pie Chart
		drawPieChart: function( viz_div, g_data, title, defaultOp, show_title, show_legend ) {
			show_title = undefined === show_title ? true : show_title;
			show_legend = undefined === show_legend ? true : show_legend;
			var data = google.visualization.arrayToDataTable(g_data),
			chart = new google.visualization.PieChart(viz_div),
			options = {
				title : title,
				is3D : true,
				height : 300,
				backgroundColor : 'transparent',
				legend : {position : 'bottom'},
				tooltip : {isHTML : true}
			};

			if ( undefined === defaultOp ) {
				defaultOp = {};
			}

			options = $.extend( {}, defaultOp, options );

			// Hide title and legend if config says so
			if ( false === show_title ) {
				options.title = undefined;
			}
			if ( false === show_legend ) {
				options.legend.position = 'none';
			}

			chart.draw( data, options );
			$(window).resize( $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.PieChart(viz_div);
					chart.draw( data, options );
				}, 200 );
			} ) );

			$(document).on( 'fsqm.chartRedraw', $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.PieChart(viz_div);
					chart.draw( data, options );
				}, 200 );
			} ) );
		},

		// Draw Area Chart
		drawAreaChart: function( viz_div, g_data, htitle, vtitle, title, height, defaultOp, show_title, show_legend ) {
			show_title = undefined === show_title ? true : show_title;
			show_legend = undefined === show_legend ? true : show_legend;
			var data = google.visualization.arrayToDataTable(g_data),
			chart = new google.visualization.AreaChart(viz_div),
			options = {
				hAxis : {
					title : htitle,
					baseline : 0,
					gridlines: {
						count: -1
					}
				},
				vAxis : {
					title : vtitle,
					baseline: 0,
					gridlines: {
						count: -1
					}
				},
				title : title,
				backgroundColor : 'transparent',
				height : height,
				tooltip : {isHTML : true},
				legend: {position: 'bottom'}
			};
			if ( undefined === defaultOp ) {
				defaultOp = {};
			}
			options = $.extend( {}, defaultOp, options );

			// Hide title and legend if config says so
			if ( false === show_title ) {
				options.title = undefined;
			}
			if ( false === show_legend ) {
				options.legend.position = 'none';
				options.axisTitlesPosition = 'none';
				options.hAxis.textPosition = 'none';
				options.vAxis.textPosition = 'none';
				options.hAxis.gridlines.color = 'none';
				options.vAxis.gridlines.color = 'none';
			}

			chart.draw( data, options );
			$(window).resize( $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.AreaChart(viz_div);
					chart.draw( data, options );
				}, 200 );
			} ) );

			$(document).on( 'fsqm.chartRedraw', $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.AreaChart(viz_div);
					chart.draw( data, options );
				}, 200 );
			} ) );
		},

		// Draw Candle Stick Chart
		drawCandlestickChart: function( viz_div, g_data, htitle, vtitle, title, height, settings, defaultOp, show_title, show_legend ) {
			show_title = undefined === show_title ? true : show_title;
			show_legend = undefined === show_legend ? true : show_legend;
			var data = google.visualization.arrayToDataTable(g_data, true),
			chart = new google.visualization.CandlestickChart(viz_div),
			options = {
				hAxis : {
					title : htitle,
					gridlines: {
						count: -1
					}
				},
				vAxis : {
					title : vtitle,
					minValue : parseFloat(settings.min),
					maxValue : parseFloat(settings.max),
					viewWindow : {
						max : parseFloat(settings.max),
						min :  parseFloat(settings.min)
					},
					gridlines: {
						count: -1
					}
				},
				title : title,
				backgroundColor : 'transparent',
				height : height,
				tooltip : {isHTML : true},
				legend: {position: 'none'}
			};
			if ( undefined === defaultOp ) {
				defaultOp = {};
			}
			options = $.extend( {}, defaultOp, options );

			// Hide title and legend if config says so
			if ( false === show_title ) {
				options.title = undefined;
			}
			if ( false === show_legend ) {
				options.legend.position = 'none';
				options.axisTitlesPosition = 'none';
				options.hAxis.textPosition = 'none';
				options.vAxis.textPosition = 'none';
				options.hAxis.gridlines.color = 'none';
				options.vAxis.gridlines.color = 'none';
			}

			chart.draw( data, options );
			$(window).resize( $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.CandlestickChart(viz_div);
					chart.draw( data, options );
				}, 200 );
			} ) );
			$(document).on( 'fsqm.chartRedraw', $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.CandlestickChart(viz_div);
					chart.draw( data, options );
				}, 200 );
			} ) );
		},

		// Draw Bar Chart
		drawBarChart : function( viz_div, g_data, htitle, vtitle, title, height, defaultOp, show_title, show_legend ) {
			show_title = undefined === show_title ? true : show_title;
			show_legend = undefined === show_legend ? true : show_legend;
			var that = this;
			var data = google.visualization.arrayToDataTable(g_data),
			chart = that.settings.material ?  new google.charts.Bar( viz_div ) : new google.visualization.BarChart( viz_div ),
			options = {
				hAxis : {
					title : htitle,
					baseline : 0,
					gridlines: {
						count: -1
					}
				},
				vAxis : {
					title : vtitle,
					gridlines: {
						count: -1
					}
				},
				title : title,
				backgroundColor : 'transparent',
				height : height,
				tooltip : {isHTML : true},
				legend: {position: 'bottom'}
			};
			if ( that.settings.material ) {
				options.bars = 'horizontal';
			}
			if ( undefined === defaultOp ) {
				defaultOp = {};
			}
			options = $.extend( {}, defaultOp, options );

			// Hide title and legend if config says so
			if ( false === show_title ) {
				options.title = undefined;
			}
			if ( false === show_legend ) {
				options.legend.position = 'none';
				options.axisTitlesPosition = 'none';
				options.hAxis.textPosition = 'none';
				options.vAxis.textPosition = 'none';
				options.hAxis.gridlines.color = 'none';
				options.vAxis.gridlines.color = 'none';
			}

			if ( that.settings.material ) {
				chart.draw( data, google.charts.Bar.convertOptions( options ) );
			} else {
				chart.draw( data, options );
			}

			var reDrawCharts = function() {
				chart = that.settings.material ? new google.charts.Bar( viz_div ) : new google.visualization.BarChart( viz_div );
				if ( that.settings.material ) {
					chart.draw( data, google.charts.Bar.convertOptions( options ) );
				} else {
					chart.draw( data, options );
				}
			};

			$(window).resize( $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( reDrawCharts, 200 );
			} ) );
			$(document).on( 'fsqm.chartRedraw', $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( reDrawCharts, 200 );
			} ) );
		},

		drawColumnChart : function( viz_div, g_data, htitle, vtitle, title, height, defaultOp, show_title, show_legend ) {
			show_title = undefined === show_title ? true : show_title;
			show_legend = undefined === show_legend ? true : show_legend;
			var that = this;
			var data = google.visualization.arrayToDataTable(g_data),
			chart = that.settings.material ? new google.charts.Bar( viz_div ) : new google.visualization.ColumnChart( viz_div ),
			options = {
				hAxis : {
					title : htitle,
					gridlines: {
						count: -1
					}
				},
				vAxis : {
					title : vtitle,
					baseline : 0,
					gridlines: {
						count: -1
					}
				},
				title : title,
				backgroundColor : 'transparent',
				height : height,
				tooltip : {isHTML : true},
				legend: {position: 'bottom'}
			};
			if ( undefined === defaultOp ) {
				defaultOp = {};
			}
			options = $.extend( {}, defaultOp, options );

			// Hide title and legend if config says so
			if ( false === show_title ) {
				options.title = undefined;
			}
			if ( false === show_legend ) {
				options.legend.position = 'none';
				options.axisTitlesPosition = 'none';
				options.hAxis.textPosition = 'none';
				options.vAxis.textPosition = 'none';
				options.hAxis.gridlines.color = 'none';
				options.vAxis.gridlines.color = 'none';
			}

			if ( that.settings.material ) {
				chart.draw( data, google.charts.Bar.convertOptions( options ) );
			} else {
				chart.draw( data, options );
			}

			var reDrawCharts = function() {
				chart = that.settings.material ? new google.charts.Bar( viz_div ) : new google.visualization.ColumnChart( viz_div );
				if ( that.settings.material ) {
					chart.draw( data, google.charts.Bar.convertOptions( options ) );
				} else {
					chart.draw( data, options );
				}
			};

			$(window).resize( $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( reDrawCharts, 200 );
			} ) );
			$(document).on( 'fsqm.chartRedraw', $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( reDrawCharts, 200 );
			} ) );
		},

		// Draw Combo Charts
		// Mainly for showing average lines
		drawComboChart: function( viz_div, g_data, htitle, vtitle, title, height, defaultOp, show_title, show_legend ) {
			show_title = undefined === show_title ? true : show_title;
			show_legend = undefined === show_legend ? true : show_legend;
			var that = this,
			data = google.visualization.arrayToDataTable( g_data ),
			chart = new google.visualization.ComboChart( viz_div ),
			options = {
				title : title,
				vAxis: {
					title: vtitle,
					gridlines: {
						count: -1
					}
				},
				hAxis: {
					title: htitle,
					gridlines: {
						count: -1
					}
				},
				backgroundColor : 'transparent',
				height : height,
				tooltip : {isHTML : true},
				legend: {position: 'bottom'}
			};

			if ( undefined === defaultOp ) {
				defaultOp = {};
			}
			options = $.extend( {}, defaultOp, options );

			// Hide title and legend if config says so
			if ( false === show_title ) {
				options.title = undefined;
			}
			if ( false === show_legend ) {
				options.legend.position = 'none';
				options.axisTitlesPosition = 'none';
				options.hAxis.textPosition = 'none';
				options.vAxis.textPosition = 'none';
				options.hAxis.gridlines.color = 'none';
				options.vAxis.gridlines.color = 'none';
			}

			chart.draw( data, options );

			$(window).resize( $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.ComboChart( viz_div );
					chart.draw( data, options );
				}, 200 );
			} ) );

			$(document).on( 'fsqm.chartRedraw', $.debounce( 250, function() {
				$(viz_div).html('');
				setTimeout( function() {
					chart = new google.visualization.ComboChart( viz_div );
					chart.draw( data, options );
				}, 200 );
			} ) );
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
