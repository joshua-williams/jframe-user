<?php

namespace User\Form{
	use \JFrame\Vars;
	use \JFrame\Loader;
	use \Config;
	use \App;
	
	class Login extends \JFrame\Form{
		
		function __construct(){
			$this->attr('name', 'user-login');
			$this->prop('type', 'div');
		}
		
		protected function fields(){
			return array(
				
				array(
					'type' => 'text',
					'name' => 'email',
					'label' => 'Username',
					'class' => 'form-control',
					'placeholder' => 'Email Address',
				),
				
				array(
					'type' => 'password',
					'name' => 'passwd',
					'label' => 'Password',
					'class' => 'form-control',
					'placeholder' => 'Password',
				),
				array(
					'type' => 'submit',
					'value' => 'Login',
					'label' => '&nbsp;',
					'class' => 'btn btn-primary'
				),
			);
		}
		
		function action(){
			$username = Vars::get('email');
			$passwd = Vars::get('passwd');
			$svc = Loader::get('User\Service\User');
			$response = $svc->login($username, $passwd);
			$this->response = $response;
			if($return = Vars::get('return')){
				$this->response->setReturn($return);
			}
			return $this->response;
		}
	}
}
?>