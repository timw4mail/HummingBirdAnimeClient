<?php use function Aviat\AnimeClient\{colNotEmpty, renderTemplate}; ?>
<main>
	<?php if ($auth->isAuthenticated()): ?>
		<a class="bracketed" href="<?= $url->generate($collection_type . '.collection.add.get') ?>">Add Item</a>
	<?php endif ?>
	<?php if (empty($sections)): ?>
		<h3>There's nothing here!</h3>
	<?php else: ?>
		<br />
		<label>Filter: <input type='text' class='media-filter' /></label>
		<br />
		<?= $component->tabs('collection-tab', $sections, static function ($items, $section) use ($auth, $helper, $url, $collection_type) {
			$hasNotes = colNotEmpty($items, 'notes');
			$hasMedia = $section === 'All';
			$firstTh = ($auth->isAuthenticated()) ? '<td>&nbsp;</td>' : '';
			$mediaTh = ($hasMedia) ? '<th>Media</th>' : '';
			$noteTh = ($hasNotes) ? '<th>Notes</th>' : '';

			$rendered = [];
			foreach ($items as $item)
			{
				$rendered[] = renderTemplate(__DIR__ . '/list-item.php', [
						'auth' => $auth,
						'collection_type' => $collection_type,
						'hasMedia' => $hasMedia,
						'hasNotes' => $hasNotes,
						'helper' => $helper,
						'item' => $item,
						'url' => $url,
				]);
			}
			$rows = implode('', array_map('mb_trim', $rendered));

			return <<<HTML
				<table class="full-width media-wrap">
					<thead>
						<tr>
							{$firstTh}
							<th>Title</th>
							{$mediaTh}
							<th>Episode Count</th>
							<th>Episode Length</th>
							<th>Show Type</th>
							<th>Age Rating</th>
							{$noteTh}
							<th>Genres</th>
						</tr>
					</thead>
					<tbody>{$rows}</tbody>
				</table>
HTML;

		}) ?>
	<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js/tables.min.js') ?>"></script>