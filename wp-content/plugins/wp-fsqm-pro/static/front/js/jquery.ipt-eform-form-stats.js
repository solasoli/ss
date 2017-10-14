/**
 * Form Statistics Charts for eForm
 *
 * This plugin is responsible for generating charts and data for eForm
 * form statistics through Chart.js ( https://github.com/chartjs/Chart.js )
 *
 * @license Themeforest Split License
 * @package eForm - WordPress Form Builder
 * @subpackage Form Statistics Charts
 */
;(function ( $, window, document, undefined ) {
	"use strict";
	var pluginName = "eFormFormStats",
	defaults = {
		propertyName: "value"
	};

	// The actual plugin constructor
	function Plugin ( element, options ) {
		this.element = element;
		this.jElement = $( this.element );
		this.settings = $.extend( {}, defaults, options );
		this._defaults = defaults;
		this._name = pluginName;
		this.init();
	}

	Plugin.prototype = {
		init: function () {
			// Set the things from the data
			this.statType = this.jElement.data( 'stattype' );
			this.chartType = this.jElement.data( 'charttype' );
			this.chartData = this.jElement.data( 'chartdata' );
			this.chartOptions = this.jElement.data( 'chartoptions' );
			this.canvasObject = this.jElement.find( 'canvas.ipt-eform-stats-canvas' );

			// Now switch through the stattype and do accordingly
			switch ( this.statType ) {
				case 'submissions':
					this.makeSubmissionsChart();
					break;
				case 'overall':
					this.makeOverallChart();
					break;
				case 'scorestat':
					this.makeScoreStat();
					break;
				case 'user_substat':
					this.makeSubmissionsChart();
					break;
				case 'user_sub':
					this.makeOverallChart();
					break;
				case 'user_score':
					this.makeScoreStat();
					break;
			}
		},

		makeOverallChart: function() {
			// It will be either pie or doughnut
			var chartOptions = {
				responsive: true,
				responsiveAnimationDuration: 400,
				maintainAspectRatio: false,
				cutoutPercentage: 0,
				animation: {
					animateRotate: true,
					animateScale: false
				},
				legend: {
					position: 'bottom'
				}
			};

			if ( 'doughnut' == this.chartType ) {
				chartOptions.cutoutPercentage = 50;
				chartOptions.animation.animateScale = true;
			}

			this.createPieChart( this.canvasObject, this.chartData, chartOptions );
		},

		makeScoreStat: function() {
			// It will be either pie or doughnut
			var chartOptions = {
				responsive: true,
				responsiveAnimationDuration: 400,
				maintainAspectRatio: false,
				cutoutPercentage: 0,
				animation: {
					animateRotate: true,
					animateScale: false
				},
				legend: {
					position: 'bottom'
				}
			};

			if ( 'doughnut' == this.chartType ) {
				chartOptions.cutoutPercentage = 50;
				chartOptions.animation.animateScale = true;
			}

			this.createPieChart( this.canvasObject, this.chartData, chartOptions );
		},

		makeSubmissionsChart: function() {
			// In this case, we are making the combo charts only
			var chartOptions = {
				responsive: true,
				responsiveAnimationDuration: 400,
				maintainAspectRatio: false,
				scales: {
					xAxes: [{
						scaleLabel: {
							display: true,
							labelString: this.chartOptions.xlabelString
						}
					}],
					yAxes: [{
						scaleLabel: {
							display: true,
							labelString: this.chartOptions.ylabelString
						}
					}]
				},
				legend: {
					position: 'bottom'
				}
			};
			this.createBarChart( this.canvasObject, this.chartData, chartOptions );
		},

		// Helpers for creating charts
		createBarChart: function( ctx, data, options ) {
			return new Chart( ctx, {
				type: 'bar',
				data: data,
				options: options
			} );
		},
		createPieChart: function( ctx, data, options ) {
			return new Chart( ctx, {
				type: 'pie',
				data: data,
				options: options
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
	$('.ipt-eform-stats').eFormFormStats();
});
