<?php

namespace User\Form{
	use \JFrame\Vars;
	use \JFrame\Loader;
	
	class RecoverPassword extends \JFrame\Form{
		protected $class = 'recover-password';
		protected $submit = 'Reset Password';
		protected $return = 'user/login';
		
		function action(){
			$svc = Loader::getService('User', false, 'User');
			$result = $svc->resetPassword(array(
				'hash' => Vars::get('hash'),
				'passwd' => Vars::get('passwd'),
				'c_passwd' => Vars::get('c_passwd')
			));
			if($result){
				$this->response->setMessage('Your password has been updated.');
			}else{
				$this->response->setError($svc->message);
			}
		}
	}
}
?>