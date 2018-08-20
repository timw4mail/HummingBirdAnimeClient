module.exports = function filter(filename) {
	return ! String(filename).includes('min');
}