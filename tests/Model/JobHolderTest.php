<?php

namespace Dynamic\Jobs\Tests;

use Dynamic\Jobs\Model\JobHolder;
use SilverStripe\Core\Injector\Injector;
use \SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class JobHolderTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = '../fixtures.yml';

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
}
