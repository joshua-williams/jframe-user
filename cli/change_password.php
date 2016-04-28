<?php

require_once(PATH_JFRAME.'/lib/DB.php');
require_once(PATH_JFRAME.'/lib/Vars.php');

if(!is_file("config/databases.php")) $this->setError('Config not found. Please run command from application root');
$dbConfig = include("config/databases.php");
$userConfig = include("config/user.php");
$hash = $userConfig['hash'];

if(!is_array($dbConfig)) $this->setError('Invalid database config.');

$db = new JFrame\DB($dbConfig['default']);
$email = $this->opt('email', $this->getResponse('Email Address'));
$user = $db->loadObject("SELECT * FROM users WHERE email='$email'");
if(!$user) $this->setError('User not found');
$passwd = $this->opt('password', $this->getResponse('New Password'));
$cPasswd = $this->getResponse('Re-Type Password');
if($passwd != $cPasswd) $this->setError('Password does not match');
$passwd = md5($hash.$passwd);
$result = $db->query("UPDATE users SET passwd = '$passwd' WHERE user_id=$user->user_id");
if(!$result) $this->setError('Failed to change password');
$this->write("$user->first_name $user->last_name<$user->email> password has been changed");
?>