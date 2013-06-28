<?php
	class Submit extends HTMLRequest {
		
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			$this->setOption("cacheEnabled", false);
		}
		
		public function parseRequest($params){
			$this->loadMustache();
			
			$this->title = sprintf("Submit A Mod - %s Summoner Mod Repository", REPO_NAME);
			
			if (isset($_POST["mod-name"])){
				$this->pageHeader = "Thanks for your submission";
				$tpl = $this->m->loadTemplate("submit_complete");
				
				// TODO: check actual parameters...
				
				$submitPage = $tpl->render();
			} else {
				$this->pageHeader = "Submit A Mod";
				$tpl = $this->m->loadTemplate("submit");
				
				$submitPage = $tpl->render(array(
							"REPONAME" => REPO_NAME
				));
			}
			
			$this->setResult(true, $this->getHTMLContent($submitPage));
		}
		
		public function getCacheId(){
			return "submit";
		}
	}