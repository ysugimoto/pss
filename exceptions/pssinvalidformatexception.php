<?php

class PssInvalidFormatException extends RuntimeException {
	
	protected $message;
	
	public function __construct($format = '', $line = 0) {
		
		if ( $line === 0 ) {
			$line = Pss::getCurrentLine() + 1;
		}
		$this->message = 'Parse Error: Invaid condition format ' . $format
		                 . ' on '. Pss::getCurrentFile() . ' near line ' . $line;
	}
}
