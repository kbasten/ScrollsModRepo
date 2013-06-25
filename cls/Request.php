<?php
	abstract class Request {
	
		private $pdo; // mod database
		private $succeeded = false;
		protected $result = "";
		
		private $opts = array(
					"floodSeconds"	=> 6, // more than 20 requests in 6 seconds
					"floodRequests"	=> 20,
					"cacheEnabled"	=> true,
					"cacheTTL"		=> 300 // cache everything for 5 minutes by default
		);
		
		private $headers = array();
		
		public function __construct(PDO $pdo){
			$this->pdo = $pdo;
		}
	
		public abstract function parseRequest($params);
		
		public abstract function getType(); // one of the TYPE enums
		
		// override if needed
		public function canCache(){
			return true;
		}
		
		// override if needed
		protected function formatCacheContent($input){ return $input; }
		
		// override if needed
		protected function deformatCacheContent($input){ return $input;	}
		
		// looks in the cache and returns content if still valid, inserts
		// new content otherwise.
		public function processCache($params){
			if ($this->getOption("cacheEnabled")){
				// check the database for cache
				$c = new Cache($this);
				
				$cacheId = $this->getCacheId($params);
				
				// check whether cache is valid, and if so, retrieve content
				// from the cache. Content is loaded into the cache with
				// this call.
				if ($c->isValid($cacheId)){
					// parse cache content back from string to suitable object with
					// deformatCacheContent method different for every request type
					$this->setResult(true, $this->deformatCacheContent($c->getContent()));
				} else {
					// execute request like it would if there was no cache
					$this->parseRequest($params);
					
					// format result into string for db storage
					$cacheContent = $this->formatCacheContent($this->result);
					
					// save formatted result to cache
					$c->save($cacheId, $cacheContent);
				}
			} else {
				// caching is not enabled, just parse the request
				$this->parseRequest($params);
			}
		}
		
		final protected function setResult($success, $result){
			$this->succeeded = $success;
			$this->result = $result;
		}
		
		// adds header to the request
		final protected function setHeader($id, $content){
			$this->headers[$id] = $content;
		}
		
		final public function getHeaders(){
			return $this->headers;
		}
		
		final public function getResult(){
			$msg = $this->result;
			
			return array($this->succeeded, $msg);
		}
		
		public function isFlooding($ip){
			// check for flood (more than 10 requests in the last 6 seconds)
			$sth = $this->getDB()->prepare("SELECT COUNT(*) AS r
						FROM requests
						WHERE time > UNIX_TIMESTAMP() - :seconds
						AND ip = :ip");
			$sth->bindValue(":seconds", $this->getOption("floodSeconds"), PDO::PARAM_INT);
			$sth->bindValue(":ip", $ip, PDO::PARAM_STR);
			$sth->execute();
			
			$requestCount = $sth->fetch(PDO::FETCH_ASSOC);
			
			// done more requests than allowed?
			return $requestCount["r"] > $this->getOption("floodRequests");
		}
		
		// default cache id function, doesn't work so well when the user
		// starts adding random parameters, so override this if possible
		protected function getCacheId($params){
			// first sort params by key
			ksort($params);
			
			// then make a query string from the thing and hash it, key done! :)
			// as mentioned, key differs with random user-added parameters
			
			return md5(http_build_query($params));		
		}
		
		final public function setOption($key, $value){
			$this->opts[$key] = $value;
		}
		
		final public function getOption($key){
			return $this->opts[$key];
		}
		
		public function getDB(){
			return $this->pdo;
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