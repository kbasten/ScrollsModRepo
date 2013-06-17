<?php
	abstract class HTMLRequest extends Request {
	
		private $c404 = "That's an error. Page not found.";
	
		protected $m;
		
		private $baseTpl;
		protected $title = "Summoner Mod Repository";
		protected $pageHeader = "Mod Repo";
	
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			$this->setHeader("Content-type", "text/html");
			$this->setHeader("Cache-Control", "no-cache, no-store, must-revalidate");
		}
		
		// loads the mustache render engine, this is not done for cached requests
		final protected function loadMustache(){
			// html needs Mustache :)
			require_once "cls/includes/Mustache/Autoloader.php";
			Mustache_Autoloader::register();
			
			$this->m = new Mustache_Engine(array(
						"loader"	=> new Mustache_Loader_FilesystemLoader("tpl/html/")
			));
			$this->baseTpl = $this->m->loadTemplate("base");
		}
	
		final public function getType(){
			return TYPE::HTML;
		}
		
		// TODO: Add whitespace strip here
		final protected function formatCacheContent($in){
			return $in;
		}
		
		protected function set404($c404){
			$this->c404 = $c404;
		}
		
		public function p404(){
			return $this->c404;
		}
		
		public function getHTMLContent($pageContent){
			return $this->baseTpl->render(array(
				"TITLE"		=> $this->title,
				"PHEADER"	=> $this->pageHeader,
				"CONTENT"	=> $pageContent
			));
		}
		
		// override this from the default cache id function,
		// because most url parameters can be ignored by default
		//public function getCacheId($params){
		//	return $params["url_0"];
		//}
	}