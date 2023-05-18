<?php
use Aviat\AnimeClient\Kitsu;
?>
<main class="user-page details">
	<h2 class="toph">
		About
		<?= $helper->a(
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
					<td><?= $helper->img($data['avatar'], ['alt' => '']); ?></td>
					<td><?= $escape->html($data['about']) ?></td>
				</tr>
			</table>
			<br />
			<table class="media-details">
				<?php foreach ([
					'joinDate' => 'Joined',
					'birthday' => 'Birthday',
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
					<td><?= $helper->a($data['website'], $data['website']) ?></td>
				</tr>
				<?php endif ?>

				<?php if ( ! empty($data['waifu'])): ?>
				<tr>
					<td><?= $escape->html($data['waifu']['label']) ?></td>
					<td>
						<?php
							$character = $data['waifu']['character'];
							echo $component->character(
									$character['names']['canonical'],
									$url->generate('character', ['slug' => $character['slug']]),
									$helper->img(Kitsu::getImage($character))
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
			<?= $component->tabs('user-favorites', $data['favorites'], static function ($items, $type) use ($component, $helper, $url) {
				$rendered = [];
				if ($type === 'character')
				{
					uasort($items, fn ($a, $b) => $a['names']['canonical'] <=> $b['names']['canonical']);
				}
				else
				{
					uasort($items, fn ($a, $b) => $a['titles']['canonical'] <=> $b['titles']['canonical']);
				}

				foreach ($items as $id => $item)
				{
					if ($type === 'character')
					{
						$rendered[] = $component->character(
								$item['names']['canonical'],
								$url->generate('character', ['slug' => $item['slug']]),
								$helper->img(Kitsu::getImage($item))
						);
					}
					else
					{
						$rendered[] = $component->media(
								array_merge(
										[$item['titles']['canonical']],
										Kitsu::getFilteredTitles($item['titles']),
								),
								$url->generate("{$type}.details", ['id' => $item['slug']]),
								$helper->img(Kitsu::getPosterImage($item), ['width' => 220]),
						);
					}
				}

				return implode('', array_map('mb_trim', $rendered));

			}, 'content full-width media-wrap') ?>
			<?php endif ?>
		</article>
	</section>
</main>