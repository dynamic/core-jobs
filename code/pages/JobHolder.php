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

	/*public function getCategoryList() {
		$Cats = JobCategory::get();

		$doSet = new ArrayList();
		foreach ($Cats as $cat) {
			$doSet->push(new ArrayData(array(
				'Category' => $cat->Name,
				'JobCount' => $cat->Jobs()
					->where("\"PostDate\" <= '" . date('Y-m-d') . "'")
					->Count()
			)));
		}
		return $doSet;
	}

	public function getJobTypeList() {
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

	public function init() {
		parent::init();

		Requirements::css('jobs/css/job.css');

	}

	function index($request) {
		return $this->render(array(
			'Cat' => false
		));
	}

	public function Results() {
		return $this->getPostedJobs()
			->sort('StartDate DESC');
	}

	/*// filter by job category
	public function category() {

		// get ID from url params
		$cat = 0;
		$Params = $this->getURLParams();
		if($Params['ID']) {
			$cat = $Params['ID'];
			$cat = Convert::raw2sql($cat);
		}

		if ($cat) {
			if ($Category = JobCategory::get()->filter('Name', $cat)->First()) {
				$Results = $Category->Jobs()
					//->filter(array('CloseDate:GreaterThan' => date('Y-m-d'), 'StartDate:LessThan' => date('Y-m-d')))
					->where("\"PostDate\" <= '" . date('Y-m-d') . "'")
					->sort('StartDate DESC');
				$CategoryName = $Category->Name;
			} else {
				$Results = false;
				$CategoryName = $cat;
				//debug::show($Category);
			}

			return $this->render(array(
				'Results' => $Results,
				'Cat' => $CategoryName
			));
		}

	}*/

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