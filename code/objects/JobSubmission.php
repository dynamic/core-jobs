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
		'Address' => 'Varchar(255)',
		'Address2' => 'Varchar(255)',
		'City' => 'Varchar(255)',
		'State' => 'Varchar(2)',
		'Postal' => 'Varchar(10)',
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
		$ResumeField = UploadField::create('Resume')->setTitle('Resume');
		$ResumeField->getValidator()->allowedExtensions = array('pdf', 'doc', 'docx');
		$ResumeField->setFolderName('Uploads/Resumes');
		$ResumeField->setConfig('allowedMaxFileNumber', 1);
		$ResumeField->setCanAttachExisting(false);
		$ResumeField->setCanPreviewFolder(false);
		$ResumeField->relationAutoSetting = false;

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
					new StateDropdownField('State', 'State'),
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

}