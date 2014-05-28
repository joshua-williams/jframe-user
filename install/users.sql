CREATE TABLE users(
	user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
	first_name VARCHAR(45),
	last_name VARCHAR(45),
	email VARCHAR(255),
	passwd VARCHAR(45),
	`status` enum('active','suspended','pending'),
	last_login DATETIME,
	last_activity DATETIME,
	login_attempts INT(2),
	`hash` VARCHAR(255)
);

INSERT INTO GROUPS (type_id, title) VALUES (1, 'webmaster'), (1, 'super-admin'), (1, 'admin')
