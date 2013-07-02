<?php
	abstract class Maintenance {
		
		private $db;
		
		public function __construct(PDO $pdo){
			$this->db = $pdo;
		}
		
		public abstract function process($params);
		
		protected function getDB(){
			return $this->db;
		}
	}
	
	class MException extends Exception {
	
		public function __construct($msg){
			parent::__construct($msg, 0);
		}
	
		public function __toString(){
			return $this->getMessage() . "\n";
		}
	}
	
	class Header {
	
		private $key = "";
		private $value = "";
	
		// for things like "HTTP/1.0 404 Not Found", $value is omitted
		public function __construct($key, $value = ""){
			$this->key = $key;
			$this->value = $value;
		}
		
		public function getKey(){
			return $this->key;
		}
		
		public function setValue($value){
			$this->value = $value;
		}
		
		public function __toString(){
			if ($this->key == ""){
				return $this->value;
			} else {
				return sprintf("%s: %s", $this->key, $this->value);
			}
		}
	}