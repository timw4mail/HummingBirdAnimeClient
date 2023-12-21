<main class="details fixed">
	<section class="flex flex-no-wrap">
		<aside class="info">
			<?= $_->h->img($data['cover_image'], ['class' => 'cover', 'width' => '350']) ?>

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

				<?php if (count($data['links']) > 0): ?>
					<tr>
						<td>External Links</td>
						<td>
							<?php foreach ($data['links'] as $urlName => $externalUrl): ?>
								<a rel='external' href="<?= $externalUrl ?>"><?= $urlName ?></a><br />
							<?php endforeach ?>
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
			<h2 class="toph"><?= $data['title'] ?></h2>
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

		<?= $component->tabs('manga-characters', $data['characters'], static function($list, $role) use ($component, $helper, $_) {
			$rendered = [];
			foreach ($list as $id => $char)
			{
				$rendered[] = $component->character(
					$char['name'],
					$_->urlFromRoute('character', ['slug' => $char['slug']]),
					$_->h->img($char['image'], ['loading' => 'lazy']),
					($role !== 'main') ? 'small-character' : 'character'
				);
			}

			return implode('', array_map('mb_trim', $rendered));
		}) ?>
	<?php endif ?>

	<?php if (count($data['staff']) > 0): ?>
		<h2>Staff</h2>

		<?= $component->verticalTabs('manga-staff', $data['staff'],
				fn($people) => implode('', array_map(
						fn ($person) => $component->character(
							$person['name'],
							$_->urlFromRoute('person', ['slug' => $person['slug']]),
							$_->h->img($person['image']),
						),
						$people
				))
		) ?>
	<?php endif ?>
</main>