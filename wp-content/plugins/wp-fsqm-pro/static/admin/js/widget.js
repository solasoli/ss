jQuery(document).ready(function($) {
	// A helper function to help with customizer widget update
	var customizerHelperElm = null;
	var ajaxHappening = false;
	var customizerHelper = function() {
		try {
			customizerHelperElm.closest('.form').find('[name="savewidget"]').trigger('click');
		} catch ( e ) {
			// Do nothing
		} finally {
			$(document).off( 'ajaxComplete', customizerHelper );
		}
	};
	$(document).ajaxStart(function() {
		ajaxHappening = true;
	});
	$(document).ajaxStop(function() {
		ajaxHappening = false;
	});

	// Hide the lists at first
	$('.ipt_fsqm_tw_outer').each( function() {
		var mcq = $(this).find('.ipt_fsqm_tw_mcqwrap'),
		pinfo = $(this).find('.ipt_fsqm_tw_pinfowrap'),
		filter = $(this).find('.ipt_fsqm_tw_filterwrap');

		if ( mcq.val() == '0' ) {
			$(this).find('.ipt_fsqm_tw_mcqs .ipt_fsqm_tw_qlist_wrap').hide();
		}
		if ( pinfo.val() == '0' ) {
			$(this).find('.ipt_fsqm_tw_pinfos .ipt_fsqm_tw_qlist_wrap').hide();
		}
		if ( filter.val() == '0' ) {
			$(this).find('.ipt_fsqm_tw_filters .ipt_fsqm_tw_qlist_wrap').hide();
		}
	} );

	// Toggle expand/collaspe on clicking the h2
	$(document).on( 'click', 'h2.ipt_fsqm_tw_qlist_toggle', function() {
		// Get the parent and class
		var elm = $(this).closest('.ipt_fsqm_tw_outer'),
		target = elm.find( '.' + $(this).closest('.ipt_fsqm_tw').data( 'target' ) );
		if ( target.val() == '1' ) {
			$(this).next('.ipt_fsqm_tw_qlist_wrap').slideUp('fast');
			target.val( '0' );
		} else {
			$(this).next('.ipt_fsqm_tw_qlist_wrap').slideDown('fast');
			target.val( '1' );
		}
	} );

	// Toggle expand/collaspe on selecting element
	$(document).on( 'change', 'input.ipt_fsqm_tw_elm', function() {
		var target = $(this).closest('.ipt_fsqm_tw_qlist');
		if ( $(this).is(':checked') ) {
			target.removeClass('qlist_hidden').find('.ipt_fsqm_tw_cmeta').hide().slideDown('fast');
		} else {
			target.addClass('qlist_hidden').find('.ipt_fsqm_tw_cmeta').hide();
		}
	} );

	// Auto save on form select
	$(document).on( 'change', '.ipt_fsqm_tw_form_id', function(e) {
		if ( "undefined" !== typeof( wp ) && "undefined" !== typeof( wp.customize ) ) {
			customizerHelperElm = $(this);
			if ( true === ajaxHappening ) {
				$(document).on( 'ajaxComplete', customizerHelper );
			} else {
				customizerHelperElm.closest('.form').find('[name="savewidget"]').trigger('click');
			}
		} else {
			$(this).closest('form').find('[name="savewidget"]').trigger('click');
		}
	} );
});
