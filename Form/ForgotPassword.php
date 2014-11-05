<?php

namespace User\Form{
	use \JFrame\Vars;
	use \JFrame\Loader;
	use \JFrame\FormResponse;
	
	class ForgotPassword extends \JFrame\Form{
		protected $class = 'forgot-password';
		protected $submit = 'Reset Password';
		protected $return = 'user/login';
		
		function __construct(){
			parent::__construct();
			
			$this->addFields(array(
				array(
					'id' => 'email',
					'type' => 'email',
					'name' => 'email',
					'placeholder' => 'Email Address',
					'min_length' => 5,
				),
				
				'submit' => array(
					'id' => 'btn-reset-login',
					'type' => 'input',
					'value' => 'Reset Password',
					'append' => "
						<a href='".SITE_URL."/user/login'>Return to login</a>
					"
				),
			));
			
			$this->addControl(array(
				'type' => 'submit',
				'label' => 'Reset'
			));
		}
		function action(){
			$svc = Loader::getService('User',false,'User');
			$result = $svc->emailPasswordReset(Vars::get('email'));
			if($result){
				$this->response->set(array(
					'return' => SITE_URL . '/user/login',
					'message'=> 'An email has been sent with instruction to change your password'
				));
			}else{
				$this->response->set(array(
					'error' => $svc->message,
					'return' => SITE_URL . '/user/forgot-password'
				));
			}
		}
	}
}
?>