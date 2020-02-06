	<section class="users-list hidden">
<?php $this->load->view('uncodr/admin/config/users-list'); ?>
	</section>
	<section class="users-edit hidden">
<?php $this->load->view('uncodr/admin/config/users-edit'); ?>
	</section>
	<div class="user-add modal hidden" data-persist="1">
		<div class="content panel">
			<a href="admin/config/users#status:all" class="btn btn-red circle lg modal-close"><i class="ion ion-close-round"></i></a>
			<header>
				<h3 class="title">Add User</h3>
			</header>
			<form class="body register">
				<div class="row">
					<div class="col m-6">
						<label>
							<span class="field-name">Email ID</span>
							<input type="email" name="email" class="full" value="" required>
						</label>
						<label>
							<span class="field-name">Login</span>
							<input type="text" name="login" class="full" value="" required>
						</label>
					</div>
					<div class="col m-6">
						<label>
							<span class="field-name">Name</span>
							<input type="text" name="name" class="full" value="" required>
						</label>
						<label>
              <span class="field-name">Group</span>
							<span class="select full"><select name="group" required>
								<option value="" selected="selected" disabled="disabled">Select group to assign</option>
<?php foreach ($groups as $group) { ?>
								<option value="<?= $group['code']; ?>"><?= $group['name']; ?></option>
<?php } ?>
							</select><span></span></span>
						</label>
					</div>
				</div>
				<footer class="text-right">
					<button type="submit" class="btn btn-submit">Sign Up</button>
				</footer>
			</form>
		</div>
	</div>
