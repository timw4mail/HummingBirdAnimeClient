// -------------------------------------------------------------------------
// ! Base
// -------------------------------------------------------------------------

const matches = (elm, selector) => {
	let matches = (elm.document || elm.ownerDocument).querySelectorAll(selector),
		i = matches.length;
	while (--i >= 0 && matches.item(i) !== elm);
	return i > -1;
}

export const AnimeClient = {
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
	hasElement(selector) {
		return AnimeClient.$(selector).length > 0;
	},
	/**
	 * Scroll to the top of the Page
	 *
	 * @return {void}
	 */
	scrollToTop() {
		window.scroll(0,0);
	},
	/**
	 * Hide the selected element
	 *
	 * @param  {string|Element} sel - the selector of the element to hide
	 * @return {void}
	 */
	hide(sel) {
		sel.setAttribute('hidden', 'hidden');
	},
	/**
	 * UnHide the selected element
	 *
	 * @param  {string|Element} sel - the selector of the element to hide
	 * @return {void}
	 */
	show(sel) {
		sel.removeAttribute('hidden');
	},
	/**
	 * Display a message box
	 *
	 * @param  {string} type - message type: info, error, success
	 * @param  {string} message - the message itself
	 * @return {void}
	 */
	showMessage(type, message) {
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
	closestParent(current, parentSelector) {
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
	url(path) {
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
	throttle(interval, fn, scope) {
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
AnimeClient.on = function (sel, event, target, listener) {
	if (arguments.length === 3) {
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
AnimeClient.ajax = function(url, config) {
	// Set some sane defaults
	config = config || {};
	config.data = config.data || {};
	config.type = config.type || 'GET';
	config.dataType = config.dataType || '';
	config.success = config.success || AnimeClient.noop;
	config.mimeType = config.mimeType || 'application/x-www-form-urlencoded';
	config.error = config.error || AnimeClient.noop;

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

	switch (method) {
		case 'GET':
			request.send(null);
		break;

		default:
			request.send(config.data);
		break;
	}
};

/**
 * Do a get request
 *
 * @param {string} url
 * @param {object|function} data
 * @param {function} [callback]
 */
AnimeClient.get = function(url, data, callback = null) {
	if (callback === null) {
		callback = data;
		data = {};
	}

	return AnimeClient.ajax(url, {
		data,
		success: callback
	});
};

// -------------------------------------------------------------------------
// Export
// -------------------------------------------------------------------------

export default AnimeClient;