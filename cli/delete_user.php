<?php

require_once(PATH_JFRAME.'/lib/DB.php');
require_once(PATH_CONFIG.'/config.php');

$dbConfig = (object) Config::get('databases')->application;
$db = new JFrame\DB($dbConfig);

$email = $this->getResponse("Email Address");
$user = $db->loadObject("SELECT * FROM users WHERE email = '$email'");
if(!$user) $this->setError("User not found with email address $email");
$confirm = $this->getResponse("Are you sure you want to delete user $user->first_name $user->last_name <$user->email>? [y/n]");
if(strtolower($confirm) != 'y') exit;
$db->query("DELETE FROM users WHERE user_id = $user->user_id");
$db->query("DELETE FROM table_map WHERE gt_id=1 AND src_id=$user->user_id");
$this->write("$user->first_name $user->last_name <$user->email> has been deleted");
?>