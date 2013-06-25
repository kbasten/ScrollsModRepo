<?php
	class Submit extends HTMLRequest {
		
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			$this->setOption("cacheEnabled", false);
		}
		
		public function parseRequest($params){
			$this->loadMustache();
			
			$this->title = sprintf("Submit A Mod - %s Summoner Mod Repository", REPO_NAME);
			$this->pageHeader = "Submit A Mod";
			
			$tpl = $this->m->loadTemplate("submit");
			
			$submitPage = $tpl->render(array(
						"REPONAME" => REPO_NAME
			));
			
			$this->setResult(true, $this->getHTMLContent($submitPage));
		}
		
		public function getCacheId(){
			return "submit";
		}
	}