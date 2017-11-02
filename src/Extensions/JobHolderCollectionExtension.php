<?php

namespace Dynamic\Jobs\Extensions;

use SilverStripe\Core\Extension;
use SilverStripe\Forms\Form;
use SilverStripe\ORM\ArrayList;

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
     * @param $searchCriteria
     */
    public function updateCollectionItems(ArrayList &$collection, &$searchCriteria)
    {
        $collection = $collection->filterByCallback(function ($item, $list) {
            return (
                $item->PostDate <= date('Y-m-d') &&
                $item->EndPostDate >= date('Y-m-d')
            );
        });
    }
}
