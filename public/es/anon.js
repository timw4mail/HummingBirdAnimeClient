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
 * @return {XMLHttpRequest}
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

	return request
};

/**
 * Do a get request
 *
 * @param {string} url
 * @param {object|function} data
 * @param {function} [callback]
 * @return {XMLHttpRequest}
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
