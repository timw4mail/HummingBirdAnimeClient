<main class="details fixed">
	<section class="flex flex-no-wrap">
		<aside class="info">
			<picture class="cover">
				<source srcset="<?= $urlGenerator->assetUrl("images/manga/{$data['id']}-original.webp") ?>" type="image/webp">
				<source srcset="<?= $urlGenerator->assetUrl("images/manga/{$data['id']}-original.jpg") ?>" type="image/jpeg">
				<img src="<?= $urlGenerator->assetUrl("images/manga/{$data['id']}-original.jpg") ?>" alt="" />
			</picture>
			<br />
			<br />
			<table>
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
			<h2><a rel="external" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php foreach($data['titles'] as $title): ?>
				<h3><?= $title ?></h3>
			<?php endforeach ?>

			<br />
			<p><?= nl2br($data['synopsis']) ?></p>
		</article>
	</section>

	<?php if (count($characters) > 0): ?>
		<br />
		<hr />
		<h2>Characters</h2>
		<?php foreach ($characters as $role => $list): ?>
			<h3><?= ucfirst($role) ?></h3>
			<section class="media-wrap flex flex-wrap flex-justify-start">
				<?php foreach ($list as $id => $char): ?>
					<?php if ( ! empty($char['image']['original'])): ?>
						<article class="character">
							<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
							<div class="name">
								<?= $helper->a($link, $char['name']); ?>
							</div>
							<a href="<?= $link ?>">
								<picture>
									<source
										srcset="<?= $urlGenerator->assetUrl("images/characters/{$id}.webp") ?>"
										type="image/webp"
									>
									<source
										srcset="<?= $urlGenerator->assetUrl("images/characters/{$id}.jpg") ?>"
										type="image/jpeg"
									>
									<img src="<?= $urlGenerator->assetUrl("images/characters/{$id}.jpg") ?>" alt="" />
								</picture>
							</a>
						</article>
					<?php endif ?>
				<?php endforeach ?>
			</section>
		<?php endforeach ?>
	<?php endif ?>
</main>