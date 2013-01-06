<?php

class FileNotFoundException extends RuntimeException {
	
	protected $message;
	
	public function __construct($file, $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message = 'File "' . $file . '" is not found on '
		                 . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
