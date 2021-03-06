<?php

namespace User\Model{
	
	class User extends \JFrame\Model{
		protected $user_id;
		protected $first_name;
		protected $last_name;
		protected $created;
		protected $email;
		protected $groups;
		protected $passwd;
		
		function __construct($properties=false){
			parent::__construct($properties);
			if(!is_array($this->groups)) $this->groups = array();
		}
	}
}
?>