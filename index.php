<?php
	define("IN_API",	true);
	// error_reporting(0);
	
	header("Access-Control-Allow-Origin: *");
	
	// set the correct ip for cloudflare system, currently disabled
	$_SERVER["REMOTE_ADDR"] = isset($_SERVER["HTTP_CF_CONNECTING_IP"]) ? $_SERVER["HTTP_CF_CONNECTING_IP"] : $_SERVER["REMOTE_ADDR"]; 
	require_once "config.php";
	
	// keep track of execution time for logging later on
	$startTime = microtime(true);
	
	$url = strtok($_SERVER["REQUEST_URI"], "?");
	
	$urlParts = explode("/", $url);
	array_shift($urlParts); // shift the first empty element, because REQUEST_URI always starts with a /
	
	for ($i = 0; $i < count($urlParts); $i++){
		$urlParts[$i] = preg_replace("/[^a-zA-Z0-9]/", "", $urlParts[$i]);
	}

	$className = ucfirst($urlParts[0]);	
	// show the html file for index if no other request is specified
	if ($className == ""){
		$className = "Index";
	}
	
	$pathToModule = sprintf("cls/%s.php", $className);
	
	$result = array(false);
	$msg = "";
	$executionTime = 0;
	
	$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME), DB_USER, DB_PASS);
	try {
		try {
			require_once "cls/ApiException.php";
			// check whether this class exists and throw an exception otherwise
			if (file_exists($pathToModule)){
				require_once "cls/Util.php";
				require_once "cls/Request.php";
				
				require_once $pathToModule;
				
				// check for flood (more than 10 requests in the last 6 seconds)
				$sth = $pdo->prepare("SELECT COUNT(*) AS r
							FROM requests
							WHERE time > UNIX_TIMESTAMP() - 6
							AND ip = ?");
				$sth->bindValue(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
				$sth->execute();
				
				$requestCount = $sth->fetch(PDO::FETCH_ASSOC);
				
				if ($requestCount['r'] > 10){
					throw new ApiException("Rate limit exceeded.", ErrorCode::E_RATE_LIM_EXCEEDED);
				} else {
					$r = new $className($pdo);
					
					$params = array();
					
					for ($i = 0; $i < count($urlParts); $i++){
						$params['url_' . $i] = $urlParts[$i];
					}
					
					foreach ($_GET as $key => $p){
						$params[$key] = urldecode($p);
					}
					
					$r->parseRequest($params);
					
					$result = $r->getResult();
					
					// add headers for different content types etc.
					foreach ($r->getHeaders() as $headerId => $headerContent){
						header(sprintf("%s: %s", $headerId, $headerContent));
					}
					
					$executionTime = (int)((microtime(true) - $startTime) * 1000);
					// most of the requests will be of type json
					if ($r->getType() == TYPE::JSON){ // rather this than ($r instanceof JSONRequest)
						$out = array(
							"msg"	=> $result[0] ? "success" : "fail",
							"data"	=> $result[1]
						);
						
						if (isset($_GET['d'])){
							$out['time'] = $executionTime;
						}
							
						echo json_encode($out, $r->getJsonEncodeOption());
					} else if ($r->getType() == TYPE::HTML){
						if ($result[0]){
							echo $r->getHTMLContent();
						} else {
							echo $r->p404();
						}
					} else if ($r->getType() == TYPE::DOWNLOAD){
						if ($result[0]){
							$r->download();
						} else {
							header("Content-type: application/json");
							echo json_encode(array("msg" => "fail", "data" => $result[1]));
						}
					}
				}
			} else {
				$msg = "No such method '$className'.";
				throw new ApiException($msg, ErrorCode::E_NO_SUCH_METHOD);
			}
		} catch (PDOException $e){
			// don't show the database errors to the user but log the message in our db
			$msg = "Database exception. " . $e->getMessage();
			throw new ApiException("Database exception.", ErrorCode::E_DATABASE);
		}
	} catch (ApiException $e){
		// ApiExceptions are always formatted as json
		header("Content-type: application/json");
		$msg = $e;
		echo $msg;
	}
	
	$sth = $pdo->prepare("INSERT INTO requests (ip, time, request, msg, success, exectime)
				VALUES (?, UNIX_TIMESTAMP(), ?, ?, ?, ?)");
	$sth->bindValue(1, $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR);
	$sth->bindValue(2, $_SERVER['REQUEST_URI'], PDO::PARAM_STR);
	$sth->bindValue(3, $msg, PDO::PARAM_STR);
	$sth->bindValue(4, $result[0] ? 1 : 0, PDO::PARAM_INT);
	$sth->bindValue(5, $executionTime, PDO::PARAM_INT);
	$sth->execute();
	$pdo = null;