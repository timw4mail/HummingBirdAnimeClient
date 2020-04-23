// -------------------------------------------------------------------------
// ! Base
// -------------------------------------------------------------------------

const matches = (elm, selector) => {
	let m = (elm.document || elm.ownerDocument).querySelectorAll(selector);
	let i = matches.length;
	while (--i >= 0 && m.item(i) !== elm) {}	return i > -1;
};

const AnimeClient = {
	/**
	 * Placeholder function
	 */
	noop: () => {},
	/**
	 * DOM selector
	 *
	 * @param {string} selector - The dom selector string
	 * @param {object} [context]
	 * @return {[HTMLElement]} - array of dom elements
	 */
	$(selector, context = null) {
		if (typeof selector !== 'string') {
			return selector;
		}

		context = (context !== null && context.nodeType === 1)
			? context
			: document;

		let elements = [];
		if (selector.match(/^#([\w]+$)/)) {
			elements.push(document.getElementById(selector.split('#')[1]));
		} else {
			elements = [].slice.apply(context.querySelectorAll(selector));
		}

		return elements;
	},
	/**
	 * Does the selector exist on the current page?
	 *
	 * @param {string} selector
	 * @returns {boolean}
	 */
	hasElement (selector) {
		return AnimeClient.$(selector).length > 0;
	},
	/**
	 * Scroll to the top of the Page
	 *
	 * @return {void}
	 */
	scrollToTop () {
		const el = AnimeClient.$('header')[0];
		el.scrollIntoView(true);
	},
	/**
	 * Hide the selected element
	 *
	 * @param  {string|Element} sel - the selector of the element to hide
	 * @return {void}
	 */
	hide (sel) {
		if (typeof sel === 'string') {
			sel = AnimeClient.$(sel);
		}

		if (Array.isArray(sel)) {
			sel.forEach(el => el.setAttribute('hidden', 'hidden'));
		} else {
			sel.setAttribute('hidden', 'hidden');
		}
	},
	/**
	 * UnHide the selected element
	 *
	 * @param  {string|Element} sel - the selector of the element to hide
	 * @return {void}
	 */
	show (sel) {
		if (typeof sel === 'string') {
			sel = AnimeClient.$(sel);
		}

		if (Array.isArray(sel)) {
			sel.forEach(el => el.removeAttribute('hidden'));
		} else {
			sel.removeAttribute('hidden');
		}
	},
	/**
	 * Display a message box
	 *
	 * @param  {string} type - message type: info, error, success
	 * @param  {string} message - the message itself
	 * @return {void}
	 */
	showMessage (type, message) {
		let template =
			`<div class='message ${type}'>
				<span class='icon'></span>
				${message}
				<span class='close'></span>
			</div>`;

		let sel = AnimeClient.$('.message');
		if (sel[0] !== undefined) {
			sel[0].remove();
		}

		AnimeClient.$('header')[0].insertAdjacentHTML('beforeend', template);
	},
	/**
	 * Finds the closest parent element matching the passed selector
	 *
	 * @param  {HTMLElement} current - the current HTMLElement
	 * @param  {string} parentSelector - selector for the parent element
	 * @return {HTMLElement|null} - the parent element
	 */
	closestParent (current, parentSelector) {
		if (Element.prototype.closest !== undefined) {
			return current.closest(parentSelector);
		}

		while (current !== document.documentElement) {
			if (matches(current, parentSelector)) {
				return current;
			}

			current = current.parentElement;
		}

		return null;
	},
	/**
	 * Generate a full url from a relative path
	 *
	 * @param  {string} path - url path
	 * @return {string} - full url
	 */
	url (path) {
		let uri = `//${document.location.host}`;
		uri += (path.charAt(0) === '/') ? path : `/${path}`;

		return uri;
	},
	/**
	 * Throttle execution of a function
	 *
	 * @see https://remysharp.com/2010/07/21/throttling-function-calls
	 * @see https://jsfiddle.net/jonathansampson/m7G64/
	 * @param {Number} interval - the minimum throttle time in ms
	 * @param {Function} fn - the function to throttle
	 * @param {Object} [scope] - the 'this' object for the function
	 * @return {Function}
	 */
	throttle (interval, fn, scope) {
		let wait = false;
		return function (...args) {
			const context = scope || this;

			if ( ! wait) {
				fn.apply(context, args);
				wait = true;
				setTimeout(function() {
					wait = false;
				}, interval);
			}
		};
	},
};

// -------------------------------------------------------------------------
// ! Events
// -------------------------------------------------------------------------

function addEvent(sel, event, listener) {
	// Recurse!
	if (! event.match(/^([\w\-]+)$/)) {
		event.split(' ').forEach((evt) => {
			addEvent(sel, evt, listener);
		});
	}

	sel.addEventListener(event, listener, false);
}

function delegateEvent(sel, target, event, listener) {
	// Attach the listener to the parent
	addEvent(sel, event, (e) => {
		// Get live version of the target selector
		AnimeClient.$(target, sel).forEach((element) => {
			if(e.target == element) {
				listener.call(element, e);
				e.stopPropagation();
			}
		});
	});
}

/**
 * Add an event listener
 *
 * @param  {string|HTMLElement} sel - the parent selector to bind to
 * @param  {string} event - event name(s) to bind
 * @param  {string|HTMLElement|function} target - the element to directly bind the event to
 * @param  {function} [listener] - event listener callback
 * @return {void}
 */
AnimeClient.on = (sel, event, target, listener) => {
	if (listener === undefined) {
		listener = target;
		AnimeClient.$(sel).forEach((el) => {
			addEvent(el, event, listener);
		});
	} else {
		AnimeClient.$(sel).forEach((el) => {
			delegateEvent(el, target, event, listener);
		});
	}
};

// -------------------------------------------------------------------------
// ! Ajax
// -------------------------------------------------------------------------

/**
 * Url encoding for non-get requests
 *
 * @param data
 * @returns {string}
 * @private
 */
function ajaxSerialize(data) {
	let pairs = [];

	Object.keys(data).forEach((name) => {
		let value = data[name].toString();

		name = encodeURIComponent(name);
		value = encodeURIComponent(value);

		pairs.push(`${name}=${value}`);
	});

	return pairs.join('&');
}

/**
 * Make an ajax request
 *
 * Config:{
 * 	data: // data to send with the request
 * 	type: // http verb of the request, defaults to GET
 * 	success: // success callback
 * 	error: // error callback
 * }
 *
 * @param  {string} url - the url to request
 * @param  {Object} config  - the configuration object
 * @return {void}
 */
AnimeClient.ajax = (url, config) => {
	// Set some sane defaults
	const defaultConfig = {
		data: {},
		type: 'GET',
		dataType: '',
		success: AnimeClient.noop,
		mimeType: 'application/x-www-form-urlencoded',
		error: AnimeClient.noop
	};

	config = {
		...defaultConfig,
		...config,
	};

	let request = new XMLHttpRequest();
	let method = String(config.type).toUpperCase();

	if (method === 'GET') {
		url += (url.match(/\?/))
			? ajaxSerialize(config.data)
			: `?${ajaxSerialize(config.data)}`;
	}

	request.open(method, url);

	request.onreadystatechange = () => {
		if (request.readyState === 4) {
			let responseText = '';

			if (request.responseType === 'json') {
				responseText = JSON.parse(request.responseText);
			} else {
				responseText = request.responseText;
			}

			if (request.status > 299) {
				config.error.call(null, request.status, responseText, request.response);
			} else {
				config.success.call(null, responseText, request.status);
			}
		}
	};

	if (config.dataType === 'json') {
		config.data = JSON.stringify(config.data);
		config.mimeType = 'application/json';
	} else {
		config.data = ajaxSerialize(config.data);
	}

	request.setRequestHeader('Content-Type', config.mimeType);

	if (method === 'GET') {
		request.send(null);
	} else {
		request.send(config.data);
	}
};

/**
 * Do a get request
 *
 * @param {string} url
 * @param {object|function} data
 * @param {function} [callback]
 */
AnimeClient.get = (url, data, callback = null) => {
	if (callback === null) {
		callback = data;
		data = {};
	}

	return AnimeClient.ajax(url, {
		data,
		success: callback
	});
};

// ----------------------------------------------------------------------------
// Event subscriptions
// ----------------------------------------------------------------------------
AnimeClient.on('header', 'click', '.message', hide);
AnimeClient.on('form.js-delete', 'submit', confirmDelete);
AnimeClient.on('.js-clear-cache', 'click', clearAPICache);
AnimeClient.on('.vertical-tabs input', 'change', scrollToSection);
AnimeClient.on('.media-filter', 'input', filterMedia);

// ----------------------------------------------------------------------------
// Handler functions
// ----------------------------------------------------------------------------

/**
 * Hide the html element attached to the event
 *
 * @param event
 * @return void
 */
function hide (event) {
	AnimeClient.hide(event.target);
}

/**
 * Confirm deletion of an item
 *
 * @param event
 * @return void
 */
function confirmDelete (event) {
	const proceed = confirm('Are you ABSOLUTELY SURE you want to delete this item?');

	if (proceed === false) {
		event.preventDefault();
		event.stopPropagation();
	}
}

/**
 * Clear the API cache, and show a message if the cache is cleared
 *
 * @return void
 */
function clearAPICache () {
	AnimeClient.get('/cache_purge', () => {
		AnimeClient.showMessage('success', 'Successfully purged api cache');
	});
}

/**
 * Scroll to the accordion/vertical tab section just opened
 *
 * @param event
 * @return void
 */
function scrollToSection (event) {
	const el = event.currentTarget.parentElement;
	const rect = el.getBoundingClientRect();

	const top = rect.top + window.pageYOffset;

	window.scrollTo({
		top,
		behavior: 'smooth',
	});
}

/**
 * Filter an anime or manga list
 *
 * @param event
 * @return void
 */
function filterMedia (event) {
	const rawFilter = event.target.value;
	const filter = new RegExp(rawFilter, 'i');

	// console.log('Filtering items by: ', filter);

	if (rawFilter !== '') {
		// Filter the cover view
		AnimeClient.$('article.media').forEach(article => {
			const titleLink = AnimeClient.$('.name a', article)[0];
			const title = String(titleLink.textContent).trim();
			if ( ! filter.test(title)) {
				AnimeClient.hide(article);
			} else {
				AnimeClient.show(article);
			}
		});

		// Filter the list view
		AnimeClient.$('table.media-wrap tbody tr').forEach(tr => {
			const titleCell = AnimeClient.$('td.align-left', tr)[0];
			const titleLink = AnimeClient.$('a', titleCell)[0];
			const linkTitle = String(titleLink.textContent).trim();
			const textTitle = String(titleCell.textContent).trim();
			if ( ! (filter.test(linkTitle) || filter.test(textTitle))) {
				AnimeClient.hide(tr);
			} else {
				AnimeClient.show(tr);
			}
		});
	} else {
		AnimeClient.show('article.media');
		AnimeClient.show('table.media-wrap tbody tr');
	}
}

if ('serviceWorker' in navigator) {
	navigator.serviceWorker.register('/sw.js').then(reg => {
		console.log('Service worker registered', reg.scope);
	}).catch(error => {
		console.error('Failed to register service worker', error);
	});
}

// Click on hidden MAL checkbox so
// that MAL id is passed
AnimeClient.on('main', 'change', '.big-check', (e) => {
	const id = e.target.id;
	document.getElementById(`mal_${id}`).checked = true;
});

function renderAnimeSearchResults (data) {
	const results = [];

	data.forEach(x => {
		const item = x.attributes;
		const titles = item.titles.join('<br />');

		results.push(`
			<article class="media search">
				<div class="name">
					<input type="radio" class="mal-check" id="mal_${item.slug}" name="mal_id" value="${x.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/anime/${x.id}.webp" type="image/webp" />
							<source srcset="/public/images/anime/${x.id}.jpg" type="image/jpeg" />
							<img src="/public/images/anime/${x.id}.jpg" alt="" width="220" />
						</picture>
						<span class="name">
							${item.canonicalTitle}<br />
							<small>${titles}</small>
						</span>
					</label>
				</div>
				<div class="table">
					<div class="row">
						<span class="edit">
							<a class="bracketed" href="/anime/details/${item.slug}">Info Page</a>
						</span>
					</div>
				</div>
			</article>
		`);
	});

	return results.join('');
}

function renderMangaSearchResults (data) {
	const results = [];

	data.forEach(x => {
		const item = x.attributes;
		const titles = item.titles.join('<br />');

		results.push(`
			<article class="media search">
				<div class="name">
					<input type="radio" id="mal_${item.slug}" name="mal_id" value="${x.mal_id}" />
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<picture width="220">
							<source srcset="/public/images/manga/${x.id}.webp" type="image/webp" />
							<source srcset="/public/images/manga/${x.id}.jpg" type="image/jpeg" />
							<img src="/public/images/manga/${x.id}.jpg" alt="" width="220" />
						</picture>
						<span class="name">
							${item.canonicalTitle}<br />
							<small>${titles}</small>
						</span>
					</label>
				</div>
				<div class="table">
					<div class="row">
						<span class="edit">
							<a class="bracketed" href="/manga/details/${item.slug}">Info Page</a>
						</span>
					</div>
				</div>
			</article>
		`);
	});

	return results.join('');
}

const search = (query) => {
	// Show the loader
	AnimeClient.show('.cssload-loader');

	// Do the api search
	AnimeClient.get(AnimeClient.url('/anime-collection/search'), { query }, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);

		// Hide the loader
		AnimeClient.hide('.cssload-loader');

		// Show the results
		AnimeClient.$('#series-list')[ 0 ].innerHTML = renderAnimeSearchResults(searchResults.data);
	});
};

if (AnimeClient.hasElement('.anime #search')) {
	AnimeClient.on('#search', 'input', AnimeClient.throttle(250, (e) => {
		const query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		search(query);
	}));
}

// Action to increment episode count
AnimeClient.on('body.anime.list', 'click', '.plus-one', (e) => {
	let parentSel = AnimeClient.closestParent(e.target, 'article');
	let watchedCount = parseInt(AnimeClient.$('.completed_number', parentSel)[ 0 ].textContent, 10) || 0;
	let totalCount = parseInt(AnimeClient.$('.total_number', parentSel)[ 0 ].textContent, 10);
	let title = AnimeClient.$('.name a', parentSel)[ 0 ].textContent;

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

	AnimeClient.show('#loading-shadow');

	// okay, lets actually make some changes!
	AnimeClient.ajax(AnimeClient.url('/anime/increment'), {
		data,
		dataType: 'json',
		type: 'POST',
		success: (res) => {
			const resData = JSON.parse(res);

			if (resData.errors) {
				AnimeClient.hide('#loading-shadow');
				AnimeClient.showMessage('error', `Failed to update ${title}. `);
				AnimeClient.scrollToTop();
				return;
			}

			if (resData.data.attributes.status === 'completed') {
				AnimeClient.hide(parentSel);
			}

			AnimeClient.hide('#loading-shadow');

			AnimeClient.showMessage('success', `Successfully updated ${title}`);
			AnimeClient.$('.completed_number', parentSel)[ 0 ].textContent = ++watchedCount;
			AnimeClient.scrollToTop();
		},
		error: () => {
			AnimeClient.hide('#loading-shadow');
			AnimeClient.showMessage('error', `Failed to update ${title}. `);
			AnimeClient.scrollToTop();
		}
	});
});

const search$1 = (query) => {
	AnimeClient.show('.cssload-loader');
	AnimeClient.get(AnimeClient.url('/manga/search'), { query }, (searchResults, status) => {
		searchResults = JSON.parse(searchResults);
		AnimeClient.hide('.cssload-loader');
		AnimeClient.$('#series-list')[ 0 ].innerHTML = renderMangaSearchResults(searchResults.data);
	});
};

if (AnimeClient.hasElement('.manga #search')) {
	AnimeClient.on('#search', 'input', AnimeClient.throttle(250, (e) => {
		let query = encodeURIComponent(e.target.value);
		if (query === '') {
			return;
		}

		search$1(query);
	}));
}

/**
 * Javascript for editing manga, if logged in
 */
AnimeClient.on('.manga.list', 'click', '.edit-buttons button', (e) => {
	let thisSel = e.target;
	let parentSel = AnimeClient.closestParent(e.target, 'article');
	let type = thisSel.classList.contains('plus-one-chapter') ? 'chapter' : 'volume';
	let completed = parseInt(AnimeClient.$(`.${type}s_read`, parentSel)[ 0 ].textContent, 10) || 0;
	let total = parseInt(AnimeClient.$(`.${type}_count`, parentSel)[ 0 ].textContent, 10);
	let mangaName = AnimeClient.$('.name', parentSel)[ 0 ].textContent;

	if (isNaN(completed)) {
		completed = 0;
	}

	// Setup the update data
	let data = {
		id: parentSel.dataset.kitsuId,
		mal_id: parentSel.dataset.malId,
		data: {
			progress: completed
		}
	};

	// If the episode count is 0, and incremented,
	// change status to currently reading
	if (isNaN(completed) || completed === 0) {
		data.data.status = 'current';
	}

	// If you increment at the last chapter, mark as completed
	if ((!isNaN(completed)) && (completed + 1) === total) {
		data.data.status = 'completed';
	}

	// Update the total count
	data.data.progress = ++completed;

	AnimeClient.show('#loading-shadow');

	AnimeClient.ajax(AnimeClient.url('/manga/increment'), {
		data,
		dataType: 'json',
		type: 'POST',
		mimeType: 'application/json',
		success: () => {
			if (data.data.status === 'completed') {
				AnimeClient.hide(parentSel);
			}

			AnimeClient.hide('#loading-shadow');

			AnimeClient.$(`.${type}s_read`, parentSel)[ 0 ].textContent = completed;
			AnimeClient.showMessage('success', `Successfully updated ${mangaName}`);
			AnimeClient.scrollToTop();
		},
		error: () => {
			AnimeClient.hide('#loading-shadow');
			AnimeClient.showMessage('error', `Failed to update ${mangaName}`);
			AnimeClient.scrollToTop();
		}
	});
});
