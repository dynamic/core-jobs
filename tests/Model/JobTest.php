<?php

namespace Dynamic\Jobs\Tests;

use Dynamic\Jobs\Model\Job;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class JobTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

    /**
     *
     */
    public function testGetCMSFields()
    {
        /** @var Job $object */
        $object = Injector::inst()->create(Job::class);
        $fields = $object->getCMSFields();
        $this->assertInstanceOf(FieldList::class, $fields);
    }
}
