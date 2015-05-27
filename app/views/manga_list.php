<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $title ?></title>
	<link rel="stylesheet" href="/public/css/marx.css" />
	<link rel="stylesheet" href="/public/css/base.css" />
	<link rel="stylesheet" href="/public/css/manga.css" />
</head>
<body>
	<h1>Tim's Manga List [<a href="//anime.timshomepage.net">Anime List</a>]</h1>
	<?php include 'manga_nav.php' ?>
	<main>
		<?php foreach ($sections as $name => $items): ?>
			<section class="status">
				<h2><?= $name ?></h2>
				<section class="media-wrap">
					<?php foreach($items as $item): ?>
					<article class="media" id="manga-<?= $item['manga']['id'] ?>">
						<img src="<?= $item['manga']['poster_image'] ?>" />
						<div class="name"><a href="https://hummingbird.me/manga/<?= $item['manga']['id'] ?>">
							<?= $item['manga']['romaji_title'] ?>
							<?= (isset($item['manga']['english_title'])) ? "<br />({$item['manga']['english_title']})" : ""; ?>
						</a></div>
						<div class="media_metadata">
							<div class="media_type"><?= $item['manga']['manga_type'] ?></div>
							<?php /*<div class="airing_status"><?= $item['manga']['status'] ?></div>*/ ?>
							<div class="user_rating"><?= ($item['rating'] > 0) ? (int)($item['rating'] * 2) : '-' ?> / 10</div>
							<div class="completion">
								Chapters: <?= $item['chapters_read'] ?> / <?= ($item['manga']['chapter_count'] > 0) ? $item['manga']['chapter_count'] : "-" ?><?php /*<br />
								Volumes: <?= $item['volumes_read'] ?> / <?= ($item['manga']['volume_count'] > 0) ? $item['manga']['volume_count'] : "-" ?>*/ ?>
							</div>
						</div>
					</article>
					<?php endforeach ?>
				</section>
			</section>
		<?php endforeach ?>
	</main>
</body>
</html>