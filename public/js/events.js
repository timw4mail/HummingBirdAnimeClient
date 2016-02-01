/**
 * jQuery event handlers
 */
(($) => {

	'use strict';

	// Close event for messages
	$('header').on('click', '.message', function() {
		$(this).hide();
	});

})(jQuery);