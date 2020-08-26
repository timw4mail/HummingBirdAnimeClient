<?php use function Aviat\AnimeClient\colNotEmpty; ?>
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
		<?php $i = 0; ?>
		<div class="tabs">
			<?php foreach ($sections as $name => $items): ?>
				<?php $hasNotes = colNotEmpty($items, 'notes') ?>
				<input type="radio" id="collection-tab-<?= $i ?>" name="collection-tabs" />
				<label for="collection-tab-<?= $i ?>"><h2><?= $name ?></h2></label>
				<div class="content full-height">
					<table class="full-width media-wrap">
						<thead>
							<tr>
								<?php if ($auth->isAuthenticated()): ?><td>&nbsp;</td><?php endif ?>
								<th>Title</th>
								<th>Episode Count</th>
								<th>Episode Length</th>
								<th>Show Type</th>
								<th>Age Rating</th>
								<?php if ($hasNotes): ?><th>Notes</th><?php endif ?>
								<th>Genres</th>
							</tr>
						</thead>
						<tbody>
						<?php foreach ($items as $item): ?>
							<?php include 'list-item.php' ?>
						<?php endforeach ?>
						</tbody>
					</table>
				</div>
				<?php $i++ ?>
			<?php endforeach ?>
			<!-- All -->
			<?php include 'list-all.php' ?>
		</div>
	<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js/tables.min.js') ?>"></script>