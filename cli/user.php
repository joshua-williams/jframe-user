#!/usr/bin/php5
<?php

require_once(dirname(dirname(dirname(__DIR__))). "/swfx/jframe/lib/CLI.php");

class CLI extends \JFrame\CLI{
	
	function getuser(){
		
	}
	
	function adduser(){
		require_once("$this->path/cli/add_user.php");
	}
	
	function deleteuser(){
		require_once("$this->path/cli/delete_user.php");
	}
	
	function changepwd(){
		require_once("$this->path/cli/change_password.php");
	}
	
	function install(){
		require_once("$this->path/cli/install.php");
		
	}
}

$cli = new CLI();

?>
