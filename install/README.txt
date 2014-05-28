Installation

This module requires the core JFrame database tables found in vendor/jframe/install.

1. Create the users table with the users.sql script in this directory.

2. Add the following to the config/config.php
	const facebook_login = true;
	const facebook_app_id = {{your_facebook_app_id}};