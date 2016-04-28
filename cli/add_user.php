<?php

require_once(PATH_JFRAME.'/lib/DB.php');
require_once(PATH_JFRAME.'/lib/Vars.php');

if(!is_file("config/databases.php")) $this->setError('Config not found. Please run command from application root');
$dbConfig = include("config/databases.php");
$config = include("config/config.php");
$hash = $config['hash'];
if(!is_array($dbConfig)) $this->setError('Invalid database config.');
$db = new JFrame\DB($dbConfig['default']);

$user['email'] = $this->getResponse('Email Address');
if(!$user['email']) $this->setError('User must have an email address');
$exists = $db->loadResult("SELECT user_id FROM users WHERE email=:email", $user);
if($exists) $this->setError('User already exists.');
$user['first_name'] = $this->getResponse('First Name');
$user['last_name'] = $this->getResponse('Last Name');
$user['passwd'] = $this->getResponse('Password');
$user['passwd'] = md5($hash.$user['passwd']);

$userGroups = $db->loadResult("
	SELECT GROUP_CONCAT(group_id, ' - ', title, '\n' SEPARATOR '') FROM groups WHERE type_id = 1 AND title != 'webmaster'
");
$groups = $this->getResponse("Choose user groups.".PHP_EOL.$userGroups.'Comma seperated group ids');
if(!$groups) $this->setError('User must belong to at least one group');
$groups = explode(',', str_replace(' ', '', $groups));

$this->write("Creating user...");
$user_id = $db->query("
	INSERT INTO users (first_name, last_name, email, passwd) VALUES (:first_name, :last_name, :email, :passwd)
", $user);

$this->write("Adding user to groups...");
if(!$user_id) $this->setError('Failed to create new user');
foreach($groups as $ugid){
	if(!$db->loadResult("SELECT group_id FROM groups WHERE type_id=1 AND group_id=$ugid")){
		$this->write("$ugid is not a valid user group");
		continue;
	}
	$db->query("INSERT INTO table_map (gt_id, src_id, rel_id) VALUES (1, $user_id, $ugid) ");
	$this->write("User added to $ugid user group id");
}
$this->write("User has been created");
?>