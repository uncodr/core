<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #fff; border: 1px solid rgba(38,50,56,0.12); box-shadow: 0 1px 2px rgba(38,50,56,0.12);">
	<tr>
		<td style="border-bottom: 1px solid rgba(38,50,56,0.06); vertical-align: middle; padding: 16px 20px;"><a href="<?= $this->baseURL; ?>" target="_blank"><img src="<?= $this->baseURL; ?>app/assets/img/logo2.png" height="auto"></a></td>
	</tr>
	<tr>
		<td style="min-height: 300px; vertical-align: top; padding: 20px;">
			<p>Hi <?= $name; ?>,</p>
			<p>Welcome to <?= $siteTitle; ?>! Thank you for registering with us.</p>
			<p>Your account has been created. Just activate it and you're all set to go!</p>
			<ol type="1">
				<li style="margin-bottom: 10px;">To activate the account, <a href="<?= ($this->baseURL).'auth/reset/'.$vcode; ?>" target="_blank">CLICK HERE</a> or copy-&amp;-paste the link below:<br><?= ($this->baseURL).'auth/reset/'.$vcode; ?></li>
				<li style="margin-bottom: 10px;">You will be asked to choose a password and then to confirm it.</li>
				<li style="margin-bottom: 10px;">After setting a new password, go to <?= $this->baseURL; ?>auth and enter the email ID (<?= $email; ?>) &amp; password you chose.</li>
				<li>If you forget your password or wish to change it, click "Forgot Password".</li>
			</ol>
		</td>
	</tr>
	<tr>
		<td style="font-size: 0.9em; padding: 12px 20px; border-top: 1px solid rgba(38,50,56,0.06); vertical-align: middle;">
			<p>Copyright &copy; <?= $siteTitle; ?></p>
		</td>
	</tr>
</table>
