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

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canEdit($member = null)
    {
        return Permission::check('Job_EDIT', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canDelete($member = null)
    {
        return Permission::check('Job_DELETE', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool|int
     */
    public function canCreate($member = null)
    {
        return Permission::check('Job_CREATE', 'any', $member);
    }

    /**
     * @param null $member
     *
     * @return bool
     */
    public function canView($member = null)
    {
        return true;
    }
}
