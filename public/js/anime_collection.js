((_) => {

	'use strict';

	const search = (query) => {
		// Show the loader
		_.$('.cssload-loader')[0].removeAttribute('hidden');

		// Do the api search
		_.get(_.url('/anime-collection/search'), {query}, (searchResults, status) => {
			searchResults = JSON.parse(searchResults);

			// Hide the loader
			_.$('.cssload-loader')[0].setAttribute('hidden', 'hidden');

			// Show the results
			_.$('#series_list')[0].innerHTML = render_anime_search_results(searchResults.data);
		});
	};

		_.on('#search', 'keyup', _.throttle(250, function() {
			const query = encodeURIComponent(this.value);
			if (query === '') {
				return;
			}

			search(query);
		}));

})(AnimeClient);