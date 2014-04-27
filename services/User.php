<?php

namespace User\Service{
	use \JFrame\Security;
	use \JFrame\Router;
	use \JFrame\Loader;
	use \Config;
	use \App;
	
	class User extends \JFrame\Service{
		
		function emailPasswordReset($email){
			if(!$email) return $this->setError('Email address required');
			$user = $this->db->loadObject("SELECT * FROM users WHERE email=:email", array('email'=>$email));
			if(!$user) return $this->setError('We are unable to match your email address with any users.');
			$hash = Security::encrypt(Config::enc_key, time().'-'.$email);
			$this->db->query("UPDATE users SET hash=:hash WHERE user_id=:user_id", array('hash'=>$hash,'user_id'=>$user->user_id));
			$link = Config::site_url . '/user/recover-password/' . $hash;
			
			$config = (object) App::getConfig('mail')->PHPMailer['default'];
			
			define('BR', chr(10));
			define('BR2',chr(10).chr(10));
			
			$body = "$user->first_name,".BR2;
			$body.="A request has been submitted to recover a lost password from ".Config::application.BR2;
			$body.="To complete the password change, please visit the following URL and enter the requested info:".BR2;
			$body.=$link.BR2;
			$body.="Passwords must be alphanumeric, at least 8 characters long.".BR2;
			$body.="If you did not specifically request this password change, please disregard this notice.".BR2;
			$body.="We are available 24/7. If you have any questions, comments, or concerns, please do not hesitate to contact us.".BR2;
			$body.="Thank you,".BR;
			$body.=Config::application;
				
			$mail = Loader::getModel('PHPMailer', array(
				'host' => $config->host,
				'port' => $config->port,
				'username' => $config->username,
				'password' => $config->password,
				'subject' => Config::application . ' Password Reset Request',
				'sender_name' => Config::application,
				'text' => $body,
			), 'Mail');
			$mail->addRecipient($email, 'Joshua Williams');
			return $mail->send() ? true : false;
		}
		
		function resetPassword(Array $params){
			$params = (object) $params;
			if(!$params->hash) return $this->setError('Invalid request.');
			if(!$params->passwd) return $this->setError('Please enter a new password.');
			if(!$params->c_passwd) return $this->setError('You password does not match.');
			if(!$user_id = $this->validateHash($params->hash)){
				return $this->setError('The page you requested has expired.');
			}
			if($params->passwd != $params->c_passwd){
				return $this->setError('Your password does not match.');
			}
			$new_password = md5(Config::enc_key . $params->passwd);
			$result = $this->db->query("
				UPDATE users SET passwd = :password
				WHERE user_id = :user_id
					AND hash = :hash
			", array(
				'password' => $new_password,
				'user_id' => $user_id,
				'hash' => $params->hash
			));
			if(!$result){
				return $this->setError("We were unable to update your password");
			}
			return true;
		}
		
		function validateHash($hash){
			$user_id = $this->db->loadResult("
				SELECT user_id FROM users
				WHERE `hash`=:hash
			", array('hash'=>$hash));
			if(!$user_id) return false;
			
			$request_time = Security::decrypt(Config::enc_key, $hash);
			$lapsed = (time() - $request_time) / 60;
			
			if($lapsed > Config::password_recover_timeout) return false;
			return $user_id;
 		}
				
	}
}

?>