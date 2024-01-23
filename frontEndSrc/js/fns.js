/**
 * Make sure properties are in an easily splittable format
 *
 * @private
 * @param {String} props
 * @param {String} [sep='.'] The default separator
 * @return {String}
 */
function _normalizeProperty(props, sep = '.') {
	// Since we split by period, and property lookup
	// is the same by dot or [], replace bracket lookups
	// with periods
	return props.replace(/\[(.*?)]/g, sep + '$1');
}

/**
 * Tell if a nested object has a given property (or array a given index)
 * given an object such as a.b.c.d = 5, hasNestedProperty(a, 'b.c.d') will return true.
 *
 * @param {Object} object the object to get the property from
 * @param {String} property the path to the property as a string
 * @returns {boolean} true when property in object, false otherwise
 */
export function hasNestedProperty(object, property) {
	if (object && typeof object === 'object') {
		if (typeof property === 'string' && property !== '') {
			property = _normalizeProperty(property);

			let split = property.split('.');
			return split.reduce((obj, prop, idx, array) => {
				if (idx === array.length - 1) {
					return !!(obj && obj.hasOwnProperty(prop));
				}

				return obj && obj[prop];
			}, object);
		} else if (typeof property === 'number') {
			return property in object;
		}
	}

	return false;
}

/**
 * Get the value of a deeply nested property in an object
 *
 * @param {Object} object the object to get the property
 * @param {string} property the path to the property as a string
 * @param {string} [sep='.'] The default separator to split on
 * @return {*} the value of the property
 */
export function getNestedProperty(object, property, sep = '.') {
	if (isType('string', property) && property !== '') {
		// convert numbers to dot syntax
		property = _normalizeProperty(property, sep);
		const levels = property.split(sep);

		try {
			return levels.reduce((obj, prop) => obj[prop], object);
		} catch (e) {
			return undefined;
		}
	}

	return null;
}

/**
 * Reliably get the type of the value of a variable
 *
 * @param {*} x The variable to get the type of
 * @return {string} The name of the type
 */
export function getType(x) {
	// is it an array?
	if (Array.isArray(x)) {
		return 'array';
	}

	// Use typeof for truthy primitives
	if (typeof x !== 'object') {
		return (typeof x).toLowerCase();
	}

	const type = function () {
		return Object.prototype.toString.call(this).slice(8, -1);
	}

	// Otherwise, strip the type out of the '[Object x]' toString value
	return type.call(x).toLowerCase();
}

/**
 * Check whether the value matches the passed type name
 *
 * @param {string} type Javascript type name
 * @param {*} val The value to type check
 * @return {boolean}
 */
export function isType(type, val) {
	return getType(val) === String(type).toLowerCase();
}