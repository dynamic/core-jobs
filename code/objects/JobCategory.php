<?php

class JobCategory extends DataObject {

	private static $belongs_many_many = array(
		'Jobs' => 'Job'
    );

	public function getRelatedPages(){

		$controller = Controller::curr();
		$class = $controller->Data()->ClassName;

		if($class == 'DetailPage' || is_subclass_of($class, 'DetailPage')) {
			$parentID = $controller->Data()->Parent()->ID;
		} else {
			$parentID = $controller->Data()->ID;
		}

		$pages = Job::get()
			->filter(array('Categories.ID'=>$this->ID,'ParentID'=>$parentID));

		return $pages->Count();

	}

}