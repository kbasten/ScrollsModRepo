<?php
	class Mod extends HTMLRequest {
	
		public function parseRequest($params){
			$required = array(
				array(
					"key" 		=> "url_1", // id of the mod/platform
					"replace"	=> "/[^a-z0-9]/"
				)
			);
			
			$fields = Util::getRequired($params, $required);
		
			$sth = $this->getDB()->prepare("SELECT name, description, longdesc, versionCode, downloads,
									bugs, devname, opensource, lastUpdate
						FROM mods
						WHERE identifier = :id");
			$sth->bindValue(":id", $fields["url_1"], PDO::PARAM_STR);
			$sth->execute();
		
			$mod = $sth->fetch(PDO::FETCH_ASSOC);
			
			if (!empty($mod)){
				$this->loadMustache();
				$this->title = sprintf("%s - %s Summoner Mod Repository", $mod["name"], REPO_NAME);
				$this->pageHeader = $mod["name"];
				
				$tpl = $this->m->loadTemplate("mod");
				
				$page = $tpl->render(array(
							"LONGDESC"		=> $mod["longdesc"],
							"DEV"			=> $mod["devname"],
							"OPENSOURCE"	=> $mod["opensource"],
							"VCODE"			=> $mod["versionCode"],
							"LASTUPDATE"	=> date("M jS Y", $mod["lastUpdate"]),
							"DOWNLOADS"		=> $mod["downloads"],
							"BUGS"			=> $mod["bugs"] != "" ? $mod["bugs"] : "None",
							"REPONAME"		=> REPO_NAME
				));
			
				$this->setResult(true, $this->getHTMLContent($page));
			} else {
				$this->set404(sprintf("That's an error. Mod '%s' not found in this repository.", $fields["url_1"]));
			}
		}
		
		public function getCacheId($params){
			return "modlist/" . $params["url_1"];
		}
	}