<?php
use Aviat\AnimeClient\API\Kitsu;
?>
<main class="user-page details">
	<h2 class="toph">
		<?= $helper->a(
			"https://kitsu.io/users/{$data['slug']}",
			$data['name'], [
			'title' => 'View profile on Kitsu'
		])
		?>
	</h2>

	<p><?= $escape->html($data['about']) ?></p>

	<section class="flex flex-no-wrap">
		<aside class="info">
			<center>
				<?= $helper->img($urlGenerator->assetUrl($data['avatar']), ['alt' => '']); ?>
			</center>
			<br />
			<table class="media-details">
				<tr>
					<td>Location</td>
					<td><?= $data['location'] ?></td>
				</tr>
				<tr>
					<td>Website</td>
					<td><?= $helper->a($data['website'], $data['website']) ?></td>
				</tr>
				<?php if ( ! empty($data['waifu'])): ?>
				<tr>
					<td><?= $escape->html($data['waifu']['label']) ?></td>
					<td>
						<?php
							$character = $data['waifu']['character'];
							echo $helper->a(
								$url->generate('character', ['slug' => $character['slug']]),
								$character['canonicalName']
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
				if ($type === 'characters')
				{
					uasort($items, fn ($a, $b) => $a['canonicalName'] <=> $b['canonicalName']);
				}
				else
				{
					uasort($items, fn ($a, $b) => Kitsu::filterTitles($a)[0] <=> Kitsu::filterTitles($b)[0]);
				}

				foreach ($items as $id => $item)
				{
					if ($type === 'characters')
					{
						$rendered[] = $component->character(
								$item['canonicalName'],
								$url->generate('character', ['slug', $item['slug']]),
								$helper->picture("images/characters/{$item['id']}.webp")
						);
					}
					else
					{
						$rendered[] = $component->media(
								Kitsu::filterTitles($item),
								$url->generate("{$type}.details", ['id' => $item['slug']]),
								$helper->picture("images/{$type}/{$item['id']}.webp"),
						);
					}
				}

				return implode('', array_map('mb_trim', $rendered));

			}, 'content full-width media-wrap') ?>
			<?php endif ?>
		</article>
	</section>
</main>