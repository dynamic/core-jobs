<?php

class JobCategory extends Tag {

	private static $belongs_many_many = array(
		'Jobs' => 'Job');

}