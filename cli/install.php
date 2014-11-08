<?php

require_once(PATH_JFRAME.'/lib/DB.php');
require_once(PATH_CONFIG.'/config.php');

$this->write('JFrame User Installation...');
$config['max_login_attempts'] = $this->getResponse("Maximum login attempts");
$config['max_login_attempts_penalty'] = $this->getResponse('Maximun login attempt penalty');
$config['password_recover_timeout'] = $this->getResponse('Password recovery timeout');
$first_name = $this->getResponse('Super-Admin first name');
$last_name = $this->getResponse('Super-Admin last name');
$email = $this->getResponse('Super-Admin email address');
$passwd = $this->getResponse('Super-Admin password');

$c = (object) $config;
$content = "<?php".chr(10)."return array(
	'max_login_attempts' => '$c->max_login_attempts',
	'max_login_attempts_penalty' => '$c->max_login_attempts_penalty',
	'password_recover_timeout' => '$c->password_recover_timeout',
	'facebook_login' => false,
	'facebook_app_id' => false,
);";

///////////// $this->write USER CONFIG FILE /////////////
$file = fopen(PATH.'/config/user.config.php','w');
fwrite($file, $content);
fclose($file);


///////// CREATE USER TABLE ///////////////////////
$dbConfig = (object) Config::get('databases')->application;
$db = new JFrame\DB($dbConfig);
if(!$db->has_connection) setError('Unable to connection to database. Please check your database config.');
$db->query("
	CREATE TABLE IF NOT EXISTS users(
		user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
		first_name VARCHAR(45),
		last_name VARCHAR(45),
		email VARCHAR(255),
		passwd VARCHAR(45),
		`status` enum('active','suspended','pending'),
		last_login DATETIME,
		last_activity DATETIME,
		created TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
		login_attempts INT(2),
		`hash` VARCHAR(255)
	);
");

$constant = (object) array(
	'WEBMASTER_UGID' => $db->query("INSERT INTO groups (type_id, title) VALUES (1, 'webmaster')"),
	'SUPER_ADMIN_UGID' => $db->query("INSERT INTO groups (type_id, title) VALUES (1, 'super-admin')"),
	'ADMIN_UGID' => $db->query("INSERT INTO groups (type_id, title) VALUES (1, 'admin')"),
);

$passwd = md5(Config::hash.$passwd);
$user_id = $db->query("
	INSERT INTO users 
		(first_name, last_name, email, passwd)
	VALUES
		('$first_name', '$last_name', '$email', '$passwd')
");
$db->query("
	INSERT INTO table_map (gt_id, src_id, rel_id)
	VALUES (1, $user_id, $constant->WEBMASTER_UGID)
");
/////////// ADD CONSTANTS TO USER\MODULE ///////////////////
$str = file_get_contents(PATH_MODULE.'/install/User.php');
$pattern = '/[\/]+DEFINE CONSTANTS(.*?)[\/]+END DEFINE CONSTANTS/s';
preg_match($pattern, $str, $matches);
$content = "
	IF(!DEFINED('WEBMASTER_UGID')) DEFINE('WEBMASTER_UGID', $constant->WEBMASTER_UGID);
	IF(!DEFINED('SUPER_ADMIN_UGID')) DEFINE('SUPER_ADMIN_UGID', $constant->SUPER_ADMIN_UGID);
	IF(!DEFINED('ADMIN_UGID')) DEFINE('ADMIN_UGID', $constant->ADMIN_UGID);
";
$content = str_replace($matches[0], $content, $str);
$file = fopen(PATH.'/modules/User/User.php','w');
fwrite($file, $content);
fclose($file);

	
/////////////// ENABLE USER MODULE ///////////////////////////
$modules = (array) Config::get('modules');
$modules = array_merge($modules, array('user'=>array('namespace'=>'User')));
$content = "<?php" . chr(10) . "return array(";
foreach($modules as $alias=>$mod){
	$namespace = $mod['namespace'];
	$content.="
	'$alias' => array(
		'namespace' => '$namespace'
	),
	";
}

$content.=chr(10).');';
$file = fopen(PATH.'/config/modules.php', 'w');
fwrite($file,$content);
fclose($file);
?>
