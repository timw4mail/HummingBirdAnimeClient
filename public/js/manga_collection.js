((_) => {

	'use strict';

	const search = (tempHtml, query) => {
		_.$('.cssload-loader')[0].removeAttribute('hidden');
		_.get(_.url('/manga/search'), {'query':query,}, (searchResults, status) => {
			searchResults = JSON.parse(searchResults);
			_.$('.cssload-loader')[0].setAttribute('hidden', 'hidden');

			Mustache.parse(tempHtml);
			_.$('#series_list')[0].innerHTML = Mustache.render(tempHtml, searchResults);
		});
	};

	_.get('/public/templates/manga-ajax-search-results.html', tempHtml => {
		_.on('#search', 'keyup', _.throttle(250, function(e) {
			let query = encodeURIComponent(this.value);
			if (query === '') {
				return;
			}

			search(tempHtml, query);
		}));
	});

})(AnimeClient);