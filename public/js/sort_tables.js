const LightTableSorter = (function() {
	let _cellIndex, _onClickEvent, _order, _reset, _sort, _text, _th, _toggle;
	_th = null;
	_cellIndex = null;
	_order = '';
	_text = function(row) {
		return row.cells.item(_cellIndex).textContent.toLowerCase();
	};
	_sort = function(a, b) {
		let n, textA, textB;
		textA = _text(a);
		textB = _text(b);
		n = parseInt(textA, 10);
		if (n) {
			textA = n;
			textB = parseInt(textB, 10);
		}
		if (textA > textB) {
			return 1;
		}
		if (textA < textB) {
			return -1;
		}
		return 0;
	};
	_toggle = function() {
		let c;
		c = _order !== 'sorting_asc' ? 'sorting_asc' : 'sorting_desc';
		_th.className = (_th.className.replace(_order, '') + ' ' + c).trim();
		return _order = c;
	};
	_reset = function() {
		_th.className = _th.className.replace('sorting_asc', 'sorting').replace('sorting_desc', 'sorting');
		return _order = '';
	};
	_onClickEvent = function(e) {
		let row, rows, tbody, _i, _len;
		if (_th && (_cellIndex !== e.target.cellIndex)) {
			_reset();
		}
		_th = e.target;
		if (_th.nodeName.toLowerCase() === 'th') {
			_cellIndex = _th.cellIndex;
			tbody = _th.offsetParent.getElementsByTagName('tbody')[0];
			rows = tbody.rows;
			if (rows) {
				rows = Array.prototype.slice.call(rows, 0);
				rows = Array.prototype.sort.call(rows, _sort);
				if (_order === 'sorting_asc') {
					Array.prototype.reverse.call(rows);
				}
				_toggle();
				tbody.innerHtml = '';
				for (_i = 0, _len = rows.length; _i < _len; _i++) {
					row = rows[_i];
					tbody.appendChild(row);
				}
			}
		}
	};
	return {
		init: function() {
			let ths = document.getElementsByTagName('th');
			let _results = [];
			for (let _i = 0, _len = ths.length; _i < _len; _i++) {
				let th = ths[_i];
				th.className = 'sorting';
				_results.push(th.onclick = _onClickEvent);
			}
			return _results;
		}
	};
})();

LightTableSorter.init();