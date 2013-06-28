<?php
	class Addmod extends Maintenance {
		
		public function process($params){
			if (!isset($params[0])){
				throw new MException("Mod id not specified.");
			}
			$modId = $params[0];
			
			$sth = $this->getDB()->prepare("SELECT id, name, description, longdesc, versionCode, opensource, 
									devname, devemail, bfish
						FROM submissions
						WHERE id = :id");
			$sth->bindValue(":id", $modId, PDO::PARAM_INT);
			$sth->execute();
			
			$modExists = $sth->fetch(PDO::FETCH_ASSOC);
			if (!empty($modExists)){
				// this mod is in the modqueue
				// check whether it's not yet in the mod database
				
				$sth = $this->getDB()->prepare("SELECT id
							FROM mods
							WHERE name = :modname");
				$sth->bindValue(":modname", $modExists["name"], PDO::PARAM_STR);
				$sth->execute();
				
				$modInRepo = $sth->fetch();
				
				if (empty($modInRepo)){ // mod is not yet in repo
					// add mod to repo
					// get latest id
					$sth = $this->getDB()->prepare("SELECT MAX(id) AS m
								FROM mods");
					$sth->execute();
					$maxId = $sth->fetch(PDO::FETCH_ASSOC);
					
					$nextId = 1;
					if (!empty($maxId)){ // there are mods in the repo already, if not use 1
						$nextId = $maxId["m"] + 1;
					}
					
					$identifier = md5("mod" . $nextId);
					
					$sth = $this->getDB()->prepare("INSERT INTO mods (id, identifier, name, description, longdesc, version, versionCode, lastupdate,
											opensource, devname, devemail, bfish)
								VALUES (:id, :identifier, :name, :description, :longdesc, 1, :versionCode, UNIX_TIMESTAMP(),
											:opensource, :devname, :devemail, :bfish)");
					$sth->bindValue(":id", $nextId, PDO::PARAM_INT);
					$sth->bindValue(":identifier", $identifier, PDO::PARAM_STR);
					$sth->bindValue(":name", $modExists["name"], PDO::PARAM_STR);
					$sth->bindValue(":description", $modExists["description"], PDO::PARAM_STR);
					$sth->bindValue(":longdesc", $modExists["longdesc"], PDO::PARAM_STR);
					$sth->bindValue(":versionCode", $modExists["versionCode"], PDO::PARAM_STR);
					$sth->bindValue(":opensource", $modExists["opensource"], PDO::PARAM_STR);
					$sth->bindValue(":devname", $modExists["devname"], PDO::PARAM_STR);
					$sth->bindValue(":devemail", $modExists["devemail"], PDO::PARAM_STR);
					$sth->bindValue(":bfish", $modExists["bfish"], PDO::PARAM_STR);
					
					if ($sth->execute()){
						echo sprintf("Mod %s with id %d added to the repo, available = 0.\n", $modExists["name"], $nextId);
						// mod is added, remove it from the submission queue
						
						$sth = $this->getDB()->prepare("DELETE FROM submissions
									WHERE id = :id");
						$sth->bindValue(":id", $modExists["id"], PDO::PARAM_INT);
						$sth->execute();
					} else {
						throw new MException("Error adding mod to repo.");
					}
				} else { // mod exists already in the repo
					throw new MException(sprintf("Mod %s is already in the repo.", $modExists["name"]));
				}
			} else { // mod is not in the submission queue
				throw new MException(sprintf("Mod %d does not exist in the submission queue.", $modId));
			}
		}		
	}