<?php

namespace Oro\BugTrackerBundle\Twig;

use Oro\BugTrackerBundle\Entity\Activity;


class ActivityExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('activity_template_detect', [$this, 'getActivityTemplate']),
        );
    }

    /**
     * @param Activity $activity
     * @return string
     */
    public function getActivityTemplate(Activity $activity)
    {
        // перенести в макрос

        $entity = $activity->getEntity();
        $type = $activity->getType();
        $templatePath = "BugTrackerBundle:Activity/$entity:$type.html.twig";

        return $templatePath;
    }
}
