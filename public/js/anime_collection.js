(function($, undefined) {

	function search(query, callback)
	{
		$.get(BASE_URL + 'collection/search', {'query':query}, callback);
	}

	$("#search").on('keypress', $.throttle(750, function(e) {
		var query = encodeURIComponent($(this).val());
		search(query, function(res) {
			var template = $.templates("#show_list");
			var html = template.render(res);
			$('#series_list').html(html);
		});
	}));

}(jQuery));