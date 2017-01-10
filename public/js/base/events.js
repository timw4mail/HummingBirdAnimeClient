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
	ac.on('form.js-delete', 'submit', function (event) {
		let proceed = confirm("Are you ABSOLUTELY SURE you want to delete this item?");

		if (proceed === false) {
			event.preventDefault();
			event.stopPropagation();
		}
	});

	// Clear the api cache
	ac.on('.js-clear-cache', 'click', function () {
		ac.get('/cache_purge', () => {
			ac.showMessage('success', `Sucessfully purged api cache`);
		});
	});

})(AnimeClient);