<article class="media" data-kitsu-id="<?= $item['id'] ?>" data-mal-id="<?= $item['mal_id'] ?>">
	<?php if ($_->isAuthenticated()): ?>
		<div class="edit-buttons" hidden>
			<button class="plus-one-chapter">+1 Chapter</button>
		</div>
	<?php endif ?>
	<?= $_->h->img($item['manga']['image'], ['width' => 220, 'loading' => 'lazy']) ?>
	<div class="name">
		<a href="<?= $_->urlFromRoute('manga.details', ['id' => $item['manga']['slug']]) ?>">
			<?= $_->escape->html($item['manga']['title']) ?>
			<?php foreach($item['manga']['titles'] as $title): ?>
				<br /><small><?= $title ?></small>
			<?php endforeach ?>
		</a>
	</div>
	<div class="table">
		<?php if ($_->isAuthenticated()): ?>
			<div class="row">
							<span class="edit">
								<a class="bracketed"
								   title="Edit information about this manga"
								   href="<?= $_->urlFromRoute('edit', [
									   'controller' => 'manga',
									   'id' => $item['id'],
									   'status' => $name
								   ]) ?>">
									Edit
								</a>
							</span>
			</div>
		<?php endif ?>
		<div class="row">
			<div><?= $item['manga']['type'] ?></div>
			<div class="user-rating">Rating: <?= $item['user_rating'] ?> / 10</div>
		</div>

		<?php if ($item['rereading']): ?>
			<div class="row">
				<?php foreach(['rereading'] as $attr): ?>
					<?php if($item[$attr]): ?>
						<span class="item-<?= $attr ?>"><?= ucfirst($attr) ?></span>
					<?php endif ?>
				<?php endforeach ?>
			</div>
		<?php endif ?>

		<?php if ($item['reread'] > 0): ?>
			<div class="row">
				<?php if ($item['reread'] == 1): ?>
					<div>Reread once</div>
				<?php elseif ($item['reread'] == 2): ?>
					<div>Reread twice</div>
				<?php elseif ($item['reread'] == 3): ?>
					<div>Reread thrice</div>
				<?php else: ?>
					<div>Reread <?= $item['reread'] ?> times</div>
				<?php endif ?>
			</div>
		<?php endif ?>

		<div class="row">
			<div class="chapter_completion">
				Chapters: <span class="chapters_read"><?= $item['chapters']['read'] ?></span> /
				<span class="chapter_count"><?= $item['chapters']['total'] ?></span>
			</div>
		</div>
	</div>
</article>