<div class="wrapper">
	<h1 class="app-title"><a href="<?= $this->baseURL; ?>" class="logo"><?= APP_NAME; ?></a></h1>
	<div class="panel">
		<header><h2 class="title text-center"><?= $heading; ?></h2></header>
		<form class="login body">
			<label>
				<span class="field-name">Username or Email ID</span>
				<input type="text" name="user" value="" required>
			</label>
			<label>
				<span class="field-name">Password</span>
				<input type="password" name="password" value="" required>
			</label>
			<a href="auth/reset" class="alt-link">Forgot Password?</a>
			<button type="submit" class="btn btn-submit">Login</button>
		</form>
	</div>
</div>
