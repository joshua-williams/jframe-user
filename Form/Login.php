<?php

namespace User\Form{
	use \JFrame\Vars;
	use \Config;
	
	class Login extends \JFrame\Form{
		protected $class = 'login';
		protected $submit = 'Login';
		protected $return = 'user';
		
		function __construct(){
			parent::__construct();
			$user_js = file_get_contents(PATH_MOD. '/User/assets/js/user.js');
			$app_id = Config::get('user.config')->facebook_app_id;
			$user_js = str_replace('{{facebook_app_id}}', $app_id, $user_js);
			$this->addJS($user_js, TRUE);
		}
		
		function action(){
			$username = Vars::get('username');
			$passwd = Vars::get('passwd');
			if(!$username) return $this->response->setError('Username required');
		}
	}
}
?>