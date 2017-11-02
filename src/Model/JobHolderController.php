<?php

namespace Dynamic\Jobs\Model;

use \PageController;
use SilverStripe\ORM\FieldType\DBHTMLText;

/**
 * Class JobHolderController
 * @package Dynamic\Jobs\Model
 */
class JobHolderController extends PageController
{
    /**
     * @var array
     */
    private static $allowed_actions = array(
        'application'
    );

    /**
     * @return DBHTMLText
     */
    public function application()
    {
        //Determine if the application is valid
        if ($params = $this->getURLParams()) {
            if (is_numeric($params['ID']) && $ID = $params['ID']) {
                $application = JobSubmission::get()
                    ->byID($ID);
                return $application->renderWith('JobSubmission');
            }
        }
        return null;
    }
}

