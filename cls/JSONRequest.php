<?php
	abstract class JSONRequest extends Request {
		
		private $jsonEncode = JSON_NUMERIC_CHECK;
		
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			$this->setHeader("Content-type", "application/json");
		}
	
		public function getType(){
			return TYPE::JSON;
		}
		
		// Sets the second argument for the call to json_encode() later
		// defaults to json_numeric_check
		protected function setJsonEncodeOption($option){
			$this->jsonEncode = $option;
		}

		public function getJsonEncodeOption(){
			return $this->jsonEncode;
		}
	}