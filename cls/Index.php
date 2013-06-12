<?php
	class Index extends HTMLRequest {
	
		public function parseRequest($params){
			$this->setResult(true, "Index");
		}
		
		public function getHTMLContent(){
			return $this->result;
		}
	}