<?php
	define("IN_API", true);
	$dir = dirname(__FILE__);
	
	if (substr($dir, -1) != "/"){
		$dir .= "/";
	}
	
	define("MBASE_PATH", $dir);
	
	require_once MBASE_PATH . "../config.php";	
	require_once "Maintenance.php";
	require_once "cls/Mailer.php";
	
	if (!isset($argv[1])){
		echo "No function specified.\n";
		exit;
	} else {
		$className = $argv[1];
		$class = sprintf("%scls/%s.php", MBASE_PATH, $className);
		if (file_exists($class)){
			require_once $class;
			
			$pdo = new PDO(sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME), DB_USER, DB_PASS);
			
			$c = new $className($pdo);
			
			try {
				$c->process(array_slice($argv, 2));
			} catch (MException $e){
				echo $e;
			}
		} else {
			echo "Class " . $className . " does not exist.\n";
			exit;
		}
	}