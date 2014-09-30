#!/usr/bin/php5
<?php
require_once("../../../vendor/jframe/lib/CLI.php");
DEFINE('PATH_CLI', __DIR__);

class CLI extends \JFrame\CLI{
	
	function install(){
		require_once(PATH_CLI.'/install.php');
		
	}
}

$cli = new CLI();

?>
