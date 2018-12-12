		<div class="page-heading">
			<input type="text" name="search" placeholder="Search" class="search">
			<h1><?= $heading; ?></h1>
			<a href="admin/posts/media#new" class="btn sm btn-new">Add New</a>
		</div>
		<ul class="list-inline toolbar">
			<li class="btn-group">
				<a class="btn btn-default active" data-type="all">All</a>
				<a class="btn btn-default" data-type="image"><i class="ion ion-image"></i></a>
				<a class="btn btn-default" data-type="audio"><i class="ion ion-ios-musical-notes"></i></a>
				<a class="btn btn-default" data-type="video"><i class="ion ion-ios-videocam"></i></a>
				<a class="btn btn-default"><i class="ion ion-funnel"></i></a>
			</li>
			<li>
				<a class="btn btn-default gallery-trash"><i class="ion ion-trash-b"></i></a>
			</li>
			<li class="btn-group hidden trash-axn">
				<a class="btn btn-red"><i class="ion ion-trash-b"></i></a>
				<a class="btn sep btn-default gallery-trash-cancel"><i class="ion ion-close-round"></i></a>
			</li>
		</ul>
		<div class="gallery thumb-150">
<?php /*
			<h3 class="list-title">February 2017</h3>
			<ul class="list-inline items">
				<li style="background-image: url('<?= baseURL('uploads/2017/02/snake-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/koala-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/monkey-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/panther-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/elephants-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/tiger-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/koala-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/elephants-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/tiger-320x180.jpg'); ?>');"></li>
			</ul>
			<h3 class="list-title">January 2017</h3>
			<ul class="list-inline items">
				<li style="background-image: url('<?= baseURL('uploads/2017/02/koala-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/elephants-320x180.jpg'); ?>');"></li>
				<li style="background-image: url('<?= baseURL('uploads/2017/02/tiger-320x180.jpg'); ?>');"></li>
			</ul>
*/ ?>
		</div>
