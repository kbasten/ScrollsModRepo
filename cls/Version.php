<?php
	class Version extends JSONRequest {
	
		public static $version = 2;
	
		public function parseRequest($params){
			$this->setResult(true, array("version" => Version::$version));
		}
		
		public function getCacheId($params){
			return "version";
		}
	}