<?php
	/**
	 * General repository information containing the name and number of mods
	 */
	class Repoinfo extends JSONRequest {
	
		public function parseRequest($params){
			$required = array(
				array(
					"key" 		=> "url_1",
					"replace"	=> "/[^a-z0-9]/",
					"possible"	=> array("mod"),
					"optional"	=> true
				),
				array(
					"key"		=> "url_2", // id of the mod
					"replace"	=> "/[^a-z0-9]/",
					"optional"	=> true
				)
			);
			
			$fields = Util::getRequired($params, $required);
			
			// this is just the general repo info
			if (!isset($fields["url_1"])){
				$sth = $this->getDB()->prepare("SELECT COUNT(*) AS c
							FROM mods
							WHERE available = 1");
				$sth->execute();
				
				$result = $sth->fetch(PDO::FETCH_ASSOC);
				
				$modsInRepo = !empty($result) ? $result["c"] : 0;
				
				$this->setResult(true, array(
							"name" 	=> REPO_NAME, 
							"url" 	=> REPO_URL, 
							"version"	=> 1,
							"mods" 	=> $modsInRepo
				));
			} else { // detailed mod info
				if (isset($fields["url_2"]) && !empty($fields["url_2"])){
					$modId = $fields["url_2"];
					
					$sth = $this->getDB()->prepare("SELECT name, description, longdesc, devname, downloads
								FROM mods
								WHERE identifier = :id");
					$sth->bindValue(":id", $modId);
					$sth->execute();
					
					$result = $sth->fetch(PDO::FETCH_ASSOC);
					
					if (!empty($result)){
						$this->setResult(true, $result);
					} else {
						$this->setResult(false, sprintf("Couldn't load data for mod '%s'.", $modId));
					}
				} else {
					throw new ApiException("Missing required parameter 'url_2'.", ErrorCode::E_MISS_REQ_KEY);
				}
			}
		}
		
		public function getCacheId($params){
			if (isset($params["url_1"])){
				return sprintf("repoinfo:%s:%s", $params["url_1"], isset($params["url_2"]) ? $params["url_2"] : "");
			} else {
				return "repoinfo";
			}
		}
	}