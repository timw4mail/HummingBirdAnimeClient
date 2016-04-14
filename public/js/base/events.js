/**
 * Event handlers
 */
((ac) => {

	'use strict';

	// Close event for messages
	ac.on('header', 'click', '.message', function () {
		ac.hide(this);
	});

	// Confirm deleting of list or library items
	ac.on('form.js-danger', 'submit', function (event) {
		let proceed = confirm("Are you ABSOLUTELY SURE you want to delete this item?");

		if (proceed === false) {
			event.preventDefault();
			event.stopPropagation();
		}
	});

})(AnimeClient);