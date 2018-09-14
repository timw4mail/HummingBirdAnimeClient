import _ from './AnimeClient.js';
/**
 * Event handlers
 */
// Close event for messages
_.on('header', 'click', '.message', function () {
	_.hide(this);
});

// Confirm deleting of list or library items
_.on('form.js-delete', 'submit', (event) => {
	const proceed = confirm('Are you ABSOLUTELY SURE you want to delete this item?');

	if (proceed === false) {
		event.preventDefault();
		event.stopPropagation();
	}
});

// Clear the api cache
_.on('.js-clear-cache', 'click', () => {
	_.get('/cache_purge', () => {
		_.showMessage('success', 'Successfully purged api cache');
	});
});
