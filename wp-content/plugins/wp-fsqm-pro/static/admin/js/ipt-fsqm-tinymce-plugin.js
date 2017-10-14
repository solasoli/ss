(function() {
	"use strict";

	tinymce.PluginManager.add( 'iptFSQMv3', function( editor, url ) {
		var menus = [], i, len, forms, themes, trendsWizardBody;

		forms = iptFSQMTMMenu.forms;
		themes = iptFSQMTMMenu.themes;

		// Prepopulate the trends wizard-1 body
		trendsWizardBody = [];
		// Add the form
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'listbox',
			name: 'fsqmFormID',
			label: iptFSQMTML10n.l10n.ifl,
			values: forms
		};
		// Server load
		trendsWizardBody[ trendsWizardBody.length ] = {
			type   : 'listbox',
			name   : 'fsqmTSL',
			label  : iptFSQMTML10n.l10n.itvsl,
			values : iptFSQMTML10n.l10n.itvsllb,
			value: '1'
		};
		// Visualization Column
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'textbox',
			name: 'fsqmTVC',
			label: iptFSQMTML10n.l10n.itvc,
			value: iptFSQMTML10n.l10n.itvcv
		};

		// Report Type
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'spacer'
		};
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'container',
			html: '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.twb1.rt + '</h2>'
		};
		// Loop through all report types and add checkboxes
		for ( i in iptFSQMTMMenu.rtype ) {
			trendsWizardBody[ trendsWizardBody.length ] = iptFSQMTMMenu.rtype[ i ];
		}
		// Report Data Customization
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'spacer'
		};
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'container',
			html: '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.twb1.rdc + '</h2>'
		};
		// Loop through all report data and add checkboxes
		for ( i in iptFSQMTMMenu.rdata ) {
			trendsWizardBody[ trendsWizardBody.length ] = iptFSQMTMMenu.rdata[ i ];
		}
		// Report Appearance
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'spacer'
		};
		trendsWizardBody[ trendsWizardBody.length ] = {
			type: 'container',
			html: '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.twb1.ra + '</h2>'
		};
		// Loop through all report data and add checkboxes
		for ( i in iptFSQMTMMenu.rappe ) {
			trendsWizardBody[ trendsWizardBody.length ] = iptFSQMTMMenu.rappe[ i ];
		}

		// Populate the menu
		// with built in objects
		menus = [
			// Insert System Shortcode
			{
				text: iptFSQMTML10n.l10n.ss.ss,
				icon: 'icon ipt-icomoon-cog',
				menu: [
					// User Portal
					{
						text: iptFSQMTML10n.l10n.ss.up,
						icon: 'icon ipt-icomoon-user',
						onclick: function() {
							var body = [], i, height = jQuery(window).height(), width = jQuery(window).width();
							var win = editor.windowManager.open({
								title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.ss.up,
								height: ( height - 200 ),
								width: ( width < 900 ) ? ( width - 50 ) : 800,
								classes: 'ipt-fsqm-panel',
								autoScroll: true,
								body: [
									{
										type   : 'container',
										html   : '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.ss.uplabels.llogin_attr + '</h2>'
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPlogin',
										label  : iptFSQMTML10n.l10n.ss.uplabels.login_attr.login,
										value  : iptFSQMTML10n.l10n.ss.updefaults.login
									},
									{
										type   : 'checkbox',
										name   : 'fsqmUPshow_register',
										text   : iptFSQMTML10n.l10n.ss.uplabels.login_attr.show_register,
										checked : true
									},
									{
										type   : 'checkbox',
										name   : 'fsqmUPshow_forgot',
										text   : iptFSQMTML10n.l10n.ss.uplabels.login_attr.show_forgot,
										checked : true
									},
									{
										type   : 'container',
										html   : '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.ss.uplabels.lportal_attr + '</h2>'
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPtitle',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.title,
										value  : iptFSQMTML10n.l10n.ss.updefaults.title
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPcontent',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.content,
										value  : iptFSQMTML10n.l10n.ss.updefaults.content,
										tooltip: iptFSQMTML10n.l10n.ss.uplabels.portal_attr.contenttt,
										multiline: true,
										minHeight: 50
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPnosubmission',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.nosubmission,
										value  : iptFSQMTML10n.l10n.ss.updefaults.nosubmission
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPformlabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.formlabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.formlabel
									},
									{
										type   : 'checkbox',
										name   : 'fsqmUPfilters',
										text   : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.filters,
										checked: true,
										tooltip: iptFSQMTML10n.l10n.ss.uplabels.portal_attr.filterstt
									},
									{
										type   : 'checkbox',
										name   : 'fsqmUPshowcategory',
										text   : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.showcategory,
										checked : true
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPcategorylabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.categorylabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.categorylabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPdatelabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.datelabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.datelabel
									},
									{
										type   : 'checkbox',
										name   : 'fsqmUPshowscore',
										text   : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.showscore,
										checked : true,
										tooltip: iptFSQMTML10n.l10n.ss.uplabels.portal_attr.showscorett
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPscorelabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.scorelabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.scorelabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPmscorelabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.mscorelabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.mscorelabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPpscorelabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.pscorelabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.pscorelabel
									},
									{
										type   : 'checkbox',
										name   : 'fsqmUPshowremarks',
										text   : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.showremarks,
										checked : false,
										tooltip: iptFSQMTML10n.l10n.ss.uplabels.portal_attr.showremarkstt
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPremarkslabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.remarkslabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.remarkslabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPactionlabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.actionlabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.actionlabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPlinklabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.linklabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.linklabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPeditlabel',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.editlabel,
										value  : iptFSQMTML10n.l10n.ss.updefaults.editlabel
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPavatar',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.avatar,
										value  : iptFSQMTML10n.l10n.ss.updefaults.avatar
									},
									{
									    type   : 'listbox',
									    name   : 'fsqmUPtheme',
									    label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.theme,
									    values : themes,
									    value  : 'designer-4'
									},
									{
										type   : 'textbox',
										name   : 'fsqmUPlogout_r',
										label  : iptFSQMTML10n.l10n.ss.uplabels.portal_attr.logout_r,
										value  : iptFSQMTML10n.l10n.ss.updefaults.logout_r,
										tooltip: iptFSQMTML10n.l10n.ss.uplabels.portal_attr.logout_r_tt
									}
								],
								onsubmit: function( e ) {
									var textboxAttrs = [ 'login', 'title', 'nosubmission', 'formlabel', 'categorylabel', 'datelabel', 'scorelabel', 'mscorelabel', 'pscorelabel', 'actionlabel', 'linklabel', 'editlabel', 'avatar', 'logout_r', 'remarkslabel' ],
									checkboxAttrs = [ 'show_register', 'show_forgot', 'filters', 'showcategory', 'showscore', 'showremarks' ],
									shortcode = '[ipt_fsqm_utrackback';

									// Add the textbox attr
									for ( i in textboxAttrs ) {
										shortcode += ' ' + textboxAttrs[i] + '="' + e.data['fsqmUP'+textboxAttrs[i]] + '"';
									}
									// Add the checkboxAttrs
									for ( i in checkboxAttrs ) {
										shortcode += ' ' + checkboxAttrs[i] + '="' + ( e.data['fsqmUP'+checkboxAttrs[i]] === true ? '1' : '0' ) + '"';
									}
									// Add the theme and close shortcode tag
									shortcode += ' theme="' + e.data.fsqmUPtheme + '"]';

									// Add content and shortcode
									shortcode += e.data.fsqmUPcontent + '[/ipt_fsqm_utrackback]';

									editor.insertContent( '<br />' + shortcode + '<br />' );
								}
							});
						}
					},
					// Trackback
					{
						text: iptFSQMTML10n.l10n.ss.tb,
						icon: 'icon ipt-icomoon-retweet',
						onclick: function() {
							var win = editor.windowManager.open({
								title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.ss.tb,
								height: 100,
								width: 500,
								classes: 'ipt-fsqm-panel',
								body: [
									{
										type   : 'textbox',
										name   : 'fsqmTBFL',
										label  : iptFSQMTML10n.l10n.ss.tbfl,
										tooltip: iptFSQMTML10n.l10n.ss.tbfltt,
										value  : iptFSQMTML10n.l10n.ss.tbfll
									},
									{
										type   : 'textbox',
										name   : 'fsqmTBST',
										label  : iptFSQMTML10n.l10n.ss.tbsbtl,
										value  : iptFSQMTML10n.l10n.ss.tbsbt
									}
								],
								onsubmit: function( e ) {
									editor.insertContent( '<br />[ipt_fsqm_trackback label="' + e.data.fsqmTBFL + '" submit="' + e.data.fsqmTBST + '"]<br />' );
								}
							});
						}
					},
					// Login
					{
						text: iptFSQMTML10n.l10n.ss.login.lb,
						icon: 'icon ipt-icomoon-sign-in',
						onclick: function() {
							var win = editor.windowManager.open({
								title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.ss.login.lb,
								height: 250,
								width: 700,
								classes: 'ipt-fsqm-panel',
								body: [
									{
									    type   : 'listbox',
									    name   : 'fsqmLGThm',
									    label  : iptFSQMTML10n.l10n.ss.login.theme,
									    values : themes,
									    value  : 'material-default'
									},
									{
										type   : 'textbox',
										name   : 'fsqmLGrd',
										label  : iptFSQMTML10n.l10n.ss.login.rd,
										value  : ''
									},
									{
										type   : 'checkbox',
										name   : 'fsqmLGrg',
										label   : iptFSQMTML10n.l10n.ss.login.rg,
										checked : true,
										tooltip: iptFSQMTML10n.l10n.ss.login.rgtt
									},
									{
										type   : 'textbox',
										name   : 'fsqmLGrgurl',
										label  : iptFSQMTML10n.l10n.ss.login.rgurl,
										value  : ''
									},
									{
										type   : 'checkbox',
										name   : 'fsqmLGfg',
										label   : iptFSQMTML10n.l10n.ss.login.fg,
										checked : true,
										tooltip: iptFSQMTML10n.l10n.ss.login.fgtt
									},
									{
										type   : 'textbox',
										name   : 'fsqmLGmsg',
										label  : iptFSQMTML10n.l10n.ss.login.msg,
										value  : iptFSQMTML10n.l10n.ss.login.msgdf
									},
								],
								onsubmit: function( e ) {
									var shortcode = '[ipt_eform_login ' +
										'theme="' + e.data.fsqmLGThm + '" ' +
										'redir="' + e.data.fsqmLGrd + '" ' +
										'register="' + ( true === e.data.fsqmLGrg ? '1' : '0' ) + '" ' +
										'regurl="' + e.data.fsqmLGrgurl + '" ' +
										'forgot="' + ( true === e.data.fsqmLGfg ? '1' : '0' ) + '"' +
										']';
									if ( '' != e.data.fsqmLGmsg ) {
										shortcode += e.data.fsqmLGmsg + '[/ipt_eform_login]';
									}
									editor.insertContent( '<br />' + shortcode + '<br />' );
								}
							});
						}
					}
				]
			},
			// Insert Forms
			{
				text: iptFSQMTML10n.l10n.if,
				icon: 'icon ipt-icomoon-insert-template',
				onclick: function() {
					editor.windowManager.open( {
						title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.if,
						height: 100,
						width: 500,
						classes: 'ipt-fsqm-panel',
						body: [
							{
								type: 'listbox',
								name: 'fsqmFormID',
								label: iptFSQMTML10n.l10n.ifl,
								values: forms
							}
						],
						onsubmit: function( e ) {
							editor.insertContent( '<br />[ipt_fsqm_form id="' + e.data.fsqmFormID + '"]<br />' );
						}
					});
				}
			},
			// Insert Trends
			{
				text: iptFSQMTML10n.l10n.it,
				icon: 'icon ipt-icomoon-stats',
				onclick: function() {
					var activeFormID = null,
					height = jQuery(window).height(),
					width = jQuery(window).width();
					editor.windowManager.open( {
						title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.it,
						height: ( height - 200 ),
						width: ( width < 900 ) ? ( width - 50 ) : 800,
						classes: 'ipt-fsqm-panel',
						autoScroll: true,
						body: trendsWizardBody,
						onsubmit: function( e ) {
							// Get the form ID
							var formID = e.data.fsqmFormID;
							// Store the settings
							var rtypeConfig = {},
							rdataConfig = {},
							rappearanceConfig = {},
							serverLoad = e.data.fsqmTSL,
							visualizationTitle = e.data.fsqmTVC,
							i;
							// Store the report type
							for ( i in iptFSQMTML10n.trends.reportTypes ) {
								rtypeConfig[ iptFSQMTML10n.trends.reportTypes[ i ].value ] = false;
								if ( true === e.data[ 'fsqmTW1rt' + iptFSQMTML10n.trends.reportTypes[ i ].value ] ) {
									rtypeConfig[ iptFSQMTML10n.trends.reportTypes[ i ].value ] = true;
								}
							}

							// Store the report data
							for ( i in iptFSQMTML10n.trends.reportData ) {
								rdataConfig[ iptFSQMTML10n.trends.reportData[ i ].value ] = false;
								if ( true === e.data[ 'fsqmTW1rd' + iptFSQMTML10n.trends.reportData[ i ].value ] ) {
									rdataConfig[ iptFSQMTML10n.trends.reportData[ i ].value ] = true;
								}
							}

							// Store the report appearance
							for ( i in iptFSQMTML10n.trends.reportAppearance ) {
								rappearanceConfig[ iptFSQMTML10n.trends.reportAppearance[ i ].value ] = false;
								if ( true === e.data[ 'fsqmTW1ra' + iptFSQMTML10n.trends.reportAppearance[ i ].value ] ) {
									rappearanceConfig[ iptFSQMTML10n.trends.reportAppearance[ i ].value ] = true;
								}
							}

							// Show another window to wait for ajax
							editor.windowManager.open({
								title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.it,
								height: 100,
								width: 500,
								body: [
									{
										type: 'container',
										html: iptFSQMTML10n.l10n.ajax
									}
								]
							});

							// Prep the ajax call
							jQuery.get( ajaxurl, {
								action: 'ipt_fsqm_shortcode_get_mcqs_for_mce',
								wpnonce: iptFSQMTML10n.nonce,
								form_id: formID,
								reportType: rtypeConfig
							}, function(data) {
								editor.windowManager.close();
								if ( data.html !== undefined && data.mcqs === undefined ) {
									editor.windowManager.alert( 'Error! ' + data.html );
									return;
								}
								var body = [], i, j, win, chartType, toggleType;
								// Some state variable for bypassing
								// weird tinymce programmatic checkbox change issue
								var onGoingCheckMCQ = false,
								onGoingCheckFreeType = false,
								onGoingCheckpInfo = false,
								onGoingCheckUsers = false,
								onGoingCheckURL = false;
								// MCQ Questions
								if ( data.mcqs.length ) {
									for ( i in data.mcqs ) {
										// Add the main questions
										body[body.length] = {
											type   : 'checkbox',
											name   : 'fsqmMID' + data.mcqs[i].value,
											label  : data.mcqs[i].value == 'all' ? iptFSQMTML10n.l10n.twb2.sm : '',
											text   : data.mcqs[i].text,
											checked : data.mcqs[i].value == 'all' ? true : false,
											onChange: function( e ) {
												if ( onGoingCheckMCQ ) {
													return;
												}
												onGoingCheckMCQ = true;
												var elm;
												// Change Show All
												if ( this._name != 'fsqmMIDall' ) {
													elm = win.find( '#fsqmMIDall' );
													elm.checked( false );
													elm.value( false );
												// Disable other checkboxes
												} else {
													for ( i in data.mcqs ) {
														if ( data.mcqs[i].value != 'all' ) {
															elm = win.find( '#fsqmMID' + data.mcqs[i].value );
															elm.checked( false );
															elm.value( false );
														}
													}
												}
												onGoingCheckMCQ = false;
											}
										};
										// Add the Chart type
										if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.mcqs[ i ].type ] ) ) {
											chartType = [];
											for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.mcqs[ i ].type ] ) {
												if ( 'default' != j ) {
													chartType[ chartType.length ] = {
														text: iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.mcqs[ i ].type ][ j ],
														value: j
													};
												}
											}
											body[ body.length ] = {
												label: iptFSQMTML10n.l10n.twb2.sct,
												type: 'listbox',
												name: 'fsqmMID' + data.mcqs[i].value + 'ctype',
												values: chartType,
												value: iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.mcqs[ i ].type ].default
											};
										}
										// Add the Toggle options
										if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ] ) ) {
											for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ] ) {
												body[ body.length ] = {
													type: 'checkbox',
													label: iptFSQMTML10n.trends.cTypeToggle.toggle_labels[ iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ][ j ] ],
													name: 'fsqmMID' + data.mcqs[i].value + 'toggle' + iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ][ j ],
													checked: true
												};
											}
										}
										// Add a spacer
										body[ body.length ] = {
											type: 'spacer'
										};
									}
									body[ body.length ] = {
										type: 'spacer'
									};
								}

								// Freetype Questions
								if ( data.freetypes.length ) {
									for ( i in data.freetypes ) {
										// Just add the main question
										body[body.length] = {
											type   : 'checkbox',
											name   : 'fsqmFID' + data.freetypes[i].value,
											label  : data.freetypes[i].value == 'all' ? iptFSQMTML10n.l10n.twb2.sf : '',
											text   : data.freetypes[i].text,
											checked : data.freetypes[i].value == 'all' ? true : false,
											onChange: function( e ) {
												if ( onGoingCheckFreeType ) {
													return;
												}
												onGoingCheckFreeType = true;
												var elm;
												// Change Show All
												if ( this._name != 'fsqmFIDall' ) {
													elm = win.find( '#fsqmFIDall' );
													elm.checked( false );
													elm.value( false );
												// Disable other checkboxes
												} else {
													for ( i in data.freetypes ) {
														if ( data.freetypes[i].value != 'all' ) {
															elm = win.find( '#fsqmFID' + data.freetypes[i].value );
															elm.checked( false );
															elm.value( false );
														}
													}
												}
												onGoingCheckFreeType = false;
											}
										};
										// No need to account for ctype and toggles
										// Cause these are text types
									}
									body[ body.length ] = {
										type: 'spacer'
									};
								}

								// Insert pinfo elements
								if ( data.pinfos.length ) {
									for ( i in data.pinfos ) {
										// Add the main questions
										body[body.length] = {
											type   : 'checkbox',
											name   : 'fsqmPID' + data.pinfos[i].value,
											label  : data.pinfos[i].value == 'all' ? iptFSQMTML10n.l10n.twb2.sp : '',
											text   : data.pinfos[i].text,
											checked : data.pinfos[i].value == 'all' ? true : false,
											onChange: function( e ) {
												if ( onGoingCheckpInfo ) {
													return;
												}
												onGoingCheckpInfo = true;
												var elm;
												// Change Show All
												if ( this._name != 'fsqmPIDall' ) {
													elm = win.find( '#fsqmPIDall' );
													elm.checked( false );
													elm.value( false );
												// Disable other checkboxes
												} else {
													for ( i in data.pinfos ) {
														if ( data.pinfos[i].value != 'all' ) {
															elm = win.find( '#fsqmPID' + data.pinfos[i].value );
															elm.checked( false );
															elm.value( false );
														}
													}
												}
												onGoingCheckpInfo = false;
											}
										};
										// Add the Chart type
										if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.pinfos[ i ].type ] ) ) {
											chartType = [];
											for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.pinfos[ i ].type ] ) {
												if ( 'default' != j ) {
													chartType[ chartType.length ] = {
														text: iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.pinfos[ i ].type ][ j ],
														value: j
													};
												}
											}
											body[ body.length ] = {
												label: iptFSQMTML10n.l10n.twb2.sct,
												type: 'listbox',
												name: 'fsqmPID' + data.pinfos[i].value + 'ctype',
												values: chartType,
												value: iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.pinfos[ i ].type ].default
											};
										}
										// Add the Toggle options
										if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ] ) ) {
											for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ] ) {
												body[ body.length ] = {
													type: 'checkbox',
													label: iptFSQMTML10n.trends.cTypeToggle.toggle_labels[ iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ][ j ] ],
													name: 'fsqmPID' + data.pinfos[i].value + 'toggle' + iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ][ j ],
													checked: true
												};
											}
										}
										// Add a spacer
										body[ body.length ] = {
											type: 'spacer'
										};
									}
								}

								// Insert filters
								if ( "undefined" !== typeof( data.filters ) ) {
									// Add a spacer
									body[ body.length ] = {
										type: 'spacer'
									};
									body[ body.length ] = {
										type: 'container',
										html: '<h2 style="font-weight: bold">' + iptFSQMTML10n.l10n.twb2.fl + '</h2>'
									};

									// Add users
									for ( i in data.filters.users ) {
										body[body.length] = {
											type   : 'checkbox',
											name   : 'fsqmFlUs' + data.filters.users[i].value,
											label  : data.filters.users[i].value == 'all' ? iptFSQMTML10n.l10n.twb2.su : '',
											text   : data.filters.users[i].text,
											checked : data.filters.users[i].value == 'all' ? true : false,
											onChange: function( e ) {
												if ( onGoingCheckUsers ) {
													return;
												}
												onGoingCheckUsers = true;
												var elm;
												// Change Show All
												if ( this._name != 'fsqmFlUsall' ) {
													elm = win.find( '#fsqmFlUsall' );
													elm.checked( false );
													elm.value( false );
												// Disable other checkboxes
												} else {
													for ( i in data.filters.users ) {
														if ( data.filters.users[i].value != 'all' ) {
															elm = win.find( '#fsqmFlUs' + data.filters.users[i].value );
															elm.checked( false );
															elm.value( false );
														}
													}
												}
												onGoingCheckUsers = false;
											}
										};
									}

									// Add URL Tracks
									body[ body.length ] = {
										type: 'spacer'
									};
									for ( i in data.filters.urltb ) {
										body[body.length] = {
											type   : 'checkbox',
											name   : 'fsqmFlURL' + data.filters.urltb[i].value,
											label  : data.filters.urltb[i].value == 'all' ? iptFSQMTML10n.l10n.twb2.surl : '',
											text   : data.filters.urltb[i].text,
											checked : data.filters.urltb[i].value == 'all' ? true : false,
											onChange: function( e ) {
												if ( onGoingCheckURL ) {
													return;
												}
												onGoingCheckURL = true;
												var elm;
												// Change Show All
												if ( this._name != 'fsqmFlURLall' ) {
													elm = win.find( '#fsqmFlURLall' );
													elm.checked( false );
													elm.value( false );
												// Disable other checkboxes
												} else {
													for ( i in data.filters.urltb ) {
														if ( data.filters.urltb[i].value != 'all' ) {
															elm = win.find( '#fsqmFlURL' + data.filters.urltb[i].value );
															elm.checked( false );
															elm.value( false );
														}
													}
												}
												onGoingCheckURL = false;
											}
										};
									}

									// Add User meta key and value
									body[ body.length ] = {
										type: 'spacer'
									};
									body[ body.length ] = {
										type: 'textbox',
										name: 'fsqmFlUMK',
										label: iptFSQMTML10n.l10n.twb2.umk
									};
									body[ body.length ] = {
										type: 'textbox',
										name: 'fsqmFlUMV',
										label: iptFSQMTML10n.l10n.twb2.umv
									};

									// Add Score Obtained Range
									body[ body.length ] = {
										type: 'spacer'
									};
									body[ body.length ] = {
										type: 'textbox',
										name: 'fsqmFlSoMin',
										label: iptFSQMTML10n.l10n.twb2.somin
									};
									body[ body.length ] = {
										type: 'textbox',
										name: 'fsqmFlSoMax',
										label: iptFSQMTML10n.l10n.twb2.somax
									};

									// Add date range
									body[ body.length ] = {
										type: 'spacer'
									};
									body[ body.length ] = {
										type: 'textbox',
										name: 'fsqmFlDtMin',
										label: iptFSQMTML10n.l10n.twb2.dtmin.replace( '%s', data.filters.dates.least_date )
									};
									body[ body.length ] = {
										type: 'textbox',
										name: 'fsqmFlDtMax',
										label: iptFSQMTML10n.l10n.twb2.dtmax.replace( '%s', data.filters.dates.recent_date )
									};
								}

								var height = jQuery(window).height(), width = jQuery(window).width(),
								prevData = data;
								win = editor.windowManager.open({
									title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.it,
									height: ( height - 200 ),
									width: ( width < 900 ) ? ( width - 50 ) : 800,
									autoScroll: true,
									classes: 'ipt-fsqm-panel',
									body: body,
									onsubmit: function( e ) {
										var i, j;
										// Get the basics
										var shortcode = '[ipt_fsqm_trends form_id="' + formID +
											'" load="' + serverLoad + '" title="' + visualizationTitle + '"';

										// Now add the mcqs ( if any )
										if ( data.mcqs.length ) {
											shortcode += ' mcq_ids="';
											// Form the JSON and insert it too
											var mcqJSON = {
												charttype: {}, // We need charttype (case sensitive) beacuse it is expected by PHP
												toggles: {}, // We need toggles (case sensitive) because it is expected by PHP
											};
											if ( e.data.fsqmMIDall === true ) {
												shortcode += 'all';
												for ( i in data.mcqs ) {
													// Look for chart
													if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.mcqs[ i ].type ] ) ) {
														mcqJSON.charttype[ data.mcqs[ i ].value ] = e.data[ 'fsqmMID' + data.mcqs[ i ].value + 'ctype' ];
													}

													// Look for toggles
													if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ] ) ) {
														mcqJSON.toggles[ data.mcqs[ i ].value ] = {};
														for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ] ) {
															mcqJSON.toggles[ data.mcqs[ i ].value ][ iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ][ j ] ] = e.data[ 'fsqmMID' + data.mcqs[i].value + 'toggle' + iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ][ j ] ];
														}
													}
												}
											} else {
												var mcq_ids = [];
												for ( i in data.mcqs ) {
													if ( e.data['fsqmMID' + data.mcqs[i].value ] === true ) {
														mcq_ids.push( data.mcqs[i].value );

														// Look for chart
														if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.mcqs[ i ].type ] ) ) {
															mcqJSON.charttype[ data.mcqs[ i ].value ] = e.data[ 'fsqmMID' + data.mcqs[ i ].value + 'ctype' ];
														}

														// Look for toggles
														if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ] ) ) {
															mcqJSON.toggles[ data.mcqs[ i ].value ] = {};
															for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ] ) {
																mcqJSON.toggles[ data.mcqs[ i ].value ][ iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ][ j ] ] = e.data[ 'fsqmMID' + data.mcqs[i].value + 'toggle' + iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.mcqs[ i ].type ][ j ] ];
															}
														}
													}
												}
												shortcode += mcq_ids.join( ',' );
											}

											shortcode += '" mcq_config=\'' + JSON.stringify( mcqJSON ) + '\'';
										}

										// Add freetype
										if ( data.freetypes.length ) {
											shortcode += ' freetype_ids="';
											if ( true === e.data.fsqmFIDall ) {
												shortcode += 'all';
											} else {
												var freetype_ids = [];
												for ( i in data.freetypes ) {
													if ( true === e.data[ 'fsqmFID' + data.freetypes[ i ].value ] ) {
														freetype_ids.push( data.freetypes[ i ].value );
													}
												}
												shortcode += freetype_ids.join( ',' );
											}
											shortcode += '"';
										}

										// Add pinfo
										if ( data.pinfos.length ) {
											shortcode += ' pinfo_ids="';
											// Form the JSON and insert it too
											var pinfoJSON = {
												charttype: {}, // We need charttype (case sensitive) beacuse it is expected by PHP
												toggles: {}, // We need toggles (case sensitive) because it is expected by PHP
											};
											if ( e.data.fsqmPIDall === true ) {
												shortcode += 'all';
												for ( i in data.pinfos ) {
													// Look for chart
													if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.pinfos[ i ].type ] ) ) {
														pinfoJSON.charttype[ data.pinfos[ i ].value ] = e.data[ 'fsqmPID' + data.pinfos[ i ].value + 'ctype' ];
													}

													// Look for toggles
													if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ] ) ) {
														pinfoJSON.toggles[ data.pinfos[ i ].value ] = {};
														for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ] ) {
															pinfoJSON.toggles[ data.pinfos[ i ].value ][ iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ][ j ] ] = e.data[ 'fsqmPID' + data.pinfos[ i ].value + 'toggle' + iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ][ j ] ];
														}
													}
												}
											} else {
												var pinfo_ids = [];
												for ( i in data.pinfos ) {
													if ( e.data['fsqmPID' + data.pinfos[i].value ] === true ) {
														pinfo_ids.push( data.pinfos[i].value );

														// Look for chart
														if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_chart_types[ data.pinfos[ i ].type ] ) ) {
															pinfoJSON.charttype[ data.pinfos[ i ].value ] = e.data[ 'fsqmPID' + data.pinfos[ i ].value + 'ctype' ];
														}

														// Look for toggles
														if ( "undefined" !== typeof( iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ] ) ) {
															pinfoJSON.toggles[ data.pinfos[ i ].value ] = {};
															for ( j in iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ] ) {
																pinfoJSON.toggles[ data.pinfos[ i ].value ][ iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ][ j ] ] = e.data[ 'fsqmPID' + data.pinfos[ i ].value + 'toggle' + iptFSQMTML10n.trends.cTypeToggle.possible_toggle_types[ data.pinfos[ i ].type ][ j ] ];
															}
														}
													}
												}
												shortcode += pinfo_ids.join( ',' );
											}

											shortcode += '" pinfo_config=\'' + JSON.stringify( pinfoJSON ) + '\'';
										}

										// Add filters
										if ( "undefined" !== typeof( data.filters ) ) {
											var filters = {};
											// Add Users
											filters.users = 'all';
											if ( true !== e.data.fsqmFlUsall ) {
												var selected_user_ids = [];
												for ( i in data.filters.users ) {
													if ( data.filters.users[ i ].value !== 'all' ) {
														if ( true === e.data[ 'fsqmFlUs' + data.filters.users[ i ].value ] ) {
															selected_user_ids[ selected_user_ids.length ] = data.filters.users[ i ].value;
														}
													}
												}
												filters.users = selected_user_ids.join( ',' );
											}

											// Add URL Tracks
											filters.urlTracks = 'all';
											if ( true !== e.data.fsqmFlURLall ) {
												var selected_urlTracks = [];
												for ( i in data.filters.urltb ) {
													if ( 'all' !== data.filters.urltb[ i ].value ) {
														if ( true === e.data[ 'fsqmFlURL' + data.filters.urltb[ i ].value ] ) {
															selected_urlTracks[ selected_urlTracks.length ] = data.filters.urltb[ i ].value;
														}
													}
												}
												filters.urlTracks = selected_urlTracks.join( ',' );
											}

											// Add meta key and value
											filters.mk = e.data.fsqmFlUMK;
											filters.mv = e.data.fsqmFlUMV;

											// Add score obtained range
											filters.smin = e.data.fsqmFlSoMin;
											filters.smax = e.data.fsqmFlSoMax;

											// Add date range
											filters.dtmin = e.data.fsqmFlDtMin;
											filters.dtmax = e.data.fsqmFlDtMax;

											// Add the JSON to the shortcode
											shortcode += " filters='" + JSON.stringify( filters ) + "'";
										}

										// Add data variables
										shortcode += " data='" + JSON.stringify( rdataConfig ) + "'";

										// Add appearance variable
										shortcode += " appearance='" + JSON.stringify( rappearanceConfig ) + "'";

										shortcode += ']';
										editor.insertContent( '<br />' + shortcode + '<br />' );
									}
								});
							}).fail(function() {
								editor.windowManager.close();
								editor.windowManager.alert('Error! Could not connect to server');
							});
						}
					});
				}
			},
			// Insert Popup Forms
			{
				text: iptFSQMTML10n.l10n.pf,
				icon: 'icon ipt-icomoon-newtab',
				onclick: function() {
					var win, height = jQuery(window).height(), width = jQuery(window).width();
					win = editor.windowManager.open({
						title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.pf,
						height: ( height < 850 ) ? ( height - 100 ) : 750,
						width: ( width < 900 ) ? ( width - 50 ) : 800,
						autoScroll: true,
						classes: 'ipt-fsqm-panel',
						body: [
							{
								type: 'listbox',
								name: 'fsqmFormID',
								label: iptFSQMTML10n.l10n.ifl,
								values: forms
							},
							{
								type   : 'textbox',
								name   : 'fsqmPFBT',
								label  : iptFSQMTML10n.l10n.pfbt,
								value  : iptFSQMTML10n.l10n.pfbtl
							},
							{
								type   : 'colorpicker',
								name   : 'fsqmPFBC',
								label  : iptFSQMTML10n.l10n.pfbc,
								value  : '#ffffff',
								onChange: function(e) {
									jQuery('#ipt_fsqmPFPVDIV').css( 'color', this.value() );
								}
							},
							{
								type   : 'colorpicker',
								name   : 'fsqmPFBBC',
								label  : iptFSQMTML10n.l10n.pfbbc,
								value  : '#3C609E',
								onChange: function(e) {
									jQuery('#ipt_fsqmPFPVDIV').css( 'background-color', this.value() );
								}
							},
							{
								type   : 'listbox',
								name   : 'fsqmPFBP',
								label  : iptFSQMTML10n.l10n.pfbp,
								values : iptFSQMTML10n.l10n.pfbplb
							},
							{
								type   : 'listbox',
								name   : 'fsqmPFBS',
								label  : iptFSQMTML10n.l10n.pfbs,
								values : iptFSQMTML10n.l10n.pfbslb
							},
							{
								type   : 'textbox',
								name   : 'fsqmPFBH',
								label  : iptFSQMTML10n.l10n.pfbheader,
								value  : '%FORM%'
							},
							{
								type   : 'textbox',
								name   : 'fsqmPFBSUB',
								label  : iptFSQMTML10n.l10n.pfbsubtitle,
								value  : ''
							},
							{
								type   : 'textbox',
								name   : 'fsqmPFBICN',
								label  : iptFSQMTML10n.l10n.pfbicon,
								value  : 'fa fa-file-text'
							},
							{
								type   : 'textbox',
								name   : 'fsqmPFBWDH',
								label  : iptFSQMTML10n.l10n.pfbwidth,
								value  : '600'
							},
							{
								type   : 'container',
								name   : 'fsqmPFPV',
								label  : iptFSQMTML10n.l10n.pfpv,
								html   : '<div id="ipt_fsqmPFPVDIV" style="width: 200px;margin: 10px auto;background-color: #3C609E;color: #fff;height: 50px;border-radius: 5px 5px 0 0;text-align: center;line-height: 50px;">' + iptFSQMTML10n.l10n.pfbtl + '</div>'
							}
						],
						onsubmit: function( e ) {
							var shortcode = '[ipt_fsqm_popup style="' + e.data.fsqmPFBS + '" header="' + e.data.fsqmPFBH + '" subtitle="' + e.data.fsqmPFBSUB + '" icon="' + e.data.fsqmPFBICN + '" width="' + e.data.fsqmPFBWDH + '" form_id="' + e.data.fsqmFormID + '" pos="' + e.data.fsqmPFBP + '" color="' + e.data.fsqmPFBC + '" bgcolor="' + e.data.fsqmPFBBC + '"]' + e.data.fsqmPFBT + '[/ipt_fsqm_popup]';
							editor.insertContent( '<br />' + shortcode + '<br />' );
							// Show popup for hidden trigger
							if ( e.data.fsqmPFBP == 'h' ) {
								var btnHTML = '<a class="eform-manual-popup" href="#ipt-fsqm-popup-form-' + e.data.fsqmFormID + '" data-form-id="' + e.data.fsqmFormID + '" data-eform-popup="1">' + e.data.fsqmPFBT + '</a>';
								editor.windowManager.confirm( iptFSQMTML10n.l10n.pfmt + '  -  ' + btnHTML, function( s ) {
									if ( s ) {
										editor.insertContent( '<br />' + btnHTML + '<br />' );
									}
								} );
							}
						}
					});
				}
			},
			// Insert Leaderboard
			{
				text: iptFSQMTML10n.l10n.lb.lb,
				icon: 'icon ipt-icomoon-trophy',
				menu: [
					// Form Leaderboard
					{
						text: iptFSQMTML10n.l10n.lb.flb,
						icon: 'icon ipt-icomoon-list-alt',
						onclick: function() {
							var win, height = jQuery(window).height(), width = jQuery(window).width();
							win = editor.windowManager.open({
								title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.lb.flb,
								height: ( height < 850 ) ? ( height - 100 ) : 750,
								width: ( width < 900 ) ? ( width - 50 ) : 800,
								autoScroll: true,
								classes: 'ipt-fsqm-panel',
								body: [
									// Forms
									{
										type: 'listbox',
										name: 'fsqmFormID',
										label: iptFSQMTML10n.l10n.ifl,
										values: forms
									},
									// Appearance
									{
										type: 'spacer'
									},
									{
										type: 'container',
										html: '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.lb.flba + '</h2>'
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_heading',
										text    : iptFSQMTML10n.l10n.lb.flbah,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_image',
										text    : iptFSQMTML10n.l10n.lb.flbai,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_avatar',
										text    : iptFSQMTML10n.l10n.lb.flbaa,
										checked : true
									},
									{
										type    : 'textbox',
										name    : 'eformLBFA_avatarsize',
										label   : iptFSQMTML10n.l10n.lb.flbaa,
										value   : '64'
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_name',
										text    : iptFSQMTML10n.l10n.lb.flban,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_meta',
										text    : iptFSQMTML10n.l10n.lb.flbam,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_score',
										text    : iptFSQMTML10n.l10n.lb.flbas,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_maxscore',
										text    : iptFSQMTML10n.l10n.lb.flbams,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_per',
										text    : iptFSQMTML10n.l10n.lb.flbap,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_date',
										text    : iptFSQMTML10n.l10n.lb.flbad,
										checked : true
									},
									{
										type    : 'checkbox',
										name    : 'eformLBFA_com',
										text    : iptFSQMTML10n.l10n.lb.flbac,
										checked : false
									},
									// Labels
									{
										type: 'spacer'
									},
									{
										type: 'container',
										html: '<h2 style="font-weight: bold;">' + iptFSQMTML10n.l10n.lb.flbl + '</h2>'
									},
									{
										type    : 'textbox',
										name    : 'eformLBFL_name',
										label   : iptFSQMTML10n.l10n.lb.flblname,
										value   : iptFSQMTML10n.l10n.lb.flblvname
									},
									{
										type    : 'textbox',
										name    : 'eformLBFL_score',
										label   : iptFSQMTML10n.l10n.lb.flblscore,
										value   : iptFSQMTML10n.l10n.lb.flblvscore
									},
									{
										type    : 'textbox',
										name    : 'eformLBFL_max_score',
										label   : iptFSQMTML10n.l10n.lb.flblmax_score,
										value   : iptFSQMTML10n.l10n.lb.flblvmax_score
									},
									{
										type    : 'textbox',
										name    : 'eformLBFL_percentage',
										label   : iptFSQMTML10n.l10n.lb.flblpercentage,
										value   : iptFSQMTML10n.l10n.lb.flblvpercentage
									},
									{
										type    : 'textbox',
										name    : 'eformLBFL_date',
										label   : iptFSQMTML10n.l10n.lb.flbldate,
										value   : iptFSQMTML10n.l10n.lb.flblvdate
									},
									{
										type    : 'textbox',
										name    : 'eformLBFL_comment',
										label   : iptFSQMTML10n.l10n.lb.flblcomment,
										value   : iptFSQMTML10n.l10n.lb.flblvcomment
									},
									// Content
									{
										type      : 'textbox',
										name      : 'eformLBFL_content',
										label     : iptFSQMTML10n.l10n.lb.flblcontent,
										value     : '',
										multiline : true
									},

								],
								onsubmit: function( e ) {
									var shortcode = '[ipt_eform_lb_form form_id="' + e.data.fsqmFormID + '"';
									// Get the appearance JSON
									var appearance = {
										'avatar'      : e.data.eformLBFA_avatar,
										'avatar_size' : e.data.eformLBFA_avatarsize,
										'name'        : e.data.eformLBFA_name,
										'date'        : e.data.eformLBFA_date,
										'score'       : e.data.eformLBFA_score,
										'max_score'   : e.data.eformLBFA_maxscore,
										'percentage'  : e.data.eformLBFA_per,
										'comment'     : e.data.eformLBFA_com,
										'heading'     : e.data.eformLBFA_heading,
										'image'       : e.data.eformLBFA_image,
										'meta'        : e.data.eformLBFA_meta,
									};
									shortcode += " appearance='" + JSON.stringify( appearance ) + "'";

									// Get labels
									shortcode += ' lname="' + e.data.eformLBFL_name + '"';
									shortcode += ' ldate="' + e.data.eformLBFL_date + '"';
									shortcode += ' lscore="' + e.data.eformLBFL_score + '"';
									shortcode += ' lmax_score="' + e.data.eformLBFL_max_score + '"';
									shortcode += ' lpercentage="' + e.data.eformLBFL_percentage + '"';
									shortcode += ' lcomment="' + e.data.eformLBFL_comment + '"';

									// Get content
									shortcode += ']' + e.data.eformLBFL_content;

									// End it
									shortcode += '[/ipt_eform_lb_form]';

									// Insert it
									editor.insertContent( '<br />' + shortcode + '<br />' );
								}
							});
						}
					}
				]
			},
			// Insert Statistics
			{
				text: iptFSQMTML10n.l10n.st.st,
				icon: 'icon ipt-icomoon-area-chart',
				menu: [
					// Form Statistics
					{
						text: iptFSQMTML10n.l10n.st.stfs,
						icon: 'icon ipt-icomoon-wpforms',
						menu: [
							// Submission Breakdown
							{
								text: iptFSQMTML10n.l10n.st.fssb,
								tooltip: iptFSQMTML10n.l10n.st.fssbtt,
								onclick: function() {
									var i, width = jQuery(window).width(), body = [];
									body[0] = {
										type   : 'container',
										html   : '<p style="font-weight: bold;">' + iptFSQMTML10n.l10n.st.fssbtt + '</p>'
									};
									for ( i in iptFSQMTML10n.l10n.st.fssb_lbs ) {
										body[ body.length ] = {
											type   : 'textbox',
											name   : 'fsqmSB' + i,
											label  : iptFSQMTML10n.l10n.st.fssb_lbs[ i ],
											value  : iptFSQMTML10n.l10n.st.fssb_df[ i ],
											tooltip: iptFSQMTML10n.l10n.st.fssb_tts[ i ],
										};
									}
									var win = editor.windowManager.open({
										title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.st.fssb,
										height: 400,
										width: ( width < 900 ) ? ( width - 50 ) : 800,
										classes: 'ipt-fsqm-panel',
										autoScroll: true,
										body: body,
										onsubmit: function( e ) {
											var shortcode = '[ipt_eform_stat', i;
											for ( i in iptFSQMTML10n.l10n.st.fssb_lbs ) {
												shortcode += ' ' + i + '="' + e.data[ 'fsqmSB' + i ] + '"';
											}
											shortcode += ']';
											editor.insertContent( '<br />' + shortcode + '<br />' );
										}
									});
								}
							},
							// Overall Submissions
							{
								text: iptFSQMTML10n.l10n.st.fsos,
								tooltip: iptFSQMTML10n.l10n.st.fsostt,
								onclick: function() {
									var i, width = jQuery(window).width(), body = [];
									body[0] = {
										type   : 'container',
										html   : '<p style="font-weight: bold;">' + iptFSQMTML10n.l10n.st.fsostt + '</p>'
									};
									for ( i in iptFSQMTML10n.l10n.st.fsos_lbs ) {
										if ( 'type' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmOS' + i,
												label  : iptFSQMTML10n.l10n.st.fsos_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.fsos_df[ i ],
												values : iptFSQMTML10n.l10n.st.charts,
												tooltip: iptFSQMTML10n.l10n.st.fsos_tts[ i ],
											};
										} else {
											body[ body.length ] = {
												type   : 'textbox',
												name   : 'fsqmOS' + i,
												label  : iptFSQMTML10n.l10n.st.fsos_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.fsos_df[ i ],
												tooltip: iptFSQMTML10n.l10n.st.fsos_tts[ i ],
											};
										}
									}

									var win = editor.windowManager.open({
										title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.st.fsos,
										height: 400,
										width: ( width < 900 ) ? ( width - 50 ) : 800,
										classes: 'ipt-fsqm-panel',
										autoScroll: true,
										body: body,
										onsubmit: function( e ) {
											var shortcode = '[ipt_eform_substat', i;
											for ( i in iptFSQMTML10n.l10n.st.fsos_lbs ) {
												shortcode += ' ' + i + '="' + e.data[ 'fsqmOS' + i ] + '"';
											}
											shortcode += ']';
											editor.insertContent( '<br />' + shortcode + '<br />' );
										}
									});
								}
							},
							// Score Breakdown
							{
								text: iptFSQMTML10n.l10n.st.fscb,
								tooltip: iptFSQMTML10n.l10n.st.fscbtt,
								onclick: function() {
									var i, width = jQuery(window).width(), body = [];
									body[0] = {
										type   : 'container',
										html   : '<p style="font-weight: bold;">' + iptFSQMTML10n.l10n.st.fscbtt + '</p>'
									};
									for ( i in iptFSQMTML10n.l10n.st.fscb_lbs ) {
										if ( 'type' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmSCB' + i,
												label  : iptFSQMTML10n.l10n.st.fscb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.fscb_df[ i ],
												values : iptFSQMTML10n.l10n.st.charts,
												tooltip: iptFSQMTML10n.l10n.st.fscb_tts[ i ],
											};
										} else {
											body[ body.length ] = {
												type   : 'textbox',
												name   : 'fsqmSCB' + i,
												label  : iptFSQMTML10n.l10n.st.fscb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.fscb_df[ i ],
												tooltip: iptFSQMTML10n.l10n.st.fscb_tts[ i ],
											};
										}
									}

									var win = editor.windowManager.open({
										title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.st.fscb,
										height: 400,
										width: ( width < 900 ) ? ( width - 50 ) : 800,
										classes: 'ipt-fsqm-panel',
										autoScroll: true,
										body: body,
										onsubmit: function( e ) {
											var shortcode = '[ipt_eform_formscorestat', i;
											for ( i in iptFSQMTML10n.l10n.st.fscb_lbs ) {
												shortcode += ' ' + i + '="' + e.data[ 'fsqmSCB' + i ] + '"';
											}
											shortcode += ']';
											editor.insertContent( '<br />' + shortcode + '<br />' );
										}
									});
								}
							}
						]
					},
					// User Statistics
					{
						text: iptFSQMTML10n.l10n.st.stus,
						icon: 'icon ipt-icomoon-user3',
						menu: [
							// Submission Breakdown
							{
								text: iptFSQMTML10n.l10n.st.ussb,
								tooltip: iptFSQMTML10n.l10n.st.ussbtt,
								onclick: function() {
									var i, width = jQuery(window).width(), body = [];
									body[0] = {
										type   : 'container',
										html   : '<p style="font-weight: bold;">' + iptFSQMTML10n.l10n.st.ussbtt + '</p>'
									};
									for ( i in iptFSQMTML10n.l10n.st.ussb_lbs ) {
										if ( 'type' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmUSB' + i,
												label  : iptFSQMTML10n.l10n.st.ussb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.ussb_df[ i ],
												values : iptFSQMTML10n.l10n.st.charts,
												tooltip: iptFSQMTML10n.l10n.st.ussb_tts[ i ],
											};
										} else if ( 'theme' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmUSB' + i,
												label  : iptFSQMTML10n.l10n.st.ussb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.ussb_df[ i ],
												values : iptFSQMTMMenu.themes,
												tooltip: iptFSQMTML10n.l10n.st.ussb_tts[ i ],
											};
										} else if ( 'show_login' == i ) {
											body[ body.length ] = {
												type   : 'checkbox',
												name   : 'fsqmUSB' + i,
												text   : iptFSQMTML10n.l10n.st.ussb_lbs[ i ],
												tooltip: iptFSQMTML10n.l10n.st.ussb_tts[ i ],
												checked : true,
											};
										} else {
											body[ body.length ] = {
												type   : 'textbox',
												name   : 'fsqmUSB' + i,
												label  : iptFSQMTML10n.l10n.st.ussb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.ussb_df[ i ],
												tooltip: iptFSQMTML10n.l10n.st.ussb_tts[ i ],
											};
										}
									}

									var win = editor.windowManager.open({
										title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.st.ussb,
										height: 400,
										width: ( width < 900 ) ? ( width - 50 ) : 800,
										classes: 'ipt-fsqm-panel',
										autoScroll: true,
										body: body,
										onsubmit: function( e ) {
											var shortcode = '[ipt_eform_userstatsub', i;
											for ( i in iptFSQMTML10n.l10n.st.ussb_lbs ) {
												if ( 'show_login' == i ) {
													shortcode += ' ' + i + '="' + ( true == e.data[ 'fsqmUSB' + i ] ? '1' : '0' ) + '"';
												} else {
													shortcode += ' ' + i + '="' + e.data[ 'fsqmUSB' + i ] + '"';
												}
											}
											shortcode += ']';
											editor.insertContent( '<br />' + shortcode + '<br />' );
										}
									});
								}
							},

							// Overall Submissions
							{
								text: iptFSQMTML10n.l10n.st.usob,
								tooltip: iptFSQMTML10n.l10n.st.usobtt,
								onclick: function() {
									var i, width = jQuery(window).width(), body = [];
									body[0] = {
										type   : 'container',
										html   : '<p style="font-weight: bold;">' + iptFSQMTML10n.l10n.st.usobtt + '</p>'
									};
									for ( i in iptFSQMTML10n.l10n.st.usob_lbs ) {
										if ( 'type' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmUOB' + i,
												label  : iptFSQMTML10n.l10n.st.usob_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.usob_df[ i ],
												values : iptFSQMTML10n.l10n.st.charts,
												tooltip: iptFSQMTML10n.l10n.st.usob_tts[ i ],
											};
										} else if ( 'theme' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmUOB' + i,
												label  : iptFSQMTML10n.l10n.st.usob_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.usob_df[ i ],
												values : iptFSQMTMMenu.themes,
												tooltip: iptFSQMTML10n.l10n.st.usob_tts[ i ],
											};
										} else if ( 'show_login' == i ) {
											body[ body.length ] = {
												type   : 'checkbox',
												name   : 'fsqmUOB' + i,
												text   : iptFSQMTML10n.l10n.st.usob_lbs[ i ],
												tooltip: iptFSQMTML10n.l10n.st.usob_tts[ i ],
												checked : true,
											};
										} else {
											body[ body.length ] = {
												type   : 'textbox',
												name   : 'fsqmUOB' + i,
												label  : iptFSQMTML10n.l10n.st.usob_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.usob_df[ i ],
												tooltip: iptFSQMTML10n.l10n.st.usob_tts[ i ],
											};
										}
									}

									var win = editor.windowManager.open({
										title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.st.usob,
										height: 400,
										width: ( width < 900 ) ? ( width - 50 ) : 800,
										classes: 'ipt-fsqm-panel',
										autoScroll: true,
										body: body,
										onsubmit: function( e ) {
											var shortcode = '[ipt_eform_usersub', i;
											for ( i in iptFSQMTML10n.l10n.st.usob_lbs ) {
												if ( 'show_login' == i ) {
													shortcode += ' ' + i + '="' + ( true == e.data[ 'fsqmUOB' + i ] ? '1' : '0' ) + '"';
												} else {
													shortcode += ' ' + i + '="' + e.data[ 'fsqmUOB' + i ] + '"';
												}
											}
											shortcode += ']';
											editor.insertContent( '<br />' + shortcode + '<br />' );
										}
									});
								}
							},

							// Score Breakdown
							{
								text: iptFSQMTML10n.l10n.st.usscb,
								tooltip: iptFSQMTML10n.l10n.st.usscbtt,
								onclick: function() {
									var i, width = jQuery(window).width(), body = [];
									body[0] = {
										type   : 'container',
										html   : '<p style="font-weight: bold;">' + iptFSQMTML10n.l10n.st.usscbtt + '</p>'
									};
									for ( i in iptFSQMTML10n.l10n.st.usscb_lbs ) {
										if ( 'type' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmUCB' + i,
												label  : iptFSQMTML10n.l10n.st.usscb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.usscb_df[ i ],
												values : iptFSQMTML10n.l10n.st.charts,
												tooltip: iptFSQMTML10n.l10n.st.usscb_tts[ i ],
											};
										} else if ( 'theme' == i ) {
											body[ body.length ] = {
												type   : 'listbox',
												name   : 'fsqmUCB' + i,
												label  : iptFSQMTML10n.l10n.st.usscb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.usscb_df[ i ],
												values : iptFSQMTMMenu.themes,
												tooltip: iptFSQMTML10n.l10n.st.usscb_tts[ i ],
											};
										} else if ( 'show_login' == i ) {
											body[ body.length ] = {
												type   : 'checkbox',
												name   : 'fsqmUCB' + i,
												text   : iptFSQMTML10n.l10n.st.usscb_lbs[ i ],
												tooltip: iptFSQMTML10n.l10n.st.usscb_tts[ i ],
												checked : true,
											};
										} else {
											body[ body.length ] = {
												type   : 'textbox',
												name   : 'fsqmUCB' + i,
												label  : iptFSQMTML10n.l10n.st.usscb_lbs[ i ],
												value  : iptFSQMTML10n.l10n.st.usscb_df[ i ],
												tooltip: iptFSQMTML10n.l10n.st.usscb_tts[ i ],
											};
										}
									}

									var win = editor.windowManager.open({
										title: iptFSQMTML10n.l10n.slabel + iptFSQMTML10n.l10n.st.usscb,
										height: 400,
										width: ( width < 900 ) ? ( width - 50 ) : 800,
										classes: 'ipt-fsqm-panel',
										autoScroll: true,
										body: body,
										onsubmit: function( e ) {
											var shortcode = '[ipt_eform_userscorestat', i;
											for ( i in iptFSQMTML10n.l10n.st.usscb_lbs ) {
												if ( 'show_login' == i ) {
													shortcode += ' ' + i + '="' + ( true == e.data[ 'fsqmUCB' + i ] ? '1' : '0' ) + '"';
												} else {
													shortcode += ' ' + i + '="' + e.data[ 'fsqmUCB' + i ] + '"';
												}
											}
											shortcode += ']';
											editor.insertContent( '<br />' + shortcode + '<br />' );
										}
									});
								}
							}
						]
					},
				]
			}
		];

		// Open up scope for third party integrations
		for ( i in iptFSQMTMMenu.addons ) {
			if ( typeof iptFSQMTMMenu.addons[i] == 'function' ) {
				menus[menus.length] = iptFSQMTMMenu.addons[i].apply( this, [ editor, url, forms, themes ] );
			}
		}

		// Add the editor button
		editor.addButton( 'ipt_fsqm_tmce_menubutton', {
			title: iptFSQMTML10n.l10n.label,
			type: 'menubutton',
			icon: 'ipt-fsqmic-small-logo',
			menu: menus
		} );

		// Peace
	} );
})();
