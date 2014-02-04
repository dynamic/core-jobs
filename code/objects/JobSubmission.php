<?php

class JobSubmission extends DataObject {

	private static $singular_name = 'Application';
	private static $plural_name = 'Applications';
	private static $description = 'Online job application allowing for a resume upload';

	private static $db = array(
		'FirstName' => 'Varchar(255)',
		'LastName' => 'Varchar(255)',
		'Email' => 'Varchar(255)',
		'Phone' => 'Varchar(255)',
		'Message' => 'Text',
		'Available' => 'Date',

	);

	private static $has_one = array(
		'Job' => 'Job',
		'Resume' => 'File'
	);

	private static $has_many = array();
	private static $many_many = array();
	private static $many_many_extraFields = array();
	private static $belongs_many_many = array();

	private static $default_sort = 'Created DESC';

	private static $casting = array(
		"CreatedLabel" => "Text"
	);

	private static $summary_fields = array(
		'Name' => 'Applicant',
		'Job.Title' => 'Job',
		'CreatedLabel' => 'Date'
	);

	private static $searchable_fields = array(
		'FirstName',
		'LastName',
		'Job.ID'
	);

	public function getName() {
		if ($this->FirstName) {
			return $this->FirstName . ' ' . $this->LastName;
		} else {
			return 'No Name';
		}
	}

	public function getCreatedLabel() {
		return $this->getNiceDate();
	}

	// formattedDate
	public function getNiceDate() {
		return $this->obj('Created')->Format('M j Y g:i a');
	}

	public function getFrontEndFields($params = null) {

		// Resume Upload
		$ResumeField = FileField::create('Resume')->setTitle('Resume');
		$ResumeField->getValidator()->allowedExtensions = array('pdf', 'doc', 'docx');
		$ResumeField->setFolderName('Uploads/Resumes');
		$ResumeField->setConfig('allowedMaxFileNumber', 1);

		$fields = FieldList::create(
			TextField::create('FirstName', 'First Name')
				->setAttribute('required', true),
			TextField::create('LastName', 'Last Name')
				->setAttribute('required', true),
			EmailField::create('Email')
				->setAttribute('required', true),
			TextField::create('Phone')
				->setAttribute('required', true),
			DateField::create('Available', 'Date Available')
				->setConfig('showcalendar', true),
			$ResumeField,
			TextareaField::create('Message'),
			HiddenField::create('JobID')
				->setValue($this->getJobID())
		);

		$this->extend('updateFrontEndFields', $fields);

		return $fields;
	}

	public function getJobID(){
		$controller = Controller::curr();
		$request = $controller->Request;
		$params = $request->allParams();
		debug::show($params);
	}

	// Required fields
	public function getRequiredFields() {
		return new RequiredFields(array(
			'FirstName',
			'LastName',
			'Email',
			'Phone'
		));
	}

	public function getCMSFields() {

		// Jobs dropdown
		$JobsField = new DropdownField('JobID', 'Job', Job::get()->map('ID', 'Title'));
		$JobsField->setEmptyString('--Select--');

		$fields = new FieldList(
			new TabSet('Root',
				new Tab('Main',
					$JobsField,
					new TextField('FirstName'),
					new TextField('LastName'),
					new EmailField('Email'),
					new TextField('Phone'),
					new DateField('Available'),
					new UploadField('Resume'),
					new TextareaField('Message')
				)
			)
		);

		return $fields;

	}

	// populate drop downs in forms
	public function StatesList() {
		return array(
			'AL'=>"Alabama",
			'AK'=>"Alaska",
			'AZ'=>"Arizona",
			'AR'=>"Arkansas",
			'CA'=>"California",
			'CO'=>"Colorado",
			'CT'=>"Connecticut",
			'DE'=>"Delaware",
			'DC'=>"District Of Columbia",
			'FL'=>"Florida",
			'GA'=>"Georgia",
			'HI'=>"Hawaii",
			'ID'=>"Idaho",
			'IL'=>"Illinois",
			'IN'=>"Indiana",
			'IA'=>"Iowa",
			'KS'=>"Kansas",
			'KY'=>"Kentucky",
			'LA'=>"Louisiana",
			'ME'=>"Maine",
			'MD'=>"Maryland",
			'MA'=>"Massachusetts",
			'MI'=>"Michigan",
			'MN'=>"Minnesota",
			'MS'=>"Mississippi",
			'MO'=>"Missouri",
			'MT'=>"Montana",
			'NE'=>"Nebraska",
			'NV'=>"Nevada",
			'NH'=>"New Hampshire",
			'NJ'=>"New Jersey",
			'NM'=>"New Mexico",
			'NY'=>"New York",
			'NC'=>"North Carolina",
			'ND'=>"North Dakota",
			'OH'=>"Ohio",
			'OK'=>"Oklahoma",
			'OR'=>"Oregon",
			'PA'=>"Pennsylvania",
			'RI'=>"Rhode Island",
			'SC'=>"South Carolina",
			'SD'=>"South Dakota",
			'TN'=>"Tennessee",
			'TX'=>"Texas",
			'UT'=>"Utah",
			'VT'=>"Vermont",
			'VA'=>"Virginia",
			'WA'=>"Washington",
			'WV'=>"West Virginia",
			'WI'=>"Wisconsin",
			'WY'=>"Wyoming",
			'-' => '-----',
			'AB' => 'Alberta',
			'BC' => 'British Columbia',
			'MB' => 'Manitoba',
			'NB' => 'New Brunswick',
			'NL' => 'Newfoundland and Labrador',
			'NS' => 'Nova Scotia',
			'ON' => 'Ontario',
			'PE' => 'Prince Edward Island',
			'QC' => 'Quebec',
			'SK' => 'Saskatchewan'
		);
	}

}