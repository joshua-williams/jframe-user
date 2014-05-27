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
	'submit' => array(
		'id' => 'btn-login',
		'type' => 'input',
		'value' => 'Log In'
	),
);

?>