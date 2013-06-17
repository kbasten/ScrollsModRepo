<?php
	class Index extends HTMLRequest {
	
		public function parseRequest($params){
			$this->loadMustache();
			
			$this->title = sprintf("Index - %s Summoner Mod Repository", REPO_NAME);
			$this->pageHeader = "Repository index";
			
			$tpl = $this->m->loadTemplate("index");
		
			$sth = $this->getDB()->prepare("SELECT identifier, name, description, longdesc, versionCode, downloads,
									opensource, lastUpdate
						FROM mods
						ORDER BY name ASC");
			$sth->execute();
		
			$modList = array();
			while ($mod = $sth->fetch(PDO::FETCH_ASSOC)){
				$modList[] = array(
							"NAME"		=> $mod['name'],
							"DESC"		=> $mod['description'],
							"LONGDESC"	=> $mod['longdesc'],
							"OPENSOURCE"=> $mod['opensource'],
							"VCODE"		=> $mod['versionCode'],
							"LASTUPDATE"=> date("M jS Y", $mod['lastUpdate'])
				);
			}
			
			$fullPage = $tpl->render(array(
						"REPONAME"		=> REPO_NAME,
						"REPOURL"		=> REPO_URL,
						"MODS"			=> $modList
			));
		
			$this->setResult(true, $this->getHTMLContent($fullPage));
		}
	}