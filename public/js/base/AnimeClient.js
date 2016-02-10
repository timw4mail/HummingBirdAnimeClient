var AnimeClient = (function(w) {

	'use strict';

	const slice = Array.prototype.slice;

	// -------------------------------------------------------------------------
	// ! Base
	// -------------------------------------------------------------------------

	function matches(elm, selector) {
		let matches = (elm.document || elm.ownerDocument).querySelectorAll(selector),
			i = matches.length;
		while (--i >= 0 && matches.item(i) !== elm);
		return i > -1;
	}

	const _ = {
		/**
		 * Placeholder function
		 */
		noop: () => {},
		/**
		 * DOM selector
		 *
		 * @param {string} selector - The dom selector string
		 * @param {object} context
		 * @return {array} - array of dom elements
		 */
		$(selector, context) {
			if (typeof selector != "string" || selector === undefined) {
				return selector;
			}

			context = (context != null && context.nodeType === 1)
				? context
				: document;

			let elements = [];
			if (selector.match(/^#([\w]+$)/)) {
				elements.push(document.getElementById(selector.split('#')[1]));
			} else {
				elements = slice.apply(context.querySelectorAll(selector));
			}

			return elements;
		},
		/**
		 * Scroll to the top of the Page
		 *
		 * @return {void}
		 */
		scrollToTop() {
			w.scroll(0,0);
		},
		/**
		 * Display a message box
		 *
		 * @param  {String} type - message type: info, error, success
		 * @param  {String} message - the message itself
		 * @return {void}
		 */
		showMessage(type, message) {
			let template =
				`<div class="message ${type}">
					<span class="icon"></span>
					${message}
					<span class="close"></span>
				</div>`;

			let sel = AnimeClient.$('.message');
			if (sel[0] !== undefined) {
				sel[0].remove();
			}

			_.$('header')[0].insertAdjacentHTML('beforeend', template);
		},
		/**
		 * Finds the closest parent element matching the passed selector
		 *
		 * @param  {DOMElement} current - the current DOMElement
		 * @param  {string} parentSelector - selector for the parent element
		 * @return {DOMElement|null} - the parent element
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
		 * @param  {String} path - url path
		 * @return {String} - full url
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
		 * @param {Object} scope - the 'this' object for the function
		 * @return {void}
		 */
		throttle(interval, fn, scope) {
			var wait = false;
			return function () {
				var context = scope || this;
				var args = arguments;

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
			_.$(target, sel).forEach((element) => {
				if(e.target == element) {
					listener.call(element, e);
					e.stopPropagation();
				}
			});
		});
	}

	_.on = function (sel, event, target, listener) {
		if (arguments.length === 3) {
			listener = target;
			_.$(sel).forEach((el) => {
				addEvent(el, event, listener);
			});
		} else {
			_.$(sel).forEach((el) => {
				delegateEvent(el, target, event, listener);
			});
		}
	}

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

		return pairs.join("&");
	};

	_.ajax = function(url, config) {
		// Set some sane defaults
		config = config || {};
		config.data = config.data || {};
		config.type = config.type || 'GET';
		config.dataType = config.dataType || '';
		config.success = config.success || _.noop;
		config.error = config.error || _.noop;

		let request = new XMLHttpRequest();
		let method = String(config.type).toUpperCase();

		if (method === "GET") {
			url += (url.match(/\?/))
				? ajaxSerialize(config.data)
				: `?${ajaxSerialize(config.data)}`;
		}

		request.open(method, url);

		request.onreadystatechange = () => {
			if (request.readyState === 4) {
				let responseText = '';

				if (request.responseType == 'json') {
					responseText = JSON.parse(request.responseText);
				} else {
					responseText = request.responseText;
				}

				if (request.status > 400) {
					config.error.call(null, request.status, responseText, request.response);
				} else {
					config.success.call(null, responseText, request.status);
				}
			}
		};

		switch (method) {
			case "GET":
				request.send(null);
			break;

			default:
				request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				request.send(ajaxSerialize(config.data));
			break;
		}
	};

	_.get = function(url, data, callback) {
		if (arguments.length === 2) {
			callback = data;
			data = {};
		}

		return _.ajax(url, {
			data: data,
			success: callback
		});
	};

	// -------------------------------------------------------------------------
	// Export
	// -------------------------------------------------------------------------

	return _;
})(window);