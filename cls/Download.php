<?php
	/**
	 * Main download class, used for downloading the modloader and mods
	 */
	class Download extends DownloadRequest {
	
		public function parseRequest($params){
			$required = array(
				array(
					"key" 		=> "url_1", // need to know the id of the mod
					"replace"	=> "/[^a-z0-9]/"
				)
			);
			
			$fields = Util::getRequired($params, $required);
			
			$sth = $this->pdo->prepare("SELECT name, id, version
						FROM mods
						WHERE identifier = ?");
			$sth->bindValue(1, $fields['url_1'], PDO::PARAM_STR);
			$sth->execute();

			$mod = $sth->fetch(PDO::FETCH_ASSOC);
			
			if (empty($mod)){
				// mod does not exist
				$this->setResult(false, "Mod does not exist.");
			} else {
				$this->setResult(true);
				// add header for dll files
				$this->setHeader("Content-type", "application/x-msdos-program");
				
				$this->setFilePath(sprintf("mods/%d/%s.mod.dll", $mod['id'], $mod['name']));
				
				// add option to download file with original name
				if (isset($_GET['realname'])){
					$this->setHeader("Content-Disposition", sprintf("attachment; filename=\"%s.mod.dll\"", $mod['name']));
				}
			}
		}
	}