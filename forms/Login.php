<?php

namespace User\Form{
	use \JFrame\Vars;
	
	class Login extends \JFrame\Form{
		protected $class = 'login';
		protected $submit = 'Login';
		protected $return = 'user';
		
		function action(){
			$username = Vars::get('username');
			$passwd = Vars::get('passwd');
			if(!$username) return $this->response->setError('Username required');
		}
	}
}
?>