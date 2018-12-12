<!DOCTYPE html>
<html lang="en">
<head>
<?php loadTemplate('partials/head', 'core'); ?>
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/fonts.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/plugins.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/style.css', 'core'); ?>">
</head>
<body class="<?= $bodyClass; ?>">

<?php loadTemplate('partials/header', 'core'); ?>
<main>
	<div class="wrapper">
		<h1><?= $heading; ?></h1>
<?php loadTemplate('partials/content', 'core'); ?>
	</div>
</main>

<?php loadTemplate('partials/js-common', 'core'); ?>
</body>
</html>
