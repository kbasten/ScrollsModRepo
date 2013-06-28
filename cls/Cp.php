<?php
	class Cp extends HTMLRequest {
		
		public function __construct(PDO $pdo){
			parent::__construct($pdo);
			
			// make sure none of the requests in the control panel are cached
			$this->setOption("cacheEnabled", false);
		}
		
		public function parseRequest($params){
			$required = array(
				array(
					"key" 		=> "url_1",
					"replace"	=> "/[^a-z]/",
					"possible"	=> array("submit", "edit", "update")
				)
			);
			
			$fields = Util::getRequired($params, $required);
			
			$this->loadMustache();
			
			$this->title = sprintf("Control panel - %s Summoner Mod Repository", REPO_NAME);
			
			// array used for rendering mustache
			$layoutParams = array();
			
			if ($fields["url_1"] == "submit"){
				if (isset($_POST["mod-name"])){
					
					// check whether a mod with this name already exists
					$sth = $this->getDB()->prepare("SELECT name
								FROM mods
								WHERE name = :name");
					$sth->bindValue(":name", $_POST["mod-name"], PDO::PARAM_STR);
					$sth->execute();
					
					$exists = $sth->fetch(PDO::FETCH_ASSOC);
					if (!empty($exists)){ // mod with this name already exists, don't submit
						$this->pageHeader = "Mod not submitted";
						
						$tpl = $this->m->loadTemplate("submit_error");
						
						$layoutParams["ERROR"] = "A mod with that name already exists in our repository. Please pick a different name and try again.";
					} else {
						// mod name does not exist yet, add mod to review queue
						// TODO: check actual post parameters...
						$sth = $this->getDB()->prepare("INSERT INTO submissions (name, description, longdesc, versionCode, 
												opensource, devname, devemail, ip, bfish)
									VALUES (:name, :description, :longdesc, :versionCode, :opensource, :devname, :devemail,
												:ip, :bfish)");
						
						$sth->bindValue(":name", $_POST["mod-name"], PDO::PARAM_STR);
						$sth->bindValue(":description", $_POST["mod-desc-short"], PDO::PARAM_STR);
						$sth->bindValue(":longdesc", $_POST["mod-desc-long"], PDO::PARAM_STR);
						$sth->bindValue(":versionCode", $_POST["mod-version"], PDO::PARAM_STR);
						$sth->bindValue(":opensource", $_POST["mod-repo"], PDO::PARAM_STR);
						$sth->bindValue(":devname", $_POST["mod-author"], PDO::PARAM_STR);
						$sth->bindValue(":devemail", $_POST["mod-author-email"], PDO::PARAM_STR);
						$sth->bindValue(":ip", $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
						// now use blowfish to encrypt the mod token, users can use a mod token to submit a new version
						$sth->bindValue(":bfish", Util::blowfishCrypt($_POST["mod-token"]), PDO::PARAM_STR);
						
						$sth->execute();
						
						// TODO: check for database errors
						$this->pageHeader = "Thanks for your submission";
						$tpl = $this->m->loadTemplate("submit_complete");
					}
				} else { // no post parameters submitted yet, show form for filling out details
					$this->pageHeader = "Submit A Mod";
					$tpl = $this->m->loadTemplate("submit");
				}
			} else if ($fields["url_1"] == "update"){ // developer is requesting an update
				$this->pageHeader = "Update A Mod";

				if (isset($_POST["mod-name"])){
					// get the mod's information first
					$sth = $this->getDB()->prepare("SELECT id, bfish
								FROM mods
								WHERE name = :name
								AND devemail = :email");
					$sth->bindValue(":name", $_POST["mod-name"], PDO::PARAM_STR);
					$sth->bindValue(":email", $_POST["mod-author-email"], PDO::PARAM_STR);
					
					$sth->execute();
					
					// check whether this mod exists and is from the developer with mod-author-email
					$mod = $sth->fetch(PDO::FETCH_ASSOC);
					
					if (!empty($mod)){
						// this mod is indeed submitted by mod-author-email, now check the blowfish
						
						if (crypt($_POST["mod-token"], $mod["bfish"]) == $mod["bfish"]){
							// this is the correct mod token, this user has permission to submit a new update
							// check whether this mod is still in the update queue
							if (!$this->isInUpdateQueue($mod["id"])){
								// add update to review queue
								$sth = $this->getDB()->prepare("INSERT INTO updatequeue (modid, submitted, ip, whatsnew, newversionCode)
											VALUES (:modid, UNIX_TIMESTAMP(), :ip, :whatsnew, :newversionCode)");
								$sth->bindValue(":modid", $mod["id"], PDO::PARAM_INT);
								$sth->bindValue(":ip", $_SERVER["REMOTE_ADDR"], PDO::PARAM_STR);
								$sth->bindValue(":whatsnew", $_POST["mod-new"], PDO::PARAM_STR);
								$sth->bindValue(":newversionCode", $_POST["mod-version"], PDO::PARAM_STR);
								
								$sth->execute();
							
								// submission of update is complete, show message
								// TODO: Add different message here
								$tpl = $this->m->loadTemplate("submit_complete");
							} else { // mod is already being reviewed
								$tpl = $this->m->loadTemplate("submit_error");
								
								$layoutParams["ERROR"] = "This mod is already in the update review queue, please wait for it to be processed.";
							}
						} else { // user didn't fill out the mod token correctly, don't allow updating
							$tpl = $this->m->loadTemplate("submit_error");
							
							$layoutParams["ERROR"] = "Incorrect mod token.";
						}
					} else { // this mod does not exist or is not submitted by mod-author-email
						$tpl = $this->m->loadTemplate("submit_error");
						
						$layoutParams["ERROR"] = sprintf("This mod does not exist or is not submitted by %s.", $_POST["mod-author-email"]);
					}
				} else { // no POST parameters yet, show the input page
					$tpl = $this->m->loadTemplate("submit_update");
				}
				
			}
			// REPONAME used on most pages, add here instead of in every if clause :)
			$layoutParams["REPONAME"] = REPO_NAME;
			$layoutParams["REPOURL"] = REPO_URL;
			
			// and finally render the page with the parameters as set above
			$cpPage = $tpl->render($layoutParams);
			$this->setResult(true, $this->getHTMLContent($cpPage));
		}
		
		// returns true if mod with id $modid is already in the update queue
		private function isInUpdateQueue($modid){
			$sth = $this->getDB()->prepare("SELECT id
						FROM updatequeue
						WHERE modid = :id");
			$sth->bindValue(":id", $modid, PDO::PARAM_INT);
			
			$sth->execute();
			$inQueue = $sth->fetch();
			
			return !empty($inQueue);
		}
	}