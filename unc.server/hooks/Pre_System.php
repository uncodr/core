<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pre_System {

	public function checkConf() {

		$configFiles = [
			// ['path' => APPPATH.'config/database.php', 'function' => '_database'],
			['path' => FCPATH.'.htaccess', 'function' => '_htaccess']
		];
		foreach($configFiles as $file) {
			if(!file_exists($file['path'])) {
				$content = $this->{$file['function']}();
				@file_put_contents($file['path'], $content);
			}
		}
	}

	/**
	 * get the htaccess file content
	 * @return		string			content of .htaccess file
	 */
	private function _htaccess() {

		$output = 'RewriteEngine on'."\n\n";
		$output .= 'RewriteCond %{REQUEST_URI}::$1 ^(.*?/)(.*)::\2$'."\n";
		$output .= 'RewriteRule ^(.*)$ - [E=BASE:%1]'."\n";
		$output .= 'RewriteCond $1 !^(index\.php|robots\.txt|favicon\.ico|sitemap\.xml)'."\n";
		$output .= 'RewriteRule ^(.*)$ %{ENV:BASE}index.php/$1 [L,QSA]'."\n\n";
		$output .= 'SetEnv CI_ENV \'production\''."\n";

		return $output;
	}
}
