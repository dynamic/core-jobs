<?php

namespace Dynamic\Jobs\Tests\Extensions;

use Dynamic\Jobs\Extensions\JobHolderCollectionExtension;
use SilverStripe\Dev\TestOnly;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\DataObject;

/**
 * Class JobHolderCollectionExtensionTest_Object
 * @package Dynamic\Jobs\Tests\Extensions
 */
class JobHolderCollectionExtensionTest_Object extends DataObject implements TestOnly
{
    /**
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

    public function getCollectionForm(Form $form)
    {
        $this->extend('updateCollectionForm', $form);

        return $form;
    }
}