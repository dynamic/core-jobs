<?php

class JobSubmission extends DataObject {
	
	static $db = array(
		'FirstName' => 'Varchar(255)',
		'LastName' => 'Varchar(255)',
		'Email' => 'Varchar(255)',
		'Phone' => 'Varchar(255)',
		'Message' => 'Text',
		'Available' => 'Date',
		
	);
	
	static $has_one = array(
		'Job' => 'Job',
		'Resume' => 'File'
	);
	
	static $has_many = array(
		'Links' => 'JobLink'
	);
	
	static $singular_name = "Application";
	static $plural_name = "Applications";
	
	static $default_sort = 'Created DESC';
	
	static $casting = array(
		"CreatedLabel" => "Text"
	);
	
	static $summary_fields = array(
		'Name' => 'Applicant',
		'Job.Title' => 'Job',
		'CreatedLabel' => 'Date'
	);
	
	static $searchable_fields = array(
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
		//$fields = $this->scaffoldFormFields($params);
		
		// Date Available
		//$DateField = new DateField('Available', 'Date Available');
		//$DateField->setConfig('showcalendar', true);
		
		// Resume Upload
		//$ResumeField = new FileField('Resume', 'Resume');
		//$ResumeField->getValidator()->allowedExtensions = array('pdf', 'doc', 'docx');
		//$ResumeField->setFolderName('Uploads/Resumes');
		//$ResumeField->setAttribute('required', true);
		//$ResumeField->setConfig('allowedMaxFileNumber', 1);
		//$ResumeField->setRecord($this); 
		
		// Links
		$gridFieldConfig = GridFieldConfig_RelationEditor::create();
		$LinksField = new GridField('Links', 'Links', JobLink::get(), $gridFieldConfig);
		
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
			FileField::create('Resume', 'Resume')
				->addHelpText('accepted formats: .doc, .docx, .pdf')
				->setFolderName('Uploads/Resumes'),
			//$ResumeField,
			//$LinksField,
			TextareaField::create('Message')
				->setSize('xlarge')
		);
		
		//$this->extend('updateFrontEndFields', $fields);
	
		return $fields;
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
	
}