<!DOCTYPE html>
<html lang="en">
<head>
	<title><?= $title ?></title>
	<meta charset="utf-8" />
	<meta http-equiv="cache-control" content="no-store" />
	<meta http-equiv="Content-Security-Policy" content="script-src 'self'" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0" />
	<link rel="icon" href="<?= $urlGenerator->assetUrl('favicon.ico') ?>" />
	<link rel="stylesheet" href="<?= $urlGenerator->assetUrl('css.php/g/base/debug') ?>" />
	<script defer="defer" src="<?= $urlGenerator->assetUrl('js.php/g/base') ?>"></script>
</head>
<body class="<?= $escape->attr($url_type) ?> list">
	<header>
		<?php include 'main-menu.php' ?>
		<?php if(isset($message) && is_array($message)):
			foreach($message as $m)
			{
				extract($m);
	 			include 'message.php';
			}
	 	endif ?>
	</header>