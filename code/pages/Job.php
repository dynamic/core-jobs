<?php

class Job extends Page
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
        'ResponsibilitiesTitle' => 'Varchar(255)',
        'Responsibilities' => 'HTMLText',
        'DutiesTitle' => 'Varchar(255)',
        'Duties' => 'HTMLText',
        'SkillsTitle' => 'Varchar(255)',
        'Skills' => 'HTMLText',
        'ExperienceTitle' => 'Varchar(255)',
        'Experience' => 'HTMLText',
        'RequirementsTitle' => 'Varchar(255',
        'Requirements' => 'HTMLText',
        'SalaryTitle' => 'Varchar(255)',
        'Salary' => 'HTMLText',
    );

    /**
     * @var array
     */
    private static $has_many = array(
        'Submissions' => 'JobSubmission'
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
        $this->ResponsibilitiesTitle = 'Responsibilities';
        $this->DutiesTitle = 'Duties';
        $this->SkillsTitle = 'Skills';
        $this->ExperienceTitle = 'Experience';
        $this->RequirementsTitle = 'Requirements';
        $this->SalaryTitle = 'Salary and Benefits';

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

        $fields->addFieldsToTab('Root.Details.Responsibilities', [
            TextField::create('ResponsibilitiesTitle', 'Section Title'),
            HtmlEditorField::create('Responsibilities'),
        ]);

        $fields->addFieldsToTab('Root.Details.Duties', [
            TextField::create('DutiesTitle', 'Section Title'),
            HtmlEditorField::create('Duties'),
        ]);

        $fields->addFieldsToTab('Root.Details.Skills', [
            TextField::create('SkillsTitle', 'Section Title'),
            HtmlEditorField::create('Skills'),
        ]);

        $fields->addFieldsToTab('Root.Details.Experience', [
            TextField::create('ExperienceTitle', 'Section Title'),
            HtmlEditorField::create('Experience'),
        ]);

        $fields->addFieldsToTab('Root.Details.Requirements', [
            TextField::create('RequirementsTitle', 'Section Title'),
            HtmlEditorField::create('Requirements'),
        ]);

        $fields->addFieldsToTab('Root.Details.Salary', [
            TextField::create('SalaryTitle', 'Section Title'),
            HtmlEditorField::create('Salary'),
        ]);

        if ($this->ID) {
            $config = GridFieldConfig_RelationEditor::create();
            if (class_exists('GridFieldOrderableRows')) {
                $config->addComponent(new GridFieldOrderableRows('Sort'));
            }
            if (class_exists('GridFieldAddExistingSearchButton')) {
                $config->removeComponentsByType('GridFieldAddExistingAutocompleter');
                $config->addComponent(new GridFieldAddExistingSearchButton());
            }
            $categories = $this->Categories()->sort('Sort');
            $categoriesField = GridField::create('Spiffs', 'Spiffs', $categories, $config);
            $fields->addFieldsToTab('Root.Details.Categories', array(
                $categoriesField,
            ));
        }

        return $fields;
    }

    /**
     * @return ValidationResult
     */
    public function validate()
    {
        $result = parent::validate();

        /*if($this->Country == 'DE' && $this->Postcode && strlen($this->Postcode) != 5) {
            $result->error('Need five digits for German postcodes');
        }*/

        return $result;
    }

    /**
     * @return bool
     */
    public function getPosted()
    {
        if ($this->PostDate) {
            return $this->obj('PostDate')->NiceUS();
        }
        return false;
    }

    /**
     * @return string
     */
    public function getApplyButton()
    {
        $apply = '<button type="submit" class="job-apply" onclick="parent.location=\''.
            $this->Link().
            'apply\'">Apply for this position</button>';

        if ($this->parent()->Application()->ID != 0) {
            $download = $this->parent()->Application()->URL;
            $apply.=" or <a href=\"$download\" target=\"_blank\">Download the Application</a>";
        }
        $apply .= "";

        return $apply;
    }

    /**
     * @return mixed
     */
    public function ApplicationLink()
    {
        return $this->parent()->Application()->URL;
    }

    /**
     * @return mixed
     */
    public function getCategoryList()
    {
        return $this->Categories()->sort('Sort');
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
            'Available'
        );
        $Form->Fields()->push(HiddenField::create('JobID', 'JobID', $this->ID));

        $page = $this->customise(array(
            'Form' => $Form
        ))/*->renderWith(array('Page', 'Page'))*/;

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
