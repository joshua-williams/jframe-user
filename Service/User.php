<?php 

namespace User\Service{
	use \JFrame\App;
	use \JFrame\Loader;
	use \User\Model\User as UserModel;
	
	class User extends \JFrame\Service{
		private $config;
		
		function __construct(){
			parent::__construct();
			$this->app = App::instance();
			$this->config = App::getConfig('user', 'object');
		}
		
		function getUsers(){
			return $this->db->loadObjectList("
				SELECT user_id, first_name, last_name, email, created FROM users		
			");
		}
		
		function getUser($identifier){
			$user = $this->db->loadObject("
				SELECT 
					u.user_id, u.first_name, u.last_name, u.email,
					GROUP_CONCAT(g.title) AS groups,
					GROUP_CONCAT(m.rel_id) AS group_ids, u.created
				FROM users u
					INNER JOIN table_map m ON m.src_id = u.user_id
				    INNER JOIN groups g ON g.group_id = m.rel_id
				WHERE m.gt_id = (SELECT id FROM group_types WHERE group_type='user')
					AND (u.user_id = :identifier OR u.email=:identifier)
				GROUP BY u.user_id
			", array('identifier'=>$identifier));
			return $user;
		}
		
		public function authorize($user_groups=false, $return=false){
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
		
		public function getGroups(){
			return $this->db->loadObjectList("
				SELECT g.group_id AS id, title, created FROM groups g
				INNER JOIN group_types t ON t.id = g.type_id
				WHERE t.group_type = 'user'
			");
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
		
		function save(UserModel $user){
			$user = $user->properties(1);
			if(!$user->email) return $this->response->setError('Email address is required.');
			$exists = $this->db->loadResult("SELECT user_id FROM users WHERE email=:email", array('email'=>$user->email));
			if($exists) return $this->response->setError('User already exists with that email address');
				
			if(strlen($user->passwd) < 7) return $this->response->setError('Password must be at least 7 characters');
			$id = $this->db->query("
				INSERT INTO users (first_name, last_name, email, passwd) VALUES (:first_name, :last_name, :email, :passwd)
			", array(
				'first_name' => $user->first_name,
				'last_name' => $user->last_name,
				'email' => $user->email,
				'passwd' => md5($this->config->hash.$user->passwd)
			));
				
			if(!$id) $this->response->setError('Failed to create user');
			$gtid = $this->db->loadResult("SELECT id FROM group_types WHERE group_type = 'user'");
			$this->db->query("
				INSERT INTO table_map (gt_id, src_id, rel_id) VALUES (:gtid, :user_id, :group_id)
			", array(
				'gtid' => $gtid,
				'user_id' => $id,
				'group_id' => $user->groups[0]
			));
			return $this->response->setSuccess("User has been created");
		}
		
		function update(UserModel $user){
			$user = $user->properties(1);
			$exists = $this->db->loadResult("SELECT user_id FROM users WHERE user_id !=:id email=:email", array('email'=>$user->email, 'id'=>$user->user_id));
			if($exists) return $this->response->setError('User not found');
			if(!$user->email) return $this->response->setError('Email address is required.');
				
			$this->db->query("
				UPDATE users SET first_name=:first_name, last_name=:last_name, email=:email
				WHERE user_id = :user_id
			", array(
							'first_name' => $user->first_name,
							'last_name' => $user->last_name,
							'email' => $user->email,
							'user_id' => $user->user_id
					));
				
			// UPDATE USER GROUPS
			$this->db->query("
				DELETE FROM table_map
				WHERE gt_id = (SELECT id FROM group_types WHERE group_type='user')
					AND src_id = :src_id
					AND rel_id = :rel_id
			", array('src_id'=>$user->user_id, 'rel_id'=>$user->groups[0]));
				
			foreach($user->groups as $group){
				$this->db->query("
					INSERT INTO table_map
						(gt_id, src_id, rel_id)
					VALUES
						((SELECT id FROM group_types WHERE group_type='user'), :src_id, :rel_id)
				", array('src_id'=>$user->user_id, 'rel_id'=>$group));
			}
			if($this->db->rowsAffected()) return $this->response->setError('User has been updated');
			return $this->response->setError('No changes made to user');
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