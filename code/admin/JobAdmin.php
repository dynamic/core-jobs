<?php

class JobAdmin extends ModelAdmin {
	private static $managed_models = array(
		'JobSubmission',
		'JobCategory');

	private static $url_segment = 'jobs';

	private static $menu_title = 'Jobs';

}