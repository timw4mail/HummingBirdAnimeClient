<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8"/>
	<title><?= $title ?></title>
	<meta http-equiv="cache-control" content="no-store" />
	<meta http-equiv="Content-Security-Policy" content="script-src 'self'" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=1" />
	<link rel="stylesheet" href="<?= $urlGenerator->assetUrl('css/' . $config->get('theme') . '.min.css') ?>" />
	<link rel="<?= $config->get('theme') === 'dark' ? '' : 'alternate ' ?>stylesheet" title="Dark Theme" href="<?= $urlGenerator->assetUrl('css/dark.min.css') ?>" />
	<link rel="icon" href="<?= $urlGenerator->assetUrl('images/icons/favicon.ico') ?>" />
	<link rel="apple-touch-icon" sizes="57x57" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-57x57.png') ?>">
	<link rel="apple-touch-icon" sizes="60x60" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-60x60.png') ?>">
	<link rel="apple-touch-icon" sizes="72x72" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-72x72.png') ?>">
	<link rel="apple-touch-icon" sizes="76x76" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-76x76.png') ?>">
	<link rel="apple-touch-icon" sizes="114x114" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-114x114.png') ?>">
	<link rel="apple-touch-icon" sizes="120x120" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-120x120.png') ?>">
	<link rel="apple-touch-icon" sizes="144x144" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-144x144.png') ?>">
	<link rel="apple-touch-icon" sizes="152x152" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-152x152.png') ?>">
	<link rel="apple-touch-icon" sizes="180x180" href="<?= $urlGenerator->assetUrl('images/icons/apple-icon-180x180.png') ?>">
	<link rel="icon" type="image/png" sizes="192x192"  href="<?= $urlGenerator->assetUrl('images/icons/android-icon-192x192.png') ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= $urlGenerator->assetUrl('images/icons/favicon-32x32.png') ?>">
	<link rel="icon" type="image/png" sizes="96x96" href="<?= $urlGenerator->assetUrl('images/icons/favicon-96x96.png') ?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= $urlGenerator->assetUrl('images/icons/favicon-16x16.png') ?>">

</head>
<body class="<?= $escape->attr($url_type) ?> list">
	<?php include 'setup-check.php' ?>
	<header>
	<?php
		include 'main-menu.php';
		if(isset($message) && is_array($message))
		{
			foreach($message as $m)
			{
				$message = $m['message'];
				$message_type = $m['message_type'];
				include 'message.php';
			}
		}
	?>

	</header>