<?php

use function Aviat\AnimeClient\friendlyTime;

?>
<main class="details fixed">
	<section class="flex">
		<aside class="info">
			<?= $_->h->img($data['cover_image'], ['width' => '390']) ?>

			<br />

			<table class="media-details">
				<tr>
					<td class="align-right">Airing Status</td>
					<td><?= $data['status'] ?></td>
				</tr>

				<?php if ( ! empty($data['airDate'])): ?>
				<tr>
					<td>Original Airing</td>
					<td><?= $data['airDate'] ?></td>
				</tr>
				<?php endif ?>

				<tr>
					<td>Show Type</td>
					<td><?= (strlen($data['show_type']) > 3) ? ucfirst(strtolower($data['show_type'])) : $data['show_type'] ?></td>
				</tr>

				<?php if ($data['episode_count'] !== 1): ?>
					<tr>
						<td>Episode Count</td>
						<td><?= $data['episode_count'] ?? '-' ?></td>
					</tr>
				<?php endif ?>

				<?php if (( ! empty($data['episode_length'])) && $data['episode_count'] !== 1): ?>
					<tr>
						<td>Episode Length</td>
						<td><?= friendlyTime($data['episode_length']) ?></td>
					</tr>
				<?php endif ?>

				<?php if (isset($data['total_length'], $data['episode_count']) && $data['total_length'] > 0): ?>
					<tr>
						<td>Total Length</td>
						<td><?= friendlyTime($data['total_length']) ?></td>
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
						<?= implode(', ', $data['genres']) ?>
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
			<?php if (count($data['streaming_links']) > 0): ?>
				<hr />
				<h4>Streaming on:</h4>
				<table class="full-width invisible streaming-links">
					<thead>
					<tr>
						<th class="align-left">Service</th>
						<th>Subtitles</th>
						<th>Dubs</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($data['streaming_links'] as $link): ?>
						<tr>
							<td class="align-left">
								<?php if ($link['meta']['link'] !== FALSE): ?>
									<a
										href="<?= $link['link'] ?>"
										title="Stream '<?= $data['title'] ?>' on <?= $link['meta']['name'] ?>"
									>
										<?= $_->h->img("/public/images/{$link['meta']['image']}", [
											'class' => 'streaming-logo',
											'width' => 50,
											'height' => 50,
											'alt' => "{$link['meta']['name']} logo",
										]) ?>
										&nbsp;&nbsp;<?= $link['meta']['name'] ?>
									</a>
								<?php else: ?>
									<?= $_->h->img("/public/images/{$link['meta']['image']}", [
										'class' => 'streaming-logo',
										'width' => 50,
										'height' => 50,
										'alt' => "{$link['meta']['name']} logo",
									]) ?>
									&nbsp;&nbsp;<?= $link['meta']['name'] ?>
								<?php endif ?>
							</td>
							<td><?= implode(', ', array_map(fn ($sub) => Locale::getDisplayLanguage($sub, 'en'), $link['subs'])) ?></td>
							<td><?= implode(', ', array_map(fn ($dub) => Locale::getDisplayLanguage($dub, 'en'), $link['dubs'])) ?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			<?php endif ?>
			<?php if ( ! empty($data['trailer_id'])): ?>
				<div class="responsive-iframe">
					<h4>Trailer</h4>
					<iframe
						width="560"
						height="315"
						role='img'
						src="https://www.youtube.com/embed/<?= $data['trailer_id'] ?>"
						allow="autoplay; encrypted-media"
						allowfullscreen
						tabindex='0'
						title="<?= $data['title'] ?> trailer video"
					></iframe>
				</div>
			<?php endif ?>
		</article>
	</section>

	<?php if (count($data['characters']) > 0): ?>
		<section>
			<h2>Characters</h2>

			<?= $_->component->tabs('character-types', $data['characters'], static function ($characterList, $role)
			use ($_) {
				$rendered = [];
				foreach ($characterList as $id => $character):
					if (empty($character['image']))
					{
						continue;
					}
					$rendered[] = $_->component->character(
						$character['name'],
						$_->urlFromRoute('character', ['slug' => $character['slug']]),
						$_->h->img($character['image']),
						(strtolower($role) !== 'main') ? 'small-character' : 'character'
					);
				endforeach;

				return implode('', array_map('mb_trim', $rendered));
			}) ?>
		</section>
	<?php endif ?>

	<?php if (count($data['staff']) > 0): ?>
		<section>
			<h2>Staff</h2>

			<?= $_->component->verticalTabs('staff-role', $data['staff'], static function ($staffList)
			use ($_) {
				$rendered = [];
				foreach ($staffList as $id => $person):
					if (empty($person['image']))
					{
						continue;
					}
					$rendered[] = $_ ->component->character(
						$person['name'],
						$_->urlFromRoute('person', ['slug' => $person['slug']]),
						$_->h->img($person['image']),
						'character small-person',
					);
				endforeach;

				return implode('', array_map('mb_trim', $rendered));
			}) ?>
		</section>
	<?php endif ?>
</main>