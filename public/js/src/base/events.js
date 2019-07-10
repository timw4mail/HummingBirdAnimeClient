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

// Filter the current page (cover view)
_.on('.media-filter', 'input', (event) => {
	const rawFilter = event.target.value;
	const filter = new RegExp(rawFilter, 'i');

	// console.log('Filtering items by: ', filter);

	if (rawFilter !== '') {
		// Filter the cover view
		_.$('article.media').forEach(article => {
			const titleLink = _.$('.name a', article)[0];
			const title = String(titleLink.textContent).trim();
			if ( ! filter.test(title)) {
				_.hide(article);
			} else {
				_.show(article);
			}
		});

		// Filter the list view
		_.$('table.media-wrap tbody tr').forEach(tr => {
			const titleCell = _.$('td.align-left', tr)[0];
			const titleLink = _.$('a', titleCell)[0];
			const linkTitle = String(titleLink.textContent).trim();
			const textTitle = String(titleCell.textContent).trim();
			if ( ! (filter.test(linkTitle) || filter.test(textTitle))) {
				_.hide(tr);
			} else {
				_.show(tr);
			}
		});
	} else {
		_.$('article.media').forEach(article => _.show(article));
		_.$('table.media-wrap tbody tr').forEach(tr => _.show(tr));
	}
});
