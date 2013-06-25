<?php
	class Version extends JSONRequest {
	
		public static $version = 1;
	
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			// $this->setOption("cacheEnabled", false);
		}
	
		public function parseRequest($params){		
			$this->setResult(true, array("version" => Version::$version));
		}
		
		public function getCacheId($params){
			return "version";
		}
	}