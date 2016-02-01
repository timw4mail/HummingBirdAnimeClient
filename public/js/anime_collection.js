(($, AnimeClient) => {

	'use strict';

	function search(query, callback)
	{
		var endpoint = '//' + document.location.host + '/collection/search';
		return $.get(AnimeClient.url('/collection/search'), {'query':query}, callback);
	}

	$.get('/public/templates/ajax-search-results.html').done(tempHtml => {
		$('#search').on('keypress', $.throttle(750, function(e) {
			$('.cssload-loader').removeAttr('hidden');
			let query = encodeURIComponent($(this).val());
			$.get(AnimeClient.url('/collection/search'), {'query':query}).done((searchResults) => {
				$('.cssload-loader').attr('hidden', 'hidden');

				// Give mustache a key to iterate over
				searchResults = {
					anime: searchResults
				};

				Mustache.parse(tempHtml);
				$('#series_list').html(Mustache.render(tempHtml, searchResults));
			}).fail(() => {
				$('.cssload-loader').attr('hidden', 'hidden');
			});
		}));
	});

	/*$.get('/public/templates/ajax-search-results.html', tempHtml => {
		$('#search').on('keypress', $.throttle(750, function(e) {
			var query = encodeURIComponent($(this).val());
			search(query, function(searchResults) {
				// Give mustache a key to iterate over
				searchResults = {
					anime: searchResults
				};

				Mustache.parse(tempHtml);
				var rendered = Mustache.render(tempHtml, searchResults);
				$('#series_list').html(rendered);
			});
		}));
	});*/
})(jQuery, AnimeClient);