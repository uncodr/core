<table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #fff; border: 1px solid rgba(38,50,56,0.12); box-shadow: 0 1px 2px rgba(38,50,56,0.12);">
	<tr>
		<td style="border-bottom: 1px solid rgba(38,50,56,0.06); vertical-align: middle; padding: 16px 20px;"><a href="<?= $this->baseURL; ?>" target="_blank"><img src="<?= $this->baseURL; ?>app/assets/img/logo2.png" height="auto"></a></td>
	</tr>
	<tr>
		<td style="min-height: 300px; vertical-align: top; padding: 20px;">
			<p>Hi <?= $name; ?>,</p>
			<p>It appears that you have requested for a password reset. Your OTP is<br><br><span style="font-size: 2em;"><?= $otp; ?></span></p>
			<p>Alternatively, you can <a href="<?= ($this->baseURL).'auth/reset/'.$vcode; ?>" target="_blank" style="font-size: 1.25em;">CLICK HERE</a> or copy-&amp;-paste the link below:<br><?= ($this->baseURL).'auth/reset/'.$vcode; ?></p>
			<p>If you have not requested the password reset, please ignore this email.</p>
		</td>
	</tr>
	<tr>
		<td style="font-size: 0.9em; padding: 12px 20px; border-top: 1px solid rgba(38,50,56,0.06); vertical-align: middle;">
			<p>Copyright &copy; <?= $siteTitle; ?></p>
		</td>
	</tr>
</table>
