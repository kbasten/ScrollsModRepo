<?php
	/**
	 * Main download class, used for downloading the modloader and mods
	 */
	class Download extends DownloadRequest {
	
		public function parseRequest($params){
			$required = array(
				array(
					"key"		=> "url_1",
					"replace"	=> "/[^a-z]/",
					"possible"	=> array("installer", "update", "mod")
				),
				array(
					"key" 		=> "url_2", // need to know the id of the mod
					"replace"	=> "/[^a-z0-9]/",
					"optional"	=> true
				)
			);
			
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
			
			print_r($params);
			$fields = Util::getRequired($params, $required);
			
			if ($fields['url_1'] == "installer"){
				if (!isset($fields['url_2'])){
					throw new ApiException("Missing required parameter 'platform'.", 100);
				} else if (!in_array($fields['url_2'], array_keys($platformConfig))){
					throw new ApiException("Value '" . $fields['url_2'] . "' not possible for field 'platform'.", 105);
				}
				
				$platform = $fields['url_2'];
				
				$this->setResult(true, "");
				$this->setFilePath(sprintf("downloads/%s/installer.%s", $platform, $platformConfig[$platform]["extension"]));
				$this->setHeader("Content-type", $platformConfig[$platform]["type"]);
				$this->setHeader("Content-Disposition", sprintf("attachment; filename=\"Summoner-%s.%s\"", $platform, $platformConfig[$platform]["extension"]));
			} else if ($fields['url_1'] == "update"){
				$this->setResult(true, "");
			
				$this->setFilePath("update/ScrollsModLoader.dll");
				$this->setHeader("Content-type", "application/x-msdos-program");
			} else if ($fields['url_1'] == "mod"){
				$sth = $this->pdo->prepare("SELECT name, id, version
							FROM mods
							WHERE identifier = ?");
				$sth->bindValue(1, $fields['url_2'], PDO::PARAM_STR);
				$sth->execute();

				$mod = $sth->fetch(PDO::FETCH_ASSOC);
				
				if (empty($mod)){
					// mod does not exist
					$this->setResult(false, "Download does not exist.");
				} else {
					$this->setResult(true, "");					
					$this->setFilePath(sprintf("downloads/mods/%d/%s.mod.dll", $mod['id'], $mod['name']));
					
					// add header for dll files
					$this->setHeader("Content-type", "application/x-msdos-program");
					
					// add option to download file with original name
					if (isset($_GET['realname'])){
						$this->setHeader("Content-Disposition", sprintf("attachment; filename=\"%s.mod.dll\"", $mod['name']));
					}
				}
			}
		}
	}