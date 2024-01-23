<?php use function Aviat\AnimeClient\renderTemplate; ?>
<main class="media-list">
<?php if ($_->isAuthenticated()): ?>
<a class="bracketed" href="<?= $_->urlFromRoute($collection_type . '.collection.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<br />
	<label>Filter: <input type='text' class='media-filter' /></label>
	<br />
	<?= $_->component->tabs('collection-tab', $sections, static function ($items) use ($_, $collection_type) {
		$rendered = [];
		foreach ($items as $item)
		{
			$rendered[] = renderTemplate(__DIR__ . '/cover-item.php', [
					'_' => $_,
					'collection_type' => $collection_type,
					'item' => $item,
			]);
		}

		return implode('', array_map('mb_trim', $rendered));
	}, 'media-wrap', true) ?>
<?php endif ?>
</main>
