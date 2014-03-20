<?php

class JobDetail extends DataObject {

	private static $singular_name = 'Job Detail';
	private static $plural_name = 'Job Details';
	private static $description = 'Basic class to handle job details';

	private static $db = array(
		'Title' => 'Varchar(255)',
		'Content' => 'Text'
	);
	private static $has_one = array();
	private static $has_many = array();
	private static $many_many = array();
	private static $many_many_extraFields = array();
	private static $belongs_many_many = array();

	private static $casting = array();
	private static $defaults = null;
	private static $default_sort = 'Title';


	private static $summary_fields = array(
		'Title' => 'Title',
		'Content' => 'Content'
	);
	private static $searchable_fields = array(
		'Title',
		'Content'
	);
	private static $field_labels = null;
	private static $indexes = null;

	public function getCMSFields(){
		$fields = parent::getCMSFields();

		$fields->addFieldToTab(
			'Root.Main',
			TextField::create('Title')
				->setTitle('Title')
		);
		$fields->addFieldToTab(
			'Root.Main',
			TextareaField::create('Content')
				->setTitle('Content')
		);

		$this->extend('updateCMSFields', $fields);

		return $fields;
	}

	public function validate(){
		$result = parent::validate();

		/*if($this->Country == 'DE' && $this->Postcode && strlen($this->Postcode) != 5) {
			$result->error('Need five digits for German postcodes');
		}*/

		return $result;
	}

}