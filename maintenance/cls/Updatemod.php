<?php
	class Updatemod extends Maintenance {
		
		public function process($params){
			if (!isset($params[0])){
				throw new MException("Mod id not specified.");
			}
			$modId = $params[0];
			
			$sth = $this->getDB()->prepare("SELECT id, name, version, versionCode, available, devname, devemail
						FROM mods
						WHERE id = :id");
			$sth->bindValue(":id", $modId, PDO::PARAM_INT);
			$sth->execute();
			
			$modExists = $sth->fetch(PDO::FETCH_ASSOC);
			if (!empty($modExists)){
				// this mod is in the repo
				
				if ($modExists["available"] == 0){
					throw new MException("This mod is not made available yet.");
				} else { // mod is already available
					$newVersion = $modExists["version"] + 1;
					
					$fileLocation = sprintf('%1$s../downloads/mods/%2$s/%3$d/%2$s.mod.dll', MBASE_PATH, $modExists["name"], $newVersion);
					
					if (!file_exists($fileLocation)){
						throw new MException(sprintf("Mod %s has no version %d in the repo yet.", $modExists["name"], $modExists["version"]));
					} else { // file exists on server, nothing to stop us from activating now :)
						// check the update queue whether new information has been submitted
						$sth = $this->getDB()->prepare("SELECT submitted, newversionCode
									FROM updatequeue
									WHERE modid = :id");
						$sth->bindValue(":id", $modExists["id"], PDO::PARAM_INT);
						$sth->execute();
						$newVersionInfo = $sth->fetch(PDO::FETCH_ASSOC);
						
						$lastUpdate = time(); // use current time if there's no update
						$versionCode = $modExists["versionCode"]; // use old version code if new code is not submitted
						if (!empty($newVersionInfo)){
							$lastUpdate = $newVersionInfo["submitted"];
							$versionCode = $newVersionInfo["newversionCode"];
							
							// and remove update from queue
							$sth = $this->getDB()->prepare("DELETE FROM updatequeue
										WHERE modid = :id");
							$sth->bindValue(":id", $modExists["id"], PDO::PARAM_INT);
							if ($sth->execute()){
								echo "Mod update request removed from queue.\n";
							} else {
								echo "Mod update was not in queue.\n";
							}
						}
					
						// now update the mod in the database so the update is public
						$sth = $this->getDB()->prepare("UPDATE mods
									SET version = :newversion, versionCode = :newversionCode, lastupdate = :lastupdate
									WHERE id = :id");
						$sth->bindValue(":newversion", $newVersion, PDO::PARAM_INT);
						$sth->bindValue(":newversionCode", $versionCode, PDO::PARAM_STR);
						$sth->bindValue(":lastupdate", $lastUpdate, PDO::PARAM_INT);
						$sth->bindValue(":id", $modExists["id"], PDO::PARAM_INT);
						
						if ($sth->execute()){							
							// send mail to developer to notify him of the update
							$m = new Mailer(new MailAddress("mods@scrollsguide.com", sprintf("%s Mod Repo", REPO_NAME)));
							
							$m->addRecipient(new MailAddress($modExists["devemail"], $modExists["devname"]));
							
							$m->setSubject("Your mod has been updated.");

							$tpl = $m->getMustache()->loadTemplate("mod_updated.mustache");
							$m->setBody($tpl->render(array(
										"REPONAME" => REPO_NAME, 
										"REPOURL" => REPO_URL, 
										"DEVNAME" => $modExists["devname"], 
										"MODNAME" => $modExists["name"],
										"VERSION" => $newVersion,
										"VERSIONCODE" => $versionCode
							)));
						
							$m->send();
							
							echo sprintf("Mod %s is now updated to version %d. Wait for the cache to update.\n", $modExists["name"], $newVersion);
						} else {
							throw new MException("Mot not made available, database error.");
						}
					}
				}
			} else { // mod is not in the submission queue
				throw new MException(sprintf("Mod %d does not exist in the submission queue.", $modId));
			}
		}		
	}