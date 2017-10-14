/**
 * eForm Leaderboard jQuery Plugin
 *
 * @license    Themeforest Split
 * @since v3.3
 * @author     Swashata Ghosh ( swashata@iptms.co )
 */
;(function ( $, window, document, undefined ) {
	"use strict";
	var pluginName = "ipteFormLB",
	defaults = {
		type: "form" // Could be form | category | performance
	};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		this.jElement = $( element );
		this.settings = $.extend( {}, defaults, options, this.jElement.data( 'settings' ) );
		this._defaults = defaults;
		this._name = pluginName;
		this.dataTable = null;
		this.init();
	}

	Plugin.prototype = {
		// Main initiator
		init: function () {
			// Set the primary CSS
			var primaryCSS = {
				id: ipteFormLB.css,
				location: ipteFormLB.cssl
			},
			// reference to the instance
			that = this;

			// Call the UI initiator
			this.jElement.iptPluginUIFFront({
				callback: function() {
					that.initLeaderBoard();
				},
				additionalThemes: [ primaryCSS ]
			});
		},

		//
		// Common leaderboard callback function. It checks for possible
		// leaderboard type and does things accordinly
		//
		initLeaderBoard: function () {
			// some logic
			switch ( this.settings.type ) {
				// Form Leaderboard
				default:
				case 'form':
					this.initLeaderBoardForm();
					break;
			}
		},


		/**
		 * Form type leaderboard callback
		 */
		initLeaderBoardForm: function() {
			// Store the reference
			var that = this;

			// Store some DOM variable
			this.table = this.jElement.find( '.ipt-eform-lb-table' );
			this.dataWrap = this.jElement.find( '.ipt_eform_leaderboard_data' );

			// Apply the datatable
			this.dataTable = this.table.DataTable({
				bJQueryUI: true,
				language : ipteFormLB.l10n,
				sPaginationType: "full_numbers",
				aaSorting : [[1, "desc"]],
				bProcessing : true,
				aLengthMenu : [[10, 30, 60, -1], [10, 30, 60, ipteFormLB.allLabel]],
				iDisplayLength : 30,
				dom : '<"fg-toolbar fsqm-up-tt ui-toolbar ui-widget-header ui-helper-clearfix ui-corner-tl ui-corner-tr"lprf>' + 't'+ '<"fg-toolbar ui-toolbar fsqm-up-bt ui-widget-header ui-helper-clearfix ui-corner-bl ui-corner-br"ip>',
				responsive : true,
				autoWidth: false,
				columnDefs : [
					{ width : "350px", targets : [ 0 ] }
				]
			});

			// Add the widget class
			this.table.addClass('ui-widget-content');
			this.dataWrap.find('.dataTables_filter input[type="text"], .dataTables_filter input[type="search"]').addClass('ipt_uif_text');
			this.dataWrap.find('.dataTables_length select').addClass('ipt_uif_select');
			this.dataWrap.trigger( 'dataTablesCompleted.eform' );
		},

		//
		// Just a bogus definition so that we can safely ignore the last method
		// comma
		//
		bogus: function() {

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
	$( '.ipt_eform_leaderboard' ).ipteFormLB();
});
