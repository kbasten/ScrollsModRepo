<?php
	/**
	 * Main download class, used for downloading the modloader and mods
	 */
	class Download extends DownloadRequest {
	
		public function parseRequest($params){
			$required = array(
				array(
					"key"		=> "url_1", // type of the download (installer, update, mod)
					"replace"	=> "/[^a-z]/",
					"possible"	=> array("installer", "update", "mod")
				),
				array(
					"key" 		=> "url_2", // id of the mod/platform
					"replace"	=> "/[^a-z0-9]/",
					"optional"	=> true
				)
			);
			
			$fields = Util::getRequired($params, $required);
			
			$platformConfig = array(
						"windows"	=> 
							array(
								"extension"	=> "exe",
								"type"		=> "application/x-msdos-program"
							),
						"mac"		=> 
							array(
								"extension"	=> "dmg",
								"type"		=> "application/x-apple-diskimage"
							)
			);
			
			if ($fields["url_1"] == "installer"){
				// check the platform
				if (!isset($fields["url_2"])){
					throw new ApiException("Missing required parameter 'platform'.", ErrorCode::E_MISS_REQ_KEY);
				} else if (!in_array($fields["url_2"], array_keys($platformConfig))){
					throw new ApiException("Value '" . $fields["url_2"] . "' not possible for field 'platform'.", ErrorCode::E_VAL_NOT_POSSIBLE);
				}
				
				$platform = $fields["url_2"];
				
				$this->setResult(true, "");
				
				$this->setFilePath(sprintf("downloads/%s/%d/installer.%s", $platform, Version::$version, $platformConfig[$platform]["extension"]));
				$this->setHeader("Content-type", $platformConfig[$platform]["type"]);
				
				// display a more user-friendly name instead of installer.exe/dmg
				$this->setHeader("Content-Disposition", sprintf("attachment; filename=\"Summoner-%s.%s\"", $platform, $platformConfig[$platform]["extension"]));
			} else if ($fields["url_1"] == "update"){
				$this->setResult(true, "");
			
				$this->setFilePath(sprintf("downloads/update/%d/updater.exe", Version::$version));
				$this->setHeader("Content-type", "application/x-msdos-program");
				// no need to set a user-friendly name here, this is only called by 
				// the framework from in-game, no browser interaction
			} else if ($fields["url_1"] == "mod"){
				$sth = $this->getDB()->prepare("SELECT name, id, version
							FROM mods
							WHERE identifier = :id");
				$sth->bindValue(":id", $fields["url_2"], PDO::PARAM_STR);
				$sth->execute();

				$mod = $sth->fetch(PDO::FETCH_ASSOC);
				
				if (empty($mod)){
					// mod does not exist
					$this->setResult(false, "Download does not exist.");
				} else {				
					$sth = $this->getDB()->prepare("UPDATE mods
								SET downloads = downloads + 1
								WHERE id = :id");
					$sth->bindValue(":id", $mod["id"], PDO::PARAM_INT);
					$sth->execute();
				
					$this->setResult(true, "");
					
					// single quotes because $s shouldn't be evaluated :)
					$this->setFilePath(sprintf('downloads/mods/%1$s/%2$d/%1$s.mod.dll', $mod["name"], $mod["version"]));
					
					// add header for dll files
					$this->setHeader("Content-type", "application/x-msdos-program");
					
					// add option to download file with original name
					if (isset($_GET["realname"])){
						$this->setHeader("Content-Disposition", sprintf("attachment; filename=\"%s.mod.dll\"", $mod["name"]));
					}
				}
			}
		}
	}