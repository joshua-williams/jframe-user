<?php

namespace User\Service{
	use \App;
	use \Config;
	use \Mandrill;
	use \JFrame\Session;
	use \JFrame\Loader;
	
	class User extends \JFrame\Service{
		public $response;
		public $has_error;
		
		function __construct(){
			parent::__construct();
			$this->response = Loader::get('JFrame\Response');
		}
		
		function authorize($user_groups, $return=false){
			$sess = Session::getInstance();
			if(!$user = $sess->get('user')){
				if(!$return) return false;
				App::redirect($return);
			}
			
			if(is_array($user_groups)){
				foreach($user_groups as $group_id){
					foreach($user['groups'] as $g){
						if(in_array($g->group_id, array(WEBMASTER_UGID, SUPER_ADMIN_UGID, ADMIN_UGID))) return $user;
						if($g->group_id == $group_id) return $user;;
					}
				}
			}elseif(is_string($user_groups)){
				foreach($user['groups'] as $g){
					if(in_array($user_groups, array(WEBMASTER_UGID, SUPER_ADMIN_UGID, ADMIN_UGID))) return $user;
					if($g->group_id == $user_groups) return $user;
				}
			}
			if(!$return) return false;
			App::redirect($return);
		}
		
		public function inGroup($groups, $user=false){
			if($user){
				$user = (array) $user->properties();
			}else{
				$sess = Session::getInstance();
				if(!$user = (array) $sess->get('user')) return false;
			}
			
			if(is_array($groups)){
				foreach($user['groups'] as $group){
					foreach($groups as $_group){
						if($group->group_id == $_group) return true;
					}
				}
			}else{
				foreach($user['groups'] as $group){
					if($group->group_id == $groups) return true;
				}
			}
			return false;
		}
		function getUser($user_id, $as_model=false){
			$user = $this->db->loadObject("
				SELECT u.*
				FROM users u
				WHERE u.user_id = :user_id
			", array(
				'user_id' => $user_id,
			));
			if(!$user) return false;
			$user->groups = $this->getUserGroups($user_id);
			if(!$as_model) return $user;
			return Loader::get('User\Model\User', (array) $user);
		}
		
		function getUserByEmail($email, $as_model=false){
			$user = $this->db->loadObject("
				SELECT u.*
				FROM users u
				WHERE u.email = :email
			", array( 
				'email' => $email,
			));
			if(!$user) return false;
			$user->groups = $this->getUserGroups($user->user_id);
			if(!$as_model) return $user;
			return Loader::get('User\Model\User', (array) $user);
		}

		function getUserGroups($user_id){
			return $this->db->loadObjectList("
				SELECT g.group_id, g.title AS `group`
				FROM table_map m
					INNER JOIN groups g ON g.group_id = m.rel_id
				WHERE m.gt_id = :USER_GTID
					AND m.src_id = :user_id
			", array('user_id'=>$user_id, 'USER_GTID'=>USER_GTID)); 
		}
		
		function addUserToGroup($user_id, $group_id){
			if(!$user = $this->getUser($user_id)) return false;
			if(is_numeric($group_id) || is_string($group_id)){
				if(!in_array($group_id, $user->groups)){
					$this->db->query("
						INSERT INTO table_map (gt_id, src_id, rel_id) VALUES (:gt_id, :src_id, :rel_id)
					", array( 'gt_id' => USER_GTID, 'src_id' => $user->user_id, 'rel_id' => $group_id ));
				}
			}elseif(is_array($group)){
				foreach($group_id as $gid){
					if(!in_array($gid, $user->groups)){
						$this->db->query("
							INSERT INTO table_map (gt_id, src_id, rel_id) VALUES (:gt_id, :src_id, :rel_id)
						", array( 'gt_id' => USER_GTID, 'src_id' => $user->user_id, 'rel_id' => $group_id));
					}
				}
			}
			return true;
		}
		function login($email, $passwd){
			if(!$email) return $this->response->setError('Please enter your username.');
			if(!$passwd) return $this->response->setError('Please enter your password.');
			$user = $this->getUserByEmail($email);
			$time = date('Y-m-d H:i:s');
			if(!$user) return $this->response->setError('Invalid login attempt');
			$config = Config::get('user.config');
			if($user->login_attempts >= $config->max_login_attempts){
				return $this->response->setError('You have made too many login attempts.');
			}
			$passwd = md5(Config::hash.$passwd);
			if($user->passwd != $passwd){
				$this->db->query("
					UPDATE users SET login_attempts=:login_attempts, last_activity=:last_activity WHERE user_id=:user_id
				", array(
					'login_attempts' => $user->login_attempts + 1,
					'last_activity' => $time,
					'user_id' => $user->user_id
				));
				return $this->response->setError('Invalid login attempt');
			}else{
				$this->db->query("
					UPDATE users SET 
						last_login = :last_login,
						login_attempts = 0, 
						last_activity = :last_activity 
					WHERE user_id=:user_id
				", array(
					'user_id' => $user->user_id,
					'last_login' => $time,
					'last_activity' => $time
				));
			}
			
			$sess = Session::getInstance();
			$sess->restart();
			$user = Loader::get('User\Model\User', (array) $user);
			$sess->set('user',$user->properties());
			App::dispatchEvent('User.Event.Login', $user, $this->response);
			return $this->response;
		}
		
		function logout(){
			$sess = Session::getInstance();
			$sess->destroy();
		}
		
		function register(UserModel $user, $passwd, $c_passwd){
			$user = (object) $user->properties();
			if(!$user->email) return $this->response->setError('Your email address is required.');
			if(!$this->isValidEmail($user->email)) return $this->response->setError('Please enter a valid email address');
			$emailExists = $this->db->loadResult("SELECT email FROM users WHERE email=:email",array('email'=>$user->email));
			if($emailExists) return $this->response->setError('A user with that email address already exists.');
			if(!$user->first_name) return $this->response->setError('Your first name is required.');
			if(!$user->last_name) return $this->response->setError('Your last name is required.');
			if(!$user->zip) return $this->response->setError('Your zip code is required.');
			
			if(!$passwd) return $this->response->setError('Please choose a password.');
			if(!$c_passwd) return $this->response->setError('Please confirm your password.');
			if($passwd != $c_passwd) $this->setError('Your password does not match.');
			$passwd = md5(Config::hash.$passwd);
			
			$user_id = $this->db->query("
				INSERT INTO users
				(first_name, last_name, email, zip, phone, passwd)
				VALUES
				(:first_name, :last_name, :email, :zip, :phone, :passwd)
			", array(
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'email' => $user->email,
				'zip' => $user->zip,
				'phone' => $user->phone,
				'passwd' => $passwd
			));
			if(!$user_id) return $this->response->setError("Sorry, we were unable to register your account.");
			
			$map_id = $this->db->query("
				INSERT INTO table_map 
					(gt_id, group_id, rel_id)
				VALUES
					(:USER_GT_ID, :SSB_UGID, :user_id)
			", array(
				'USER_GT_ID' => USER_GT_ID,
				'SSB_UGID' => SSB_UGID,
				'user_id' => $user_id
			));
			App::dispatchEvent('SummerScrapbook.Event.Register', (array)$user);
			return $this->response;
		}
		
		function emailPasswordReset($email){
			if(!$email) return $this->response->setError('Please enter your email address');
			$user = $this->db->loadObject("SELECT user_id, first_name, email FROM users WHERE email=:email",array('email'=>$email));
			if(!$user) return $this->response->setError('Unable to find a user with this email address.');
			$hash = Loader::get('JFrame\Security')->encrypt(Config::enc_key, time().'-'.$email);
			
			$this->db->query("UPDATE users SET hash = :hash WHERE user_id = :user_id", array(
				'hash' => $hash,
				'user_id' => $user->user_id
			));
			$html = file_get_contents(PATH.'/templates/Email/forgot_password/forgot_password.html');
			$text = file_get_contents(PATH.'/templates/Email/forgot_password/forgot_password.txt');
			
			$pattern = '/({{name}})|({{link}})|({{application}})/';
			$vars = array(
				'application' => Config::application,
				'name' => $user->first_name,
				'link' => PUBLIC_URL . '/user/reset-password/'.$hash	
			);
			foreach($vars as $key=>$val){
				$html = str_replace('{{'.$key.'}}', $val, $html);
				$text = str_replace('{{'.$key.'}}', $val, $text);
			}
			
			require_once(PATH.'/vendor/mandrill/Mandrill.php');
			$mandrill = new Mandrill('idcYcbKgBLGJXJZvfJhY9A');
			$message = array(
				'html' => $html,
				'text' => $text,
				'subject' => Config::application . ' Password Recovery',
				'from_email' => 'ask-npf@nationalparks.org',
				'from_name' => Config::application,
				'to' => array(
					array(
						'email' => $user->email,
						'name' => $user->first_name,
						'type' => 'to'),
					),
				'track_opens' => true,
				'tags' => array('ssb-recover-password'),
				'async' => false,
			);
			$result = $mandrill->messages->send($message);
			return $this->response->setSuccess('An email has been sent with instructions to recover your password.');
		}
		
		
		function resetPassword($hash, $email, $passwd, $c_passwd){
			if(!$hash || !$email || !$passwd || !$c_passwd) return false;
			if($passwd != $c_passwd) return $this->response->setError('Your password does not match.');
			$passwd = md5(Config::hash.$passwd);
			$user = $this->db->loadObject("SELECT * FROM users WHERE hash=:hash", array('hash'=>$hash));
			if(!$user) return false;
			if($user->email != $email) return false;
			$this->db->query("UPDATE users SET hash = NULL, passwd = :passwd WHERE user_id=:user_id", array(
				'user_id'=>$user->user_id,
				'passwd' => $passwd
			));
			return true;
		}
		
		function validateHash($hash){
			$user = $this->db->loadObject("SELECT * FROM users WHERE hash=:hash", array('hash'=>$hash));
			if(!$user) return false;
			$hash = Loader::get('JFrame\Security')->decrypt(Config::enc_key, $hash);
			
			preg_match('/([0-9]+)\-(.*)$/', $hash, $matches);
			
			if(!$matches) return false;
			
			if(count($matches) != 3) App::redirect(PUBLIC_URL.'/User/login');
			
			$time = $matches[1];
			$email = $matches[2];
			$minutes_lapsed = round((time() - $time) / 60);
				
			if($minutes_lapsed > Config::get('user.config')->password_recover_timeout){
				return false;
			}
			if($email != $user->email) return false;
			return true;
		}
		private function isValidEmail($email){
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
			if(!preg_match('/@.+\./', $email)) return false;
			return true;
		}
	}
}

?>