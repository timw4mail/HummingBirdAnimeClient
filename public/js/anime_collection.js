(($, AnimeClient) => {

	'use strict';

	const search = (tempHtml, query) => {
		$('.cssload-loader').removeAttr('hidden');
		$.get(AnimeClient.url('/collection/search'), {'query':query}, (searchResults, status) => {
			$('.cssload-loader').attr('hidden', 'hidden');

			// Give mustache a key to iterate over
			searchResults = {
				anime: searchResults
			};

			Mustache.parse(tempHtml);
			$('#series_list').html(Mustache.render(tempHtml, searchResults));
		});
	};

	AnimeClient.get('/public/templates/anime-ajax-search-results.html', tempHtml => {
		AnimeClient.on('#search', 'keyup', AnimeClient.throttle(250, function(e) {
			let query = encodeURIComponent(this.value);
			if (query === '') {
				return;
			}

			search(tempHtml, query);
		}));
	});

})(Zepto, AnimeClient);