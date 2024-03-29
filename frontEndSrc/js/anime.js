import _ from './anime-client.js'
import { renderSearchResults } from './template-helpers.js'
import { getNestedProperty, hasNestedProperty } from "./fns";

const search = (query, isCollection = false) => {
	// Show the loader
	_.show('.cssload-loader');

	// Do the api search
	return _.get(_.url('/anime-collection/search'), { query }, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);

		// Hide the loader
		_.hide('.cssload-loader');

		// Show the results
		_.$('#series-list')[ 0 ].innerHTML = renderSearchResults('anime', searchResults, isCollection);
	});
};

// Anime list search
if (_.hasElement('.anime #search')) {
	let prevRequest = null;

	_.on('#search', 'input', _.throttle(250, (e) => {
		const query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		if (prevRequest !== null) {
			prevRequest.abort();
		}

		prevRequest = search(query);
	}));
}

// Anime collection search
if (_.hasElement('#search-anime-collection')) {
	let prevRequest = null;

	_.on('#search-anime-collection', 'input', _.throttle(250, (e) => {
		const query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		if (prevRequest !== null) {
			prevRequest.abort();
		}

		prevRequest = search(query, true);
	}));
}

// Action to increment episode count
_.on('body.anime.list', 'click', '.plus-one', (e) => {
	let parentSel = _.closestParent(e.target, 'article');
	let watchedCount = parseInt(_.$('.completed_number', parentSel)[ 0 ].textContent, 10) || 0;
	let totalCount = parseInt(_.$('.total_number', parentSel)[ 0 ].textContent, 10);
	let title = _.$('.name a', parentSel)[ 0 ].textContent;

	// Setup the update data
	let data = {
		id: parentSel.dataset.kitsuId,
		anilist_id: parentSel.dataset.anilistId,
		mal_id: parentSel.dataset.malId,
		data: {
			progress: watchedCount + 1
		}
	};

	const displayMessage = (type, message) => {
		_.hide('#loading-shadow');
		_.showMessage(type, `${message} ${title}`);
		_.scrollToTop();
	}

	const showError = () => displayMessage('error', 'Failed to update');

	// If the episode count is 0, and incremented,
	// change status to currently watching
	if (isNaN(watchedCount) || watchedCount === 0) {
		data.data.status = 'CURRENT';
	}

	// If you increment at the last episode, mark as completed
	if ((!isNaN(watchedCount)) && (watchedCount + 1) === totalCount) {
		data.data.status = 'COMPLETED';
	}

	_.show('#loading-shadow');

	// okay, lets actually make some changes!
	_.ajax(_.url('/anime/increment'), {
		data,
		dataType: 'json',
		type: 'POST',
		success: (res) => {
			try {
				const resData = JSON.parse(res);

				// Do a rough sanity check for weird errors
				let updatedProgress = getNestedProperty(resData, 'data.libraryEntry.update.libraryEntry.progress');
				if (hasNestedProperty(resData, 'error') || updatedProgress !== data.data.progress) {
					showError();
					return;
				}

				// We've completed the series
				if (getNestedProperty(resData, 'data.libraryEntry.update.libraryEntry.status') === 'COMPLETED') {
					_.hide(parentSel);
					displayMessage('success', 'Completed')

					return;
				}

				// Just a normal update
				_.$('.completed_number', parentSel)[ 0 ].textContent = ++watchedCount;
				displayMessage('success', 'Updated');
			} catch (_) {
				showError();
			}
		},
		error: showError,
	});
});