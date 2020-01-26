		<div class="page-heading">
			<h1>Edit <?= $heading; ?></h1>
		</div>
		<ul class="list-inline toolbar">
			<li class="btn-group">
				<a class="btn btn-default btn-save">Save</a>
				<a class="btn btn-default btn-save btn-preview">Preview</a>
				<a class="btn sep btn-save btn-publish">Publish</a>
			</li>
			<li>
				<a href="admin/posts/<?= $subPage; ?>s#status:all" class="btn btn-default danger">Go Back</a>
			</li>
		</ul>
		<div class="row process-post">
			<div class="col sm-8 ml-9">
				<input type="hidden" name="id" value="">
				<input type="hidden" name="type" value="<?= $subPage; ?>">
				<div class="perma hint pull-right transparent">
					<span class="semi-bold lite">Default URL:</span>
					<span class="inline-block normal">
						/<strong class="post-permalink"></strong>
						<input type="text" name="slug" value="" class="hidden xs" required>
						<a class="edit-permalink btn btn-default sm">Edit</a>
					</span>
				</div>
				<label>
					<span class="field-name">Title</span>
					<input type="text" name="title" value="" class="big full" required>
				</label>
				<textarea name="content" class="wysiwyg full"></textarea>
				<input type="hidden" name="template" value="">
				<input type="hidden" name="excerpt" value="">
				<input type="hidden" name="parent" value="0">
			</div>
			<div class="col sm-4 ml-3">
				<div class="row">
					<div class="col xs-12 s-6 sm-12">
						<p class="label">Featured Image <a href="#" class="pull-right">Edit</a></p>
						<div class="dropzone"></div>
						<input type="hidden" name="meta[thumbnail]" value="" data-method="put">
					</div>
					<div class="col xs-12 s-6 sm-12 post-status">
						<p class="label"><?= substr($heading, 0, -1); ?> Settings<a class="settings-link pull-right">Edit</a></p>
						<div class="panel">
							<div class="body summary">
								<ul class="list-icons no-margin">
									<li><i class="ion ion-paper-airplane"></i>Status: <strong class="post_status">Draft</strong></li>
<?php if($subPage == 'article') { ?>
									<li><i class="ion ion-eye"></i>Visibility: <strong class="post_password">Public</strong></li>
<?php } if($subPage != 'page') { ?>
									<li><i class="ion ion-chatboxes"></i>Comments: <strong class="post_commentStatus">Disabled</strong></li>
<?php } ?>
								</ul>
							</div>
							<div class="body form" style="display: none;">
								<div class="margin">
									<span class="select full">
										<select name="status">
											<option value="" disabled="disabled">Status</option>
											<option value="0">Trash</option>
											<option value="1" selected="selected" data-saved="true">Draft</option>
											<option value="2">Review Pending</option>
											<option value="3">Published</option>
										</select><span></span>
									</span>
								</div>
<?php if($subPage == 'article') { ?>
								<div>
									<label><span class="radio"><input name="password" type="radio" value="" checked="checked" data-saved="true"><span></span></span>Public</label>
									<label><span class="radio"><input name="password" type="radio" value="SYSTEM"><span></span></span>Private</label>
									<label class="no-margin">
										<span class="radio"><input name="password" type="radio" value="custom" class="custom"><span></span></span>Protected
										<div><input type="text" class="custom-password hidden" placeholder="Select Password"></div>
									</label>
								</div>
<?php } if($subPage != 'page') { ?>
								<label class="comment-status"><span class="checkbox"><input name="commentStatus" type="checkbox" value="1"><span></span></span>Enable Comments</label>
<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="tag-search"></div>
<?php /*
		<p class="hint no-margin"><span class="pwd-0">Public: Visible to anyone who has the link. </span><span class="pwd-SYSTEM">Private: Visible to anyone who is a user of your website. </span><span class="pwd-custom">Protected: Visible to anyone who knows the password you select.</span></p>
		<p>
			<a href="https://www.tinymce.com/docs/demo/" target="_blank">TinyMCE Demo</a><br>
			<a href="http://codepen.io/tinymce/pen/NGegZK" target="_blank">Codepen: All Plugins</a><br>
			<a href="http://codepen.io/tinymce/pen/Gqmkja/" target="_blank">Codepen: Inline Editor</a>
		</p>
		<ul class="styled-radio">
			<li><input name="password" value="" type="radio"><span>Public</span></li>
			<li><input name="password" value="SYSTEM" type="radio"><span>Private</span></li>
			<li><input name="password" value="password" type="radio"><span>Password</span></li>
		</ul>
*/ ?>
