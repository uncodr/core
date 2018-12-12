<div class="wrapper">
	<h1 class="app-title"><a href="<?= $this->baseURL; ?>" class="logo"><?= APP_NAME; ?></a></h1>
	<div class="panel">
		<header><h2 class="title text-center"><?= $heading; ?></h2></header>
		<form class="body register" data-autologin="<?= $reg['autologin']; ?>">
			<label>
				<span class="field-name">Name</span>
				<input type="text" name="name" value="" required>
			</label>
			<label>
				<span class="field-name">Email ID</span>
				<input type="email" name="email" value="" required>
			</label>
			<label>
				<span class="field-name">Login</span>
				<input type="text" name="login" value="" required>
			</label>
			<label>
				<span class="checkbox"><input type="checkbox" name="meta[tocAgree]" value="1" required><span></span></span>
				<span class="field-name">I agree to the terms and conditions</span>
			</label>
			<a href="auth" class="alt-link">Back to Login</a>
			<button type="submit" class="btn btn-submit">Sign Up</button>
		</form>
	</div>
</div>
