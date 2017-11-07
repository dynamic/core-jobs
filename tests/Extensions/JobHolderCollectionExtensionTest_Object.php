<?php

namespace Dynamic\Jobs\Tests\Extensions;

use Dynamic\Jobs\Extensions\JobHolderCollectionExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;

/**
 * Class JobHolderCollectionExtensionTest_Object
 * @package Dynamic\Jobs\Tests\Extensions
 */
class JobHolderCollectionExtensionTest_Object extends DataObject implements TestOnly
{
    /**
     * Needs its own table (table too long otherwise)
     *
     * @var string
     */
    private static $table_name = 'JobHolderCollectionExtension';

    /**
     * @var array
     */
    private static $extensions = array(
        JobHolderCollectionExtension::class
    );

    /**
     * @param $filter
     *
     * @return mixed
     */
    public function getCollectionFilters($filter)
    {
        $this->extend('updateCollectionFilters', $filter);

        return $filter;
    }

    /**
     * @param Form $form
     *
     * @return Form
     */
    public function getCollectionForm(Form $form)
    {
        $this->extend('updateCollectionForm', $form);

        return $form;
    }

    /**
     * @param ArrayList $collection
     *
     * @return ArrayList
     */
    public function getCollectionItems(ArrayList $collection)
    {
        $this->extend('updateCollectionItems', $collection);

        return $collection;
    }
}
