<?php
	/**
	 * General repository information containing the name and number of mods
	 */
	class Repoinfo extends JSONRequest {
	
		public function parseRequest($params){
			$sth = $this->pdo->prepare("SELECT COUNT(*) AS c
						FROM mods");
			$sth->execute();
			
			$result = $sth->fetch(PDO::FETCH_ASSOC);
			
			$modsInRepo = 0;
			if (!empty($result)){
				$modsInRepo = $result['c'];
			}
			
			$this->setResult(true, array(
						"name" 	=> REPO_NAME, 
						"url" 	=> REPO_URL, 
						"version"	=> REPO_VERSION,
						"mods" 	=> $modsInRepo
			));
		}
	}