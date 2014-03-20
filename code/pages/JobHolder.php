<?php

class JobHolder extends HolderPage{

	private static $singular_name = "Job Group";
	private static $plural_name = "Job Groups";
	private static $description = 'Page allowing for and showing job detail pages';

	public static $item_class = 'Job';

	private static $default_child = 'Job';
	private static $allowed_children = array(
		'Job');

	private static $db = array(
		'Message' => 'HTMLText',
		'FromAddress' => 'Varchar(255)',
		'EmailRecipient' => 'Varchar(255)',
		'EmailSubject' => 'Varchar(255)'
	);

	private static $has_one = array(
		'Application' => 'File');

	private static $has_many = array();
	private static $many_many = array();
	private static $many_many_extraFields = array();
	private static $belongs_many_many = array();


	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$app = new UploadField('Application', 'Application Form');
		$app->allowedExtensions = array('pdf','PDF');

		$fields->addFieldToTab('Root.ApplicationFile', $app);
		$fields->addFieldsToTab('Root.Confirmation', array(
			HTMLEditorField::create('Message', 'Application Confirmation Message')
		));
		$fields->addFieldsToTab('Root.SubmissionEmailSettings', array(
			EmailField::create('FromAddress','Submission From Address'),
			EmailField::create('EmailRecipient','Submission Recipient'),
			TextField::create('EmailSubject','Submission Email Subject Line')
		));

		$fields->extend('updateCMSFields', $fields);

		return $fields;
	}

	// custom gets
	public function getPostedJobs() {
		return Job::get()
			->where("\"PostDate\" <= '" . date('Y-m-d') . "'")
			->sort('PostDate DESC');
	}

	// tag list for sidebar
	/*public function getTags() {

		$hit = Tag::get()
			->filter(array(
				'Jobs.ID:GreaterThan'=>0,
				'Jobs.ClassName' => $this->stat('item_class'),
				'Jobs.ID.ParentID' => $this->ID))
			//->sort('RelatedPages', 'DESC')
			->limit(10);
		if($hit->Count()==0){
			$hit = false;
		}
		return $hit;
	}*/

	/*public function getJobTypeList() {
		$JobTypes = singleton('Job')->dbObject('PositionType')->enumValues();

		$doSet = new ArrayList();
		foreach(singleton('Job')->dbObject('PositionType')->enumValues() as $type) {
			$doSet->push(new ArrayData(array(
				'Type' => $type,
				'JobCount' => $this->getPostedJobs()
					->filter(array('PositionType' => $type))
					->Count()
			)));
		}

		return $doSet;
	}*/

}

class JobHolder_Controller extends HolderPage_Controller{

	private static $allowed_actions = array(
		'tag',
		'application');

	public function init() {
		parent::init();

		Requirements::css('jobs/css/job.css');

	}

	/*function index($request) {
		return $this->render(array(
			'Cat' => false
		));
	}

	public function Results() {
		return $this->getPostedJobs()
			->sort('StartDate DESC');
	}*/

	public function tag() {

		$request = $this->request;
		$params = $request->allParams();

		if ($tag = Convert::raw2sql($params['ID'])) {

			$filter = array('Categories.Title' => $tag);

			return $this->customise(array(
				'Message' => 'showing entries tagged "' . $tag . '"',
				'Items' => $this->Items($filter)
			));

		}

		return $this->Items();

	}

	/*// fiter by job type
	public function type() {

		// get ID from url params
		$type = 0;
		$Params = $this->getURLParams();
		if($Params['ID']) {
			$type = $Params['ID'];
			$type = Convert::raw2sql($type);
		}

		if ($type) {
			$Results = Job::get()
				->filter(array('PositionType' => $type))
				->where("\"PostDate\" <= '" . date('Y-m-d') . "'")
				->sort('PostDate DESC');

			return $this->render(array(
				'Results' => $Results,
				'Type' => $type
			));
		}
	}*/

	public function application(){

		//Determine if the application is valid
		if($params = $this->getURLParams()){
			if(is_numeric($params['ID']) && $ID = $params['ID']){
				$application = JobSubmission::get()
					->byID($ID);
				return $application->renderWith('JobSubmission');
			}
		}

	}

}