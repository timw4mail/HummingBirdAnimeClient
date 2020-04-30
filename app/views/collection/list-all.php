<input type='radio' checked='checked' id='collection-tab-<?= $i ?>' name='collection-tabs' />
<label for='collection-tab-<?= $i ?>'><h2>All</h2></label>
<div class="content full-height">
	<table class="full-width media-wrap">
		<thead>
		<tr>
			<?php if ($auth->isAuthenticated()): ?><td>&nbsp;</td><?php endif ?>
			<th>Title</th>
			<th>Media</th>
			<th>Episode Count</th>
			<th>Episode Length</th>
			<th>Show Type</th>
			<th>Age Rating</th>
			<th>Notes</th>
			<th>Genres</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($all as $item): ?>
			<?php $editLink = $url->generate($collection_type . '.collection.edit.get', ['id' => $item['hummingbird_id']]); ?>
			<tr>
				<?php if ($auth->isAuthenticated()): ?>
					<td>
						<a class="bracketed" href="<?= $editLink ?>">Edit</a>
					</td>
				<?php endif ?>
				<td class="align-left">
					<a href="<?= $url->generate('anime.details', ['id' => $item['slug']]) ?>">
						<?= $item['title'] ?>
					</a>
					<?= ! empty($item['alternate_title']) ? ' <br /><small> ' . $item['alternate_title'] . '</small>' : '' ?>
				</td>
				<td><?= implode(', ', $item['media']) ?></td>
				<td><?= ($item['episode_count'] > 1) ? $item['episode_count'] : '-'  ?></td>
				<td><?= $item['episode_length'] ?></td>
				<td><?= $item['show_type'] ?></td>
				<td><?= $item['age_rating'] ?></td>
				<td class="align-left"><?= $item['notes'] ?></td>
				<td class="align-left"><?= implode(', ', $item['genres']) ?></td>
			</tr>
		<?php endforeach ?>
		</tbody>
	</table>
</div>