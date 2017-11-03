<?php

namespace Dynamic\Jobs\Model;

use \Page;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldAddExistingAutocompleter;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use Symbiote\GridFieldExtensions\GridFieldAddExistingSearchButton;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

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
        'Sections' => JobSection::class,
        'Submissions' => JobSubmission::class,
    );

    /**
     * @var array
     */
    private static $many_many = array(
        'Categories' => JobCategory::class,
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
    private static $default_parent = JobHolder::class;

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
                singleton(Job::class)->dbObject('PositionType')->enumValues()
            )->setEmptyString('--select--'),
            DateField::create('PostDate', 'Position Post Date'),
                //->setConfig('showcalendar', true),
            DateField::create('EndPostDate', 'Position Post End Date'),
                //->setConfig('showcalendar', true),
        ]);

        if ($this->ID) {
            // sections
            $config = GridFieldConfig_RelationEditor::create();
            if (class_exists(GridFieldOrderableRows::class)) {
                $config->addComponent(new GridFieldOrderableRows('Sort'));
            }
            $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
            $config->removeComponentsByType(GridFieldDeleteAction::class);
            $config->addComponent(new GridFieldDeleteAction(false));
            $sections = $this->Sections()->sort('Sort');
            $sectionsField = GridField::create('Sections', 'Sections', $sections, $config);
            $fields->addFieldsToTab('Root.Details.Sections', array(
                $sectionsField,
            ));

            // categories
            $config = GridFieldConfig_RelationEditor::create();
            if (class_exists(GridFieldOrderableRows::class)) {
                $config->addComponent(new GridFieldOrderableRows('Sort'));
            }
            if (class_exists(GridFieldAddExistingSearchButton::class)) {
                $config->removeComponentsByType(GridFieldAddExistingAutocompleter::class);
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
    public function canCreate($member = null, $context = [])
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
