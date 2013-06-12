<?php
	abstract class HTMLRequest extends Request {
	
		private $c404 = "That's an error. Page not found.";
	
		protected $m;
	
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			// html needs Mustache :)
			require_once "cls/includes/Mustache/Autoloader.php";
			Mustache_Autoloader::register();
			
			$this->m = new Mustache_Engine();
			
			$this->setHeader("Content-type", "text/html");
		}
	
		public function getType(){
			return TYPE::HTML;
		}
		
		protected function set404($c404){
			$this->c404 = $c404;
		}
		
		public function p404(){
			return $this->c404;
		}
		
		public abstract function getHTMLContent();
	}