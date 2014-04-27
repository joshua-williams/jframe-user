<?php

return array(
	array(
		'id' => 'password',
		'type' => 'password',
		'name' => 'passwd',
		'placeholder' => 'New Password',
		'min_length' => 5,
	),
	array(
		'id' => 'c_password',
		'type' => 'password',
		'name' => 'c_passwd',
		'placeholder' => 'Re-Enter Password',
		'min_length' => 5,
	),
	array(
		'type' => 'hidden',
		'name' => 'hash',
		'value' => \JFrame\Vars::get('hash'),
	),
	'submit' => array(
		'id' => 'btn-reset-password',
		'type' => 'input',
		'value' => 'Reset Password',
		'append' => "
			<a href='".SITE_URL."/user/login'>Return to login</a>
		"
	),
);

?>