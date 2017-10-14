/**
 * Helper script for eForm Login Shortcode
 *
 * @license Themeforest Split License
 */
;(function ( $, window, document, undefined ) {
	"use strict";

	// Create the defaults once
	var pluginName = "IPTeFormLogin",
	defaults = {
		propertyName: "value"
	};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
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
		init: function () {
			$(this.element).iptPluginUIFFront();
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
	$('.ipt_eform_login').IPTeFormLogin();
});
