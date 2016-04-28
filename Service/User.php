<?php 

namespace User\Service{
	use \JFrame\App;
	use \JFrame\Loader;
	
	class User extends \JFrame\Service{
		private $config;
		
		function __construct(){
			parent::__construct();
			$this->app = App::instance();
			$this->config = App::getConfig('user', 'object');
		}
		
		function authorize($user_groups=false, $return=false){
			if(!$user = $this->app->session->get('user')){
				if(!$return) return false;
				$this->app->redirect($return);
			}
			$groups = $this->db->loadObjectList("SELECT group_id,title FROM groups WHERE type_id = (SELECT id FROM group_types WHERE group_type='user') AND disabled=0");
				
			if($this->inGroup('webmaster')) return $user;
				
			if(is_array($user_groups)){
				foreach($user_groups as $group_id){
					foreach($user['groups'] as $g){
						if($g->group_id == $group_id) return $user;;
					}
				}
			}elseif(is_string($user_groups) || is_numeric($user_groups)){
				foreach($user['groups'] as $g){
					if($g->group_id == $user_groups) return $user;
					if($g->group == $user_groups) return $user;
				}
			}
			if(!$return) return false;
			$this->app->redirect($return);
		}
		
		public function inGroup($groups, $user=false){
			if($user){
				$user = (array) $user->properties();
			}else{
				if(!$user = (array) $this->app->session->get('user')) return false;
			}
				
			if(is_array($groups)){
				foreach($user['groups'] as $group){
					foreach($groups as $_group){
						if($group->group_id == $_group) return true;
						if($group->group == $_group) return true;
					}
				}
			}else{
				foreach($user['groups'] as $group){
					if($group->group_id == $groups) return true;
					if($group->group == $groups) return true;
				}
			}
			return false;
		}
		
		function login($email, $password){
			if(!$email) return $this->response->setError('Please enter your username.');
			if(!$password) return $this->response->setError('Please enter your password.');
			$user = $this->getUserByEmail($email);
			$time = date('Y-m-d H:i:s');
			if(!$user) return $this->response->setError('Invalid login attempt');
			if($mla = $this->config->max_login_attempts){
				if($user->login_attempts > $mla) return $this->response->setError('You have made too many login attempts.');
			}
			$passwd = md5($this->config->hash.$password);
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
					UPDATE users SET last_login = :last_login, login_attempts = 0,  last_activity = :last_activity  WHERE user_id=:user_id
				", array(
					'user_id' => $user->user_id,
					'last_login' => $time,
					'last_activity' => $time
				));
			}
			$user = Loader::get('User\Model\User', (array) $user)->properties();
			$this->app->session->restart();
			$this->app->session->set('user', $user);
			$this->response->setSuccess('Welcome back, ' . $user['first_name']);
			$this->app->dispatchEvent('User.Login', $user, $this->response);
			return $this->response;
		}
		
		function getUserByEmail($email, $as_model=false){
			$user = $this->db->loadObject("
				SELECT u.* FROM users u WHERE u.email = :email
			", array( 'email' => $email));
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
				WHERE m.gt_id = (SELECT id FROM group_types WHERE group_type='user')
					AND m.src_id = :user_id
			", array('user_id'=>$user_id));
		}
	}
}

?>