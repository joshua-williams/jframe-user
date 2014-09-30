#!/usr/bin/php5
<?php
$path = dirname(dirname(dirname(dirname(__FILE__))));
DEFINE('PATH', $path);
DEFINE('PATH_CONFIG', "$path/config");
DEFINE('PATH_JFRAME', PATH.'/vendor/jframe');
require_once(PATH_JFRAME.'/lib/DB.php');
require_once(PATH_CONFIG.'/config.php');

write('JFrame User Installation...');
$config['max_login_attempts'] = getResponse("Maximum login attempts");
$config['max_login_attempts_penalty'] = getResponse('Maximun login attempt penalty');
$config['password_recover_timeout'] = getResponse('Password recovery timeout');
$email = getResponse('Super-Admin email address');
$passwd = getResponse('Super-Admin password');
$c = (object) $config;
$content = "return array(
	'max_login_attempts' => '$c->max_login_attempts',
	'max_login_attempts_penalty' => '$c->max_login_attempts_penalty',
	'password_recover_timeout' => '$c->password_recover_timeout',
	'facebook_login' => false,
	'facebook_app_id' => false,
);";

//$passwd = md5(Config::hash.$passwd);

///////////// WRITE USER CONFIG FILE /////////////
$file = fopen(PATH.'/config/user.config.php','w');
fwrite($file, $content);
fclose($file);



///////// CREATE USER TABLE ///////////////////////
$dbConfig = (object) Config::get('databases')->application;
$db = new JFrame\DB($dbConfig);
if(!$db->has_connection) setError('Unable to connection to database. Please check your database config.');
$queries = explode(';', file_get_contents(PATH.'/modules/User/install/users.sql'));
foreach($queries as $query){
	$db->query($query);
}


/////////// ADD CONSTANTS TO USER\MODULE ///////////////////
$str = file_get_contents(PATH.'/modules/User/User.php');
$pattern = '/[\/]+DEFINE CONSTANTS(.*?)[\/]+END DEFINE CONSTANTS/s';
preg_match($pattern, $str, $matches);
$constant = (object) array(
	'WEBMASTER_UGID' => $db->loadResult("SELECT group_id FROM groups WHERE type_id=1 AND title = 'webmaster'"),
	'SUPER_ADMIN_UGID' => $db->loadResult("SELECT group_id FROM groups WHERE type_id=1 AND title = 'super-admin'"),
	'ADMIN_UGID' => $db->loadResult("SELECT group_id FROM groups WHERE type_id=1 AND title = 'admin'")
);

$content = "
	IF(!DEFINED('WEBMASTER_UGID')) DEFINE('WEBMASTER_UGID', $constant->WEBMASTER_UGID);
	IF(!DEFINED('SUPER_ADMIN_UGID')) DEFINE('SUPER_ADMIN_UGID', $constant->SUPER_ADMIN_UGID);
	IF(!DEFINED('ADMIN_UGID')) DEFINE('ADMIN_UGID', $constant->ADMIN_UGID);
";
$content = str_replace($matches[0], $content, $str);
$file = fopen(PATH.'/modules/User/User.php','w');
fwrite($file, $content);
fclose($file);





function getResponse($prompt){
	fwrite(STDOUT, $prompt.': ');
	return trim(fgets(STDIN));
}
function vars($var,$default=null){
	if(!isset($_SERVER['argv'])) return false;
	$args = $_SERVER['argv'];
	foreach($args as $key=>$val){
		$_var = preg_replace('/^[\-]+/', '', $val);
		if($var != $_var) continue;
		if(!isset($args[($key+1)])) return $default;
		return $args[($key+1)];
	}
	return $default;
}

function write($msg){
	fwrite(STDOUT, $msg.PHP_EOL);
}

function setError($msg=''){
	fwrite(STDOUT, $msg.PHP_EOL);
	exit;
}	
	

?>
