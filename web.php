<?php

define('PSS_START_TIME', microtime());
define('PSS_DIR', realpath(__DIR__ . '/'));
define('PSS_CLASS_PREFIX', 'Pss_');
define('PSS_VERSION', '0.8');

// Register autoload plugin classes
spl_autoload_register(function($name) {
	
	$file = strtolower(str_replace(PSS_CLASS_PREFIX, '', $name));
	$file = str_replace('_', '/', $file);
	
	if ( file_exists(PSS_DIR . '/' . $file . '.php') ) {
		require_once(PSS_DIR . '/' . $file . '.php');
	} else if ( file_exists(PSS_DIR . '/base/' . $file . '.php') ) {
		require_once(PSS_DIR . '/base/' . $file . '.php');
	} else if ( file_exists(PSS_DIR . '/plugins/' . $file . '.php') ) {
		require_once(PSS_DIR . '/plugins/' . $file . '.php');
	} else if ( file_exists(PSS_DIR . '/controls/' . $file . '.php') ) {
		require_once(PSS_DIR . '/controls/' . $file . '.php');
	}
});

function server($key) {
	$key = strtoupper($key);
	return ( isset($_SERVER[$key]) ) ? $_SERVER[$key] : FALSE;
}

function post($key) {
	return ( isset($_POST[$key]) ) ? $_POST[$key] : FALSE;
}

function get($key) {
	return ( isset($_GET[$key]) ) ? $_GET[$key] : FALSE;
}


// Main process
if ( server('request_method') === 'POST' ) {
	$input  = post('pss');
	if ( is_array(post('option')) ) {
		foreach ( post('option') as $opt ) {
			Pss::addOption($opt, 1);
		}
	} else if ( is_string(post('option')) ) {
		$index = 0;
		while ( $word = substr(post('option'), $index++, 1) ) {
			Pss::addOption($word, 1);
		}
	}
	
	if ( empty($input) ) {
		header('HTTP/1.1 503 Internal Server Error');
		echo 'PSS data is empty.';
		exit;
	}
	
	try {
		$compiled = Pss::compile($input);
	} catch ( RuntimeException $e ) {
		header('HTTP/1.1 503 Internal Server Error');
		echo $e->getMessage();
		exit;
	}

} else {
	$input = get('file');
	if ( get('opt') ) {
		$index = 0;
		while ( $word = substr(get('opt'), $index++, 1) ) {
			Pss::addOption($word, 1);
		}
	}

	if ( ! $input ) {
		header('HTTP/1.1 503 Internal Server Error');
		echo '"file" parameter must be required!';
		exit;
	}
	
	$css = @file_get_contents(rawurldecode($input));
	
	try {
		$compiled = Pss::compile($css, rawurldecode($input));
	} catch ( RuntimeException $e ) {
		header('HTTP/1.1 503 Internal Server Error');
		echo $e->getMessage();
		exit;
	}
}

header('HTTP/1.1 200 OK');
header('Content-Type: text/css');
header('Content-Length: ' . strlen($compiled));
echo $compiled;
exit;