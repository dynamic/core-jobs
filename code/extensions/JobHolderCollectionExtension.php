<?php

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
    public function updateCollectionForm(&$form)
    {
        $fields = $form->Fields();
        $fields->dataFieldByName('Categories__ID')
            ->setEmptyString('(Any)');
    }

    /**
     * @param $collection
     * @param $searchCriteria
     */
    public function updateCollectionItems(&$collection, &$searchCriteria)
    {
        $collection = $collection->filterByCallback(function ($item, $list) {
            return (
                $item->PostDate <= date('Y-m-d') &&
                $item->EndPostDate >= date('Y-m-d')
            );
        });
    }
}