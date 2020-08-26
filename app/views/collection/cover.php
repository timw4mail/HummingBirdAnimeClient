<?php use function Aviat\AnimeClient\renderTemplate; ?>
<main class="media-list">
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate($collection_type . '.collection.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<br />
	<label>Filter: <input type='text' class='media-filter' /></label>
	<br />
	<?= $component->tabs('collection-tab', $sections, static function ($items) use ($auth, $collection_type, $helper, $url, $component) {
		$rendered = [];
		foreach ($items as $item)
		{
			$rendered[] = renderTemplate(__DIR__ . '/cover-item.php', [
					'auth' => $auth,
					'collection_type' => $collection_type,
					'helper' => $helper,
					'item' => $item,
					'url' => $url,
			]);
		}

		return implode('', array_map('mb_trim', $rendered));
	}, 'media-wrap', true) ?>
<?php endif ?>
</main>
