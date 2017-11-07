<?php

namespace Dynamic\Jobs\Tests;

use Dynamic\Jobs\Model\Job;
use Dynamic\Jobs\Model\JobController;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\Forms\Form;

/**
 * Class JobControllerTest
 * @package Dynamic\Jobs\Tests
 */
class JobControllerTest extends FunctionalTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     * @var bool
     */
    protected static $use_draft_site = true;

    /**
     * Tests apply()
     */
    public function testApply()
    {
        /** @var Job $object */
        $object = $this->objFromFixture(Job::class, 'one');
        $link = $object->Link('apply');

        $page = $this->get($link);
        $this->assertInstanceOf(HttpResponse::class, $page);
        $this->assertEquals(200, $page->getStatusCode());
    }

    /**
     * Tests JobApp()
     */
    public function testJobApp()
    {
        /** @var JobController $object */
        $object = Injector::inst()->create(JobController::class);
        $form = $object->JobApp();

        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * Tests doApply()
     */
    public function testDoApply()
    {
        $this->markTestIncomplete();
        // $this->autoFollowRedirection = false;

        /** @var Job $object */
        /*
        $object = $this->objFromFixture(Job::class, 'one');

        $this->get($object->Link('apply'));
        $page = $this->submitForm('Form_JobApp', 'action_doApply', array(
            'FirstName' => 'Eric',
            'LastName' => 'Praline',
            'Email' => 'eric.perline@gmail.com',
            'Phone' => '444-555-6666',
            'Resume' => null,
        ));

        print_r($page);

        $this->assertInstanceOf(HttpResponse::class, $page);
        $this->assertEquals(302, $page->getStatusCode());
        */
    }
}
