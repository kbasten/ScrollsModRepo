<?php
	// 100: Missing required key
	// 101: Unknown field
	// 102: No such method
	// 103: Max value exceeded
	// 104: Min value not met
	// 105: Database error
	// 106: Value not possible for field
	// 107: Cannot combine field
	// 108: Rate limit exceeded
	// 109: Download file location not set
	// 110: Download file not found
	
	class ApiException extends Exception {
	
		public function __construct($msg = null, $code = 0){
			parent::__construct($msg, $code);
		}
	
		public function __toString(){
			$out = array(
				"msg"	=> "exception",
				"code"	=> $this->getCode(),
				"exception" => $this->getMessage()
			);
			return json_encode($out, JSON_NUMERIC_CHECK);
		}
	}