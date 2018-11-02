import _ from './AnimeClient.js';
/**
 * Event handlers
 */
// Close event for messages
_.on('header', 'click', '.message', (e) => {
	_.hide(e.target);
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

// Alleviate some page jumping
 _.on('.vertical-tabs input', 'change', (event) => {
	const el = event.currentTarget.parentElement;
	const rect = el.getBoundingClientRect();

	const top = rect.top + window.pageYOffset;

	window.scrollTo({
		top,
		behavior: 'smooth',
	});
});
