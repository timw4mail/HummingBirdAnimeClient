import _ from './anime-client.js'
import { renderAnimeSearchResults } from './template-helpers.js'

const search = (query) => {
	// Show the loader
	_.show('.cssload-loader');

	// Do the api search
	_.get(_.url('/anime-collection/search'), { query }, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);

		// Hide the loader
		_.hide('.cssload-loader');

		// Show the results
		_.$('#series-list')[ 0 ].innerHTML = renderAnimeSearchResults(searchResults.data);
	});
};

if (_.hasElement('.anime #search')) {
	_.on('#search', 'input', _.throttle(250, (e) => {
		const query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		search(query);
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
		mal_id: parentSel.dataset.malId,
		data: {
			progress: watchedCount + 1
		}
	};

	// If the episode count is 0, and incremented,
	// change status to currently watching
	if (isNaN(watchedCount) || watchedCount === 0) {
		data.data.status = 'current';
	}

	// If you increment at the last episode, mark as completed
	if ((!isNaN(watchedCount)) && (watchedCount + 1) === totalCount) {
		data.data.status = 'completed';
	}

	_.show('#loading-shadow');

	// okay, lets actually make some changes!
	_.ajax(_.url('/anime/increment'), {
		data,
		dataType: 'json',
		type: 'POST',
		success: (res) => {
			const resData = JSON.parse(res);

			if (resData.errors) {
				_.hide('#loading-shadow');
				_.showMessage('error', `Failed to update ${title}. `);
				_.scrollToTop();
				return;
			}

			if (resData.data.attributes.status === 'completed') {
				_.hide(parentSel);
			}

			_.hide('#loading-shadow');

			_.showMessage('success', `Successfully updated ${title}`);
			_.$('.completed_number', parentSel)[ 0 ].textContent = ++watchedCount;
			_.scrollToTop();
		},
		error: () => {
			_.hide('#loading-shadow');
			_.showMessage('error', `Failed to update ${title}. `);
			_.scrollToTop();
		}
	});
});