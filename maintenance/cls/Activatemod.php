<?php
	class Activatemod extends Maintenance {
		
		public function process($params){
			if (!isset($params[0])){
				throw new MException("Mod id not specified.");
			}
			$modId = $params[0];
			
			// also select devname and email for mailing updates
			$sth = $this->getDB()->prepare("SELECT id, name, devname, devemail, version, available
						FROM mods
						WHERE id = :id");
			$sth->bindValue(":id", $modId, PDO::PARAM_INT);
			$sth->execute();
			
			$modExists = $sth->fetch(PDO::FETCH_ASSOC);
			if (!empty($modExists)){
				// this mod is in the repo
				
				if ($modExists["available"] == 0){
					// check whether file has been uploaded yet
					
					$fileLocation = sprintf('%1$s../downloads/mods/%2$s/%3$d/%2$s.mod.dll', MBASE_PATH, $modExists["name"], $modExists["version"]);
					
					if (!file_exists($fileLocation)){
						throw new MException(sprintf("Mod %s has no version %d in the repo yet.", $modExists["name"], $modExists["version"]));
					} else { // file exists on server, nothing to stop us from activating now :)
						$sth = $this->getDB()->prepare("UPDATE mods
									SET available = 1
									WHERE id = :id");
						$sth->bindValue(":id", $modExists["id"], PDO::PARAM_INT);
						
						if ($sth->execute()){
							// send mail to developer to notify him of the update
							$m = new Mailer(new MailAddress("mods@scrollsguide.com", sprintf("%s Mod Repo", REPO_NAME)));
							
							$m->addRecipient(new MailAddress($modExists["devemail"], $modExists["devname"]));
							
							$m->setSubject("Your mod has been added.");

							$tpl = $m->getMustache()->loadTemplate("mod_activated.mustache");
							$m->setBody($tpl->render(array(
										"REPONAME" => REPO_NAME, 
										"REPOURL" => REPO_URL, 
										"DEVNAME" => $modExists["devname"], 
										"MODNAME" => $modExists["name"]
							)));
						
							$m->send();
						
							echo sprintf("Mod %s is now available. Wait for the cache to update.\n", $modExists["name"]);
						} else {
							throw new MException("Mot not made available, database error.");
						}
					}
				} else {
					throw new MException(sprintf("Mod %s is already available.", $modExists["name"]));
				}
			} else { // mod is not in the submission queue
				throw new MException(sprintf("Mod %d does not exist in the submission queue.", $modId));
			}
		}		
	}