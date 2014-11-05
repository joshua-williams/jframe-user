<?php

require_once(PATH_JFRAME.'/lib/DB.php');
require_once(PATH_CONFIG.'/config.php');

$dbConfig = (object) Config::get('databases')->application;
$db = new JFrame\DB($dbConfig);
$email = $this->vars('email', $this->getResponse('Email Address'));
$user = $db->loadObject("SELECT * FROM users WHERE email='$email'");
if(!$user) $this->setError('User not found');
$passwd = $this->vars('password', $this->getResponse('New Password'));
$cPasswd = $this->getResponse('Re-Type Password');
if($passwd != $cPasswd) $this->setError('Password does not match');
$passwd = md5(Config::hash.$passwd);
$result = $db->query("UPDATE users SET passwd = '$passwd' WHERE user_id=$user->user_id");
if(!$result) $this->setError('Failed to change password');
$this->write("$user->first_name $user->last_name<$user->email> password has been changed");
?>