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
	<div class="tabs">
	<?php $i = 0; ?>
	<?php foreach ($sections as $name => $items): ?>
		<input type="radio" id="collection-tab-<?= $i ?>" name="collection-tabs" />
		<label for="collection-tab-<?= $i ?>"><h2><?= $name ?></h2></label>
		<div class="content full-height">
			<section class="media-wrap">
				<?php foreach ($items as $item): ?>
					<?php include __DIR__ . '/cover-item.php'; ?>
				<?php endforeach ?>
			</section>
		</div>
		<?php $i++; ?>
	<?php endforeach ?>
	<!-- All Tab -->
	<input type='radio' checked='checked' id='collection-tab-<?= $i ?>' name='collection-tabs' />
	<label for='collection-tab-<?= $i ?>'><h2>All</h2></label>
	<div class='content full-height'>
		<section class="media-wrap">
			<?php foreach ($all as $item): ?>
				<?php include __DIR__ . '/cover-item.php'; ?>
			<?php endforeach ?>
		</section>
	</div>
	</div>
<?php endif ?>
</main>
