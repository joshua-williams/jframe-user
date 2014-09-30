#!/usr/bin/php5
<?php
DEFINE('PATH', dirname(dirname(dirname(__DIR__))));
DEFINE('PATH_MODULES', PATH. '/modules');
DEFINE('PATH_USER', PATH_MODULES.'/User');
DEFINE('PATH_CLI', __DIR__);
DEFINE('PATH_CONFIG', PATH.'/config');

require_once(PATH. "/vendor/jframe/lib/CLI.php");

class CLI extends \JFrame\CLI{
	
	function getuser(){
		
	}
	
	function changepwd(){
		
	}
	
	function install(){
		require_once(PATH_CLI.'/install.php');
		
	}
}

$cli = new CLI();

?>
