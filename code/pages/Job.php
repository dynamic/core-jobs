<?php

class Job extends DetailPage{

	private static $singular_name = 'Job';
	private static $plural_name = 'Jobs';
	private static $description = 'Job detail page allowing for application submissions';

	public static $listing_page_class = 'JobHolder';
	private static $default_parent = 'JobHolder';
	private static $can_be_root = false;

	private static $db = array(
		'PositionType' => "Enum('Full-time, Part-time, Freelance, Internship')",
		'PostDate' => 'Date',
		'Experience' => 'Varchar(200)'
	);

	private static $has_many = array(
		'Submissions' => 'JobSubmission'
	);

	private static $many_many = array(
		'Categories' => 'JobCategory',
		//'Requirements' => 'JobRequirement',
		//'Skills' => 'JobSkill',
		//'Responsibilities' => 'JobResponsibility'
	);

	private static $many_many_extraFields = array(
		/*'Requirements' => array(
			'SortOrder' => 'Int'
		),
		'Skills' => array(
			'SortOrder' => 'Int'
		),
		'Responsibilities' => array(
			'SortOrder' => 'Int'
		)*/
	);

	private static $belongs_many_many = array();

	public function populateDefaults() {
	    $this->PostDate = date('Y-m-d');
	    parent::populateDefaults();
	}

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		// calendar fields
		$PostDate = DateField::create('PostDate', 'Position Post Date')
			->setConfig('showcalendar', true)
			->setConfig('dateformat', 'MMM dd, YYYY');

		// Tag Field
		$fields->removeByName('Tags');//remove generic tag field
		$TagField = TagField::create('Categories', null, null, 'Job');
		$TagField->setSeparator(', ');
		$fields->addFieldToTab('Root.Main', $TagField, 'Content');


		$fields->addFieldsToTab("Root.Job", array(
			DropdownField::create('PositionType', 'Position Type', singleton('Job')->dbObject('PositionType')->enumValues())
				->setEmptyString('--select--'),
			$PostDate
		));

		$fields->extend('updateCMSFields', $fields);

		$fields->removeByName('Address');

		return $fields;
	}

	// Dates
	public function getPosted() {
		if ($this->PostDate) return $this->obj('PostDate')->NiceUS();
		return false;
	}

	// Apply Button
	public function getApplyButton() {
		$apply = '<button type="submit" class="job-apply" onclick="parent.location=\'' . $this->Link() . 'apply\'">Apply for this position</button>';
		if($this->parent()->Application()->ID!=0){
			$download = $this->parent()->Application()->URL;
			$apply.=" or <a href=\"$download\" target=\"_blank\">Download the Application</a>";
		}
		$apply.="";
		return $apply;
	}

	// return Requirements in order
	public function getRequirementList() {
		return $this->Requirements()->sort('SortOrder');
	}

	// return Skills in order
	public function getSkillList() {
		return $this->Skills()->sort('SortOrder');
	}

	// return Responsibilities in order
	public function getResponsibilityList() {
		return $this->Responsibilities()->sort('SortOrder');
	}

	public function ApplicationLink(){
		return $this->parent()->Application()->URL;
	}

	public function getTags(){
		return $this->Categories();
	}

}

class Job_Controller extends DetailPage_Controller{

	private static $allowed_actions = array(
		'apply',
		'JobApp',
		'complete');

	public function init() {
		parent::init();

		Requirements::css('jobs/css/job.css');

	}

	public function apply() {

		$Form = $this->JobApp();

		$Form->Fields()->insertBefore(ReadOnlyField::create('PositionName', 'Position', $this->getTitle()), 'Available');
		$Form->Fields()->push(HiddenField::create('JobID', 'JobID', $this->ID));

		$page = $this->customise(array(
			'Form' => $Form
		))/*->renderWith(array('Page', 'Page'))*/;

		return $page;

	}

	public function JobApp() {

		$App = singleton('JobSubmission');

		$fields = $App->getFrontEndFields();

		$actions = FieldList::create(
			new FormAction('doApply', 'Apply')
		);

		$required = $App->getRequiredFields();

		$required = new RequiredFields(array(
			'FirstName',
			'LastName',
			'Email',
			'Phone'));


		return Form::create($this, "JobApp", $fields, $actions, $required);

	}

	public function doApply($data, $form){

		$entry = new JobSubmission();
		$form->saveInto($entry);

		$entry->JobID = $this->ID;

        if($entry->write()){
	        $to = $this->parent()->EmailRecipient;
	        $from = $this->parent()->FromAddress;
	        $subject = $this->parent()->EmailSubject;
	        $body = $this->parent()->EmailMessage;

	        $email = new Email($from,$to,$subject,$body);
	        $email->setTemplate('JobSubmission');

	        $email->populateTemplate(
	        	JobSubmission::get()
	        	->byID($entry->ID)
	        );

	        $email->send();

	        $this->redirect(Controller::join_links($this->Link(), 'complete'));
        }

	}

	public function complete() {
		return $this->customise(array());
	}

}