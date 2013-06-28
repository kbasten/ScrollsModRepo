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