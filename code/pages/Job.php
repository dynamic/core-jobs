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
		'EndPostDate' => 'Date',
		'Experience' => 'Varchar(200)'
	);

	private static $has_many = array(
		'Submissions' => 'JobSubmission'
	);

	private static $many_many = array(
		'Tags' => 'Tag',
		'Requirements' => 'JobRequirement',
		'Skills' => 'JobSkill',
		'Responsibilities' => 'JobResponsibility'
	);

	private static $many_many_extraFields = array(
		'Requirements' => array(
			'SortOrder' => 'Int'
		),
		'Skills' => array(
			'SortOrder' => 'Int'
		),
		'Responsibilities' => array(
			'SortOrder' => 'Int'
		)
	);

	private static $belongs_many_many = array();

	private static $casting = array();
	private static $defaults = null;
	private static $default_sort = null;

	private static $summary_fields = null;
	private static $searchable_fields = null;
	private static $field_labels = null;
	private static $indexes = null;

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
		$endPostDate = DateField::create('EndPostDate', 'Position Post End Date')
			->setConfig('showcalendar', true)
			->setConfig('dateformat', 'MMM dd, YYYY');

		$requirementsConfig = GridFieldConfig_RelationEditor::create();
		$requirementsConfig->addComponent(new GridFieldSortableRows("SortOrder"));
		if(class_exists('GridFieldManyRelationHandler')){
			$requirementsConfig->removeComponentsByType('GridFieldAddExistingAutocompleter');
			$requirementsConfig->addComponent(new GridFieldManyRelationHandler());
		}

		$requirementsGrid = new GridField(
			'Requirements',
			'Job Requirements',
			$this->Requirements()->sort('SortOrder'),
			$requirementsConfig
		);

		$skillsConfig = GridFieldConfig_RelationEditor::create();
		$skillsConfig->addComponent(new GridFieldSortableRows("SortOrder"));
		if(class_exists('GridFieldManyRelationHandler')){
			$skillsConfig->removeComponentsByType('GridFieldAddExistingAutocompleter');
			$skillsConfig->addComponent(new GridFieldManyRelationHandler());
		}

		$skillsGrid = new GridField(
			'Skills',
			'Job Skills',
			$this->Skills()->sort('SortOrder'),
			$skillsConfig
		);

		$responsibilitiesConfig = GridFieldConfig_RelationEditor::create();
		$responsibilitiesConfig->addComponent(new GridFieldSortableRows("SortOrder"));
		if(class_exists('GridFieldManyRelationHandler')){
			$responsibilitiesConfig->removeComponentsByType('GridFieldAddExistingAutocompleter');
			$responsibilitiesConfig->addComponent(new GridFieldManyRelationHandler());
		}

		$responsibilitiesGrid = new GridField(
			'Responsibilities',
			'Job Responsibilities',
			$this->Responsibilities()->sort('SortOrder'),
			$responsibilitiesConfig
		);


		$fields->addFieldsToTab(
			"Root.JobDetails",
			array(
				new HeaderField(
					'JobType',
					'Position Details',
					3
				),
				DropdownField::create(
					'PositionType',
					'Position Type',
					singleton('Job')->dbObject('PositionType')->enumValues()
				)->setEmptyString('--select--'),
				$PostDate,
				$endPostDate
			)
		);

		$fields->addFieldToTab(
			'Root.JobRequirements',
			$requirementsGrid
		);
		$fields->addFieldToTab(
			'Root.JobSkills',
			$skillsGrid
		);
		$fields->addFieldToTab(
			'Root.JobResponsibilities',
			$responsibilitiesGrid
		);

		$fields->extend('updateCMSFields', $fields);

		$fields->removeByName('Address');
		if(class_exists('Addressable')){

			$postal = new RegexTextField('Postcode', 'Postal Code');
			$postal->setRegex('/^[0-9]+$/');

			$fields->addFieldsToTab(
				'Root.JobDetails',
				array(
					new HeaderField('PositionLocation', 'Position Location Details', 3),
					TextField::create('Address')
						->setTitle('Address'),
					TextField::create('Suburb')
						->setTitle('City'),
					$state = StateDropdownField::create('State')
						->setTitle('State/Province'),
					$postal,
					CountryDropdownField::create('Country')
						->setTitle('Country')
				)
			);

			$state->setEmptyString('(Select a state/province)');

		}


		return $fields;
	}

	public function validate(){
		$result = parent::validate();

		/*if($this->Country == 'DE' && $this->Postcode && strlen($this->Postcode) != 5) {
			$result->error('Need five digits for German postcodes');
		}*/

		return $result;
	}

	// Dates
	public function getPosted() {
		if ($this->PostDate) return $this->obj('PostDate')->NiceUS();
		return false;
	}

	// Apply Button
	public function getApplyButton() {
		$apply = '<button type="submit" class="job-apply" onclick="parent.location=\''.
			$this->Link().
			'apply\'">Apply for this position</button>';
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
		return $this->Tags();
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

		$Form->Fields()->insertBefore(
			ReadOnlyField::create(
				'PositionName',
				'Position',
				$this->getTitle()
			),
			'Available'
		);
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