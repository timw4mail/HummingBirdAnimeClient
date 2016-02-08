(($, AnimeClient) => {

	'use strict';

	const search = (tempHtml, query) => {
		$('.cssload-loader').removeAttr('hidden');
		AnimeClient.get(AnimeClient.url('/manga/search'), {'query':query,}, (searchResults, status) => {
			searchResults = JSON.parse(searchResults);
			$('.cssload-loader').attr('hidden', 'hidden');

			Mustache.parse(tempHtml);
			$('#series_list').html(Mustache.render(tempHtml, searchResults));
		});
	};

	AnimeClient.get('/public/templates/manga-ajax-search-results.html', tempHtml => {
		AnimeClient.on('#search', 'keyup', AnimeClient.throttle(250, function(e) {
			let query = encodeURIComponent(this.value);
			if (query === '') {
				return;
			}

			search(tempHtml, query);
		}));
	});

})(Zepto, AnimeClient);