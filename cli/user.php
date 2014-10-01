#!/usr/bin/php5
<?php

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
