<?php

namespace Dynamic\Jobs\Tests;

use Dynamic\Jobs\Model\Job;
use Dynamic\Jobs\Model\JobHolder;
use SilverStripe\Core\Injector\Injector;
use \SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\ValidationResult;

class JobHolderTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     * {@inheritdoc}
     */
    public function tearDown()
    {
        DBDatetime::clear_mock_now();
        parent::tearDown();
    }

    /**
     * Tests getCMSFields()
     */
    public function testGetCMSFields()
    {
        /** @var JobHolder $object */
        $object = Injector::inst()->create(JobHolder::class);
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }

    /**
     * Tests validate()
     */
    public function testValidate()
    {
        /** @var JobHolder $object */
        $object = Injector::inst()->create(JobHolder::class);
        $valid = $object->validate();
        $this->assertInstanceOf(ValidationResult::class, $valid);
    }

    /**
     * Tests getPostedJobs()
     */
    public function testGetPostedJobs()
    {
        /** @var JobHolder $holder */
        $holder = $this->objFromFixture(JobHolder::class, 'default');

        /** @var Job $past */
        $past = $this->objFromFixture(Job::class, 'past');
        $past->write();
        /** @var Job $open */
        $open = $this->objFromFixture(Job::class, 'open');
        $open->write();
        /** @var Job $future */
        $future = $this->objFromFixture(Job::class, 'future');
        $future->write();

        DBDatetime::set_mock_now('2017-11-15');
        $jobCount = $holder->getPostedJobs()->count();
        $this->assertEquals(1, $jobCount);

        DBDatetime::set_mock_now('2017-11-29');
        $jobCount = $holder->getPostedJobs()->count();
        $this->assertEquals(2, $jobCount);

        DBDatetime::set_mock_now('2017-12-15');
        $jobCount = $holder->getPostedJobs()->count();
        $this->assertEquals(0, $jobCount);
    }
}
