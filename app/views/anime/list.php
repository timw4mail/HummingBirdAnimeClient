<main class="media-list">
<?php if ($auth->isAuthenticated()): ?>
<a class="bracketed" href="<?= $url->generate('anime.add.get') ?>">Add Item</a>
<?php endif ?>
<?php if (empty($sections)): ?>
<h3>There's nothing here!</h3>
<?php else: ?>
	<?php foreach ($sections as $name => $items): ?>
	<h2><?= $name ?></h2>
	<?php if (empty($items)): ?>
		<h3>There's nothing here!</h3>
	<?php else: ?>
		<table>
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
					<th>Rated</th>
					<th colspan="2">Attributes</th>
					<th>Notes</th>
					<th>Genres</th>
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
					<td class="justify">
						<a href="<?= $url->generate('anime.details', ['id' => $item['anime']['slug']]) ?>">
							<?= $item['anime']['title'] ?>
						</a>
						<?php foreach ($item['anime']['titles'] as $title): ?>
							<br/><?= $title ?>
						<?php endforeach ?>
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
	                    <ul>
						<?php if ($item['rewatched'] > 0): ?>
	                        <li>Rewatched <?= $item['rewatched'] ?> time(s)</li>
						<?php endif ?>
						<?php foreach(['private','rewatching'] as $attr): ?>
							<?php if($item[$attr]): ?>
	                            <li><?= ucfirst($attr); ?></li>
							<?php endif ?>
						<?php endforeach ?>
	                    </ul>
					</td>
					<td>
						<?php foreach($item['anime']['streaming_links'] as $link): ?>
							<?php if ($link['meta']['link'] !== FALSE): ?>
								<a href="<?= $link['link'] ?>" title="Stream '<?= $item['anime']['title'] ?>' on <?= $link['meta']['name'] ?>">
									<?= $helper->picture("images/{$link['meta']['image']}", 'svg', [
										'class' => 'streaming-logo',
										'width' => 50,
										'height' => 50,
										'alt' => "{$link['meta']['name']} logo",
									]); ?>
								</a>
							<?php else: ?>
								<?= $helper->picture("images/{$link['meta']['image']}", 'svg', [
									'class' => 'streaming-logo',
									'width' => 50,
									'height' => 50,
									'alt' => "{$link['meta']['name']} logo",
								]); ?>
							<?php endif ?>
						<?php endforeach ?>
					</td>
					<td>
						<p><?= $escape->html($item['notes']) ?></p>
					</td>
					<td class="align-left">
						<?php sort($item['anime']->genres) ?>
						<?= implode(', ', $item['anime']->genres) ?>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	<?php endif ?>
	<?php endforeach ?>
<?php endif ?>
</main>
<script defer="defer" src="<?= $urlGenerator->assetUrl('js/tables.min.js') ?>"></script>