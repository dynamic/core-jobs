1`<?php

class JobTest extends SapphireTest
{
    /**
     * @var string
     */
    protected static $fixture_file = 'core-jobs/tests/fixtures.yml';

    /**
     *
     */
    public function testGetCMSFields()
    {
        $object = $this->objFromFixture('Job', 'one');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf('FieldList', $fields);
        $this->assertNotNull($fields->dataFieldByName('Categories'));
    }
}