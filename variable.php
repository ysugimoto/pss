<?php


class Pss_Variable {
	
	protected $value;
	
	public function __construct($value) {
		
		$this->value = $this->_detect(trim($value));
	}
	
	public function __toString() {
		
		return $this->getValue();
	}
	
	public function getValue() {
		
		return $this->value;
	}
	
	public function execute($key, $css) {
		
		if ( is_string($this->value) ) {
			$css = preg_replace('/\{?\$' . preg_quote($key) . '\}?/', $this->value, $css);
		}
		
		return $css;
	}
	
	protected function _detect($value) {
		
		$value = trim($value, '\'"');
		if ( $value[0] === '[' ) {
			$value = preg_replace('/\[(.+)\]/', '$1', $value);
			return array_map(function($v) {
				return trim(trim($v), '\'"');
			}, explode(',', $value));
		} else if ( $value[0] === '{' ) {
			$value = preg_replace('/\{(.+)\}/', '$1', $value);
			$exp   = explode(',', $value);
			$o     = new stdClass;
			foreach ( $exp as $v ) {
				if ( strpos($v, ':') === FALSE ) {
					throw new RuntimeException('Parse error: Object-variable is invalid.');
				}
				$kv = array_map('trim', explode(':', $v));
				$o->{trim($kv[0], '\'"')} = trim($kv[1], '\'"');
			}
			return $o;
		} else {
			if ( preg_match('/^[0-9\.]+$/', $value) ) {
				$value = intval($value);
			}
			return $value;
		}
	}
}
