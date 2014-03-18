<?php

class JobSkill extends JobDetail {

	private static $singular_name = 'Job Skill';
	private static $plural_name = 'Job Skills';
	private static $description = 'Skill for a related job';

	private static $db = array();
	private static $has_one = array();
	private static $has_many = array();
	private static $many_many = array();
	private static $many_many_extraFields = array();
	private static $belongs_many_many = array(
		'Jobs' => 'Job'
	);

	private static $casting = array();
	private static $defaults = null;
	private static $default_sort = null;


	private static $summary_fields = null;
	private static $searchable_fields = null;
	private static $field_labels = null;
	private static $indexes = null;

	public function getCMSFields(){
		$fields = parent::getCMSFields();


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