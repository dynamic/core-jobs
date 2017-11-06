<?php

namespace Dynamic\Jobs\Tests\Extensions;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;

/**
 * Class JobHolderCollectionExtensionTest
 * @package Dynamic\Jobs\Tests\Extensions
 */
class JobHolderCollectionExtensionTest extends SapphireTest
{
    /**
     * @var array
     */
    protected static $extra_dataobjects = array(
        JobHolderCollectionExtensionTest_Object::class,
    );

    /**
     * Tests updateCollectionFilters()
     */
    public function testUpdateCollectionFilters()
    {
        /** @var JobHolderCollectionExtensionTest_Object $object */
        $object = Injector::inst()->create(JobHolderCollectionExtensionTest_Object::class);
        $object->write();
        $filter = array();
        $newFilter = $object->getCollectionFilters($filter);

        $this->assertArrayHasKey('ParentID', $newFilter);
    }

    /**
     * Tests updateCollectionForm()
     */
    public function testUpdateCollectionForm()
    {
        /** @var JobHolderCollectionExtensionTest_Object $object */
        $object = Injector::inst()->create(JobHolderCollectionExtensionTest_Object::class);

        $fields = new FieldList(
            DropdownField::create('Categories__ID')
        );

        $form = new Form(null, 'Form', $fields, new FieldList());
        $newForm = $object->getCollectionForm($form);

        $this->assertInstanceOf(Form::class, $newForm);
    }
}
