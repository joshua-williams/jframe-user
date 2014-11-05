<?php

namespace User\Controller{
	use \App;
	use \Config;
	use \JFrame\Loader;
	use \JFrame\Session;
	use \JFrame\Router;
	use \JFrame\Response;
	
	class User extends \JFrame\Controller{
		
		function construct(){
			$session = Session::getInstance()->start();
		}
		
		function beforeRender(){
			$response = $this->getResponse();
			$this->assign('response', $response);
			$event = 'user.'.strtolower(Router::$route->view) . '.beforeRender';
			$this->dispatchEvent('user.beforeRender');
			$this->dispatchEvent($event);
			
		}
		
		function afterRender(){
			$event = 'user.'.strtolower(Router::$route->view) . '.afterRender';
			$this->dispatchEvent($event);
		}
		
		function index(){
			
		}
		
		function login(){
			$form = Loader::get('User\Form\Login');
			App::dispatchEvent('User.Event.onLoadLoginForm', $form, $this);
			$this->addForm($form, 'form');
		}
		
		function forgotUsername(){
			die('too bad');
		}
		
		function forgotPassword(){
			$form = Loader::get('User\Form\ForgotPassword');
			App::dispatchEvent('User.Event.onLoadResetPasswordForm', $form, $this);
			$this->assign('form', $form->render());
		}
		
		function recoverPassword(){
			$hash = $this->route->vars('hash');
			$svc = Loader::getService('User', false, 'User');
			if(!$svc->validateHash($hash)){
				App::$session->set('response', new Response(array(
					'return' => SITE_URL . '/user/reset-password',
					'error' => 'The page you requested has expired'
				)));
				header('Location: '.SITE_URL . '/user/forgot-password');
				exit;
			}
			$form = Loader::getForm('RecoverPassword', 'User');
			$this->assign('form', $form->render());
		}
		
		function logout(){
			
		}
		
		function authenticate(){
			
		}
	}
}
?>