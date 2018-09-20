<main class="media-list">
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate($collection_type . '.collection.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<div class="tabs">
	<?php $i = 0; ?>
	<?php foreach ($sections as $name => $items): ?>
		<input <?= $i === 0 ? 'checked="checked"' : '' ?> type="radio" id="collection-tab-<?= $i ?>" name="collection-tabs" />
		<label for="collection-tab-<?= $i ?>"><h2><?= $name ?></h2></label>
		<div class="content">
			<section class="media-wrap">
				<?php foreach ($items as $item): ?>
					<?php include __DIR__ . '/cover-item.php'; ?>
				<?php endforeach ?>
			</section>
		</div>
		<?php $i++; ?>
	<?php endforeach ?>
	</div>
<?php endif ?>
</main>
