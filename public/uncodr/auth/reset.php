<div class="wrapper">
	<h1 class="app-title"><a href="<?= $this->baseURL; ?>" class="logo"><?= APP_NAME; ?></a></h1>
	<div class="panel">
		<header><h2 class="title text-center"><?= $heading; ?></h2></header>
		<form class="recover body hidden">
			<p>An <strong>otp will be emailed</strong> to you, after which you can reset your password.</p>
			<label>
				<span class="field-name">Username or Email ID</span>
				<input type="text" name="user" value="" required>
			</label>
			<a href="auth" class="alt-link">Back to Login</a>
			<button type="submit" class="btn btn-submit">Submit</button>
		</form>
		<form class="reset body hidden">
			<input type="hidden" name="user" value="<?= $uData['user']; ?>">
			<label>
				<span class="field-name">Your OTP</span>
				<input type="text" name="otp" value="<?= $uData['otp']; ?>" required>
			</label>
			<label>
				<span class="field-name">New Password</span>
				<input type="password" name="password" value="" required>
			</label>
			<label>
				<span class="field-name">Confirm Password</span>
				<input type="password" name="password2" value="" required>
			</label>
			<button type="submit" class="btn btn-submit">Submit</button>
		</form>
	</div>
</div>
