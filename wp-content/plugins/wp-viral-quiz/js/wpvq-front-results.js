(function($) 
{ 
	// Not the same content depending on the quiz type
	var resultTitle = ''; var resultContent = '';
	if (wpvq_type == 'WPVQGamePersonality') 
	{
		var firstResult 		=  wpvq_results.shift();
		resultTitle 			=  firstResult.label;
		resultContent 			=  firstResult.content;
	} 
	else if (wpvq_type == 'WPVQGameTrueFalse') 
	{
		resultTitle = wpvq_results.resultValue;
		resultContent = wpvq_results.appreciationContent;
	}

	// Appreciation
	$('.wpvq-appreciation-content, .wpvq-personality-content').html(resultContent);

	// Social media + local caption
	if(jQuery.wpvq_add_social_meta(wpvq_type, resultTitle, wpvq_results.appreciationContent))
	{
		$('#wpvq-final-score').css('display', 'block');
		$('#wpvq-final-personality').css('display', 'block');
	}

	// Continue with other result if multiplePersonalities
	$.each(wpvq_results, function( index, value ) {
		$('#wpvq-final-personality-lists').append('<hr />');
		$('#wpvq-final-personality-lists').append('<span class="wpvq-local-caption wpvq-you-are">'+value.label+'</span>');
		$('#wpvq-final-personality-lists').append('<div class="wpvq-personality-content"></div>'+value.content+'</div>');
	});

})(jQuery);