<?php 

namespace User\Form{
	use \JFrame\Vars;
	
	class GroupForm extends \JFrame\Form{
		
		function __construct(){
			$this->attr('name', 'user');
			$this->attr('class', 'form');
			$this->addFields(array(
				array('name'=>'title', 'type'=>'text', 'placeholder'=>'Group Name', 'class'=>'form-control'),
				array('type'=>'submit', 'value'=>'New User', 'class'=>'btn btn-primary'),
			));
		}
		
		function action(){
			$svc = new \User\Service\User();
			return $svc->addGroup(Vars::get('title'));
		}
	}
}

?>