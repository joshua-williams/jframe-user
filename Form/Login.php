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
		}
		
		function action(){
			$username = Vars::get('username');
			$passwd = Vars::get('passwd');
			if(!$username) return $this->response->setError('Username required');
		}
	}
}
?>