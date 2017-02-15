<?php

class JobCategory extends DataObject
{
    /**
     * @var array
     */
    private static $db = [
        'Title' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $belongs_many_many = array(
        'Jobs' => 'Job'
    );
}
