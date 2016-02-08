AnimeClient = (function (ac) {

	/**
	 * Url encoding for non-get requests
	 *
	 * @param data
	 * @returns {string}
	 * @private
	 */
	function serialize(data) {
		let pairs = [];

		Object.keys(data).forEach((name) => {
			let value = data[name].toString();

			name = encodeURIComponent(name);
			value = encodeURIComponent(value);

			pairs.push(`${name}=${value}`);
		});

		return pairs.join("&");
	};

	ac.ajax = function(url, config) {
		// Set some sane defaults
		config = config || {};
		config.data = config.data || {};
		config.type = config.type || 'GET';
		config.dataType = config.dataType || 'json';
		config.success = config.success || ac.noop;
		config.error = config.error || ac.noop;

		let request = new XMLHttpRequest();
		let method = String(config.type).toUpperCase();

		if (method === "GET") {
			url += (url.match(/\?/))
				? serialize(config.data)
				: `?${serialize(config.data)}`;
		}

		request.open(method, url);

		request.onreadystatechange = () => {
			if (request.readyState === 4) {
				if (request.status > 400) {
					config.error.call(request.statusText, request.statusText, request.response);
				} else {
					config.success.call(request.responseText, request.responseText, request.status);
				}
			}
		};

		switch (method) {
			case "GET":
				request.send(null);
			break;

			default:
				request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				request.send(serialize(config.data));
			break;
		}
	};

	ac.get = function(url, data, callback) {
		if (arguments.length === 2) {
			callback = data;
			data = {};
		}

		return ac.ajax(url, {
			data: data,
			success: callback
		});
	};

	return ac;

})(AnimeClient);