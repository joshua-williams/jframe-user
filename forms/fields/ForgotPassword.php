<?php

return array(
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
);

?>