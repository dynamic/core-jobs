<?php

namespace Dynamic\Jobs\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBDatetime;

class JobHolderCollectionExtension extends Extension
{
    /**
     * @param $searchCriteria
     */
    public function updateCollectionFilters(&$searchCriteria)
    {
        $searchCriteria['ParentID'] = $this->owner->ID;
    }

    /**
     * @param $form
     */
    public function updateCollectionForm(Form &$form)
    {
        $fields = $form->Fields();
        $fields->dataFieldByName('Categories__ID')
            ->setEmptyString('(Any)');
    }

    /**
     * @param $collection
     */
    public function updateCollectionItems(ArrayList &$collection)
    {
        $collection = $collection->filterByCallback(function ($item) {
            return (
                $item->PostDate <= DBDatetime::now() &&
                $item->EndPostDate >= DBDatetime::now()
            );
        });
    }
}
