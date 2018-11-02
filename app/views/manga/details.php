<main class="details fixed">
	<section class="flex flex-no-wrap">
		<aside class="info">
			<?= $helper->picture("images/manga/{$data['id']}-original.webp", 'jpg', ['class' => 'cover']) ?>

			<br />

			<table class="media_details">
				<tr>
					<td>Manga Type</td>
					<td><?= ucfirst($data['manga_type']) ?></td>
				</tr>
				<tr>
					<td>Volume Count</td>
					<td><?= $data['volume_count'] ?></td>
				</tr>
				<tr>
					<td>Chapter Count</td>
					<td><?= $data['chapter_count'] ?></td>
				</tr>
				<tr>
					<td>Genres</td>
					<td>
						<?= implode(', ', $data['genres']); ?>
					</td>
				</tr>
			</table>
		</aside>
		<article class="text">
			<h2 class="toph"><a rel="external" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php foreach ($data['titles'] as $title): ?>
				<h3><?= $title ?></h3>
			<?php endforeach ?>

			<br />
			<p><?= nl2br($data['synopsis']) ?></p>
		</article>
	</section>

	<?php if (count($characters) > 0): ?>
		<h2>Characters</h2>
		<div class="tabs">
			<?php $i = 0 ?>
			<?php foreach ($characters as $role => $list): ?>
				<input
					type="radio" name="character-role-tabs"
					id="character-tabs<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
				<label for="character-tabs<?= $i ?>"><?= ucfirst($role) ?></label>
				<section class="content media-wrap flex flex-wrap flex-justify-start">
					<?php foreach ($list as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
							<article class="<?= $role === 'supporting' ? 'small_' : '' ?>character">
								<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
								<div class="name">
									<?= $helper->a($link, $char['name']); ?>
								</div>
								<a href="<?= $link ?>">
									<?= $helper->picture("images/characters/{$id}.webp") ?>
								</a>
							</article>
						<?php endif ?>
					<?php endforeach ?>
				</section>
				<?php $i++ ?>
			<?php endforeach ?>
		</div>
	<?php endif ?>

	<?php if (count($staff) > 0): ?>
		<h2>Staff</h2>

		<div class="vertical-tabs">
			<?php $i = 0 ?>
			<?php foreach ($staff as $role => $people): ?>
				<div class="tab">
					<input
						type="radio" name="staff-roles" id="staff-role<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="staff-role<?= $i ?>"><?= $role ?></label>
					<section class='content media-wrap flex flex-wrap flex-justify-start'>
						<?php foreach ($people as $pid => $person): ?>
							<article class='character person'>
								<?php $link = $url->generate('person', ['id' => $pid]) ?>
								<div class="name">
									<a href="<?= $link ?>">
										<?= $person['name'] ?>
									</a>
								</div>
								<a href="<?= $link ?>">
									<?= $helper->picture("images/people/{$pid}.webp") ?>
								</a>
							</article>
						<?php endforeach ?>
					</section>
				</div>
				<?php $i++ ?>
			<?php endforeach ?>
		</div>
	<?php endif ?>
</main>