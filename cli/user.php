#!/usr/bin/php5
<?php

require_once(dirname(dirname(dirname(__DIR__))). "/vendor/jframe/lib/CLI.php");

class CLI extends \JFrame\CLI{
	
	function getuser(){
		
	}
	
	function adduser(){
		require_once(PATH_CLI . '/add_user.php');
	}
	
	function deleteuser(){
		require_once(PATH_CLI . '/delete_user.php');
	}
	
	function changepwd(){
		require_once(PATH_CLI . '/change_password.php');
	}
	
	function install(){
		require_once(PATH_CLI.'/install.php');
		
	}
}

$cli = new CLI();

?>
