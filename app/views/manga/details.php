<main class="details fixed">
	<section class="flex flex-no-wrap">
		<div>
			<img class="cover" src="<?= $data['cover_image'] ?>" alt="<?= $data['title'] ?> cover image" />
			<br />
			<br />
			<table>
				<tr>
					<td>Manga Type</td>
					<td><?= $data['manga_type'] ?></td>
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
		</div>
		<div>
			<h2><a rel="external" href="<?= $data['url'] ?>"><?= $data['title'] ?></a></h2>
			<?php if( ! empty($data['en_title'])): ?>
				<h3><?= $data['en_title'] ?></h3>
			<?php endif ?>

			<br />
			<p><?= nl2br($data['synopsis']) ?></p>
		</div>
	</section>
	<section>
	<?php if (count($characters) > 0): ?>
	<h2>Characters</h2>
	<div class="flex flex-wrap">
	<?php foreach($characters as $char): ?>
		<?php if ( ! empty($char['image']['original'])): ?>
		<div class="character">
			<?php $link = $url->generate('character', ['slug' => $char['slug']]) ?>
			<?= $helper->a($link, $char['name']); ?>
			<br />
			<a href="<?= $link ?>">
			<?= $helper->img($char['image']['original'], [
				'width' => '225'
			]) ?>
			</a>
		</div>
		<?php endif ?>
	<?php endforeach ?>
	</div>
	<?php endif ?>
	</section>

</main>