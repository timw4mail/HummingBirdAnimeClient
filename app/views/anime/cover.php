<main class="media-list">
<?php if ($_->isAuthenticated()): ?>
<a class="bracketed" href="<?= $_->urlFromRoute('anime.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<br />
	<label>Filter: <input type='text' class='media-filter' /></label>
	<br />
	<?php foreach ($sections as $name => $items): ?>
	<?php if (empty($items)): ?>
		<section class="status">
			<h2><?= $_->escape->html($name) ?></h2>
			<h3>There's nothing here!</h3>
		</section>
	<?php else: ?>
		<section class="status">
			<h2><?= $_->escape->html($name) ?></h2>
			<section class="media-wrap">
				<?php foreach($items as $item): ?>
				<?php if ($item['private'] && ! $_->isAuthenticated()) continue; ?>
					<?= $_->component->animeCover($item) ?>
				<?php endforeach ?>
			</section>
		</section>
	<?php endif ?>
	<?php endforeach ?>
<?php endif ?>
</main>
