<?php 

namespace User\Form{
	use \JFrame\App;
	ini_set('display_errors', 1);
	
	class User extends \JFrame\Form{
		protected $svc;
		
		function __construct(){
			$this->prop('type', 'div');
			$this->svc = new \User\Service\User();
			foreach($this->svc->getGroups() as $group){
				if($group->title == 'webmaster') continue;
				if($group->title == 'super-admin') continue;
				$userGroups[] = array('label'=>ucfirst($group->title), 'value'=>$group->id);
			}
			
			$this->addFields(array(
				array('name'=>'user_id', 'type'=>'hidden'),
				array(
					'name' => 'first_name',
					'type' => 'text',
					'class' => 'form-control input-sm',
					'label' => 'First Name',
					'parent' => array('class'=>'col-sm-6')
				),
				array(
					'name' => 'last_name',
					'type' => 'text',
					'class' => 'form-control input-sm',
					'label' => 'Last Name',
					'parent' => array('class'=>'col-sm-6')
				),
				array(
					'name' => 'email',
					'type' => 'text',
					'class' => 'form-control input-sm',
					'label' => 'Email Address',
					'parent' => array('class'=>'col-sm-6')
				),
				array(
					'name' => 'passwd',
					'type' => 'password',
					'class' => 'form-control input-sm',
					'label' => 'Password',
					'parent' => array('class'=>'col-sm-6')
				),
				array(
					'name' => 'groups[]',
					'type' => 'dropdown',
					'class' => 'form-control input-sm',
					'label' => 'User Group',
					'parent' => array('class'=>'col-sm-6'),
					'options' => $userGroups
				),
				array(
					'type' => 'submit',
					'value' => 'Save',
					'class' => 'btn btn-primary pull-right',
					'parent' => array('class'=>'col-sm-12')
				),	
			));
		}
		
		function action(){
			$user = new \User\Model\User($_POST);
			$response = ($user->prop('user_id')) ? $this->svc->update($user) : $this->svc->save($user);
			$return = App::instance()->config('site_url') . '/user/' . $user->prop('user_id');
			$response->setReturn($return);
			if($response->getErrors())  $response->setReturn("$return/update");
			return $response;
		}
	}
}

?>