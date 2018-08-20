import _ from './base/AnimeClient';
import { render_manga_search_results } from './manga_search_results'

const search = (query) => {
	_.$('.cssload-loader')[0].removeAttribute('hidden');
	_.get(_.url('/manga/search'), {query}, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);
		_.$('.cssload-loader')[0].setAttribute('hidden', 'hidden');
		_.$('#series_list')[0].innerHTML = render_manga_search_results(searchResults.data);
	});
};

if (_.hasElement('.manga #search')) {
	_.on('#search', 'keyup', _.throttle(250, function (e) {
		let query = encodeURIComponent(this.value);
		if (query === '') {
			return;
		}

		search(query);
	}));
}


