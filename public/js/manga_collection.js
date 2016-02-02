(($, AnimeClient) => {

	'use strict';

	const search = (tempHtml, query) => {
		$('.cssload-loader').removeAttr('hidden');
		$.get(AnimeClient.url('/manga/search'), {'query':query,}, (searchResults, status) => {
			$('.cssload-loader').attr('hidden', 'hidden');

			Mustache.parse(tempHtml);
			$('#series_list').html(Mustache.render(tempHtml, searchResults));
		});
	};

	$.get('/public/templates/manga-ajax-search-results.html', tempHtml => {
		$('#search').on('keyup', AnimeClient.throttle(250, function(e) {
			let query = encodeURIComponent($(this).val());
			if (query === '') {
				return;
			}

			search(tempHtml, query);
		}));
	});

})(Zepto, AnimeClient);