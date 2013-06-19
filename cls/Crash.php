<?php
	class Crash extends JSONRequest {
	
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			$this->setOption("cacheEnabled", false);
		}
	
		public function parseRequest($params){
			$required = array(
				"GET"	=> array(
					array(
						"key"		=> "url_1", // type of the download (installer, update, mod)
						"replace"	=> "/[^a-z]/",
						"possible"	=> array("mod", "modloader")
					),
					array(
						"key" 		=> "url_2", // name of the mod
						"replace"	=> "/[^a-z0-9]/",
						"optional"	=> true
					),
					array(
						"key" 		=> "url_3", // name of the mod
						"replace"	=> "/[^a-z0-9]/",
						"optional"	=> true
					)
				), 
				"POST"	=> array(
					array(
						"key"		=> "os",
						"replace"	=> "/[^a-zA-Z]/",
						"possible"	=> array("Win", "Mac", "Unix", "Unknown")
					),
					array(
						"key"		=> "version",
						"replace"	=> "/[^0-9]/"
					),
					array(
						"key"		=> "exception",
					)
				)
			);
			
			$fields = array(
						"GET"	=> Util::getRequired($params, $required["GET"]),
						"POST"	=> Util::getRequired($_POST, $required["POST"])
			);
			
			if ($fields["GET"]["url_1"] == "mod"){ // mod error
				if (!isset($fields["GET"]["url_2"])){
					throw new ApiException("Missing required key 'url_2'.", ErrorCode::E_MISS_REQ_KEY);
				}
				
				$modId = $fields["GET"]["url_2"];
			} else { // summoner error
				$modId = "Summoner";
			}
			
			$sth = $this->getDB()->prepare("INSERT INTO exceptions (`mod`, time, os, version, exception)
						VALUES (:modid, UNIX_TIMESTAMP(), :os, :version, :exception)");			
			
			$sth->bindValue(":modid", $modId, PDO::PARAM_STR);
			$sth->bindValue(":os", $fields["POST"]["os"], PDO::PARAM_STR);
			$sth->bindValue(":version", $fields["POST"]["version"], PDO::PARAM_INT);
			$sth->bindValue(":exception", $fields["POST"]["exception"], PDO::PARAM_STR);
			
			$sth->execute();
			
			$this->setResult(true, "Crash report submitted.");
		}
		
	}