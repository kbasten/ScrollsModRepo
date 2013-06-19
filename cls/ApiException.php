<?php
	class ApiException extends Exception {
	
		public function __construct($msg = null, $code = ErrorCode::E_NONE){
			parent::__construct($msg, $code);
		}
	
		public function __toString(){
			$out = array(
						"msg" => "exception",
						"code" => $this->getCode(),
						"exception" => $this->getMessage()
			);
			return json_encode($out, JSON_NUMERIC_CHECK);
		}
	}
	
	class ErrorCode {
		const E_NONE                = 0;
		const E_MISS_REQ_KEY 		= 100; // 100: Missing required key
		const E_FIELD_UNKOWN 		= 101; // 101: Unknown field
		const E_NO_SUCH_METHOD 		= 102; // 102: No such method
		const E_MAX_VAL_EXCEEDED 	= 103; // 103: Max value exceeded
		const E_MIN_VAL_NOT_MET  	= 103; // 104: Min value not met
		const E_DATABASE			= 105; // 105: Database error
		const E_VAL_NOT_POSSIBLE    = 106; // 106: Value not possible for field
		const E_CANT_COMBINE		= 107; // 107: Cannot combine field
		const E_RATE_LIM_EXCEEDED   = 108; // 108: Rate limit exceeded
		const E_NO_FILE_LOCATION    = 109; // 109: Download file location not set
		const E_FILE_NOT_FOUND		= 110; // 110: Download file not found
	}
