<main class="details fixed">
	<section class="flex flex-no-wrap">
		<aside class="info">
			<?= $helper->picture("images/manga/{$data['id']}-original.webp", 'jpg', ['class' => 'cover']) ?>

			<br />

			<table class="media-details">
				<tr>
					<td class="align-right">Publishing Status</td>
					<td><?= $data['status'] ?></td>
				</tr>
				<tr>
					<td>Manga Type</td>
					<td><?= ucfirst(strtolower($data['manga_type'])) ?></td>
				</tr>
				<?php if ( ! empty($data['volume_count'])): ?>
				<tr>
					<td>Volume Count</td>
					<td><?= $data['volume_count'] ?></td>
				</tr>
				<?php endif ?>
				<?php if ( ! empty($data['chapter_count'])): ?>
				<tr>
					<td>Chapter Count</td>
					<td><?= $data['chapter_count'] ?></td>
				</tr>
				<?php endif ?>

				<?php if ( ! empty($data['age_rating'])): ?>
					<tr>
						<td>Age Rating</td>
						<td><abbr title="<?= $data['age_rating_guide'] ?>"><?= $data['age_rating'] ?></abbr>
						</td>
					</tr>
				<?php endif ?>
				<tr>
					<td>Genres</td>
					<td>
						<?= implode(', ', $data['genres']); ?>
					</td>
				</tr>
			</table>

			<br />
		</aside>
		<article class="text">
			<h2 class="toph"><a rel="external" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php foreach ($data['titles_more'] as $title): ?>
				<h3><?= $title ?></h3>
			<?php endforeach ?>

			<br />
			<div class="description">
				<p><?= str_replace("\n", '</p><p>', $data['synopsis']) ?></p>
			</div>
		</article>
	</section>

	<?php if (count($data['characters']) > 0): ?>
		<h2>Characters</h2>

		<div class="tabs">
			<?php $i = 0 ?>
			<?php foreach ($data['characters'] as $role => $list): ?>
				<input
					type="radio" name="character-role-tabs"
					id="character-tabs<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
				<label for="character-tabs<?= $i ?>"><?= ucfirst($role) ?></label>
				<section class="content media-wrap flex flex-wrap flex-justify-start">
					<?php foreach ($list as $id => $char): ?>
						<?php if ( ! empty($char['image']['original'])): ?>
							<article class="<?= $role === 'supporting' ? 'small-' : '' ?>character">
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

	<?php if (count($data['staff']) > 0): ?>
		<h2>Staff</h2>

		<div class="vertical-tabs">
			<?php $i = 0 ?>
			<?php foreach ($data['staff'] as $role => $people): ?>
				<div class="tab">
					<input
						type="radio" name="staff-roles" id="staff-role<?= $i ?>" <?= $i === 0 ? 'checked' : '' ?> />
					<label for="staff-role<?= $i ?>"><?= $role ?></label>
					<section class='content media-wrap flex flex-wrap flex-justify-start'>
						<?php foreach ($people as $person): ?>
							<article class='character person'>
								<?php $link = $url->generate('person', ['id' => $person['id'], 'slug' => $person['slug']]) ?>
								<div class="name">
									<a href="<?= $link ?>">
										<?= $person['name'] ?>
									</a>
								</div>
								<a href="<?= $link ?>">
									<?= $helper->picture("images/people/{$person['id']}.webp") ?>
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