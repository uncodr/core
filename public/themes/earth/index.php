<!DOCTYPE html>
<html lang="en">
<head>
<?php loadTemplate('partials/head', 'core'); ?>
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/fonts.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/plugins.css', 'core'); ?>">
    <link rel="stylesheet" type="text/css" href="<?= assetURL('css/style.css', 'core'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= assetURL('css/style.css', $this->theme); ?>">
</head>
<body class="<?= $bodyClass; ?>">

<?php
loadTemplate($this->partials['header'], 'earth');
loadTemplate($this->partials[$pageType], 'earth');
loadTemplate('partials/js-common', 'core');
?>

</body>
</html>
