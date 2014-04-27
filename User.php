<?php

namespace User{
	use \JFrame\RouteMap;
	
	class Module{
		
		function getConfig(){
			return array(
				'template_engine' => 'twig',
				'default_template' => 'template',
			);
		}
		
		function getRoutes(){
			return array(
				new RouteMap('user','User','User'),
				new RouteMap('user/login','User','User','login'),
				new RouteMap('user/forgot-username', 'User', 'User', 'forgotUsername', 'forgot-username'),
				new RouteMap('user/forgot-password', 'User', 'User', 'forgotPassword', 'forgot-password'),
				new RouteMap('user/recover-password/:hash', 'User', 'User', 'recoverPassword', 'recover-password', '.*'),
			);
		}
		
		function getEvents(){
			return array(
				'user.beforeRender',
				'user.login.beforeRender',
				'user.login.afterRender',

			);
		}
		
	}
}
?>