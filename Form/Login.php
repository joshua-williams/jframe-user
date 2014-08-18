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
<<<<<<< HEAD
			$user_js = file_get_contents(PATH_MOD. '/User/assets/js/user.js');
			$this->addJS($user_js, TRUE);
=======
			$this->addFields(array(
				array(
					'type' => 'text',
					'name' => 'email',
					'label' => 'Username',
					'placeholder' => 'myemail@address.com'
				),
				
				array(
					'type' => 'password',
					'name' => 'passwd',
					'label' => 'Password',
					'placeholder' => 'Password'
				),
			));
			
			$this->addControl(array(
				'type' => 'submit',
				'label' => 'Login'
			));
>>>>>>> bb636056c0f4216c5874ce4a4e3face605fe42c7
		}
		
		function action(){
			$username = Vars::get('username');
			$passwd = Vars::get('passwd');
			$svc = Loader::get('User\Service\User');
			$response = $svc->login($username, $passwd);
			die('<xmp>'.print_r($response,1));
		}
	}
}
?>