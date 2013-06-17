<?php
	class Cache {
	
		private $r; // request the cache is called for
		
		private $content = "";
	
		public function __construct(Request $r){
			$this->r = $r;
		}
		
		public function isValid($id){
			$sth = $this->r->getDB()->prepare("SELECT content
						FROM cache
						WHERE id = ?
						AND cachetime + ? > UNIX_TIMESTAMP()");
			$sth->bindValue(1, $id, PDO::PARAM_STR);
			$sth->bindValue(2, $this->r->getOption("cacheTTL"), PDO::PARAM_INT);
			
			$sth->execute();
			
			$cacheResult = $sth->fetch(PDO::FETCH_ASSOC);
			
			// there's something in the cache, save the content and let the 
			// request know there is something
			if (!empty($cacheResult)){
				$this->content = $cacheResult["content"];
				return true;
			} else {
				return false;
			}
		}
		
		public function save($id, $content){
			$sth = $this->r->getDB()->prepare("INSERT INTO cache (id, cachetime, content)
						VALUES (?, UNIX_TIMESTAMP(), ?)
						ON DUPLICATE KEY UPDATE cachetime = UNIX_TIMESTAMP(), content = ?");
			$sth->bindValue(1, $id, PDO::PARAM_STR);
			$sth->bindValue(2, $content, PDO::PARAM_STR);
			$sth->bindValue(3, $content, PDO::PARAM_STR);
			
			$sth->execute();
		}
		
		// returns the content of the cache
		public function getContent(){
			return $this->content;
		}
	}