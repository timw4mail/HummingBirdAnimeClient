function render_manga_search_results (data) {
	const results = [];

	data.forEach(x => {
		const item = x.attributes;
		const titles = item.titles.reduce((prev, current) => {
			return prev + `${current}<br />`;
		}, []);

		results.push(`
			<article class="media search">
				<div class="name">
					<input type="radio" class="big-check" id="${item.slug}" name="id" value="${x.id}" />
					<label for="${item.slug}">
						<img src="/public/images/manga/${x.id}.jpg" alt="" width="220" />
						<span class="name">
							${item.canonicalTitle}<br />
							<small>${titles}</small>
						</span>
					</label>
				</div>
			</article>
		`);
	});

	return results.join('');
}
