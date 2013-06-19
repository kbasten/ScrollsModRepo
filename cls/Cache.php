<?php
	class Cache {
	
		private $r; // request the cache is called for
		
		private $content = "";
	
		// TODO: Remove $id from isValid and save methods, use $r->getCacheId() instead
		public function __construct(Request $r){
			$this->r = $r;
		}
		
		// check database for content that's still valid
		// and if this exists, save it for returning to the request
		public function isValid($id){
			$sth = $this->r->getDB()->prepare("SELECT content
						FROM cache
						WHERE id = :id
						AND cachetime + :time > UNIX_TIMESTAMP()");
			$sth->bindValue(":id", $id, PDO::PARAM_STR);
			$sth->bindValue(":time", $this->r->getOption("cacheTTL"), PDO::PARAM_INT);
			
			$sth->execute();
			
			// this fails if there's no content
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
		
		// saves content to cache and overwrites old values
		public function save($id, $content){
			$sth = $this->r->getDB()->prepare("INSERT INTO cache (id, cachetime, content)
						VALUES (:id, UNIX_TIMESTAMP(), :content)
						ON DUPLICATE KEY UPDATE cachetime = UNIX_TIMESTAMP(), content = :content");
			$sth->bindValue(":id", $id, PDO::PARAM_STR);
			$sth->bindValue(":content", $content, PDO::PARAM_STR);
			
			$sth->execute();
		}
		
		// removes an item from the cache
		public function clear($id){
			$sth = $this->r->getDB()->prepare("DELETE FROM cache
						WHERE id = :id");
			$sth->bindValue(":id", $id, PDO::PARAM_STR);
			
			$sth->execute();
		}
		
		// returns the content of the cache
		public function getContent(){
			return $this->content;
		}
	}