<?php
	if (!defined("IN_API")){ exit; }
	
	define("DB_HOST",		"");
	define("DB_USER",		"");
	define("DB_PASS",		"");

	define("DB_NAME",		"");
	
	define("REPO_NAME",		"");
	define("REPO_URL",		"");
	
	function __autoload($name){
		require_once "cls/" . $name . ".php";
	}