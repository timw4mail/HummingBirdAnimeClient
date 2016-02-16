/**
 * Event handlers
 */
((ac) => {

	'use strict';

	// Close event for messages
	ac.on('header', 'click', '.message', function () {
		ac.hide(this);
	});

})(AnimeClient);