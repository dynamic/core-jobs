1`<?php

class JobHolderTest extends SapphireTest
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
        $object = $this->objFromFixture('JobHolder', 'default');
        $fields = $object->getCMSFields();
        $this->assertInstanceOf('FieldList', $fields);
    }
}