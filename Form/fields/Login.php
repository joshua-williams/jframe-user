<?php

return array(
	array(
		'id' => 'username',
		'type' => 'text',
		'name' => 'username',
		'label' => 'Username',
		'placeholder' => 'Username',
		'min_length' => 7,
		'append_label' => "
			<a href='" . SITE_URL . "/user/forgot-username'>I forgot?</a>
		", 
	),
	array(
		'type' => 'password',
		'name' => 'passwd',
		'label' => 'Password',
		'placeholder' => 'Password',
		'append_label' => "
			<a href='" . SITE_URL . "/user/forgot-password'>I forgot?</a>
		",
	),
	
	array(
		'type' => 'button',
		'name' => 'facebook',
		'label' => 'Signin with Facebook',
	),
	
	'controls' => array(
		'row_class' => 'buttons',
		
		array(
			'class' => 'fb-login-button',
			'type' => 'div',
			'label' => 'Login With Facebook',
			'data' => array(
				'max-rows' => 1,
				'size' => 'large',
				'show-faces' => false,
				'auto-logout-link' => false
			)
		),
		array(
			'id' => 'btn-login',
			'type' => 'submit',
			'label' => 'Log In'
		),
		array(
			'id' => 'fb-login',
			'class' => 'fb-login',
			'type' => 'button',
			'label' => 'Login with Facebook'
		),
	),
	
);

?>