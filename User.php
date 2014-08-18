<?php

namespace User{
	use \JFrame\RouteMap;
	
	DEFINE('USER_ERROR_NOT_LOGGED_IN', 'The user is not logged in');
	DEFINE('USER_ERROR_NOT_IN_GROUP', 'The user does not belong to the group(s)');
	DEFINE('USER_GT_ID', 1);
	DEFINE('WEBMASTER_UGID', 1);
	DEFINE('SUPER_ADMIN_UGID', 2);
	DEFINE('ADMIN_UGID', 3);
	
	class Module extends \JFrame\Module{
		protected $events = array('BeforeRender');
		
		public static function getConfig(){
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
	}
}
?>