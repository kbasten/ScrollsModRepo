<?php
	abstract class Request {
	
		protected $pdo; // mod database
		private $succeeded = false;
		protected $result = "";
		
		protected $type;
		
		private $headers = array();
		
		public function __construct(PDO $pdo){
			$this->pdo = $pdo;
		}
	
		public abstract function parseRequest($params);
		
		public abstract function getType(); // one of the TYPE enums
		
		protected function setResult($success, $result){
			$this->succeeded = $success;
			$this->result = $result;
		}
		
		protected function setHeader($id, $content){
			$this->headers[$id] = $content;
		}
		
		public function getHeaders(){
			return $this->headers;
		}
		
		public function getResult(){
			$msg = $this->result;
			/*
			if ($this->succeeded){
				$msg = $this->result;
			} else {
				if ($this->getType() == TYPE::JSON){
					$msg = $this->failMessage();
				}
			}
			*/
			return array($this->succeeded, $msg);
		}
	}
	
	/**
	 * Enum for request types
	 */
	class TYPE {
		const JSON = 0;
		const HTML = 1;
		const DOWNLOAD = 2;
	}