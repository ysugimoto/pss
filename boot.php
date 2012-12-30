<?php
/**
 * ====================================================================
 * 
 * PSS: PHP-CSS preprocessor 
 * 
 * Command line bootstrap
 * 
 * @package  PSS
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

require_once(__DIR__ . '/Pss.php');
$args   = array_slice($_SERVER['argv'], 1);
$input  = null;
$output = null;

foreach ( $args as $arg ) {
	if ( strpos($arg, '-') === 0 ) {
		Pss::addOption(substr(trim($arg, '-'), 0, 1), substr(trim($arg, '-'), 1));
		continue;
	}
	if ( ! $input ) {
		$input = $arg;
	} else if ( ! $output ) {
		$output = $arg;
	}
}

if ( ! file_exists($input) ) {
	echo 'Input file: ' . $input . ' is not exists.' . PHP_EOL;
	exit;
}

if ( ! $output ) {
	echo Pss::compile($input);
} else {
	file_put_contents($output, Pss::compile($input));
	echo 'Compilation succeed!' . PHP_EOL;
}
exit;
