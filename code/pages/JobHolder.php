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
			->filter(array('PostDate:GreaterThanOrEqual' => date('Y-m-d')))
			->sort('PostDate DESC');
	}

}

class JobHolder_Controller extends HolderPage_Controller{

	private static $allowed_actions = array(
		'application');

	public function init() {
		parent::init();

		Requirements::css('jobs/css/job.css');

	}

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