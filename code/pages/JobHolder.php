<?php

class JobHolder extends Page
{
    /**
     * @var string
     */
    private static $singular_name = "Job Group";

    /**
     * @var string
     */
    private static $plural_name = "Job Groups";

    /**
     * @var string
     */
    private static $description = 'display a list of available jobs';

    /**
     * @var array
     */
    private static $db = array(
        'Message' => 'HTMLText',
        'FromAddress' => 'Varchar(255)',
        'EmailRecipient' => 'Varchar(255)',
        'EmailSubject' => 'Varchar(255)'
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Application' => 'File'
    );

    /**
     * @var string
     */
    private static $default_child = 'Job';

    /**
     * @var array
     */
    private static $allowed_children = array('Job');

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $app = new UploadField('Application', 'Application Form');
        $app->allowedExtensions = array('pdf','PDF');
        $fields->addFieldToTab('Root.ApplicationFile', $app);

        $fields->addFieldsToTab('Root.Configuration', array(
            EmailField::create('FromAddress', 'Submission From Address'),
            EmailField::create('EmailRecipient', 'Submission Recipient'),
            TextField::create('EmailSubject', 'Submission Email Subject Line'),
            HTMLEditorField::create('Message', 'Submission Message'),
        ));

        return $fields;
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        if(!$this->EmailRecipient) {
            $result->error('Please enter Email Recpient before saving.');
        }

        if(!$this->EmailSubject) {
            $result->error('Please enter Email Subject before saving.');
        }

        return $result;
    }

    /**
     * @return DataList
     */
    public function getPostedJobs()
    {
        $jobs = Job::get()
            ->filter([
                'PostDate:LessThanOrEqual' => date('Y-m-d'),
                'EndPostDate:GreaterThanOrEqual' => date('Y-m-d'),
            ])
            ->sort('PostDate DESC');
        return $jobs;
    }
}

class JobHolder_Controller extends Page_Controller
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'application'
    );

    /**
     * @return HTMLText
     */
    public function application()
    {
        //Determine if the application is valid
        if ($params = $this->getURLParams()) {
            if (is_numeric($params['ID']) && $ID = $params['ID']) {
                $application = JobSubmission::get()
                    ->byID($ID);
                return $application->renderWith('JobSubmission');
            }
        }
    }
}
