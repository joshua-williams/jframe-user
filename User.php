<?php

namespace User{
	use \JFrame\RouteMap;
	
	DEFINE('USER_ERROR_NOT_LOGGED_IN', 'The user is not logged in');
	DEFINE('USER_ERROR_NOT_IN_GROUP', 'The user does not belong to the group(s)');
	DEFINE('USER_ERROR_EXISTS', 'A user with this email already exists');
	DEFINE('USER_GTID', 1);
	
	IF(!DEFINED('WEBMASTER_UGID')) DEFINE('WEBMASTER_UGID', 1);
	IF(!DEFINED('SUPER_ADMIN_UGID')) DEFINE('SUPER_ADMIN_UGID', 2);
	IF(!DEFINED('ADMIN_UGID')) DEFINE('ADMIN_UGID', 3);

	
	class Module extends \JFrame\Module{
		protected $events = array('Login', 'onLoadLoginForm', 'onLoadResetPasswordForm');
		
		public  function getRoutes(){
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