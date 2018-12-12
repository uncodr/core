<!DOCTYPE html>
<html lang="en">
<head>
<?php loadTemplate('partials/head', 'core'); ?>
	<link rel="stylesheet" type="text/css" href="<?= assetURL('css/fonts.css', 'core'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= assetURL('css/plugins.css', 'core'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= assetURL('css/style.css', 'core'); ?>">
	<link rel="stylesheet" type="text/css" href="<?= assetURL('css/admin.css', 'core'); ?>">
</head>
<body class="admin">
<div class="loading big"></div>
<header class="topbar">
	<a class="hamburger"></a>
	<h2 class="title">
		<a href="<?= $this->baseURL; ?>" target="_blank"><span class="logo mini"></span><span class="hidden-xs"><?= $this->siteConfigs['siteTitle']; ?></span></a>
	</h2>
	<ul class="list-group nav-primary semi-bold">
		<li class="dropdown">
			<a href="admin/posts/articles#new"><i class="ion ion-plus-round"></i><span class="hidden-xs"> New</span></a>
			<ul class="list-group content">
				<li><a href="admin/posts/articles#new">Article</a></li>
				<li><a href="admin/posts/pages#new">Page</a></li>
				<li><a href="admin/config/users#new">User</a></li>
			</ul>
		</li>
		<li><a href="admin/config/update"><i class="ion ion-loop"></i><span class="hidden-xs"> Update</span></a></li>
		<li class="right dropdown">
			<a href="admin/config/profile">
				<i class="ion ion-person"></i><span class="hidden-xs username"></span>
			</a>
			<ul class="list-group content">
				<li><a href="admin/config/profile">Profile</a></li>
				<li><a href="<?= APP_URL; ?>help"><?= APP_NAME; ?> Help</a></li>
				<li class="divider"><a href="auth/logout">Logout</a></li>
			</ul>
		</li>
		<li class="right"><a href="admin/user/notifications"><i class="ion ion-ios-bell"></i></a></li>
	</ul>
</header>
<aside class="left none">
	<ul class="list-group semi-bold">
<?php foreach($navMenu as $href => $link) { ?>
		<li<?= ($link['class'])? ' class="'.$link['class'].'"':''; ?>>
			<a href="admin/<?= $href; ?>"><i class="ion ion-<?= $link['icon']; ?>"></i><span><?= $link['text']; ?></span></a>
		</li>
<?php } ?>
		<li>
			<a href="auth/logout"><i class="ion ion-power"></i><span>Logout</span></a>
		</li>
	</ul>
</aside>
<main>
<?php loadTemplate('partials/content', 'core'); ?>
</main>
<footer>
	<p class="bold no-margin">Thank you for choosing <a href="<?= APP_URL; ?>" target="_blank"><?= APP_NAME; ?></a></p>
</footer>

<?php loadTemplate('partials/js-common', 'core'); ?>
<script type="text/javascript" src="<?= assetURL('js/api.js', 'core'); ?>"></script>
<script type="text/javascript" src="<?= assetURL('js/admin.js', 'core'); ?>"></script>
<script type="text/javascript" src="<?= assetURL('third_party/tinymce/tinymce.min.js', 'core'); ?>"></script>
<script type="text/javascript">admin.init();</script>
<?php loadTemplate('partials/js-dynamic', 'core'); ?>

</body>
</html>
