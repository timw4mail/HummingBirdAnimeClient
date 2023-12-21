<?php
use Aviat\AnimeClient\Kitsu;
?>
<main class="user-page details">
	<h2 class="toph">
		About
		<?= $_->h->a(
				"https://kitsu.io/users/{$data['slug']}",
				$data['name'], [
				'title' => 'View profile on Kitsu'
		])
		?>
	</h2>

	<section class="flex flex-no-wrap">
		<aside class="info">
			<table class="media-details invisible">
				<tr>
					<?php if($data['avatar'] !== null): ?>
					<td><?= $_->h->img($data['avatar'], ['alt' => '', 'width' => '225']); ?></td>
					<?php endif ?>
					<td><?= $_->escape->html($data['about']) ?></td>
				</tr>
			</table>
			<br />
			<table class="media-details">
				<?php foreach ([
					'joinDate' => 'Joined',
					'birthday' => 'Birthday',
					'gender' => 'Gender',
					'location' => 'Location'
			   ] as $key => $label): ?>
				<?php if ($data[$key] !== null): ?>
				<tr>
					<td><?= $label ?></td>
					<td><?= $data[$key] ?></td>
				</tr>
				<?php endif ?>
				<?php endforeach; ?>

				<?php if ($data['website'] !== null): ?>
				<tr>
					<td>Website</td>
					<td><?= $_->h->a($data['website'], $data['website']) ?></td>
				</tr>
				<?php endif ?>

				<?php if ($data['waifu']['character'] !== null): ?>
				<tr>
					<td><?= $_->escape->html($data['waifu']['label']) ?></td>
					<td>
						<?php
							$character = $data['waifu']['character'];
							echo $_->component->character(
									$character['names']['canonical'],
									$_->urlFromRoute('character', ['slug' => $character['slug']]),
									$_->h->img(Kitsu::getImage($character))
							);
						?>
					</td>
				</tr>
				<?php endif ?>
			</table>

			<h3>User Stats</h3><br />
			<table class="media-details">
				<?php foreach($data['stats'] as $label => $stat): ?>
				<tr>
					<td><?= $label ?></td>
					<td><?= $stat ?></td>
				</tr>
				<?php endforeach ?>
			</table>
		</aside>
		<article>
			<?php if ( ! empty($data['favorites'])): ?>
			<h3>Favorites</h3>
			<?= $_->component->tabs('user-favorites', $data['favorites'], static function ($items, $type) use ($_) {
				if ($type === 'character')
				{
					uasort($items, fn ($a, $b) => $a['names']['canonical'] <=> $b['names']['canonical']);
				}
				else
				{
					uasort($items, fn ($a, $b) => $a['titles']['canonical'] <=> $b['titles']['canonical']);
				}

				$rendered = array_map(fn ($item) => match ($type) {
					'character' => $_->component->character(
							$item['names']['canonical'],
							$_->urlFromRoute('character', ['slug' => $item['slug']]),
							$_->h->img(Kitsu::getImage($item))
					),
					default => $_->component->media(
							array_merge(
									[$item['titles']['canonical']],
									Kitsu::getFilteredTitles($item['titles']),
							),
							$_->urlFromRoute("{$type}.details", ['id' => $item['slug']]),
							$_->h->img(Kitsu::getPosterImage($item), ['width' => 220]),
					),
				}, $items);

				return implode('', array_map('mb_trim', $rendered));

			}, 'content full-width media-wrap') ?>
			<?php endif ?>
		</article>
	</section>
</main>