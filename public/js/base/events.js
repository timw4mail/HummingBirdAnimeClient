/**
 * Event handlers
 */
((ac) => {

	'use strict';

	// Close event for messages
	ac.on('header', 'click', '.message', function () {
		this.setAttribute('hidden', 'hidden');
	});

})(AnimeClient);