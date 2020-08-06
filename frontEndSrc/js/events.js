import _ from './anime-client.js';

// ----------------------------------------------------------------------------
// Event subscriptions
// ----------------------------------------------------------------------------
_.on('header', 'click', '.message', hide);
_.on('form.js-delete', 'submit', confirmDelete);
_.on('.js-clear-cache', 'click', clearAPICache);
_.on('.vertical-tabs input', 'change', scrollToSection);
_.on('.media-filter', 'input', filterMedia);

// ----------------------------------------------------------------------------
// Handler functions
// ----------------------------------------------------------------------------

/**
 * Hide the html element attached to the event
 *
 * @param event
 * @return void
 */
function hide (event) {
	_.hide(event.target)
}

/**
 * Confirm deletion of an item
 *
 * @param event
 * @return void
 */
function confirmDelete (event) {
	const proceed = confirm('Are you ABSOLUTELY SURE you want to delete this item?');

	if (proceed === false) {
		event.preventDefault();
		event.stopPropagation();
	}
}

/**
 * Clear the API cache, and show a message if the cache is cleared
 *
 * @return void
 */
function clearAPICache () {
	_.get('/cache_purge', () => {
		_.showMessage('success', 'Successfully purged api cache');
	});
}

/**
 * Scroll to the accordion/vertical tab section just opened
 *
 * @param event
 * @return void
 */
function scrollToSection (event) {
	const el = event.currentTarget.parentElement;
	const rect = el.getBoundingClientRect();

	const top = rect.top + window.pageYOffset;

	window.scrollTo({
		top,
		behavior: 'smooth',
	});
}

/**
 * Filter an anime or manga list
 *
 * @param event
 * @return void
 */
function filterMedia (event) {
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
		_.show('article.media');
		_.show('table.media-wrap tbody tr');
	}
}

// ----------------------------------------------------------------------------
// Other event setup
// ----------------------------------------------------------------------------
(() => {
	// Var is intentional
	var hidden = null;
	var visibilityChange = null;

	if (typeof document.hidden !== "undefined") {
		hidden = "hidden";
		visibilityChange = "visibilitychange";
	} else if (typeof document.msHidden !== "undefined") {
		hidden = "msHidden";
		visibilityChange = "msvisibilitychange";
	} else if (typeof document.webkitHidden !== "undefined") {
		hidden = "webkitHidden";
		visibilityChange = "webkitvisibilitychange";
	}

	function handleVisibilityChange() {
		// Check the user's session to see if they are currently logged-in
		// when the page becomes visible
		if ( ! document[hidden]) {
			_.get('/heartbeat', (beat) => {
				const status = JSON.parse(beat)

				// If the session is expired, immediately reload so that
				// you can't attempt to do an action that requires authentication
				if (status.hasAuth !== true) {
					location.reload();
				}
			});
		}
	}

	if (hidden === null) {
		console.info('Page visibility API not supported, JS session check will not work');
	} else {
		document.addEventListener(visibilityChange, handleVisibilityChange, false);
	}
})();
