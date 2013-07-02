<?php
	abstract class JSONRequest extends Request {
		
		private $jsonEncode = JSON_NUMERIC_CHECK;
		
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			// default json header :)
			$this->setHeader(new Header("Content-type", "application/json"));
		}
	
		final public function getType(){
			return TYPE::JSON;
		}
		
		// Sets the second argument for the call to json_encode() later
		// defaults to json_numeric_check
		protected function setJsonEncodeOption($option){
			$this->jsonEncode = $option;
		}

		// converts array into string for database storage
		final protected function formatCacheContent($in){
			return json_encode($in, $this->getJsonEncodeOption());
		}
		
		// converts string back to array, or it would be double escaped
		final protected function deformatCacheContent($in){
			return json_decode($in, true);
		}
		
		public function getJsonEncodeOption(){
			return $this->jsonEncode;
		}
	}