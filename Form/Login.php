<?php

namespace User\Form{
	use \JFrame\Vars;
	use \JFrame\Loader;
	use \Config;
	use \App;
	
	class Login extends \JFrame\Form{
		protected $class = 'login';
		protected $submit = 'Login';
		protected $return = 'user';
		
		function __construct(){
			parent::__construct();
			$user_js = file_get_contents(PATH_MOD. '/User/assets/js/user.js');
			$this->addJS($user_js, TRUE);
			$this->addFields(array(
				array(
					'type' => 'text',
					'name' => 'email',
					'label' => 'Username',
					'placeholder' => 'myemail@address.com',
				),
				
				array(
					'type' => 'password',
					'name' => 'passwd',
					'label' => 'Password',
					'placeholder' => 'Password',
					'append_label' => '<a href="'.SITE_URL.'/user/forgot-password">Forgot Password?</a>',
				),
			));
			
			$this->addControl(array(
				'type' => 'submit',
				'label' => 'Login'
			));
		}
		
		function action(){
			$username = Vars::get('email');
			$passwd = Vars::get('passwd');
			$svc = Loader::get('User\Service\User');
			$this->response = $svc->login($username, $passwd);
		}
	}
}
?>