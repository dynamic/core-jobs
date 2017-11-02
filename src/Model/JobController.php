<?php

namespace Dynamic\Jobs\Model;

use PageController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Email\Email;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\View\ViewableData_Customised;

/**
 * Class JobController
 * @package Dynamic\Jobs\Model
 */
class JobController extends PageController
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
            ReadonlyField::create(
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
     * @return Form
     */
    public function JobApp()
    {
        $App = singleton(JobSubmission::class);

        $fields = $App->getFrontEndFields();

        $actions = FieldList::create(
            new FormAction('doApply', 'Apply')
        );

        $required = new RequiredFields(array(
            'FirstName',
            'LastName',
            'Email',
            'Phone'
        ));


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
