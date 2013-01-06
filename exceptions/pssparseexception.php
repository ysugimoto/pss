<?php

class PssParseException extends RuntimeException {
	
	protected $message;
	
	public function __construct($char = '', $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message = 'Parse error: Missing "' . $char . '" on '
		                 . Pss::getCurrentFile() . ' at line ' . $line;
	}
}
