<?php


class Pss_Base64 extends Pss_Plugin {
	
	public static function inline($param) {
		
		$path = realpath(Pss::$currentDir . '/' . trim($param));
		if ( ! file_exists($path) ) {
			throw new RuntimeException('Base64 file ' . $param . ' is not found.');
		}
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		if ( ! $ext ) {
			throw new RuntimeException('Base64 file cannot detect mimetype.');
		}
		
		if ( $ext === 'jpg' ) {
			$ext = 'jpeg';
		}
		
		return 'data:image/' . $ext .  ';base64,' . base64_encode(file_get_contents($path));
	}
}
