<?php use function Aviat\AnimeClient\colNotEmpty; ?>
<main class="media-list">
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate('anime.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<br />
	<label>Filter: <input type='text' class='media-filter' /></label>
	<br />
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<?php if (empty($items)): ?>
		<h3>There's nothing here!</h3>
	<?php else: ?>
		<?php
			$hasNotes = colNotEmpty($items, 'notes');
		?>
		<table class='media-wrap'>
			<thead>
				<tr>
					<?php if($auth->isAuthenticated()): ?>
					<td class="no-border">&nbsp;</td>
					<?php endif ?>
					<th>Title</th>
					<th>Airing Status</th>
					<th>Score</th>
					<th>Type</th>
					<th>Progress</th>
					<th>TV Rating</th>
					<th>Attributes</th>
					<?php if($hasNotes): ?><th>Notes</th><?php endif ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach($items as $item): ?>
				<?php if ($item['private'] && ! $auth->isAuthenticated()) continue; ?>
				<tr id="a-<?= $item['id'] ?>">
					<?php if ($auth->isAuthenticated()): ?>
					<td>
						<a class="bracketed" href="<?= $url->generate('edit', [
							'controller' => 'anime',
							'id' => $item['id'],
							'status' => $item['watching_status']
						]) ?>">Edit</a>
					</td>
					<?php endif ?>
					<td class="align-left justify">
						<a href="<?= $url->generate('anime.details', ['id' => $item['anime']['slug']]) ?>">
							<?= $item['anime']['title'] ?>
						</a>
						<br />
						<?= implode('<br />', $item['anime']['titles']) ?>
					</td>
					<td><?= $item['airing']['status'] ?></td>
					<td><?= $item['user_rating'] ?> / 10 </td>
					<td><?= $item['anime']['show_type'] ?></td>
					<td id="<?= $item['anime']['slug'] ?>">
						Episodes: <br />
						<span class="completed_number"><?= $item['episodes']['watched'] ?></span>&nbsp;/&nbsp;<span class="total_number"><?= $item['episodes']['total'] ?></span>
					</td>
					<td><?= $item['anime']['age_rating'] ?></td>
					<td>
						<?php foreach($item['anime']['streaming_links'] as $link): ?>
							<?php if ($link['meta']['link'] !== FALSE): ?>
								<a href="<?= $link['link'] ?>" title="Stream '<?= $item['anime']['title'] ?>' on <?= $link['meta']['name'] ?>">
									<?= $helper->img("/public/images/{$link['meta']['image']}", [
											'class' => 'small-streaming-logo',
											'width' => 25,
											'height' => 25,
											'alt' => "{$link['meta']['name']} logo",
									]) ?>
								</a>
							<?php else: ?>
								<?= $helper->img("/public/images/{$link['meta']['image']}", [
										'class' => 'small-streaming-logo',
										'width' => 25,
										'height' => 25,
										'alt' => "{$link['meta']['name']} logo",
								]) ?>
							<?php endif ?>
						<?php endforeach ?>

						<br />

	                    <ul>
						<?php if ($item['rewatched'] > 0): ?>
							<?php if ($item['rewatched'] == 1): ?>
							<li>Rewatched once</li>
							<?php elseif ($item['rewatched'] == 2): ?>
							<li>Rewatched twice</li>
							<?php elseif ($item['rewatched'] == 3): ?>
							<li>Rewatched thrice</li>
							<?php else: ?>
							<li>Rewatched <?= $item['rewatched'] ?> times</li>
							<?php endif ?>
						<?php endif ?>
						<?php foreach(['private','rewatching'] as $attr): ?>
							<?php if($item[$attr]): ?><li><?= ucfirst($attr); ?></li><?php endif ?>
						<?php endforeach ?>
	                    </ul>
					</td>
					<?php if ($hasNotes): ?><td><p><?= $escape->html($item['notes']) ?></p></td><?php endif ?>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php endif ?>
	<?php endforeach ?>
<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js/tables.min.js') ?>"></script>