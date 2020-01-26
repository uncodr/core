		<div class="page-heading">
			<h1>Edit <?= $heading; ?></h1>
		</div>
		<ul class="list-inline toolbar">
			<li>
				<a class="btn btn-red btn-remove">Remove User</a>
			</li>
			<li>
				<a href="admin/config/users#status:all" class="btn btn-default">Go Back</a>
			</li>
		</ul>
		<div class="row">
			<div class="col s-8 ml-9">
				<div class="panel">
					<header class="mini bold">User Details<a class="axn btn edit-user">Edit</a></header>
					<div class="body left-labelled">
						<div class="row user-details">
							<div class="col m-6">
								<p><span class="label">Email</span><strong class="field" data-name="email"></strong></p>
								<p><span class="label">Username</span><strong class="field" data-name="login"></strong></p>
								<p><span class="label">Verified Email?</span><strong class="field" data-name="emailVerified"></strong></p>
								<p><span class="label">Last Login</span><span class="field" data-name="lastLogin"></span> <span class="prompt field" data-name="loginCount"></span></p>
							</div>
							<div class="col m-6">
								<p><span class="label">Name</span><strong class="field" data-name="name"></strong> <span class="prompt solid">Public</span></p>
								<p><span class="label">Display Name</span><strong class="field" data-name="screenName"></strong> <span class="prompt solid">Public</span></p>
								<p><span class="label">Status</span><strong class="field" data-name="status"></strong></p>
								<p><span class="label">Created On</span><span class="field" data-name="addedOn"></span></p>
							</div>
						</div>
						<form class="row user-details" style="display: none;">
							<div class="col m-6">
								<label class="margin">
									<span class="field-name">Email</span>
									<input type="text" name="email" value="" required>
								</label>
								<label class="margin">
									<span class="field-name">Username</span>
									<input type="text" name="login" value="" required>
								</label>
								<span class="label">User's Email</span>
								<ul class="list-inline spaced right">
									<li><label>
										<span class="radio"><input name="email_verified" type="radio" value="1" required><span></span></span>Verified
									</label></li>
									<li><label>
										<span class="radio"><input name="email_verified" type="radio" value="0" required><span></span></span>Unverified
									</label></li>
								</ul>
							</div>
							<div class="col m-6">
								<label class="margin">
									<span class="field-name">Name</span>
									<input type="text" name="name" value="" required>
								</label>
								<label class="margin">
									<span class="field-name">Display Name</span>
									<input type="text" name="screen_name" value="" required>
								</label>
								<span class="label">Status</span>
								<ul class="list-inline spaced right">
									<li><label>
										<span class="radio"><input name="status" type="radio" value="1" required><span></span></span>Active
									</label></li>
									<li><label>
										<span class="radio"><input name="status" type="radio" value="0" required><span></span></span>Inactive
									</label></li>
								</ul>
							</div>
							<div class="col m-12 text-center"><button class="btn btn-save" type="submit">Save</button></div>
						</form>
						<ul class="list-group user-groups row">
							<li class="title row bold">
								<div class="col m-6">Groups <a class="btn sm group-add">Add</a></div>
								<div class="col m-2 hidden-xs hidden-s">Expiry</div>
								<div class="col m-4 hidden-xs hidden-s">Status</div>
							</li>
							<li class="add row border-bottom" style="display: none;">
								<div class="col m-6">
									<span class="select"><select name="id" required>
										<option value="" selected="selected" disabled="disabled">Select group to assign</option>
<?php foreach ($groups as $group) { ?>
										<option value="<?= $group['groupID']; ?>" data-expiry="<?= codeToTime($group['expiry']); ?>"><?= $group['name']; ?></option>
<?php } ?>
									</select><span></span></span>
								</div>
								<div class="col m-2"><input name="expiry" placeholder="dd/mm/yyyy" class="date-picker no-margin" value="" type="text"></div>
								<div class="col m-4 pad0-7">
									<ul class="list-inline separate pull-right">
										<li><a class="group-save">Save</a></li>
										<li><a class="group-add">Cancel</a></li>
									</ul>
									<span class="checkbox">
										<input name="status" type="checkbox" value="1"><span></span>
									</span>
								</div>
							</li>
							<li class="template hidden row">
								<div class="col m-6 bold pad0-7">{{name}}</div>
								<div class="col m-2">
									<div class="field pad0-7">{{expiry}}</div>
									<span class="field hidden"><input name="expiry" placeholder="dd/mm/yyyy" class="date-picker no-margin" value="" type="text"></span>
								</div>
								<div class="col m-4 pad0-7">
									<ul class="list-inline separate pull-right {{class}}">
										<li><a class="group-save">Edit</a></li>
										<li><a class="group-delete">Delete</a></li>
									</ul>
									<span class="field">{{status}}</span>
									<span class="field hidden checkbox">
										<input name="status" type="checkbox" value="1"><span></span>
									</span>
								</div>
							</li>
						</ul>
					</div>
					<footer class="default">
						<ul class="list-inline spaced">
							<li class="bold"><a href="">Change Password</a></li>
							<li class="bold"><a href="">Email Password Reset Link</a></li>
							<li class="right"><span class="prompt solid">Public</span> denotes publically visible info.</li>
						</ul>
					</footer>
				</div>
			</div>
			<div class="col s-4 ml-3">
				<div class="img-placeholder"></div>
				<div class="row">
					<div class="col xs-12 s-6 sm-12 post-status">
						<p class="label"><?= substr($heading, 0, -1); ?> Settings<a class="settings-link pull-right">Edit</a></p>
						<div class="panel">
							<div class="body summary">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
