<tr>
	<?php if ($auth->isAuthenticated()): ?>
		<td>
			<a class="bracketed"
			   href="<?= $url->generate($collection_type . '.collection.edit.get', ['id' => $item['hummingbird_id']]) ?>">Edit</a>
		</td>
	<?php endif ?>
	<td class="align-left">
		<a href="<?= $url->generate('anime.details', ['id' => $item['slug']]) ?>">
			<?= $item['title'] ?>
		</a>
		<?= ! empty($item['alternate_title']) ? ' <br /><small> ' . $item['alternate_title'] . '</small>' : '' ?>
	</td>
	<td><?= $item['episode_count'] ?></td>
	<td><?= ($item['episode_count'] > 1) ? $item['episode_count'] : '-'  ?></td>
	<td><?= $item['show_type'] ?></td>
	<td><?= $item['age_rating'] ?></td>
	<?php if ($hasNotes): ?><td class="align-left"><?= $item['notes'] ?></td><?php endif ?>
	<td class="align-left"><?= implode(', ', $item['genres']) ?></td>
</tr>