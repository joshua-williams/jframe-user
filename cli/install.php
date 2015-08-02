<?php
require_once(PATH_JFRAME . '/lib/App.php');
require_once(PATH_JFRAME . '/lib/Vars.php');
require_once(PATH_JFRAME . '/lib/Util.php');
require_once(PATH_JFRAME . '/lib/DB.php');

$db = \JFrame\DB::getInstance();
if(!$db->has_connection) $this->setError('User module failed to install. Database connection required');

$this->write('JFrame User Installation...');
// check to see if the user module has already been installed
if($db->table_exists('users')){
	$this->setError('User module failed to install. User table already exists');
}
if(file_exists('config/user.php')){
	$this->setError('User module failed to install.  User config already exists');
}
$config['hash'] = \JFrame\Util::generateKey();
$config['max_login_attempts'] = $this->getResponse("Maximum login attempts");
$config['max_login_attempts_penalty'] = $this->getResponse('Maximun login attempt penalty');
$config['password_recover_timeout'] = $this->getResponse('Password recovery timeout');
$first_name = $this->getResponse('Developer first name');
$last_name = $this->getResponse('Developer last name');
$email = $this->getResponse('Developer email address');
$passwd = $this->getResponse('Developer password');


$content = "<?php
	return array(
		'max_login_attempts' => '{{max_login_attempts}}',
		'max_login_attempts_penalty' => '{{max_login_attempts_penalty}}',
		'password_recover_timeout' => '{{password_recover_timeout}}',
		'hash' => '{{hash}}',
	);	
?>";
if(!file_exists('config')) mkdir('config');
foreach($config as $key=>$val){
	$content = str_replace('{{'.$key.'}}', $val, $content);
}
$file = fopen('config/user.php','w');
fwrite($file, $content);
fclose($file);


///////// CREATE USER TABLE ///////////////////////
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

$passwd = md5($config['hash'].$passwd);
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

$this->write('User module has been installed');
?>