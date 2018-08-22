const LightTableSorter = (() => {
	let th = null;
	let cellIndex = null;
	let order = '';
	const text = (row) => row.cells.item(cellIndex).textContent.toLowerCase();
	const sort = (a, b) => {
		let textA = text(a);
		let textB = text(b);
		const n = parseInt(textA, 10);
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
	const toggle = () => {
		const c = order !== 'sorting_asc' ? 'sorting_asc' : 'sorting_desc';
		th.className = (th.className.replace(order, '') + ' ' + c).trim();
		return order = c;
	};
	const reset = () => {
		th.classList.remove('sorting_asc', 'sorting_desc');
		th.classList.add('sorting');
		return order = '';
	};
	const onClickEvent = (e) => {
		if (th && (cellIndex !== e.target.cellIndex)) {
			reset();
		}
		th = e.target;
		if (th.nodeName.toLowerCase() === 'th') {
			cellIndex = th.cellIndex;
			const tbody = th.offsetParent.getElementsByTagName('tbody')[0];
			let rows = Array.from(tbody.rows);
			if (rows) {
				rows.sort(sort);
				if (order === 'sorting_asc') {
					rows.reverse();
				}
				toggle();
				tbody.innerHtml = '';

				rows.forEach(row => {
					tbody.appendChild(row);
				});
			}
		}
	};
	return {
		init: () => {
			let ths = document.getElementsByTagName('th');
			let results = [];
			for (let i = 0, len = ths.length; i < len; i++) {
				let th = ths[i];
				th.classList.add('sorting');
				results.push(th.onclick = onClickEvent);
			}
			return results;
		}
	};
})();

LightTableSorter.init();