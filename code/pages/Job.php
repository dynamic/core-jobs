<?php

class Job extends Page implements PermissionProvider
{
    /**
     * @var string
     */
    private static $singular_name = 'Job';

    /**
     * @var string
     */
    private static $plural_name = 'Jobs';

    /**
     * @var string
     */
    private static $description = 'Job detail page allowing for application submissions';

    /**
     * @var array
     */
    private static $db = array(
        'PositionType' => "Enum('Full-time, Part-time, Freelance, Internship')",
        'PostDate' => 'Date',
        'EndPostDate' => 'Date',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Sections' => 'JobSection',
        'Submissions' => 'JobSubmission',
    );

    /**
     * @var array
     */
    private static $many_many = array(
        'Categories' => 'JobCategory',
    );

    /**
     * @var array
     */
    private static $many_many_extraFields = array(
        'Categories' => array(
            'Sort' => 'Int'
        ),
    );

    /**
     * @var array
     */
    private static $searchable_fields = [
        'Categories.ID' => [
            'title' => 'Category',
        ],
        'PositionType' => [
            'title' => 'Type',
        ],
    ];

    /**
     * @var string
     */
    private static $default_parent = 'JobHolder';

    /**
     * @var bool
     */
    private static $can_be_root = false;

    /**
     *
     */
    public function populateDefaults()
    {
        $this->PostDate = date('Y-m-d');

        parent::populateDefaults();
    }

    /**
     * @return FieldList
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldsToTab('Root.Details.Info', [
            DropdownField::create(
                'PositionType',
                'Position Type',
                singleton('Job')->dbObject('PositionType')->enumValues()
            )->setEmptyString('--select--'),
            DateField::create('PostDate', 'Position Post Date')
                ->setConfig('showcalendar', true),
            DateField::create('EndPostDate', 'Position Post End Date')
                ->setConfig('showcalendar', true),
        ]);

        if ($this->ID) {
            // sections
            $config = GridFieldConfig_RelationEditor::create();
            if (class_exists('GridFieldOrderableRows')) {
                $config->addComponent(new GridFieldOrderableRows('Sort'));
            }
            $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
            $config->removeComponentsByType('GridFieldDeleteAction');
            $config->addComponent(new GridFieldDeleteAction(false));
            $sections = $this->Sections()->sort('Sort');
            $sectionsField = GridField::create('Sections', 'Sections', $sections, $config);
            $fields->addFieldsToTab('Root.Details.Sections', array(
                $sectionsField,
            ));

            // categories
            $config = GridFieldConfig_RelationEditor::create();
            if (class_exists('GridFieldOrderableRows')) {
                $config->addComponent(new GridFieldOrderableRows('Sort'));
            }
            if (class_exists('GridFieldAddExistingSearchButton')) {
                $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
                $config->addComponent(new GridFieldAddExistingSearchButton());
            }
            $categories = $this->Categories()->sort('Sort');
            $categoriesField = GridField::create('Categories', 'Categories', $categories, $config);
            $fields->addFieldsToTab('Root.Details.Categories', array(
                $categoriesField,
            ));
        }

        return $fields;
    }

    /**
     * @return string
     */
    public function getApplyButton()
    {
        $apply = Controller::join_links(
            $this->Link(),
            'apply'
        );
        return $apply;
    }

    /**
     * @return mixed
     */
    public function getApplicationLink()
    {
        if ($this->parent()->Application()->ID != 0) {
            return $this->parent()->Application()->URL;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getCategoryList()
    {
        return $this->Categories()->sort('Sort');
    }

    /**
     * @return bool
     */
    public function getPrimaryCategory()
    {
        if ($this->Categories()->exists()) {
            return $this->Categories()->first();
        }
        return false;
    }

    /**
     * @return array
     */
    public function providePermissions()
    {
        return array(
            'Job_EDIT' => 'Edit a Job',
            'Job_DELETE' => 'Delete a Job',
            'Job_CREATE' => 'Create a Job',
        );
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canEdit($member = null)
    {
        return Permission::check('Job_EDIT', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canDelete($member = null)
    {
        return Permission::check('Job_DELETE', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canCreate($member = null)
    {
        return Permission::check('Job_CREATE', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }
}

class Job_Controller extends Page_Controller
{
    /*
     *
     */
    private static $allowed_actions = array(
        'apply',
        'JobApp',
        'complete');

    /**
     * @return ViewableData_Customised
     */
    public function apply()
    {
        $Form = $this->JobApp();

        $Form->Fields()->insertBefore(
            ReadOnlyField::create(
                'PositionName',
                'Position',
                $this->getTitle()
            ),
            'FirstName'
        );
        $Form->Fields()->push(HiddenField::create('JobID', 'JobID', $this->ID));

        $page = $this->customise(array(
            'Form' => $Form
        ));

        return $page;
    }

    /**
     * @return static
     */
    public function JobApp()
    {
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

    /**
     * @param $data
     * @param $form
     */
    public function doApply($data, $form)
    {
        $entry = new JobSubmission();
        $form->saveInto($entry);

        $entry->JobID = $this->ID;

        if ($entry->write()) {
            $to = $this->parent()->EmailRecipient;
            $from = $this->parent()->FromAddress;
            $subject = $this->parent()->EmailSubject;
            $body = $this->parent()->EmailMessage;

            $email = new Email($from, $to, $subject, $body);
            $email->setTemplate('JobSubmission');

            $email->populateTemplate(
                JobSubmission::get()
                ->byID($entry->ID)
            );

            $email->send();

            $this->redirect(Controller::join_links($this->Link(), 'complete'));
        }
    }

    /**
     * @return ViewableData_Customised
     */
    public function complete()
    {
        return $this->customise(array());
    }
}
