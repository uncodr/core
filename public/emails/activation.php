<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #fff; border: 1px solid rgba(38,50,56,0.12); box-shadow: 0 1px 2px rgba(38,50,56,0.12);">
	<tr>
		<td style="border-bottom: 1px solid rgba(38,50,56,0.06); vertical-align: middle; padding: 16px 20px;"><a href="<?= $this->baseURL; ?>" target="_blank"><img src="<?= $this->baseURL; ?>app/assets/img/logo2.png" height="auto"></a></td>
	</tr>
	<tr>
		<td style="min-height: 300px; vertical-align: top; padding: 20px;">
			<p>Hi <?= $name; ?>,</p>
			<p><strong>You are ready to go!</strong></p>
			<p>We have finished setting up your Jamboree account. Just confirm your email address by clicking the button below:</p>
			<p><a href="<?= ($this->baseURL).'auth/reset/'.$vcode; ?>" target="_blank" style="padding: 1rem 1.3rem; line-height: 1; color: #fff; background-color: #60cc5d; border: .1rem solid rgba(38,50,56,0.12); border-radius: .2rem; text-align: center; cursor: pointer; box-shadow: 0 .1rem .2rem rgba(38,50,56,0.27);">CLICK HERE</a><br></p>
			<p>You may also copy-&amp;-paste the link below:<br><?= ($this->baseURL).'auth/reset/'.$vcode; ?></p>
			<p>You will be asked to choose a password and then to confirm it.<br>After setting a new password, go to <?= $this->baseURL; ?>auth and enter the email ID (<?= $email; ?>) &amp; password you chose.</p>
		</td>
	</tr>
	<tr>
		<td style="font-size: 0.9em; padding: 12px 20px; border-top: 1px solid rgba(38,50,56,0.06); vertical-align: middle;">
			<p>Copyright &copy; <?= $siteTitle; ?></p>
		</td>
	</tr>
</table>
