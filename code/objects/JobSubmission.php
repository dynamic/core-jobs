<?php

class JobSubmission extends DataObject
{
    /**
     * @var string
     */
    private static $singular_name = 'Application';

    /**
     * @var string
     */
    private static $plural_name = 'Applications';

    /**
     * @var string
     */
    private static $description = 'Online job application allowing for a resume upload';

    /**
     * @var array
     */
    private static $db = array(
        'FirstName' => 'Varchar(255)',
        'LastName' => 'Varchar(255)',
        'Email' => 'Varchar(255)',
        'Phone' => 'Varchar(255)',
        'Available' => 'Date',
        'Content' => 'HTMLText',
    );

    /**
     * @var array
     */
    private static $has_one = array(
        'Job' => 'Job',
        'Resume' => 'File'
    );

    private static $default_sort = 'Created DESC';

    /**
     * @var array
     */
    private static $casting = array(
        "CreatedLabel" => "Text"
    );

    /**
     * @var array
     */
    private static $summary_fields = array(
        'Name' => 'Applicant',
        'Job.Title' => 'Job',
        'Created.NiceUS' => 'Date'
    );

    /**
     * @var array
     */
    private static $searchable_fields = array(
        'FirstName',
        'LastName',
        'Job.ID'
    );

    /**
     * @return string
     */
    public function getName()
    {
        if ($this->FirstName) {
            return $this->FirstName . ' ' . $this->LastName;
        } else {
            return 'No Name';
        }
    }

    public function getTitle()
    {
        return $this->getName();
    }

    /**
     * @param null $params
     * @return static
     */
    public function getFrontEndFields($params = null)
    {
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
            SimpleHtmlEditorField::create('Content', 'Cover Letter'),
            HiddenField::create('JobID')
                ->setValue($this->getCurrentJob())
        );

        $this->extend('updateFrontEndFields', $fields);

        return $fields;
    }

    /**
     * @return RequiredFields
     */
    public function getRequiredFields()
    {
        return new RequiredFields(array(
            'FirstName',
            'LastName',
            'Email',
            'Phone'
        ));
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->removeByName([
            'JobID',
        ]);

        $JobsField = DropdownField::create('JobID', 'Job', Job::get()->map('ID', 'Title'))
            ->setEmptyString('--Select--');

        $fields->addFieldsToTab('Root.Main', array(
            ReadonlyField::create('JobTitle', 'Job', $this->Job()->getTitle()),
            new TextField('FirstName'),
            new TextField('LastName'),
            new EmailField('Email'),
            new TextField('Phone'),
            new DateField('Available'),
            new UploadField('Resume')));

        return $fields;
    }

    public function getCurrentJob(){
        $controller = Controller::curr();
        $request = $controller->Request;
        $params = $request->allParams();
    }
}
